<?php

namespace Ortic\Css2Less\tokens;

/**
 * Class LessRule
 * @package Ortic\Css2Less\tokens
 */
class LessRule
{
    private $selectors = array();
    private $tokens = array();

    /**
     * @param $selectors
     */
    public function __construct($selectors)
    {
        $this->selectors = $selectors;
    }

    /**
     * Add new node to rule
     * @param $token
     */
    public function addToken($token)
    {
        $this->tokens[] = $token;
    }

    /**
     * Returns the list of selectors (e.g. #logo img)
     * @return array
     */
    public function getSelectors()
    {
        return $this->selectors;
    }

    /**
     * Returns a list of tokens/nodes for the current selector
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
