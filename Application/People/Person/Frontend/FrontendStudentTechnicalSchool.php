<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class FrontendStudentTechnicalSchool
 * 
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentTechnicalSchool extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Berufsbildende Schulen';

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public static function getStudentTechnicalSchoolContent($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            ) {
                $schoolDiploma = ($tblSchoolDiploma = $tblStudentTechnicalSchool->getServiceTblSchoolDiploma())
                    ? $tblSchoolDiploma->getName() : '';
                $technicalDiploma = ($tblTechnicalDiploma = $tblStudentTechnicalSchool->getServiceTblTechnicalDiploma())
                    ? $tblTechnicalDiploma->getName() : '';
                $technicalCourse = ($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())
                    ? $tblTechnicalCourse->getName() : '';
                $praxisLessons = $tblStudentTechnicalSchool->getPraxisLessons();
                $durationOfTraining = $tblStudentTechnicalSchool->getDurationOfTraining();
                $studentTenseOfLessons = ($tblStudentTenseOfLessons = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())
                    ? $tblStudentTenseOfLessons->getName() : '';
                $studentTrainingStatus = ($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())
                    ? $tblStudentTrainingStatus->getName() : '';
            } else {
                $schoolDiploma = '';
                $technicalDiploma = '';
                $technicalCourse = '';
                $praxisLessons = '';
                $durationOfTraining = '';
                $studentTenseOfLessons = '';
                $studentTrainingStatus = '';
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Bildung',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bildungsgang / Berufsbezeichnung / Ausbildung', 6),
                                    self::getLayoutColumnValue($technicalCourse, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Allgemeinbildender Abschluss', 6),
                                    self::getLayoutColumnValue($schoolDiploma, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Berufsbildender Abschluss', 6),
                                    self::getLayoutColumnValue($technicalDiploma, 6),
                                )),
                            )))
                        ),
                    ), 8),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            '',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Geleistete Praxisstunden', 8),
                                    self::getLayoutColumnValue($praxisLessons, 4),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Planmäßige Ausbildungsdauer', 8),
                                    self::getLayoutColumnValue($durationOfTraining, 4),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Zeitform des Unterrichts', 6),
                                    self::getLayoutColumnValue($studentTenseOfLessons, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Ausbildungsstatus', 6),
                                    self::getLayoutColumnValue($studentTrainingStatus, 6),
                                )),
                            )))
                        ),
                    ), 4),
                )),
            )));

            $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentTechnicalSchoolContent($PersonId));

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())),
                new Hospital()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentTechnicalSchoolContent($PersonId = null)
    {
        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))
                && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
            ) {

                $Global->POST['Meta']['TechnicalSchool']['serviceTblTechnicalCourse'] = ($tblTechnicalCourse = $tblStudentTechnicalSchool->getServiceTblTechnicalCourse())
                    ? $tblTechnicalCourse->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['serviceTblSchoolDiploma'] = ($tblSchoolDiploma = $tblStudentTechnicalSchool->getServiceTblSchoolDiploma())
                    ? $tblSchoolDiploma->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['serviceTblTechnicalDiploma'] = ($tblTechnicalDiploma = $tblStudentTechnicalSchool->getServiceTblTechnicalDiploma())
                    ? $tblTechnicalDiploma->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['PraxisLessons'] = $tblStudentTechnicalSchool->getPraxisLessons();
                $Global->POST['Meta']['TechnicalSchool']['DurationOfTraining'] = $tblStudentTechnicalSchool->getDurationOfTraining();
                $Global->POST['Meta']['TechnicalSchool']['tblStudentTenseOfLesson'] = ($tblStudentTenseOfLessons = $tblStudentTechnicalSchool->getTblStudentTenseOfLesson())
                    ? $tblStudentTenseOfLessons->getId() : 0;
                $Global->POST['Meta']['TechnicalSchool']['tblStudentTrainingStatus'] = ($tblStudentTrainingStatus = $tblStudentTechnicalSchool->getTblStudentTrainingStatus())
                    ? $tblStudentTrainingStatus->getId() : 0;

                $Global->savePost();
            }
        }

        return $this->getEditStudentTechnicalSchoolTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentTechnicalSchoolForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentTechnicalSchoolTitle(TblPerson $tblPerson = null)
    {
        return new Title(new Hospital() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentTechnicalSchoolForm(TblPerson $tblPerson = null)
    {
        $tblTechnicalCourseAll = Course::useService()->getTechnicalCourseAll();
        $tblSchoolDiplomaAll = Course::useService()->getSchoolDiplomaAll();
        $tblTechnicalDiplomaAll = Course::useService()->getTechnicalDiplomaAll();
        $tblStudentTenseOfLessonAll = Student::useService()->getStudentTenseOfLessonAll();
        $tblStudentTrainingStatusAll = Student::useService()->getStudentTrainingStatusAll();

        return (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Meta[TechnicalSchool][serviceTblTechnicalCourse]', 'Bildungsgang / Berufsbezeichnung / Ausbildung',
                                array('{{ Name }}' => $tblTechnicalCourseAll), null, true, null))
                                ->configureLibrary(SelectBox::LIBRARY_SELECT2),
                            (new SelectBox('Meta[TechnicalSchool][serviceTblSchoolDiploma]', 'Höchster Abschluss an einer allgemeinbildenden Schule',
                                array('{{ Name }}' => $tblSchoolDiplomaAll), null, true, null))
                                 ->configureLibrary(SelectBox::LIBRARY_SELECT2),
                            (new SelectBox('Meta[TechnicalSchool][serviceTblTechnicalDiploma]', 'Höchster Abschluss an einer berufsbildenden Schule',
                                array('{{ Name }}' => $tblTechnicalDiplomaAll), null, true, null))
                            ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        ), Panel::PANEL_TYPE_INFO)
                        , 8),
                    new FormColumn(
                        new Panel('&nbsp;', array(
                            new TextField('Meta[TechnicalSchool][PraxisLessons]', '100', 'Geleistete Praxisstunden'),
                            new TextField('Meta[TechnicalSchool][DurationOfTraining]', '36', 'Planmäßige Ausbildungsdauer in Monaten'),
                            (new SelectBox('Meta[TechnicalSchool][tblStudentTenseOfLesson]', 'Zeitform des Unterrichts',
                                array('{{ Name }}' => $tblStudentTenseOfLessonAll), null, true, null))
                                ->configureLibrary(SelectBox::LIBRARY_SELECT2),
                            (new SelectBox('Meta[TechnicalSchool][tblStudentTrainingStatus]', 'Ausbildungsstatus',
                                array('{{ Name }}' => $tblStudentTrainingStatusAll), null, true, null))
                                ->configureLibrary(SelectBox::LIBRARY_SELECT2)
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentTechnicalSchoolContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentTechnicalSchoolContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();
    }
}