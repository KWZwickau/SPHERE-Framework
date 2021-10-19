<?php

namespace SPHERE\Application\Api\Education\Graduation\Gradebook;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiGradeMaintenance
 *
 * @package SPHERE\Application\Api\Education\Graduation\Gradebook
 */
class ApiGradeMaintenance extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''):string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadDivisionSelect');
        $Dispatcher->registerMethod('loadDivisionSubjectSelect');
        $Dispatcher->registerMethod('loadDivisionSubjectInformation');
        $Dispatcher->registerMethod('loadMoveButton');
        $Dispatcher->registerMethod('copyTestsAndGrades');

        $Dispatcher->registerMethod('loadUnreachableGrades');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param array|null $Data
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionSelect(?array $Data, string $Identifier): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', $Identifier . 'DivisionSelect'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionSelect',
            'Data' => $Data,
            'Identifier' => $Identifier
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     * @param string $Identifier
     *
     * @return string
     */
    public function loadDivisionSelect(?array $Data, string $Identifier = 'Source'): string
    {
        // todo Meldung bei verschiedenen Schuljahren oder nur eine Auswahl
        if (isset($Data[$Identifier]['YearId'])
            && ($tblYear = Term::useService()->getYearById($Data[$Identifier]['YearId']))
            && ($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))
        ) {
            return (new SelectBox('Data[' . $Identifier . '][DivisionId]', 'Klasse', array('{{ DisplayName }}' => $tblDivisionList)))
                ->ajaxPipelineOnChange(array(self::pipelineLoadDivisionSubjectSelect($Data, $Identifier)))->setRequired();
        }

        return '';
    }

    /**
     * @param array|null $Data
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionSubjectSelect(?array $Data, string $Identifier = 'Source'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', $Identifier . 'DivisionSubjectSelect'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionSubjectSelect',
            'Data' => $Data,
            'Identifier' => $Identifier
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     * @param string $Identifier
     * @return string
     */
    public function loadDivisionSubjectSelect(?array $Data, string $Identifier = 'Source'): string
    {
        if (isset($Data[$Identifier]['DivisionId'])
            && ($tblDivision = Division::useService()->getDivisionById($Data[$Identifier]['DivisionId']))
        ) {
            if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                return (new SelectBox('Data[' . $Identifier . '][DivisionSubjectId]', 'Fachgruppe',
                    array('{{ NameForSorter }}' => $tblDivisionSubjectList)))
                    ->ajaxPipelineOnChange(array(
                        self::pipelineLoadDivisionSubjectInformation($Data, $Identifier),
//                        self::pipelineLoadMoveButton($Data)
                    ))->setRequired();
            } else {
                return  new \SPHERE\Common\Frontend\Message\Repository\Warning('Keine Fächer vorhanden!', new Exclamation());
            }
        }

        return '';
    }

    /**
     * @param array|null $Data
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineLoadDivisionSubjectInformation(?array $Data, string $Identifier = 'Source'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', $Identifier . 'DivisionSubjectInformation'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadDivisionSubjectInformation',
            'Data' => $Data,
            'Identifier' => $Identifier
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     * @param string $Identifier
     *
     * @return string
     */
    public function loadDivisionSubjectInformation(?array $Data, string $Identifier = 'Source'): string
    {
        $result = '';
        if (isset($Data[$Identifier]['DivisionSubjectId'])
            && ($tblDivisionSubject = Division::useService()->getDivisionSubjectById($Data[$Identifier]['DivisionSubjectId']))
        ) {
            if (($tblDivision = $tblDivisionSubject->getTblDivision())
                && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
            ) {
//                $_POST[$Identifier]['DivisionSubjectId'] = $tblDivisionSubject->getId();

                $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();
                $tblSubjectGroup = $tblSubjectGroup ? $tblSubjectGroup : null;

                $info = array();

                $tblScoreRule = Gradebook::useService()->getScoreRuleByDivisionAndSubjectAndGroup(
                    $tblDivision,
                    $tblSubject,
                    $tblSubjectGroup
                );
                $tblScoreType = Gradebook::useService()->getScoreTypeByDivisionAndSubject($tblDivision, $tblSubject);

                $countTests = array();
                $countGrades = array();
                if (($tblTestList = Evaluation::useService()->getTestDistinctListBy($tblDivision, $tblSubject, $tblSubjectGroup))) {
                    foreach ($tblTestList as $tblTest) {
                        if (($tblTestType = $tblTest->getTblTestType())) {
                            if (isset($countTests[$tblTestType->getIdentifier()])) {
                                $countTests[$tblTestType->getIdentifier()]++;
                            } else {
                                $countTests[$tblTestType->getIdentifier()] = 1;
                            }

                            if (($tblGradeList = Gradebook::useService()->getGradeAllByTest($tblTest))) {
                                if (isset($countGrades[$tblTestType->getIdentifier()])) {
                                    $countGrades[$tblTestType->getIdentifier()] += count($tblGradeList);
                                } else {
                                    $countGrades[$tblTestType->getIdentifier()] = count($tblGradeList);
                                }
                            }
                        }
                    }
                }

                $info[] = 'Bewertungssystem: ' . ($tblScoreType ? $tblScoreType->getName() : new Warning('Kein Bewertungssystem hinterlegt!'));
                $info[] = 'Berechnungsvorschrift: ' . ($tblScoreRule ? $tblScoreRule->getName() : new Warning('Keine Berechnungsvorschrift hinterlegt!'));
                $info[] = ' ';

                $info[] = 'Leistungsüberprüfungen: ' . (isset($countTests['TEST']) && $countTests['TEST']
                        ? new Bold($countTests['TEST']) . ' vorhanden' : new Warning('Keine Leistungsüberprüfungen vorhanden!'));
                $info[] = 'Zensuren: ' . (isset($countGrades['TEST']) && $countGrades['TEST'] > 0
                        ? new Bold($countGrades['TEST']) . ' vorhanden' : new Warning('Keine Zensuren vorhanden!'));

                if (isset($countTests['APPOINTED_DATE_TASK'])) {
                    $info[] = ' ';
                    $info[] = 'Stichtagsnotenaufträge: ' . new Bold($countTests['APPOINTED_DATE_TASK']) . ' vorhanden';
                    $info[] = 'Stichtagsnoten: ' . (isset($countGrades['APPOINTED_DATE_TASK']) && $countGrades['APPOINTED_DATE_TASK'] > 0
                        ? new Bold($countGrades['APPOINTED_DATE_TASK']) . ' vorhanden' : new Warning('Keine Stichtagsnoten vorhanden!'));
                }

                if (isset($countTests['BEHAVIOR_TASK'])) {
                    $info[] = ' ';
                    $info[] = 'Kopfnotenaufträge: ' . new Bold($countTests['BEHAVIOR_TASK']) . ' vorhanden';
                    $info[] = 'Kopfnoten: ' . (isset($countGrades['BEHAVIOR_TASK']) && $countGrades['BEHAVIOR_TASK'] > 0
                        ? new Bold($countGrades['BEHAVIOR_TASK']) . ' vorhanden' : new Warning('Keine Kopfnoten vorhanden!'));
                }

                if ($Identifier == 'Target' && $tblTestList) {
                    $info[] = new Danger('Die vorhandenen Leistungsüberprüfungen und Zensuren werden gelöscht.');
                }

                $result = implode('<br>', $info);
            }
        }

        return self::pipelineLoadMoveButton($Data) . $result;
    }

    /**
     * @param array|null $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadMoveButton(?array $Data): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'MoveButton'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadMoveButton',
            'Data' => $Data,
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     *
     * @return string
     */
    public static function loadMoveButton(?array $Data): string
    {
        if (isset($Data['Source']['DivisionSubjectId'])
            && isset($Data['Target']['DivisionSubjectId'])
            && ($tblSourceDivisionSubject = Division::useService()->getDivisionSubjectById($Data['Source']['DivisionSubjectId']))
            && ($tblTargetDivisionSubject = Division::useService()->getDivisionSubjectById($Data['Target']['DivisionSubjectId']))
        ) {
            return (new Primary('Zensuren Verschieben', self::getEndpoint()))->ajaxPipelineOnClick(self::pipelineCopyTestsAndGrades(
                $tblSourceDivisionSubject->getId(),
                $tblTargetDivisionSubject->getId()
            ));
        }

        return (new Primary('Zensuren Verschieben', self::getEndpoint()))->setDisabled();
    }

    /**
     * @param $SourceDivisionSubjectId
     * @param $TargetDivisionSubjectId
     *
     * @return Pipeline
     */
    public static function pipelineCopyTestsAndGrades($SourceDivisionSubjectId, $TargetDivisionSubjectId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'OutputInformation'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'copyTestsAndGrades',
            'SourceDivisionSubjectId' => $SourceDivisionSubjectId,
            'TargetDivisionSubjectId' => $TargetDivisionSubjectId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SourceDivisionSubjectId
     * @param $TargetDivisionSubjectId
     *
     * @return string
     */
    public static function copyTestsAndGrades($SourceDivisionSubjectId, $TargetDivisionSubjectId): string
    {
        if (($tblSourceDivisionSubject = Division::useService()->getDivisionSubjectById($SourceDivisionSubjectId))
            && ($tblTargetDivisionSubject = Division::useService()->getDivisionSubjectById($TargetDivisionSubjectId))
            && ($tblSourceDivision = $tblSourceDivisionSubject->getTblDivision())
            && ($tblSourceSubject = $tblSourceDivisionSubject->getServiceTblSubject())
            && ($tblTargetDivision = $tblTargetDivisionSubject->getTblDivision())
            && ($tblTargetSubject = $tblTargetDivisionSubject->getServiceTblSubject())
        ) {

            $protocol = Gradebook::useService()->copyTestsAndGrades(
                $tblSourceDivision,
                $tblSourceSubject,
                ($tblSourceGroup = $tblSourceDivisionSubject->getTblSubjectGroup()) ? $tblSourceGroup : null,
                $tblTargetDivision,
                $tblTargetSubject,
                ($tblTargetGroup = $tblTargetDivisionSubject->getTblSubjectGroup()) ? $tblTargetGroup : null
            );

            $output = '';
            if (isset($protocol['DeleteTests'])) {
                $output.= new Panel(
                    'Gelöschte Tests (' . $protocol['DeleteTestsCount'] . ')',
                    $protocol['DeleteTests'],
                    Panel::PANEL_TYPE_WARNING
                );
            }
            if (isset($protocol['UpdateTests'])) {
                $output.= new Panel(
                    'Verschobene Tests (' . $protocol['UpdateTestsCount'] . ')',
                    $protocol['UpdateTests'],
                    Panel::PANEL_TYPE_SUCCESS
                );
            }

            return $output;
        }

        return '';
    }

    /**
     * @param array|null $Data
     *
     * @return Pipeline
     */
    public static function pipelineLoadUnreachableGrades(?array $Data): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'UnreachableGrades'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadUnreachableGrades',
            'Data' => $Data
        ));

        $Pipeline->appendEmitter($ModalEmitter);
        $Pipeline->setLoadingMessage('Daten werden geladen. Bitte warten');

        return $Pipeline;
    }

    /**
     * @param array|null $Data
     *
     * @return string
     */
    public function loadUnreachableGrades(?array $Data): string
    {
        ini_set('memory_limit', '1G');

        if (isset($Data['YearId'])
            && ($tblYear = Term::useService()->getYearById($Data['YearId']))
        ) {
            $panel = new Panel('Schuljahr', $tblYear->getDisplayName());

            $list = array();

            list($fromCreateDate, $toCreateDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if (!$fromCreateDate && !$toCreateDate) {
                return $panel . new \SPHERE\Common\Frontend\Message\Repository\Warning('Das Schuljahr besitzt keinen Zeitraum!');
            }

            if (($tblGradeList = Gradebook::useService()->getGradeAllByFromCreateDate($fromCreateDate, $toCreateDate))) {
                foreach ($tblGradeList as $tblGrade) {
                    $tblPerson = $tblGrade->getServiceTblPerson();
                    if (($tblDivision = $tblGrade->getServiceTblDivision())) {
                        $tblYear = $tblDivision->getServiceTblYear();
                    } else {
                        $tblYear = false;
                    }
                    $tblSubject = $tblGrade->getServiceTblSubject();
                    $tblSubjectGroup = $tblGrade->getServiceTblSubjectGroup();
                    $tblTest = $tblGrade->getServiceTblTest();

                    if (!$tblPerson || !$tblDivision || !$tblSubject || !$tblTest) {
                        $list[] = array(
                            'Year' => $tblYear ? $tblYear->getDisplayName() : $this->getWarning('Schuljahr'),
                            'Division' => $tblDivision ? $tblDivision->getDisplayName() : $this->getWarning('Klasse'),
                            'Subject' => $tblSubject ? $tblSubject->getDisplayName() : $this->getWarning('Fach'),
                            'SubjectGroup' => $tblSubjectGroup ? $tblSubjectGroup->getName() : '',
                            'Test' => $tblTest ? $tblTest->getGradeTypeCode() : $this->getWarning('Leistungsüberprüfung'),
                            'Person' => $tblPerson ? $tblPerson->getLastFirstName() : $this->getWarning('Person'),
                            'Grade' => $tblGrade->getDisplayGrade(),
                            'GradeId' => $tblGrade->getId(),
                            'Info' => ''
                        );
                    } else {
                        $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                            $tblDivision,
                            $tblSubject,
                            $tblSubjectGroup ? $tblSubjectGroup : null
                        );
                        // Fach-Klassen-Gruppe nicht mehr vorhanden
                        if (!$tblDivisionSubject) {
                            $list[] = array(
                                'Year' => $tblYear ? $tblYear->getDisplayName() : $this->getWarning('Schuljahr'),
                                'Division' => $tblDivision->getDisplayName(),
                                'Subject' => $tblSubject->getDisplayName(),
                                'SubjectGroup' => $tblSubjectGroup ? $tblSubjectGroup->getName() : '',
                                'Test' => $tblTest->getGradeTypeCode(),
                                'Person' => $tblPerson->getLastFirstName(),
                                'Grade' => $tblGrade->getDisplayGrade(),
                                'GradeId' => $tblGrade->getId(),
                                'Info' => $this->getWarning('Fach-Klassen-Gruppen')
                            );
                        } elseif (!$tblSubjectGroup
                            && Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject($tblDivision, $tblSubject)
                        ) {
                            $list[] = array(
                                'Year' => $tblYear ? $tblYear->getDisplayName() : $this->getWarning('Schuljahr'),
                                'Division' => $tblDivision->getDisplayName(),
                                'Subject' => $tblSubject->getDisplayName(),
                                'SubjectGroup' => '',
                                'Test' => $tblTest->getGradeTypeCode(),
                                'Person' => $tblPerson->getLastFirstName(),
                                'Grade' => $tblGrade->getDisplayGrade(),
                                'GradeId' => $tblGrade->getId(),
                                'Info' => new Warning(new Disable() . ' Die Note hat keine Gruppe, es sind allerdings jetzt Gruppen vorhanden.
                                    Die Noten ist nicht mehr aufrufbar!')
                            );
                        }
                    }
                }
            }

            if (empty($list)) {
                return $panel . new Success('Es sind keine unerreichbare Zensuren vorhanden');
            } else {
                return $panel . new TableData(
                    $list,
                    null,
                    array(
                        'Year' => 'Schuljahr',
                        'Division' => 'Klasse',
                        'Subject' => 'Fach',
                        'SubjectGroup' => 'Gruppe',
                        'Test' => 'Leistungsüberprüfung',
                        'Person' => 'Person',
                        'Grade' => 'Zensur',
                        'GradeId' => 'GradeId',
                        'Info' => 'Info'
                    )
                );
            }
        }

        return '';
    }

    private function getWarning(string $name): string
    {
        return new Warning(new Disable() . ' ' . $name . ' nicht vorhanden!');
    }
}