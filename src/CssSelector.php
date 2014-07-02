<?php
/**
 * @TODO currently not in use
 */
namespace Ortic\Css2Less;

class CssSelector
{

    /**
     * @var string $selector
     */
    protected $selector;

    /**
     * @var CssSelector[] $childSelectors
     */
    protected $childSelectors = [];

    /**
     * @param string $selector the selector name
     * @param array $childSelectors optional children if known at this point
     */
    public function __construct($selector, $childSelectors = [])
    {
        $this->selector = $selector;
        $this->childSelectors = $childSelectors;
    }

    public function addChildSelector(CssSelector $selector)
    {
        $this->childSelectors[] = $selector;
    }

    public function getSelector() {
        return $this->selector;
    }

    public function getChildSelectors() {
        return $this->childSelectors;
    }
}