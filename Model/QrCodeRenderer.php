<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Model;

use Apirone\Lib\PhpQRCode\QRCode;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;
use SchrammelCodes\EpcQrCode\Model\Config\Reader;
use SchrammelCodes\EpcQrCode\Model\Config\Source\PaymentReferenceType;
use SepaQr\Data as SepaQrData;
use SepaQr\Exception as SepaQException;

/**
 * @package SchrammelCodes\EpcQrCode\Model
 */
class QrCodeRenderer
{
    private const ERROR_LOG_PREFIX = 'Error rendering EPC QR code: ';

    public function __construct(
        private readonly Reader $configReader,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Check if all prerequisites are met to render the QR code.
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function canRender(OrderInterface $order): bool
    {
        $storeId = $order->getStoreId() ? (int)$order->getStoreId() : null;

        try {
            if ($this->configReader->isEpcQrCodeEnabled($storeId) && $this->areMandatoryPrerequisitesMet($order)) {
                return true;
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error(self::ERROR_LOG_PREFIX . $e->getMessage());
        }

        return false;
    }

    public function getBase64EncodedQrCode(OrderInterface $order): ?string
    {
        try {
            if (!$this->canRender($order)) {
                return null;
            }

            $data = $this->prepareData($order);
        } catch (Exception $e) {
            $this->logger->error(self::ERROR_LOG_PREFIX . $e->getMessage());
            return null;
        }

        $qrCodeOptions = [
            's' => 'qrm'
        ];

        $storeId = $order->getStoreId() ? (int)$order->getStoreId() : null;
        try {
            $qrCodeOptions['fc'] = $this->configReader->getCodeColor($storeId);
            $qrCodeOptions['bc'] = $this->configReader->getCodeBeColor($storeId);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning('EPC QR code color configuration not found. Using default colors.');
        }

        return QrCode::png($data, $qrCodeOptions);
    }

    /**
     * Return the QR code image tag or null on failure.
     *
     * @param OrderInterface $order
     * @return string|null
     */
    public function renderQrCodeImageTag(OrderInterface $order): ?string
    {
        $base64EncodedQrCode = $this->getBase64EncodedQrCode($order);

        if ($base64EncodedQrCode === null) {
            return null;
        }

        return '<img class="epc-qr-code" src="' . $base64EncodedQrCode . '" />';
    }

    /**
     * Prepare the data for the QR code.
     *
     * @param OrderInterface $order
     * @return SepaQrData
     * @throws EpcQrCodeException
     * @throws NoSuchEntityException
     * @throws SepaQException
     */
    private function prepareData(OrderInterface $order): SepaQrData
    {
        $storeId = $order->getStoreId() ? (int)$order->getStoreId() : null;

        $paymentData = SepaQrData::create()
            ->setServiceTag($this->configReader->getServiceTag())
            ->setVersion($this->configReader->getVersion())
            ->setCharacterSet($this->configReader->getCharEncoding($storeId))
            ->setIdentification($this->configReader->getIdentification())
            ->setName($this->configReader->getBeneficiaryName($storeId))
            ->setIban($this->configReader->getIban($storeId))
            ->setAmount((float)$order->getGrandTotal());

        switch ($this->configReader->getReferenceType($storeId)) {
            case PaymentReferenceType::TYPE_PAYMENT_REFERENCE:
                $reference = str_replace(
                    ['%orderNumber%', '%firstName%', '%lastName%'],
                    [$order->getIncrementId(), $order->getCustomerFirstname(), $order->getCustomerLastname()],
                    $this->configReader->getPaymentReference($storeId)
                );
                $paymentData->setRemittanceText($reference);
                break;
            case PaymentReferenceType::TYPE_CREDITOR_REFERENCE:
                $paymentData->setRemittanceReference($order->getIncrementId());
                break;
            default:
                EpcQrCodeException::configException(__('Invalid reference type.'));
        }

        $bic = $this->configReader->getBic($storeId);
        if ($bic !== null) {
            $paymentData->setBic($bic);
        }

        $customerHint = $this->configReader->getCustomerHint($storeId);
        if ($customerHint !== null) {
            $paymentData->setInformation($customerHint);
        }

        return $paymentData;
    }

    /**
     * Ensure all mandatory prerequisites are met.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function areMandatoryPrerequisitesMet(OrderInterface $order): bool
    {
        $grandTotal = (float)$order->getGrandTotal();

        if ($grandTotal < 0.01 || $grandTotal > 999999999.99) {
            $this->logger->notice(
                sprintf(
                    'Grand total of %f is out of supported range for EPC QR code. Order #%s',
                    $grandTotal,
                    $order->getIncrementId()
                )
            );
            return false;
        }

        $storeId = $order->getStoreId() ? (int)$order->getStoreId() : null;

        try {
            $charEncoding = $this->configReader->getCharEncoding($storeId);
            $referenceType = $this->configReader->getReferenceType($storeId);

            $result = $this->configReader->getBeneficiaryName($storeId) &&
                $this->configReader->getIban($storeId) &&
                $referenceType && in_array($referenceType, PaymentReferenceType::ALLOWED_PAYMENT_REFERENCE_TYPES) &&
                $charEncoding && in_array($charEncoding, range(1, 8)) &&
                $this->configReader->getServiceTag() === 'BCD' &&
                $this->configReader->getVersion() === 2 &&
                $this->configReader->getIdentification() === 'SCT';

            // If payment reference is used, ensure it is set in the configuration.
            if ($result &&
                $referenceType === PaymentReferenceType::TYPE_PAYMENT_REFERENCE &&
                !$this->configReader->getPaymentReference($storeId)
            ) {
                EpcQrCodeException::configException(
                    __('Payment reference should be used, but is not configured.')
                );
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error(self::ERROR_LOG_PREFIX . $e->getMessage());
        }

        return false;
    }
}
