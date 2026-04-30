<?php
namespace SPHERE\Application\Api\People\Meta\Liberation;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiLiberation extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openOverViewModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverOverViewModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalLiberationOverViewReceiver');
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenOverViewModal($PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverOverViewModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiLiberation::API_TARGET => 'openOverViewModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $PersonId
     *
     * @return string
     */
    public function openOverViewModal($PersonId)
    {

        if(($tblPerson = Person::useService()->getPersonById($PersonId))
        && ($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
            $tblStudentLiberationList = Student::useService()->getStudentLiberationAllByStudent($tblStudent);
            $Content = '';
            foreach($tblStudentLiberationList as $tblStudentLiberation){
                $DateFrom = $tblStudentLiberation->getDateFrom();
                $DateTo = $tblStudentLiberation->getDateTo();
                $Description = $tblStudentLiberation->getDescription();
                $Type = $Category = '';
                if(($tblStudentLiberationType = $tblStudentLiberation->getTblStudentLiberationType())){
                    $Type = $tblStudentLiberationType->getName();
                    if(($tblStudentLiberationCategory = $tblStudentLiberationType->getTblStudentLiberationCategory())){
                        $Category = $tblStudentLiberationCategory->getName();
                    }
                }
                $Content .= new Headline($Category.' - '.new Bold($Type));
                $Content .= '<div style="height: 8px"></div>';
                $Content .= new Container('Befreiung von: '.($DateFrom ? new Bold($DateFrom) :'---').'&nbsp;&nbsp;'
                    .'bis: '.($DateTo ? new Bold($DateTo) : '---'));
                $Content .= new Container('Beschreibung:');
                $Content .= new Container(($Description ? new Container(nl2br($Description)) : '---'));
            }

            return new Title('Befreiung vom Unterricht')
                .new Layout(new LayoutGroup(new LayoutRow(
                    new LayoutColumn(
                        new Well(
                            new Title($tblPerson->getLastFirstName())
                            .$Content
                        )
                        , 12),
                )));
        } else {
            return new Warning('Person wurde nicht gefunden');
        }
    }
}