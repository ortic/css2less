<?php

namespace Ortic\Css2Less;

use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\KeyFrame;

class Css2Less
{
    /**
     * @var array $cssInput
     */
    protected $cssInput = [];

    /**
     * Pass your CSS file into the class as an array, as a string or simply as the
     * content of your CSS.
     *
     * @param $cssInput array|string $cssInputFiles
     */
    public function __construct($cssInput)
    {
        if (is_array($cssInput)) {
            $inputArray = $cssInput;
        } else {
            $inputArray[] = $cssInput;
        }

        // get content of files
        foreach ($inputArray as $input) {
            if (is_file($input)) {
                $this->cssInput[] = file_get_contents($input);
            } else {
                $this->cssInput[] = $input;
            }
        }
    }

    /**
     * Parses the CSS content into a LESS compatible hierarchy
     */
    public function parse()
    {
        $result =  $this->parseInternal(join ("\n", $this->cssInput));
        return $result;
    }

    /**
     * @TODO move this into a new class so we can have a format as LESS, SCSS and so on
     * @param array $lessTree
     */
    public function formatAsLess($lessTree, $level = 0) {
        $return = '';
        foreach ($lessTree as $lessKey => $lessValue) {
            switch ($lessKey) {
                case 'selector':
                    $return .= $this->formatSelectorAsLess($lessValue, $level);
                    break;
                case 'charset':
                    $return .= $this->formatCharsetAsLess($lessValue);
                    break;
                case 'atRuleSet':
                    $return .= $this->formatAtRuleSetAsLess($lessValue);
                    break;
                case 'keyFrame':
                    $return .= $this->formatKeyFrameAsLess($lessValue, $level);
                    break;
                case 'atRuleBlockList':
                    $return .= $this->formatAtRuleBlockList($lessValue, $level);
                    break;
                default:
                    throw new Exception (sprintf('Unknown type %s', $lessKey));
            }
        }
        return $return;
    }

    protected function formatAtRuleBlockList($lessValue, $level = 0) {
        $return = '';
        foreach ($lessValue as $atRuleBlock) {
            $return .= "@{$atRuleBlock[0]} {$atRuleBlock[1]}{\n";
            $return .= $this->formatAsLess($atRuleBlock[2], $level + 1);
            $return .= "}\n";
        }
        return $return;
    }

    protected function formatKeyFrameAsLess($lessValue, $level = 0) {
        $return = '';
        foreach ($lessValue as $keyFrame) {
            $return .= "@{$keyFrame[0]} {$keyFrame[1]} {\n";
            $return .= $this->formatAsLess($keyFrame[2], $level + 1);
            $return .= "}\n";
        }
        return $return;
    }

    protected function formatAtRuleSetAsLess($lessValue) {
        $return = '';
        foreach ($lessValue as $ruleSet) {
            $return .= "@{$ruleSet[0]} {$ruleSet[1]}{\n";
            foreach ($ruleSet[2] as $rule) {
                $return .= "\t{$rule}\n";
            }
            $return .= "}\n";
        }
        return $return;
    }

    protected function formatCharsetAsLess($lessValue) {
        $return = '';
        foreach ($lessValue as $charsetRule) {
            $return .= "{$charsetRule}\n";
        }
        return $return;
    }

    protected function formatSelectorAsLess($lessValue, $level = 0) {
        $return = '';
        $indentation = str_repeat("\t", $level);
        foreach ($lessValue as $selector => $selectValues) {
            $return .= $indentation . $selector;
            $return .= " {\n";

            foreach ($selectValues as $childSelector => $childValues) {
                if ($childSelector == '@rules') {
                    foreach ($childValues as $rule) {
                        $return .= $indentation . "\t" . (string)$rule . "\n";
                    }
                }
                else {
                    $return .= $this->formatSelectorAsLess([$childSelector => $childValues], $level + 1);
                }
            }
            $return .= $indentation. "}\n";
        }
        return $return;
    }

    protected function parseInternal($cssContent)
    {
        $cssParser = new Parser($cssContent);
        $cssTree = $cssParser->parse()->getContents();

        return $this->parseInternalTree($cssTree);
    }

    protected function parseInternalTree($cssTree) {
        // this is the place where we save the hierarchy of our selectors ( p { a {} } )
        $lessSelectorTree = [];

        // holds the charset properties
        $lessCharsetTree = [];

        // holds @ rules like @font-face
        $lessAtRuleSetTree = [];

        // holds @ rules with nested selectors like @media
        $lessAtRuleBlockListTree = [];

        // holds all keyframe related rules
        $lessKeyFrameTree = [];

        foreach ($cssTree as $cssElement) {
            // parse classic declaration blocks like p a { ...
            if ($cssElement instanceof DeclarationBlock) {
                $currentTree = & $lessSelectorTree;
                $this->parseDeclarationBlock($cssElement, $currentTree);
            } // parse charset
            elseif ($cssElement instanceof Charset) {
                $lessCharsetTree[] = (string)$cssElement;
            } // parse @ rules
            elseif ($cssElement instanceof AtRuleSet) {
                $lessAtRuleSetTree[] = $this->parseAtRuleSet($cssElement);
            } elseif ($cssElement instanceof AtRuleBlockList) {
                $lessAtRuleBlockListTree[] = $this->parseAtRuleBlockList($cssElement);
            } elseif ($cssElement instanceof KeyFrame) {
                $lessKeyFrameTree[] = $this->parseKeyFrame($cssElement);
            } else {
                print_r($cssElement);
            }
        }

        // @TODO this array madness needs some proper classes
        return ['charset' => $lessCharsetTree, 'selector' => $lessSelectorTree, 'atRuleSet' => $lessAtRuleSetTree, 'atRuleBlockList' => $lessAtRuleBlockListTree, 'keyFrame' => $lessKeyFrameTree];
    }

    /**
     * Parses a keyframe rule like
     * <code>
     * @keyframes mymove {
     *   from { top: 0px; }
     * }
     * </code>
     *
     * @param KeyFrame $cssElement
     * @return array
     */
    public function parseKeyFrame(KeyFrame $cssElement) {
        $cssTree = $cssElement->getContents();

        return [
            $cssElement->getVendorKeyFrame(),
            $cssElement->getAnimationName(),
            $this->parseInternalTree($cssTree),
        ];
    }

    /**
     * Parses @ rules like @media where you can have nested elements
     *
     * @param AtRuleBlockList $cssElement
     * @return array
     */
    public function parseAtRuleBlockList(AtRuleBlockList $cssElement) {
        $cssTree = $cssElement->getContents();

        return [
            $cssElement->atRuleName(),
            $cssElement->atRuleArgs(),
            $this->parseInternalTree($cssTree),
        ];
    }

    /**
     * Parses @ rules like @font-face
     *
     * @param AtRuleSet $cssElement
     * @return array
     */
    public function parseAtRuleSet(AtRuleSet $cssElement)
    {
        // add rules
        return [
            $cssElement->atRuleName(),
            $cssElement->atRuleArgs(),
            $cssElement->getRules(),
        ];
    }

    /**
     * @param $cssElement
     * @param $currentTree
     * @return mixed
     */
    public function parseDeclarationBlock(DeclarationBlock $cssElement, &$currentTree)
    {
        // use a loop in case there are multiple selectors like h1, h2 { ...
        foreach ($cssElement->getSelectors() as $selector) {
            // split hierarchy into array [p, a] to build a proper tree
            $selectorPath = preg_split('[ ]', (string)$selector, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($selectorPath as $selectSinglePath) {
                if (!array_key_exists($selectSinglePath, $currentTree)) {
                    $currentTree[$selectSinglePath] = [];
                }
                $currentTree = & $currentTree[$selectSinglePath];
            }

            // add rules to last selector
            // @TODO yet another array madness, proper classes please
            $currentTree['@rules'] = $cssElement->getRules();
        }
    }
}