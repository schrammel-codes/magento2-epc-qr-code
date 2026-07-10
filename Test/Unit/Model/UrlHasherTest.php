<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Model;

use Magento\Framework\FlagManager;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SchrammelCodes\EpcQrCode\Model\UrlHasher;

class UrlHasherTest extends TestCase
{
    private FlagManager $flagManager;
    private LoggerInterface $logger;
    private UrlHasher $urlHasher;

    protected function setUp(): void
    {
        $this->flagManager = $this->createMock(FlagManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->urlHasher = new UrlHasher($this->flagManager, $this->logger);
    }

    public function testCreateHashForOrderReturnsSha256OfOrderData(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getIncrementId')->willReturn('100000001');
        $order->method('getCustomerEmail')->willReturn('test@example.com');
        $order->method('getCreatedAt')->willReturn('2024-01-15 10:00:00');

        $expected = hash('sha256', '100000001test@example.com2024-01-15 10:00:00');

        $this->assertSame($expected, $this->urlHasher->createHashForOrder($order));
    }

    public function testCreateHashForOrderIsDeterministic(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getIncrementId')->willReturn('100000002');
        $order->method('getCustomerEmail')->willReturn('other@example.com');
        $order->method('getCreatedAt')->willReturn('2024-06-01 08:30:00');

        $this->assertSame(
            $this->urlHasher->createHashForOrder($order),
            $this->urlHasher->createHashForOrder($order)
        );
    }

    public function testShouldVerifyHashReturnsFalseAndLogsWhenUpgradeTimestampMissing(): void
    {
        $this->flagManager->method('getFlagData')->willReturn(null);

        $order = $this->createOrderMock('2024-01-15 10:00:00');

        $this->logger->expects($this->once())->method('warning');

        $this->assertFalse($this->urlHasher->shouldVerifyHashForOrder($order));
    }

    public function testShouldVerifyHashReturnsFalseAndLogsWhenOrderCreatedAtIsInvalid(): void
    {
        $this->flagManager->method('getFlagData')->willReturn(1700000000);

        $order = $this->createOrderMock('not-a-date');

        $this->logger->expects($this->once())->method('warning');

        $this->assertFalse($this->urlHasher->shouldVerifyHashForOrder($order));
    }

    public function testShouldVerifyHashReturnsTrueWhenOrderCreatedAfterUpgrade(): void
    {
        $upgradeTimestamp = strtotime('2024-01-01 00:00:00');
        $this->flagManager->method('getFlagData')->willReturn($upgradeTimestamp);

        $order = $this->createOrderMock('2024-06-01 12:00:00');

        $this->logger->expects($this->never())->method('warning');

        $this->assertTrue($this->urlHasher->shouldVerifyHashForOrder($order));
    }

    public function testShouldVerifyHashReturnsFalseWhenOrderCreatedBeforeUpgrade(): void
    {
        $upgradeTimestamp = strtotime('2024-06-01 00:00:00');
        $this->flagManager->method('getFlagData')->willReturn($upgradeTimestamp);

        $order = $this->createOrderMock('2024-01-01 12:00:00');

        $this->logger->expects($this->never())->method('warning');

        $this->assertFalse($this->urlHasher->shouldVerifyHashForOrder($order));
    }

    private function createOrderMock(string $createdAt): OrderInterface
    {
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->method('getCreatedAt')->willReturn($createdAt);

        return $order;
    }
}
