<?php

declare(strict_types=1);

namespace HeimrichHannot\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;

final class UtilsBundleUpdateV3Rector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Ersetzt `$this->container->get(\'huh.utils.container\')->isBackend()` sowie `System::getContainer()->get(\'huh.utils.model\')->findModelInstanceByPk(...)` mit FullyQualified Utils.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$this->container->get('huh.utils.container')->isBackend();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
\Contao\System::getContainer()->get(HeimrichHannot\UtilsBundle\Util\Utils::class)->container()->isBackend();
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
\Contao\System::getContainer()->get(HeimrichHannot\UtilsBundle\Util\Utils::class)->model()->findModelInstanceByPk($dc->table, $dc->id);
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        if ($this->isName($node->name, 'isBackend')) {
            return $this->refactorIsBackend($node);
        }

        if ($this->isName($node->name, 'findModelInstanceByPk')) {
            return $this->refactorFindModel($node);
        }

        return null;
    }

    private function refactorIsBackend(MethodCall $node): ?MethodCall
    {
        $getCall = $node->var;
        if (! $getCall instanceof MethodCall || ! $this->isName($getCall->name, 'get')) {
            return null;
        }

        $propertyFetch = $getCall->var;
        if (! $propertyFetch instanceof PropertyFetch || ! $this->isName($propertyFetch->var, 'this') || ! $this->isName($propertyFetch->name, 'container')) {
            return null;
        }

        $arg = $getCall->args[0]->value ?? null;
        if (! $arg instanceof String_ || $arg->value !== 'huh.utils.container') {
            return null;
        }

        $systemCall = new StaticCall(
            new FullyQualified('Contao\\System'),
            'getContainer'
        );

        $getUtils = new MethodCall(
            $systemCall,
            'get',
            [new Arg(new ClassConstFetch(
                new FullyQualified('HeimrichHannot\\UtilsBundle\\Util\\Utils'),
                'class'
            ))]
        );

        $containerCall = new MethodCall(
            $getUtils,
            'container'
        );

        return new MethodCall($containerCall, 'isBackend');
    }

    private function refactorFindModel(MethodCall $node): ?MethodCall
    {
        $getCall = $node->var;
        if (! $getCall instanceof MethodCall || ! $this->isName($getCall->name, 'get')) {
            return null;
        }

        $arg = $getCall->args[0]->value ?? null;
        if (! $arg instanceof String_ || $arg->value !== 'huh.utils.model') {
            return null;
        }

        $systemCall = new StaticCall(
            new FullyQualified('Contao\\System'),
            'getContainer'
        );

        $getUtils = new MethodCall(
            $systemCall,
            'get',
            [new Arg(new ClassConstFetch(
                new FullyQualified('HeimrichHannot\\UtilsBundle\\Util\\Utils'),
                'class'
            ))]
        );

        $modelCall = new MethodCall(
            $getUtils,
            'model'
        );

        return new MethodCall($modelCall, 'findModelInstanceByPk', $node->args);
    }
}
