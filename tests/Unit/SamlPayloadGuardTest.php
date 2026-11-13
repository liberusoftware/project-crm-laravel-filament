<?php

use App\Services\Sso\SamlPayloadGuard;
use OneLogin\Saml2\Utils;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

function guardedSamlXml(string $algorithm = XMLSecurityDSig::EXC_C14N, string $uri = '#assertion'): string
{
    return '<Response xmlns:ds="'.XMLSecurityDSig::XMLDSIGNS.'"><ds:Signature><ds:SignedInfo><ds:Reference URI="'.$uri.'"><ds:Transforms><ds:Transform Algorithm="'.$algorithm.'"/></ds:Transforms></ds:Reference></ds:SignedInfo></ds:Signature></Response>';
}

it('accepts bounded standard SAML transforms for subsequent signature verification', function (string $algorithm) {
    expect(SamlPayloadGuard::allows(base64_encode(guardedSamlXml($algorithm))))->toBeTrue();
})->with([
    'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
    XMLSecurityDSig::C14N,
    XMLSecurityDSig::C14N_COMMENTS,
    XMLSecurityDSig::EXC_C14N,
    XMLSecurityDSig::EXC_C14N_COMMENTS,
]);

it('rejects executable XPath and XSLT signature transforms before verification', function (string $algorithm) {
    expect(SamlPayloadGuard::allows(base64_encode(guardedSamlXml($algorithm))))->toBeFalse();
})->with([
    'http://www.w3.org/TR/1999/REC-xpath-19991116',
    'http://www.w3.org/2002/06/xmldsig-filter2',
    'http://www.w3.org/TR/1999/REC-xslt-19991116',
    'urn:unsupported-transform',
]);

it('rejects malformed, oversized, and entity-bearing SAML input', function () {
    foreach ([null, [], false, '', '%%%invalid', base64_encode('<broken'), base64_encode("\0"), base64_encode('<!DOCTYPE Response [<!ENTITY value "test">]><Response/>'), str_repeat('A', 1_048_577)] as $payload) {
        expect(SamlPayloadGuard::allows($payload))->toBeFalse();
    }
});

it('rejects external and empty XML signature references', function (string $uri) {
    expect(SamlPayloadGuard::allows(base64_encode(guardedSamlXml(uri: $uri))))->toBeFalse();
})->with(['https://outside.example.invalid/assertion', '', '#']);

it('bounds redirect decompression and accepts normal redirect messages', function () {
    $xml = guardedSamlXml();
    expect(SamlPayloadGuard::allows(base64_encode(gzdeflate($xml)), deflated: true))->toBeTrue()
        ->and(SamlPayloadGuard::allows(base64_encode($xml), deflated: true))->toBeTrue()
        ->and(SamlPayloadGuard::allows(base64_encode(gzdeflate('<Response>'.str_repeat('x', 2_097_152).'</Response>')), deflated: true))->toBeFalse();
});

it('bounds signature and transform work before calling the XML security library', function () {
    $signature = '<ds:Signature><ds:SignedInfo><ds:Reference URI="#a"/></ds:SignedInfo></ds:Signature>';
    $transform = '<ds:Transform Algorithm="'.XMLSecurityDSig::EXC_C14N.'"/>';
    expect(SamlPayloadGuard::allows(base64_encode('<Response xmlns:ds="'.XMLSecurityDSig::XMLDSIGNS.'">'.str_repeat($signature, 3).'</Response>')))->toBeFalse()
        ->and(SamlPayloadGuard::allows(base64_encode('<Response xmlns:ds="'.XMLSecurityDSig::XMLDSIGNS.'">'.str_repeat('<ds:Reference URI="#a"/>', 3).'</Response>')))->toBeFalse()
        ->and(SamlPayloadGuard::allows(base64_encode('<Response xmlns:ds="'.XMLSecurityDSig::XMLDSIGNS.'">'.str_repeat($transform, 7).'</Response>')))->toBeFalse();
});

it('keeps certificate-based SAML signatures separate from HMAC secrets', function () {
    expect(Utils::isSupportedSigningAlgorithm(XMLSecurityKey::HMAC_SHA1))->toBeFalse()
        ->and(Utils::isSupportedSigningAlgorithm(XMLSecurityKey::RSA_SHA256))->toBeTrue();
});
