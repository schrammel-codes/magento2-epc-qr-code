<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use SchrammelCodes\EpcQrCode\Model\IbanNormalizer;

/**
 * @package SchrammelCodes\EpcQrCode\Exception
 * phpcs:ignore Magento2.Functions.StaticFunction
 */
class EpcQrCodeException extends LocalizedException
{
    public const INVALID_IBAN_COUNTRY = 'IBAN_COUNTRY';
    public const INVALID_IBAN_LENGTH = 'IBAN_LENGTH';

    /**
     * Throw a configuration exception with given message.
     *
     * @param Phrase $message
     * @return void
     * @throws EpcQrCodeException
     */
    public static function configException(Phrase $message): void
    {
        throw new EpcQrCodeException($message);
    }

    /**
     * Throw an exception for an invalid IBAN based on given reason and IBAN.
     *
     * @param string $reason
     * @param string $iban
     * @throws EpcQrCodeException
     */
    public static function invalidIbanException(string $reason, string $iban): void
    {
        $countryCode = substr($iban, 0, 2);
        $reasonText = match ($reason) {
            self::INVALID_IBAN_COUNTRY => __('IBAN country code "%1" is invalid.', $countryCode),
            self::INVALID_IBAN_LENGTH => __(
                'Invalid IBAN length (%1). IBAN for %2 has to be %3 characters long.',
                strlen($iban),
                $countryCode,
                IbanNormalizer::IBAN_COUNTRY_CODE_LENGTH[$countryCode] ?? 'unknown'
            )
        };

        throw new EpcQrCodeException($reasonText);
    }
}
