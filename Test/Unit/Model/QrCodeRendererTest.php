<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SchrammelCodes\EpcQrCode\Model\Config\Reader;
use SchrammelCodes\EpcQrCode\Model\QrCodeRenderer;
use SchrammelCodes\EpcQrCode\Model\UrlHasher;

/**
 * @package SchrammelCodes\EpcQrCode\Test\Unit\Model
 */
class QrCodeRendererTest extends TestCase
{
    #[DataProvider('canRenderDataProvider')]
    public function testCanRender(
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
        $urlMock = $this->createMock(UrlInterface::class);
        $urlHasherMock = $this->createMock(UrlHasher::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $renderer = new QrCodeRenderer($configurationReaderMock, $urlMock, $loggerMock, $urlHasherMock);

        $orderMock = $this->getOrderMock($orderValue);

        $this->assertEquals($expected, $renderer->canRender($orderMock));
    }

    public static function canRenderDataProvider(): array
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
            'Account holder saved empty (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => '',
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
            'Payment reference saved empty (cannot render)' => [
                'isEnabled' => true,
                'accountHolder' => 'John Doe',
                'iban' => 'AT611904300234573201',
                'bic' => 'ABCDATWW',
                'referenceType' => 'PR',
                'paymentReference' => '',
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

    public function testCanRenderReturnsFalseAndLogsWhenNoSuchEntityExceptionIsThrown(): void
    {
        $configReader = $this->createMock(Reader::class);
        $configReader->method('isEpcQrCodeEnabled')->willThrowException(new NoSuchEntityException());

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('error');

        $renderer = new QrCodeRenderer(
            $configReader,
            $this->createMock(UrlInterface::class),
            $loggerMock,
            $this->createMock(UrlHasher::class)
        );

        $this->assertFalse($renderer->canRender($this->getOrderMock(100.00)));
    }

    #[DataProvider('orderCurrencyDataProvider')]
    public function testCanRenderDependsOnOrderCurrency(?string $currencyCode, bool $expected): void
    {
        $renderer = new QrCodeRenderer(
            $this->getConfigurationReaderMock(
                true,
                'John Doe',
                'AT611904300234573201',
                'ABCDATWW',
                'CR',
                null,
                'Order #12345',
                1
            ),
            $this->createMock(UrlInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(UrlHasher::class)
        );

        $this->assertSame($expected, $renderer->canRender($this->getOrderMock(100.00, $currencyCode)));
    }

    public static function orderCurrencyDataProvider(): array
    {
        return [
            'EUR (can render)' => ['EUR', true],
            'PLN (cannot render)' => ['PLN', false],
            'CHF (cannot render)' => ['CHF', false],
            'USD (cannot render)' => ['USD', false],
            'Missing currency (cannot render)' => [null, false],
        ];
    }

    #[DataProvider('optionalFieldsDataProvider')]
    public function testRendersQrCodeWithEmptyOptionalFields(?string $bic, ?string $customerHint): void
    {
        $configReader = $this->getConfigurationReaderMock(
            true,
            'John Doe',
            'AT611904300234573201',
            $bic,
            'CR',
            null,
            $customerHint,
            1
        );

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method('error');

        $renderer = new QrCodeRenderer(
            $configReader,
            $this->createMock(UrlInterface::class),
            $loggerMock,
            $this->createMock(UrlHasher::class)
        );

        $this->assertNotNull($renderer->getRawPngQrCode($this->getOrderMock(100.00)));
    }

    public static function optionalFieldsDataProvider(): array
    {
        return [
            'BIC and customer hint not set' => ['bic' => null, 'customerHint' => null],
            'BIC and customer hint saved empty' => ['bic' => '', 'customerHint' => ''],
            'BIC saved empty, customer hint set' => ['bic' => '', 'customerHint' => 'Thank you'],
            'BIC set, customer hint saved empty' => ['bic' => 'ABCDATWW', 'customerHint' => ''],
        ];
    }

    #[DataProvider('colorDataProvider')]
    public function testQrCodeColors(
        ?string $codeColor,
        ?string $backgroundColor,
        string $expectedCodeColor,
        string $expectedBackgroundColor
    ): void {
        $configReader = $this->getConfigurationReaderMock(
            true,
            'John Doe',
            'AT611904300234573201',
            'ABCDATWW',
            'CR',
            null,
            null,
            1
        );
        $configReader->method('getCodeColor')->willReturn($codeColor);
        $configReader->method('getCodeBeColor')->willReturn($backgroundColor);

        $renderer = new QrCodeRenderer(
            $configReader,
            $this->createMock(UrlInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(UrlHasher::class)
        );

        $png = $renderer->getRawPngQrCode($this->getOrderMock(100.00));
        $this->assertNotNull($png);

        $image = imagecreatefromstring($png);
        $colorAt = static fn (int $position): string => sprintf('#%06X', imagecolorat($image, $position, $position));

        // The image is surrounded by a quiet zone, so the top left corner is always rendered in the background color.
        $this->assertSame($expectedBackgroundColor, $colorAt(0));

        // The code starts with the top left finder pattern, whose corner module is always dark. Walking the diagonal,
        // the first pixel not in the background color therefore has to be rendered in the code color.
        $position = 0;
        while ($position < imagesx($image) - 1 && $colorAt($position) === $expectedBackgroundColor) {
            $position++;
        }
        $this->assertSame($expectedCodeColor, $colorAt($position));
    }

    public static function colorDataProvider(): array
    {
        return [
            'Colors not set (default colors)' => [
                'codeColor' => null,
                'backgroundColor' => null,
                'expectedCodeColor' => '#000000',
                'expectedBackgroundColor' => '#FFFFFF',
            ],
            'Colors saved empty (default colors)' => [
                'codeColor' => '',
                'backgroundColor' => '',
                'expectedCodeColor' => '#000000',
                'expectedBackgroundColor' => '#FFFFFF',
            ],
            'Configured colors' => [
                'codeColor' => '#000080',
                'backgroundColor' => '#FFFF00',
                'expectedCodeColor' => '#000080',
                'expectedBackgroundColor' => '#FFFF00',
            ],
            'Code color configured, background color saved empty' => [
                'codeColor' => '#000080',
                'backgroundColor' => '',
                'expectedCodeColor' => '#000080',
                'expectedBackgroundColor' => '#FFFFFF',
            ],
            'Code color saved empty, background color configured' => [
                'codeColor' => '',
                'backgroundColor' => '#FFFF00',
                'expectedCodeColor' => '#000000',
                'expectedBackgroundColor' => '#FFFF00',
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

    private function getOrderMock(float $orderValue, ?string $currencyCode = 'EUR'): OrderInterface
    {
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->method('getStoreId')->willReturn(1);
        $orderMock->method('getGrandTotal')->willReturn($orderValue);
        $orderMock->method('getOrderCurrencyCode')->willReturn($currencyCode);
        $orderMock->method('getIncrementId')->willReturn('100000001');

        return $orderMock;
    }
}
