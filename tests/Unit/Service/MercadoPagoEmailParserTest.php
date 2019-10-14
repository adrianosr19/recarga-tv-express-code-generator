<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Unit\Service;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Service\MercadoPagoEmailParser;
use PhpImap\IncomingMail;
use PHPUnit\Framework\TestCase;

class MercadoPagoEmailParserTest extends TestCase
{
    /**
     * @dataProvider emails
     * @param string $emailBody
     */
    public function testShouldReturnASalesArrayOnSuccessCase(string $emailBody)
    {
        // arrange
        $emailSubject = 'Você recebeu um pagamento por TVE anual';
        $parser = new MercadoPagoEmailParser();

        $incomingMailMock = $this->createStub(IncomingMail::class);
        $incomingMailMock->subject = $emailSubject;
        $incomingMailMock->fromAddress = 'info@mercadopago.com';
        $incomingMailMock->method('__get')
            ->willReturn($emailBody);

        // act
        $sale = $parser->parse($incomingMailMock);

        // assert
        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertSame('anual', $sale->product);
        $this->assertEquals('email@example.com', $sale->costumerEmail);
    }

    public function testShouldRaiseErrorWhenTryingToParseUnsupportedEmail()
    {
        $this->expectException(\Error::class);

        // arrange
        $parser = new MercadoPagoEmailParser();
        $incomingMailMock = $this->createStub(IncomingMail::class);
        $incomingMailMock->subject = 'Você recebeu um pagamento por Combo MFC + TVE anual';
        $incomingMailMock->fromAddress = 'info@mercadopago.com';
        $incomingMailMock->method('__get')
            ->willReturn('');

        // act
        $parser->parse($incomingMailMock);
    }

    public function emails(): array
    {
        $dataDir = __DIR__ . '/../../data';

        return [
            'Without phone' => [file_get_contents("$dataDir/email-without-phone.html")],
            'With phone' => [file_get_contents("$dataDir/email-without-phone.html")],
            'Without name' => [file_get_contents("$dataDir/email-without-name.html")],
            'With two credit cards' => [file_get_contents("$dataDir/email-with-two-credit-cards.html")],
        ];
    }
}