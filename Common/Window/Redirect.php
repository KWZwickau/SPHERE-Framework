<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Debugger\DebuggerFactory;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Redirect
 *
 * @package SPHERE\Common\Window
 */
class Redirect extends Extension implements ITemplateInterface
{

    const TIMEOUT_SUCCESS = 1;
    const TIMEOUT_ERROR = 5;
    const TIMEOUT_WAIT = 15;

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param string $Route
     * @param int    $Timeout
     * @param array  $Data
     */
    public function __construct($Route, $Timeout = Redirect::TIMEOUT_WAIT, $Data = array())
    {

        if ($Route === null) {
            $Route = parse_url($this->getRequest()->getUrl(), PHP_URL_PATH);
            parse_str(parse_url($this->getRequest()->getUrl(), PHP_URL_QUERY), $Query);
            if (isset( $Query['_Sign'] )) {
                unset( $Query['_Sign'] );
            }
            $Data = array_merge($Data, $Query);
        }

        if ((new DebuggerFactory())->createLogger(new ErrorLogger())->isEnabled()) {
            $Timeout = 300;
        }

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
        $this->Template->setVariable('Timeout', ($Timeout < 1 ? $Timeout = 1 : $Timeout));
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
