<?php
namespace SPHERE\Common\Documentation\Designer;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Extension\Extension;

/**
 * Class Book
 *
 * @package SPHERE\Common\Documentation\Designer
 */
class Book extends Extension
{

    /** @var null|string $VisibleChapter Hash */
    private static $VisibleChapter = null;
    /** @var null|string $VisiblePage Hash */
    private static $VisiblePage = null;
    /** @var array $Directory */
    private $Directory = array('Kapitelübersicht');
    /** @var Chapter[] $ChapterList */
    private $ChapterList = array();
    /** @var string $Title */
    private $Title = '{{ Title }}';

    /**
     * @param string $Title
     */
    public function __construct($Title)
    {

        $this->Title = $Title;
    }

    /**
     * @return null|string
     */
    public static function getCurrentPage()
    {

        return self::$VisiblePage;
    }

    /**
     * @param string $Hash
     */
    public static function setCurrentPage($Hash)
    {

        self::$VisiblePage = $Hash;
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

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Listing($this->getBookDirectory()),
                        new Listing($this->getChapterDirectory()),
                        new Form(
                            new FormGroup(
                                new FormRow(
                                    new FormColumn(
                                        new TextField('Search', 'Suchtext', 'Auf der aktuellen Seite suchen',
                                            new Search())
                                    )
                                )
                            ), new Primary('Suchen')
                        ),
                    ), 3),
                    new LayoutColumn(
                        implode('', $this->ChapterList)
                        , 9),
                )), new Title($this->Title)
            )
        );
    }

    /**
     * @return array
     */
    public function getBookDirectory()
    {

        return $this->Directory;
    }

    /**
     * @return array
     */
    public function getChapterDirectory()
    {

        /** @var Chapter $Chapter */
        foreach ($this->ChapterList as $Chapter) {
            if ($Chapter->getHash() == self::getCurrentChapter()) {
                return $Chapter->getDirectory();
            }
        }
        return array();
    }

    /**
     * @return null|string
     */
    public static function getCurrentChapter()
    {

        return self::$VisibleChapter;
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
        if (!Book::getCurrentChapter()) {
            Book::setCurrentChapter($Chapter->getHash());
        }
        if (Book::getCurrentChapter() == $Chapter->getHash()) {
            array_push($this->ChapterList, $Chapter);
            array_push($this->Directory, new ChevronRight().' '.new Bold($Title.' '.new Muted($Description)));
        } else {
            array_push(
                $this->Directory,
                new Link($Title.' '.new Muted($Description), '/Manual/StyleBook', new TileBig(),
                    array('Chapter' => $Chapter->getHash())
                )
            );
        }
        return $Chapter;
    }

    /**
     * @param string $Hash
     */
    public static function setCurrentChapter($Hash)
    {

        self::$VisibleChapter = $Hash;
    }

    /**
     * @param null|string $Chapter
     * @param null|string $Page
     */
    public function setVisible($Chapter = null, $Page = null)
    {

        if ($Chapter) {
            self::$VisibleChapter = $Chapter;
        }
        if ($Page) {
            self::$VisiblePage = $Page;
        }
    }
}
