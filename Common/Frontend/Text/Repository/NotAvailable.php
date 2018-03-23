<?php

namespace SPHERE\Common\Frontend\Text\Repository;

use SPHERE\Common\Frontend\Text\ITextInterface;

/**
 * Class NotAvailable
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class NotAvailable implements ITextInterface
{

    /** @var string $Value */
    private $Value = 'N/A';

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

        return '<span class="text-muted">'.$this->getValue().'</span>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
