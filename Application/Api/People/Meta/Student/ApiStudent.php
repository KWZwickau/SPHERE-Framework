<?php

namespace SPHERE\Application\Api\People\Meta\Student;


use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiStudent extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('compareStudentIdentifier');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return InlineReceiver
     */
    public static function receiverControlIdentifier($Content = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('StudentNumber');
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCompareIdentifier($PersonId)
    {
        $ComparePasswordPipeline = new Pipeline(false);
        $ComparePasswordEmitter = new ServerEmitter(ApiStudent::receiverControlIdentifier(), ApiStudent::getEndpoint());
        $ComparePasswordEmitter->setGetPayload(array(
            ApiStudent::API_TARGET => 'compareStudentIdentifier'
        ));
        $ComparePasswordEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $ComparePasswordPipeline->appendEmitter($ComparePasswordEmitter);

        return $ComparePasswordPipeline;
    }

    /**
     * @param array $Meta
     * @param       $PersonId
     *
     * @return string
     */
    public function compareStudentIdentifier($Meta, $PersonId)
    {

        if (isset($Meta['Student']['Identifier']) && ($Identifier = $Meta['Student']['Identifier'])) {
            $tblStudent = Student::useService()->getStudentByIdentifier($Identifier);
            if ($tblStudent) {
                $tblPerson = $tblStudent->getServiceTblPerson();
                // Eigene Schülernummer ignorieren
                if ($tblPerson && $PersonId == $tblPerson->getId()) {
                    return '';
                }
                // Person ausgeben die der Schülernummer entspricht
                $PersonString = '';
                if ($tblPerson) {
                    $PersonString = $tblPerson->getLastFirstName();
                }
                return new Danger(new Bold('Die Schülernummer ist bereits vergeben: '.$PersonString
//                    .new Container()
                    .new Container('Es erfolgt keine Speicherung der Schülernummer.')));
            }
        }
        return '';
    }
}