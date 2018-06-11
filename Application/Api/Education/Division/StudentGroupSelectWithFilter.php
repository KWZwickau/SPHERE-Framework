<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2018
 * Time: 16:29
 */

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionApplication;
use SPHERE\Application\Education\Lesson\Division\Filter\Filter;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
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
 * Class StudentGroupSelectWithFilter
 *
 * @package SPHERE\Application\Api\Education\Division
 */
class StudentGroupSelectWithFilter extends Extension implements IApiInterface
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
     * @param null $DivisionSubjectId
     * @param null $Filtered
     *
     * @return Layout
     */
    public static function tablePerson($DivisionSubjectId = null, $Filtered = null)
    {

        // get Content
        $tblDivisionSubject = DivisionApplication::useService()->getDivisionSubjectById($DivisionSubjectId);
        if ($tblDivisionSubject && ($tblDivision = $tblDivisionSubject->getTblDivision())) {
            // filter
            $filter = new Filter($Filtered, $tblDivision);
            $header = array(
                'Name' => 'Name'
            );
            $filterHeader = $filter->getHeader();
            if (!empty($filterHeader)) {
                $header = array_merge($header, $filterHeader);
            }
            // SekII zusätzliche Anzeige der Leistungskurse
            $isSekII = false;
            $personAdvancedCourses = array();
            if (($levelName = $filter->getLevelName())
                && $levelName == '11' || $levelName == '12'
            ) {
                $isSekII = true;

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

            if ($isSekII) {
                $header['AdvancedCourse1'] = '1. LK';
                $header['AdvancedCourse2'] = '2. LK';
            }
            $header['Option'] = ' ';

            // left selected persons
            if (($tblPersonSelectedList = DivisionApplication::useService()->getStudentByDivisionSubject($tblDivisionSubject))) {
                $tableSelected = array();
                foreach ($tblPersonSelectedList as $tblPerson) {
                    $item = array();
                    $item['Name'] = $tblPerson->getLastFirstName();

                    if ($filter->getTblGroup()) {
                        $item['Group'] = $filter->getTblGroup()->getName();
                    }
                    if ($filter->getTblGender()) {
                        $item['Gender'] = $filter->getTblGenderStringByPerson($tblPerson);
                    }
                    if ($filter->getTblCourse()) {
                        $item['Course'] = $filter->getTblCourseStringByPerson($tblPerson);
                    }
                    if ($filter->getTblSubjectOrientation()) {
                        $item['SubjectOrientation'] = $filter->getTblSubjectOrientationStringByPerson($tblPerson);
                    }
                    if ($filter->getTblSubjectProfile()) {
                        $item['SubjectProfile'] = $filter->getTblSubjectProfileStringByPerson($tblPerson);
                    }
                    if ($filter->getTblSubjectForeignLanguage()) {
                        $item['SubjectForeignLanguage'] = $filter->getTblSubjectForeignLanguagesStringByPerson($tblPerson);
                    }
                    if ($filter->getTblSubjectReligion()) {
                        $item['SubjectReligion'] = $filter->getTblSubjectReligionStringByPerson($tblPerson);
                    }
                    if ($filter->getTblSubjectElective()) {
                        $item['SubjectElective'] = $filter->getTblSubjectElectivesStringByPerson($tblPerson);
                    }

                    if ($isSekII) {
                        $item['AdvancedCourse1'] = isset($personAdvancedCourses[0][$tblPerson->getId()])
                            ? $personAdvancedCourses[0][$tblPerson->getId()] : '';
                        $item['AdvancedCourse2'] = isset($personAdvancedCourses[1][$tblPerson->getId()])
                            ? $personAdvancedCourses[1][$tblPerson->getId()] : '';
                    }

                    $item['Option'] = (new Standard('', self::getEndpoint(), new MinusSign(),
                        array('Id' => $tblPerson->getId(), 'DivisionSubjectId' => $DivisionSubjectId), 'Entfernen'))
                        ->ajaxPipelineOnClick(self::pipelineMinus($tblPerson->getId(), $DivisionSubjectId));

                    $tableSelected[$tblPerson->getId()] = $item;
                }

                $left = (new TableData($tableSelected, new Title('Ausgewählte', 'Schüler'), $header,
                    array(
                        'columnDefs' => array(
                            array('width' => '1%', 'targets' => array(-1))
                        ),
                    )
                ))->setHash(__NAMESPACE__ . 'StudentGroupSelectWithFilter' . 'Selected');
            } else {
                $left = new Info('Keine Schüler ausgewählt');
            }

            // right available persons
            $tableAvailable = array();
            if (($tblPersonList = DivisionApplication::useService()->getStudentAllByDivision($tblDivision))) {
                foreach ($tblPersonList as $tblPerson) {
                    if (!isset($tableSelected[$tblPerson->getId()])
                        && $filter->isFilterFulfilledByPerson($tblPerson)
                    ) {
                        $item = array();
                        $item['Name'] = $tblPerson->getLastFirstName();

                        if ($filter->getTblGroup()) {
                            $item['Group'] = $filter->getTblGroup()->getName();
                        }
                        if ($filter->getTblGender()) {
                            $item['Gender'] = $filter->getTblGenderStringByPerson($tblPerson);
                        }
                        if ($filter->getTblCourse()) {
                            $item['Course'] = $filter->getTblCourseStringByPerson($tblPerson);
                        }
                        if ($filter->getTblSubjectOrientation()) {
                            $item['SubjectOrientation'] = $filter->getTblSubjectOrientationStringByPerson($tblPerson);
                        }
                        if ($filter->getTblSubjectProfile()) {
                            $item['SubjectProfile'] = $filter->getTblSubjectProfileStringByPerson($tblPerson);
                        }
                        if ($filter->getTblSubjectForeignLanguage()) {
                            $item['SubjectForeignLanguage'] = $filter->getTblSubjectForeignLanguagesStringByPerson($tblPerson);
                        }
                        if ($filter->getTblSubjectReligion()) {
                            $item['SubjectReligion'] = $filter->getTblSubjectReligionStringByPerson($tblPerson);
                        }
                        if ($filter->getTblSubjectElective()) {
                            $item['SubjectElective'] = $filter->getTblSubjectElectivesStringByPerson($tblPerson);
                        }

                        if ($isSekII) {
                            $item['AdvancedCourse1'] = isset($personAdvancedCourses[0][$tblPerson->getId()])
                                ? $personAdvancedCourses[0][$tblPerson->getId()] : '';
                            $item['AdvancedCourse2'] = isset($personAdvancedCourses[1][$tblPerson->getId()])
                                ? $personAdvancedCourses[1][$tblPerson->getId()] : '';
                        }

                        $item['Option'] = (new Standard('', self::getEndpoint(), new PlusSign(),
                            array('Id' => $tblPerson->getId(), 'DivisionSubjectId' => $DivisionSubjectId), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($tblPerson->getId(), $DivisionSubjectId));

                        $tableAvailable[$tblPerson->getId()] = $item;
                    }
                }
            }
            if (empty($tableAvailable)) {
                $right = new Info('Keine weiteren Schüler verfügbar');
            } else {
                $right = (new TableData($tableAvailable, new Title('Verfügbare', 'Schüler'), $header,
                    array(
                        'columnDefs' => array(
                            array('width' => '1%', 'targets' => array(-1))
                        ),
                    )
                ))->setHash(__NAMESPACE__ . 'StudentGroupSelectWithFilter' . 'Available');
            }

        } else {
            $left = new Warning('Klasse nicht gefunden');
            $right = new Warning('Klasse nicht gefunden');
        }

        return
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn($left, 6),
                        new LayoutColumn($right, 6)
                    ))
                ))
            ));
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