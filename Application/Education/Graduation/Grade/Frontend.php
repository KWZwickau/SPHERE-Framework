<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    public function frontendGradebook($YearId = null)
    {
        $stage = new Stage('Notenbuch');

        // todo auswahl schuljahr -> dann eventuell über $_GET
        $tblYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            $tblYear = current($tblYearList);
        }

        $stage->setContent(
            ApiTeacherGroup::receiverBlock($this->loadViewTeacherGroups(), 'Content')
        );

        return $stage;
    }

    /**
     * @return string
     */
    public function loadViewTeacherGroups(): string
    {
        $tblType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP);
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
           return
               (new Primary("{$tblType->getName()} hinzufügen", ApiTeacherGroup::getEndpoint(), new Plus()))
                   ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineLoadViewTeacherGroupEdit())
               // todo tabelle
               . "hallo {$tblPerson->getFullName()}";
        } else {
            return new Danger("Keine Person zum Benutzerkonto gefunden", new Exclamation());
        }
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadViewTeacherGroupEdit($DivisionCourseId): string
    {
        return $this->getTeacherGroupEdit($this->formTeacherGroup($DivisionCourseId, true), $DivisionCourseId);
    }

    public function getTeacherGroupEdit($form, $DivisionCourseId = null): string
    {
        $tblType = DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP);
        if ($DivisionCourseId) {
            $title = new Title(new Edit() . " {$tblType->getName()} bearbeiten");
        } else {
            $title = new Title(new Plus() . " {$tblType->getName()} hinzufügen");
        }

        return $title
            . new Well($form);
    }

    /**
     * @param null $DivisionCourseId
     * @param bool $setPost
     * @param null $Data
     *
     * @return Form
     */
    public function formTeacherGroup($DivisionCourseId = null, bool $setPost = false, $Data = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        if ($setPost && $tblDivisionCourse) {
            // todo
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblDivisionCourse->getName();
            $Global->POST['Data']['Description'] = $tblDivisionCourse->getDescription();
            $Global->POST['Data']['Subject'] = $tblDivisionCourse->getServiceTblSubject() ? $tblDivisionCourse->getServiceTblSubject()->getId() : 0;
            $Global->savePost();
        }

        $tblSubjectList = array();
        if ($tblDivisionCourse) {
            $tblYear = $tblDivisionCourse->getServiceTblYear();

        } else {
            $tblYear = Grade::useService()->getYear();
        }
        if ($tblYear && ($tblPerson = Account::useService()->getPersonByLogin())) {
            $tblSubjectList = DivisionCourse::useService()->getSubjectListByTeacherAndYear($tblPerson, $tblYear);
        }

        return (new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn($tblDivisionCourse
                    ? new Panel('Fach', $tblDivisionCourse->getSubjectName(), Panel::PANEL_TYPE_INFO)
                    : (new SelectBox('Data[Subject]', 'Fach', array('{{ DisplayName }}' => $tblSubjectList)))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiTeacherGroup::pipelineLoadTeacherGroupStudentSelect())
                )
            )),
            new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Name]', '', 'Name', new Pen()))->setRequired()
                , 6),
                new FormColumn(
                    new TextField('Data[Description]', '', 'Beschreibung', new Pen())
                , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    ApiTeacherGroup::receiverBlock($this->loadTeacherGroupStudentSelect(''), 'TeacherGroupStudentSelect')
                )
            )),
            new FormRow(array(
                new FormColumn(
                    (new Primary('Speichern', ApiTeacherGroup::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiTeacherGroup::pipelineSaveTeacherGroupEdit($DivisionCourseId))
                )
            ))
        ))))->disableSubmitAction();
    }

    /**
     * @param $SubjectId
     *
     * @return Warning|string
     */
    public function loadTeacherGroupStudentSelect($SubjectId)
    {
        if (($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            if (($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblYear = Grade::useService()->getYear())
                && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))
            ) {
                $size = 3;
                $columnList = array();
                $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
                /** @var TblTeacherLectureship $tblTeacherLectureship */
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())) {
                        // SekII-Kurse nicht mit anzeigen
                        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                            continue;
                        }

                        $contentPanel = array();
                        if (($tblStudentList = $tblDivisionCourse->getStudents())) {
                            foreach ($tblStudentList as $tblStudent) {
                                // todo anzeige wenn Person bereits in einer anderen Fachgruppe zu dieser Person ist
                                $contentPanel[] = new CheckBox("Data[Students][{$tblStudent->getId()}]", $tblStudent->getLastFirstNameWithCallNameUnderline(), 1);
                            }
                        }

                        $columnList[] = new LayoutColumn(new Panel($tblDivisionCourse->getDisplayName(), $contentPanel, Panel::PANEL_TYPE_INFO), $size);
                    }
                }
                return new Layout(new LayoutGroup(
                    Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                    new Title('Verfügbare Schüler')
                ));
            }
        } else {
            return new Warning("Bitte wählen Sie zunächst ein Fach aus.", new Exclamation());
        }
    }
}