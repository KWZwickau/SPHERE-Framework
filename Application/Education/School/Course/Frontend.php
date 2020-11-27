<?php
namespace SPHERE\Application\Education\School\Course;

use SPHERE\Application\Api\Education\School\ApiCourse;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\School\Course
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return TableData
     */
    public function loadTechnicalCourseTable()
    {
        $dataList = array();
        if (($tblTechnicalCourseAll = Course::useService()->getTechnicalCourseAll())) {
            foreach ($tblTechnicalCourseAll as $tblTechnicalCourse) {
                $dataList[] = array(
                    'Name' => $tblTechnicalCourse->getName(),
                    'GenderMaleName' => $tblTechnicalCourse->getGenderMaleName(),
                    'GenderFemaleName' => $tblTechnicalCourse->getGenderFemaleName(),
                    'Options' => (new Standard(
                        '',
                        ApiCourse::getEndpoint(),
                        new Edit(),
                        array(),
                        'Bearbeiten'
                    ))->ajaxPipelineOnClick(ApiCourse::pipelineOpenEditTechnicalCourseModal($tblTechnicalCourse->getId()))
                );
            }
        }

        $columns = array(
            'Name' => 'Name',
            'GenderMaleName' => 'Männlicher Name',
            'GenderFemaleName' => 'Weiblicher Name',
            'Options' => ' '
        );

        return new TableData(
            $dataList,
            null,
            $columns,
            array(
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                ),
                'order' => array(
                    array(0, 'asc')
                ),
            )
        );
    }

    /**
     * @param null $TechnicalCourseId
     * @param false $setPost
     *
     * @return Form
     */
    public function formTechnicalCourse($TechnicalCourseId = null, $setPost = false)
    {
        if ($TechnicalCourseId && ($tblTechnicalCourse = Course::useService()->getTechnicalCourseById($TechnicalCourseId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Name'] = $tblTechnicalCourse->getName();
                $Global->POST['Data']['GenderMaleName'] = $tblTechnicalCourse->getGenderMaleName();
                $Global->POST['Data']['GenderFemaleName'] = $tblTechnicalCourse->getGenderFemaleName();

                $Global->savePost();
            }
        }

        if ($TechnicalCourseId) {
            $saveButton = (new Primary('Speichern', ApiCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiCourse::pipelineEditTechnicalCourseSave($TechnicalCourseId));
        } else {
            $saveButton = (new Primary('Speichern', ApiCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiCourse::pipelineCreateTechnicalCourseSave());
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Name]', 'Ergotherapeut/in', 'Name')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[GenderMaleName]', 'Ergotherapeut', 'Männlicher Name')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[GenderFemaleName]', 'Ergotherapeutin', 'Weiblicher Name')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }
}
