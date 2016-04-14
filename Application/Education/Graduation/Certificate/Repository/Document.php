<?php
namespace SPHERE\Application\Education\Graduation\Certificate\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;

class Document
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $Pages */
    private $Pages = array();

    /**
     * Document constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<div class="Document">{{ Pages }}</div>');
    }

    /**
     * @param Page $Page
     *
     * @return $this
     */
    public function addPage(Page $Page)
    {

        $this->Pages[] = $Page;
        return $this;
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

        $this->Template->setVariable('Pages', implode("\n", $this->Pages));
        return $this->Template->getContent();
    }
}
