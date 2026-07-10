<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$io = new \Magento\Framework\Code\Generator\Io(
    new \Magento\Framework\Filesystem\Driver\File(),
    __DIR__ . '/../generated/code'
);

(new \Magento\Framework\ObjectManager\Code\Generator\Factory(
    \Magento\Framework\Controller\Result\Raw::class,
    null,
    $io
))->generate();
