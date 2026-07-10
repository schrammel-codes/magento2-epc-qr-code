<?php

declare(strict_types=1);

namespace SchrammelCodes\EpcQrCode\Setup\Patch\Data;

use Magento\Framework\FlagManager;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use SchrammelCodes\EpcQrCode\Model\UrlHasher;

/**
 * @codeCoverageIgnore
 */
class SetUpgradeTimestampFlag implements DataPatchInterface, PatchRevertableInterface
{
    public function __construct(private readonly FlagManager $flagManager)
    {
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->flagManager->saveFlag(UrlHasher::EPC_QR_CODE_V2_1_UPGRADE_TIMESTAMP_FLAG, time());
    }

    /**
     * @inheritDoc
     */
    public function revert()
    {
        $this->flagManager->deleteFlag(UrlHasher::EPC_QR_CODE_V2_1_UPGRADE_TIMESTAMP_FLAG);
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
