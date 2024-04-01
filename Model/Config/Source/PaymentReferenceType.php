<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @package SchrammelCodes\EpcQrCode\Model\Config\Source
 */
class PaymentReferenceType implements OptionSourceInterface
{
    public const TYPE_CREDITOR_REFERENCE = 'CR';
    public const TYPE_PAYMENT_REFERENCE = 'PR';
    public const ALLOWED_PAYMENT_REFERENCE_TYPES = [self::TYPE_CREDITOR_REFERENCE, self::TYPE_PAYMENT_REFERENCE];

    /**
     * @inheriDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TYPE_PAYMENT_REFERENCE,
                'label' => __('Payment Reference - free text (max. 140 characters)')
            ],
            [
                'value' => self::TYPE_CREDITOR_REFERENCE,
                'label' => __('Creditor Reference - order increment ID will be used (max. 35 characters)')
            ],
        ];
    }
}
