<?php

use App\Models\Team;
use App\Services\StripeService;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

it('updates Stripe payment details with the existing API contract after an SDK upgrade', function () {
    config(['services.stripe.secret' => 'sk_test_compatibility', 'services.stripe.api_version' => '2026-08-26.dahlia']);
    $previousClient = ApiRequestor::httpClient();
    $http = Mockery::mock(ClientInterface::class);
    $headers = fn (array $headers): bool => in_array('Stripe-Version: 2026-08-26.dahlia', $headers, true);
    $http->shouldReceive('request')->once()->with('post', 'https://api.stripe.com/v1/payment_methods/pm_test/attach', Mockery::on($headers), ['customer' => 'cus_test'], false, 'v1', Mockery::any())
        ->andReturn(['{"id":"pm_test","object":"payment_method"}', 200, []]);
    $http->shouldReceive('request')->once()->with('post', 'https://api.stripe.com/v1/customers/cus_test', Mockery::on($headers), ['invoice_settings' => ['default_payment_method' => 'pm_test']], false, 'v1', Mockery::any())
        ->andReturn(['{"id":"cus_test","object":"customer"}', 200, []]);
    ApiRequestor::setHttpClient($http);

    try {
        $team = new Team()->forceFill(['stripe_id' => 'cus_test']);
        new StripeService()->updatePaymentMethod($team, 'pm_test');
        $http->shouldHaveReceived('request')->twice();
    } finally {
        ApiRequestor::setHttpClient($previousClient);
    }
});
