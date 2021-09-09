<?php
namespace SPHERE\Application\Education\Certificate\Generator\Repository\Element;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;

class Image extends Element
{

    public function __construct($Location, $Width = 'auto', $Height = 'auto')
    {

        parent::__construct();

        if (!defined("DOMPDF_ENABLE_REMOTE")) {
            define("DOMPDF_ENABLE_REMOTE", true);
        }

        $this->setContent('<img src="'.$this->getPdfImage($Location).'" style="width: '.$Width.' !important; height: '.$Height.' !important;"/>');
    }

    private function getPdfImage($Location)
    {

        $ProtocolSecure = 'http://';
        if(strpos($this->getRequest()->getHost(), 'schulsoftware.schule')){
            $ProtocolSecure = 'https://';
        }

        $PathBase = $this->getRequest()->getPathBase();
        if (empty( $PathBase )) {
            $PathBase = $ProtocolSecure.$_SERVER['SERVER_NAME'];
        }
        return $PathBase.'/'.trim($Location, '/\\');
    }
}
