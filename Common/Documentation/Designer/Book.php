<?php
namespace SPHERE\Common\Documentation\Designer;

/**
 * Class Book
 *
 * @package SPHERE\Common\Documentation\Designer
 */
class Book
{

    /** @var Chapter[] $ChapterList */
    private $ChapterList = array();

    private $Title = '{{ Title }}';

    /**
     * @param string $Title
     */
    public function __construct($Title)
    {

        $this->Title = $Title;
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

        return implode('', $this->ChapterList);
    }

    /**
     * @param string $Title
     * @param string $Description
     *
     * @return Chapter
     */
    public function createChapter($Title, $Description)
    {

        $Chapter = new Chapter($Title, $Description);
        array_push($this->ChapterList, $Chapter);
        return $Chapter;
    }
}
