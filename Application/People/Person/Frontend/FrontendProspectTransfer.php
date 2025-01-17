<?php
namespace SPHERE\Application\People\Person\Frontend;

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
class FrontendProspectTransfer extends FrontendReadOnly
{
    const TITLE = 'Interessenten - Transfer';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getProspectTransferContent($PersonId = null, $AllowEdit = 1)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return '';
        }
        $tblStudent = $tblPerson->getStudent();
        $arrivePanel = self::getProspectTransferArrivePanel($tblStudent ? $tblStudent : null);

        $content = new Layout(new LayoutGroup(array(new LayoutRow(array(
//                new LayoutColumn($enrollmentPanel, 4),
                new LayoutColumn($arrivePanel, 4),
//                new LayoutColumn($leavePanel, 4),
        )))));

        $editLink = '';
        if($AllowEdit == 1){
            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditProspectTransferContent($PersonId));
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

    /**
     * @param TblStudent|null $tblStudent
     *
     * @return Panel
     */
    private static function getProspectTransferArrivePanel(TblStudent $tblStudent = null)
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

        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Letzte Schulart', 6),
            self::getLayoutColumnValue($arriveType, 6),
        ));
        $rows[] = new LayoutRow(array(
            self::getLayoutColumnLabel('Letzter Bildungsgang', 6),
            self::getLayoutColumnValue($arriveCourse, 6),
        ));

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
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditProspectTransferContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
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
                $Global->savePost();
            }
        }

        return $this->getEditProspectTransferTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditProspectTransferForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditProspectTransferTitle(TblPerson $tblPerson = null)
    {
        return new Title(new SizeHorizontal() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditProspectTransferForm(TblPerson $tblPerson = null)
    {
        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );

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
        // Normaler Inhalt
        $useCompanyAllSchoolArrive = $tblCompanyAllSchoolNursery;

        $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');

        // add selected Company if missing in list
        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
        if ($tblStudent) {

            // Erweiterung der SelectBox wenn Daten vorhanden aber nicht enthalten sind
            // Arrive
            $tblStudentTransferTypeArriveEntity = Student::useService()->getStudentTransferByType($tblStudent,
                $tblStudentTransferTypeArrive);
            if ($tblStudentTransferTypeArriveEntity && ($TransferCompanyArrive = $tblStudentTransferTypeArriveEntity->getServiceTblCompany())) {
                if (!array_key_exists($TransferCompanyArrive->getId(), $useCompanyAllSchoolArrive)) {
                    $TransferCompanyArriveList = array($TransferCompanyArrive->getId() => $TransferCompanyArrive);
                    $useCompanyAllSchoolArrive = array_merge($useCompanyAllSchoolArrive, $TransferCompanyArriveList);
                }
            }
        }

        $arrayArrive[] = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][School]',
            'Abgebende Schule / Kita', array(
                '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolArrive
            ))
        )->configureLibrary(SelectBox::LIBRARY_SELECT2);
        $arrayArrive[] = (new SelectBox('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][StateSchool]',
            'Staatliche Stammschule', array(
                '{{ Name }} {{ ExtendedName }} {{ Description }}' => $useCompanyAllSchoolArrive
            ))
        )->configureLibrary(SelectBox::LIBRARY_SELECT2);
        $arrayArrive[] = new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Type]',
                'Letzte Schulart', array(
                    '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
            ), new Education());
        $arrayArrive[] = new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Course]',
            'Letzter Bildungsgang', array(
                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
            ), new Education());
        $arrayArrive[] = new DatePicker('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Date]',
            'Datum',
            'Datum', new Calendar());
        $arrayArrive[] = new TextArea('Meta[Transfer]['.$tblStudentTransferTypeArrive->getId().'][Remark]',
            'Bemerkungen', 'Bemerkungen', new Pencil());

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Schüler - Aufnahme', $arrayArrive, Panel::PANEL_TYPE_INFO),
                    ), 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveProspectTransferContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelProspectTransferContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}