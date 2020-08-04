<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionApplication;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class SubjectTeacher
 *
 * @package SPHERE\Application\Api\Education\Division
 */
class SubjectTeacher extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method Callable Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('tablePerson');
        $Dispatcher->registerMethod('serviceAddPerson');
        $Dispatcher->registerMethod('serviceRemovePerson');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverUsed($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('UsedReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('ServiceReceiver');
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return array
     */
    public static function getTableContentUsed(TblDivisionSubject $tblDivisionSubject)
    {
        $tblSubjectTeacherAllSelected = DivisionApplication::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
        $usedList = array();
        if ($tblSubjectTeacherAllSelected) {
            array_walk($tblSubjectTeacherAllSelected, function (TblSubjectTeacher $tblSubjectTeacher) use ($tblDivisionSubject, &$usedList) {
                if (($tblPerson = $tblSubjectTeacher->getServiceTblPerson())) {
                    $address = ($tblAddress = $tblPerson->fetchMainAddress())
                        ? $tblAddress->getGuiString()
                        : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');

                    $Item['Id'] = $tblPerson->getId();
                    $Item['DivisionSubjectId'] = $tblDivisionSubject->getId();
                    $Item['SubjectTeacherId'] = $tblSubjectTeacher->getId();
                    $Item['Name'] = $tblPerson->getLastFirstName();
                    $Item['Address'] = $address;

                    array_push($usedList, $Item);
                }
            });
        }

        return $usedList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return array
     */
    public static function getTableContentAvailable(TblDivisionSubject $tblDivisionSubject)
    {
        $tblSubjectTeacherAllSelected = DivisionApplication::useService()->getSubjectTeacherByDivisionSubject($tblDivisionSubject);
        $tblTeacherAllList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        $tblTeacherSelectedList = array();
        if ($tblSubjectTeacherAllSelected){
            foreach ($tblSubjectTeacherAllSelected as $tblSubjectTeacher){
                if ($tblSubjectTeacher->getServiceTblPerson()){
                    $tblTeacherSelectedList[] = $tblSubjectTeacher->getServiceTblPerson();
                }
            }
        }

        if (!empty($tblTeacherSelectedList) && $tblTeacherAllList) {
            $tblTeacherAllList = array_udiff($tblTeacherAllList, $tblTeacherSelectedList,
                function (TblPerson $ObjectA, TblPerson $ObjectB) {
                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        $availableList = array();
        if ($tblTeacherAllList) {
            array_walk($tblTeacherAllList, function (TblPerson $tblPerson) use ($tblDivisionSubject, &$availableList) {
                $Item['Id'] = $tblPerson->getId();
                $Item['DivisionSubjectId'] = $tblDivisionSubject->getId();
                $Item['Name'] = $tblPerson->getLastFirstName();
                $Item['Address'] = ($tblAddress = $tblPerson->fetchMainAddress())
                    ? $tblAddress->getGuiString()
                    : new \SPHERE\Common\Frontend\Text\Repository\Warning('Keine Adresse hinterlegt');
                array_push($availableList, $Item);
            });
        }
        return $availableList;
    }

    /**
     * @param null $DivisionSubjectId
     *
     * @return Layout
     */
    public static function tablePerson($DivisionSubjectId = null)
    {
        // get Content
        $tblDivisionSubject = DivisionApplication::useService()->getDivisionSubjectById($DivisionSubjectId);
        $ContentList = false;
        $ContentListAvailable = false;
        if ($tblDivisionSubject) {
            $ContentList = self::getTableContentUsed($tblDivisionSubject);
            $ContentListAvailable = self::getTableContentAvailable($tblDivisionSubject);
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Person) {
                    $Table[] = array(
                        'Name' => $Person['Name'],
                        'Address' => $Person['Address'],
                        'Option' => (new Standard('', self::getEndpoint(), new MinusSign(), array(), 'Entfernen'))
                            ->ajaxPipelineOnClick(self::pipelineMinus($Person['DivisionSubjectId'], $Person['SubjectTeacherId']))
                    );
                }
                // Anzeige
                $left = (new TableData($Table, new Title('Ausgewählte', 'Lehrer'), array(
                    'Name' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'SubjectTeacherSelect' . 'Selected');
            } else {
                $left = new Info('Keine Lehrer ausgewählt');
            }
        } else {
            $left = new Warning('Fach-Klasse nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Person) {
                    $TableAvailable[] = array(
                        'Name' => $Person['Name'],
                        'Address' => $Person['Address'],
                        'Option' => (new Standard('', self::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($Person['Id'], $Person['DivisionSubjectId']))
                    );
                }
                // Anzeige
                $right = (new TableData($TableAvailable, new Title('Verfügbare', 'Lehrer'), array(
                    'Name' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                        ),
                        'responsive' => false
                    )
                ))->setHash(__NAMESPACE__ . 'SubjectTeacherSelect' . 'Available');
            } else {
                $right = new Info('Keine weiteren Lehrer verfügbar');
            }
        } else {
            $right = new Warning('Fach-Klasse nicht gefunden');
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $left
                    , 6),
                new LayoutColumn(
                    $right
                    , 6)
            ))));
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $SubjectTeacherId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($DivisionSubjectId = null, $SubjectTeacherId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemovePerson',
            'SubjectTeacherId' => $SubjectTeacherId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePerson',
            'DivisionSubjectId' => $DivisionSubjectId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $SubjectTeacherId
     */
    public function serviceRemovePerson($SubjectTeacherId = null)
    {
        if (($tblSubjectTeacher = DivisionApplication::useService()->getSubjectTeacherById($SubjectTeacherId))) {
            DivisionApplication::useService()->removeSubjectTeacher($tblSubjectTeacher);
        }
    }

    /**
     * @param null $Id
     * @param null $DivisionSubjectId
     *
     * @return Pipeline
     */
    public static function pipelinePlus($Id = null, $DivisionSubjectId = null)
    {
        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceAddPerson',
            'Id' => $Id,
            'DivisionSubjectId' => $DivisionSubjectId
        ));
        $Pipeline->appendEmitter($Emitter);

        // refresh Table
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'tablePerson',
            'DivisionSubjectId' => $DivisionSubjectId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param null $Id
     * @param null $DivisionSubjectId
     */
    public function serviceAddPerson($Id = null, $DivisionSubjectId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($Id))
            && ($tblDivisionSubject = DivisionApplication::useService()->getDivisionSubjectById($DivisionSubjectId))
        ) {
            DivisionApplication::useService()->addSubjectTeacher($tblDivisionSubject, $tblPerson);
        }
    }
}