<?php

declare(strict_types=1);

namespace HeimrichHannot\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;

final class RequestBundleToSymfonyRequestRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Assign::class, MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        // 1) Ersetze $request = System::getContainer()->get('huh.request');
        if ($node instanceof Assign) {
            if (! $this->isName($node->var, 'request')) {
                return null;
            }
            if (! $node->expr instanceof MethodCall) {
                return null;
            }
            /** @var MethodCall $getCall */
            $getCall = $node->expr;

            // Prüfe auf ->get('huh.request')
            if (! $this->isName($getCall->name, 'get')) {
                return null;
            }
            if (! isset($getCall->args[0]) || ! $getCall->args[0]->value instanceof String_) {
                return null;
            }
            /** @var String_ $serviceName */
            $serviceName = $getCall->args[0]->value;
            if ($serviceName->value !== 'huh.request') {
                return null;
            }

            // Prüfe das statische System::getContainer()
            if (! $getCall->var instanceof StaticCall) {
                return null;
            }
            /** @var StaticCall $staticCall */
            $staticCall = $getCall->var;
            $className = $this->getName($staticCall->class);
            if (! in_array($className, ['System', 'Contao\\System'], true)) {
                return null;
            }
            if (! $this->isName($staticCall->name, 'getContainer')) {
                return null;
            }

            // Neu: System::getContainer()->get('request_stack')->getCurrentRequest()
            $containerCall     = $this->nodeFactory->createStaticCall($className, 'getContainer');
            $requestStackCall  = $this->nodeFactory->createMethodCall(
                $containerCall,
                'get',
                [new Arg(new String_('request_stack'))]
            );
            $currentRequestCall = $this->nodeFactory->createMethodCall(
                $requestStackCall,
                'getCurrentRequest'
            );

            $node->expr = $currentRequestCall;
            return $node;
        }

        // 2) Ersetze $request->hasGet()/getGet() → $request->query->has()/get()
        if ($node instanceof MethodCall) {
            if (! $node->var instanceof Variable || ! $this->isName($node->var, 'request')) {
                return null;
            }

            // hier kommt jetzt die PropertyFetch über NodeFactory
            $queryFetch = $this->nodeFactory->createPropertyFetch($node->var, 'query');

            if ($this->isName($node->name, 'hasGet')) {
                return $this->nodeFactory->createMethodCall($queryFetch, 'has', $node->args);
            }

            if ($this->isName($node->name, 'getGet')) {
                return $this->nodeFactory->createMethodCall($queryFetch, 'get', $node->args);
            }
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Ersetzt System::getContainer()->get(\'huh.request\') + hasGet/getGet durch RequestStack + query->has()/get()',
            [
                new CodeSample(
                // before
                    <<<'PHP'
$request = System::getContainer()->get('huh.request');
if ($request->hasGet('start') && $request->getGet('length') != -1) {
    $arrOptions['limit'] = $request->getGet('length');
    $arrOptions['offset'] = $request->getGet('start');
}
PHP
                    ,
                    // after
                    <<<'PHP'
$request = System::getContainer()->get('request_stack')->getCurrentRequest();
if ($request->query->has('start') && $request->query->get('length') != -1) {
    $arrOptions['limit'] = $request->query->get('length');
    $arrOptions['offset'] = $request->query->get('start');
}
PHP
                ),
            ]
        );
    }
}
