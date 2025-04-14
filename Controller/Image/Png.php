<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Controller\Image;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use SchrammelCodes\EpcQrCode\Model\Config\Reader as ConfigReader;
use SchrammelCodes\EpcQrCode\Model\QrCodeRenderer;

class Png implements HttpGetActionInterface
{
    public function __construct(
        private readonly ConfigReader $configReader,
        private readonly RequestInterface $request,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly QrCodeRenderer $qrCodeRenderer,
        private readonly RawFactory $resultFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): ?ResultInterface
    {
        $result = $this->resultFactory->create();
        if ($this->configReader->isEpcQrCodeImageSrcBase64Encoded()) {
            $result->setHttpResponseCode(403);
            $result->setHeader('Content-Type', 'text/plain');
            $result->setContents('403 Forbidden');

            return $result;
        }

        $orderId = $this->request->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $qrCode = $this->qrCodeRenderer->getRawPngQrCode($order);

        if (!$qrCode) {
            return null;
        }

        $result->setHeader('Content-Type', 'image/png');
        $result->setContents($qrCode);

        return $result;
    }
}
