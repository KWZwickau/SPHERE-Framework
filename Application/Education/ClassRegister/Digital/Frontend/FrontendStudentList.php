<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\People\Meta\Agreement\ApiAgreement;
use SPHERE\Application\Api\People\Meta\Liberation\ApiLiberation;
use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Document\Generator\Repository\Element\Image;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Commodity;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\Common\Frontend\Link\Repository\PhoneLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Stage;

class FrontendStudentList extends FrontendSelectDivisionCourse
{
    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function frontendStudentList(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ): string {
        $icon = new PersonGroup();
        $name = 'Schülerliste';
        $Route = '/Education/ClassRegister/Digital/Student';
        $content = $this->getStudentListContent($DivisionCourseId, $BasicRoute, $Route);

        return Digital::useFrontend()->getStage($DivisionCourseId, $BasicRoute, $Route, $icon, $name, $content, $BackDivisionCourseId);
    }

    /**
     * @param $DivisionCourseId
     * @param $BasicRoute
     * @param $Route
     *
     * @return string
     */
    public function getStudentListContent($DivisionCourseId, $BasicRoute, $Route): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(false, true, new DateTime('today')))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $studentTable = array();
            $count = 0;
            $hasColumnPicture = false;
            $hasSupport = false;
            $hasMedicalRecord = false;
            $hasAgreement = false;
            $hasLiberation = false;
            $hasColumnCourse = false;
            $hasDivision = false;
            $hasCoreGroup = false;
            $hasSchoolAttendanceYear = false;
            $isDivision = $tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION;
            foreach ($tblPersonList as $tblPerson) {
                $schoolType = '';
                $level = '';
                $divisionName = '';
//                $divisionTeacher = '';
                $coreGroupName = '';
//                $coreGroupTeacher = '';
                $schoolAttendanceYear = '';
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);

                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
                        $schoolType = $tblSchoolType->getShortName();
                        // Schulbesuchsjahr bei Förderschulen anzeigen
                        if ($tblSchoolType->getShortName() == 'FöS') {
                            $hasSchoolAttendanceYear = true;
                            $schoolAttendanceYear = $tblStudent->getSchoolAttendanceYear(false);
                        }
                    }
                    $tblCourse = $tblStudentEducation->getServiceTblCourse();
                    $level = $tblStudentEducation->getLevel();
                    if (!$isDivision && ($tblDivision = $tblStudentEducation->getTblDivision())) {
                        $hasDivision = true;
                        $divisionName = $tblDivision->getName();
//                        $divisionTeacher = $tblDivision->getDivisionTeacherNameListString(', ');
                    }
                    if ($isDivision && ($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                        $hasCoreGroup = true;
                        $coreGroupName = $tblCoreGroup->getName();
//                        $coreGroupTeacher = $tblCoreGroup->getDivisionTeacherNameListString(', ');
                    }
                } else {
                    $tblSchoolType = false;
                    $tblCourse = false;
                }

                $birthday = '';
                $Gender = '';
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                    if ($tblCommon->getTblCommonBirthDates()) {
                        $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                        $tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender();
                        if ($tblGender) {
                            $Gender = $tblGender->getShortName();
                        }
                    }
                }
                $PersonPicture = '';
                if(($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))){
                    $hasColumnPicture = true;
                    $PersonPicture = new Center((new Link($tblPersonPicture->getPicture('50px', '10px'), $tblPerson->getId()))
                        ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId())));
                }

                if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                    $courseName = Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                } else {
                    $courseName = $tblCourse ? $tblCourse->getName() : '';
                }
                if (!$hasColumnCourse && $courseName) {
                    $hasColumnCourse = true;
                }

                $medicalRecord = '';
                $agreement = '';
                $liberation = '';
                $support = '';
                if ($tblStudent) {
                    if (($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                        && ($tblMedicalRecord->getDisease()
                            || $tblMedicalRecord->getMedication()
                            || $tblMedicalRecord->getAttendingDoctor())
                    ) {
                        $hasMedicalRecord = true;
                        $medicalRecord = (new Standard('', MedicalRecordReadOnly::getEndpoint(), new Hospital(), array(), 'Krankenakte'))
                            ->ajaxPipelineOnClick(MedicalRecordReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                    }

                    if (Student::useService()->getStudentAgreementAllByStudent($tblStudent)) {
                        $hasAgreement = true;
                        $agreement = (new Standard('', ApiAgreement::getEndpoint(), new Check(), array(), 'Einverständniserklärung'))
                            ->ajaxPipelineOnClick(ApiAgreement::pipelineOpenOverViewModal($tblPerson->getId()));
                    }
                    if(Student::useService()->getStudentLiberationAllByStudent($tblStudent)) {
                        $hasLiberation = true;
                        $liberation = (new Standard('', ApiLiberation::getEndpoint(), new Extern(), array(), 'Unterrichtsbefreiung'))
                            ->ajaxPipelineOnClick(ApiLiberation::pipelineOpenOverViewModal($tblPerson->getId()));
                    }
                }
                if (Student::useService()->getIsSupportByPerson($tblPerson)) {
                    $hasSupport = true;
                    $support = (new Standard('', ApiSupportReadOnly::getEndpoint(), new Tag(), array(), 'Inklusion'))
                        ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
                }

                $studentTable[] = array(
                    'Number'        => ++$count,
                    'Name'          => new Bold($tblPerson->getLastFirstNameWithCallNameUnderline(true)),
                    'Picture'       => $PersonPicture,
                    'Support'       => $support,
                    'MedicalRecord' => $medicalRecord,
                    'Agreement'     => $agreement,
                    'Gender'        => $Gender,
                    'Birthday'      => $birthday,
                    'Address'       => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiTwoRowString(false) : '',
                    'SchoolType'    => $schoolType,
                    'Level'         => $level,
                    'Liberation'    => $liberation,
                    'Course'        => $courseName,
                    'DivisionName'  => $divisionName,
//                    'DivisionTeacher' => $divisionTeacher,
                    'CoreGroupName' => $coreGroupName,
//                    'CoreGroupTeacher' => $coreGroupTeacher,
                    'SchoolAttendanceYear' => $schoolAttendanceYear,
                    'Option'        =>
                        (new Standard(
                            '', '/Education/ClassRegister/Digital/StudentDetail', new EyeOpen(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'PersonId'   => $tblPerson->getId(),
                                'BasicRoute' => $BasicRoute,
                            ),
                            'Schülerdetails anzeigen'
                        ))
                );
            }

            $countDateColumn = 3;
            $columns['Number'] = '#';
            $columns['Name'] = 'Name';
            if ($hasColumnPicture) {
                $countDateColumn++;
                $columns['Picture'] = 'Foto';
            }
            if ($hasSupport) {
                $countDateColumn++;
                $columns['Support'] = 'Inklu&shy;sion';
            }
            if ($hasMedicalRecord) {
                $countDateColumn++;
                $columns['MedicalRecord'] = 'Kranken&shy;akte';
            }
            if ($hasAgreement) {
                $countDateColumn++;
                $columns['Agreement'] = 'Einver&shy;ständnis&shy;erklärung';
            }
            $columns['Gender'] = 'Ge&shy;schlecht';
            $columns['Birthday'] = 'Geburts&shy;datum';
            $columns['Address'] = 'Adresse';
            $columns['SchoolType'] = 'Schul&shy;art';
            $columns['Level'] = 'Klassen&shy;stufe';
            if ($hasLiberation) {
                $countDateColumn++;
                $columns['Liberation'] = 'Befreiung';
            }
            if ($hasColumnCourse) {
                $columns['Course'] = 'Bildungs&shy;gang';
            }
            if ($hasDivision) {
                $columns['DivisionName'] = 'Klasse';
//                $columns['DivisionTeacher'] = 'Klassen&shy;lehrer';
            }
            if ($hasCoreGroup) {
                $columns['CoreGroupName'] = 'Stamm&shy;gruppe';
//                $columns['CoreGroupTeacher'] = 'Tutor';
            }
            if ($hasSchoolAttendanceYear) {
                $columns['SchoolAttendanceYear'] = 'SBJ';
            }

            $columns['Option'] = '';

            return
                ApiSupportReadOnly::receiverOverViewModal()
                . MedicalRecordReadOnly::receiverOverViewModal()
                . ApiAgreement::receiverOverViewModal()
                . ApiLiberation::receiverOverViewModal()
                . ApiPersonPicture::receiverModal()
                . (($inActivePanel = \SPHERE\Application\Reporting\Standard\Person\Person::useFrontend()
                    ->getInActiveStudentPanel($tblDivisionCourse, false, $BasicRoute, $Route)) ? $inActivePanel : '')
                . (new TableData($studentTable, null, $columns,
                    array(
                        'paging' => false,
                        'columnDefs' => array(
                            array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                            array('type' => 'de_date', 'targets' => $countDateColumn),
                            // feste breite für Adresse
                            array('width' => '160px', 'targets' => $countDateColumn + 1),
                            array('orderable' => false, 'width' => '30px', 'targets' => -1),
                        ),
                        'responsive' => false
                    )
                ));
        }

        return '';
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param string $BasicRoute
     *
     * @return string
     *
     * @noinspection PhpUnused
     */
    public function frontendStudentDetail($DivisionCourseId = null, $PersonId = null, string $BasicRoute = '') : string
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Detailansicht des Schülers');
        $Route = '/Education/ClassRegister/Digital/StudentDetail';
        $Stage->addButton(new Standard('Zurück', '/Education/ClassRegister/Digital/Student' , new ChevronLeft(),
            array(
                'DivisionCourseId' => $DivisionCourseId,
                'BasicRoute' => $BasicRoute,
            ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
        ) {
            $tblStudent = $tblPerson->getStudent();

            // Einverständniserklärung, Inklusion, Krankenakte
            if ($tblStudent) {
                if (($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())
                    && ($tblMedicalRecord->getDisease()
                        || $tblMedicalRecord->getMedication()
                        || $tblMedicalRecord->getAttendingDoctor())
                ) {
                    $Stage->addButton((new Standard('Krankenakte', MedicalRecordReadOnly::getEndpoint(), new Hospital(), array(), 'Krankenakte'))
                        ->ajaxPipelineOnClick(MedicalRecordReadOnly::pipelineOpenOverViewModal($tblPerson->getId())));
                }

                if (Student::useService()->getStudentAgreementAllByStudent($tblStudent)) {
                    $Stage->addButton((new Standard('Einverständniserklärung', ApiAgreement::getEndpoint(), new Check(), array(), 'Einverständniserklärung'))
                        ->ajaxPipelineOnClick(ApiAgreement::pipelineOpenOverViewModal($tblPerson->getId())));
                }
            }
            if (Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Integration')) {
                $Stage->addButton(new Standard(
                    'Inklusion bearbeiten', '/Education/ClassRegister/Digital/Integration', new Commodity(),
                    array(
                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                        'PersonId'   => $tblPerson->getId(),
                        'BasicRoute' => $BasicRoute,
                        'ReturnRoute'=> $Route,
                    ),
                    'Inklusion des Schülers verwalten'
                ));
            } elseif (Student::useService()->getIsSupportByPerson($tblPerson)) {
                $Stage->addButton((new Standard('Inklusion', ApiSupportReadOnly::getEndpoint(), new Tag(), array(), 'Inklusion'))
                    ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId())));
            }

            $absenceButton = (new Standard(
                'Fehlzeiten bearbeiten', '/Education/ClassRegister/Digital/AbsenceStudent', new Time(),
                array(
                    'DivisionCourseId' => $tblDivisionCourse->getId(),
                    'PersonId' => $tblPerson->getId(),
                    'BasicRoute' => $BasicRoute,
                    'ReturnRoute' => $Route
                ),
                'Fehlzeiten des Schülers verwalten'
            ));
            $Stage->addButton($absenceButton);

            $Stage->addButton(new External('Herunterladen', '/Api/Document/Standard/ClassRegister/StudentDetail/Create', new Download(), [
                'DivisionCourseId' => $DivisionCourseId,
                'PersonId' => $tblPerson->getId(),
            ], 'Detailansicht des Schülers als PDF herunterladen'));

            $Stage->setContent(
                ApiSupportReadOnly::receiverOverViewModal()
                . MedicalRecordReadOnly::receiverOverViewModal()
                . ApiAgreement::receiverOverViewModal()
                . ApiPersonPicture::receiverModal()
                . $this->getStudentDetailLayout($tblDivisionCourse, $tblPerson, false, $absenceButton)
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param bool $isDownload
     * @param string $absenceButton
     *
     * @return Layout|null
     */
    public function getStudentDetailLayout(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, bool $isDownload, string $absenceButton = ''): ?Layout
    {
        if ($isDownload) {
            $pictureHeight = '115px';
            $sizeContact = 6;
        } else {
            $pictureHeight = '138px';
            $sizeContact = 4;
        }

        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && (list($fromDate, $tillDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear))
            && $fromDate
            && $tillDate
        ) {
            $tblStudent = $tblPerson->getStudent();

            $panelStudent = new Panel(
                'Schüler',
                $tblPerson->getLastFirstNameWithCallNameUnderline(true),
                Panel::PANEL_TYPE_INFO
            );
            $panelCourse = new Panel(
                'Kurs',
                $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName(),
                Panel::PANEL_TYPE_INFO
            );
            $panelGender = new Panel(
                'Geschlecht',
                $tblPerson->getGenderString(),
                Panel::PANEL_TYPE_INFO
            );
            $panelBirthday = new Panel(
                'Geburtsdatum',
                $tblPerson->getBirthday(),
                Panel::PANEL_TYPE_INFO
            );

            $panelEducation = '';
            $tblSchoolType = null;
            $tblCompany = null;
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                $tblSchoolType = $tblStudentEducation->getServiceTblSchoolType();
                $tblCompany = $tblStudentEducation->getServiceTblCompany();
                $tblCourse = $tblStudentEducation->getServiceTblCourse();

                $content['company'] = 'Schule: ' . ($tblCompany ? $tblCompany->getName() : '');
                $content['schoolType'] = 'Schulart: ' . ($tblSchoolType ? $tblSchoolType->getName() : '');
                $content['level'] = 'Klassenstufe : ' . $tblStudentEducation->getLevel();
                if ($tblSchoolType && $tblSchoolType->isTechnical()) {
                    $content['course'] = 'Bildungsgang: ' . Student::useService()->getTechnicalCourseGenderNameByPerson($tblPerson);
                } else {
                    $content['course'] = 'Bildungsgang: ' . ($tblCourse ? $tblCourse->getName() : '');
                }
                // Schulbesuchsjahr bei Förderschulen anzeigen
                if ($tblSchoolType && $tblSchoolType->getShortName() == 'FöS' && $tblStudent) {
                    $content['schoolAttendanceYear'] = 'Schulbesuchsjahr: ' . $tblStudent->getSchoolAttendanceYear(false);
                }
                if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                    $content['division'] = 'Klasse: ' . $tblDivision->getDisplayName();
                    $content['divisionTeacher'] = 'Klassenlehrer: ' . $tblDivision->getDivisionTeacherNameListString(', ');
                }
                if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                    $content['coreGroup'] = 'Stammgruppe: ' . $tblCoreGroup->getDisplayName();
                    $content['coreGroupTeacher'] = 'Tutoren: ' . $tblCoreGroup->getDivisionTeacherNameListString(', ');
                }

                $panelEducation = new Panel(new Education() . ' Bildung', $content, Panel::PANEL_TYPE_INFO);
            }

            $tblAddress = $tblPerson->fetchMainAddress();
            $panelAddress = new Panel(new MapMarker() . ' Adresse', $tblAddress ? $tblAddress->getGuiTwoRowString() : '', Panel::PANEL_TYPE_INFO);

            $authorizedToCollect = ($tblChild = $tblPerson->getChild()) ? $tblChild->getAuthorizedToCollect() : '';
            $panelAuthorizedToCollect = new Panel(new PersonParent() . ' Abholberechtigte', $authorizedToCollect, Panel::PANEL_TYPE_INFO);

            // Fehlzeiten
            list($absenceDays, $absenceLessons)
                = Absence::useService()->getAbsenceDataByStudent($tblPerson, $tblYear, $tblCompany ?: null, $tblSchoolType ?: null, $fromDate, $tillDate);
            if ($isDownload) {
                $contentAbsence = [
                    'Zeugnisrelevante Fehlzeiten Tage (E, U): ' . $absenceDays,
                    'Zeugnisrelevante Fehlzeiten UE (E, U): ' . $absenceLessons
                ];
            } else {
                $contentAbsence = new PullRight($absenceButton) . new Container('Zeugnisrelevante Fehlzeiten Tage (E, U): ' . $absenceDays)
                    . new Container('Zeugnisrelevante Fehlzeiten UE (E, U): ' . $absenceLessons);
            }
            $panelAbsence = new Panel(new Time() . ' Fehlzeiten', $contentAbsence, Panel::PANEL_TYPE_INFO);

            // vergessene Arbeitsmittel
            $sumHomework = Digital::useService()->getForgottenSumByPersonAndYear($tblPerson, $tblYear, true);
            $sumEquipment = Digital::useService()->getForgottenSumByPersonAndYear($tblPerson, $tblYear, false);
            $forgottenData = [
                'Summe Vergessene Hausaufgaben: ' . $sumHomework,
                'Summe Vergessene Arbeitsmittel: ' . $sumEquipment,
                'Gesamtsumme: ' . ($sumHomework + $sumEquipment),
            ];
            $panelForgotten = new Panel(new History() . ' Vergessene Arbeitsmittel/Hausaufgaben', $forgottenData, Panel::PANEL_TYPE_INFO);

            $rows = [];
            if (($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))) {
                $PersonPicture = (new Link($tblPersonPicture->getPicture($pictureHeight, '10px'), $tblPerson->getId()))
                    ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId()));
            } else {
                if ($isDownload) {
                    $PersonPicture = new Image('/Common/Style/Resource/SSWIcon.png', 'auto', $pictureHeight, 0.2);
                } else {
                    $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWIcon.png');
                    $PersonPicture = '<img src="' . $File->getLocation() . '" style="height: ' . $pictureHeight . '; border-radius: 10px; opacity: 0.2">';
                }
            }
            $rows[] = new LayoutRow(array(
                new LayoutColumn(new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn($panelStudent)),
                    new LayoutRow(new LayoutColumn($panelCourse)),
                ))), 10),
                new LayoutColumn(new Center($PersonPicture), 2),
            ));

            $rows[] = new LayoutRow(array(
                new LayoutColumn($panelGender, 6),
                new LayoutColumn($panelBirthday, 6),
            ));
            $rows[] = new LayoutRow(array(
                new LayoutColumn($panelEducation, 6),
                new LayoutColumn([$panelAddress, $panelAuthorizedToCollect], 6),
            ));
            $rows[] = new LayoutRow(array(
                new LayoutColumn($panelForgotten, 6),
                new LayoutColumn($panelAbsence, 6),
            ));

            $tblRelationshipTypes = $this->getRelationshipTypes();

            // telefonnummern inklusive Bemerkung
            if ($phones = $this->getPhones($tblPerson, $tblRelationshipTypes)) {
                $columns = [];
                foreach ($phones as $phone) {
                    $columns[] = new LayoutColumn($phone, $sizeContact);
                }

                $rows[] = new LayoutRow(new LayoutColumn(new Title(new PhoneIcon() . ' Telefonnummern')));
                $rows = array_merge($rows, Grade::useService()->getLayoutRowsByLayoutColumnList($columns, $sizeContact));
            }

            // emails
            if ($mails = $this->getMails($tblPerson, $tblRelationshipTypes)) {
                $columns = [];
                foreach ($mails as $mail) {
                    $columns[] = new LayoutColumn($mail, $sizeContact);
                }

                $rows[] = new LayoutRow(new LayoutColumn(new Title(new Envelope() . ' E-Mail Adressen')));
                $rows = array_merge($rows, Grade::useService()->getLayoutRowsByLayoutColumnList($columns, $sizeContact));
            }

            return new Layout(new LayoutGroup($rows));
        }

        return null;
    }

    /**
     * @return TblType[]
     */
    private function getRelationshipTypes(): array {
        $tblRelationshipTypes = [];
        if (($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN))) {
            $tblRelationshipTypes[$tblType->getId()] = $tblType;
        }
        if (($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_AUTHORIZED))) {
            $tblRelationshipTypes[$tblType->getId()] = $tblType;
        }
        if (($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN_SHIP))) {
            $tblRelationshipTypes[$tblType->getId()] = $tblType;
        }
        if (($tblType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_EMERGENCY_CONTACT))) {
            $tblRelationshipTypes[$tblType->getId()] = $tblType;
        }

        return $tblRelationshipTypes;
    }

    private function getPhones(TblPerson $tblPerson, array $tblRelationshipTypes): array
    {
        $list = [];
        if (($phones = Phone::useService()->getPhoneListByStudent($tblPerson, $tblRelationshipTypes))) {
            foreach($phones as $phone) {
                $content = [];
                /** @var TblToPerson $tblToPerson */
                foreach ($phone['tblToPersonList'] as $tblToPerson) {
                    if (($tblPhone = $tblToPerson->getTblPhone())) {
                        $content[] = new PhoneLink($tblToPerson->getIcon() . ' ' . $tblPhone->getNumber() . ' ' , $tblPhone->getNumber())
                            . ' | ' . $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription()
                            . ($tblToPerson->getRemark() ? ' | ' . new Muted($tblToPerson->getRemark()) : '');
                    }
                }

                $list[] = new Panel(
                    $phone['tblPerson']->getFullName() . ($phone['tblRelationshipType'] ?  ' (' . $phone['tblRelationshipType']->getName() . ')' : ''),
                    $content,
                    Panel::PANEL_TYPE_DEFAULT
                );
            }
        }

        return $list;
    }

    private function getMails(TblPerson $tblPerson, array $tblRelationshipTypes): array
    {
        $list = [];
        if (($mails = Mail::useService()->getMailListByStudent($tblPerson, $tblRelationshipTypes))) {
            foreach($mails as $mail) {
                $content = [];
                /** @var \SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson $tblToPerson */
                foreach ($mail['tblToPersonList'] as $tblToPerson) {
                    if (($tblMail = $tblToPerson->getTblMail())) {
                        $content[] = new Mailto(new Envelope() . ' ' . $tblMail->getAddress() . ' ' , $tblMail->getAddress())
                            . ' | ' . $tblToPerson->getTblType()->getName() . ' ' . $tblToPerson->getTblType()->getDescription()
                            . ($tblToPerson->getRemark() ? ' | ' . new Muted($tblToPerson->getRemark()) : '');
                    }
                }

                $list[] = new Panel(
                    $mail['tblPerson']->getFullName() . ($mail['tblRelationshipType'] ?  ' (' . $mail['tblRelationshipType']->getName() . ')' : ''),
                    $content,
                    Panel::PANEL_TYPE_DEFAULT
                );
            }
        }

        return $list;
    }
}