<?php

declare(strict_types=1);

use HeimrichHannot\Rector\Rector\Utils\FilterByPrefixesToSUtilsRector;
use HeimrichHannot\Rector\Rector\UtilsBundleUpdateV3Rector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UtilsBundleUpdateV3Rector::class);
    $rectorConfig->rule(FilterByPrefixesToSUtilsRector::class);
};
