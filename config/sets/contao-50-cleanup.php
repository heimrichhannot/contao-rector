<?php

declare(strict_types=1);

use HeimrichHannot\Rector\Rector\RequestBundleToSymfonyRequestRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RequestBundleToSymfonyRequestRector::class);
};
