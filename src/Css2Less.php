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
     * @var CssParser $parser
     */
    protected $parser;

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
     * Returns a string containing the LESS content matching the CSS input
     * @return string
     */
    public function getLess()
    {
        $lessTree = array();

        // this variable is true, if we're within a ruleset, e.g. p { .. here .. }
        // we have to normalize them
        $withinRulset = false;
        $ruleSet = null;
        $ruleSetList = new LessRuleList();

        $tokens = $this->parser->getTokens();

        foreach ($tokens as $token) {
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
                $ruleSetList->addRule($ruleSet);
                $ruleSet = null;
            } else {
                // as long as we're in a ruleset, we're adding all token to a custom array
                // this will be lessified once we've found CssRulesetEndToken and then added
                // to the actual $lessTree variable
                if ($withinRulset) {
                    $ruleSet->addToken($token);
                } else {
                    $lessTree[] = $token;
                }
            }
        }

        $return = '';
        foreach ($lessTree as $node) {
            // @TODO this format method shouldn't be in this class..
            $return .= $ruleSetList->formatTokenAsLess($node) . "\n";
        }

        $return .= $ruleSetList->lessify();

        return $return;
    }

}
