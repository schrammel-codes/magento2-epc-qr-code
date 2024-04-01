<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @package SchrammelCodes\EpcQrCode\Model\Config\Source
 */
class CharacterEncoding implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => 'UTF-8'],
            ['value' => 2, 'label' => 'ISO 8859-1'],
            ['value' => 3, 'label' => 'ISO 8859-2'],
            ['value' => 4, 'label' => 'ISO 8859-4'],
            ['value' => 5, 'label' => 'ISO 8859-5'],
            ['value' => 6, 'label' => 'ISO 8859-7'],
            ['value' => 7, 'label' => 'ISO 8859-10'],
            ['value' => 8, 'label' => 'ISO 8859-15'],
        ];
    }
}
