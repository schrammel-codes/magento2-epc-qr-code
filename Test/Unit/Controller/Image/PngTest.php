<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Controller\Image;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use SchrammelCodes\EpcQrCode\Controller\Image\Png;
use SchrammelCodes\EpcQrCode\Model\Config\Reader as ConfigReader;
use SchrammelCodes\EpcQrCode\Model\QrCodeRenderer;
use SchrammelCodes\EpcQrCode\Model\UrlHasher;

class PngTest extends TestCase
{
    private ConfigReader $configReader;
    private RequestInterface $request;
    private OrderRepositoryInterface $orderRepository;
    private QrCodeRenderer $qrCodeRenderer;
    private RawFactory $resultFactory;
    private UrlHasher $urlHasher;
    private Raw $result;
    private Png $controller;

    protected function setUp(): void
    {
        $this->configReader = $this->createMock(ConfigReader::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->qrCodeRenderer = $this->createMock(QrCodeRenderer::class);
        $this->resultFactory = $this->createMock(RawFactory::class);
        $this->urlHasher = $this->createMock(UrlHasher::class);
        $this->result = $this->createMock(Raw::class);

        $this->resultFactory->method('create')->willReturn($this->result);

        $this->controller = new Png(
            $this->configReader,
            $this->request,
            $this->orderRepository,
            $this->qrCodeRenderer,
            $this->resultFactory,
            $this->urlHasher
        );
    }

    public function testExecuteReturns403WhenBase64Enabled(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(true);

        $this->result->expects($this->once())->method('setHttpResponseCode')->with(403);
        $this->result->expects($this->once())->method('setContents')->with('403 Forbidden');

        $this->assertSame($this->result, $this->controller->execute());
    }

    public function testExecuteThrowsNotFoundWhenOrderIdIsNonNumeric(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(false);
        $this->request->method('getParam')->willReturnMap([['order_id', null, 'abc'], ['hash', null, '']]);

        $this->expectException(NotFoundException::class);

        $this->controller->execute();
    }

    public function testExecuteThrowsNotFoundWhenOrderDoesNotExist(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(false);
        $this->request->method('getParam')->willReturnMap([['order_id', null, '999'], ['hash', null, '']]);
        $this->orderRepository->method('get')->willThrowException(new NoSuchEntityException());

        $this->expectException(NotFoundException::class);

        $this->controller->execute();
    }

    public function testExecuteThrowsNotFoundWhenHashIsInvalid(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(false);
        $this->request->method('getParam')->willReturnMap([['order_id', null, '1'], ['hash', null, 'wrong']]);

        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('get')->with(1)->willReturn($order);

        $this->urlHasher->method('shouldVerifyHashForOrder')->willReturn(true);
        $this->urlHasher->method('createHashForOrder')->willReturn('correct-hash');

        $this->expectException(NotFoundException::class);

        $this->controller->execute();
    }

    public function testExecuteReturnsNullWhenQrCodeCannotBeRendered(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(false);
        $this->request->method('getParam')->willReturnMap([['order_id', null, '1'], ['hash', null, '']]);

        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('get')->with(1)->willReturn($order);
        $this->urlHasher->method('shouldVerifyHashForOrder')->willReturn(false);
        $this->qrCodeRenderer->method('getRawPngQrCode')->willReturn(null);

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteReturnsPngResultWithImageContents(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(false);
        $this->request->method('getParam')->willReturnMap([['order_id', null, '1'], ['hash', null, 'valid-hash']]);

        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('get')->with(1)->willReturn($order);
        $this->urlHasher->method('shouldVerifyHashForOrder')->willReturn(true);
        $this->urlHasher->method('createHashForOrder')->willReturn('valid-hash');
        $this->qrCodeRenderer->method('getRawPngQrCode')->willReturn('PNG_DATA');

        $this->result->expects($this->once())->method('setHeader')->with('Content-Type', 'image/png');
        $this->result->expects($this->once())->method('setContents')->with('PNG_DATA');

        $this->assertSame($this->result, $this->controller->execute());
    }

    public function testExecuteSkipsHashVerificationForOlderOrders(): void
    {
        $this->configReader->method('isEpcQrCodeImageSrcBase64Encoded')->willReturn(false);
        $this->request->method('getParam')->willReturnMap([['order_id', null, '1'], ['hash', null, '']]);

        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('get')->with(1)->willReturn($order);
        $this->urlHasher->method('shouldVerifyHashForOrder')->willReturn(false);
        $this->qrCodeRenderer->method('getRawPngQrCode')->willReturn('PNG_DATA');

        $this->result->expects($this->once())->method('setHeader')->with('Content-Type', 'image/png');
        $this->result->expects($this->once())->method('setContents')->with('PNG_DATA');

        $this->assertSame($this->result, $this->controller->execute());
    }
}
