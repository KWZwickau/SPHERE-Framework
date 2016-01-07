<?php
namespace SPHERE\Common\Documentation\Designer;

use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
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
 * Class Chapter
 *
 * @package SPHERE\Common\Documentation\Designer
 */
class Chapter extends Extension
{

    /** @var array $Directory */
    private $Directory = array('Seitenübersicht');

    /** @var Page[] $PageList */
    private $PageList = array();

    private $Title = '{{ Title }}';
    private $Description = '{{ Description }}';

    /**
     * @param string $Title
     * @param string $Description
     */
    public function __construct($Title, $Description = '')
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

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        implode('', $this->PageList)
                    ),
                )), new Title(new TileSmall().' '.$this->Title, $this->Description)
            )
        );
    }

    /**
     * @param string $Title
     * @param string $Description
     * @param string $Search
     * @param bool   $useNumber
     *
     * @return Page
     */
    public function createPage($Title, $Description, $Search, $useNumber = false)
    {

        // Automatic Chapter Number
        if ($useNumber) {
            $NumberList = range('1', '99', 1);
            $Number = $NumberList[count($this->Directory) - 1];
            $Title = $Number.'. '.$Title;
        } else {
            $Number = null;
        }

        $Page = new Page($Title, $Description, $Search, $Number);
        if (!Book::getCurrentPage()) {
            Book::setCurrentPage($Page->getHash());
        }
        if (Book::getCurrentPage() == $Page->getHash()) {
            array_push($this->PageList, $Page);
            array_push($this->Directory, new ChevronRight().' '.new Bold($Title.' '.new Muted($Description)));
        } else {
            array_push(
                $this->Directory,
                new Link($Title.' '.new Muted($Description), $this->getRequest()->getPathInfo(), new TileBig(),
                    array('Chapter' => Book::getCurrentChapter(), 'Page' => $Page->getHash())
                )
            );
        }
        return $Page;
    }

    /**
     * @return string
     */
    public function getHash()
    {

        return sha1($this->Title.$this->Description);
    }

    /**
     * @return array
     */
    public function getDirectory()
    {

        return $this->Directory;
    }
}
