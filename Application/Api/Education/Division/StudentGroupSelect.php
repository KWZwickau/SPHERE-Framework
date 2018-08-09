<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.06.2017
 * Time: 08:22
 */

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionApplication;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

/**
 * Class StudentGroupSelect
 *
 * @package SPHERE\Application\Api\Education\Division
 */
class StudentGroupSelect extends Extension implements IApiInterface
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
    public static function receiverAvailable($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('AvailableReceiver');
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

        $tblSubjectStudentList = DivisionApplication::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject);
        $tblDivision = $tblDivisionSubject->getTblDivision();

        $usedList = array();
        if ($tblSubjectStudentList && $tblDivision) {
            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                if (($tblPerson = $tblSubjectStudent->getServiceTblPerson())) {
                    $usedList[] = array(
                        'Id' => $tblPerson->getId(),
                        'Name' => $tblPerson->getLastFirstName(),
                    );
                }
            }
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

        $tblPersonUsedList = array();
        if ($tblSubjectStudentList = DivisionApplication::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubject)) {
            foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                if ($tblSubjectStudent->getServiceTblPerson()) {
                    $tblPersonUsedList[] = $tblSubjectStudent->getServiceTblPerson();
                }
            }
        }

        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblStudentList = DivisionApplication::useService()->getStudentAllByDivision($tblDivision))
        ) {
            $tblStudentList = array_udiff($tblStudentList, $tblPersonUsedList,
                function (TblPerson $tblPersonA, TblPerson $tblPersonB) {
                    return $tblPersonA->getId() - $tblPersonB->getId();
                });
        } else {
            $tblStudentList = false;
        }

        $availableList = array();
        if ($tblStudentList) {
            /** @var TblPerson $tblPerson */
            foreach ($tblStudentList as $tblPerson) {
                $availableList[] = array(
                    'Id' => $tblPerson->getId(),
                    'Name' => $tblPerson->getLastFirstName(),
                );
            }
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
        $personAdvancedCourses = array();
        if ($tblDivisionSubject) {
            $ContentList = self::getTableContentUsed($tblDivisionSubject);
            $ContentListAvailable = self::getTableContentAvailable($tblDivisionSubject);

            if (($tblDivision = $tblDivisionSubject->getTblDivision())
                && ($tblDivisionSubjectList = DivisionApplication::useService()->getDivisionSubjectByDivision($tblDivision))) {
                foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                    if (($tblSubjectGroup = $tblDivisionSubjectItem->getTblSubjectGroup())
                        && $tblSubjectGroup->isAdvancedCourse()
                    ) {
                        if (($tblSubjectStudentList = DivisionApplication::useService()->getSubjectStudentByDivisionSubject(
                            $tblDivisionSubjectItem))
                        ) {
                            foreach ($tblSubjectStudentList  as $tblSubjectStudent) {
                                if (($tblSubject = $tblDivisionSubjectItem->getServiceTblSubject())
                                    && ($tblPerson = $tblSubjectStudent->getServiceTblPerson())
                                ) {
                                    if ($tblSubject->getName() == 'Deutsch' || $tblSubject->getName() == 'Mathematik') {
                                        $personAdvancedCourses[0][$tblPerson->getId()] = $tblSubject->getAcronym();
                                    } else {
                                        $personAdvancedCourses[1][$tblPerson->getId()] = $tblSubject->getAcronym();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Select
        $Table = array();
        if (is_array($ContentList)) {
            if (!empty($ContentList)) {
                foreach ($ContentList as $Person) {
                    $Table[] = array(
                        'Name' => $Person['Name'],
                        'AdvancedCourse1' => isset($personAdvancedCourses[0][$Person['Id']])
                            ? $personAdvancedCourses[0][$Person['Id']] : '',
                        'AdvancedCourse2' => isset($personAdvancedCourses[1][$Person['Id']])
                            ? $personAdvancedCourses[1][$Person['Id']] : '',
                        'Option' => (new Standard('', self::getEndpoint(), new MinusSign(),
                            array('Id' => $Person['Id'], 'DivisionSubjectId' => $DivisionSubjectId), 'Entfernen'))
                            ->ajaxPipelineOnClick(self::pipelineMinus($Person['Id'], $DivisionSubjectId))
                    );
                }
                // Anzeige
                $left = (new TableData($Table, new Title('Ausgewählte', 'Schüler'), array(
                    'Name' => 'Name',
                    'AdvancedCourse1' => '1. LK',
                    'AdvancedCourse2' => '2. LK',
                    'Option' => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('width' => '1%', 'orderable' => false, 'targets' => -1),
                        ),
                    )
                ))->setHash(__NAMESPACE__ . 'StudentGroupSelect' . 'Selected');
            } else {
                $left = new Info('Keine Schüler ausgewählt');
            }
        } else {
            $left = new Warning('Klasse nicht gefunden');
        }

        // Select
        $TableAvailable = array();
        if (is_array($ContentListAvailable)) {
            if (!empty($ContentListAvailable)) {
                foreach ($ContentListAvailable as $Person) {
                    $TableAvailable[] = array(
                        'Name' => $Person['Name'],
                        'AdvancedCourse1' => isset($personAdvancedCourses[0][$Person['Id']])
                            ? $personAdvancedCourses[0][$Person['Id']] : '',
                        'AdvancedCourse2' => isset($personAdvancedCourses[1][$Person['Id']])
                            ? $personAdvancedCourses[1][$Person['Id']] : '',
                        'Option' => (new Standard('', self::getEndpoint(), new PlusSign(),
                            array('Id' => $Person['Id'], 'DivisionSubjectId' => $DivisionSubjectId), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($Person['Id'], $DivisionSubjectId))
                    );
                }
                // Anzeige
                $right = (new TableData($TableAvailable, new Title('Verfügbare', 'Schüler'), array(
                    'Name' => 'Name',
                    'AdvancedCourse1' => '1. LK',
                    'AdvancedCourse2' => '2. LK',
                    'Option' => ''
                ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('width' => '1%', 'orderable' => false, 'targets' => array(-1)),
                        ),
                    )
                ))->setHash(__NAMESPACE__ . 'StudentGroupSelect' . 'Available');
            } else {
                $right = new Info('Keine weiteren Schüler verfügbar');
            }
        } else {
            $right = new Warning('Klasse nicht gefunden');
        }

        if ($tblDivisionSubject) {
            $layoutGroup = new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Fach - Gruppe', array(
                            'Fach: ' . new Bold($tblDivisionSubject->getServiceTblSubject()
                                ? $tblDivisionSubject->getServiceTblSubject()->getName() : ''),
                            'Gruppe: ' . new Bold($tblDivisionSubject->getTblSubjectGroup()->getName())
                        ), Panel::PANEL_TYPE_INFO)
                    )
                )
            );
        } else {
            $layoutGroup = new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Warning('Fach - Gruppe nicht gefunden', new Ban())
                    )
                )
            );
        }

        return
            new Layout(array(
                $layoutGroup,
                new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $left
                    , 6),
                new LayoutColumn(
                    $right
                    , 6)
            )))));

    }

    /**
     * @param null $Id
     * @param null $DivisionSubjectId
     *
     * @return Pipeline
     */
    public static function pipelineMinus($Id = null, $DivisionSubjectId = null)
    {

        $Pipeline = new Pipeline();

        // execute Service
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'serviceRemovePerson',
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
    public function serviceRemovePerson($Id = null, $DivisionSubjectId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($Id))
            && ($tblDivisionSubject = DivisionApplication::useService()->getDivisionSubjectById($DivisionSubjectId))
            && ($tblSubjectStudent = DivisionApplication::useService()->getSubjectStudentByDivisionSubjectAndPerson(
                $tblDivisionSubject, $tblPerson))
        ) {

            DivisionApplication::useService()->removeSubjectStudent($tblSubjectStudent);
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

            DivisionApplication::useService()->addSubjectStudentData($tblDivisionSubject, $tblPerson);
        }
    }
}