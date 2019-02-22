<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.02.21.
 * Time: 14:19
 */

namespace App\Webtown\WfConfigEditorBundle\DefinitionDumper;


use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Config\Definition\ScalarNode;

class ArrayDumper
{
    public function dump(ConfigurationInterface $configuration)
    {
        return $this->dumpNode($configuration->getConfigTreeBuilder()->buildTree());
    }

    public function dumpNode(NodeInterface $node)
    {
        $base = [
            'name' => $node->getName(),
            'info' => $node instanceof BaseNode ? $node->getInfo() : null,
            'required' => $node->isRequired(),
        ];
        $children = null;
        if ($node instanceof ArrayNode) {
            $children = $node->getChildren();

            if ($node instanceof PrototypedArrayNode) {
                $children = $this->getPrototypeChildren($node);
                $base['prototype'] = array_keys($children);
            }
        }

        if ($children) {
            $value = [];
            /** @var NodeInterface $childNode */
            foreach ($children as $name => $childNode) {
                $value[$name] = $this->dumpNode($childNode);
            }

            $base['children'] = $value;
            return $base;
        }

        $base['default'] = $node->hasDefaultValue() ? $node->getDefaultValue() : '';
        return $base;
    }

    private function getPrototypeChildren(PrototypedArrayNode $node): array
    {
        $prototype = $node->getPrototype();
        $key = $node->getKeyAttribute();

        // Do not expand prototype if it isn't an array node nor uses attribute as key
        if (!$key && !$prototype instanceof ArrayNode) {
            return $node->getChildren();
        }

        if ($prototype instanceof ArrayNode) {
            $keyNode = new ArrayNode($key, $node);
            $children = $prototype->getChildren();

            if ($prototype instanceof PrototypedArrayNode && $prototype->getKeyAttribute()) {
                $children = $this->getPrototypeChildren($prototype);
            }

            // add children
            foreach ($children as $childNode) {
                $keyNode->addChild($childNode);
            }
        } else {
            $keyNode = new ScalarNode($key, $node);
        }

        $info = 'Prototype';
        if (null !== $prototype->getInfo()) {
            $info .= ': '.$prototype->getInfo();
        }
        $keyNode->setInfo($info);

        return array(sprintf('*%s', $key) => $keyNode);
    }
}
