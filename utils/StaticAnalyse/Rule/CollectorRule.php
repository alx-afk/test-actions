<?php

namespace Utils\StaticAnalyse\Rule;

use Illuminate\Contracts\Queue\ShouldQueue;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;

class CollectorRule implements Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof \PHPStan\Node\CollectedDataNode) {
            $items = $node->get(Node\Stmt\Class_::class);
            foreach ($items as $item) {
                return $this->check($item, $scope);
            }
        } else {
            return $this->check($node, $scope);
        }
        return [];
    }

    private function check(Node $node, Scope $scope)
    {
        if (!($node instanceof Node\Stmt\Class_)) {
            return [];
        }

        $className = $scope->getClassReflection();
        if (!$className?->implementsInterface(ShouldQueue::class)) {
            return [];
        }

        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof Node\Stmt\Property && $stmt->props[0]->name->toString() === 'test')) {
                continue;
            }
            $propertyValue = $stmt->props[0]->default;
            if (!($propertyValue instanceof Node\Scalar\LNumber)) {
                return ['Error'];
            }
            if ($propertyValue->value !== 3) {
                return ['Class must have property with value 3'];
            }
            return [];
        }
        return ['Common Error'];
    }
}
