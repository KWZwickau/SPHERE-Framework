<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;

class Frontend extends FrontendTeacherGroup
{
    /**
     * @return Stage
     */
    public function frontendGradeBook(): Stage
    {
        $stage = new Stage();

        $hasHeadmasterRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Headmaster');
        $hasTeacherRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Teacher');
        $hasAllReadonlyRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/AllReadOnly');

        if (($roleValue = Grade::useService()->getRole())) {
            if ($roleValue == "Headmaster") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsHeadmaster"] = 1;
                $global->savePost();
            }
            if ($roleValue == "AllReadonly") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsAllReadonly"] = 1;
                $global->savePost();
            }
        }

        $roleChange = "";
        if ($hasHeadmasterRole && $hasTeacherRole) {
            $roleChange =
                (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[IsHeadmaster]', new Bold('Schulleitung'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeRole()))
                )))))->disableSubmitAction();
        } elseif ($hasAllReadonlyRole && $hasTeacherRole) {
            $roleChange =
                (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[IsAllReadonly]', new Bold('Integrationsbeauftragter'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeRole()))
                )))))->disableSubmitAction();
        }

        if (($tblYear =Grade::useService()->getYear())) {
            $global = $this->getGlobal();
            $global->POST["Data"]["Year"] = $tblYear->getId();
            $global->savePost();
        }

        $stage->setContent(
            new Container("&nbsp;")
            . new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiGradeBook::receiverBlock($this->getHeader(self::VIEW_GRADE_BOOK_SELECT), 'Header')
                    , 8),
                    new LayoutColumn(
                        new PullRight(ApiGradeBook::receiverBlock("", "ChangeRole") . $roleChange)
                    , 2),
                    new LayoutColumn(array(
                        ApiGradeBook::receiverBlock("", "ChangeYear"),
                        (new Form(new FormGroup(new FormRow(new FormColumn(
                            (new SelectBox('Data[Year]', '', array("{{ DisplayName }}" => Term::useService()->getYearAll())))
                                ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeYear()))
                        )))))->disableSubmitAction()
                    ), 2)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiGradeBook::receiverBlock($this->loadViewGradeBookSelect(), 'Content')
                    )
                ))
            )))
        );

        return $stage;
    }

    /**
     * @param string $View
     *
     * @return string
     */
    public function getHeader(string $View): string
    {
        $role = Grade::useService()->getRole();

        $textGradeBook = $View == self::VIEW_GRADE_BOOK_SELECT || $View == self::VIEW_GRADE_BOOK_CONTENT
            ? new Info(new Edit() . new Bold(" Notenbuch"))
            : "Notenbuch";

        $textTeacherGroup = $View == self::VIEW_TEACHER_GROUP
            ? new Info(new Edit() . new Bold(" Lerngruppen"))
            : "Lerngruppen";

        return
            (new Standard($textGradeBook, ApiGradeBook::getEndpoint()))
                ->ajaxPipelineOnClick(array(
                    ApiGradeBook::pipelineLoadHeader(self::VIEW_GRADE_BOOK_SELECT),
                    ApiGradeBook::pipelineLoadViewGradeBookSelect()
                ))
            . ($role == "Teacher"
                ? (new Standard($textTeacherGroup, ApiTeacherGroup::getEndpoint()))
                    ->ajaxPipelineOnClick(array(
                        ApiGradeBook::pipelineLoadHeader(self::VIEW_TEACHER_GROUP),
                        ApiTeacherGroup::pipelineLoadViewTeacherGroups()
                    ))
                : "")
            ;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param null $Filtern
     *
     * @return string
     */
    public function loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filtern = null): string
    {
        $isReadonly = false;
        $textKurs = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
        ) {
            $textKurs = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
            $content = "";

            // prüfen bei Lehrer ob er auch die Lehraufträge für alle schüler noch hat
        } else {
            $content = new Danger("Kurse oder Fach nicht gefunden.", new Exclamation());
        }

        return new Title("Notenbuch" . new Muted(new Small(" für Kurs: ")) . $textKurs . new Muted(new Small(" im Fach: ")) . $textSubject) . $content;
    }
}