<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Plugin\Payment\Block\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\Payment\Block\Info\Instructions;
use SchrammelCodes\EpcQrCode\Model\QrCodeRenderer;

/**
 * @package SchrammelCodes\EpcQrCode\Plugin\Payment\Block\Info
 */
class InstructionsPlugin
{
    public function __construct(private readonly QrCodeRenderer $qrCodeRenderer)
    {
    }

    /**
     * @param Instructions $subject
     * @param string $result
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(Instructions $subject, string $result): string
    {
        $info = $subject->getInfo();
        $order = $info->getOrder();

        if ($info->getMethod() !== Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE ||
            !$this->qrCodeRenderer->canRender($order)
        ) {
            return $result;
        }

        $qrCode = $this->qrCodeRenderer->renderQrCodeImageTag($info->getOrder());
        if ($qrCode !== null) {
            return $result . PHP_EOL . $qrCode;
        }

        return $result;
    }
}
