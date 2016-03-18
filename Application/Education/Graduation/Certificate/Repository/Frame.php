<?php
namespace SPHERE\Application\Education\Graduation\Certificate\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;

class Frame
{

    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var array $Data */
    private $Data = array();

    /** @var array $Documents */
    private $Documents = array();

    /**
     * Frame constructor.
     */
    public function __construct()
    {

        $this->Template = Template::getTwigTemplateString('<html><head><style type="text/css">'.file_get_contents(__DIR__.'/../Style.css').'{{ PreviewCss }}</style></head><body>{{ Documents }}</body></html>');
    }

    /**
     * @param Document $Document
     *
     * @return $this
     */
    public function addDocument(Document $Document)
    {

        $this->Documents[] = $Document;
        return $this;
    }

    /**
     * @param array $Data
     *
     * @return $this
     */
    public function setData($Data)
    {

        $this->Data = $Data;
        return $this;
    }

    /**
     * @return IBridgeInterface
     */
    public function getTemplate()
    {

        $Prepare = clone $this->Template;
        $Prepare->setVariable('Documents', implode("\n", $this->Documents));
        $Payload = Template::getTwigTemplateString($Prepare->getContent());
        $Payload->setVariable('Data', $this->Data);
        return $Payload;
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

        $Prepare = clone $this->Template;
        $Prepare->setVariable('PreviewCss', file_get_contents(__DIR__.'/../Preview.css'));
        $Prepare->setVariable('Documents', implode("\n", $this->Documents));
        $Payload = Template::getTwigTemplateString($Prepare->getContent());
        $Payload->setVariable('Data', $this->Data);
        return $Payload->getContent();
    }
}
