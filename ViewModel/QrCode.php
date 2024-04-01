<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\ViewModel;

use Exception;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use SchrammelCodes\EpcQrCode\Model\QrCodeRenderer;

/**
 * @package SchrammelCodes\EpcQrCode\ViewModel
 */
class QrCode implements ArgumentInterface
{
    public function __construct(
        private readonly QrCodeRenderer $renderer,
        private readonly OrderFactory $orderFactory
    ) {
    }

    /**
     * Render the QR code image tag for the given order increment ID.
     *
     * @param string $incrementId
     * @return string|null
     */
    public function renderQrCodeImageTagFromOrderIncrementId(string $incrementId): ?string
    {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($incrementId);

        return $this->renderQrCodeImageTagFromOrder($order);
    }

    /**
     * Render the QR code image tag for the given order.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    public function renderQrCodeImageTagFromOrder(OrderInterface $order): ?string
    {
        if ($order->getPayment()->getMethod() !== Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE ||
            !$this->renderer->canRender($order)
        ) {
            return null;
        }

        return $this->renderer->renderQrCodeImageTag($order);
    }
}
