<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;

class FrontendExamGrade extends FrontendBasic
{
    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $PrepareCertificateId
     *
     * @return string
     */
    public function loadViewExamGradeEditContent($DivisionCourseId, $SubjectId, $PrepareCertificateId, $Filter): string
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareCertificateId))
        ) {
            $header = $this->getExamGradeEditHeader($tblDivisionCourse, $tblSubject, $Filter);
            $content = $this->getExamGradeForm($tblDivisionCourse, $tblSubject, $tblPrepare, $Filter);
        } else {
            $header = '';
            $content = new Danger("Kurse oder Fach nicht gefunden.", new Exclamation());
        }

        return $header
            . $content;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param $Filter
     *
     * @return string
     */
    public function getExamGradeEditHeader(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, $Filter): string
    {
        $textCourse = new Bold($tblDivisionCourse->getDisplayName());
        $textSubject = new Bold($tblSubject->getDisplayName());

        return
            new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($tblDivisionCourse->getId(), $tblSubject->getId(), $Filter))
                . "&nbsp;&nbsp;&nbsp;Prüfungsnoten"
                . new Muted(new Small(" für Kurs: ")) . $textCourse
                . new Muted(new Small(" im Fach: ")) . $textSubject
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . ApiGradeBook::receiverModal();
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param TblPrepareCertificate $tblPrepare
     * @param $Filter
     *
     * @return Form
     */
    public function getExamGradeForm(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, TblPrepareCertificate $tblPrepare, $Filter): Form
    {
        $SchoolTypeShortName = '';
        if (($tblSchoolTypeList = DivisionCourse::useService()->getSchoolTypeListByDivisionCourse($tblDivisionCourse))) {
            /** @var TblType $tblSchoolType */
            $tblSchoolType = reset($tblSchoolTypeList);
            $SchoolTypeShortName = $tblSchoolType->getShortName();
        }

        list($studentTable, $columnTable, $Interactive, $buttonList) = Prepare::useFrontend()->getExamSubjectData($tblPrepare, $tblSubject, $SchoolTypeShortName);

        $tableData = new TableData($studentTable, null, $columnTable, $Interactive, true);

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $tableData
                    ),
                    new FormColumn(
                        (new Primary('Speichern', ApiGradeBook::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiGradeBook::pipelineSaveExamGradeEdit(
                                $tblDivisionCourse->getId(), $tblSubject->getId(), $tblPrepare->getId(), $Filter
                            ))
                    )
                )),
            )),
        );
    }
}