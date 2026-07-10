<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;
use SchrammelCodes\EpcQrCode\Model\Config\Reader;
use SchrammelCodes\EpcQrCode\Model\IbanNormalizer;

class ReaderTest extends TestCase
{
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;
    private IbanNormalizer $ibanNormalizer;
    private Reader $reader;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->ibanNormalizer = $this->createMock(IbanNormalizer::class);
        $this->reader = new Reader($this->scopeConfig, $this->storeManager, $this->ibanNormalizer);
    }

    public function testGetServiceTagReturnsBcd(): void
    {
        $this->assertSame('BCD', $this->reader->getServiceTag());
    }

    public function testGetVersionReturnsTwo(): void
    {
        $this->assertSame(2, $this->reader->getVersion());
    }

    public function testGetIdentificationReturnsSct(): void
    {
        $this->assertSame('SCT', $this->reader->getIdentification());
    }

    public function testGetIbanReturnsNormalizedIban(): void
    {
        $this->scopeConfig->method('getValue')->willReturn('at61 1904 3002 3457 3201');
        $this->ibanNormalizer->method('normalize')->willReturn('AT611904300234573201');

        $this->assertSame('AT611904300234573201', $this->reader->getIban(1));
    }

    public function testGetIbanThrowsWhenIbanIsEmpty(): void
    {
        $this->scopeConfig->method('getValue')->willReturn(null);

        $this->expectException(EpcQrCodeException::class);

        $this->reader->getIban(1);
    }
}
