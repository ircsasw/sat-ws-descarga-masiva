<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Authenticate;

use PhpCfdi\SatWsDescargaMasiva\Services\Authenticate\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Tests\EnvelopSignatureVerifier;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class AuthenticateTranslatorTest extends TestCase
{
    public function testCreateSoapRequest(): void
    {
        $translator = new AuthenticateTranslator();
        $fiel = $this->createFielUsingTestingFiles();

        $since = new DateTime('2019-08-01T03:38:19Z');
        $until = new DateTime('2019-08-01T03:43:19Z');
        $uuid = 'uuid-cf6c80fb-00ae-44c0-af56-54ec65decbaa-1';
        $requestBody = $translator->createSoapRequestWithData($fiel, $since, $until, $uuid);
        $this->assertSame(
            $this->xmlFormat($translator->nospaces($this->fileContents('authenticate/request.xml'))),
            $this->xmlFormat($requestBody)
        );

        $xmlSecVerification = (new EnvelopSignatureVerifier())->verify(
            $requestBody,
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            ['http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'],
            $fiel->getCertificatePemContents()
        );
        $this->assertTrue($xmlSecVerification, 'The signature cannot be verified using XMLSecLibs');
    }

    public function testCreateTokenFromSoapResponseWithToken(): void
    {
        $expectedCreated = new DateTime('2019-08-01T03:38:20.044Z');
        $expectedExpires = new DateTime('2019-08-01T03:43:20.044Z');

        $translator = new AuthenticateTranslator();
        $responseBody = $translator->nospaces($this->fileContents('authenticate/response-with-token.xml'));
        $token = $translator->createTokenFromSoapResponse($responseBody);
        $this->assertFalse($token->isValueEmpty());
        $this->assertTrue($token->isExpired());
        $this->assertTrue($token->getCreated()->equalsTo($expectedCreated));
        $this->assertTrue($token->getExpires()->equalsTo($expectedExpires));
        $this->assertFalse($token->isValid());
    }

    public function testCreateTokenFromSoapResponseWithError(): void
    {
        $translator = new AuthenticateTranslator();
        $responseBody = $translator->nospaces($this->fileContents('authenticate/response-with-error.xml'));
        $token = $translator->createTokenFromSoapResponse($responseBody);
        $this->assertTrue($token->isValueEmpty());
        $this->assertTrue($token->isExpired());
        $this->assertFalse($token->isValid());
        $this->assertSame('', $token->getValue());
    }
}
