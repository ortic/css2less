<?php

namespace Ortic\Css2Less;

use Ortic\Css2Less\tokens\LessRuleList;
use Ortic\Css2Less\tokens\LessRule;

class Css2Less
{
    /**
     * @var string $cssContent
     */
    protected $cssContent;

    /**
     * @var \CssParser $parser
     */
    protected $parser;

    /**
     * Tokens.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Nested CSS tree
     *
     * @var array
     */
    protected $lessTree = [];

    /**
     * List of CSS rules
     *
     * @var LessRuleList
     */
    protected $ruleSetList;

    /**
     * Variables.
     *
     * @var array
     */
    protected $variables = [];

    /**
     * Create a new parser object, use parameter to specify CSS you
     * wish to convert into a LESS file
     *
     * @param string $cssContent
     */
    public function __construct($cssContent)
    {
        $this->cssContent = $cssContent;
        $this->parser = new \CssParser($this->cssContent);
    }

    /**
     * Iterates through all tokens and extracts the values into variables
     */
    protected function extractVariables()
    {
        $properties = ['color', 'font-family'];
        foreach ($properties as $property) {
            $this->variables[$property] = [];
        }

        foreach ($this->tokens as $token) {
            if ($token instanceof \CssRulesetDeclarationToken) {
                if (in_array($token->Property, $properties)) {
                    if (!array_key_exists($token->Value, $this->variables[$token->Property])) {
                        $this->variables[$token->Property][$token->Value] = $token->Property . '_' . (count($this->variables[$token->Property]) + 1);

                    }
                    $token->Value = '$' . $this->variables[$token->Property][$token->Value];
                }
            }
        }
    }

    /**
     * Returns a string containing all variables to be printed in the output
     *
     * @return string
     */
    protected function getVariables()
    {
        $return = '';
        foreach ($this->variables as $properties) {
            foreach ($properties as $variable => $property) {
                $return .= "\${$property}: {$variable};\n";
            }
        }
        $return .= "\n";
        return $return;
    }

    /**
     * Returns a string containing the LESS content matching the CSS input
     * @return string
     */
    public function getLess($extractVariables = false)
    {

        $this->tokens = $this->parser->getTokens();

        // extract variables
        if ($extractVariables) {
            $this->extractVariables();
        }

        $this->buildNestedTree();

        $return = '';

        // print variables
        if ($extractVariables) {
            $return .= $this->getVariables();
        }

        foreach ($this->lessTree as $node) {
            // @TODO this format method shouldn't be in this class..
            $return .= $this->ruleSetList->formatTokenAsLess($node) . "\n";
        }

        $return .= $this->ruleSetList->lessify();

        return $return;
    }

    /**
     * Build a nested tree based on the flat CSS tokens
     */
    protected function buildNestedTree()
    {
        // this variable is true, if we're within a ruleset, e.g. p { .. here .. }
        // we have to normalize them
        $withinRulset = false;
        $ruleSet = null;
        $this->ruleSetList = new LessRuleList();

        foreach ($this->tokens as $token) {
            // we have to skip some tokens, their information is redundant
            if ($token instanceof \CssAtMediaStartToken ||
                $token instanceof \CssAtMediaEndToken
            ) {
                continue;
            }

            // we have to build a hierarchy with CssRulesetStartToken, CssRulesetEndToken
            if ($token instanceof \CssRulesetStartToken) {
                $withinRulset = true;
                $ruleSet = new LessRule($token->Selectors);
            } elseif ($token instanceof \CssRulesetEndToken) {
                $withinRulset = false;
                if ($ruleSet) {
                    $this->ruleSetList->addRule($ruleSet);
                }
                $ruleSet = null;
            } else {
                // as long as we're in a ruleset, we're adding all token to a custom array
                // this will be lessified once we've found CssRulesetEndToken and then added
                // to the actual $lessTree variable
                if ($withinRulset) {
                    $ruleSet->addToken($token);
                } else {
                    $this->lessTree[] = $token;
                }
            }
        }
    }
}
