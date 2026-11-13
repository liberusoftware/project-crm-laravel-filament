<?php

use App\Models\SamlConnection;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Saml\SignsSamlResponses;

uses(SignsSamlResponses::class);

beforeEach(function () {
    $this->idpKeypair();
    $this->samlTeam = Team::factory()->create();
    SamlConnection::factory()->create([
        'team_id' => $this->samlTeam->id,
        'idp_entity_id' => 'https://idp.example.com/entity',
        'idp_sso_url' => 'https://idp.example.com/sso',
        'idp_x509_cert' => $this->idpCertPem,
        'enabled' => true,
    ]);
    $this->samlMember = User::factory()->create(['email' => 'member@corp.example']);
    $this->samlTeam->users()->attach($this->samlMember);
});

it('rejects a signed assertion without a matching browser and team request', function (string $scenario) {
    $requestId = '_req'.bin2hex(random_bytes(8));
    $response = $this->buildSignedResponse($this->samlTeam, $this->samlMember->email, $requestId);
    $state = match ($scenario) {
        'missing-request' => [],
        'missing-team' => ['saml_request_id' => $requestId],
        'wrong-team' => ['saml_request_id' => $requestId, 'saml_team' => $this->samlTeam->id + 1],
    };

    $this->withSession($state)->post('/saml/'.$this->samlTeam->id.'/acs', ['SAMLResponse' => $response])->assertForbidden();
    $this->assertGuest();
})->with(['missing-request', 'missing-team', 'wrong-team']);

it('accepts the initiated browser flow once and rejects a replay after logout', function () {
    $this->get('/saml/'.$this->samlTeam->id.'/login')->assertRedirect();
    $requestId = session('saml_request_id');
    expect($requestId)->toBeString()->not->toBeEmpty()
        ->and(session('saml_team'))->toBe($this->samlTeam->id);
    $response = $this->buildSignedResponse($this->samlTeam, $this->samlMember->email, $requestId);

    $this->post('/saml/'.$this->samlTeam->id.'/acs', ['SAMLResponse' => $response])->assertRedirect('/app');
    $this->assertAuthenticatedAs($this->samlMember->fresh());
    expect(session('saml_request_id'))->toBeNull()->and(session('saml_team'))->toBeNull();
    Auth::logout();
    $this->app['auth']->forgetGuards();

    $this->post('/saml/'.$this->samlTeam->id.'/acs', ['SAMLResponse' => $response])->assertForbidden();
    $this->assertGuest();
});
