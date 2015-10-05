<?php
namespace SPHERE\Common\Documentation\Designer;

/**
 * Class Chapter
 *
 * @package SPHERE\Common\Documentation\Designer
 */
class Chapter
{

    /** @var Page[] $PageList */
    private $PageList = array();

    private $Title = '{{ Title }}';
    private $Description = '{{ Description }}';

    /**
     * @param $Title
     * @param $Description
     */
    public function __construct($Title, $Description)
    {

        $this->Title = $Title;
        $this->Description = $Description;
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

        return implode('', $this->PageList);
    }

    /**
     * @return Page
     */
    public function createPage()
    {

        $Page = new Page();
        array_push($this->PageList, $Page);
        return $Page;
    }
}
