<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourse;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    public function frontendDivisionCourse(): Stage
    {
        $stage = new Stage('Kurs', 'Übersicht');

        $Filter['Year'] = -1;
        $stage->setContent(
            ApiDivisionCourse::receiverModal()
            . new Panel(new Filter() . ' Filter', $this->formFilter(), Panel::PANEL_TYPE_INFO)
            . ApiDivisionCourse::receiverBlock($this->loadDivisionCourseTable($Filter), 'DivisionCourseContent')
        );

        return $stage;
    }

    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadDivisionCourseTable($Filter = null): string
    {
        $addLink = (new Primary('Kurs hinzufügen', ApiDivisionCourse::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenCreateDivisionCourseModal($Filter));

        $typeFilter = null;
        if (isset($Filter['Type']) && ($tblCourseTypeFilter = DivisionCourse::useService()->getDivisionCourseTypeById($Filter['Type']))) {
            $typeFilter = $tblCourseTypeFilter->getIdentifier();
        }

        $tblDivisionCourseList = array();
        // aktuelle Übersicht
        if (isset($Filter['Year']) && $Filter['Year'] == -1) {
            if (($tblYearList = Term::useService()->getYearByNow())) {
                foreach ($tblYearList as $tblYearItem) {
                    if (($tblDivisionCourseYearList = DivisionCourse::useService()->getDivisionCourseListBy($tblYearItem, $typeFilter))) {
                        $tblDivisionCourseList = array_merge($tblDivisionCourseYearList, $tblDivisionCourseList);
                    }
                }
            }
        // ausgewähltes Schuljahr
        } elseif (isset($Filter['Year']) && ($tblYear = Term::useService()->getYearById($Filter['Year']))) {
            $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, $typeFilter);
        } else {
        // alle Schuljahre
            $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy(null, $typeFilter);
        }

        if ($tblDivisionCourseList) {
            $dataList = array();
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $dataList[] = array(
                    'Year' => $tblDivisionCourse->getYearName(),
                    'Name' => $tblDivisionCourse->getName(),
                    'Description' => $tblDivisionCourse->getDescription(),
                    'Type' => $tblDivisionCourse->getTypeName(),
                    'Option' =>
                        (new Standard('', ApiDivisionCourse::getEndpoint(), new Pencil(), array(), 'Name des Kurses bearbeiten'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenEditDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                        . (new Standard('', ApiDivisionCourse::getEndpoint(), new Remove(), array(), 'Kurs löschen'))
                            ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenDeleteDivisionCourseModal($tblDivisionCourse->getId(), $Filter))
                );
            }

            return $addLink . new TableData(
                $dataList,
                null,
                array(
                    'Year' => 'Schuljahr',
                    'Name' => 'Name',
                    'Description' => 'Beschreibung',
                    'Type' => 'Typ',
                    'Option' => '&nbsp;'
                ),
                array(
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 1),
                    ),
                    'order'      => array(array(0, 'asc'), array(1, 'asc')),
//                    'pageLength' => -1,
//                    'paging'     => false,
//                    'info'       => false,
//                    'searching'  => false,
                    'responsive' => false
                )
            );
        }

        return $addLink . '';
    }

    /**
     * @return Form
     */
    public function formFilter(): Form
    {
        $tblTypeAll = DivisionCourse::useService()->getDivisionCourseTypeAll();
        $tblYearAll = Term::useService()->getYearAll();
        if ($tblYearAll && Term::useService()->getYearByNow()) {

            $tblYearAll[] = new SelectBoxItem(-1, 'Aktuelle Übersicht');

            $Global = $this->getGlobal();
            $Global->POST['Filter']['Year'] = -1;
            $Global->savePost();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll)))
                        ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 6),
                new FormColumn(
                    (new SelectBox('Filter[Type]', 'Typ', array('{{ Name }}' => $tblTypeAll)))
                        ->ajaxPipelineOnChange(ApiDivisionCourse::pipelineLoadDivisionCourseContent())
                    , 6)
            ))
        )));
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     * @param bool $setPost
     *
     * @return Form
     */
    public function formDivisionCourse($DivisionCourseId = null,$Filter = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId);
        if ($setPost && $tblDivisionCourse) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Name'] = $tblDivisionCourse->getName();
            $Global->POST['Data']['Description'] = $tblDivisionCourse->getDescription();
            $Global->savePost();
        }

        if ($DivisionCourseId) {
            $saveButton = (new Primary('Speichern', ApiDivisionCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineEditDivisionCourseSave($DivisionCourseId, $Filter));
        } else {
            $saveButton = (new Primary('Speichern', ApiDivisionCourse::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDivisionCourse::pipelineCreateDivisionCourseSave($Filter));
        }
        $buttonList[] = $saveButton;

//        if ($DivisionCourseId) {
//            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
//                'Löschen',
//                ApiDivisionCourse::getEndpoint(),
//                new Remove(),
//                array(),
//                false
//            ))->ajaxPipelineOnClick(ApiDivisionCourse::pipelineOpenDeleteDivisionCourseModal($DivisionCourseId));
//        }

        $tblYearAll = Term::useService()->getYearAllSinceYears(0);
        $tblCourseAll = DivisionCourse::useService()->getDivisionCourseAll();
        $courseNameList = array();
        if ($tblCourseAll) {
            array_walk($tblCourseAll, function (TblDivisionCourse $tblDivisionCourse) use (&$courseNameList) {
                if (!in_array($tblDivisionCourse->getName(), $courseNameList)) {
                    array_push($courseNameList, $tblDivisionCourse->getName());
                }
            });
        }
        $tblTypeAll = DivisionCourse::useService()->getDivisionCourseTypeAll();

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn($tblDivisionCourse
                        ? new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                        : (new SelectBox('Data[Year]', 'Schuljahr', array('{{ Name }} {{ Description }}' => $tblYearAll), new Education()))->setRequired()
                    , 6),
                    new FormColumn($tblDivisionCourse
                        ? new Panel('Typ', $tblDivisionCourse->getTypeName(), Panel::PANEL_TYPE_INFO)
                        : (new SelectBox('Data[Type]', 'Typ', array('{{ Name }}' => $tblTypeAll)))->setRequired()
                    , 6)
                )),
                new FormRow(array(
                    new FormColumn(
                        (new AutoCompleter('Data[Name]', 'Name', 'z.B: 7a', $courseNameList, new Pencil()))->setRequired()
                    , 6),
                    new FormColumn(
                        new TextField('Data[Description]', 'zb: für Fortgeschrittene', 'Beschreibung', new Pencil())
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        $buttonList
                    )
                )),
            ))
        ))->disableSubmitAction();
    }
}