<?php
namespace SPHERE\Common\Frontend\Ajax;

use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\TwigHandler;

/**
 * Class Pipeline
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
class Pipeline
{

    /** @var AbstractEmitter[] $Emitter */
    private $Emitter = array();
    /** @var string $SuccessMessage */
    private $SuccessMessage = '';
    /** @var string $LoadingMessage */
    private $LoadingMessage = 'Bitte warten...';

    /**
     * @param $Message
     * @return $this
     */
    public function setSuccessMessage($Message)
    {
        $this->SuccessMessage = $Message;
        return $this;
    }

    /**
     * @param $Message
     * @return $this
     */
    public function setLoadingMessage($Message)
    {
        $this->LoadingMessage = $Message;
        return $this;
    }

    /**
     * @param AbstractEmitter $AbstractEmitter
     * @return $this
     */
    public function addEmitter(AbstractEmitter $AbstractEmitter)
    {
        array_push($this->Emitter, $AbstractEmitter);
        return $this;
    }

    /**
     * @internal
     * @deprecated
     * @param Pipeline $Pipeline
     * @return $this
     */
    public function addPipeline(Pipeline $Pipeline)
    {
        $this->Emitter = array_merge( $this->Emitter, $Pipeline->getEmitter() );
        return $this;
    }

    /**
     * @param Form $Form
     * @return string
     */
    public function parseScript( Form $Form = null )
    {

//        (new CacheFactory())->createHandler(new TwigHandler())->clearCache();
        $Template = Template::getTemplate(__DIR__ . '/Pipeline.twig');

        $Template->setVariable('NOTIFYHASH', $NotifyHash = sha1(uniqid('',true)) );
        $Template->setVariable('NOTIFYLOADING', $this->LoadingMessage );

        foreach ($this->Emitter as $Index => $Emitter) {

            $Url = $Emitter->getAjaxUri() . $Emitter->getAjaxGetPayload();

            if ($Index !== 0) {
                $Template->setVariable('CALLBACK', '.always(function(){' . file_get_contents(__DIR__ . '/Pipeline.twig') . '})');
                $Template = Template::getTwigTemplateString($Template->getContent());
            }

            if( $Form === null ) {
                if (strlen($Emitter->getAjaxPostPayload()) == 2) {
                    $Method = 'GET';
                } else {
                    $Method = 'POST';
                }
                $Template->setVariable('METHOD', $Method);
                $Template->setVariable('POST', $Emitter->getAjaxPostPayload());
            } else {
                $Template->setVariable('METHOD', 'POST');
                $Template->setVariable('POST', 'jQuery("form#'.$Form->getHash().'").serializeArray()');
            }

            $Template->setVariable('URL', $Url);
            $Template->setVariable('URL_BASE', $Emitter->getAjaxUri());

            $Receiver = $Emitter->getAjaxReceiver();
            $Template->setVariable('ReceiverList', $Receiver);

            $Template->setVariable('RESPONSE', AbstractReceiver::RESPONSE_CONTAINER);
        }

        if( !empty( $this->SuccessMessage ) ) {
            $Template->setVariable('NOTIFY', "
            AjaxNotify".$NotifyHash.".update({progress: 95});
            AjaxNotify".$NotifyHash.".update({
                message: '" . $this->SuccessMessage . "',
                type: 'success',
            }); 
            setTimeout(function() {
                AjaxNotify".$NotifyHash.".update({progress: 100});
                AjaxNotify".$NotifyHash.".close();
            }, 2000);
            ");
        } else {
            $Template->setVariable('NOTIFY', "
                AjaxNotify".$NotifyHash.".update({progress: 100});
                AjaxNotify".$NotifyHash.".close();
            ");
        }

        return $Template->getContent();
    }

    /**
     *
     */
    public function __toString()
    {
        return (string)'<script type="text/javascript">'
            . 'executeScript(function() {'
            . 'Client.Use("ModAlways", function(){'
            . $this->parseScript()
            . '});'
            . '});'
            . '</script>';
    }

    /**
     * @return AbstractEmitter[]
     */
    public function getEmitter()
    {
        return $this->Emitter;
    }
}