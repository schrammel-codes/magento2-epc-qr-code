<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;
use SchrammelCodes\EpcQrCode\Model\Config\Backend\Iban;
use SchrammelCodes\EpcQrCode\Model\IbanNormalizer;

/**
 * @package SchrammelCodes\EpcQrCode\Test\Unit\Model\Config\Backend
 */
class IbanTest extends TestCase
{
    #[DataProvider('validValueDataProvider')]
    public function testBeforeSaveNormalizesValidValues(?string $value, string $expected): void
    {
        $backendModel = $this->createBackendModel($value);

        $backendModel->beforeSave();

        $this->assertSame($expected, $backendModel->getValue());
    }

    public static function validValueDataProvider(): array
    {
        return [
            'Not set' => ['value' => null, 'expected' => ''],
            'Empty' => ['value' => '', 'expected' => ''],
            'Whitespace only' => ['value' => '   ', 'expected' => ''],
            'Valid IBAN' => ['value' => 'AT611904300234573201', 'expected' => 'AT611904300234573201'],
            'Valid IBAN with whitespaces and lowercase' => [
                'value' => ' at61 1904 3002 3457 3201 ',
                'expected' => 'AT611904300234573201',
            ],
        ];
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testBeforeSaveThrowsExceptionOnInvalidValues(string $value, string $messageRegex): void
    {
        $backendModel = $this->createBackendModel($value);

        $this->expectException(EpcQrCodeException::class);
        $this->expectExceptionMessageMatches($messageRegex);

        $backendModel->beforeSave();
    }

    public static function invalidValueDataProvider(): array
    {
        return [
            'Invalid country code' => [
                'value' => 'XX611904300234573201',
                'messageRegex' => '/IBAN country code "XX" is invalid./',
            ],
            'Invalid length' => [
                'value' => 'AT6119043002345732',
                'messageRegex' => '/Invalid IBAN length \(18\)/',
            ],
        ];
    }

    private function createBackendModel(?string $value): Iban
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($this->createMock(ManagerInterface::class));

        return new Iban(
            new IbanNormalizer(),
            $context,
            $this->createMock(Registry::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(TypeListInterface::class),
            null,
            null,
            ['value' => $value]
        );
    }
}
