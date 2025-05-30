<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.12.2018
 * Time: 14:16
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
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
use SPHERE\Common\Frontend\Icon\Repository\SizeHorizontal;
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
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentTransferContent($PersonId = null, $AllowEdit = 1)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $tblStudent = $tblPerson->getStudent();

            $enrollmentPanel = self::getStudentTransferEnrollmentPanel($tblStudent ? $tblStudent : null);
            $arrivePanel = self::getStudentTransferArrivePanel($tblStudent ? $tblStudent : null);
            $leavePanel = self::getStudentTransferLeavePanel($tblStudent ? $tblStudent : null);

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($enrollmentPanel, 4),
                    new LayoutColumn($arrivePanel, 4),
                    new LayoutColumn($leavePanel, 4),
                )),
            )));

            $editLink = '';
            if($AllowEdit == 1){
                $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentTransferContent($PersonId));
            }
            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                new SizeHorizontal()
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

//        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
//            new LayoutRow(array(
//                self::getLayoutColumnLabel('Schule'),
//                self::getLayoutColumnValue($enrollmentCompany),
//                self::getLayoutColumnLabel('Schulart'),
//                self::getLayoutColumnValue($enrollmentType),
//                self::getLayoutColumnLabel('Einschulungsart'),
//                self::getLayoutColumnValue($enrollmentTransferType),
//            )),
//        )));
//        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
//            new LayoutRow(array(
//                self::getLayoutColumnLabel('Bildungsgang'),
//                self::getLayoutColumnValue($enrollmentCourse),
//                self::getLayoutColumnLabel('Datum'),
//                self::getLayoutColumnValue($enrollmentDate),
//                self::getLayoutColumnLabel('Bemerkungen'),
//                self::getLayoutColumnValue($enrollmentRemark),
//            )),
//        )));
        $contentEnrollment[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Schule', 6),
                self::getLayoutColumnValue($enrollmentCompany, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Schulart', 6),
                self::getLayoutColumnValue($enrollmentType, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Einschulungsart', 6),
                self::getLayoutColumnValue($enrollmentTransferType, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Bildungsgang', 6),
                self::getLayoutColumnValue($enrollmentCourse, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Datum', 6),
                self::getLayoutColumnValue($enrollmentDate, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Bemerkungen', 6),
                self::getLayoutColumnValue($enrollmentRemark, 6),
            )),
        )));

        $enrollmentPanel = FrontendReadOnly::getSubContent(
            'Ersteinschulung',
            $contentEnrollment
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

        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Abgebende Schule / Kita', 6),
            self::getLayoutColumnValue($arriveCompany, 6),
        ));
        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Staatliche Stammschule', 6),
            self::getLayoutColumnValue($arriveStateCompany, 6),
        ));

//        // wird im Block für berufsbildende Schulen gepflegt
//        if (!School::useService()->hasConsumerTechnicalSchool()) {
        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Letzte Schulart', 6),
            self::getLayoutColumnValue($arriveType, 6),
        ));
        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Letzter Bildungsgang', 6),
            self::getLayoutColumnValue($arriveCourse, 6),
        ));
//        }

        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Datum', 6),
            self::getLayoutColumnValue($arriveDate, 6),
        ));
        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Bemerkungen', 6),
            self::getLayoutColumnValue($arriveRemark, 6),
        ));

        $contentArrive[] =  new Layout(new LayoutGroup($rows));

        return FrontendReadOnly::getSubContent(
            'Schüler - Aufnahme',
            $contentArrive
        );
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

//        $contentLeave[] =  new Layout(new LayoutGroup(array(
//            new LayoutRow(array(
//                self::getLayoutColumnLabel('Aufnehmende Schule'),
//                self::getLayoutColumnValue($leaveCompany),
//                self::getLayoutColumnLabel('Letzte Schulart'),
//                self::getLayoutColumnValue($leaveType, 6),
//            )),
//        )));
//        $contentLeave[] =  new Layout(new LayoutGroup(array(
//            new LayoutRow(array(
//                self::getLayoutColumnLabel('Letzter Bildungsgang'),
//                self::getLayoutColumnValue($leaveCourse),
//                self::getLayoutColumnLabel('Datum'),
//                self::getLayoutColumnValue($leaveDate),
//                self::getLayoutColumnLabel('Bemerkungen'),
//                self::getLayoutColumnValue($leaveRemark),
//            )),
//        )));
        $contentLeave[] =  new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                self::getLayoutColumnLabel('Aufnehmende Schule', 6),
                self::getLayoutColumnValue($leaveCompany, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Letzte Schulart', 6),
                self::getLayoutColumnValue($leaveType, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Letzter Bildungsgang', 6),
                self::getLayoutColumnValue($leaveCourse, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Datum', 6),
                self::getLayoutColumnValue($leaveDate, 6),
            )),
            new LayoutRow(array(
                self::getLayoutColumnLabel('Bemerkungen', 6),
                self::getLayoutColumnValue($leaveRemark, 6),
            )),
        )));

        $leavePanel = FrontendReadOnly::getSubContent(
            'Schüler - Abgabe',
            $contentLeave
        );

        return $leavePanel;
    }

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
        return new Title(new SizeHorizontal() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentTransferForm(TblPerson $tblPerson = null)
    {
        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
//        $tblCompanyAllOwn = array();

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

        // Normaler Inhalt
        $useCompanyAllSchoolEnrollment = $tblCompanyAllSchool;
        $useCompanyAllSchoolArrive = $tblCompanyAllSchoolNursery;
        $useCompanyAllSchoolLeave = $tblCompanyAllSchool;
//        $tblSchoolList = School::useService()->getSchoolAll();
//        if ($tblSchoolList) {
//            foreach ($tblSchoolList as $tblSchool) {
//                if ($tblSchool->getServiceTblCompany()) {
//                    $tblCompanyAllOwn[] = $tblSchool->getServiceTblCompany();
//                }
//            }
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
        }

        $NodeEnrollment = 'Schülertransfer - Ersteinschulung';
        $NodeArrive = 'Schülertransfer - Schüler Aufnahme';
        $NodeLeave = 'Schülertransfer - Schüler Abgabe';

        $arrayArrive[] =
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
                    'Id'                                                      => $tblPerson->getId(),
                )))->ajaxPipelineOnClick(
                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
            ));
        $arrayArrive[] =
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
                    'Id'                                                      => $tblPerson->getId(),
                )))->ajaxPipelineOnClick(
                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
            ));

//        // wird im Block für berufsbildende Schulen gepflegt
//        if (!School::useService()->hasConsumerTechnicalSchool()) {
        $arrayArrive[] =
            ApiMassReplace::receiverField((
            $Field = new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Type]',
                'Letzte Schulart', array(
                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                ), new Education())
            ))
            . ApiMassReplace::receiverModal($Field, $NodeArrive)
            . new PullRight((new Link('Massen-Änderung',
                ApiMassReplace::getEndpoint(), null, array(
                    ApiMassReplace::SERVICE_CLASS => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                    ApiMassReplace::SERVICE_METHOD => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_SCHOOL_TYPE,
                    'Id' => $tblPerson->getId(),
                )))->ajaxPipelineOnClick(
                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
            ));
        $arrayArrive[] =
            ApiMassReplace::receiverField((
            $Field = new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Course]',
                'Letzter Bildungsgang', array(
                    '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                ), new Education())
            ))
            . ApiMassReplace::receiverModal($Field, $NodeArrive)
            . new PullRight((new Link('Massen-Änderung',
                ApiMassReplace::getEndpoint(), null, array(
                    ApiMassReplace::SERVICE_CLASS => MassReplaceTransfer::CLASS_MASS_REPLACE_TRANSFER,
                    ApiMassReplace::SERVICE_METHOD => MassReplaceTransfer::METHOD_REPLACE_ARRIVE_COURSE,
                    'Id' => $tblPerson->getId(),
                )))->ajaxPipelineOnClick(
                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
            ));
//        }

        $arrayArrive[] =
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
                    'Id'                                                            => $tblPerson->getId(),
                )))->ajaxPipelineOnClick(
                ApiMassReplace::pipelineOpen($Field, $NodeArrive)
            ));
        $arrayArrive[] =
            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Remark]',
                'Bemerkungen', 'Bemerkungen', new Pencil());

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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeEnrollment)
                            )),
                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeEnrollment->getId().'][Remark]',
                                'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Schüler - Aufnahme', $arrayArrive, Panel::PANEL_TYPE_INFO),
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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
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
                                    'Id'                                                            => $tblPerson->getId(),
                                )))->ajaxPipelineOnClick(
                                ApiMassReplace::pipelineOpen($Field, $NodeLeave)
                            )),
                            new TextArea('Meta[Transfer]['.$tblStudentTransferTypeLeave->getId().'][Remark]',
                                'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                )),
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