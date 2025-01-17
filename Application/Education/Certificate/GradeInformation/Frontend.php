<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
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
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\GradeInformation
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendSelectDivision(): Stage
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @param null $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision($IsAllYears = null, $YearId = null): Stage
    {
        $Stage = new Stage('Noteninformation', 'Kurs auswählen');

        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Teacher');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')), '/Education/Certificate/GradeInformation/Teacher', new Edit()));
            $Stage->addButton(new Standard('Ansicht: Leitung', '/Education/Certificate/GradeInformation/Headmaster'));
        }

        $buttonList = Term::useService()->setYearButtonList('/Education/Certificate/GradeInformation/Teacher', $IsAllYears, $YearId, $tblYear, false);

        $divisionTable = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && $tblYear
            && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))
        ) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $schoolTypes = $tblDivisionCourse->getSchoolTypeListFromStudents(true);
                // nur Kurse anzeigen, wo auch ein Zeugnisauftrag existiert
                if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse))) {
                    foreach ($tblPrepareList as $tblPrepare) {
                        // nur Noteninformationen
                        if (($tblCertificateType = $tblPrepare->getCertificateType()) && $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION') {
                            $divisionTable[] = array(
                                'Year' => $tblYear->getDisplayName(),
                                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                                'DivisionCourseTyp' => $tblDivisionCourse->getTypeName(),
                                'SchoolTypes' => $schoolTypes,
                                'Option' => new Standard(
                                    '', '/Education/Certificate/GradeInformation/Create', new Select(),
                                    array(
                                        'DivisionId' => $tblDivisionCourse->getId(),
                                        'Route' => 'Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }
            }
        }

        $Stage->setContent(Prepare::useFrontend()->getSelectDivisionCourseContent($divisionTable, $buttonList, 'Es existieren keine Noteninformation-Aufträge für Ihre Kurse.'));

        return $Stage;
    }

    /**
     * @param null $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision($IsAllYears = null, $YearId = null): Stage
    {
        $Stage = new Stage('Noteninformation', 'Kurs auswählen');

        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/GradeInformation/Teacher');
        if ($hasHeadmasterRight && $hasTeacherRight) {
            $Stage->addButton(new Standard('Ansicht: Lehrer', '/Education/Certificate/GradeInformation/Teacher'));
            $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')), '/Education/Certificate/GradeInformation/Headmaster', new Edit()));
        }

        $buttonList = Term::useService()->setYearButtonList('/Education/Certificate/GradeInformation/Headmaster', $IsAllYears, $YearId, $tblYear, true);
        $divisionTable = array();

        if ($IsAllYears) {
            $tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAll();
        } elseif ($tblYear) {
            $tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAllByYear($tblYear);
        } else {
            $tblGenerateCertificateList = false;
        }

        if ($tblGenerateCertificateList) {
            foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                // nur Noteninformationen
                if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType()) && $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION') {
                    // nur Kurse anzeigen, wo auch ein Zeugnisauftrag existiert
                    if (($tblPrepareList = $tblGenerateCertificate->getPrepareList())) {
                        foreach ($tblPrepareList as $tblPrepare) {
                            if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                                && !isset($divisionTable[$tblDivisionCourse->getId()])
                                && ($tblYearItem = $tblDivisionCourse->getServiceTblYear())
                            ) {
                                $divisionTable[$tblDivisionCourse->getId()] = array(
                                    'Year' => $tblYearItem->getDisplayName(),
                                    'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                                    'DivisionCourseTyp' => $tblDivisionCourse->getTypeName(),
                                    'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                                    'Option' => new Standard(
                                        '', '/Education/Certificate/GradeInformation/Create', new Select(),
                                        array(
                                            'DivisionId' => $tblDivisionCourse->getId(),
                                            'Route' => 'Headmaster'
                                        ),
                                        'Auswählen'
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        $Stage->setContent(Prepare::useFrontend()->getSelectDivisionCourseContent($divisionTable, $buttonList, 'Es existieren keine entsprechenden Noteninformation-Aufträge.'));

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendGradeInformation($DivisionId = null, string $Route = 'Teacher')
    {
        $Stage = new Stage('Noteninformation', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/GradeInformation/' . $Route, new ChevronLeft()));

        $tableData = array();
        if ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId)) {
            if (($tblPrepareCertificateAllByDivision = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse))) {
                foreach ($tblPrepareCertificateAllByDivision as $tblPrepareCertificate) {
                    if (($tblCertificateType = $tblPrepareCertificate->getCertificateType()) && $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION') {
                        $tableData[] = array(
                            'Date' => $tblPrepareCertificate->getDate(),
                            'Name' => $tblPrepareCertificate->getName(),
                            'Option' =>
                                (new Standard(
                                    '', '/Education/Certificate/GradeInformation/Setting', new Setup(),
                                    array(
                                        'PrepareId' => $tblPrepareCertificate->getId(),
                                        'Route' => $Route
                                    ),
                                    'Einstellungen'
                                ))
                                . (new Standard(
                                    '', '/Education/Certificate/GradeInformation/Setting/Preview', new EyeOpen(),
                                    array(
                                        'PrepareId' => $tblPrepareCertificate->getId(),
                                        'Route' => $Route
                                    ),
                                    'Vorschau und Herunterladen der Noteninformation'
                                ))
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
                                        $tblDivisionCourse->getTypeName(),
                                        $tblDivisionCourse->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tableData, null, array(
                                        'Date' => 'Datum',
                                        'Name' => 'Name',
                                        'Option' => ''
                                    ),
                                        array(
                                            'order' => array(
                                                array(0, 'desc')
                                            ),
                                            'columnDefs' => array(
                                                array('type' => 'de_date', 'targets' => 0),
                                                array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                            )
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Kurs nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Route
     * @param null $Grades
     * @param null $Remarks
     *
     * @return Stage|string
     */
    public function frontendSetting($PrepareId = null, string $Route = 'Teacher', $Grades = null, $Remarks = null)
    {
        $Stage = new Stage('Noteninformation', 'Kursübersicht');
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
                array(
                    'DivisionId' => $tblDivisionCourse->getId(),
                    'Route' => $Route
                )
            ));

            $tblGradeTypeList = array();
            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
            );
            $studentTable = array();
            if (($tblBehaviorTask = $tblPrepare->getServiceTblBehaviorTask())
                && ($tempList = $tblBehaviorTask->getGradeTypes())
            ) {
                foreach ($tempList as $tblGradeTypeTemp) {
                    $tblGradeTypeList[$tblGradeTypeTemp->getId()] = $tblGradeTypeTemp;
                    $columnTable['GradeType' . $tblGradeTypeTemp->getId()] = $tblGradeTypeTemp->getName();
                }
            }
            $columnTable['Remark'] = 'Bemerkungen zum Schüler';

            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                $count = 0;
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    $studentTable[$tblPerson->getId()] = array(
                        'Number' => ++$count,
                        'Name' => $tblPerson->getLastFirstName(),
                    );

                    // Post setzen
                    if ($Grades === null) {
                        $Global = $this->getGlobal();
                        if ($tblBehaviorTask
                            && ($tblPrepareGradeList = Prepare::useService()->getBehaviorGradeAllByPrepareCertificateAndPerson($tblPrepare, $tblPerson))
                        ) {
                            foreach ($tblPrepareGradeList as $tblPrepareGrade) {
                                if (($tblGradeType = $tblPrepareGrade->getServiceTblGradeType())) {
                                    $Global->POST['Grades'][$tblPerson->getId()][$tblGradeType->getId()] = $tblPrepareGrade->getGrade();
                                }
                            }
                        }

                        if (($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($tblPrepare, $tblPerson, 'Remark'))) {
                            $Global->POST['Remarks'][$tblPerson->getId()] = $tblPrepareInformation->getValue();
                        }
                        $Global->savePost();
                    }

                    foreach ($tblGradeTypeList as $tblGradeType) {
                        $studentTable[$tblPerson->getId()]['GradeType' . $tblGradeType->getId()] = new TextField('Grades[' . $tblPerson->getId() . '][' . $tblGradeType->getId() . ']');
                    }

                    $studentTable[$tblPerson->getId()]['Remark'] = new TextArea('Remarks[' . $tblPerson->getId() . ']');
                }
            }

            $tableData = new TableData($studentTable, null, $columnTable,
                array(
                    "columnDefs" => array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                        array(
                            "width" => "50px",
                            "targets" => count($columnTable) == 7 ? array(2, 3, 4, 5) : null
                        ),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    "paging" => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching" => false, // Deaktivieren Suchen
                    "info" => false,  // Deaktivieren Such-Info
                    "sort" => false,
                    "responsive" => false
                ));

            $form = new Form(
                new FormGroup(array(
                    new FormRow(
                        new FormColumn(
                            $tableData
                        )
                    ),
                )),
                new Primary('Speichern', new Save())
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Noteninformation',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
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
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                GradeInformation::useService()->updatePrepareBehaviorGradesAndRemark(
                                    $form, $tblPrepare, $Route, $Grades, $Remarks
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/GradeInformation', new ChevronLeft()
            ));

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendShowTemplate($PrepareId = null, $PersonId = null, string $Route = 'Teacher')
    {
        $Stage = new Stage('Noteninformation', 'Vorschau und Herunterladen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/GradeInformation/Setting/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $Stage->addButton(new External(
                'Noteninformation herunterladen',
                '/Api/Education/Certificate/Generator/Preview',
                new Download(),
                array(
                    'PrepareId' => $tblPrepare->getId(),
                    'PersonId' => $tblPerson->getId(),
                    'Name' => 'Noteninformation'
                ),
                false
            ));

            $tblCertificate = false;
            $ContentLayout = array();
            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            ) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                if (class_exists($CertificateClass)) {
                    $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                    /** @var Certificate $Template */
                    $Template = new $CertificateClass($tblStudentEducation ?: null, $tblPrepare);

                    // get Content
                    $Content = Prepare::useService()->createCertificateContent($tblPerson, $tblPrepareStudent);
                    $personId = $tblPerson->getId();
                    if (isset($Content['P' . $personId]['Grade'])) {
                        $Template->setGrade($Content['P' . $personId]['Grade']);
                    }
                    if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                        $Template->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                    }

                    $pageList[$tblPerson->getId()] = $Template->buildPages($tblPerson);
                    $bridge = $Template->createCertificate($Content, $pageList);

                    $ContentLayout = $bridge->getContent();
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Noteninformation',
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    $tblDivisionCourse->getTypeName(),
                                    $tblDivisionCourse->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 4),
                            new LayoutColumn(array(
                                new Panel(
                                    'Vorlage',
                                    $tblCertificate
                                        ? ($tblCertificate->getName()
                                        . ($tblCertificate->getDescription() ? ' - ' . $tblCertificate->getDescription() : ''))
                                        : new WarningText(new Exclamation()
                                        . ' Keine Vorlage hinterlegt'),
                                    $tblCertificate
                                        ? Panel::PANEL_TYPE_SUCCESS
                                        : Panel::PANEL_TYPE_WARNING
                                ),
                            ), 12),
                            new LayoutColumn(
                                $ContentLayout
                            ),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Route
     *
     * @return Stage|string
     */
    public function frontendPreview($PrepareId = null, string $Route = 'Teacher')
    {
        $Stage = new Stage('Noteninformation', 'Kursübersicht');
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $studentTable = array();
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/GradeInformation/Create', new ChevronLeft(),
                array(
                    'DivisionId' => $tblDivisionCourse->getId(),
                    'Route' => $Route
                )
            ));

            $columnTable = array(
                'Number' => '#',
                'Name' => 'Name',
            );
            $columnTable['Option'] = '';
            if (($tblStudentList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                $count = 0;
                /** @var TblPerson $tblPerson */
                foreach ($tblStudentList as $tblPerson) {
                    $studentTable[$tblPerson->getId()] = array(
                        'Number' => ++$count,
                        'Name' => $tblPerson->getLastFirstNameWithCallNameUnderline(),
                    );

                    $studentTable[$tblPerson->getId()]['Option'] =
                        (new Standard(
                            '',
                            '/Education/Certificate/GradeInformation/Setting/Template/Show',
                            new EyeOpen(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'Route' => $Route
                            ),
                            'Vorschau anzeigen'
                        ))
                        . (new External(
                            '',
                            '/Api/Education/Certificate/Generator/Preview',
                            new Download(),
                            array(
                                'PrepareId' => $tblPrepare->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'Name' => 'Noteninformation'
                            ),
                            'Noteninformation herunterladen'
                        ));
                }
            }

            $tableData = new TableData($studentTable, null, $columnTable,
                array(
                    "columnDefs" => array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "60px",
                            "targets" => 2
                        ),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    "paging" => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching" => false, // Deaktivieren Suchen
                    "info" => false,  // Deaktivieren Such-Info
                    "sort" => false
                ));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Noteninformation',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
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
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new External(
                                    'Alle Noteninformationen herunterladen',
                                    '/Api/Education/Certificate/Generator/PreviewMultiPdf',
                                    new Download(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                        'Name' => 'Noteninformation'
                                    ),
                                    'Alle Noteninformationen herunterladen'
                                ),
                                $tableData
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/GradeInformation', new ChevronLeft()
            ));

            return $Stage . new Danger('Noteninformation nicht gefunden.', new Ban());
        }
    }
}