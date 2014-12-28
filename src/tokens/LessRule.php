<?php

namespace Ortic\Css2Less\tokens;

class LessRule
{
    private $selectors = array();
    private $tokens = array();

    public function __construct($selectors) {
        $this->selectors = $selectors;
    }

    public function addToken($token) {
        $this->tokens[] = $token;
    }

    public function getSelectors() {
        return $this->selectors;
    }

    public function getTokens() {
        return $this->tokens;
    }

    public function __toString() {
    }
}