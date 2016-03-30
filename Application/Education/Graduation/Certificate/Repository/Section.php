<?php
namespace SPHERE\Application\Education\Graduation\Certificate\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;

class Section
{

    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var string $Content */
    private $Columns = '';

    /**
     * Element constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<table class="Section"><tbody><tr>{{ Columns }}</tr></tbody></table>');
    }

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

        $this->Template->setVariable('Columns', implode(' ', $this->Columns));
        return $this->Template->getContent();
    }

    /**
     * @param Element $Element
     * @param string  $Width
     *
     * @return Section
     */
    public function addElementColumn(Element $Element, $Width = 'auto')
    {

        $this->Columns[] = '<td style="width: '.$Width.' !important;">'.$Element.'</td>';
        return $this;
    }

    /**
     * @param Slice  $Slice
     * @param string $Width
     *
     * @return Section
     */
    public function addSliceColumn(Slice $Slice, $Width = 'auto')
    {

        $this->Columns[] = '<td style="width: '.$Width.' !important;">'.$Slice.'</td>';
        return $this;
    }
}
