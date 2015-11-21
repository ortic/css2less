<?php

namespace Ortic\Css2Less\tokens;

/**
 * Class LessRuleList
 * @package Ortic\Css2Less\tokens
 */
class LessRuleList
{
    private $list = array();

    /**
     * Add a new rule object to our list
     * @param LessRule $rule
     */
    public function addRule(LessRule $rule)
    {
        $this->list[] = $rule;
    }

    /**
     * Build and returns a tree for the CSS input
     * @return array
     */
    protected function getTree()
    {
        $output = array();

        foreach ($this->list as $ruleSet) {
            $selectors = $ruleSet->getSelectors();

            foreach ($ruleSet->getTokens() as $token) {
                $this->parseTreeNode($output, $selectors, $token);
            }
        }
        return $output;
    }

    /**
     * Parse CSS input part into a LESS node
     * @param $output
     * @param $selectors
     * @param $token
     */
    protected function parseTreeNode(&$output, $selectors, $token)
    {
        foreach ($token->MediaTypes as $mediaType) {
            // make sure we're aware of our media type
            if (!array_key_exists($mediaType, $output)) {
                $output[$mediaType] = array();
            }

            foreach ($selectors as $selector) {
                // add declaration token to output for each selector
                $currentNode = &$output[$mediaType];

                // add support for direct descendants operator by aligning the spaces properly.
                // the code below supports "html >p" since we split by spaces. A selector "html > p" would cause an
                // additional tree level, we therefore normalize them with the two lines below.
                $selector = str_replace('> ', '>', $selector);
                $selector = str_replace('>', ' >', $selector);

                // selectors like "html body" must be split into an array so we can
                // easily nest them
                $selectorPath = preg_split('[ ]', $selector, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($selectorPath as $selectorPathItem) {
                    if (!array_key_exists($selectorPathItem, $currentNode)) {
                        $currentNode[$selectorPathItem] = array();
                    }
                    $currentNode = &$currentNode[$selectorPathItem];
                }

                $currentNode['@rules'][] = $this->formatTokenAsLess($token);
            }
        }
    }

    /**
     * Format LESS nodes in a nicer way with indentation and proper brackets
     * @param $token
     * @param int $level
     * @return string
     */
    public function formatTokenAsLess(\aCssToken $token, $level = 0)
    {
        $indentation = str_repeat("\t", $level);

        if ($token instanceof \CssRulesetDeclarationToken) {
            return $indentation . $token->Property . ": " . $token->Value . ($token->IsImportant ? " !important" : "") . ($token->IsLast ? "" : ";");
        } elseif ($token instanceof \CssAtKeyframesStartToken) {
            return $indentation . "@" . $token->AtRuleName . " \"" . $token->Name . "\" {";
        } elseif ($token instanceof \CssAtKeyframesRulesetStartToken) {
            return $indentation . "\t" . implode(",", $token->Selectors) . " {";
        } elseif ($token instanceof \CssAtKeyframesRulesetEndToken) {
            return $indentation . "\t" . "}";
        } elseif ($token instanceof \CssAtKeyframesRulesetDeclarationToken) {
            return $indentation . "\t\t" . $token->Property . ": " . $token->Value . ($token->IsImportant ? " !important" : "") . ($token->IsLast ? "" : ";");
        } elseif ($token instanceof \CssAtCharsetToken) {
            return $indentation . "@charset " . $token->Charset . ";";
        } elseif ($token instanceof \CssAtFontFaceStartToken) {
            return "@font-face {";
        } elseif ($token instanceof \CssAtFontFaceDeclarationToken) {
            return $indentation . "\t" . $token->Property . ": " . $token->Value . ($token->IsImportant ? " !important" : "") . ($token->IsLast ? "" : ";");
        } else {
            return $indentation . $token;
        }
    }

    protected function formatAsLess($selector, $level = 0)
    {
        $return = '';
        $indentation = str_repeat("\t", $level);
        foreach ($selector as $nodeKey => $node) {
            $return .= $indentation . "{$nodeKey} {\n";

            foreach ($node as $subNodeKey => $subNodes) {
                if ($subNodeKey === '@rules') {
                    foreach ($subNodes as $subNode) {
                        $return .= $indentation . "\t" . $subNode . "\n";
                    }
                } else {
                    $return .= $this->formatAsLess(array($subNodeKey => $subNodes), $level + 1);
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
                $return .= "@media {$mediaType} {\n";
                $return .= $this->formatAsLess($node, 1);
                $return .= "}\n";
            }
        }

        return $return;
    }
}
