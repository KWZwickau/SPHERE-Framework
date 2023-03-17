<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.11.2016
 * Time: 08:45
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use DateTime;
use SPHERE\Application\Api\Education\Certificate\Generate\ApiGenerate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

/**
 * Class Frontend
 * @package SPHERE\Application\Education\Certificate\Generate
 */
class Frontend extends Extension
{
    /**
     * @param null $IsAllYears
     * @param null $YearId
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendGenerate($IsAllYears = null, $YearId = null, $Data = null): Stage
    {
        $Stage = new Stage('Zeugnis generieren', 'Übersicht');
        $buttonList = Term::useService()->setYearButtonList('/Education/Certificate/Generate', $IsAllYears, $YearId, $tblYear, true);

        $tableData = array();
        if (($tblGenerateCertificateList = $tblYear
            ? Generate::useService()->getGenerateCertificateAllByYear($tblYear)
            : Generate::useService()->getGenerateCertificateAll())
        ) {
            foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                // Zusatz Option für Abschlusszeugnisse
                $hasDiplomaCertificate = false;
                if (($tblGenerateCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                    && $tblGenerateCertificateType->getIdentifier() == 'DIPLOMA'
                ) {
                    // Prüfungsausschuss nur bei Gy und OS, nicht bei Bfs oder FOS
                    if (($tblSchoolTypeList = $tblGenerateCertificate->getSchoolTypes())) {
                        /** @var TblType $tblSchoolType */
                        foreach ($tblSchoolTypeList as $tblSchoolType) {
                            if ($tblSchoolType->getShortName() == 'Gy' || $tblSchoolType->getShortName() == 'OS') {
                                $hasDiplomaCertificate = true;
                                break;
                            }
                        }
                    }
                }

                $tableData[] = array(
                    'Date' => $tblGenerateCertificate->getDate(),
                    'Type' => $tblGenerateCertificate->getServiceTblCertificateType()
                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName() : '',
                    'Name' => $tblGenerateCertificate->getName(),
                    'SchoolTypes' => $tblGenerateCertificate->getSchoolTypes(true),
                    'Option' =>
                        (new Standard(
                            '', '/Education/Certificate/Generate/Edit', new Edit(),
                            array(
                                'Id' => $tblGenerateCertificate->getId(),
                            )
                            , 'Bearbeiten'
                        ))
                        . (new Standard(
                            '', '/Education/Certificate/Generate/Division/Select', new Listing(),
                            array(
                                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                            )
                            , 'Kurse zuordnen'
                        ))
                        . (new Standard(
                            '', '/Education/Certificate/Generate/Division', new Equalizer(),
                            array(
                                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                            )
                            , 'Zeugnisvorlagen zuordnen'
                        ))
                        . ($hasDiplomaCertificate
                            ? (new Standard(
                                '', '/Education/Certificate/Generate/Setting', new Cog(),
                                array(
                                    'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                                )
                                , 'Zusätzliche Einstellungen: Prüfungsausschuss'
                            ))
                            : ''
                        )
                        . (new Standard(
                            '', '/Education/Certificate/Generate/Destroy', new Remove(),
                            array(
                                'Id' => $tblGenerateCertificate->getId(),
                            )
                            , 'Löschen'
                        ))
                );
            }
        }

        if ($tblYear) {
            $Form = $this->formGenerate($tblYear)
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
        } else {
            $Form = null;
        }

        $Stage->setContent(
            new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            empty($buttonList)
                                ? null
                                : new LayoutColumn($buttonList)
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($tableData, null, array(
                                    'Date' => 'Zeugnisdatum',
                                    'Type' => 'Typ',
                                    'Name' => 'Name',
                                    'SchoolTypes' => 'Schul&shy;arten',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 0)
                                        )
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    $IsAllYears
                        ? null
                        : new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(Generate::useService()->createGenerateCertificate($Form, $Data))
                            ))
                        ))

                    ), new Title(new PlusSign() . ' Hinzufügen'))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param TblYear|null $tblYear
     *
     * @return Form
     */
    private function formGenerate(TblYear $tblYear = null): Form
    {
        $certificateTypeList = array();
        $tblCertificateTypeAll = Generator::useService()->getCertificateTypeAll();
        if ($tblCertificateTypeAll) {
            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                if ($tblCertificateType->getIdentifier() !== 'LEAVE') {
                    $certificateTypeList[] = $tblCertificateType;
                }
            }
        }

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            if ($tblYear) {
                $Global->POST['Data']['Year'] = $tblYear->getId();

                // Halbjahr oder Jahreszeugnis vorauswählen anhand des aktuellen Datums
                if (($tblPeriodList = $tblYear->getPeriodList())
                    && count($tblPeriodList) == 2
                ) {
                    $tblCurrentPeriod = false;
                    foreach ($tblPeriodList as $tblPeriod) {
                        if ($tblPeriod->getFromDate() && $tblPeriod->getToDate()) {
                            $fromDate = (new DateTime($tblPeriod->getFromDate()))->format("Y-m-d");
                            $toDate = (new DateTime($tblPeriod->getToDate()))->format("Y-m-d");
                            $now = (new DateTime('now'))->format("Y-m-d");
                            if ($fromDate <= $now && $now <= $toDate) {
                                $tblCurrentPeriod = $tblPeriod;
                                break;
                            }
                        }
                    }

                    if ($tblCurrentPeriod) {
                        if ($tblPeriodList[0]->getFromDate() && $tblPeriodList[1]->getFromDate()
                            && (new DateTime($tblPeriodList[0]->getFromDate()))->format("Y-m-d")
                            < (new DateTime($tblPeriodList[1]->getFromDate()))->format("Y-m-d")
                        ) {
                            $tblFirstPeriod = $tblPeriodList[0];
                            $tblSecondPeriod = $tblPeriodList[1];
                        } else {
                            $tblFirstPeriod = $tblPeriodList[1];
                            $tblSecondPeriod = $tblPeriodList[0];
                        }

                        if ($tblFirstPeriod->getId() == $tblCurrentPeriod->getId()) {
                            $Global->POST['Data']['Type'] = Generator::useService()->getCertificateTypeByIdentifier('HALF_YEAR');
                        } elseif ($tblSecondPeriod->getId() == $tblCurrentPeriod->getId()) {
                            $Global->POST['Data']['Type'] = Generator::useService()->getCertificateTypeByIdentifier('YEAR');
                        }
                    }
                }
            }

            $Global->savePost();
        }

        $tblYearList = Term::useService()->getYearAll();

        $paramArray = array(
            'Year' => $tblYear ? $tblYear->getId() : null
        );
        $receiverAppointedDateTask = ApiGenerate::receiverFormSelect(
            (new ApiGenerate())->reloadAppointedDateTaskSelect($paramArray)
        );
        $receiverBehaviorTask = ApiGenerate::receiverFormSelect(
            (new ApiGenerate())->reloadBehaviorTaskSelect($paramArray)
        );

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Zeugnis',
                        array(
                            (new SelectBox('Data[Year]',
                                'Schuljahr',
                                array('{{ Name }} {{ Description }}' => $tblYearList)
                            ))->ajaxPipelineOnChange(
                                array(
                                    ApiGenerate::pipelineCreateAppointedDateTaskSelect($receiverAppointedDateTask),
                                    ApiGenerate::pipelineCreateBehaviorTaskSelect($receiverBehaviorTask)
                                )
                            )->setRequired(),
                            (new TextField('Data[Name]', '', 'Name des Zeugnisauftrags'))->setRequired(),
                            (new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()))->setRequired(),
                            (new DatePicker('Data[AppointedDateForAbsence]', '', new ToolTip('Optionaler Stichtag für Fehlzeiten',
                                'Für die Fehlzeiten kann ein optionaler Stichtag gesetzt werden, ansonsten wird
                                das Zeugnisdatum als Stichtag verwendet.'), new Calendar())),
                            (new SelectBox('Data[Type]', 'Typ', array('Name' => $certificateTypeList)))->setRequired()
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Notenaufträge',
                        array(
                            $receiverAppointedDateTask,
                            $receiverBehaviorTask
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Unterzeichner',
                        array(
                            (new CheckBox('Data[IsTeacherAvailable]',
                                'Name des Kursleiters und Name des/der Schulleiters/in (falls vorhanden) auf dem Zeugnis anzeigen',
                                1
                            )),
                            new TextField('Data[HeadmasterName]', '', 'Name des/der Schulleiters/in'),
                            new Panel(
                                new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                array(
                                    (new RadioBox('Data[GenderHeadmaster]', 'Männlich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich')) ? $tblCommonGender->getId() : 0)),
                                    (new RadioBox('Data[GenderHeadmaster]', 'Weiblich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich')) ? $tblCommonGender->getId() : 0))
                                ),
                                Panel::PANEL_TYPE_DEFAULT
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
            )),
        )));
    }

    /**
     * @param null $GenerateCertificateId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSelectDivision($GenerateCertificateId = null, $Data = null)
    {
        $Stage = new Stage('Zeugnis generieren', 'Kurse zuordnen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))) {
            $divisionCourseExistsList = array();
            $hasPreSelectedDivisions = false;
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    foreach ($tblPrepareList as $tblPrepareCertificate) {
                        if (($tblDivisionCourse = $tblPrepareCertificate->getServiceTblDivision())) {
                            $Global->POST['Data']['Division'][$tblDivisionCourse->getId()] = 1;
                            $divisionCourseExistsList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                        }
                    }
                } else {
                    // vorselektieren anhand der Notenaufträge
                    if (($tblTask = $tblGenerateCertificate->getServiceTblAppointedDateTask())
                        && ($tblTempList = $tblTask->getDivisionCourses())
                    ) {
                        $hasPreSelectedDivisions = true;
                        foreach($tblTempList as $tblTemp) {
                            $Global->POST['Data']['Division'][$tblTemp->getId()] = 1;
                        }
                    }
                    if (($tblTask = $tblGenerateCertificate->getServiceTblBehaviorTask())
                        && ($tblTempList = $tblTask->getDivisionCourses())
                    ) {
                        $hasPreSelectedDivisions = true;
                        foreach($tblTempList as $tblTemp) {
                            $Global->POST['Data']['Division'][$tblTemp->getId()] = 1;
                        }
                    }
                }
            }
            $Global->savePost();

            $layoutGroups = array();
            if (($tblYear = $tblGenerateCertificate->getServiceTblYear())) {
                if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_DIVISION, $divisionCourseExistsList))) {
                    $layoutGroups[] = $temp;
                }
                if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP, $divisionCourseExistsList))) {
                    $layoutGroups[] = $temp;
                }
                if (($temp = $this->getLayoutGroupForDivisionCoursesSelectByTypeIdentifier($tblYear, TblDivisionCourseType::TYPE_TEACHING_GROUP, $divisionCourseExistsList))) {
                    $layoutGroups[] = $temp;
                }
            }

            if (!empty($layoutGroups)) {
                $columnList[] = new FormColumn(new Layout($layoutGroups));
                $columnList[] = new FormColumn(new HiddenField('Data[IsSubmit]'));

                $form = new Form(new FormGroup(new FormRow($columnList)));
                $form
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $content = new Well(Generate::useService()->createPrepareCertificates($form, $tblGenerateCertificate, $Data));
            } else {
                $content = new WarningMessage('Keine entsprechenden Kurse gefunden.', new Exclamation());
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ',
                                    $tblGenerateCertificate->getServiceTblCertificateType()
                                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Stichtagsnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblAppointedDateTask()
                                        ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Kopfnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblBehaviorTask()
                                        ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new WarningMessage('Bitte beachten Sie, dass Zeugnisaufträge für Klassen und Stammgruppen sich für Schüler nicht überschneiden, 
                                    da ansonsten für die Gruppenleiter 2 Zeugnisaufträge angelegt werden.', new Exclamation())
                            ),
                        ))
                    )),
                    $hasPreSelectedDivisions
                        ? new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new WarningMessage('Die vorselektierten Kurse aus den Notenaufträgen wurden noch nicht gespeichert.', new Exclamation())
                            )),
                        ))
                    )) : null,
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                $content
                            ),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugniserstellung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param TblYear $tblYear
     * @param string $TypeIdentifier
     * @param array $divisionCourseExistsList
     *
     * @return false|LayoutGroup
     */
    private function getLayoutGroupForDivisionCoursesSelectByTypeIdentifier(TblYear $tblYear, string $TypeIdentifier, array $divisionCourseExistsList)
    {
        $size = 3;
        $columnList = array();
        $contentPanelList = array();
        $toggleList = array();

        $tblDivisionCourseType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier($TypeIdentifier);
        $this->setContentPanelListForDivisionCourseType($contentPanelList, $toggleList, $tblYear, $TypeIdentifier, $divisionCourseExistsList);
        if (!empty($contentPanelList)) {
            ksort($contentPanelList);
            foreach ($contentPanelList as $schoolTypeId => $content) {
                if (($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))) {
                    if (isset($toggleList[$tblSchoolType->getId()])) {
                        array_unshift($content, new ToggleSelective('Alle wählen/abwählen', $toggleList[$tblSchoolType->getId()]));
                    }
                    $columnList[] = new LayoutColumn(new Panel($tblSchoolType->getName(), $content, Panel::PANEL_TYPE_INFO), $size);
                }
            }

            return new LayoutGroup(
                Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                new Title($tblDivisionCourseType->getName() . 'n')
            );
        }

        return false;
    }

    /**
     * @param array $contentPanelList
     * @param array $toggleList
     * @param TblYear $tblYear
     * @param string $TypeIdentifier
     * @param array $divisionCourseExistsList
     *
     * @return void
     */
    private function setContentPanelListForDivisionCourseType(array &$contentPanelList, array &$toggleList, TblYear $tblYear, string $TypeIdentifier,
        array $divisionCourseExistsList
    ) {
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, $TypeIdentifier))) {
            $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('Name', new StringNaturalOrderSorter());
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())) {
                    foreach ($tblSchoolTypeList as $tblSchoolType) {
                        $name = "Data[Division][{$tblDivisionCourse->getId()}]";
                        $checkbox = new CheckBox($name, $tblDivisionCourse->getDisplayName(), 1);
//                        // bereits hinzugefügte kurse sollen nicht wieder entfernt werden können, da dann die Daten der Zeugnisvorbereitung gelöscht werden
//                        if (isset($divisionCourseExistsList[$tblDivisionCourse->getId()])) {
//                            $checkbox->setDisabled();
//                        } else {
                        $toggleList[$tblSchoolType->getId()][$tblDivisionCourse->getId()] = $name;
//                        }
                        $contentPanelList[$tblSchoolType->getId()][$tblDivisionCourse->getId()] = $checkbox;
                        // erstmal die Kurse bei mehreren Schularten nur einmal anzeigen
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param null $GenerateCertificateId
     *
     * @return Stage|string
     */
    public function frontendDivision($GenerateCertificateId = null)
    {
        $Stage = new Stage('Zeugnis generieren', 'Kursübersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        $Stage->setMessage(
            new Warning(new Bold('Hinweis: ')
                . new Container('Für die automatischen Zuordnungen der Zeugnisvorlagen zu den Schülern werden die folgenden Daten herangezogen:')
                . new Container('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&ndash; Die Schulart wird über den Schulverlauf ermittelt (Schülerakte -> Schulverlauf -> Schulart).')
                . new Container('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&ndash; Bei Zeugnisvorlagen der Oberschule ab 
                    Klassenstufe 7 ist der Bildungsgang erforderlich (Schülerakte -> Schulverlauf -> Bildungsgang).')
                . new Container('Bei staatlichen Zeugnisvorlagen ist zusätzlich die aktuelle Schule erforderlich (Schülerakte -> Schulverlauf -> Schule).')
            )
        );

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))
            && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
        ) {
            $tableData = array();
            if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))
                && ($tblYear = $tblGenerateCertificate->getServiceTblYear())
            ) {
                foreach ($tblPrepareList as $tblPrepare) {
                    if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())) {
                        $certificateNameList = array();
                        $schoolNameList = array();
                        $countTemplates = $countStudents = 0;
                        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                            $countStudents = count($tblPersonList);
                            $countStudentsMainOnly = 0;
                            foreach ($tblPersonList as $tblPerson) {
                                // Schulnamen
                                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                                    && ($tblCompany = $tblStudentEducation->getServiceTblCompany())
                                ) {
                                    if (!isset($schoolNameList[$tblCompany->getId()])) {
                                        $schoolNameList[$tblCompany->getId()] = $tblCompany->getName();
                                    }
                                } else {
                                    $schoolNameList[0] = new Warning(new Exclamation() . ' Keine aktuelle Schule in der Schülerakte gepflegt');
                                }

                                // Template bereits gesetzt
                                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                                ) {
                                    $countTemplates++;
                                    if (!isset($certificateNameList[$tblCertificate->getId()])) {
                                        $tblConsumer = $tblCertificate->getServiceTblConsumer();
                                        $certificateNameList[$tblCertificate->getId()]
                                            = ($tblConsumer ? $tblConsumer->getAcronym() . ' ' : '')
                                            . $tblCertificate->getName()
                                            . ($tblCertificate->getDescription() ? ' ' . $tblCertificate->getDescription() : '');
                                    }
                                }

                                // bei Abschlusszeugnisse Klassenstufe 9 OS nur Hauptschüler zählen
                                if ($tblCertificateType->getIdentifier() == 'DIPLOMA'
                                    && $tblStudentEducation
                                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                                    && $tblSchoolType->getShortName() == 'OS'
                                    && $tblStudentEducation->getLevel() == 9
                                    && ($tblCourse = $tblStudentEducation->getServiceTblCourse())
                                    && $tblCourse->getName() == 'Hauptschule'
                                ) {
                                    $countStudentsMainOnly++;
                                }
                            }
                            if ($countStudentsMainOnly > 0) {
                                $countStudents = $countStudentsMainOnly;
                            }
                        }

                        $text = '';
                        if (!empty($certificateNameList)) {
                            $text = implode(', ', $certificateNameList);
                        }
                        $schoolText = '';
                        if (!empty($schoolNameList)) {
                            foreach ($schoolNameList as $schoolName) {
                                $schoolText .= new Container($schoolName);
                            }
                        }

                        $hasMissingForeignLanguage = false;
                        // check missing subjects on certificates
                        if (($missingSubjects = Setting::useService()->getCheckCertificateSubjectsForDivisionSubject($tblPrepare, $certificateNameList, $hasMissingForeignLanguage))) {
                            ksort($missingSubjects);
                        }
                        if ($missingSubjects) {
                            $missingSubjectsString = new Warning(new Ban() .  ' ' . implode(', ',
                                $missingSubjects) . (count($missingSubjects) > 1 ? ' fehlen' : ' fehlt')
                                . ' auf Zeugnisvorlage(n)'
                                .($hasMissingForeignLanguage
                                    ? ' ' . new ToolTip(new Info(),
                                        'Bei Fremdsprachen kann die Warnung unter Umständen ignoriert werden,
                                         bitte prüfen Sie die Detailansicht unter Bearbeiten.') : ''));
                        } else {
                            $missingSubjectsString = new Success(
                                new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Alle Fächer sind zugeordnet.'
                            );
                        }

                        $tableData[] = array(
                            'SchoolType' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                            'Division' => $tblDivisionCourse->getDisplayName(),
                            'School' => $schoolText,
                            'Status' => ($countTemplates < $countStudents
                                ? new Warning(new Exclamation() . ' ' . $countTemplates . ' von '
                                    . $countStudents . ' Zeugnisvorlagen zugeordnet.')
                                : new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                                    . ' ' . $countTemplates . ' von ' . $countStudents . ' Zeugnisvorlagen zugeordnet.')),
                            'Templates' => $text,
                            'CheckSubjects' => $missingSubjectsString,
                            'Option' =>
                                new Standard('', '/Education/Certificate/Generate/Division/SelectTemplate',
                                    new Edit(),
                                    array(
                                        'PrepareId' => $tblPrepare->getId(),
                                    ),
                                    'Bearbeiten'
                                )
                        );
                    }
                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ', $tblCertificateType->getName(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Name', $tblGenerateCertificate->getName(), Panel::PANEL_TYPE_INFO)
                            ), 6),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData(
                                    $tableData, null, array(
                                    'SchoolType' => 'Schulart',
                                    'Division' => 'Kurs',
                                    'School' => 'Aktuelle Schulen',
                                    'Status' => 'Zeugnisvorlagen Zuordnung',
                                    'Templates' => 'Zeugnisvorlagen',
                                    'CheckSubjects' => 'Prüfung Fächer/Zeugnis',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array('0', 'asc'),
                                            array('1', 'asc'),
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 1)
                                        ),
                                        "paging" => false,
                                        "iDisplayLength" => -1
                                    )
                                )
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Zeugnisgenerierung nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param null $PrepareId
     * @param null $Data
     *
     * @return Stage|Danger
     */
    public function frontendSelectTemplate($PrepareId = null, $Data = null)
    {
        $Stage = new Stage('Zeugnis generieren', 'Zeugnisvorlagen auswählen');
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())
            && ($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate/Division', new ChevronLeft(),
                    array('GenerateCertificateId' => $tblGenerateCertificate->getId()))
            );

            $isDiploma = $tblCertificateType->getIdentifier() == 'DIPLOMA';
            $tableData = array();
            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                $count = 0;
                foreach ($tblPersonList as $tblPerson) {
                    $isMuted = false;
                    $courseName = '';
                    $tblCompany = false;
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                        $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                        $tblCourse = $tblStudentEducation->getServiceTblCourse();
                        $tblCompany = $tblStudentEducation->getServiceTblCompany();
                        // berufsbildende Schulart
                        if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                            $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                        } else {
                            $courseName = $tblCourse ? $tblCourse->getName() : '';
                        }
                        $isMuted = $isDiploma
                            && $tblSchoolType && $tblSchoolType->getShortName() == 'OS'
                            && $tblStudentEducation->getLevel() == 9
                            && $tblCourse && $tblCourse->getName() != 'Hauptschule';
                    }

                    // Primärer Förderschwerpunkt -> zur Hilfe für Auswahl des Zeugnisses
                    $primaryFocus = '';
                    if (($tblSupport = Student::useService()->getSupportForReportingByPerson($tblPerson))
                        && ($tblPrimaryFocus = Student::useService()->getPrimaryFocusBySupport($tblSupport))
                    ) {
                        $primaryFocus = $tblPrimaryFocus->getName();
                    }

                    $tblCertificate = false;
                    if ($isMuted) {
                        $template = '';
                    } else {
                        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                        ) {

                        } else {
                            // Noteninformation
                            if ($tblPrepare->isGradeInformation()) {
                                $tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GradeInformation');
                            }
                        }

                        $template = ApiGenerate::receiverContent(
                            $this->getCertificateSelectBox(
                                $tblPerson->getId(),
                                $tblCertificate ? $tblCertificate->getId() : 0,
                                $tblCertificateType->getId(),
                            ), 'ChangeCertificate_' . $tblPerson->getId()
                        );
                    }

                    $checkSubjectsString = '';
                    if ($tblCertificate) {
                        // Abitur Fächerprüfung ignorieren
                        if ($tblCertificate->getCertificate() == 'GymAbitur') {
                            $checkSubjectsString = new Success(
                                new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Keine Fächerzuordnung erforderlich.'
                            );
                        } elseif (($checkSubjectList = Setting::useService()->getCheckCertificateMissingSubjectsForPerson($tblPerson, $tblYear, $tblCertificate))) {
                            $checkSubjectsString = new WarningText(new Ban() . ' '
                                . implode(', ', $checkSubjectList)
                                . (count($checkSubjectList) > 1 ? ' fehlen' : ' fehlt') . ' auf Zeugnisvorlage');
                        } else {
                            $checkSubjectsString = new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' alles ok');
                        }
                    }

                    $count++;
                    $tableData[$tblPerson->getId()] = array(
                        'Number' => $isMuted ? new Muted($count) : $count,
                        'Student' => $isMuted ? new Muted($tblPerson->getLastFirstName()) : $tblPerson->getLastFirstName(),
                        'Course' => $isMuted ? new Muted($courseName) : $courseName,
                        'School' => $isMuted ? '' : ($tblCompany ? $tblCompany->getName() : new Warning(
                            new Exclamation() . ' Keine aktuelle Schule in der Schülerakte gepflegt.'
                        )),
                        'PrimaryFocus' => $isMuted ? new Muted($primaryFocus) : $primaryFocus,
                        'CheckSubjects' => $checkSubjectsString,
                        'Template' => $template
                    );
                }
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TableData(
                                $tableData, null, array(
                                'Number' => 'Nr.',
                                'Student' => 'Schüler',
                                'Course' => 'Bildungsgang',
                                'School' => 'Aktuelle Schule',
                                'PrimaryFocus' => 'primärer FS',
                                'Template' => 'Zeugnisvorlage'
                                    . new PullRight(
                                        (new Standard('Alle bearbeiten', ApiGenerate::getEndpoint()))
                                            ->ajaxPipelineOnClick(ApiGenerate::pipelineOpenCertificateModal($tblPrepare->getId()))
                                    ),
                                'CheckSubjects' => 'Prüfung Fächer/Zeugnis'
                                ),
                                array(
                                    'columnDefs' => array(
                                        array("orderable" => false, "targets" => array(1,2,3,4,5,6)),
                                    ),
                                    'order' => array(
                                        array(0, 'asc'),
                                    ),
                                    'pageLength' => -1,
                                    'paging' => false,
                                    'info' => false,
                                    'searching' => false,
                                    'responsive' => false
                                )
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()));

            $Stage->setContent(
                ApiGenerate::receiverModal()
                .new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblPrepare->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ', $tblCertificateType->getName(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Name', $tblGenerateCertificate->getName(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Kurs', $tblDivisionCourse->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            ), 3)
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                Generate::useService()->editCertificateTemplates($form, $tblPrepare, $Data)
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return new Danger('Kurs nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param $personId
     * @param $certificateId
     * @param $certificateTypeId
     *
     * @return SelectBox
     */
    public function getCertificateSelectBox($personId, $certificateId, $certificateTypeId): SelectBox
    {
        $global = $this->getGlobal();
        $global->POST['Data'][$personId] = $certificateId;
        $global->savePost();

        $tblCertificateAllByType = array();
        if (($tblCertificateType = Generator::useService()->getCertificateTypeById($certificateTypeId))) {
            $tblConsumer = Consumer::useService()->getConsumerBySession();
            $tblCertificateAllStandard = Generator::useService()->getCertificateAllByConsumerAndCertificateType(null, $tblCertificateType);
            $tblCertificateAllConsumer = Generator::useService()->getCertificateAllByConsumerAndCertificateType($tblConsumer, $tblCertificateType);
            if ($tblCertificateAllConsumer) {
                $tblCertificateAllByType = array_merge($tblCertificateAllByType, $tblCertificateAllConsumer);
            }
            if ($tblCertificateAllStandard) {
                $tblCertificateAllByType = array_merge($tblCertificateAllByType, $tblCertificateAllStandard);
            }
        }

        return new SelectBox('Data[' . $personId . ']',
            '',
            array(
                '{{ serviceTblConsumer.Acronym }} {{ Name }} {{Description}}' => $tblCertificateAllByType
            ),
            null,
            true,
            null
        );
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditGenerate($Id = null, $Data = null)
    {
        $Stage = new Stage('Zeugnis generieren', 'Bearbeiten');
//        $Stage->setMessage('Die Notenaufträge können nur geändert werden bis ein Zeugnis freigeben wurde.');
        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($Id))) {
            $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Data']['Date'] = $tblGenerateCertificate->getDate();
                $Global->POST['Data']['AppointedDateForAbsence'] = $tblGenerateCertificate->getAppointedDateForAbsence();
                $Global->POST['Data']['Name'] = $tblGenerateCertificate->getName();
                $Global->POST['Data']['IsTeacherAvailable'] = $tblGenerateCertificate->isDivisionTeacherAvailable();
                $Global->POST['Data']['HeadmasterName'] = $tblGenerateCertificate->getHeadmasterName();
                $Global->POST['Data']['GenderHeadmaster'] = $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster()
                    ? $tblGenerateCertificate->getServiceTblCommonGenderHeadmaster()->getId() : 0;
                $Global->POST['Data']['AppointedDateTask'] = $tblGenerateCertificate->getServiceTblAppointedDateTask()
                    ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getId() : 0;
                $Global->POST['Data']['BehaviorTask'] = $tblGenerateCertificate->getServiceTblBehaviorTask()
                    ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getId() : 0;

                $Global->savePost();
            }

            $message = '';
            if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                $countBehaviorGrades = 0;
                foreach ($tblPrepareList as $tblPrepare) {
                    if (($tblBehaviorGradeList = Prepare::useService()->getBehaviorGradeAllByPrepareCertificate($tblPrepare))) {
                        $countBehaviorGrades += count($tblBehaviorGradeList);
                    }
                }

                if ($countBehaviorGrades > 0) {
                    $message = new WarningMessage(
                        'Es wurden bereits ' . $countBehaviorGrades . ' Kopfnoten festgelegt',
                        new Exclamation()
                    );
                }
            }

            $tblYear = $tblGenerateCertificate->getServiceTblYear();
            $Form = $this->formEditGenerate($tblYear ?: null)
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Zeugnisgenerierung',
                                    array(
                                        $tblGenerateCertificate->getDate(),
                                        $tblGenerateCertificate->getName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                ($message !== '' ? $message : '')
                                . new Well(Generate::useService()->updateGenerateCertificate($Form, $tblGenerateCertificate, $Data))
                            ),
                        ))
                    ), new Title(new Edit() . ' Bearbeiten'))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugnisgenerierung nicht gefunden', new Ban())
                . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblYear|null $tblYear
     *
     * @return Form
     */
    private function formEditGenerate(?TblYear $tblYear): Form
    {
        if ($tblYear) {
            $tblAppointedDateTaskListByYear = Grade::useService()->getAppointedDateTaskListByYear($tblYear);
            $tblBehaviorTaskListByYear = Grade::useService()->getBehaviorTaskListByYear($tblYear);
        } else {
            $tblAppointedDateTaskListByYear = false;
            $tblBehaviorTaskListByYear = false;
        }

        $selectBoxAppointedDateTask = new SelectBox('Data[AppointedDateTask]', 'Stichtagsnotenauftrag',
            array('{{ DateString }} {{ Name }}' => $tblAppointedDateTaskListByYear));
        $selectBoxBehaviorTask = new SelectBox('Data[BehaviorTask]', 'Kopfnotenauftrag',
            array('{{ DateString }} {{ Name }}' => $tblBehaviorTaskListByYear));

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Zeugnis',
                        array(
                            (new TextField('Data[Name]', '', 'Name des Zeugnisauftrags'))->setRequired(),
                            (new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()))->setRequired(),
                            (new DatePicker('Data[AppointedDateForAbsence]', '', new ToolTip('Optionaler Stichtag für Fehlzeiten',
                                'Für die Fehlzeiten kann ein optionaler Stichtag gesetzt werden, ansonsten wird
                                das Zeugnisdatum als Stichtag verwendet.'), new Calendar()))
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Notenaufträge',
                        array(
                            $selectBoxAppointedDateTask,
                            $selectBoxBehaviorTask
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
                new FormColumn(
                    new Panel(
                        'Unterzeichner',
                        array(
                            new CheckBox('Data[IsTeacherAvailable]',
                                'Name des Kursleiters und Name des/der Schulleiters/in (falls vorhanden) auf dem Zeugnis anzeigen',
                                1
                            ),
                            new TextField('Data[HeadmasterName]', '', 'Name des/der Schulleiters/in'),
                            new Panel(
                                new Small(new Bold('Geschlecht des/der Schulleiters/in')),
                                array(
                                    (new RadioBox('Data[GenderHeadmaster]', 'Männlich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Männlich'))
                                            ? $tblCommonGender->getId() : 0)),
                                    (new RadioBox('Data[GenderHeadmaster]', 'Weiblich',
                                        ($tblCommonGender = Common::useService()->getCommonGenderByName('Weiblich'))
                                            ? $tblCommonGender->getId() : 0))
                                ),
                                Panel::PANEL_TYPE_DEFAULT
                            )
                        ),
                        Panel::PANEL_TYPE_INFO
                    ), 4
                ),
            ))
        )));
    }

    /**
     * @param $Id
     * @param $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyGenerate($Id = null, $Confirm = null): Stage
    {
        $Stage = new Stage('Zeugnisgenerierung', 'Löschen');
        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($Id))) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Generate', new ChevronLeft()
            ));

            if (!$Confirm) {
                $divisionCourseList = array();
                $divisionCourseList[0] = 'Zeugnisdatum: ' . $tblGenerateCertificate->getDate();
                $divisionCourseList[1] = 'Typ: ' . (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                        ? $tblCertificateType->getName() : '');
                $divisionCourseList[2] = 'Name: ' . $tblGenerateCertificate->getName();

                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    $divisionCourseList[3] = '&nbsp;';
                    foreach ($tblPrepareList as $tblPrepare) {
                        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())) {
                            $hasBehaviorGrades = false;
                            $countBehaviorGrades = 0;
                            $hasPrepareInformation = false;
                            $countPrepareInformation = 0;
                           if (($tblBehaviorGradeList = Prepare::useService()->getBehaviorGradeAllByPrepareCertificate($tblPrepare))) {
                                $hasBehaviorGrades = true;
                                $countBehaviorGrades = count($tblBehaviorGradeList);
                            }
                            if (($tblPrepareInformationList = Prepare::useService()->getPrepareInformationAllByPrepare($tblPrepare))) {
                                $hasPrepareInformation = true;
                                $countPrepareInformation = count($tblPrepareInformationList);
                            }
                            if ($hasBehaviorGrades && $hasPrepareInformation) {
                                $message = 'Es wurden bereits ' . $countBehaviorGrades . ' Kopfnoten festgelegt und '
                                    . $countPrepareInformation . ' Sonstige Informationen gespeichert ' . new Exclamation();
                            } elseif ($hasBehaviorGrades) {
                                $message = 'Es wurden bereits ' . $countBehaviorGrades . ' Kopfnoten festgelegt '
                                    . new Exclamation();
                            } elseif ($hasPrepareInformation) {
                                $message = 'Es wurden bereits ' . $countPrepareInformation . ' Sonstige Informationen gespeichert ' . new Exclamation();
                            } else {
                                $message = '';
                            }

                            $divisionCourseList[$tblDivisionCourse->getDisplayName()] = 'Kurs: ' . $tblDivisionCourse->getDisplayName()
                                . ($message !== '' ? new \SPHERE\Common\Frontend\Text\Repository\Danger('&nbsp;&nbsp;&nbsp;' . $message) : '');
                        }
                    }
                }

                ksort($divisionCourseList);

                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            new Question() . ' Diese Zeugnisgenerierung wirklich löschen?',
                            $divisionCourseList,
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/Certificate/Generate/Destroy', new Ok(),
                                array(
                                    'Id' => $Id,
                                    'Confirm' => true
                                )
                            )
                            . new Standard(
                                'Nein', '/Education/Certificate/Generate', new Disable()
                            )
                        ),
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            (Generate::useService()->destroyGenerateCertificate($tblGenerateCertificate)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zeugnisgenerierung wurde gelöscht')
                                    . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_SUCCESS)
                                : new Danger(new Ban() . ' Die Zeugnisgenerierung konnte nicht gelöscht werden')
                                    . new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_ERROR)
                            )
                        ))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Zeugnisgenerierung konnte nicht gefunden werden'),
                        new Redirect('/Education/Certificate/Generate', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $GenerateCertificateId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendGenerateSetting($GenerateCertificateId = null, $Data = null)
    {
        $Stage = new Stage('Zeugnis generieren', 'Zusätzliche Einstellungen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));
        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))) {
            $tblPersonList = false;
            if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            }

            if (($tblGenerateCertificateSettingList = Generate::useService()->getGenerateCertificateSettingAllByGenerateCertificate($tblGenerateCertificate))) {
                $global = $this->getGlobal();
                foreach ($tblGenerateCertificateSettingList as $tblGenerateCertificateSetting) {
                    $global->POST['Data'][$tblGenerateCertificateSetting->getField()] = $tblGenerateCertificateSetting->getValue() ?: 0;
                }
                $global->savePost();
            }

            $form = new Form(array(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            new SelectBox('Data[Leader]', 'Vorsitzende(r)', array('{{ LastFirstName }}' => $tblPersonList))
                        ), 4),
                        new FormColumn(array(
                            new SelectBox('Data[FirstMember]', 'Mitglied', array('{{ LastFirstName }}' => $tblPersonList))
                        ), 4),
                        new FormColumn(array(
                            new SelectBox('Data[SecondMember]', 'Mitglied', array('{{ LastFirstName }}' => $tblPersonList))
                        ), 4),
                    )), new \SPHERE\Common\Frontend\Form\Repository\Title('Prüfungsausschuss')
                )
            ));
            $form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ',
                                    $tblGenerateCertificate->getServiceTblCertificateType()
                                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Stichtagsnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblAppointedDateTask()
                                        ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Kopfnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblBehaviorTask()
                                        ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(Generate::useService()->updateAbiturSettings($form, $tblGenerateCertificate, $Data))
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugniserstellung nicht gefunden', new Exclamation());
        }
    }
}