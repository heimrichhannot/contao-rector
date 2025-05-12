# Rector rules for H&H extensions

## Install

Add the repo to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/heimrichhannot/contao-rector"
    }
  ]
}
```

Require the package:

```bash
composer require heimrichhannot/contao-rector --dev 
```

## Rules 

```php
<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoLevelSetList;
use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    // ...
    ->withRules([
            \HeimrichHannot\Rector\Rector\RequestBundleToSymfonyRequestRector::class,
    ])
    ;

```