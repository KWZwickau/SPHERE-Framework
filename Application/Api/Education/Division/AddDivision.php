<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Extension\Extension;

/**
 * Class AddDivision
 *
 * @package SPHERE\Application\Api\Education\Division
 */
class AddDivision extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('reloadLevelNameInput');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFormSelect($Content = '')
    {
        return new BlockReceiver($Content);
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineCreateLevelNameInput(AbstractReceiver $Receiver)
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, self::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            self::API_TARGET => 'reloadLevelNameInput'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);

        return $FieldPipeline;
    }

    /**
     * @param array $Data
     *
     * @return AbstractField|string
     */
    public function reloadLevelNameInput()
    {
        // todo über neuen Namen oder Kürzel gehen nicht über Id, steckt allerdings noch in einem anderen Ticket
        if (isset($_POST['Level']['Type']) && ($_POST['Level']['Type'] == 9)) {
            return '';
        } else {
            return (new TextField('Level[Name]', 'z.B: 5', 'Klassenstufe (Nummer)', new Pencil()))->setRequired();
        }
    }
}