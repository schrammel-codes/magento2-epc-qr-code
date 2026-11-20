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
use SchrammelCodes\EpcQrCode\Model\Config\Backend\HexColor;

/**
 * @package SchrammelCodes\EpcQrCode\Test\Unit\Model\Config\Backend
 */
class HexColorTest extends TestCase
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
            'Uppercase color' => ['value' => '#1A2B3C', 'expected' => '#1A2B3C'],
            'Lowercase color with whitespaces' => ['value' => ' #1a2b3c ', 'expected' => '#1A2B3C'],
            'Missing hash' => ['value' => '1A2B3C', 'expected' => '#1A2B3C'],
            'Lowercase color with missing hash' => ['value' => '1a2b3c', 'expected' => '#1A2B3C'],
        ];
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testBeforeSaveThrowsExceptionOnInvalidValues(string $value): void
    {
        $backendModel = $this->createBackendModel($value);

        $this->expectException(EpcQrCodeException::class);
        $this->expectExceptionMessage(
            sprintf('Invalid color "%s". Use the HEX format #RRGGBB or leave the field empty.', trim($value))
        );

        $backendModel->beforeSave();
    }

    public static function invalidValueDataProvider(): array
    {
        return [
            'Short notation' => ['value' => '#FFF'],
            'Short notation with missing hash' => ['value' => 'FFF'],
            'Double hash' => ['value' => '##1A2B3C'],
            'Too long' => ['value' => '#1A2B3C4D'],
            'Invalid characters' => ['value' => '#GGHHII'],
            'Color name' => ['value' => 'black'],
        ];
    }

    private function createBackendModel(?string $value): HexColor
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($this->createMock(ManagerInterface::class));

        return new HexColor(
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
