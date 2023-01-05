<?php
namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division as DivisionApplication;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Filter\Filter;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
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
        $Dispatcher->registerMethod('getMessage');
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
    public static function receiverMessage($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('MessageReceiver');
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
     *
     * @return Layout
     */
    public static function tablePerson($DivisionSubjectId = null)
    {

        // get Content
        $tblDivisionSubject = DivisionApplication::useService()->getDivisionSubjectById($DivisionSubjectId);
        if ($tblDivisionSubject && ($tblDivision = $tblDivisionSubject->getTblDivision())) {

            $filter = new Filter($tblDivisionSubject);
            $filter->load();
            $header = array(
                'Name' => 'Name'
            );
            $filterHeader = $filter->getHeader();
            if (!empty($filterHeader)) {
                $header = array_merge($header, $filterHeader);
            }
            // SekII zusätzliche Anzeige der Leistungskurse
            $isSekII = Division::useService()->getIsDivisionCourseSystem($tblDivision);
            $personAdvancedCourses = array();
            if ($isSekII) {
                if (($tblDivisionSubjectList = DivisionApplication::useService()->getDivisionSubjectByDivision($tblDivision))) {
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

            // Schüler die in einer weiteren Gruppe in diesem Fach sind --> gelber Text
//            $personInAnotherGroupList = array();
//            if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
//                $tblDivisionSubjectControlList = DivisionApplication::useService()->getDivisionSubjectBySubjectAndDivision(
//                    $tblSubject,
//                    $tblDivision
//                );
//                if ($tblDivisionSubjectControlList) {
//                    foreach ($tblDivisionSubjectControlList as $tblDivisionSubjectControl) {
//                        if ($tblDivisionSubjectControl->getId() !== $tblDivisionSubject->getId()) {
//                            $tblSubjectStudentList = DivisionApplication::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectControl);
//                            if ($tblSubjectStudentList) {
//                                foreach ($tblSubjectStudentList as $tblSubjectStudent) {
//                                    if (($tblPersonItem = $tblSubjectStudent->getServiceTblPerson())) {
//                                        $personInAnotherGroupList[$tblPersonItem->getId()] = $tblPersonItem;
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }

            // left selected persons
            if (($tblPersonSelectedList = DivisionApplication::useService()->getStudentByDivisionSubject($tblDivisionSubject))) {
                $tableSelected = array();
                foreach ($tblPersonSelectedList as $tblPerson) {
                    $item = array();

                    $hasFilterError = false;
                    if ($filter->isFilterSet()) {
                        if ($filter->getTblGroup()) {
                            $text = $filter->getTblGroup()->getName();
                            if ($filter->hasGroup($tblPerson)) {
                                $item['Group'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['Group'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                        if ($filter->getTblGender()) {
                            $text = $filter->getTblGenderStringByPerson($tblPerson);
                            if ($filter->hasGender($tblPerson)) {
                                $item['Gender'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['Gender'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
//                                '<div class="alert alert-danger" style="Margin-Bottom:2px;Padding-Top:2px;Padding-Bottom:2px">' . $text . '</div>';
                            }
                        }
                        if ($filter->getTblCourse()) {
                            $text = $filter->getTblCourseStringByPerson($tblPerson);
                            if ($filter->hasCourse($tblPerson)) {
                                $item['Course'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['Course'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                        if ($filter->getTblSubjectOrientation()) {
                            $text = $filter->getTblSubjectOrientationStringByPerson($tblPerson);
                            if ($filter->hasSubjectOrientation($tblPerson)) {
                                $item['SubjectOrientation'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['SubjectOrientation'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                        if ($filter->getTblSubjectProfile()) {
                            $text = $filter->getTblSubjectProfileStringByPerson($tblPerson);
                            if ($filter->hasSubjectProfile($tblPerson)) {
                                $item['SubjectProfile'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['SubjectProfile'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                        if ($filter->getTblSubjectForeignLanguage()) {
                            $text = $filter->getTblSubjectForeignLanguagesStringByPerson($tblPerson);
                            if ($filter->hasSubjectForeignLanguage($tblPerson)) {
                                $item['SubjectForeignLanguage'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['SubjectForeignLanguage'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                        if ($filter->getTblSubjectReligion()) {
                            $text = $filter->getTblSubjectReligionStringByPerson($tblPerson);
                            if ($filter->hasSubjectReligion($tblPerson)) {
                                $item['SubjectReligion'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['SubjectReligion'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                        if ($filter->getTblSubjectElective()) {
                            $text = $filter->getTblSubjectElectivesStringByPerson($tblPerson);
                            if ($filter->hasSubjectElective($tblPerson)) {
                                $item['SubjectElective'] = $text;
                            } else {
                                $hasFilterError = true;
                                $item['SubjectElective'] = new \SPHERE\Common\Frontend\Text\Repository\Warning($text);
                            }
                        }
                    }

                    $name = $tblPerson->getLastFirstName();
                    if ($hasFilterError) {
                        $name = new \SPHERE\Common\Frontend\Text\Repository\Warning($name);
                    }
//                    elseif (isset($personInAnotherGroupList[$tblPerson->getId()])) {
//                        $name = new \SPHERE\Common\Frontend\Text\Repository\Warning($name);
//                    }
                    $item['Name'] = $name;

                    if ($isSekII) {
                        $item['AdvancedCourse1'] = isset($personAdvancedCourses[0][$tblPerson->getId()])
                            ? $personAdvancedCourses[0][$tblPerson->getId()] : '';
                        $item['AdvancedCourse2'] = isset($personAdvancedCourses[1][$tblPerson->getId()])
                            ? $personAdvancedCourses[1][$tblPerson->getId()] : '';
                    }

                    $item['Option'] = (new Standard('', self::getEndpoint(), new MinusSign(),
                        array('Id' => $tblPerson->getId(), 'DivisionSubjectId' => $tblDivisionSubject->getId()), 'Entfernen'))
                        ->ajaxPipelineOnClick(self::pipelineMinus($tblPerson->getId(), $tblDivisionSubject->getId()));

                    $tableSelected[$tblPerson->getId()] = $item;
                }

                $left[] = new Standard(
                    'Alle Schüler entfernen',
                    '/Education/Lesson/Division/SubjectStudent/RemoveAll',
                    new MinusSign(),
                    array(
                        'Id' => $tblDivision->getId(),
                        'DivisionSubjectId' => $DivisionSubjectId
                    )
                );
                $left[] = (new TableData($tableSelected, new Title('Ausgewählte', 'Schüler'), $header,
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('width' => '1%', 'orderable' => false, 'targets' => -1),
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
                        $name = $tblPerson->getLastFirstName();
//                        if (isset($personInAnotherGroupList[$tblPerson->getId()])) {
//                            $name = new \SPHERE\Common\Frontend\Text\Repository\Warning($name);
//                        }
                        $item['Name'] = $name;

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
                            array('Id' => $tblPerson->getId(), 'DivisionSubjectId' => $tblDivisionSubject->getId()), 'Hinzufügen'))
                            ->ajaxPipelineOnClick(self::pipelinePlus($tblPerson->getId(), $tblDivisionSubject->getId()));

                        $tableAvailable[$tblPerson->getId()] = $item;
                    }
                }
            }
            if (empty($tableAvailable)) {
                $right = new Info('Keine weiteren Schüler verfügbar');
            } else {
                $right[] = new Standard(
                    'Alle Schüler hinzufügen',
                    '/Education/Lesson/Division/SubjectStudent/AddAll',
                    new PlusSign(),
                    array(
                        'Id' => $tblDivision->getId(),
                        'DivisionSubjectId' => $DivisionSubjectId
                    )
                );
                $right[] = (new TableData($tableAvailable, new Title('Verfügbare', 'Schüler'), $header,
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                            array('width' => '1%', 'orderable' => false, 'targets' => array(-1)),
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
     * @param null $DivisionSubjectId
     *
     * @return bool|null|\SPHERE\Common\Frontend\Message\Repository\Danger
     */
    public static function getMessage($DivisionSubjectId = null)
    {
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            $filter = new Filter($tblDivisionSubject);
            $filter->load();

            return $filter->getMessageForSubjectGroup();
        }

        return null;
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

        // refresh Message
        $Emitter = new ServerEmitter(self::receiverMessage(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getMessage',
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

        // refresh Message
        $Emitter = new ServerEmitter(self::receiverMessage(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getMessage',
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