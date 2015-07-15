<?php
namespace MOC\V\Component\Template\Component\Bridge\Repository;

use MOC\V\Component\Template\Component\Bridge\Bridge;
use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Component\Parameter\Repository\FileParameter;

/**
 * Class TwigTemplate
 *
 * @package MOC\V\Component\Template\Component\Bridge
 */
class TwigTemplate extends Bridge implements IBridgeInterface
{

    /** @var null|\Twig_Environment $Instance */
    private $Instance = null;
    /** @var null|\Twig_Template $Template */
    private $Template = null;

    /**
     *
     */
    function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/Twig/lib/Twig/Autoloader.php' );
        \Twig_Autoloader::register();
    }

    /**
     * @param string $String
     * @param bool   $Reload
     *
     * @return IBridgeInterface
     */
    public function loadString( $String, $Reload = false )
    {

        $TemplateName = 'Source'.sha1( $String ).'.twig';
        if (!file_exists( __DIR__.'/TwigTemplate/'.$TemplateName )) {
            file_put_contents( __DIR__.'/TwigTemplate/'.$TemplateName, $String );
        }
        return $this->loadFile( new FileParameter( __DIR__.'/TwigTemplate/'.$TemplateName ), $Reload );
    }

    /**
     * @param FileParameter $Location
     * @param bool          $Reload
     *
     * @return IBridgeInterface
     */
    public function loadFile( FileParameter $Location, $Reload = false )
    {

        $this->Instance = new \Twig_Environment(
            new \Twig_Loader_Filesystem( array( dirname( $Location->getFile() ) ) ),
            array( 'auto_reload' => $Reload, 'autoescape' => false, 'cache' => realpath( __DIR__.'/TwigTemplate' ) )
        );
        $this->Instance->addFilter( new \Twig_SimpleFilter( 'utf8_encode', 'utf8_encode' ) );
        $this->Instance->addFilter( new \Twig_SimpleFilter( 'utf8_decode', 'utf8_decode' ) );
        $this->Template = $this->Instance->loadTemplate( basename( $Location->getFile() ) );
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->render( $this->VariableList );
    }

}
