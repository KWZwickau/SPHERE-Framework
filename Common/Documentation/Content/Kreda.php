<?php
namespace SPHERE\Common\Documentation\Content;

use SPHERE\Common\Documentation\Designer;

/**
 * Class Kreda
 *
 * @package SPHERE\Common\Documentation\Content
 */
class Kreda
{

    /** @var Designer\Book $Book */
    private $Book = null;

    /**
     * @param null|string $Chapter
     * @param null|string $Page
     * @param null|string $Search
     */
    public function __construct($Chapter = null, $Page = null, $Search = null)
    {

        $this->ShowChapter = $Chapter;
        $this->ShowPage = $Page;

        $Designer = new Designer();

        $this->Book = $Designer->createBook('KREDA Handbuch');
        $this->Book->setVisible($Chapter, $Page);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }
}
