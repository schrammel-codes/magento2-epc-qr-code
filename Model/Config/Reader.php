<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;
use SchrammelCodes\EpcQrCode\Model\Config\Source\PaymentReferenceType;
use SchrammelCodes\EpcQrCode\Model\IbanNormalizer;

/**
 * @package SchrammelCodes\EpcQrCode\Model\Config
 */
class Reader
{
    public const CONFIG_BASE_PATH = 'payment/banktransfer/';
    public const CONFIG_FIELD_PREFIX = 'epc_qr_';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly IbanNormalizer $ibanNormalizer
    ) {
    }

    /**
     * Return if EPC QR Code is enabled.
     *
     * @param int|null $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEpcQrCodeEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            $this->getConfigPath('enable'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return name of the beneficiary.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getBeneficiaryName(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('name'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return IBAN.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     * @throws EpcQrCodeException
     */
    public function getIban(?int $storeId = null): ?string
    {
        $iban = $this->scopeConfig->getValue(
            $this->getConfigPath('iban'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );

        return $this->ibanNormalizer->normalize($iban);
    }

    /**
     * Return BIC.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getBic(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('bic'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return reference type.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getReferenceType(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('reference_type'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return payment reference.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getPaymentReference(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('payment_reference'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }


    /**
     * Return customer hint.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCustomerHint(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('customer_hint'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return character encoding.
     *
     * @param int|null $storeId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCharEncoding(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            $this->getConfigPath('char_encoding'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return color for the QR code.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCodeColor(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('code_color'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return background color for the QR code.
     *
     * @param int|null $storeId
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCodeBeColor(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            $this->getConfigPath('be_color'),
            ScopeInterface::SCOPE_STORE,
            $storeId ?? (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * Return the fixed service tag value "BCD" that has to be passed to the QR code.
     *
     * @return string
     */
    public function getServiceTag(): string
    {
        return 'BCD';
    }

    /**
     * Return used ECP QR Code version (1 or 2). This module supports only version 2.
     *
     * @return int
     */
    public function getVersion(): int
    {
        return 2;
    }

    /**
     * Return the fixed identification value "SCT" that has to be passed to the QR code.
     *
     * @return string
     */
    public function getIdentification(): string
    {
        return 'SCT';
    }

    /**
     * Return the configuration path for the given field suffix.
     *
     * @param string $fieldSuffix
     * @return string
     */
    private function getConfigPath(string $fieldSuffix): string
    {
        return self::CONFIG_BASE_PATH . self::CONFIG_FIELD_PREFIX . $fieldSuffix;
    }
}
