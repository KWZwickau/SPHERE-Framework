<?php

namespace SPHERE\Application\Education\Certificate\Reporting;

use SPHERE\Application\Api\Education\Certificate\Reporting\ApiReporting;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Reporting
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendSelect(): Stage
    {
        $tblYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            $tblYear = current($tblYearList);
            $global = $this->getGlobal();
            $global->POST['Data']['YearId'] = $tblYear->getId();
            $global->savePost();
        }

        $form = (new Form(new FormGroup(new FormRow(new FormColumn(
            (new SelectBox('Data[YearId]', '', array('{{ DisplayName }}' => Term::useService()->getYearAll())))
                ->ajaxPipelineOnChange(ApiReporting::pipelineLoadReportingOverview())
        )))))->disableSubmitAction();

        $Stage = new Stage('Zeugnisse auswerten', 'Übersicht');
        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel (
                        'Schuljahr',
                        $form,
                        Panel::PANEL_TYPE_PRIMARY
                    )
                , 6),
            ))))
            . ApiReporting::receiverBlock(
                $this->loadReportingOverview($tblYear ?: null),
                'ReportingOverview'
            )
        );

        return $Stage;
    }

    /**
     * @param ?TblYear $tblYear
     *
     * @return string
     */
    public function loadReportingOverview(?TblYear $tblYear): string
    {
        if (!$tblYear) {
            return new Warning('Bitte wählen Sie zunächst ein Schuljahr aus.', new Exclamation());
        }

        $YearId = $tblYear->getId();

        $serialMailItemList = array();
        $statisticItemList = array();
        $rankingItemList = array();
        $courseItemList = array();
        $typeList = School::useService()->getConsumerSchoolTypeAll();
        if (!$typeList || isset($typeList['OS'])) {
            $serialMailItemList[] = 'Oberschule - Hauptschule ' . new PullRight((new Standard(
                    '',
                    '/Api/Reporting/Standard/Person/Certificate/Diploma/SerialMail/Download',
                    new Download(),
                    array(
                        'View' => View::HS,
                        'YearId' => $YearId
                    ),
                    'Serien E-Mail mit Prüfungsnoten für Hauptschulabschlusszeugnisse herunterladen'
                )));
            $serialMailItemList[] = 'Oberschule - Realschule ' . new PullRight((new Standard(
                    '',
                    '/Api/Reporting/Standard/Person/Certificate/Diploma/SerialMail/Download',
                    new Download(),
                    array(
                        'View' => View::RS,
                        'YearId' => $YearId
                    ),
                    'Serien E-Mail mit Prüfungsnoten für Realschulabschlusszeugnisse herunterladen'
                )));
            $statisticItemList[] = 'Oberschule - Hauptschule ' . new PullRight((new Standard(
                    '',
                    '/Api/Reporting/Standard/Person/Certificate/Diploma/Statistic/Download',
                    new Download(),
                    array(
                        'View' => View::HS,
                        'YearId' => $YearId
                    ),
                    'Auswertung der Prüfungsnoten für die LaSuB für Hauptschulabschlusszeugnisse herunterladen'
                )));
            $statisticItemList[] = 'Oberschule - Realschule ' . new PullRight((new Standard(
                    '',
                    '/Api/Reporting/Standard/Person/Certificate/Diploma/Statistic/Download',
                    new Download(),
                    array(
                        'View' => View::RS,
                        'YearId' => $YearId
                    ),
                    'Auswertung der Prüfungsnoten für die LaSuB für Realschulabschlusszeugnisse herunterladen'
                )));
            $rankingItemList[] = 'Oberschule - Hauptschule ' . new PullRight((new Standard(
                    '',
                    '/Education/Certificate/Reporting/Diploma',
                    new Select(),
                    array(
                        'View' => View::HS,
                        'YearId' => $YearId
                    ),
                    'Hauptschulabschlusszeugnisse auswählen'
                )));
            $rankingItemList[] = 'Oberschule - Realschule ' . new PullRight((new Standard(
                    '',
                    '/Education/Certificate/Reporting/Diploma',
                    new Select(),
                    array(
                        'View' => View::RS,
                        'YearId' => $YearId
                    ),
                    'Realschulabschlusszeugnisse auswählen'
                )));
        }
        if (!$typeList || isset($typeList['Gy'])) {
            $rankingItemList[] = 'Gymnasium - Abitur ' . new PullRight((new Standard(
                    '',
                    '/Education/Certificate/Reporting/Diploma',
                    new Select(),
                    array(
                        'View' => View::ABI,
                        'YearId' => $YearId
                    ),
                    'Abiturabschlusszeugnisse auswählen'
                )));
        }
        if (!$typeList || isset($typeList['FOS'])) {
            $serialMailItemList[] = 'Fachoberschule ' . new PullRight((new Standard(
                    '',
                    '/Api/Reporting/Standard/Person/Certificate/Diploma/SerialMail/Download',
                    new Download(),
                    array(
                        'View' => View::FOS,
                        'YearId' => $YearId
                    ),
                    'Serien E-Mail mit Prüfungsnoten für Fachoberschulabschlusszeugnisse herunterladen'
                )));
            $statisticItemList[] = 'Fachoberschule ' . new PullRight((new Standard(
                    '',
                    '/Api/Reporting/Standard/Person/Certificate/Diploma/Statistic/Download',
                    new Download(),
                    array(
                        'View' => View::FOS,
                        'YearId' => $YearId
                    ),
                    'Auswertung der Prüfungsnoten für die LaSuB für Fachoberschulabschlusszeugnisse herunterladen'
                )));
        }

        if (!$typeList || isset($typeList['Gy']) || isset($typeList['BGy'])) {
            $tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy');
            $tblSchoolTypeBGy = Type::useService()->getTypeByShortName('BGy');

            $tblDivisionCourseList = array();

            Reporting::useService()->setDivisionCourseList($tblDivisionCourseList, $tblYear, $tblSchoolTypeGy, 11);
            Reporting::useService()->setDivisionCourseList($tblDivisionCourseList, $tblYear, $tblSchoolTypeGy, 12);

            Reporting::useService()->setDivisionCourseList($tblDivisionCourseList, $tblYear, $tblSchoolTypeBGy, 12);
            Reporting::useService()->setDivisionCourseList($tblDivisionCourseList, $tblYear, $tblSchoolTypeBGy, 13);

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $courseItemList[] = $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getName() . new PullRight((new Standard(
                        '',
                        '/Api/Reporting/Standard/Person/Certificate/CourseGrades/Download',
                        new Download(),
                        array(
                            'DivisionId' => $tblDivisionCourse->getId(),
                        ),
                        'Kursnoten herunterladen'
                    )));
            }
        }

        if ($serialMailItemList) {
            $panelSerialMail = new Panel('Serien-E-Mail für Prüfungsnoten', $serialMailItemList, Panel::PANEL_TYPE_INFO);
        } else {
            $panelSerialMail = false;
        }
        if ($statisticItemList) {
            $panelStatistic = new Panel('Auswertung der Prüfungsnoten für die LaSuB', $statisticItemList, Panel::PANEL_TYPE_INFO);
        } else {
            $panelStatistic = false;
        }
        if ($rankingItemList) {
            $panelRanking = new Panel('Ranglisten für Abschlusszeugnisse', $rankingItemList, Panel::PANEL_TYPE_INFO);
        } else {
            $panelRanking = false;
        }
        if ($courseItemList) {
            $panelCourse = new Panel('Kursnoten in der SekII', $courseItemList, Panel::PANEL_TYPE_INFO);
        } else {
            $panelCourse = false;
        }

        return
            new Layout(new LayoutGroup(new LayoutRow(array(
                $panelSerialMail ? new LayoutColumn($panelSerialMail, 6) : null,
                $panelStatistic ? new LayoutColumn($panelStatistic, 6) : null,
                $panelRanking ? new LayoutColumn($panelRanking, 6) : null,
                $panelCourse ? new LayoutColumn($panelCourse, 6) : null,
            ))));
    }

    /**
     * @param $View
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendDiploma($View = null, $YearId = null): Stage
    {
        switch ($View) {
            case View::HS: $description = 'Hauptschulabschlusszeugnisse'; break;
            case View::RS: $description = 'Realschulabschlusszeugnisse'; break;
            case View::ABI: $description = 'Abiturabschlusszeugnisse'; break;
            default: $description = '';
        }
        $Stage = new Stage('Zeugnisse auswerten', $description);
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $tblYearList = array();
        $generateList = array();
        if (($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAll())) {
            foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                if (($tblGenerateYear = $tblGenerateCertificate->getServiceTblYear())
                    && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && ($tblCertificateType->getIdentifier() == 'DIPLOMA')
                ) {
                    $tblYearList[$tblGenerateYear->getId()] = $tblGenerateYear;
                    $generateList[$tblGenerateYear->getId()][] = $tblGenerateCertificate;
                }
            }
        }

        $tblYear = Term::useService()->getYearById($YearId);
        if (!empty($tblYearList)) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName', null, Sorter::ORDER_DESC);
            if (!$tblYear) {
                $tblYear = current($tblYearList);
            }
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $Stage->addButton(new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        '/Education/Certificate/Reporting/Diploma', new Edit(), array(
                            'View' => $View,
                            'YearId' => $tblYearItem->getId()
                        )));
                } else {
                    $Stage->addButton(new Standard($tblYearItem->getDisplayName(), '/Education/Certificate/Reporting/Diploma',
                        null, array('View' => $View,'YearId' => $tblYearItem->getId())));
                }
            }

            if ($tblYear && isset($generateList[$tblYear->getId()])) {
                $dataList = array();
                $sum = 0;
                $count = 0;
                foreach ($generateList[$tblYear->getId()] as $item) {
                    if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($item))) {
                        foreach ($tblPrepareList as $tblPrepare) {
                            if (($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))) {
                                foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                                    if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())
                                        && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                        && (($View == View::HS && strpos($tblCertificate->getCertificate(), 'MsAbsHs') !== false)
                                            || ($View == View::RS && strpos($tblCertificate->getCertificate(), 'MsAbsRs') !== false)
                                            || ($View == View::ABI && strpos($tblCertificate->getCertificate(), 'GymAbitur') !== false)
                                        )
                                    ) {
                                        if ($View == View::ABI) {
                                            // Berechnung der Gesamtqualifikation und der Durchschnittsnote
                                            /** @noinspection PhpUnusedLocalVariableInspection */
                                            list($countCourses, $resultBlockI) = Prepare::useService()->getResultForAbiturBlockI($tblPrepare, $tblPerson);
                                            $resultBlockII = Prepare::useService()->getResultForAbiturBlockII($tblPrepare, $tblPerson);
                                            $resultPoints = $resultBlockI + $resultBlockII;
                                            if ($resultBlockI >= 200 && $resultBlockII >= 100) {
                                                $average = Prepare::useService()->getResultForAbiturAverageGrade($resultPoints);
                                                if ($average !== '&nbsp;') {
                                                    $average = str_replace(',', '.', $average);
                                                } else {
                                                    $average = false;
                                                }
                                            } else {
                                                $average = false;
                                            }
                                        } else {
                                            $average = $this->calcDiplomaAverageGrade($tblPrepare, $tblPerson);
                                        }

                                        if ($average) {
                                            $sum += $average;
                                            $count++;
                                        }
                                        $dataList[$tblPerson->getId()] = array(
                                            'Name' => $tblPerson->getLastFirstName(),
                                            'Average' => $average ? str_replace('.', ',', $average) : '&ndash;'
                                        );

                                        if ($View == View::RS) {
                                            $dataList[$tblPerson->getId()]['AverageWithDroppedSubjects']
                                                = ($averageWithDroppedSubjects = $this->calcDiplomaAverageGradeWithDroppedSubjects($tblPrepare, $tblPerson))
                                                    ? str_replace('.', ',', $averageWithDroppedSubjects)
                                                    : '&ndash;';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($dataList)) {
                    $columns = array(
                        'Name' => 'Name',
                        'Average' => 'Notendurchschnitt',
                    );

                    $columDefs[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0);
                    $columDefs[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1);

                    if ($View == View::RS) {
                        $columns['AverageWithDroppedSubjects'] = 'Notendurchschnitt (mit abgewählte Fächer der Klassenstufe 9)';
                        $columDefs[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2);
                    }

                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                new Panel(
                                    $description,
                                    'Gesamtnotendurchschnitt: ' . ($count > 0 ? str_replace('.', ',', round(floatval($sum / $count), 1)) : '&ndash;'),
                                    Panel::PANEL_TYPE_INFO
                                )
                            )),
                            new LayoutRow(new LayoutColumn(
                                new TableData(
                                    $dataList,
                                    null,
                                    $columns,
                                    array(
                                        'columnDefs' => $columDefs,
                                        'order' => array(
                                            array(1, 'asc'),
                                        )
                                    )
                                )
                            ))
                        )))
                    );
                } else {
                    $Stage->setContent(new Warning('Es sind noch keine Abschlusszeugnisse für das Schuljahr: '
                        . $tblYear->getDisplayName() . ' vorhanden.', new Exclamation()));
                }
            }
        } else {
            $Stage->setContent(new Warning('Es sind noch keine Abschlusszeugnisse vorhanden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return bool|false|float
     */
    public function calcDiplomaAverageGrade(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {
        $gradeList = array();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
            ))
        ) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if ($tblPrepareAdditionalGrade->getGrade() != ''
                    && ($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                ) {
                    $grade = str_replace('+', '', $tblPrepareAdditionalGrade->getGrade());
                    $grade = str_replace('-', '', $grade);
                    if (is_numeric($grade)) {
                        $gradeList[$tblSubject->getId()] = $grade;
                    }
                }
            }
        }

        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
            && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
        ) {
            foreach ($tblTaskGradeList as $tblTaskGrade) {
                if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                    && !isset($gradeList[$tblSubject->getId()])
                    && $tblTaskGrade->getIsGradeNumeric()
                ) {
                    $gradeList[$tblSubject->getId()] = $tblTaskGrade->getGradeNumberValue();
                }
            }
        }

        if (!empty($gradeList)) {
            return round(floatval(array_sum($gradeList) / count($gradeList)), 2);
        }

        return false;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param TblPerson $tblPerson
     *
     * @return bool|false|float
     */
    public function calcDiplomaAverageGradeWithDroppedSubjects(TblPrepareCertificate $tblPrepare, TblPerson $tblPerson)
    {
        $gradeList = array();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('EN'))
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
            ))
        ) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if ($tblPrepareAdditionalGrade->getGrade() != ''
                    && ($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                ) {
                    $grade = str_replace('+', '', $tblPrepareAdditionalGrade->getGrade());
                    $grade = str_replace('-', '', $grade);
                    if (is_numeric($grade)) {
                        $gradeList[$tblSubject->getId()] = $grade;
                    }
                }
            }
        }

        if (($tblTask = $tblPrepare->getServiceTblAppointedDateTask())
            && ($tblTaskGradeList = Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson))
        ) {
            foreach ($tblTaskGradeList as $tblTaskGrade) {
                if (($tblSubject = $tblTaskGrade->getServiceTblSubject())
                    && !isset($gradeList[$tblSubject->getId()])
                    && $tblTaskGrade->getIsGradeNumeric()
                ) {
                    $gradeList[$tblSubject->getId()] = $tblTaskGrade->getGradeNumberValue();
                }
            }
        }

        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy(
                $tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType
            ))
        ) {
            foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                if ($tblPrepareAdditionalGrade->getGrade() != ''
                    && ($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())
                ) {
                    $grade = str_replace('+', '', $tblPrepareAdditionalGrade->getGrade());
                    $grade = str_replace('-', '', $grade);
                    if (is_numeric($grade)) {
                        $gradeList[$tblSubject->getId()] = $grade;
                    }
                }
            }
        }

        if (!empty($gradeList)) {
            return round(floatval(array_sum($gradeList) / count($gradeList)), 2);
        }

        return false;
    }
}