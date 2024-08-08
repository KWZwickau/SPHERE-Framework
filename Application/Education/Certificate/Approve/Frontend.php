<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2016
 * Time: 16:29
 */

namespace SPHERE\Application\Education\Certificate\Approve;

use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Approve
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendSelectPrepare($IsAllYears = null, $YearId = null): Stage
    {
        $Stage = new Stage('Zeugnisse freigeben', 'Übersicht');

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        }

        if ($tblYearList) {
            if($tblYear || $IsAllYears){
                $Stage->addButton(new Standard('Aktuelles Schuljahr', '/Education/Certificate/Approve'));
            } else {
                $Stage->addButton(new Standard(new Info(new Bold('Aktuelles Schuljahr')), '/Education/Certificate/Approve', new Edit()));
            }

            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $Stage->addButton(new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        '/Education/Certificate/Approve', new Edit(), array('YearId' => $tblYearItem->getId())));
                } else {
                    $Stage->addButton(new Standard($tblYearItem->getDisplayName(), '/Education/Certificate/Approve',
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }

            if ($IsAllYears) {
                $Stage->addButton(new Standard(new Info(new Bold('Alle Schuljahre')),
                    '/Education/Certificate/Approve', new Edit(), array('IsAllYears' => true)));
            } else {
                $Stage->addButton(new Standard('Alle Schuljahre', '/Education/Certificate/Approve', null,
                    array('IsAllYears' => true)));
            }
        }

        $content = false;
        $prepareList = array();
        $tblLeaveStudentList = array();
        if ($IsAllYears) {
            $tblLeaveStudentList = Prepare::useService()->getLeaveStudentAll();
        } elseif ($tblYear) {
            $tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear);
        } elseif ($tblYearList) {
            foreach ($tblYearList as $tblYearTemp) {
                if (($tempList = Prepare::useService()->getLeaveStudentAllByYear($tblYearTemp))) {
                    $tblLeaveStudentList = array_merge($tblLeaveStudentList, $tempList);
                }
            }
        }

        if ($tblLeaveStudentList) {
            $leaveStudentDivisionList = array();
            $isAlreadyPrintedList = array();
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblYearLeave = $tblLeaveStudent->getServiceTblYear())
                    && ($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
                ) {
                    if (($tblLeaveInformationCertificateDate = Prepare::useService()->getLeaveInformationBy($tblLeaveStudent, 'CertificateDate'))) {
                        $date = $tblLeaveInformationCertificateDate->getValue();
                    } else {
                        $date = '';
                    }

                    if (isset($leaveStudentDivisionList[$tblDivisionCourse->getId()])) {
                        if (!$leaveStudentDivisionList[$tblDivisionCourse->getId()]['Date'] && $date) {
                            $leaveStudentDivisionList[$tblDivisionCourse->getId()]['Date'] = $date;
                        }
                        $leaveStudentDivisionList[$tblDivisionCourse->getId()]['CountTotalCertificates']++;
                        if ($tblLeaveStudent->isApproved()) {
                            $leaveStudentDivisionList[$tblDivisionCourse->getId()]['CountApprovedCertificates']++;
                        }
                    } else {
                        $leaveStudentDivisionList[$tblDivisionCourse->getId()] = array(
                            'Year' => $tblYearLeave->getDisplayName(),
                            'Date' => $date,
                            'CountTotalCertificates' => 1,
                            'CountApprovedCertificates' => $tblLeaveStudent->isApproved() ? 1 : 0,
                            'DivisionDisplayName' => $tblDivisionCourse->getDisplayName(),
                            'DivisionId' => $tblDivisionCourse->getId(),
                        );
                    }

                    if ($tblLeaveStudent->isPrinted()) {
                        $isAlreadyPrintedList[$tblDivisionCourse->getId()] = true;
                    }
                }
            }

            $isLeaveAutomaticallyApproved = ($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('LEAVE')) && $tblCertificateType->isAutomaticallyApproved();
            foreach ($leaveStudentDivisionList as $item) {
                $countApproved = $item['CountApprovedCertificates'];
                $countStudents = $item['CountTotalCertificates'];

                $status = $this->getApproveStatusText($isLeaveAutomaticallyApproved, $countApproved, $countStudents);

                $prepareList[] = array(
                    'Year' => $item['Year'],
                    'Date' => $item['Date'],
                    'Name' => 'Abgangszeugnis',
                    'Division' => $item['DivisionDisplayName'],
                    'CertificateType' => 'Abgangszeugnis',
                    'Status' => $status,
                    'Option' =>
                        (new Standard(
                            '',
                            '/Education/Certificate/Approve/Prepare',
                            new EyeOpen(),
                            array(
                                'DivisionId' => $item['DivisionId'],
                                'IsLeave' => true
                            ),
                            'Klassenansicht -> Zeugnisse einzeln freigeben'
                        ))
                        . (new External(
                            '',
                            '/Api/Education/Certificate/Generator/PreviewMultiLeavePdf',
                            new Download(),
                            array(
                                'DivisionId' => $item['DivisionId'],
                                'Name' => 'Zeugnismuster'
                            ), 'Alle Zeugnisse als Muster herunterladen'))
                        . (new Standard(
                            '',
                            '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                            new Check(),
                            array(
                                'DivisionId' => $item['DivisionId'],
                                'IsLeave' => true,
                                'Route' => '/Education/Certificate/Approve'
                            ),
                            'Alle Zeugnisse dieser Klasse freigeben'
                        ))
                        . (isset($isAlreadyPrintedList[$item['DivisionId']])
                            ? (new Standard('', ApiPrepare::getEndpoint(), new Disable()))
                                ->ajaxPipelineOnClick(ApiPrepare::pipelineOpenResetApprovedModal('/Education/Certificate/Approve', null, $item['DivisionId'], true))
                            : (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                new Disable(),
                                array(
                                    'DivisionId' => $item['DivisionId'],
                                    'IsLeave' => true,
                                    'Route' => '/Education/Certificate/Approve'
                                ),
                                'Alle Zeugnisfreigaben dieser Klasse entfernen'
                            )))
                );
            }
        }

        if ($tblYear
            || (!$IsAllYears && !empty($tblYearList)) ) {
            if(!$tblYear){
                $tblPrepareList = array();
                // aktuelles Jahr
                foreach($tblYearList as $tblYearTemp){
                    if(($tblPrepareListTemp = Prepare::useService()->getPrepareAllByYear($tblYearTemp))){
                        $tblPrepareList = array_merge($tblPrepareList, $tblPrepareListTemp);
                    }
                }
                if(empty($tblPrepareList)){
                    $tblPrepareList = false;
                }
            } else {
                $tblPrepareList = Prepare::useService()->getPrepareAllByYear($tblYear);
            }
            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepare) {
                    $countStudents = 0;
                    $countApproved = 0;
                    $isAutomaticallyApproved = ($tblCertificateType = $tblPrepare->getCertificateType()) && $tblCertificateType->isAutomaticallyApproved();
                    $isInEdit = false;
                    $isAlreadyPrinted = false;
                    if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                        && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
                    ) {
                        foreach ($tblPersonList as $tblPerson) {
                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                                if ($tblPrepareStudent->getServiceTblCertificate()) {
                                    $countStudents++;
                                    if (!$tblPrepare->getIsPrepared() && !$isInEdit
                                        && (Prepare::useService()->getBehaviorGradeAllByPrepareCertificateAndPerson($tblPrepare, $tblPerson)
                                            || Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare, $tblPerson))
                                    ) {
                                        $isInEdit = true;
                                    }
                                }
                                if ($tblPrepareStudent->isApproved()) {
                                    $countApproved++;
                                }
                                if ($tblPrepareStudent->isPrinted()) {
                                    $isAlreadyPrinted = true;
                                }
                            }
                        }
                    }

                    $status = $this->getApproveStatusText($isAutomaticallyApproved, $countApproved, $countStudents);

                    if ($tblPrepare->getIsPrepared()) {
                        $prepareStatus = new Success('abgeschlossen');
                    } elseif ($isInEdit) {
                        $prepareStatus = new Warning('in Bearbeitung');
                    } else {
                        $prepareStatus = new \SPHERE\Common\Frontend\Text\Repository\Danger('offen');
                    }

                    if ($countStudents > 0) {
                        $prepareList[] = array(
                            'Year' => $tblDivisionCourse && $tblDivisionCourse->getServiceTblYear() ? $tblDivisionCourse->getServiceTblYear()->getDisplayName() : '',
                            'Date' => $tblPrepare->getDate(),
                            'Name' => $tblPrepare->getName(),
                            'Division' => $tblDivisionCourse ? $tblDivisionCourse->getDisplayName() : '',
                            'CertificateType' =>
                                $tblCertificateType ? $tblCertificateType->getName() : '',
                            'PrepareStatus' => $prepareStatus,
                            'Status' => $status,
                            'Option' =>
                                (new Standard(
                                    '',
                                    '/Education/Certificate/Approve/Prepare',
                                    new EyeOpen(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId()
                                    ),
                                    'Klassenansicht -> Zeugnisse einzeln freigeben'
                                ))
                                . (new External(
                                    '',
                                    '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Zeugnismuster'
                                    ), 'Alle Zeugnisse als Muster herunterladen'))
                                . (new Standard(
                                    '',
                                    '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                    new Check(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Route' => '/Education/Certificate/Approve'
                                    ),
                                    'Alle Zeugnisse dieser Klasse freigeben'
                                ))
                                . ($isAlreadyPrinted
                                    ? (new Standard('', ApiPrepare::getEndpoint(), new Disable()))
                                        ->ajaxPipelineOnClick(ApiPrepare::pipelineOpenResetApprovedModal('/Education/Certificate/Approve', $tblPrepare->getId()))
                                    : (new Standard(
                                    '',
                                    '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                    new Disable(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Route' => '/Education/Certificate/Approve'
                                    ),
                                    'Alle Zeugnisfreigaben dieser Klasse entfernen'
                                )))
                        );
                    }
                }
            }

            if (!empty($prepareList) && $tblYear) {
                $content = new TableData($prepareList, null,
                    array(
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Zeugnisauftrag',
                        'CertificateType' => 'Zeugnistyp',
                        'PrepareStatus' => 'Zeugnis&shy;vorbereitung',
                        'Status' => 'Freigaben',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                            array(2, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0),
                            array('type' => 'natural', 'targets' => 1),
                            array('orderable' => false, 'width' => '138px', 'targets' => -1),
                            array('searchable' => false, 'targets' => -1),
                        )
                    )
                );
            } elseif(!empty($prepareList)) {
                $content = new TableData($prepareList, null,
                    array(
                        'Year' => 'Schuljahr',
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Zeugnisauftrag',
                        'CertificateType' => 'Zeugnistyp',
                        'PrepareStatus' => 'Zeugnis&shy;vorbereitung',
                        'Status' => 'Freigaben',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(2, 'asc'),
                            array(3, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0),
                            array('type' => 'natural', 'targets' => 1),
                            array('orderable' => false, 'width' => '138px', 'targets' => -1),
                            array('searchable' => false, 'targets' => -1),
                        )
                    )
                );
            } else {
                $content = new \SPHERE\Common\Frontend\Message\Repository\Warning('
                    Es liegen aktuell keine Zeugnisse zum Freigeben vor.', new Exclamation());
            }
        } else {
            if (($tblPrepareList = Prepare::useService()->getPrepareAll())) {
                foreach ($tblPrepareList as $tblPrepare) {
                    if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())) {
                        $prepareList[] = array(
                            'Year' => $tblDivisionCourse->getServiceTblYear()
                                ? $tblDivisionCourse->getServiceTblYear()->getDisplayName() : '',
                            'Date' => $tblPrepare->getDate(),
                            'Name' => $tblPrepare->getName(),
                            'Division' => $tblDivisionCourse->getDisplayName(),
                            'Option' =>
                                (new Standard(
                                    '',
                                    '/Education/Certificate/Approve/Prepare',
                                    new EyeOpen(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'IsAllYears' => true
                                    ),
                                    'Klassenansicht -> Zeugnisse einzeln freigeben'
                                ))
                                . (new External(
                                    '',
                                    '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Zeugnismuster'
                                    ), 'Alle Zeugnisse als Muster herunterladen'))
                                . (new Standard(
                                    '',
                                    '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                    new Check(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Route' => '/Education/Certificate/Approve',
                                        'IsAllYears' => true
                                    ),
                                    'Alle Zeugnisse dieser Klasse freigeben'
                                ))
                                . (new Standard(
                                    '',
                                    '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                    new Disable(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Route' => '/Education/Certificate/Approve',
                                        'IsAllYears' => true
                                    ),
                                    'Alle Zeugnisfreigaben dieser Klasse entfernen'
                                ))
                        );
                    }
                }

                $content = new TableData($prepareList, null,
                    array(
                        'Year' => 'Schuljahr',
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Name',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'desc'),
                            array(2, 'asc'),
                            array(3, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 1),
                            array('type' => 'natural', 'targets' => 2),
                            array('orderable' => false, 'width' => '138px', 'targets' => -1),
                        )
                    )
                );
            }
        }

        $Stage->setContent(
            ApiPrepare::receiverModal()
            . new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Schuljahr',
                                ($tblYear
                                    ? $tblYear->getDisplayName()
                                    : ($IsAllYears
                                        ? 'Alle Schuljahre'
                                        : 'Aktuelles Schuljahr'
                                    )
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        )),
                        new LayoutColumn(array(
                            $content ?: null
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $isAutomaticallyApproved
     * @param $countApproved
     * @param $countStudents
     *
     * @return Success|Warning
     */
    private function getApproveStatusText($isAutomaticallyApproved, $countApproved, $countStudents)
    {
        if ($isAutomaticallyApproved) {
            if ($countApproved > 0) {
                $countDiff = $countStudents - $countApproved;
                $status = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    . ' ' . $countApproved . ($countApproved == 1 ? ' Zeugnis ' : ' Zeugnisse ') . 'freigegeben'
                    . ($countDiff > 0
                        ? ' und ' . $countDiff . ($countDiff == 1 ? ' Zeugnis wird ' : ' Zeugnisse werden ') . 'automatisch freigegeben.'
                        : '.'
                    ));
            } else {
                $status = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    . ' ' . $countStudents . ($countStudents == 1 ? ' Zeugnis wird ' : ' Zeugnisse werden ') . 'automatisch freigegeben.');
            }
        } else {
            $status = $countApproved < $countStudents
                ? new Warning(new Exclamation() . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnisse freigegeben.')
                : new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnissen freigegeben.');
        }

        return $status;
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param null $IsLeave
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendDivision(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = null,
        $IsAllYears = null
    ) {

        $Stage = new Stage('Zeugnisse freigeben', 'Klassenansicht');

        // Abgangszeugnisse
        if ($IsLeave) {
            if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Approve', new ChevronLeft(),
                        array(
                            'IsAllYears' => $IsAllYears
                        )
                    )
                );

                $studentTable = array();
                $isAlreadyPrinted = false;
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                            && ($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                            && $tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId()
                        ) {
                            $courseName = '';
                            // Bildungsgang
                            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                                $tblCourse = $tblStudentEducation->getServiceTblCourse();
                                // berufsbildende Schulart
                                if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                                    $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                                } else {
                                    $courseName = $tblCourse ? $tblCourse->getName() : '';
                                }
                            }
                            $tblCertificate = $tblLeaveStudent->getServiceTblCertificate();
                            $isApproved = $tblLeaveStudent->isApproved();

                            if ($tblCertificate
                                && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                                && $tblCertificateType->isAutomaticallyApproved()
                            ) {
                                $isAutomaticallyApproved = true;
                            } else {
                                $isAutomaticallyApproved = false;
                            }

                            if ($isAutomaticallyApproved) {
                                $status = $isApproved
                                    ? new Success(new Enable() . ' freigegeben')
                                    : new Success(new Enable() . ' wird automatisch freigegeben');
                            } else {
                                $status = $isApproved
                                    ? new Success(new Enable() . ' freigegeben')
                                    : new Warning(new Exclamation() . ' nicht freigegeben');
                            }

                            if ($tblLeaveStudent->isApproved()) {
                                $isAlreadyPrinted = true;
                            }

                            $studentTable[] = array(
                                'Name' => $tblPerson->getLastFirstName(),
                                'Course' => $courseName,
                                'Template' => ($tblCertificate
                                    ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                    : new Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
                                'Status' => $status,
                                'Option' =>
                                    ($tblCertificate ? new External(
                                        'Zeugnis herunterladen',
                                        '/Api/Education/Certificate/Generator/PreviewLeave',
                                        new Download(),
                                        array(
                                            'LeaveStudentId' => $tblLeaveStudent->getId(),
                                            'Name' => 'Zeugnismuster'
                                        ), 'Zeugnis als Muster herunterladen')
                                        : '')
                                    . (!$isApproved && $tblCertificate ? (new Standard(
                                        'Zeugnis freigeben', '/Education/Certificate/Approve/Prepare/SetApproved',
                                        new Check(),
                                        array(
                                            'LeaveStudentId' => $tblLeaveStudent->getId(),
                                            'IsLeave' => true,
                                            'IsAllYears' => $IsAllYears
                                        ),
                                        'Zeugnis freigeben')) : '')
                                    . ($isApproved ? (new Standard(
                                        'Zeugnisfreigabe entfernen',
                                        '/Education/Certificate/Approve/Prepare/ResetApproved',
                                        new Disable(),
                                        array(
                                            'LeaveStudentId' => $tblLeaveStudent->getId(),
                                            'IsLeave' => true,
                                            'IsAllYears' => $IsAllYears
                                        ),
                                        'Zeugnisfreigabe entfernen')) : '')
                            );
                        }
                    }
                }

                $Stage->setContent(
                    ApiPrepare::receiverModal()
                    . new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new External(
                                        'Alle Zeugnisse als Muster herunterladen',
                                        '/Api/Education/Certificate/Generator/PreviewMultiLeavePdf',
                                        new Download(),
                                        array(
                                            'DivisionId' => $tblDivisionCourse->getId(),
                                            'Name' => 'Zeugnismuster'
                                        ), 'Alle Zeugnisse als Muster herunterladen'),
                                    new Standard(
                                        'Alle Zeugnisse dieser Klasse freigeben',
                                        '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                        new Check(),
                                        array(
                                            'DivisionId' => $tblDivisionCourse->getId(),
                                            'IsLeave' => true,
                                            'Route' => '/Education/Certificate/Approve/Prepare',
                                            'IsAllYears' => $IsAllYears
                                        )
                                    ),
                                    ($isAlreadyPrinted
                                        ? (new Standard('Alle Zeugnisfreigaben dieser Klasse entfernen', ApiPrepare::getEndpoint(), new Disable()))
                                            ->ajaxPipelineOnClick(ApiPrepare::pipelineOpenResetApprovedModal(
                                                '/Education/Certificate/Approve/Prepare', null, $tblDivisionCourse->getId(), true, $IsAllYears
                                            ))
                                        : new Standard(
                                            'Alle Zeugnisfreigaben dieser Klasse entfernen',
                                            '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                            new Disable(),
                                            array(
                                                'DivisionId' => $tblDivisionCourse->getId(),
                                                'IsLeave' => true,
                                                'Route' => '/Education/Certificate/Approve/Prepare',
                                                'IsAllYears' => $IsAllYears
                                            )
                                        ))
                                )),
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($studentTable, null, array(
                                        'Name' => 'Name',
                                        'Course' => 'Bildungs&shy;gang',
                                        'Template' => 'Zeugnis&shy;vorlage',
                                        'Status' => 'Freigabe',
                                        'Option' => ''
                                    ), array(
                                        'order' => array(
                                            array('0', 'asc'),
                                        ),
                                        "paging" => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    ))
                                ))
                            ))
                        ), new Title('Übersicht'))
                    ))
                );

                return $Stage;

            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        // Zeugnisse mit Zeugnisauftrag
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                $isAlreadyPrinted = false;
                $studentTable = array();
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(),
                    $IsAllYears
                        ? array('IsAllYears' => $IsAllYears)
                        : array('YearId' => ($tblDivisionCourse->getServiceTblYear() ? $tblDivisionCourse->getServiceTblYear()->getId() : null))
                ));

                $isAutomaticallyApproved = ($tblCertificateType = $tblPrepare->getCertificateType()) && $tblCertificateType->isAutomaticallyApproved();
                if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                    foreach ($tblPersonList as $tblPerson) {
                        $courseName = '';
                        // Bildungsgang
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                            $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                            $tblCourse = $tblStudentEducation->getServiceTblCourse();
                            // berufsbildende Schulart
                            if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                                $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                            } else {
                                $courseName = $tblCourse ? $tblCourse->getName() : '';
                            }
                        }

                        $prepareStatus = '&nbsp;';
                        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                            $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                            $isApproved = $tblPrepareStudent->isApproved();
                            if ($tblCertificate) {
                                if ($tblPrepare->getIsPrepared()) {
                                    $prepareStatus = new Success('abgeschlossen');
                                } else {
                                    if (Prepare::useService()->getBehaviorGradeAllByPrepareCertificateAndPerson($tblPrepare, $tblPerson)
                                        || Prepare::useService()->getPrepareInformationAllByPerson($tblPrepare, $tblPerson)
                                    ) {
                                        $prepareStatus = new Warning('in Bearbeitung');
                                    } else {
                                        $prepareStatus = new \SPHERE\Common\Frontend\Text\Repository\Danger('offen');
                                    }
                                }
                            }

                            if ($tblPrepareStudent->isPrinted()) {
                                $isAlreadyPrinted = true;
                            }
                        } else {
                            $tblCertificate = false;
                            $isApproved = false;
                        }

                        if ($isAutomaticallyApproved) {
                            $status = $isApproved
                                ? new Success(new Enable() . ' freigegeben')
                                : new Success(new Enable() . ' wird automatisch freigegeben');
                        } else {
                            $status = $isApproved
                                ? new Success(new Enable() . ' freigegeben')
                                : new Warning(new Exclamation() . ' nicht freigegeben');
                        }

                        if (!$tblPrepareStudent || !$tblCertificate) {
                            $status = '&nbsp;';
                        }

                        $studentTable[] = array(
                            'Number' => count($studentTable) + 1,
                            'Name' => $tblPerson->getLastFirstName(),
                            'Course' => $courseName,
                            'Template' => ($tblCertificate
                                ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                    . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                : new Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
                            'PrepareStatus' => $prepareStatus,
                            'Status' => $status,
                            'Option' =>
                                ($tblCertificate ? new External(
                                    'Zeugnis herunterladen',
                                    '/Api/Education/Certificate/Generator/Preview',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                    ), false) : '')
                                . (!$isApproved && $tblCertificate ? (new Standard(
                                    'Zeugnis freigeben', '/Education/Certificate/Approve/Prepare/SetApproved',
                                    new Check(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'IsAllYears' => $IsAllYears
                                    ),
                                    'Zeugnis freigeben')) : '')
                                . ($isApproved ? (new Standard(
                                    'Zeugnisfreigabe entfernen',
                                    '/Education/Certificate/Approve/Prepare/ResetApproved',
                                    new Disable(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'PersonId' => $tblPerson->getId(),
                                        'IsAllYears' => $IsAllYears
                                    ),
                                    'Zeugnisfreigabe entfernen')) : '')
                        );
                    }
                }

                $Stage->setContent(
                    ApiPrepare::receiverModal()
                    . new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new External(
                                        'Alle Zeugnisse als Muster herunterladen',
                                        '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                        new Download(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'Name' => 'Zeugnismuster'
                                        ), 'Alle Zeugnisse als Muster herunterladen'),
                                    new Standard(
                                        'Alle Zeugnisse dieser Klasse freigeben',
                                        '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                        new Check(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'Route' => '/Education/Certificate/Approve/Prepare',
                                            'IsAllYears' => $IsAllYears
                                        )
                                    ),
                                    ($isAlreadyPrinted
                                        ? (new Standard('Alle Zeugnisfreigaben dieser Klasse entfernen', ApiPrepare::getEndpoint(), new Disable()))
                                            ->ajaxPipelineOnClick(ApiPrepare::pipelineOpenResetApprovedModal(
                                                '/Education/Certificate/Approve/Prepare', $tblPrepare->getId(), null, null, $IsAllYears
                                            ))
                                        : new Standard(
                                            'Alle Zeugnisfreigaben dieser Klasse entfernen',
                                            '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                            new Disable(),
                                            array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'Route' => '/Education/Certificate/Approve/Prepare',
                                                'IsAllYears' => $IsAllYears
                                            )
                                        )
                                    ),
                                )),
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($studentTable, null, array(
                                        'Number' => '#',
                                        'Name' => 'Name',
                                        'Course' => 'Bildungs&shy;gang',
                                        'Template' => 'Zeugnis&shy;vorlage',
                                        'PrepareStatus' => 'Zeugnis&shy;vorbereitung',
                                        'Status' => 'Freigabe',
                                        'Option' => ''
                                    ), array(
                                        'order' => array(
                                            array('0', 'asc'),
                                        ),
                                        "paging" => false, // Deaktivieren Blättern
                                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                                    ))
                                ))
                            ))
                        ), new Title('Übersicht'))
                    ))
                );

                return $Stage;
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $LeaveStudentId
     * @param null $IsLeave
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendApprovePreparePerson(
        $PrepareId = null,
        $PersonId = null,
        $LeaveStudentId = null,
        $IsLeave = null,
        $IsAllYears = null
    ) {
        $Stage = new Stage('Zeugnisse freigeben', 'Freigabe');
        if ($IsLeave) {
            if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($LeaveStudentId))
                && $tblLeaveStudent->getServiceTblPerson()
                && ($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
            ) {
                Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, $tblLeaveStudent->isPrinted());

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnis wurde freigegeben.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivisionCourse->getId(), 'IsLeave' => true,'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblPerson = Person::useService()->getPersonById($PersonId))
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            ) {

                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve/Prepare', new ChevronLeft(),
                    array(
                        'PrepareId' => $tblPrepare->getId(),
                        'IsAllYears' => $IsAllYears
                    )
                ));

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 12),
                            )),
                        )),
                    ))
                );

                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    Prepare::useService()->updatePrepareStudentSetApproved($tblPrepareStudent);

                    return $Stage
                        . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnis wurde freigegeben.',
                            new \SPHERE\Common\Frontend\Icon\Repository\Success())
                        . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                            array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
                }

                return $Stage;

            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $LeaveStudentId
     * @param null $IsLeave
     * @param null $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendResetApprovePreparePerson(
        $PrepareId = null,
        $PersonId = null,
        $LeaveStudentId = null,
        $IsLeave = null,
        $IsAllYears = null
    ) {
        $Stage = new Stage('Zeugnis', 'Freigabe entfernen');
        if ($IsLeave) {
            if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($LeaveStudentId))
                && ($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
            ) {
                Prepare::useService()->updateLeaveStudent($tblLeaveStudent, false, false);

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigabe wurde entfernt.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivisionCourse->getId(), 'IsLeave' => true,'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblPerson = Person::useService()->getPersonById($PersonId))
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            ) {

                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve/Prepare', new ChevronLeft(),
                    array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears)
                ));

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 12),
                            )),
                        )),
                    ))
                );

                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                    Prepare::useService()->updatePrepareStudentResetApproved($tblPrepareStudent);

                    return $Stage
                        . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigabe wurde entfernt.',
                            new \SPHERE\Common\Frontend\Icon\Repository\Success())
                        . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                            array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
                }

                return $Stage;

            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param null $IsLeave
     * @param string $Route
     * @param null $IsAllYears
     *
     * @return string
     */
    public function frontendApprovePrepareDivision(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = null,
        string $Route = '/Education/Certificate/Approve/Prepare',
        $IsAllYears = null
    ): string {
        $Stage = new Stage('Zeugnisse freigeben', 'Kurs freigeben');

        if ($IsLeave) {
            if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if (($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                            && ($tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId())
                            && !$tblLeaveStudent->isApproved()
                        ) {
                            Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, $tblLeaveStudent->isPrinted());
                        }
                    }
                }

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisse wurden freigegeben.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivisionCourse->getId(), 'IsLeave' => $IsLeave, 'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', $Route, new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            ) {

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                            )),
                        )),
                    ))
                );

                Prepare::useService()->updatePrepareStudentListSetApproved($tblPrepare);

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisse wurden freigegeben.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', $Route, new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        }
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param null $IsLeave
     * @param string $Route
     * @param null $IsAllYears
     *
     * @return string
     */
    public function frontendResetApprovePrepareDivision(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = null,
        string $Route = '/Education/Certificate/Approve/Prepare',
        $IsAllYears = null
    ): string {
        $Stage = new Stage('Zeugnisse freigeben', 'Kurs Freigabe entfernen');

        if ($IsLeave) {
            if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if (($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                            && ($tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId())
                            && $tblLeaveStudent->isApproved()
                        ) {
                            Prepare::useService()->updateLeaveStudent($tblLeaveStudent, false, false);
                        }
                    }
                }

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigaben wurden entfernt.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivisionCourse->getId(), 'IsLeave' => $IsLeave, 'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', $Route, new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            ) {

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Zeugnisvorbereitung',
                                        array(
                                            $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        ),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                            )),
                        )),
                    ))
                );

                Prepare::useService()->updatePrepareStudentListResetApproved($tblPrepare);

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigaben wurden entfernt.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('PrepareId' => $tblPrepare->getId(), 'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', $Route, new ChevronLeft()
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        }
    }
}