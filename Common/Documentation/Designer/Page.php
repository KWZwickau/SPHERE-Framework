<?php
namespace SPHERE\Common\Documentation\Designer;

use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;

/**
 * Class Page
 *
 * @package SPHERE\Common\Documentation\Designer
 */
class Page
{

    /** @var array $ElementList */
    private $ElementList = array();

    private $Title = '{{ Title }}';
    private $Description = '{{ Description }}';

    /**
     * @param string      $Title
     * @param string      $Description
     * @param null|string $Search
     */
    public function __construct($Title, $Description = '', $Search = null)
    {

        $this->Title = $Title;
        $this->Description = $Description;
        $this->Search = $Search;
    }

    /**
     * @param string $Title
     * @param string $Description
     *
     * @return Page
     */
    public function addHeadline($Title, $Description = '')
    {

        $Element = new Headline(new TagList().' '.$this->markSearch($Title), $this->markSearch($Description));
        array_push($this->ElementList, $Element);
        return $this;
    }

    /**
     * @param string $Text
     *
     * @return string
     */
    private function markSearch($Text)
    {

        if ($this->Search) {
            $Pattern = explode(' ', $this->Search);
            foreach ($Pattern as $Index => $Word) {
                $Pattern[$Index] = preg_quote($Word, '!');
            }
            $Text = preg_replace('!('.implode('|', $Pattern).')!is',
                '<span style="background: #FFCC00; padding: 1px;">$1</span>', $Text);
        }
        return $Text;
    }

    /**
     * @param string $Text
     *
     * @return Page
     */
    public function addParagraph($Text)
    {

        $Text = preg_replace('!^-\s!is', new Unchecked().'&nbsp;', $Text);
        $Element = new Paragraph($Text);
        array_push($this->ElementList, $this->markSearch($Element));
        return $this;
    }

    /**
     * @param string|array $Code
     * @param string       $Description
     *
     * @return Page
     */
    public function addCode($Code, $Description = '')
    {

        if (!is_array($Code)) {
            $Code = array($Code);
        }
        foreach ((array)$Code as $Line => $Value) {
            $Code[$Line] = preg_replace('!\t!is', '    ', $Value);
        }
        $Element = '<pre><code class="php">'.$this->markSearch(implode("\n", $Code)).'</code>'
            .( $Description ? '<hr/>'.new Small(new Muted($Description)) : '' )
            .'</pre>';
        array_push($this->ElementList, $Element);
        return $this;
    }

    /**
     * @return Page
     */
    public function addSeparator()
    {

        $Element = '<hr/>';
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

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        implode('', $this->ElementList)
                    )
                )), new Title(new TileBig().' '.$this->markSearch($this->Title), $this->markSearch($this->Description))
            )
        );
    }

    /**
     * @return string
     */
    public function getHash()
    {

        return sha1($this->Title.$this->Description);
    }
}
