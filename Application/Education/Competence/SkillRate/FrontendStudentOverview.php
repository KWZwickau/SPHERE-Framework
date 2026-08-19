<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Competence\ApiOnlineSkillRate;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\ParentStudentAccess\OnlineCompetence\OnlineCompetence;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;

class FrontendStudentOverview extends FrontendStudent
{
    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $PersonId
     * @param $BackRoute
     * @param null $SelectedYearId
     *
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendStudentOverview($DivisionCourseId, $SubjectId, $PersonId, $BackRoute, $SelectedYearId = null): Stage
    {
        $stage = new Stage();

        $stage->setContent(
            ApiPersonPicture::receiverModal()
            . ApiSupportReadOnly::receiverOverViewModal()
            . $this->loadViewStudentOverview($DivisionCourseId, $SubjectId, $PersonId, $BackRoute, $SelectedYearId)
        );

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $PersonId
     * @param $BackRoute
     * @param null $SelectedYearId
     *
     * @return string
     */
    public function loadViewStudentOverview($DivisionCourseId, $SubjectId, $PersonId, $BackRoute, $SelectedYearId = null): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            return new Danger('Schuljahr wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }

        $pictureHeight = '159px';
        if (($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))) {
            $PersonPicture = (new Link($tblPersonPicture->getPicture($pictureHeight, '10px'), $tblPerson->getId()))
                ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId()));
        } else {
            $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWIcon.png');
            $PersonPicture = '<img src="' . $File->getLocation() . '" style="height: ' . $pictureHeight . '; border-radius: 10px; opacity: 0.2">';
        }

        // Inklusion
        $support = '';
        if (Student::useService()->getIsSupportByPerson($tblPerson)) {
            $support = (new Standard('Inklusion', ApiSupportReadOnly::getEndpoint(), new Tag(), [], 'Inklusion des Schülers anzeigen'))
                ->ajaxPipelineOnClick(ApiSupportReadOnly::pipelineOpenOverViewModal($tblPerson->getId()));
        }

        return
            new Title(
                new Standard("Zurück", $BackRoute, new ChevronLeft(),
                    ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'PersonId' => $PersonId, 'SelectedYearId' => $SelectedYearId],
                    'Zurück zur Schüleransicht')
                . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                . new Muted(new Small(" Schülerübersicht "))
                . new PullRight($support)
            )
//            . (new Container('&nbsp;'))->setStyle(['height: 8px;'])
            . new Layout(new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            new Panel('Schüler', $tblPerson->getLastFirstNameWithCallNameUnderline(true), Panel::PANEL_TYPE_INFO)
                        )),
                        new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fach',
                                (new Form(new FormGroup(new FormRow(new FormColumn(
                                    (new SelectBox('Data[SubjectId]', '', array('{{ Name }}' => $this->getSubjectListForStudentOverview($tblPerson, $tblYear)),
                                        null, false, null))
                                        ->ajaxPipelineOnChange(ApiOnlineSkillRate::pipelineLoadSubjectContent($tblPerson->getId()))
                                )))))->disableSubmitAction(),
                                Panel::PANEL_TYPE_INFO
                            )
                        )),
                    ))), 10),
                    new LayoutColumn(new Center($PersonPicture), 2),
                ))
            ))
            . ApiOnlineSkillRate::receiverBlock(OnlineCompetence::useFrontend()->loadSubjectContent($tblPerson, null), 'SubjectContent');
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return TblSubject[]
     */
    public function getSubjectListForStudentOverview(TblPerson $tblPerson, TblYear $tblYear): array
    {
        $subjectList = [];
        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear([$tblPerson], $tblYear))) {
            $subjectList[] = new SelectBoxItem(-2, 'Alle Fächer und Fächerübergreifend');
            $subjectList[] = new SelectBoxItem(-1, 'Fächerübergreifend');
            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
            /** @var TblSubject $tblSubject */
            foreach ($tblSubjectList as $tblSubject) {
                $subjectList[] = new SelectBoxItem($tblSubject->getId(), $tblSubject->getName());
            }
        }

        return $subjectList;
    }
}