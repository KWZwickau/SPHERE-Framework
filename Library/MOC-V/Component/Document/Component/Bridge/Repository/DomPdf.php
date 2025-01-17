<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Template\Component\IBridgeInterface as IBridgeInterface_Template;

use Dompdf\Dompdf as DompdfParser;
//use \DOMPDF as DompdfParser;

/**
 * Class DomPdf
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository
 */
class DomPdf extends Bridge implements IBridgeInterface
{

    /** @var string $Source */
    private $Source = '';

    /**
     * DomPdf constructor.
     */
    public function __construct()
    {

//        require_once( __DIR__.'/../../../Vendor/DomPdf/0.6.2/dompdf_config.inc.php' );
//        require_once( __DIR__.'/../../../Vendor/DomPdf/1.0.2/autoload.inc.php' );
        require_once(__DIR__.DIRECTORY_SEPARATOR.'../../../../../Php8Combined/vendor/autoload.php');
    }

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location)
    {

        $this->setFileParameter($Location);
        return $this;
    }

    /**
     * @param IBridgeInterface_Template $Template
     *
     * @return IBridgeInterface
     */
    public function setContent(IBridgeInterface_Template $Template)
    {

        $this->Source = $Template->getContent();
        return $this;
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function saveFile(FileParameter $Location = null)
    {

        $Content = $this->getContent();
        if (null === $Location) {
            file_put_contents($this->getFileParameter()->getFile(), $Content);
        } else {
            file_put_contents($Location->getFile(), $Content);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $Renderer = new DompdfParser();
        $Options = $Renderer->getOptions();
        $Options->set('isRemoteEnabled', true);
        $Renderer->setOptions($Options);
//        $Renderer = new DompdfParserNew();
//        $Renderer->set_option('defaultFont', 'Arial');
//        $Renderer->set_option('isHtml5ParserEnabled', true);
        $Renderer->loadHtml($this->Source);
        $Renderer->setPaper(
            $this->getPaperSizeParameter()->getSize(),
            $this->getPaperOrientationParameter()->getOrientation()
        );
        $Renderer->render();
        return $Renderer->output();
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->Source;
    }
}
