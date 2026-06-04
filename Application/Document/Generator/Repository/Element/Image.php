<?php
namespace SPHERE\Application\Document\Generator\Repository\Element;

use SPHERE\Application\Document\Generator\Repository\Element;

class Image extends Element
{

    public function __construct($Location, $Width = 'auto', $Height = 'auto', $Opacity = 1.0)
    {

        parent::__construct();

        if (!defined("DOMPDF_ENABLE_REMOTE")) {
            define("DOMPDF_ENABLE_REMOTE", true);
        }

        $this->setContent('<img src="'.$this->getPdfImage($Location).'" style="width: '.$Width.' !important; height: '.$Height.' !important; opacity: '
            . $Opacity . '" />');
    }

    private function getPdfImage($Location)
    {

        // Bild direkt von der Platte als Data-URI einbetten (kein HTTP/Auth/SSL nötig)
        if (($dataUri = $this->getLocalDataUri($Location))) {
            return $dataUri;
        }

        $ProtocolSecure = 'http://';
        if(strpos($this->getRequest()->getHost(), 'schulsoftware.schule')){
            $ProtocolSecure = 'https://';
        }

        $PathBase = $this->getRequest()->getPathBase();
        if (empty($PathBase)) {
            $PathBase = $ProtocolSecure.$_SERVER['SERVER_NAME'];
        }

        return $PathBase.'/'.trim($Location, '/\\');
    }

    private function getLocalDataUri($Location)
    {

        // __DIR__ = Application/Document/Generator/Repository/Element → 5 Ebenen bis Projekt-Root
        $filePath = realpath(__DIR__.'/../../../../../'.trim($Location, '/\\'));
        if ($filePath === false || !is_readable($filePath)) {
            return null;
        }

        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false || $content === '') {
            return null;
        }

        return 'data:'.$imageInfo['mime'].';base64,'.base64_encode($content);
    }
}
