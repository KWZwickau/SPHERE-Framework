<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class Redirect
 *
 * @package SPHERE\Common\Window
 */
class Redirect extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param string $Route
     * @param int    $Timeout
     * @param array  $Data
     */
    public function __construct($Route, $Timeout = 15, $Data = array())
    {

        if (!empty( $Data )) {
            $Data = '?'.http_build_query(
                    (new Authenticator(new Get()))->getAuthenticator()->createSignature($Data, $Route)
                );
        } else {
            $Data = '';
        }

        // Trim /Client
        $Route = str_replace('/Client', '', $Route);

        $this->Template = $this->getTemplate(__DIR__.'/Redirect.twig');
        $this->Template->setVariable('Route', '/'.trim($Route, '/').$Data);
        $this->Template->setVariable('Timeout', $Timeout);
        $this->Template->setVariable('UrlBase', $this->getRequest()->getUrlBase());

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

        return $this->Template->getContent();
    }


}
