<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.07.2016
 * Time: 16:29
 */

namespace SPHERE\Application\Education\Certificate\Approve;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
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
     * @param bool $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendSelectPrepare($IsAllYears = false, $YearId = null)
    {

        $Stage = new Stage('Zeugnisse freigeben', 'Übersicht');

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList) {
            $tblYear = end($tblYearList);
        }

        if ($tblYearList) {
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

        if (($tblLeaveStudentAll = Prepare::useService()->getLeaveStudentAll())) {
            $leaveStudentDivisionList = array();
            foreach ($tblLeaveStudentAll as $tblLeaveStudent) {
                if (($tblDivision = $tblLeaveStudent->getServiceTblDivision())
                    && (($tblYearDivision = $tblDivision->getServiceTblYear()))
                ) {
                    if ($tblYear && $tblYear->getId() != $tblYearDivision->getId()) {
                        continue;
                    }

                    if (($tblLeaveInformationCertificateDate = Prepare::useService()->getLeaveInformationBy(
                        $tblLeaveStudent, 'CertificateDate'))
                    ) {
                        $date = $tblLeaveInformationCertificateDate->getValue();
                    } else {
                        $date = '';
                    }

                    if (isset($leaveStudentDivisionList[$tblDivision->getId()])) {
                        if (!$leaveStudentDivisionList[$tblDivision->getId()]['Date'] && $date) {
                            $leaveStudentDivisionList[$tblDivision->getId()]['Date'] = $date;
                        }
                        $leaveStudentDivisionList[$tblDivision->getId()]['CountTotalCertificates']++;
                        if ($tblLeaveStudent->isApproved()) {
                            $leaveStudentDivisionList[$tblDivision->getId()]['CountApprovedCertificates']++;
                        }
                    } else {
                        $leaveStudentDivisionList[$tblDivision->getId()] = array(
                            'Date' => $date,
                            'CountTotalCertificates' => 1,
                            'CountApprovedCertificates' => $tblLeaveStudent->isApproved() ? 1 : 0,
                            'DivisionDisplayName' => $tblDivision->getDisplayName(),
                            'DivisionId' => $tblDivision->getId(),
                        );
                    }
                }
            }

            if (($tblCertificateType = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
                && $tblCertificateType->isAutomaticallyApproved()
            ) {
                $isLeaveAutomaticallyApproved = true;
            } else {
                $isLeaveAutomaticallyApproved = false;
            }
            foreach ($leaveStudentDivisionList as $item) {
                if ($isLeaveAutomaticallyApproved) {
                    $status = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' ' .
                        $item['CountTotalCertificates']
                        . ' Zeugnisse werden automatisch freigegeben.');
                } else {
                    $countApproved = $item['CountApprovedCertificates'];
                    $countStudents = $item['CountTotalCertificates'];

                    $status = $countApproved < $countStudents
                        ? new Warning(new Exclamation() . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnisse freigegeben.')
                        : new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                            . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnissen freigegeben.');
                }

                $prepareList[] = array(
                    'Year' => $tblYearDivision->getDisplayName(),
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
                        . (new Standard(
                            '',
                            '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                            new Disable(),
                            array(
                                'DivisionId' => $item['DivisionId'],
                                'IsLeave' => true,
                                'Route' => '/Education/Certificate/Approve'
                            ),
                            'Alle Zeugnisfreigaben dieser Klasse entfernen'
                        ))
                );
            }
        }

        if ($tblYear) {
            $tblPrepareList = Prepare::useService()->getPrepareAllByYear($tblYear);
            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepare) {
                    $countStudents = 0;
                    $countApproved = 0;
                    $isAutomaticallyApproved = false;
                    $tblDivision = $tblPrepare->getServiceTblDivision();

                    if (($tblCertificateType = $tblPrepare->getCertificateType())
                        && $tblCertificateType->isAutomaticallyApproved()
                    ) {
                        $isAutomaticallyApproved = true;
                    }

                    if ($tblDivision) {
                        if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                            $countStudents = count($tblPersonList);
                            if (!$isAutomaticallyApproved) {
                                foreach ($tblPersonList as $tblPerson) {
                                    if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare,
                                            $tblPerson))
                                        && $tblPrepareStudent->isApproved()
                                    ) {
                                        $countApproved++;
                                    }
                                }
                            }
                        }
                    }

                    if ($isAutomaticallyApproved) {
                        $status = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                            . ' ' .$countStudents . ' Zeugnisse werden automatisch freigegeben.');
                    } else {
                        $status = $countApproved < $countStudents
                            ? new Warning(new Exclamation() . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnisse freigegeben.')
                            : new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                . ' ' . $countApproved . ' von ' . $countStudents . ' Zeugnissen freigegeben.');
                    }

                    $prepareList[] = array(
                        'Date' => $tblPrepare->getDate(),
                        'Name' => $tblPrepare->getName(),
                        'Division' => $tblDivision ? $tblDivision->getDisplayName() : '',
                        'CertificateType' =>
                            $tblCertificateType ? $tblCertificateType->getName() : '',
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
                            . (new Standard(
                                '',
                                '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                new Disable(),
                                array(
                                    'PrepareId' => $tblPrepare->getId(),
                                    'Route' => '/Education/Certificate/Approve'
                                ),
                                'Alle Zeugnisfreigaben dieser Klasse entfernen'
                            ))
                    );
                }
            }

            if (!empty($prepareList)) {
                $content = new TableData($prepareList, null,
                    array(
                        'Date' => 'Zeugnisdatum',
                        'Division' => 'Klasse',
                        'Name' => 'Zeugnisauftrag',
                        'CertificateType' => 'Zeugnistyp',
                        'Status' => 'Freigaben',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array(0, 'desc'),
                            array(1, 'asc'),
                            array(2, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'de_date', 'targets' => 0),
                            array('type' => 'natural', 'targets' => 1),
                        )
                    )
                );
            } else {
                $content = new \SPHERE\Common\Frontend\Message\Repository\Warning('
                    Es liegen aktuell keine Zeugnisse zum Freigeben vor.', new Exclamation());
            }
        } else {
            $tblPrepareList = Prepare::useService()->getPrepareAll();
            if ($tblPrepareList) {
                foreach ($tblPrepareList as $tblPrepare) {
                    $tblDivision = $tblPrepare->getServiceTblDivision();

                    if ($tblDivision) {
                        $prepareList[] = array(
                            'Year' => $tblDivision->getServiceTblYear()
                                ? $tblDivision->getServiceTblYear()->getDisplayName() : '',
                            'Date' => $tblPrepare->getDate(),
                            'Name' => $tblPrepare->getName(),
                            'Division' => $tblDivision ? $tblDivision->getDisplayName() : '',
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
                        )
                    )
                );
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Schuljahr',
                                $tblYear ? $tblYear->getDisplayName() : 'Alle Schuljahre',
                                Panel::PANEL_TYPE_INFO
                            ),
                        )),
                        new LayoutColumn(array(
                            $content ? $content : null
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PrepareId
     * @param null $DivisionId
     * @param bool $IsLeave
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendDivision(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = false,
        $IsAllYears = false
    ) {

        $Stage = new Stage('Zeugnisse freigeben', 'Klassenansicht');

        if ($IsLeave) {
            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Approve', new ChevronLeft(),
                        array(
                            'IsAllYears' => $IsAllYears
                        )
                    )
                );

                $studentTable = array();
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByDivision($tblDivision))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())) {
                            if (($tblStudent = $tblPerson->getStudent())
                                && ($tblCourse = Student::useService()->getCourseByStudent($tblStudent))
                            ) {
                                $course = $tblCourse->getName();
                            } else {
                                $course = '';
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

                            $studentTable[] = array(
                                'Name' => $tblPerson->getLastFirstName(),
                                'Course' => $course,
                                'ExcusedAbsence' => Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                    $tblDivision),
                                'UnexcusedAbsence' => Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                    $tblDivision),
                                'Template' => ($tblCertificate
                                    ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                    : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
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
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new External(
                                        'Alle Zeugnisse als Muster herunterladen',
                                        '/Api/Education/Certificate/Generator/PreviewMultiLeavePdf',
                                        new Download(),
                                        array(
                                            'DivisionId' => $tblDivision ? $tblDivision->getId() : 0,
                                            'Name' => 'Zeugnismuster'
                                        ), 'Alle Zeugnisse als Muster herunterladen'),
                                    new Standard(
                                        'Alle Zeugnisse dieser Klasse freigeben',
                                        '/Education/Certificate/Approve/Prepare/Division/SetApproved',
                                        new Check(),
                                        array(
                                            'DivisionId' => $tblDivision ? $tblDivision->getId() : 0,
                                            'IsLeave' => true,
                                            'Route' => '/Education/Certificate/Approve/Prepare',
                                            'IsAllYears' => $IsAllYears
                                        )
                                    ),
                                    new Standard(
                                        'Alle Zeugnisfreigaben dieser Klasse entfernen',
                                        '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                        new Disable(),
                                        array(
                                            'DivisionId' => $tblDivision ? $tblDivision->getId() : 0,
                                            'IsLeave' => true,
                                            'Route' => '/Education/Certificate/Approve/Prepare',
                                            'IsAllYears' => $IsAllYears
                                        )
                                    ),
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
        } else {
            $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
            if ($tblPrepare) {
                $tblDivision = $tblPrepare->getServiceTblDivision();
                $studentTable = array();
                if ($tblDivision) {
                    $Stage->addButton(new Standard(
                        'Zurück', '/Education/Certificate/Approve', new ChevronLeft(),
                        $IsAllYears ?
                            array(
                                'IsAllYears' => $IsAllYears
                            )
                            : array(
                            'YearId' => ($tblDivision->getServiceTblYear() ? $tblDivision->getServiceTblYear()->getId() : null)
                        )
                    ));

                    if (($tblCertificateType = $tblPrepare->getCertificateType())
                        && $tblCertificateType->isAutomaticallyApproved()
                    ) {
                        $isAutomaticallyApproved = true;
                    } else {
                        $isAutomaticallyApproved = false;
                    }

                    $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
                    if ($tblStudentList) {
                        foreach ($tblStudentList as $tblPerson) {
                            $course = '';
                            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                                $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                                if ($tblTransferType) {
                                    $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                        $tblTransferType);
                                    if ($tblStudentTransfer) {
                                        $tblCourse = $tblStudentTransfer->getServiceTblCourse();
                                        if ($tblCourse) {
                                            $course = $tblCourse->getName();
                                        }
                                    }
                                }
                            }

                            $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                            if ($tblPrepareStudent) {
                                $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                                $isApproved = $tblPrepareStudent->isApproved();
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

                            $studentTable[] = array(
                                'Number' => count($studentTable) + 1,
                                'Name' => $tblPerson->getLastFirstName(),
                                'Course' => $course,
                                'ExcusedAbsence' => Absence::useService()->getExcusedDaysByPerson($tblPerson,
                                    $tblDivision),
                                'UnexcusedAbsence' => Absence::useService()->getUnexcusedDaysByPerson($tblPerson,
                                    $tblDivision),
                                'Template' => ($tblCertificate
                                    ? new Success(new Enable() . ' ' . $tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? '<br>' . $tblCertificate->getDescription() : ''))
                                    : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Keine Zeugnisvorlage ausgewählt')),
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
                }

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
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
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
                                    new Standard(
                                        'Alle Zeugnisfreigaben dieser Klasse entfernen',
                                        '/Education/Certificate/Approve/Prepare/Division/ResetApproved',
                                        new Disable(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'Route' => '/Education/Certificate/Approve/Prepare',
                                            'IsAllYears' => $IsAllYears
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
     * @param bool $IsLeave
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendApprovePreparePerson(
        $PrepareId = null,
        $PersonId = null,
        $LeaveStudentId = null,
        $IsLeave = false,
        $IsAllYears = false
    ) {
        $Stage = new Stage('Zeugnisse freigeben', 'Freigabe');

        if ($IsLeave) {
            if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($LeaveStudentId))
                && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                && ($tblDivision = $tblLeaveStudent->getServiceTblDivision())
            ) {
                Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, $tblLeaveStudent->isPrinted());

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnis wurde freigegeben.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivision->getId(), 'IsLeave' => true,'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblPerson = Person::useService()->getPersonById($PersonId))
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
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
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson ? $tblPerson->getLastFirstName() : '',
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
     * @param bool $IsLeave
     * @param bool $IsAllYears
     * @return Stage|string
     */
    public function frontendResetApprovePreparePerson(
        $PrepareId = null,
        $PersonId = null,
        $LeaveStudentId = null,
        $IsLeave = false,
        $IsAllYears = false)
    {
        $Stage = new Stage('Zeugnis', 'Freigabe entfernen');

        if ($IsLeave) {
            if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($LeaveStudentId))
                && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                && ($tblDivision = $tblLeaveStudent->getServiceTblDivision())
            ) {
                Prepare::useService()->updateLeaveStudent($tblLeaveStudent, false, false);

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigabe wurde entfernt.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect('/Education/Certificate/Approve/Prepare', Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivision->getId(), 'IsLeave' => true,'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', '/Education/Certificate/Approve', new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblPerson = Person::useService()->getPersonById($PersonId))
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
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
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson ? $tblPerson->getLastFirstName() : '',
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
     * @param bool $IsLeave
     * @param string $Route
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendApprovePrepareDivision(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = false,
        $Route = '/Education/Certificate/Approve/Prepare',
        $IsAllYears = false
    ) {
        $Stage = new Stage('Zeugnisse freigeben', 'Klasse freigeben');

        if ($IsLeave) {
            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByDivision($tblDivision))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if (!$tblLeaveStudent->isApproved()) {
                            Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, $tblLeaveStudent->isPrinted());
                        }
                    }
                }

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisse wurden freigegeben.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivision->getId(), 'IsLeave' => $IsLeave, 'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', $Route, new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
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
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                            )),
                        )),
                    ))
                );

                Prepare::useService()->updatePrepareDivisionSetApproved($tblPrepare);

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
     * @param bool $IsLeave
     * @param string $Route
     * @param bool $IsAllYears
     *
     * @return Stage|string
     */
    public function frontendResetApprovePrepareDivision(
        $PrepareId = null,
        $DivisionId = null,
        $IsLeave = false,
        $Route = '/Education/Certificate/Approve/Prepare',
        $IsAllYears = false
    ) {
        $Stage = new Stage('Zeugnisse freigeben', 'Klassen Freigabe entfernen');

        if ($IsLeave) {
            if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
                if (($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByDivision($tblDivision))) {
                    foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                        if ($tblLeaveStudent->isApproved()) {
                            Prepare::useService()->updateLeaveStudent($tblLeaveStudent, false, false);
                        }
                    }
                }

                return $Stage
                    . new \SPHERE\Common\Frontend\Message\Repository\Success('Zeugnisfreigaben wurden entfernt.',
                        new \SPHERE\Common\Frontend\Icon\Repository\Success())
                    . new Redirect($Route, Redirect::TIMEOUT_SUCCESS,
                        array('DivisionId' => $tblDivision->getId(), 'IsLeave' => $IsLeave, 'IsAllYears' => $IsAllYears));
            } else {
                $Stage->addButton(new Standard(
                    'Zurück', $Route, new ChevronLeft(), array('IsAllYears' => $IsAllYears)
                ));

                return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
            }
        } else {
            if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
                && ($tblDivision = $tblPrepare->getServiceTblDivision())
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
                                        'Klasse',
                                        $tblDivision ? $tblDivision->getDisplayName() : '',
                                        Panel::PANEL_TYPE_INFO
                                    ),
                                ), 6),
                            )),
                        )),
                    ))
                );

                Prepare::useService()->updatePrepareDivisionResetApproved($tblPrepare);

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