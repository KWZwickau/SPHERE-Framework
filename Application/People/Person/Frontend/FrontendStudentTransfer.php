<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.12.2018
 * Time: 14:16
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\MassReplace\StudentFilter;
use SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class FrontendStudentTransfer
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentTransfer extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Schülertransfer';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentTransferContent($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblStudent = $tblPerson->getStudent();

//            $VisitedDivisions = array();
//            $RepeatedLevels = array();
//
//            $tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
//            if ($tblDivisionStudentAllByPerson) {
//                foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudent) {
//                    $TeacherString = ' | ';
//                    $tblDivision = $tblDivisionStudent->getTblDivision();
//                    if ($tblDivision) {
//                        $tblTeacherPersonList = Division::useService()->getTeacherAllByDivision($tblDivision);
//                        if ($tblTeacherPersonList) {
//                            foreach ($tblTeacherPersonList as $tblTeacherPerson) {
//                                if ($TeacherString !== ' | ') {
//                                    $TeacherString .= ', ';
//                                }
//                                $tblTeacher = Teacher::useService()->getTeacherByPerson($tblTeacherPerson);
//                                if ($tblTeacher) {
//                                    $TeacherString .= new Bold($tblTeacher->getAcronym().' ');
//                                }
//                                $TeacherString .= ($tblTeacherPerson->getTitle() != ''
//                                        ? $tblTeacherPerson->getTitle().' '
//                                        : '').
//                                    $tblTeacherPerson->getFirstName().' '.$tblTeacherPerson->getLastName();
//                                $tblDivisionTeacher = Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision,
//                                    $tblTeacherPerson);
//                                if ($tblDivisionTeacher && $tblDivisionTeacher->getDescription() != '') {
//                                    $TeacherString .= ' ('.$tblDivisionTeacher->getDescription().')';
//                                }
//                            }
//                        }
//                        if ($TeacherString === ' | ') {
//                            $TeacherString = '';
//                        }
//                        $tblLevel = $tblDivision->getTblLevel();
//                        $tblYear = $tblDivision->getServiceTblYear();
//                        if ($tblLevel && $tblYear) {
//                            $text = $tblYear->getDisplayName().' Klasse '.$tblDivision->getDisplayName()
//                                .new Muted(new Small(' '.($tblLevel->getServiceTblType() ? $tblLevel->getServiceTblType()->getName() : '')))
//                                .$TeacherString;
//                            $VisitedDivisions[] = $tblDivisionStudent->isInActive()
//                                ? new Strikethrough($text)
//                                : $text;
//                            foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudentTemp) {
//                                if ($tblDivisionStudent->getId() !== $tblDivisionStudentTemp->getId()
//                                    && $tblDivisionStudentTemp->getTblDivision()
//                                    && (
//                                        $tblDivisionStudentTemp->getTblDivision()->getTblLevel()
//                                        && $tblDivisionStudent->getTblDivision()->getTblLevel()->getId()
//                                        === $tblDivisionStudentTemp->getTblDivision()->getTblLevel()->getId()
//                                    )
//                                    && $tblDivisionStudentTemp->getTblDivision()->getTblLevel()->getName() != ''
//                                ) {
//                                    $RepeatedLevels[] = $tblYear->getDisplayName().' Klasse '.$tblLevel->getName();
//                                }
//                            }
//                        }
//                    }
//                }
//            }

            $enrollmentPanel = self::getStudentTransferEnrollmentPanel($tblStudent ? $tblStudent : null);
            $arrivePanel = self::getStudentTransferArrivePanel($tblStudent ? $tblStudent : null);
            $leavePanel = self::getStudentTransferLeavePanel($tblStudent ? $tblStudent : null);
//            $processPanel = self::getStudentTransferProcessPanel($tblStudent ? $tblStudent : null);
//            $visitedDivisionsPanel = new Panel('Besuchte Schulklassen',
//                $VisitedDivisions,
//                Panel::PANEL_TYPE_DEFAULT,
//                new Warning(
//                    'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
//                )
//            );
//            $repeatedDivisionsPanel = new Panel('Aktuelle Schuljahrwiederholungen',
//                $RepeatedLevels,
//                Panel::PANEL_TYPE_DEFAULT,
//                new Warning(
//                    'Vom System erkannte Schuljahr&shy;wiederholungen.'
//                    .'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
//                )
//            );

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($enrollmentPanel)
                )),
                new LayoutRow(array(
                    new LayoutColumn($arrivePanel)
                )),
                new LayoutRow(array(
                    new LayoutColumn($leavePanel)
                )),
//                new LayoutRow(array(
//                    new LayoutColumn($processPanel)
//                )),
//                new LayoutRow(array(
//                    new LayoutColumn($visitedDivisionsPanel, 6),
//                    new LayoutColumn($repeatedDivisionsPanel, 6)
//                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentTransferContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person' . new Bold(new Success($tblPerson->getFullName())),
                new TileSmall()
            );
        }

        return '';
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferEnrollmentPanel(TblStudent $tblStudent = null)
    {
        $enrollmentCompany = '';
        $enrollmentType = '';
        $enrollmentTransferType = '';
        $enrollmentCourse = '';
        $enrollmentDate = '';
        $enrollmentRemark = '';

        if ($tblStudent) {
            $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');
            $tblStudentTransferEnrollment = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeEnrollment
            );

            if ($tblStudentTransferEnrollment) {
                $enrollmentCompany = ($tblCompany = $tblStudentTransferEnrollment->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $enrollmentType = ($tblType = $tblStudentTransferEnrollment->getServiceTblType())
                    ? $tblType->getName() : '';
                $enrollmentTransferType = ($tblStudentSchoolEnrollmentType = $tblStudentTransferEnrollment->getTblStudentSchoolEnrollmentType())
                    ? $tblStudentSchoolEnrollmentType->getName() : '';
                $enrollmentCourse = ($tblCourse = $tblStudentTransferEnrollment->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $enrollmentDate = $tblStudentTransferEnrollment->getTransferDate();
                $enrollmentRemark = $tblStudentTransferEnrollment->getRemark();
            }
        }

        $contentEnrollment[] =  '&nbsp;';
        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Schule'),
                self::getLayoutColumnValue($enrollmentCompany),
                self::getLayoutColumnLabel('Schulart'),
                self::getLayoutColumnValue($enrollmentType),
                self::getLayoutColumnLabel('Einschulungsart'),
                self::getLayoutColumnValue($enrollmentTransferType),
            )),
        )));
        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Bildungsgang'),
                self::getLayoutColumnValue($enrollmentCourse),
                self::getLayoutColumnLabel('Datum'),
                self::getLayoutColumnValue($enrollmentDate),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($enrollmentRemark),
            )),
        )));

        $enrollmentPanel = new Panel(
            'Ersteinschulung',
            $contentEnrollment,
            Panel::PANEL_TYPE_INFO
        );

        return $enrollmentPanel;
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferArrivePanel(TblStudent $tblStudent = null)
    {
        $arriveCompany = '';
        $arriveStateCompany = '';
        $arriveType = '';
        $arriveCourse = '';
        $arriveDate = '';
        $arriveRemark = '';

        if ($tblStudent) {
            $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
            $tblStudentTransferArrive = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeArrive
            );

            if ($tblStudentTransferArrive) {
                $arriveCompany = ($tblCompany = $tblStudentTransferArrive->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $arriveStateCompany = ($tblStateCompany = $tblStudentTransferArrive->getServiceTblStateCompany())
                    ? $tblStateCompany->getDisplayName() : '';
                $arriveType = ($tblType = $tblStudentTransferArrive->getServiceTblType())
                    ? $tblType->getName() : '';
                $arriveCourse = ($tblCourse = $tblStudentTransferArrive->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $arriveDate = $tblStudentTransferArrive->getTransferDate();
                $arriveRemark = $tblStudentTransferArrive->getRemark();
            }
        }

        $contentArrive[] =  '&nbsp;';
        $contentArrive[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Abgebende Schule / Kita'),
                self::getLayoutColumnValue($arriveCompany),
                self::getLayoutColumnLabel('Letzte Schulart'),
                self::getLayoutColumnValue($arriveType),
                self::getLayoutColumnLabel('Staatliche Stammschule'),
                self::getLayoutColumnValue($arriveStateCompany),
            )),
        )));
        $contentArrive[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Letzter Bildungsgang'),
                self::getLayoutColumnValue($arriveCourse),
                self::getLayoutColumnLabel('Datum'),
                self::getLayoutColumnValue($arriveDate),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($arriveRemark),
            )),
        )));

        $arrivePanel = new Panel(
            'Schüler - Aufnahme',
            $contentArrive,
            Panel::PANEL_TYPE_INFO
        );

        return $arrivePanel;
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getStudentTransferLeavePanel(TblStudent $tblStudent = null)
    {
        $leaveCompany = '';
        $leaveType = '';
        $leaveCourse = '';
        $leaveDate = '';
        $leaveRemark = '';

        if ($tblStudent) {
            $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
            $tblStudentTransferLeave = Student::useService()->getStudentTransferByType(
                $tblStudent, $TransferTypeLeave
            );

            if ($tblStudentTransferLeave) {
                $leaveCompany = ($tblCompany = $tblStudentTransferLeave->getServiceTblCompany())
                    ? $tblCompany->getDisplayName() : '';
                $leaveType = ($tblType = $tblStudentTransferLeave->getServiceTblType())
                    ? $tblType->getName() : '';
                $leaveCourse = ($tblCourse = $tblStudentTransferLeave->getServiceTblCourse())
                    ? $tblCourse->getName() : '';
                $leaveDate = $tblStudentTransferLeave->getTransferDate();
                $leaveRemark = $tblStudentTransferLeave->getRemark();
            }
        }

        $contentLeave[] =  '&nbsp;';
        $contentLeave[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Aufnehmende Schule'),
                self::getLayoutColumnValue($leaveCompany),
                self::getLayoutColumnLabel('Letzte Schulart'),
                self::getLayoutColumnValue($leaveType, 6),
            )),
        )));
        $contentLeave[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Letzter Bildungsgang'),
                self::getLayoutColumnValue($leaveCourse),
                self::getLayoutColumnLabel('Datum'),
                self::getLayoutColumnValue($leaveDate),
                self::getLayoutColumnLabel('Bemerkungen'),
                self::getLayoutColumnValue($leaveRemark),
            )),
        )));

        $leavePanel = new Panel(
            'Schüler - Abgabe',
            $contentLeave,
            Panel::PANEL_TYPE_INFO
        );

        return $leavePanel;
    }

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
//    private static function getStudentTransferProcessPanel(TblStudent $tblStudent = null)
//    {
//        $processCompany = '';
//        $processCourse = '';
//        $processRemark = '';
//
//        if ($tblStudent) {
//            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
//            $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
//                $tblStudent, $TransferTypeProcess
//            );
//
//            if ($tblStudentTransferProcess) {
//                $processCompany = ($tblCompany = $tblStudentTransferProcess->getServiceTblCompany())
//                    ? $tblCompany->getDisplayName() : '';
//                $processCourse = ($tblCourse = $tblStudentTransferProcess->getServiceTblCourse())
//                    ? $tblCourse->getName() : '';
//                $processRemark = $tblStudentTransferProcess->getRemark();
//            }
//        }
//
//        $contentProcess[] =  '&nbsp;';
//        $contentProcess[] =  new Layout(new LayoutGroup(array(
//            new LayoutRow(array(
//                self::getLayoutColumnLabel('Aktuelle Schule'),
//                self::getLayoutColumnValue($processCompany),
//                self::getLayoutColumnLabel('Aktueller Bildungsgang'),
//                self::getLayoutColumnValue($processCourse),
//                self::getLayoutColumnLabel('Bemerkungen'),
//                self::getLayoutColumnValue($processRemark),
//            )),
//        )));
//
//        $processPanel = new Panel(
//            'Schulverlauf',
//            $contentProcess,
//            Panel::PANEL_TYPE_INFO
//        );
//
//        return $processPanel;
//    }
    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentTransferContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {

                $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
                $tblStudentTransferEnrollment = Student::useService()->getStudentTransferByType(
                    $tblStudent, $TransferTypeEnrollment
                );
                if ($tblStudentTransferEnrollment) {
                    $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['School'] = (
                    $tblStudentTransferEnrollment->getServiceTblCompany()
                        ? $tblStudentTransferEnrollment->getServiceTblCompany()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Type'] = (
                    $tblStudentTransferEnrollment->getServiceTblType()
                        ? $tblStudentTransferEnrollment->getServiceTblType()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Course'] = (
                    $tblStudentTransferEnrollment->getServiceTblCourse()
                        ? $tblStudentTransferEnrollment->getServiceTblCourse()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Date'] = $tblStudentTransferEnrollment->getTransferDate();
                    $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['Remark'] = $tblStudentTransferEnrollment->getRemark();
                    $Global->POST['Meta']['Transfer'][$TransferTypeEnrollment->getId()]['StudentSchoolEnrollmentType']
                        = $tblStudentTransferEnrollment->getTblStudentSchoolEnrollmentType()
                        ? $tblStudentTransferEnrollment->getTblStudentSchoolEnrollmentType()->getId()
                        : 0;
                }

                $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
                $tblStudentTransferArrive = Student::useService()->getStudentTransferByType(
                    $tblStudent, $TransferTypeArrive
                );
                if ($tblStudentTransferArrive) {
                    $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['School'] = (
                    $tblStudentTransferArrive->getServiceTblCompany()
                        ? $tblStudentTransferArrive->getServiceTblCompany()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['StateSchool'] = (
                    $tblStudentTransferArrive->getServiceTblStateCompany()
                        ? $tblStudentTransferArrive->getServiceTblStateCompany()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Type'] = (
                    $tblStudentTransferArrive->getServiceTblType()
                        ? $tblStudentTransferArrive->getServiceTblType()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Course'] = (
                    $tblStudentTransferArrive->getServiceTblCourse()
                        ? $tblStudentTransferArrive->getServiceTblCourse()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Date'] = $tblStudentTransferArrive->getTransferDate();
                    $Global->POST['Meta']['Transfer'][$TransferTypeArrive->getId()]['Remark'] = $tblStudentTransferArrive->getRemark();
                }

                $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
                $tblStudentTransferLeave = Student::useService()->getStudentTransferByType(
                    $tblStudent, $TransferTypeLeave
                );
                if ($tblStudentTransferLeave) {
                    $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['School'] = (
                    $tblStudentTransferLeave->getServiceTblCompany()
                        ? $tblStudentTransferLeave->getServiceTblCompany()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Type'] = (
                    $tblStudentTransferLeave->getServiceTblType()
                        ? $tblStudentTransferLeave->getServiceTblType()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Course'] = (
                    $tblStudentTransferLeave->getServiceTblCourse()
                        ? $tblStudentTransferLeave->getServiceTblCourse()->getId()
                        : 0
                    );
                    $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Date'] = $tblStudentTransferLeave->getTransferDate();
                    $Global->POST['Meta']['Transfer'][$TransferTypeLeave->getId()]['Remark'] = $tblStudentTransferLeave->getRemark();
                }

//                $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
//                $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
//                    $tblStudent, $TransferTypeProcess
//                );
//                if ($tblStudentTransferProcess) {
//                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['School'] = (
//                    $tblStudentTransferProcess->getServiceTblCompany()
//                        ? $tblStudentTransferProcess->getServiceTblCompany()->getId()
//                        : 0
//                    );
//                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Type'] = (
//                    $tblStudentTransferProcess->getServiceTblType()
//                        ? $tblStudentTransferProcess->getServiceTblType()->getId()
//                        : 0
//                    );
//                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Course'] = (
//                    $tblStudentTransferProcess->getServiceTblCourse()
//                        ? $tblStudentTransferProcess->getServiceTblCourse()->getId()
//                        : 0
//                    );
//                    $Global->POST['Meta']['Transfer'][$TransferTypeProcess->getId()]['Remark'] = $tblStudentTransferProcess->getRemark();
//                }

                $Global->savePost();
            }
        }

        return $this->getEditStudentTransferTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentTransferForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentTransferTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Tag() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentTransferForm(TblPerson $tblPerson = null)
    {

        FrontendStudent::setYearAndDivisionForMassReplace($tblPerson, $Year, $Division);

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        $tblCompanyAllOwn = array();

        $tblCompanyAllSchoolNursery = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('NURSERY')
        );
        if ($tblCompanyAllSchoolNursery && $tblCompanyAllSchool) {
            $tblCompanyAllSchoolNursery = array_merge($tblCompanyAllSchool, $tblCompanyAllSchoolNursery);
        } else {
            $tblCompanyAllSchoolNursery = $tblCompanyAllSchool;
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();

        $tblSchoolCourseAll = Course::useService()->getCourseAll();
        if ($tblSchoolCourseAll) {
            array_push($tblSchoolCourseAll, new TblCourse());
        } else {
            $tblSchoolCourseAll = array(new TblCourse());
        }

        $tblStudentSchoolEnrollmentTypeAll = Student::useService()->getStudentSchoolEnrollmentTypeAll();

        $tblStudentTransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
        $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
        $tblStudentTransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
//        $tblStudentTransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');

        // Normaler Inhalt
        $useCompanyAllSchoolEnrollment = $tblCompanyAllSchool;
        $useCompanyAllSchoolArrive = $tblCompanyAllSchoolNursery;
        $useCompanyAllSchoolLeave = $tblCompanyAllSchool;
        $tblSchoolList = School::useService()->getSchoolAll();
        if ($tblSchoolList) {
            foreach ($tblSchoolList as $tblSchool) {
                if ($tblSchool->getServiceTblCompany()) {
                    $tblCompanyAllOwn[] = $tblSchool->getServiceTblCompany();
                }
            }
        }
//        if (empty($tblCompanyAllOwn)) {
//            $useCompanyAllSchoolProcess = $tblCompanyAllSchool;
//        } else {
//            $useCompanyAllSchoolProcess = $tblCompanyAllOwn;
//        }


        // add selected Company if missing in list
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {

            // Erweiterung der SelectBox wenn Daten vorhanden aber nicht enthalten sind
            // Enrollment
            $tblStudentTransferTypeEnrollmentEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeEnrollment);
            if ($tblStudentTransferTypeEnrollmentEntity && ($TransferCompanyEnrollment = $tblStudentTransferTypeEnrollmentEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyEnrollment->getId(), $useCompanyAllSchoolEnrollment)) {
                    $TransferCompanyEnrollmentList = array($TransferCompanyEnrollment->getId() => $TransferCompanyEnrollment);
                    $useCompanyAllSchoolEnrollment = array_merge($useCompanyAllSchoolEnrollment,
                        $TransferCompanyEnrollmentList);
                }
            }
            // Arrive
            $tblStudentTransferTypeArriveEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeArrive);
            if ($tblStudentTransferTypeArriveEntity && ($TransferCompanyArrive = $tblStudentTransferTypeArriveEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyArrive->getId(), $useCompanyAllSchoolArrive)) {
                    $TransferCompanyArriveList = array($TransferCompanyArrive->getId() => $TransferCompanyArrive);
                    $useCompanyAllSchoolArrive = array_merge($useCompanyAllSchoolArrive, $TransferCompanyArriveList);
                }
            }
            // Leave
            $tblStudentTransferTypeLeaveEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeLeave);
            if ($tblStudentTransferTypeLeaveEntity && ($TransferCompanyLeave = $tblStudentTransferTypeLeaveEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyLeave->getId(), $useCompanyAllSchoolLeave)) {
                    $TransferCompanyLeaveList = array($TransferCompanyLeave->getId() => $TransferCompanyLeave);
                    $useCompanyAllSchoolLeave = array_merge($useCompanyAllSchoolLeave, $TransferCompanyLeaveList);
                }
            }
            // Process
//            $tblStudentTransferTypeProcessEntity = Student::useService()->getStudentTransferByType($tblStudent,
//                $tblStudentTransferTypeProcess);
//            if ($tblStudentTransferTypeProcessEntity && ($TransferCompanyProcess = $tblStudentTransferTypeProcessEntity->getServiceTblCompany())) {
//                if (!array_key_exists($TransferCompanyProcess->getId(), $useCompanyAllSchoolProcess)) {
//                    $TransferCompanyProcessList = array($TransferCompanyProcess->getId() => $TransferCompanyProcess);
//                    $useCompanyAllSchoolProcess = array_merge($useCompanyAllSchoolProcess, $TransferCompanyProcessList);
//                }
//            }
        }

        $NodeEnrollment = 'Schülertransfer - Ersteinschulung';
        $NodeArrive = 'Schülertransfer - Schüler Aufnahme';
        $NodeLeave = 'Schülertransfer - Schüler Abgabe';
//        $NodeProcess = 'Schülertransfer - Aktueller Schulverlauf';

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Ersteinschulung', array(
                            ApiMassReplace::receiverField((
                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][School]',
                                'Schule', array(
                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolEnrollment
                                ))
                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_SCHOOL,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeEnrollment,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Type]',
                                'Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_SCHOOL_TYPE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeEnrollment,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][StudentSchoolEnrollmentType]',
                                'Einschulungsart', array(
                                    '{{ Name }}' => $tblStudentSchoolEnrollmentTypeAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_TYPE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                            => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeEnrollment,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Course]',
                                'Bildungsgang', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_COURSE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeEnrollment,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                            )),
                            ApiMassReplace::receiverField((
                            $Field = new DatePicker('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][Date]',
                                'Datum', 'Datum', new Calendar())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeEnrollment)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ENROLLMENT_TRANSFER_DATE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                            => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeEnrollment,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                            )),

//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][School]',
//                            'Schule', array(
//                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Type]',
//                            'Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Course]',
//                            'Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                            ), new Education()),
//                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Date]',
//                            'Datum', 'Datum', new Calendar()),
                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Remark]',
                                'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Schüler - Aufnahme', array(
                            ApiMassReplace::receiverField((
                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][School]',
                                'Abgebende Schule / Kita', array(
                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolArrive
                                ))
                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                            .ApiMassReplace::receiverModal($Field, $NodeArrive)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_SCHOOL,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeArrive,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                            )),
                            ApiMassReplace::receiverField((
                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][StateSchool]',
                                'Staatliche Stammschule', array(
                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolArrive
                                ))
                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                            .ApiMassReplace::receiverModal($Field, $NodeArrive)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_STATE_SCHOOL,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeArrive,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                            )),
                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Type]',
                                'Letzte Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeArrive)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_SCHOOL_TYPE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeArrive,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Course]',
                                'Letzter Bildungsgang', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeArrive)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_COURSE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeArrive,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                            )),
                            ApiMassReplace::receiverField((
                            $Field = new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Date]',
                                'Datum',
                                'Datum', new Calendar())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeArrive)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_TRANSFER_DATE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeArrive,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
                            )),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][School]',
//                            'Abgebende Schule / Kita', array(
//                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchoolNursery
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Type]',
//                            'Letzte Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Course]',
//                            'Letzter Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                            ), new Education()),
//                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Date]',
//                            'Datum',
//                            'Datum', new Calendar()),
                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Remark]',
                                'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Schüler - Abgabe', array(
                            ApiMassReplace::receiverField((
                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][School]',
                                'Aufnehmende Schule', array(
                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolLeave
                                ))
                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
                            .ApiMassReplace::receiverModal($Field, $NodeLeave)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_SCHOOL,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeLeave,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Type]',
                                'Letzte Schulart', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeLeave)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_SCHOOL_TYPE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeLeave,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                            )),

                            ApiMassReplace::receiverField((
                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Course]',
                                'Letzter Bildungsgang', array(
                                    '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                                ), new Education())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeLeave)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_COURSE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeLeave,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                            )),
                            ApiMassReplace::receiverField((
                            $Field = new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Date]',
                                'Datum',
                                'Datum', new Calendar())
                            ))
                            .ApiMassReplace::receiverModal($Field, $NodeLeave)
                            .new PullRight((new Link('Massen-Änderung',
                                ApiMassReplace::getEndpoint(), null, array(
                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_LEAVE_TRANSFER_DATE,
                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
                                    'Id'                                                      => $tblPerson->getId(),
                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
                                    'Node'                                                          => $NodeLeave,
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                            )),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][School]',
//                            'Aufnehmende Schule', array(
//                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Type]',
//                            'Letzte Schulart', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
//                            ), new Education()),
//                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Course]',
//                            'Letzter Bildungsgang', array(
//                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                            ), new Education()),
//                        new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Date]',
//                            'Datum',
//                            'Datum', new Calendar()),
                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Remark]',
                                'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                )),
//                new FormRow(array(
//                    new FormColumn(array(
//                        new Panel('Schulverlauf', array(
//                            ApiMassReplace::receiverField((
//                            $Field = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][School]',
//                                'Aktuelle Schule', array(
//                                    '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolProcess
//                                ))
//                            )->configureLibrary(SelectBox::LIBRARY_SELECT2)))
//                            .ApiMassReplace::receiverModal($Field, $NodeProcess)
//                            .new PullRight((new Link('Massen-Änderung',
//                                ApiMassReplace::getEndpoint(), null, array(
//                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
//                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_CURRENT_SCHOOL,
//                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
//                                    'Id'                                                      => $tblPerson->getId(),
//                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
//                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
//                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
//                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
//                                    'Node'                                                          => $NodeProcess,
//                                )))->ajaxPipelineOnClick(
//                                ApiMassReplace::pipelineOpen($Field, $NodeProcess)
//                            )),
//
//                            ApiMassReplace::receiverField((
//                            $Field = new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Course]',
//                                'Aktueller Bildungsgang', array(
//                                    '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
//                                ), new Education())
//                            ))
//                            .ApiMassReplace::receiverModal($Field, $NodeProcess)
//                            .new PullRight((new Link('Massen-Änderung',
//                                ApiMassReplace::getEndpoint(), null, array(
//                                    ApiMassReplace::SERVICE_CLASS                                   => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
//                                    ApiMassReplace::SERVICE_METHOD                                  => MassReplaceTransfer::METHOD_REPLACE_CURRENT_COURSE,
//                                    ApiMassReplace::USE_FILTER                                      => StudentFilter::STUDENT_FILTER,
//                                    'Id'                                                      => $tblPerson->getId(),
//                                    'Year['.ViewYear::TBL_YEAR_ID.']'                               => $Year[ViewYear::TBL_YEAR_ID],
//                                    'Division['.ViewDivisionStudent::TBL_LEVEL_ID.']'               => $Division[ViewDivisionStudent::TBL_LEVEL_ID],
//                                    'Division['.ViewDivisionStudent::TBL_DIVISION_NAME.']'          => $Division[ViewDivisionStudent::TBL_DIVISION_NAME],
//                                    'Division['.ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE.']' => $Division[ViewDivisionStudent::TBL_LEVEL_SERVICE_TBL_TYPE],
//                                    'Node'                                                          => $NodeProcess,
//                                )))->ajaxPipelineOnClick(
//                                ApiMassReplace::pipelineOpen($Field, $NodeProcess)
//                            )),
////                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][School]',
////                            'Aktuelle Schule', array(
////                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll,
////                            ), new Education()),
////                        // removed SchoolType
////                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Type]',
////                            'Aktuelle Schulart', array(
////                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll,
////                            ), new Education()),
////                        new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Course]',
////                            'Aktueller Bildungsgang', array(
////                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll,
////                            ), new Education()),
//                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeProcess->getId().'][Remark]',
//                                'Bemerkungen', 'Bemerkungen', new Pencil()),
//                        ), Panel::PANEL_TYPE_INFO),
//                    ), 6),
//                    new FormColumn(array(
//                        new Panel('Besuchte Schulklassen',
//                            $VisitedDivisions,
//                            Panel::PANEL_TYPE_DEFAULT,
//                            new Warning(
//                                'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
//                            )
//                        ),
//                    ), 6),
//                    new FormColumn(array(
//                        new Panel('Aktuelle Schuljahrwiederholungen',
//                            $RepeatedLevels,
//                            Panel::PANEL_TYPE_DEFAULT,
//                            new Warning(
//                                'Vom System erkannte Schuljahr&shy;wiederholungen.'
//                                .'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
//                            )
//                        ),
//                    ), 3),
//                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentTransferContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentTransferContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}