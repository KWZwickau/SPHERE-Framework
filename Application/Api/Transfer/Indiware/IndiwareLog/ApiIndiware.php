<?php
namespace SPHERE\Application\Api\Transfer\Indiware\IndiwareLog;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\System\Extension\Extension;
// ToDO nach dem Indiware test wieder entfernen
class ApiIndiware extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('reloadFileContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverContent(string $Content = ''): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier('ShowContent');
    }

    /**
     * @param string $fileName
     *
     * @return Pipeline
     */
    public static function pipelineShowFileContent(string $fileName = ''): Pipeline
    {

        $Receiver = Self::receiverContent();
        $FieldPipeline = new Pipeline();
        $FieldEmitter = new ServerEmitter($Receiver, ApiIndiware::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiIndiware::API_TARGET => 'reloadFileContent',
            'fileName' => $fileName
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Lädt...');

        return $FieldPipeline;
    }

    /**
     * @param null $YearId
     * @return SelectBox
     */
    public function reloadFileContent($fileName = '')
    {

        $File = 'UnitTest/IndiwareLog/'.$fileName;
        if ($File) {
//            echo '<pre>';
            $Test =file_get_contents($File);
//            echo '</pre>';
            return print_r('<pre>'.$Test.'</pre>', true);
        }
        return 'Test';
    }
}