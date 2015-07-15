<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperSizeParameter;
use MOC\V\Component\Template\Component\IBridgeInterface as IBridgeInterface_Template;

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
     *
     */
    function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/DomPdf/0.6.1/dompdf_config.inc.php' );

        $this->setPaperSizeParameter( new PaperSizeParameter() );
        $this->setPaperOrientationParameter( new PaperOrientationParameter() );
    }

    /**
     * @param PaperSizeParameter $PaperSize
     *
     * @return IBridgeInterface
     */
    public function setPaperSizeParameter( PaperSizeParameter $PaperSize )
    {

        return parent::setPaperSizeParameter( $PaperSize );
    }

    /**
     * @param PaperOrientationParameter $PaperOrientation
     *
     * @return IBridgeInterface
     */
    public function setPaperOrientationParameter( PaperOrientationParameter $PaperOrientation )
    {

        return parent::setPaperOrientationParameter( $PaperOrientation );
    }

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile( FileParameter $Location )
    {

        $this->setFileParameter( $Location );
        return $this;
    }

    /**
     * @param IBridgeInterface_Template $Template
     *
     * @return IBridgeInterface
     */
    public function setContent( IBridgeInterface_Template $Template )
    {

        $this->Source = $Template->getContent();
        return $this;
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function saveFile( FileParameter $Location = null )
    {

        $Content = $this->getContent();
        if (null === $Location) {
            file_put_contents( $this->getFileParameter()->getFile(), $Content );
        } else {
            file_put_contents( $Location->getFile(), $Content );
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $Renderer = new \DOMPDF();
        $Renderer->load_html( $this->Source );
        $Renderer->set_paper(
            $this->getPaperSizeParameter()->getSize(),
            $this->getPaperOrientationParameter()->getOrientation()
        );
        $Renderer->render();
        return $Renderer->output();
    }

}
