<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.11.2018
 * Time: 08:31
 */

namespace SPHERE\Application\Api\Education\Graduation\Gradebook;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
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
use SPHERE\System\Extension\Extension;

/**
 * Class ApiGradebook
 *
 * @package SPHERE\Application\Api\Education\Graduation\Gradebook
 */
class ApiGradebookOld extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('openExtraGradesModal');
        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalGradebookReciever');
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
     * @param int $PeriodId
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineOpenExtraGradesModal($DivisionId, $SubjectId, $PeriodId, $PersonId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openExtraGradesModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionId' => $DivisionId,
            'SubjectId' => $SubjectId,
            'PeriodId' => $PeriodId,
            'PersonId' => $PersonId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param int $DivisionId
     * @param int $SubjectId
     * @param int $PeriodId
     * @param int $PersonId
     *
     * @return Danger|string
     */
    public function openExtraGradesModal($DivisionId, $SubjectId, $PeriodId, $PersonId)
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

        $table = false;
        $list = array();
        if (($tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST'))
            && ($tblYear = $tblDivision->getServiceTblYear())
            && ($list = Gradebook::useService()->getGradesFromAnotherDivisionByStudent(
                $tblDivision,
                $tblSubject,
                $tblYear,
                $tblPerson,
                $tblTestType,
                $list
            ))
            && isset($list[$PeriodId][$PersonId])
        ) {
            $dataList = array();
            foreach ($list[$PeriodId][$PersonId] as $gradeId => $value) {
                if (($tblGrade = Gradebook::useService()->getGradeById($gradeId))
                    && ($tblGradeType = $tblGrade->getTblGradeType())
                    && ($tblTest = $tblGrade->getServiceTblTest())
                    && ($tblDivisionTest = $tblTest->getServiceTblDivision())
                ) {
                    $isHighlighted = $tblGradeType->isHighlighted();
                    $date = $tblGrade->getDateForSorter();
                    $dataList[] = array(
                        'Date' => $date ? $date->format('d.m.Y') : '',
                        'GradeType' => $isHighlighted ? new Bold($tblGradeType->getDisplayName()) : $tblGradeType->getDisplayName(),
                        'Division' => $tblDivisionTest ? $tblDivisionTest->getDisplayName() : '',
                        'Description' => $tblTest->getDescription(),
                        'Grade' => $isHighlighted ? new Bold($value) : $value
                    );
                }
            }

            $table = new TableData(
                $dataList,
                null,
                array(
                    'Date' => 'Datum',
                    'GradeType' => 'Zensuren-Typ',
                    'Division' => 'Klasse',
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
            );
        }

        return new Title('Vornoten')
            . new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Schüler', $tblPerson->getLastFirstName(), Panel::PANEL_TYPE_INFO)
                    , 6),
                new LayoutColumn(
                    new Panel('Fach', $tblSubject->getDisplayName(), Panel::PANEL_TYPE_INFO)
                    , 6)
            ))))
            . ($table ? $table : '');
    }
}