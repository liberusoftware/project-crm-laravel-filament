<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Liberu\Affiliate\Contracts\AffiliateContractsServiceProvider;
use Liberu\Affiliate\Core\AffiliateCoreServiceProvider;
use Liberu\Affiliate\Core\Services\CommissionCalculator;
use Liberu\Passkeys\PasskeysServiceProvider;
use Liberu\Passkeys\Services\WebAuthnCeremonyService;
use Liberu\Payment\Adyen\AdyenPaymentAdapter;
use Liberu\Payment\Contracts\Contracts\PaymentGatewayAdapterContract;
use Liberu\Payment\Contracts\DTOs\BeginPaymentMethodSetupCommand;
use Liberu\Payment\Contracts\DTOs\CancelPaymentIntentCommand;
use Liberu\Payment\Contracts\DTOs\CapturePaymentCommand;
use Liberu\Payment\Contracts\DTOs\ConfirmPaymentIntentCommand;
use Liberu\Payment\Contracts\DTOs\ConfirmPaymentMethodSetupCommand;
use Liberu\Payment\Contracts\DTOs\ConnectionHealthDto;
use Liberu\Payment\Contracts\DTOs\CreatePaymentIntentCommand;
use Liberu\Payment\Contracts\DTOs\CreateTerminalPaymentCommand;
use Liberu\Payment\Contracts\DTOs\DetachPaymentMethodCommand;
use Liberu\Payment\Contracts\DTOs\MerchantAccountContextDto;
use Liberu\Payment\Contracts\DTOs\MoneyAmount;
use Liberu\Payment\Contracts\DTOs\PaymentCapabilityDescriptorDto;
use Liberu\Payment\Contracts\DTOs\RawWebhookPayloadDto;
use Liberu\Payment\Contracts\DTOs\RefundPaymentCommand;
use Liberu\Payment\Contracts\DTOs\SettlementQueryDto;
use Liberu\Payment\Contracts\DTOs\SettlementTransactionDto;
use Liberu\Payment\Contracts\DTOs\SubmitDisputeEvidenceCommand;
use Liberu\Payment\Contracts\DTOs\VoidPaymentCommand;
use Liberu\Payment\Contracts\Enums\PaymentLifecycleState;
use Liberu\Payment\Core\Services\PaymentGatewayRegistry;
use Liberu\Payment\Core\Services\PaymentOrchestrator;
use Liberu\Payment\GoCardless\GoCardlessPaymentAdapter;
use Liberu\Payment\Mollie\MolliePaymentAdapter;
use Liberu\Payment\Paddle\PaddlePaymentAdapter;
use Liberu\Payment\PayPal\PayPalPaymentAdapter;
use Liberu\Payment\Square\SquarePaymentAdapter;
use Liberu\Payment\Stripe\StripePaymentAdapter;

it('calculates affiliate commissions including zero and negative amounts', function () {
    $calculator = new CommissionCalculator();

    expect($calculator->calculateMinor(0, 1000, 50))->toBe(0)
        ->and($calculator->calculateMinor(-500, 1000, 50))->toBe(0)
        ->and($calculator->calculateMinor(10000, 1500, 25))->toBe(1525)
        ->and(class_exists(AffiliateContractsServiceProvider::class))->toBeTrue()
        ->and(class_exists(AffiliateCoreServiceProvider::class))->toBeTrue();
});

it('builds WebAuthn registration and assertion ceremony options', function () {
    $actor = new class() implements Authenticatable
    {
        public string $email = 'ada@example.test';

        public string $name = 'Ada Lovelace';

        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): int
        {
            return 42;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getRememberToken(): string
        {
            return '';
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }
    };

    $service = new WebAuthnCeremonyService();
    $registration = $service->beginRegistration($actor, 'crm.example.test', 'Liberu CRM');
    $assertion = $service->beginAssertion('crm.example.test', $actor);

    expect($registration['rp'])->toBe(['id' => 'crm.example.test', 'name' => 'Liberu CRM'])
        ->and($registration['user']['name'])->toBe('ada@example.test')
        ->and($registration['user']['displayName'])->toBe('Ada Lovelace')
        ->and($registration['challenge'])->not->toBeEmpty()
        ->and($assertion['rpId'])->toBe('crm.example.test')
        ->and($assertion['challenge'])->not->toBeEmpty()
        ->and(class_exists(PasskeysServiceProvider::class))->toBeTrue();
});

it('validates MoneyAmount currency and exercises PaymentGatewayRegistry and PaymentOrchestrator', function () {
    $amount = new MoneyAmount(2500, ' gbp ');
    expect($amount->amountMinor)->toBe(2500)
        ->and($amount->currency)->toBe('GBP');

    expect(fn () => new MoneyAmount(100, 'US'))->toThrow(InvalidArgumentException::class);

    $registry = new PaymentGatewayRegistry();

    $emptyKeyAdapter = new class() implements PaymentGatewayAdapterContract
    {
        public function providerKey(): string
        {
            return '   ';
        }

        public function descriptor(): PaymentCapabilityDescriptorDto
        {
            return new PaymentCapabilityDescriptorDto('empty', 'Empty', [], [], false, false, false, false, false, false, false, false, false);
        }

        public function testConnection(MerchantAccountContextDto $account): ConnectionHealthDto
        {
            return new ConnectionHealthDto('empty', false, 'no');
        }
    };

    expect(fn () => $registry->register($emptyKeyAdapter))->toThrow(InvalidArgumentException::class);
    expect($registry->has('adyen'))->toBeFalse();
    expect(fn () => $registry->resolve('missing'))->toThrow(InvalidArgumentException::class);

    $minimalAdapter = new class() implements PaymentGatewayAdapterContract
    {
        public function providerKey(): string
        {
            return 'minimal';
        }

        public function descriptor(): PaymentCapabilityDescriptorDto
        {
            return new PaymentCapabilityDescriptorDto('minimal', 'Minimal', [], [], false, false, false, false, false, false, false, false, false);
        }

        public function testConnection(MerchantAccountContextDto $account): ConnectionHealthDto
        {
            return new ConnectionHealthDto('minimal', true, 'ok');
        }
    };

    $adyen = new AdyenPaymentAdapter();
    $registry->register($minimalAdapter);
    $registry->register($adyen);

    expect($registry->has('ADYEN'))->toBeTrue()
        ->and($registry->resolve('adyen'))->toBe($adyen)
        ->and($registry->all())->toHaveKeys(['minimal', 'adyen']);

    $orchestrator = new PaymentOrchestrator($registry);
    $adyenAccount = new MerchantAccountContextDto('tenant-1', 'adyen', ['api_key' => 'secret'], true);
    $minimalAccount = new MerchantAccountContextDto('tenant-1', 'minimal', [], true);

    $intent = $orchestrator->createIntent(new CreatePaymentIntentCommand($adyenAccount, $amount, 'idem-1', captureImmediately: true));
    expect($intent->providerKey)->toBe('adyen');

    $capture = $orchestrator->capture(new CapturePaymentCommand($adyenAccount, $intent->providerIntentId, 'idem-2', $amount));
    expect($capture->state)->toBe(PaymentLifecycleState::Captured);

    $refund = $orchestrator->refund(new RefundPaymentCommand($adyenAccount, $intent->providerIntentId, $amount, 'idem-3', 'requested_by_customer'));
    expect($refund->state)->toBe(PaymentLifecycleState::Refunded);

    $body = json_encode(['id' => 'evt_1', 'event_type' => 'AUTHORISATION', 'resource_id' => 'res_1'], JSON_THROW_ON_ERROR);
    $sig = hash_hmac('sha256', $body, 'whsec');
    $event = $orchestrator->verifyWebhook(new RawWebhookPayloadDto('adyen', $body, ['X-Adyen-Hmac-Signature' => $sig], 'whsec'));
    expect($event->providerEventId)->toBe('evt_1');

    expect(fn () => $orchestrator->createIntent(new CreatePaymentIntentCommand($minimalAccount, $amount, 'i1')))->toThrow(RuntimeException::class)
        ->and(fn () => $orchestrator->capture(new CapturePaymentCommand($minimalAccount, 'p1', 'i2')))->toThrow(RuntimeException::class)
        ->and(fn () => $orchestrator->refund(new RefundPaymentCommand($minimalAccount, 'p1', $amount, 'i3')))->toThrow(RuntimeException::class)
        ->and(fn () => $orchestrator->verifyWebhook(new RawWebhookPayloadDto('minimal', '{}', [], 's')))->toThrow(RuntimeException::class);
});

it('covers all non-network payment gateway adapters and Stripe descriptor', function () {
    $adapters = [
        [new AdyenPaymentAdapter(), 'adyen', 'X-Adyen-Hmac-Signature', true],
        [new GoCardlessPaymentAdapter(), 'gocardless', 'Webhook-Signature', false],
        [new MolliePaymentAdapter(), 'mollie', 'X-Mollie-Signature', false],
        [new PaddlePaymentAdapter(), 'paddle', 'Paddle-Signature', false],
        [new PayPalPaymentAdapter(), 'paypal', 'PAYPAL-TRANSMISSION-SIG', false],
        [new SquarePaymentAdapter(), 'square', 'x-square-hmacsha256-signature', true],
    ];

    $amount = new MoneyAmount(5000, 'USD');

    foreach ($adapters as [$adapter, $key, $sigHeader, $hasTerminal]) {
        $account = new MerchantAccountContextDto('tenant-1', $key, ['api_key' => 'k'], true);
        $emptyAccount = new MerchantAccountContextDto('tenant-1', $key, [], false);

        expect($adapter->providerKey())->toBe($key)
            ->and($adapter->descriptor()->providerKey)->toBe($key)
            ->and($adapter->testConnection($account)->healthy)->toBeTrue()
            ->and($adapter->testConnection($emptyAccount)->healthy)->toBeFalse();

        $immediate = $adapter->createIntent(new CreatePaymentIntentCommand($account, $amount, 'k1', returnUrl: 'https://example.test/return', captureImmediately: true));
        $authOnly = $adapter->createIntent(new CreatePaymentIntentCommand($account, $amount, 'k2', captureImmediately: false));
        expect($immediate->state)->toBe(PaymentLifecycleState::RequiresCustomerAction)
            ->and($authOnly->state)->toBe(PaymentLifecycleState::Authorized);

        expect($adapter->confirmIntent(new ConfirmPaymentIntentCommand($account, $authOnly->providerIntentId, 'k3'))->state)->toBe(PaymentLifecycleState::Captured)
            ->and($adapter->cancelIntent(new CancelPaymentIntentCommand($account, $authOnly->providerIntentId, 'k4', 'duplicate'))->state)->toBe(PaymentLifecycleState::Cancelled)
            ->and($adapter->capture(new CapturePaymentCommand($account, $authOnly->providerIntentId, 'k5', $amount))->state)->toBe(PaymentLifecycleState::Captured)
            ->and($adapter->voidAuthorization(new VoidPaymentCommand($account, $authOnly->providerIntentId, 'k6'))->state)->toBe(PaymentLifecycleState::Voided)
            ->and($adapter->refund(new RefundPaymentCommand($account, $authOnly->providerIntentId, $amount, 'k7'))->state)->toBe(PaymentLifecycleState::Refunded);

        $setup = $adapter->beginSetup(new BeginPaymentMethodSetupCommand($account, 'cust-1', 'k8', 'https://example.test/setup'));
        $vaulted = $adapter->confirmSetup(new ConfirmPaymentMethodSetupCommand($account, $setup->setupReferenceId, 'k8b'));
        $adapter->detachPaymentMethod(new DetachPaymentMethodCommand($account, $vaulted->providerMethodId));
        expect($vaulted->providerMethodId)->toBe($setup->setupReferenceId);

        $body = json_encode(['id' => 'ev-1', 'type' => 'payment.captured', 'resource_id' => 'res-1'], JSON_THROW_ON_ERROR);
        $sig = hash_hmac('sha256', $body, 'secret');
        $parsed = $adapter->verifyAndParse(new RawWebhookPayloadDto($key, $body, [$sigHeader => [$sig]], 'secret'));
        expect($parsed->providerEventId)->toBe('ev-1');

        expect(fn () => $adapter->verifyAndParse(new RawWebhookPayloadDto($key, $body, [$sigHeader => 'bad-sig'], 'secret')))
            ->toThrow(RuntimeException::class);

        expect($adapter->fetchDispute('dp_1', $account)->status)->toBe('open')
            ->and($adapter->submitEvidence(new SubmitDisputeEvidenceCommand($account, 'dp_1', ['receipt' => 'ok']))->status)->toBe('under_review')
            ->and($adapter->listSettlementTransactions(new SettlementQueryDto($account)))->toBe([]);

        if ($hasTerminal) {
            $term = $adapter->createTerminalPayment(new CreateTerminalPaymentCommand($account, 'rdr_1', $amount, 'k9'));
            expect($term->readerId)->toBe('rdr_1')->and($term->state)->toBe(PaymentLifecycleState::Processing);
        }
    }

    $stripe = new StripePaymentAdapter();
    $txn = new SettlementTransactionDto('stripe', 'txn_1', 'pi_1', $amount, new MoneyAmount(150, 'USD'), new MoneyAmount(4850, 'USD'), 'charge', '2026-11-22T00:00:00+00:00');
    expect($stripe->providerKey())->toBe('stripe')
        ->and($stripe->descriptor()->displayName)->toBe('Stripe')
        ->and($txn->netAmount->amountMinor)->toBe(4850);
});
