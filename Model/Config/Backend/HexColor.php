<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;

/**
 * @package SchrammelCodes\EpcQrCode\Model\Config\Backend
 */
class HexColor extends Value
{
    private const HEX_COLOR_PATTERN = '/^#[0-9A-F]{6}$/';

    /**
     * Validate and normalize the HEX color before saving.
     *
     * An empty value is allowed, the QR code is then rendered with the default color.
     *
     * @throws EpcQrCodeException
     */
    public function beforeSave(): void
    {
        $value = trim((string)$this->getValue());
        $color = strtoupper($value);
        if ($color !== '' && !preg_match(self::HEX_COLOR_PATTERN, $color)) {
            EpcQrCodeException::configException(
                __('Invalid color "%1". Use the HEX format #RRGGBB or leave the field empty.', $value)
            );
        }
        $this->setValue($color);

        parent::beforeSave();
    }
}
