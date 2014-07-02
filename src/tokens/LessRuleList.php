<?php

namespace Ortic\Css2Less\tokens;

class LessRuleList
{
    private $list = [];

    public function addRule($rule)
    {
        $this->list[] = $rule;
    }

    protected function getTree()
    {
        $output = [];

        foreach ($this->list as $ruleSet) {
            $selectors = $ruleSet->getSelectors();

            foreach ($ruleSet->getTokens() as $token) {
                foreach ($token->MediaTypes as $mediaType) {
                    // make sure we're aware of our media type
                    if (!array_key_exists($mediaType, $output)) {
                        $output[$mediaType] = [];
                    }

                    // add declaration token to output for each selector
                    $currentNode = & $output[$mediaType];
                    foreach ($selectors as $selector) {
                        $selectorPath = preg_split('[ ]', $selector, -1, PREG_SPLIT_NO_EMPTY);

                        foreach ($selectorPath as $selectorPathItem) {
                            if (!array_key_exists($selectorPathItem, $currentNode)) {
                                $currentNode[$selectorPathItem] = [];
                            }
                            $currentNode = & $currentNode[$selectorPathItem];
                        }

                        $currentNode['@rules'] = $token;
                    }

                }
            }

        }
        return $output;
    }

    protected function formatAsLess($selector, $level = 0)
    {
        $return = '';
        $indentation = str_repeat("\t", $level);
        foreach ($selector as $nodeKey => $node) {
            $return .= $indentation . "{$nodeKey} {\n";

            foreach ($node as $subNodeKey => $subNodes) {
                if ($subNodeKey === '@rules') {
                    $return .= $indentation . "\t" . $subNodes . "\n";
                } else {
                    $return .= $this->formatAsLess([$subNodeKey => $subNodes], $level + 1);
                }
            }

            $return .= $indentation . "}\n";

        }
        return $return;
    }

    public function lessify()
    {
        $tree = $this->getTree();
        $return = '';

        foreach ($tree as $mediaType => $node) {
            if ($mediaType == 'all') {
                $return .= $this->formatAsLess($node);
            } else {
                $return .= "@{$mediaType} {\n";
                $return .= $this->formatAsLess($node, 1);
                $return .= "}\n";
            }
        }

        return $return;
    }
}