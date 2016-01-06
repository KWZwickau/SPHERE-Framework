<?php
namespace SPHERE\Common\Documentation\Content;

use SPHERE\Common\Documentation\Designer;
use SPHERE\Common\Documentation\Designer\Book;

/**
 * Class General
 *
 * @package SPHERE\Common\Documentation\Content
 */
class General
{

    /** @var Book $Book */
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

        $this->Book = $Designer->createBook('Allgemeine Hilfe');
        $this->Book->setVisible($Chapter, $Page);

        $Chapter = $this->Book->createChapter('Internet-Browser', '');
        if ($Chapter->getHash() == $this->Book->getCurrentChapter()) {

            $Page = $Chapter->createPage('1. Unterstützte Browser', '', $Search);
            $Page->addHeadline('1.1. Allgemein');
            $Page->addParagraph('http://www.whatbrowser.org/');
            $Page->addParagraph('http://www.krohn.io/technik/web/browser-cache-leeren/');
            $Page->addSeparator();
            $Page->addHeadline('1.2. Empfohlen');
            $Page->addParagraph('');
            $Page->addSeparator();
            $Page->addHeadline('1.3. Browser-Cache');
            $Page->addParagraph('');
            $Page->addSeparator();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Book;
    }
}
