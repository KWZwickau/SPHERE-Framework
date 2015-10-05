<?php
namespace SPHERE\Common\Documentation;

use SPHERE\Common\Documentation\Designer\Book;

/**
 * Class Designer
 *
 * @package SPHERE\Common\Documentation
 */
class Designer
{

    /** @var Book[] $BookList */
    private $BookList = array();

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

        return implode('', $this->BookList);
    }

    /**
     * @param string $Title
     *
     * @return Book
     */
    public function createBook($Title)
    {

        $Book = new Book($Title);
        array_push($this->BookList, $Book);
        return $Book;
    }
}
