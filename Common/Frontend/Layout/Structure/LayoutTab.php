<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutTab
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutTab extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param string     $TabName
     * @param int|string $TabParameter
     * @param array      $Data
     */
    public function __construct($TabName, $TabParameter, $Data = array())
    {

        $this->Template = $this->getTemplate(__DIR__.'/LayoutTab.twig');

        $this->Template->setVariable('TabName', $TabName);
        $this->Template->setVariable('TabParameter', '?'.http_build_query((new Authenticator(new Get()))
                ->getAuthenticator()->createSignature(
                    array_merge(array('TabActive' => $TabParameter), $Data), $this->getRequest()->getPathInfo()
                ))
        );
        $this->Template->setVariable('TabRoute', $this->getRequest()->getPathInfo());

        $Global = $this->getGlobal();
        if (isset( $Global->GET['TabActive'] )) {
            if ($Global->GET['TabActive'] == $TabParameter) {
                $this->Template->setVariable('TabActive', true);
            } else {
                $this->Template->setVariable('TabActive', false);
            }
        }
    }

    /**
     * @return LayoutTab
     */
    public function setActive()
    {

        $this->Template->setVariable('TabActive', true);
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

        return $this->Template->getContent();
    }
}
