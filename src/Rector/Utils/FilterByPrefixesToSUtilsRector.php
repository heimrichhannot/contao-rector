<?php

declare(strict_types=1);

namespace HeimrichHannot\Rector\Rector\Utils;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class FilterByPrefixesToSUtilsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        // Nur filterByPrefixes umstellen
        if (! $this->isName($node->name, 'filterByPrefixes')) {
            return null;
        }

        // 1) $this->arrayUtil->filterByPrefixes(...)
        if (
            $node->var instanceof PropertyFetch
            && $this->isName($node->var->var, 'this')
            && $this->isName($node->var->name, 'arrayUtil')
        ) {
            return $this->createSUtilsCall($node->args);
        }

        // 2) System::getContainer()->get('huh.utils.array')->filterByPrefixes(...)
        if (
            $node->var instanceof MethodCall
            && $this->isName($node->var->name, 'get')
            && $node->var->var instanceof StaticCall
            && $this->isName($node->var->var->class, 'Contao\\System')
            && $this->isName($node->var->var->name, 'getContainer')
            && isset($node->var->args[0])
            && $node->var->args[0]->value instanceof String_
            && $node->var->args[0]->value->value === 'huh.utils.array'
        ) {
            return $this->createSUtilsCall($node->args);
        }

        return null;
    }

    private function createSUtilsCall(array $args): MethodCall
    {
        // SUtils::array()
        $sutilsCall = new StaticCall(
            new FullyQualified('HeimrichHannot\\UtilsBundle\\StaticUtil\\SUtils'),
            'array'
        );

        // ->filterByPrefixes(...)
        return new MethodCall($sutilsCall, 'filterByPrefixes', $args);
    }

    public function getDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Ersetzt arrayUtil->filterByPrefixes() bzw. DI-Container Aufrufe durch SUtils::array()->filterByPrefixes()',
            [
                new CodeSample(
                    <<<'CODE'
$this->arrayUtil->filterByPrefixes($prefixes, $records);
System::getContainer()->get('huh.utils.array')->filterByPrefixes($prefixes, $records);
CODE
                    ,
                    <<<'CODE'
SUtils::array()->filterByPrefixes($prefixes, $records);
CODE
                ),
            ]
        );
    }
}
