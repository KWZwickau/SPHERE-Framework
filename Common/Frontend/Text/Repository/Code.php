<?php
namespace SPHERE\Common\Frontend\Text\Repository;

use SPHERE\Common\Frontend\Text\ITextInterface;

/**
 * Class Code
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class Code implements ITextInterface
{

    const TYPE_AUTOMATICALLY = null;
    const TYPE_YAML = 'yaml';
    const TYPE_TWIG = 'twig';
    const TYPE_PHP = 'php';
    const TYPE_HTML = 'html';

    /** @var string $Value */
    private $Value = '';
    /** @var null|string $Type */
    private $Type = null;

    /**
     * @param string $Value
     * @param null|string $Type
     */
    public function __construct($Value, $Type = Code::TYPE_AUTOMATICALLY)
    {

        $this->Value = $Value;
        $this->Type = $Type;
    }


    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return '<pre><code'.($this->Type ? ' class="'.$this->Type.'"' : '').'>' . $this->getValue() . '</code></pre>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
