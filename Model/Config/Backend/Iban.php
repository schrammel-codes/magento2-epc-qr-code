<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use SchrammelCodes\EpcQrCode\Exception\EpcQrCodeException;
use SchrammelCodes\EpcQrCode\Model\IbanNormalizer;

/**
 * @package SchrammelCodes\EpcQrCode\Model\Config\Backend
 */
class Iban extends Value
{
    public function __construct(
        private readonly IbanNormalizer $ibanNormalizer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate and normalize IBAN before saving.
     *
     * @throws EpcQrCodeException
     */
    public function beforeSave(): void
    {
        $iban = $this->getValue();
        $this->setValue($this->ibanNormalizer->normalize($iban));

        parent::beforeSave();
    }
}
