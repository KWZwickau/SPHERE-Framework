<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;

abstract class FrontendSelect extends FrontendPreview
{
    /**
     * @param Stage $Stage
     * @param int $view
     */
    protected function setHeaderButtonList(Stage $Stage, int $view)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasDiplomaRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Diploma');
        $hasLeaveRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Leave');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }
        if ($hasDiplomaRight) {
            $countRights++;
        }
        if ($hasLeaveRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        '/Education/Certificate/Prepare/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        '/Education/Certificate/Prepare/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                        '/Education/Certificate/Prepare/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Leitung',
                        '/Education/Certificate/Prepare/Headmaster'));
                }
            }
            if ($hasDiplomaRight) {
                if ($view == View::DIPLOMA) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Abschlusszeugnisse')),
                        '/Education/Certificate/Prepare/Diploma', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Abschlusszeugnisse',
                        '/Education/Certificate/Prepare/Diploma'));
                }
            }
            if ($hasLeaveRight) {
                if ($view == View::LEAVE) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Abgangszeugnisse')),
                        '/Education/Certificate/Prepare/Leave', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Abgangszeugnisse',
                        '/Education/Certificate/Prepare/Leave'));
                }
            }
        }
    }

    /**
     * @param array $divisionTable
     * @param array $buttonList
     * @param string $messageMissing
     *
     * @return Layout
     */
    public function getSelectDivisionCourseContent(array $divisionTable, array $buttonList, string $messageMissing): Layout
    {
        if (empty($divisionTable)) {
            $content = new Warning($messageMissing, new Ban());
        } else {
            $content = new TableData($divisionTable, null, array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'DivisionCourseTyp' => 'Kurs-Typ',
                'SchoolTypes' => 'Schularten',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                ),
            ));
        }

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(array(
                    empty($buttonList) ? null : new LayoutColumn($buttonList),
                    new LayoutColumn($content)
                ))
            ), new Title(new Select() . ' Auswahl'))
        ));
    }

    /**
     * @return Stage
     */
    public function frontendSelectDivision(): Stage
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Teacher');
        $hasDiplomaRight = Access::useService()->hasAuthorization('/Education/Certificate/Prepare/Diploma');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } elseif ($hasDiplomaRight) {
            return $this->frontendDiplomaSelectDivision();
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision($YearId = null): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Kurs auswählen');

        $this->setHeaderButtonList($Stage, View::TEACHER);
        $buttonList = Term::useService()->setYearButtonList('/Education/Certificate/Prepare/Teacher', false, $YearId, $tblYear, false);

        $divisionTable = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && $tblYear
        ) {
            $tblSchoolTypeOS = Type::useService()->getTypeByShortName('OS');
            $tblSchoolTypeFOS = Type::useService()->getTypeByShortName('FOS');
            if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    $tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents();
                    // nur Kurse anzeigen, wo auch ein Zeugnisauftrag existiert
                    if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse))) {
                        foreach ($tblPrepareList as $tblPrepare) {
                            // Noteninformationen und Abschlusszeugnisse ignorieren
                            if (!($tblCertificateType = $tblPrepare->getCertificateType())
                                || $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION'
                                || ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                                    // Ausnahme bei HOGA sollen die Klassenlehrer die Abschlusszeugnisse bearbeiten können, todo später Mandanteneintstellung
                                    && !(Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA')
                                        && (isset($tblSchoolTypeList[$tblSchoolTypeOS->getId()]) || isset($tblSchoolTypeList[$tblSchoolTypeFOS->getId()]))
                                    )
                                )
                            ) {
                                continue;
                            }

                            $divisionTable[] = array(
                                'Year' => $tblYear->getDisplayName(),
                                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                                'DivisionCourseTyp' => $tblDivisionCourse->getTypeName(),
                                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                                'Option' => new Standard(
                                    '', '/Education/Certificate/Prepare/Prepare', new Select(),
                                    array(
                                        'DivisionId' => $tblDivisionCourse->getId(),
                                        'Route' => 'Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                            break;
                        }
                    }
                }
            }
        }

        $Stage->setContent($this->getSelectDivisionCourseContent($divisionTable, $buttonList, 'Es existieren keine Zeugnisaufträge für Ihre Kurse.'));

        return $Stage;
    }

    /**
     * @param null $YearId
     * @param null $IsAllYears
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision($YearId = null, $IsAllYears = null): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Kurs auswählen');

        $this->setHeaderButtonList($Stage, View::HEADMASTER);
        $buttonList = Term::useService()->setYearButtonList('/Education/Certificate/Prepare/Headmaster', $IsAllYears, $YearId, $tblYear, true);

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
                // Noteninformationen und Abschlusszeugnisse ignorieren
                if (!($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    || $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION'
                    || $tblCertificateType->getIdentifier() == 'DIPLOMA'
                ) {
                    continue;
                }

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
                                    '', '/Education/Certificate/Prepare/Prepare', new Select(),
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

        $Stage->setContent($this->getSelectDivisionCourseContent($divisionTable, $buttonList, 'Es existieren keine entsprechenden Zeugnisaufträge.'));

        return $Stage;
    }

    /**
     * @param $YearId
     * @param $IsAllYears
     *
     * @return Stage
     */
    public function frontendDiplomaSelectDivision($YearId = null, $IsAllYears = null): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Kurs auswählen');

        $this->setHeaderButtonList($Stage, View::DIPLOMA);
        $buttonList = Term::useService()->setYearButtonList('/Education/Certificate/Prepare/Diploma', $IsAllYears, $YearId, $tblYear, true);

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
                // nur Abschlusszeugnisse
                // nur Kurse anzeigen, wo auch ein Zeugnisauftrag existiert
                if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                    && ($tblPrepareList = $tblGenerateCertificate->getPrepareList())
                ) {
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
                                    '', '/Education/Certificate/Prepare/Prepare', new Select(),
                                    array(
                                        'DivisionId' => $tblDivisionCourse->getId(),
                                        'Route' => 'Diploma'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }
            }
        }

        $Stage->setContent($this->getSelectDivisionCourseContent($divisionTable, $buttonList, 'Es existieren keine entsprechenden Zeugnisaufträge.'));

        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param string $Route
     *
     * @return Stage
     */
    public function frontendPrepare($DivisionId = null, string $Route = 'Teacher'): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/' . $Route, new ChevronLeft())
        );

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))) {
            $tableData = array();
            $tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy');
            $tblSchoolTypeFS = Type::useService()->getTypeByShortName('FS');
            $tblSchoolTypeBgj = Type::useService()->getTypeByShortName('BGJ');
            $tblSchoolTypeOS = Type::useService()->getTypeByShortName('OS');
            $tblSchoolTypeFOS = Type::useService()->getTypeByShortName('FOS');
            $tblSchoolTypeBFS = Type::useService()->getTypeByShortName('BFS');
            $tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents();
            if (($tblPrepareList = Prepare::useService()->getPrepareAllByDivisionCourse($tblDivisionCourse))) {
                foreach ($tblPrepareList as $tblPrepare) {
                    if (($tblCertificateType = $tblPrepare->getCertificateType())) {
                        // Noteninformation überspringen
                        if ($tblCertificateType->getIdentifier() == 'GRADE_INFORMATION') {
                            continue;
                        }

                        if ($Route == 'Diploma') {
                            // alle außer Abschlusszeugnisse überspringen
                            if ($tblCertificateType->getIdentifier() != 'DIPLOMA') {
                                continue;
                            }
                        } else {
                            // Abschlusszeugnisse überspringen
                            if ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                                // Ausnahme bei HOGA sollen die Klassenlehrer die Abschlusszeugnisse bearbeiten können, todo später Mandanteneintstellung
                                && !(Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'HOGA') && $Route == 'Teacher'
                                    && (isset($tblSchoolTypeList[$tblSchoolTypeOS->getId()]) || isset($tblSchoolTypeList[$tblSchoolTypeFOS->getId()]))
                                )
                            ) {
                                continue;
                            }
                        }

                        $parameters = array(
                            'PrepareId' => $tblPrepare->getId(),
                            'Route' => $Route
                        );

                        if ($Route == 'Diploma'
                            && isset($tblSchoolTypeList[$tblSchoolTypeGy->getId()])
                        ) {
                            // Gymnasium, Abitur
                            $options = new Standard(
                                '', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new EyeOpen(), $parameters, 'Einstellungen und Vorschau der Zeugnisse'
                            );
                        } else {
                            if ($tblCertificateType->getIdentifier() == 'DIPLOMA') {
                                // Fachschule, Berufsgrundbildungsjahr
                                if (isset($tblSchoolTypeList[$tblSchoolTypeFS->getId()]) || isset($tblSchoolTypeList[$tblSchoolTypeBgj->getId()])) {
                                    $routeDestination = '/Education/Certificate/Prepare/Prepare/Diploma/Technical/Setting';
                                } else {
                                    // Fachoberschule, Berufsfachschule ist wie Oberschule
                                    $routeDestination = '/Education/Certificate/Prepare/Prepare/Diploma/Setting';
                                    if (isset($tblSchoolTypeList[$tblSchoolTypeOS->getId()])) {
                                        $parameters['SchoolTypeShortName'] = $tblSchoolTypeOS->getShortName();
                                    } elseif (isset($tblSchoolTypeList[$tblSchoolTypeFOS->getId()])) {
                                        $parameters['SchoolTypeShortName'] = $tblSchoolTypeFOS->getShortName();
                                    } elseif (isset($tblSchoolTypeList[$tblSchoolTypeBFS->getId()])) {
                                        $parameters['SchoolTypeShortName'] = $tblSchoolTypeBFS->getShortName();
                                    }
                                }
                            } else {
                                $routeDestination = '/Education/Certificate/Prepare/Prepare/Setting';
                            }

                            $options = (new Standard('', $routeDestination, new Setup(), $parameters, 'Einstellungen'))
                                . (new Standard('', '/Education/Certificate/Prepare/Prepare/Preview', new EyeOpen(), $parameters, 'Vorschau der Zeugnisse'));
                        }

                        $tableData[] = array(
                            'Date' => $tblPrepare->getDate(),
                            'Type' => $tblCertificateType->getName(),
                            'Name' => $tblPrepare->getName(),
                            'Option' => $options
                        );
                    }
                }
            }

            $content = new Layout(array(
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
                            new TableData($tableData, null,
                                array(
                                    'Date' => 'Zeugnisdatum',
                                    'Type' => 'Typ',
                                    'Name' => 'Name',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'desc')
                                    ),
                                    'columnDefs' => array(
                                        array('type' => 'de_date', 'targets' => 0),
                                        array('width' => '10%', 'targets' => 3),
                                        array('orderable' => false, 'width' => '30px', 'targets' => -1),
                                    )
                                )
                            )
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
            ));
        } else {
            $content = new Danger('Kurs nicht gefunden.', new Ban());
        }
        $Stage->setContent($content);

        return $Stage;
    }
}