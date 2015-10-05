<?php
namespace SPHERE\Common\Documentation\Designer;

use SPHERE\Common\Frontend\Layout\Repository\Headline;

/**
 * Class Page
 *
 * @package SPHERE\Common\Documentation\Designer
 */
class Page
{

    /** @var array $ElementList */
    private $ElementList = array();

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @param string $Title
     * @param string $Description
     *
     * @return Page
     */
    public function createHeadline($Title, $Description)
    {

        $Element = new Headline($Title, $Description);
        array_push($this->ElementList, $Element);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return implode('', $this->ElementList);
    }
}
