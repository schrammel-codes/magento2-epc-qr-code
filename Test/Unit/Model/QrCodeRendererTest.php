<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SchrammelCodes\EpcQrCode\Model\Config\Reader;
use SchrammelCodes\EpcQrCode\Model\QrCodeRenderer;

/**
 * @package SchrammelCodes\EpcQrCode\Test\Unit\Model
 */
class QrCodeRendererTest extends TestCase
{
    /**
     * @test
     * @param bool $isEnabled
     * @param string|null $accountHolder
     * @param string|null $iban
     * @param string|null $bic
     * @param string|null $referenceType
     * @param string|null $paymentReference
     * @param string|null $customerHint
     * @param int $charEncoding
     * @param float $orderValue
     * @param bool $expected
     * @return void
     * @dataProvider canRenderDataProvider
     */
    public function canRender(
        bool $isEnabled,
        ?string $accountHolder,
        ?string $iban,
        ?string $bic,
        ?string $referenceType,
        ?string $paymentReference,
        ?string $customerHint,
        int $charEncoding,
        float $orderValue,
        bool $expected
    ): void {
        $configurationReaderMock = $this->getConfigurationReaderMock(
            $isEnabled,
            $accountHolder,
            $iban,
            $bic,
            $referenceType,
            $paymentReference,
            $customerHint,
            $charEncoding
        );
        $loggerMock = $this->createMock(LoggerInterface::class);

        $renderer = new QrCodeRenderer($configurationReaderMock, $loggerMock);

        $orderMock = $this->getOrderMock($orderValue);

        $this->assertEquals($expected, $renderer->canRender($orderMock));
    }

    private function canRenderDataProvider(): array
    {
        return [
            'Generation disabled (cannot render)' => [
                'isEnabled' => false,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => false,
            ],
            'Generation enabled with creditor reference (can render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => true,
            ],
            'Generation enabled with payment reference (can render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'PR',
                'paymentReference' => '100000001',
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => true,
            ],
            'Missing account holder (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => null,
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => false,
            ],
            'Missing IBAN (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => null,
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => false,
            ],
            'Missing BIC (can render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => null,
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => true,
            ],
            'Invalid reference type (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'XX',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => false,
            ],
            'Missing payment reference (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'PR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => false,
            ],
            'Missing customer hint (can render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => null,
                'charEncoding' => 1,
                'orderValue' => 100.00,
                'expected' => true,
            ],
            'Character encoding out of bounds (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 10,
                'orderValue' => 100.00,
                'expected' => false,
            ],
            'Order value below threshold (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 0.00,
                'expected' => false,
            ],
            'Order value above threshold (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'CR',
                'paymentReference' => null,
                'customerHint' => 'Order #12345',
                'charEncoding' => 1,
                'orderValue' => 1000000000.00,
                'expected' => false,
            ],
        ];
    }

    private function getConfigurationReaderMock(
        bool $isEnabled,
        ?string $accountHolder,
        ?string $iban,
        ?string $bic,
        ?string $referenceType,
        ?string $reference,
        ?string $customerHint,
        int $charEncoding
    ): Reader {
        $configReader = $this->createMock(Reader::class);
        $configReader->method('isEpcQrCodeEnabled')->willReturn($isEnabled);
        $configReader->method('getBeneficiaryName')->willReturn($accountHolder);
        $configReader->method('getIban')->willReturn($iban);
        $configReader->method('getBic')->willReturn($bic);
        $configReader->method('getReferenceType')->willReturn($referenceType);
        $configReader->method('getPaymentReference')->willReturn($reference);
        $configReader->method('getCustomerHint')->willReturn($customerHint);
        ;
        $configReader->method('getCharEncoding')->willReturn($charEncoding);
        $configReader->method('getServiceTag')->willReturn('BCD');
        $configReader->method('getVersion')->willReturn(002);
        $configReader->method('getIdentification')->willReturn('SCT');

        return $configReader;
    }

    private function getOrderMock(float $orderValue): OrderInterface
    {
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->method('getStoreId')->willReturn(1);
        $orderMock->method('getGrandTotal')->willReturn($orderValue);
        $orderMock->method('getIncrementId')->willReturn('100000001');

        return $orderMock;
    }
}
