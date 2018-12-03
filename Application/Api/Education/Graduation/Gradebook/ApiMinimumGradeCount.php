<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 03.12.2018
 * Time: 09:09
 */

namespace SPHERE\Application\Api\Education\Graduation\Gradebook;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * Class ApiMinimumGradeCount
 *
 * @package SPHERE\Application\Api\Education\Graduation\Gradebook
 */
class ApiMinimumGradeCount  extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadMinimumGradeCountReporting');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverContent($Content = '')
    {

        return new BlockReceiver($Content);
    }

    /**
     * @param AbstractReceiver $Receiver
     * @param $Data
     * @param $IsDivisionTeacher
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateMinimumGradeCountReportingContent(AbstractReceiver $Receiver, $Data, $IsDivisionTeacher, $PersonId)
    {

        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, self::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMinimumGradeCountReporting'
        ));
        $FieldEmitter->setPostPayload(array(
            'Data' => $Data,
            'IsDivisionTeacher' => $IsDivisionTeacher,
            'PersonId' => $PersonId
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Mindestnotenanzahl - Auswertung wird aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $Data
     * @param null $IsDivisionTeacher
     * @param null $PersonId
     *
     * @return Layout|string
     */
    public function loadMinimumGradeCountReporting($Data = null, $IsDivisionTeacher = null, $PersonId = null)
    {
        ini_set('memory_limit', '2G');

        $IsDivisionTeacher = $IsDivisionTeacher === 'true';
        if ($Data === null) {
            return '';
        }

        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            return new Warning('Bitte wählen Sie ein Schuljahr aus!', new Exclamation());
        }

        $tblType = Type::useService()->getTypeById($Data['Type']);

        if (!$IsDivisionTeacher && !$tblType) {
            return new Warning('Bitte wählen Sie eine Schulart aus!', new Exclamation());
        }

        if (isset($Data['DivisionName']) && ($divisionName = $Data['DivisionName']) && $divisionName != '') {
            $tblDivisionList = Division::useService()->getDivisionAllByName($divisionName, $tblYear, $tblType ? $tblType : null);
            if (empty($tblDivisionList)) {
                return new Warning('Klasse nicht gefunden', new Exclamation());
            }
        } else {
            if ($tblType) {
                $tblDivisionList = Division::useService()->getDivisionAllByYearAndType($tblYear, $tblType);
            } else {
                $tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear);
            }
        }

        // Klassenlehrer können nur ihre eigenen Klassen sehen
        if ($IsDivisionTeacher && $tblDivisionList) {
            $tempDivisionList = array();
            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson)) {
                        $tempDivisionList[] = $tblDivision;
                    }
                }
            }

            $tblDivisionList = empty($tempDivisionList) ? false : $tempDivisionList;
        }

        if ($tblDivisionList) {
            if ($IsDivisionTeacher) {
                $routeGradebook = '/Education/Graduation/Gradebook/Gradebook/Teacher/Selected';
            } else {
                $routeGradebook = '/Education/Graduation/Gradebook/Gradebook/Headmaster/Selected';
            }

            $panelList = array();
            $tblDivisionList = $this->getSorter($tblDivisionList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
            /** @var TblDivision $tblDivision */
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblLevel = $tblDivision->getTblLevel())
                    && ($levelName = $tblLevel->getName())
                    && ($levelName == '11' || $levelName == '12')
                ) {
                    $isSekII = true;
                } else {
                    $isSekII = false;
                }

                $contentPanel = array();
                $isDivisionFulfilled = true;
                if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectListByDivision($tblDivision))) {
                    $tblPersonListDivision = Division::useService()->getStudentAllByDivision($tblDivision);
                    $tblDivisionSubjectList = $this->getSorter($tblDivisionSubjectList)->sortObjectBy('NameForSorter');
                    /** @var TblDivisionSubject $tblDivisionSubject */
                    foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                        if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                            && ($tblMinimumGradeCountList = Gradebook::useService()->getMinimumGradeCountAllByDivisionSubject($tblDivisionSubject, $isSekII))
                        ) {
                            $subjectName = $tblSubject->getDisplayName()
                                . ($tblDivisionSubject->getTblSubjectGroup()
                                    ? ' (' .  $tblDivisionSubject->getTblSubjectGroup()->getName() . ')'
                                    : '');

                            if ($tblDivisionSubject->getTblSubjectGroup()) {
                                $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
                            } else {
                                $tblPersonList = $tblPersonListDivision;
                            }

                            if ($tblPersonList) {
                                foreach ($tblMinimumGradeCountList as $tblMinimumGradeCount) {
                                    $minimumCount = $tblMinimumGradeCount->getCount();
                                    $countPersons = 0;
                                    $countFulfil = 0;
                                    foreach ($tblPersonList as $tblPerson) {
                                        $countPersons++;
                                        $countMinimumGradeByPerson = Gradebook::useService()->getMinimumGradeCountNumber(
                                            $tblDivisionSubject,
                                            $tblPerson,
                                            $tblMinimumGradeCount
                                        );

                                        if ($countMinimumGradeByPerson >= $minimumCount) {
                                            $countFulfil++;
                                        }
                                    }

                                    $status = $countFulfil . ' von ' . $countPersons . ' Schüler';
                                    if ($countFulfil >= $countPersons) {
                                        $status = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' ' . $status);
                                    } else {
                                        $isDivisionFulfilled = false;
                                        $status = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Disable() . ' ' . $status);
                                    }
                                    $external = new External(
                                        '',
                                        $routeGradebook,
                                        new Extern(),
                                        array('DivisionSubjectId' => $tblDivisionSubject->getId()),
                                        'Notenbuch in einem neuen Tab öffnen'
                                    );

                                    $contentPanel[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                        new LayoutColumn($subjectName, 3),
                                        new LayoutColumn($tblMinimumGradeCount->getGradeTypeDisplayName(), 3),
                                        new LayoutColumn($tblMinimumGradeCount->getPeriodDisplayName(), 2),
                                        new LayoutColumn($tblMinimumGradeCount->getCourseDisplayName(), 1),
                                        new LayoutColumn($minimumCount, 1),
                                        new LayoutColumn($status . new PullRight($external), 2)
                                    ))));
                                }
                            }
                        }
                    }
                }

                $tblTypeDivision = $tblLevel ? $tblLevel->getServiceTblType() : false;
                $panelList[] = new Panel(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn($tblDivision->getDisplayName() . ($tblTypeDivision ? new Small(' (' . $tblTypeDivision->getName() . ')') : ''), 3),
                        new LayoutColumn(new Small('Zensuren-Typ:'), 3),
                        new LayoutColumn(new Small('Zeitraum:'), 2),
                        new LayoutColumn(new Small($isSekII ? 'Kurs:' : '&nbsp;'), 1),
                        new LayoutColumn(new Small('Anzahl:'), 1),
                        new LayoutColumn(new Small('Status:'), 2)
                    )))),
                    empty($contentPanel) ? new Ban() .' Keine Mindestnoten vorhanden' : $contentPanel,
                    $isDivisionFulfilled ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING
                );
            }

            if (!empty($panelList)) {
                return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($panelList))));
            }
        }

        return new Warning('Keine Mindestnoten gefunden', new Exclamation());
    }
}