<?php
namespace SPHERE\Common\Frontend\Ajax;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\Ajax\Emitter\AbstractEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ApiEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\LayoutEmitter;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Cache\CacheFactory;
use SPHERE\System\Cache\Handler\TwigHandler;
use SPHERE\System\Extension\Repository\Debugger;

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
        foreach ($this->Emitter as $Index => $Emitter) {
            // ApiEmitter
            if( $Emitter instanceof ApiEmitter ) {
                $Url = $Emitter->getAjaxUri() . $Emitter->getAjaxGetPayload();
                if ($Form === null) {
                    if (strlen($Emitter->getAjaxPostPayload()) == 2) {
                        $Method = 'GET';
                    } else {
                        $Method = 'POST';
                    }
                    $Data = $Emitter->getAjaxPostPayload();
                } else {
                    $Method = 'POST';
                    $Data = 'jQuery("form#' . $Form->getHash() . '").serializeArray()';
                }

                $ReceiverList = $Emitter->getAjaxReceiver();
                $ReceiverContext = array();
                foreach( $ReceiverList as $Receiver ) {
                    $ReceiverContext[] = $Receiver->getHandler();
                }

                /** @var IBridgeInterface $Template */
                if (!isset($Template)) {
//                    var_dump( 'New Line A' );
                    $Template = Template::getTwigTemplateString(
                        'jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, onLoad: { Message: '.json_encode($this->LoadingMessage).' }, onSuccess: { Message: '.json_encode($this->SuccessMessage).' } } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} );'
                    );
                } else {
//                    var_dump( 'Callback Line A' );
                    $Template->setVariable('Callback',
                        'function(){ jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, onLoad: { Message: '.json_encode($this->LoadingMessage).' }, onSuccess: { Message: '.json_encode($this->SuccessMessage).' } } }).loadAjax( {{ Method }}, {{ Url }} , {{ Data }}, {{ Callback }} ); }'
                    );
                    $Template = Template::getTwigTemplateString( $Template->getContent() );
                }

                $Template->setVariable( 'Method', json_encode($Method) );
                $Template->setVariable( 'Url', json_encode($Url) );
                $Template->setVariable( 'Data', json_encode($Data) );
                $Template->setVariable( 'Receiver', json_encode( $ReceiverContext ) );
                $Template->setVariable( 'Hash', json_encode(sha1(json_encode($Method).json_encode($Url).json_encode($Data).json_encode( $ReceiverContext ))) );

//                Debugger::screenDump( $Method, $Url, json_encode($Data), json_encode( $ReceiverContext ) );
            }
            // LayoutEmitter
            if( $Emitter instanceof LayoutEmitter ) {

                $Content = $Emitter->getContent();

                $ReceiverList = $Emitter->getAjaxReceiver();
                $ReceiverContext = array();
                foreach( $ReceiverList as $Receiver ) {
                    $ReceiverContext[] = $Receiver->getHandler();
                }

                /** @var IBridgeInterface $Template */
                if (!isset($Template)) {
//                    var_dump('New Line L');
                    $Template = Template::getTwigTemplateString(
                        'jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, onLoad: { Message: '.json_encode($this->LoadingMessage).' }, onSuccess: { Message: '.json_encode($this->SuccessMessage).' } } }).loadContent( {{ Content }}, {{ Callback }} );'
                    );
                } else {
//                    var_dump( 'Callback Line L' );
                    $Template->setVariable('Callback',
                        'function(){ jQuery().ModAjax({ Receiver: {{ Receiver }}, Notify: { Hash: {{ Hash }}, onLoad: { Message: '.json_encode($this->LoadingMessage).' }, onSuccess: { Message: '.json_encode($this->SuccessMessage).' } } }).loadContent( {{ Content }}, {{ Callback }} ); }'
                    );
                    $Template = Template::getTwigTemplateString( $Template->getContent() );
                }
                $Template->setVariable( 'Content', $Content );
                $Template->setVariable( 'Receiver', json_encode( $ReceiverContext ) );
                $Template->setVariable( 'Hash', json_encode(sha1(json_encode($Content).json_encode($ReceiverContext))) );

//                Debugger::screenDump( $Content, json_encode( $ReceiverContext ) );

            }
        }
        $Template->setVariable('Callback', json_encode(false) );
        return $Template->getContent();
/*
//        (new CacheFactory())->createHandler(new TwigHandler())->clearCache();
        $Template = Template::getTemplate(__DIR__ . '/Pipeline.twig');

        $NotifyHash = sha1(uniqid('',true));

        foreach ($this->Emitter as $Index => $Emitter) {

            // ApiEmitter
            if( $Emitter instanceof ApiEmitter ) {
                $Url = $Emitter->getAjaxUri() . $Emitter->getAjaxGetPayload();

                $Template = $this->loadEmitterPipeline( $Template, $Emitter, $Index, $NotifyHash );

                $Template->setVariable('NOTIFYHASH', $NotifyHash );
                $Template->setVariable('NOTIFYLOADING', $this->LoadingMessage );

                if ($Form === null) {
                    if (strlen($Emitter->getAjaxPostPayload()) == 2) {
                        $Method = 'GET';
                    } else {
                        $Method = 'POST';
                    }
                    $Template->setVariable('METHOD', $Method);
                    $Template->setVariable('POST', $Emitter->getAjaxPostPayload());
                } else {
                    $Template->setVariable('METHOD', 'POST');
                    $Template->setVariable('POST', 'jQuery("form#' . $Form->getHash() . '").serializeArray()');
                }

                $Template->setVariable('URL', $Url);
                $Template->setVariable('URL_BASE', $Emitter->getAjaxUri());

                $Receiver = $Emitter->getAjaxReceiver();
                $Template->setVariable('ReceiverList', $Receiver);

                $Template->setVariable('RESPONSE', AbstractReceiver::RESPONSE_CONTAINER);
            }

            // LayoutEmitter
            if( $Emitter instanceof LayoutEmitter ) {

                $Template = $this->loadEmitterPipeline( $Template, $Emitter, $Index, $NotifyHash );

                $Template->setVariable('NOTIFYHASH', $NotifyHash );
                $Template->setVariable('NOTIFYLOADING', $this->LoadingMessage );

                $Template->setVariable('CONTENT', $Emitter->getContent());

                $Receiver = $Emitter->getAjaxReceiver();
                $Template->setVariable('ReceiverList', $Receiver);

                $Template->setVariable('RESPONSE', AbstractReceiver::RESPONSE_CONTAINER);
            }


        }

        if( !empty( $this->SuccessMessage ) ) {
            $Template->setVariable('NOTIFY', "
            AjaxNotify".$NotifyHash.".update({progress: ".rand(85,95)."});
            AjaxNotify".$NotifyHash.".update({
                message: '" . $this->SuccessMessage . "',
                type: 'success',
            }); 
            setTimeout(function() {
                AjaxNotify".$NotifyHash.".update({progress: 100});
                AjaxNotify".$NotifyHash.".close();
            }, 100);
            ");
        } else {
            $Template->setVariable('NOTIFY', "
                AjaxNotify".$NotifyHash.".update({progress: 100});
                AjaxNotify".$NotifyHash.".close();
            ");
        }

        return $Template->getContent();
*/
    }

    /**
     * @param IBridgeInterface $Template
     * @param AbstractEmitter $Emitter
     * @param int $EmitterIndex
     * @param string $NotifyHash
     * @return IBridgeInterface
     */
    private function loadEmitterPipeline( IBridgeInterface $Template, AbstractEmitter $Emitter, $EmitterIndex, $NotifyHash ) {
        // ApiEmitter
        if( $Emitter instanceof ApiEmitter ) {
            if ($EmitterIndex == 0 || $this->Emitter[$EmitterIndex-1] instanceof LayoutEmitter ) {
                $Template->setVariable('CALLBACK', file_get_contents(__DIR__ . '/PipelineApi.twig'));
                $Template->setVariable('NOTIFYHASH', $NotifyHash);
                return Template::getTwigTemplateString($Template->getContent());
            } elseif( $this->Emitter[$EmitterIndex-1] instanceof ApiEmitter ) {
                $Template->setVariable('CALLBACK', '.always(function(){ ' . file_get_contents(__DIR__ . '/PipelineApi.twig') . ' })');
                $Template->setVariable('NOTIFYHASH', $NotifyHash);
                return Template::getTwigTemplateString($Template->getContent());
            }
        }
        // LayoutEmitter
        if( $Emitter instanceof LayoutEmitter ) {
            if ($EmitterIndex == 0 || $this->Emitter[$EmitterIndex-1] instanceof LayoutEmitter) {
                $Template->setVariable('CALLBACK', file_get_contents(__DIR__ . '/PipelineLayout.twig'));
                $Template->setVariable('NOTIFYHASH', $NotifyHash);
                return Template::getTwigTemplateString($Template->getContent());
            } elseif( $this->Emitter[$EmitterIndex-1] instanceof ApiEmitter ) {
                $Template->setVariable('CALLBACK', '.always(function(){ ' . file_get_contents(__DIR__ . '/PipelineLayout.twig') . ' })');
                $Template->setVariable('NOTIFYHASH', $NotifyHash);
                return Template::getTwigTemplateString($Template->getContent());
            }
        }
        return $Template;
    }

    /**
     *
     */
    public function __toString()
    {
        return (string)'<script type="text/javascript">'
            . 'executeScript(function() {'
            . 'Client.Use("ModAjax", function(){'
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