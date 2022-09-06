<?php

namespace SPHERE\Application\Api\Education\Graduation\Gradebook;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Alle Zensuren über alle Schuljahre eines Schülers für berufsbildende Schulen
 *
 * Class ApiGradesAllYears
 *
 * @package SPHERE\Application\Api\Education\Graduation\Gradebook
 */
class ApiGradesAllYears extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openAllGradesModal');
        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalAllGradesReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());
        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $SubjectId
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenAllGradesModal($DivisionId, $SubjectId, $PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openAllGradesModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'SubjectId' => $SubjectId,
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $SubjectId
     * @param int $PersonId
     *
     * @return Danger|string
     */
    public function openAllGradesModal($DivisionId, $SubjectId, $PersonId)
    {

        if (!($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            return (new Danger('Klasse nicht gefunden', new Exclamation()));
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return (new Danger('Fach nicht gefunden', new Exclamation()));
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return (new Danger('Person nicht gefunden', new Exclamation()));
        }

        $divisionList = array();
        $tblDivisionRepeatList = Division::useService()->getRepeatedDivisionAllByPerson($tblPerson);
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
            && ($tblType = $tblDivision->getType())
            && ($tblDivisionStudentAll = Division::useService()->getDivisionStudentAllByPerson($tblPerson))
        ) {
            foreach ($tblDivisionStudentAll as $tblDivisionStudent) {
                if (($tblDivisionItem = $tblDivisionStudent->getTblDivision())) {
                    // Wiederholte Schuljahr herausfiltern
                    if ($tblDivisionRepeatList && isset($tblDivisionRepeatList[$tblDivisionItem->getId()])) {
                        continue;
                    }

                    // Alle Klassen der Schulart finden
                    if (($tblYear = $tblDivisionItem->getServiceTblYear())
                        && ($tblTypeItem = $tblDivisionItem->getType())
                        && $tblType->getId() == $tblTypeItem->getId()
                    ) {
                        $list = array();
                        if (($tblGradeList = Gradebook::useService()->getGradesByStudent(
                            $tblPerson, $tblDivisionItem, $tblSubject, $tblTestType
                        ))) {
                            $this->getSorter($tblGradeList)->sortObjectBy('DateForSorter', new DateTimeSorter());
                            /** @var TblGrade $tblGrade */
                            foreach ($tblGradeList as $tblGrade) {
                                if (($tblPeriodItem = $tblGrade->getServiceTblPeriod())
                                    && ($tblGradeType = $tblGrade->getTblGradeType())
                                ) {
                                    $list[$tblPeriodItem->getId()][$tblPerson->getId()][$tblGrade->getId()] =
                                        $tblGradeType->isHighlighted()
                                            ? new Bold($tblGrade->getDisplayGrade())
                                            : $tblGrade->getDisplayGrade();
                                }
                            }
                        }

                        $contentDivision = '';
                        if (($tblPeriodList  = Term::useService()->getPeriodAllByYear($tblYear))) {
                            foreach ($tblPeriodList as $tblPeriod) {
                                $dataList = array();
                                if (isset($list[$tblPeriod->getId()])) {
                                    foreach ($list[$tblPeriod->getId()][$PersonId] as $gradeId => $value) {
                                        if (($tblGrade = Gradebook::useService()->getGradeById($gradeId))
                                            && ($tblGradeType = $tblGrade->getTblGradeType())
                                            && ($tblTest = $tblGrade->getServiceTblTest())
                                            && ($tblDivisionTest = $tblTest->getServiceTblDivision())
                                        ) {
                                            $isHighlighted = $tblGradeType->isHighlighted();
                                            $date = $tblGrade->getDateForSorter();
                                            $dataList[] = array(
                                                'Date' => $date ? $date->format('d.m.Y') : '',
                                                'GradeType' => $isHighlighted
                                                    ? new Bold($tblGradeType->getDisplayName())
                                                    : $tblGradeType->getDisplayName(),
//                                                'Division' => $tblDivisionTest ? $tblDivisionTest->getDisplayName() : '',
                                                'Description' => $tblTest->getDescription(),
                                                'Grade' => $isHighlighted ? new Bold($value) : $value
                                            );
                                        }
                                    }
                                }

                                $contentDivision .= new Panel(
                                    'Klasse ' . $tblDivisionItem->getDisplayName() . ' - ' . $tblPeriod->getDisplayName(),
                                    empty($dataList)
                                        ? new Warning('Keine Zensuren vorhanden!')
                                        : (new TableData(
                                            $dataList,
                                            null,
                                            array(
                                                'Date' => 'Datum',
                                                'GradeType' => 'Zensuren-Typ',
//                                                'Division' => 'Klasse',
                                                'Description' => 'Thema',
                                                'Grade' => 'Zensur'
                                            ),
                                            array(
                                                'order' => array(
                                                    array(0, 'asc')
                                                ),
                                                'columnDefs' => array(
                                                    array('type' => 'de_date', 'targets' => 0)
                                                ),
                                                "paging" => false, // Deaktivieren Blättern
                                                "iDisplayLength" => -1,    // Alle Einträge zeigen
                                                "searching" => false, // Deaktivieren Suchen
                                                "info" => false,  // Deaktivieren Such-Info
                                                "responsive" => false
                                            )
                                        ))->setHash('Table-' . $tblDivisionItem->getId() . '-' . $tblPeriod->getId()),
                                    Panel::PANEL_TYPE_INFO
                                );
                            }
                        }

                        list($startDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                        $divisionList[$startDate->format('Y-m-d') . '-' . $tblDivisionItem->getId()] = $contentDivision;
                    }
                }
            }
        }

        ksort($divisionList);
        $content = implode('<br />', $divisionList);

        /*
        * Calc Average over all Years
        */
        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');
        $average = Gradebook::useService()->calcStudentGrade(
            $tblPerson,
            $tblDivision,
            $tblSubject,
            $tblTestType,
            null,
            null,
            null,
            false,
            false,
            Gradebook::useService()->getSubjectGradesByAllYears(
                $tblPerson,
                $tblSubject,
                $tblTestType
            )
        );
        if (is_array($average)) {
            $average = '';
        } else {
            $posStart = strpos($average, '(');
            if ($posStart !== false) {
                $average = substr($average, 0, $posStart);
            }
        }

        return new Title('Vornoten ' . new Muted('(' . TblTask::ALL_YEARS_PERIOD_Name . ')'))
            . new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO)
                    , 4),
                new LayoutColumn(
                    new Panel('Fach', $tblSubject->getDisplayName(), Panel::PANEL_TYPE_INFO)
                    , 4),
                new LayoutColumn(
                    new Panel('Durchschnitt', $average, Panel::PANEL_TYPE_INFO)
                    , 4)
            ))))
            . $content;
    }
}