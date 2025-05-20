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

## Sets

| Set                                     | Description |
|-----------------------------------------|-------------|
| HeimrichHannotSetList::CLEANUP_CONTAO_5 | Replace usage of deprecated extensions not supported in Contao 5.0 |
| HeimrichHannotSetList::UTILS_3008       | Migrate utils v2 code to v3 code 

## Rules 

### Usage

> [!CAUTION]
> The rules are experimental and will do cover all cases!

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    // ...
    ->withRules([
    
        // Replace some request bundle calls with symfony request
        \HeimrichHannot\Rector\Rector\RequestBundleToSymfonyRequestRector::class,
        
        // Replace some utils bundle v2 calls with v3 calls
        \HeimrichHannot\Rector\Rector\UtilsBundleUpdateV3Rector::class,
    ])
    ;
```

