<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Test\Unit\Model;

use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;
use SchrammelCodes\EpcQrCode\Model\IbanNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @package SchrammelCodes\EpcQrCode\Test\Unit\Model
 */
class IbanNormalizerTest extends TestCase
{
    /**
     * @param string $iban
     * @param string $expected
     * @return void
     * @throws EpcQrCodeException
     * @dataProvider successfulValidationDataProvider
     */
    public function testNormalizeOnSuccessfulValidation(string $iban, string $expected): void
    {
        $ibanNormalizer = new IbanNormalizer();

        $this->assertEquals($expected, $ibanNormalizer->normalize($iban));
    }

    private function successfulValidationDataProvider(): array
    {
        return [
            ['AT123456789012345678', 'AT123456789012345678'],
            ['AT12 3456 7890 1234 5678', 'AT123456789012345678'],
            ['at123456789012345678', 'AT123456789012345678'],
            ['At12 3456 7890 1234 5678', 'AT123456789012345678'],
            ['DE12345678901234567890', 'DE12345678901234567890'],
            ['DE1234 5678 9012 3456 7890', 'DE12345678901234567890'],
            ['de12345678901234567890', 'DE12345678901234567890'],
            ['De1234 5678 9012 3456 7890', 'DE12345678901234567890'],
            ['DJ1234 5678 9012 3456 7890 1234 5', 'DJ1234567890123456789012345'],
            ['RU1234 5678 9012 3456 7890 1234 5678 901', 'RU1234567890123456789012345678901'],
        ];
    }

    /**
     * @param string $iban
     * @param string $messageRegex
     * @return void
     * @throws EpcQrCodeException
     * @dataProvider expectedExceptionDataProvider
     */
    public function testExceptionOnInvalidIban(string $iban, string $messageRegex): void
    {
        $ibanNormalizer = new IbanNormalizer();

        $this->expectException(EpcQrCodeException::class);
        $this->expectExceptionMessageMatches($messageRegex);

        $ibanNormalizer->normalize($iban);
    }

    private function expectedExceptionDataProvider(): array
    {
        return [
            ['AA12345678901234567890', '/IBAN country code "[A-Z]{2}" is invalid./'],
            ['XX12345678901234567890', '/IBAN country code "[A-Z]{2}" is invalid./'],
            ['AT12345678901234567890', '/Invalid IBAN length \(\d{2}\). IBAN for [A-Z]{2} has to be \d{2} characters long./'],
            ['DE123456789012345678', '/Invalid IBAN length \(\d{2}\). IBAN for [A-Z]{2} has to be \d{2} characters long./']
        ];
    }
}
