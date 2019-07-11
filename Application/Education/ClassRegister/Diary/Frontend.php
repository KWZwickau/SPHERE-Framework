<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
 */
class Frontend extends Extension implements IFrontendInterface
{
    public function frontendDiary(
        $DivisionId = null,
        $BasicRoute = '/Education/ClassRegister/Teacher'
    ) {
        $stage = new Stage('Klassenbuch', 'pädagogisches Tagebuch');
        if ($tblDivision = Division::useService()->getDivisionById($DivisionId)) {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute . '/Selected', new ChevronLeft(),
                array('DivisionId' => $tblDivision->getId())
            ));

            $tblYear = $tblDivision->getServiceTblYear();

            $receiver = ApiDiary::receiverBlock($this->loadDiaryTable($tblDivision), 'DiaryContent');

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel(
                                    'Klasse',
                                    $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 6),
                            new LayoutColumn(
                                new Panel(
                                    'Schuljahr',
                                    $tblYear ? $tblYear->getDisplayName() : '',
                                    Panel::PANEL_TYPE_INFO
                                )
                                , 6),
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                ApiDiary::receiverModal()
                                . (new Primary(
                                    new Plus() . ' Eintrag hinzufügen',
                                    ApiDiary::getEndpoint()
                                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenCreateDiaryModal($tblDivision->getId()))
                            ),
                            new LayoutColumn(
                                $receiver
                            )
                        ))
                    ))
                ))
            );

        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            return new Danger('Klasse nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivision $tblDivision
     * @param null $DiaryId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formDiary(TblDivision $tblDivision, $DiaryId = null, $setPost = false)
    {

        if ($DiaryId && ($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Date'] = $tblDiary->getDate();
                $Global->POST['Data']['Location'] = $tblDiary->getLocation();
                $Global->POST['Data']['Subject'] = $tblDiary->getSubject();
                $Global->POST['Data']['Content'] = $tblDiary->getContent();

                if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
                    foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                        if (($tblPersonItem = $tblDiaryStudent->getServiceTblPerson())) {
                            $Global->POST['Data']['Students'][$tblPersonItem->getId()] = 1;
                        }
                    }
                }

                $Global->savePost();
            }
        }

        if ($DiaryId) {
            $saveButton = (new Primary('Speichern', ApiDiary::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDiary::pipelineEditDiarySave($DiaryId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDiary::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDiary::pipelineCreateDiarySave($tblDivision->getId()));
        }

        // todo deaktivierte ausgewählte Schüler hinzufügen
        $columns = array();
        if (($tblDivisionStudentList = Division::useService()->getStudentAllByDivision($tblDivision))) {
            foreach($tblDivisionStudentList as $tblPerson) {
                $columns[] = new FormColumn(new CheckBox('Data[Students][' . $tblPerson->getId() . ']', $tblPerson->getLastFirstName(), 1), 4);
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                    , 6),
                    new FormColumn(
                        new TextField('Data[Location]', '', 'Ort')
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Subject]', 'Titel', 'Titel', new Calendar())
                    ),
                )),
                new FormRow(
                    $columns
                ),
                new FormRow(array(
                    new FormColumn(
                        new TextArea('Data[Content]', 'Bemerkungen', 'Bemerkungen', new Edit())
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

    /**
     * @param TblDivision $tblDivision
     *
     * @return TableData
     */
    public function loadDiaryTable(TblDivision $tblDivision)
    {
        // todo verknüpfte Klassen
        // todo einträge von Klassengewechselten Schülern innerhalb eines Schuljahres
        $dataList = array();
        if (($tblDiaryList = Diary::useService()->getDiaryAllByDivision($tblDivision))) {
            foreach ($tblDiaryList as $tblDiary) {
                $displayPerson = '';
                if (($tblPerson = $tblDiary->getServiceTblPerson())) {
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
                        && ($acronym = $tblTeacher->getAcronym())
                    ) {
                        $displayPerson = $acronym;
                    } else {
                        $displayPerson = $tblPerson->getLastName();
                    }
                }

                $personList = array();
                if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
                    foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                        if (($tblPersonItem = $tblDiaryStudent->getServiceTblPerson())) {
                            $personList[] = $tblPersonItem->getLastFirstName();
                        }
                    }
                }

                $dataList[] = array(
                    'Date' => $tblDiary->getDate(),
                    'Division' => $tblDivision->getDisplayName()
                        . (($tblYear = $tblDivision->getServiceTblYear()) ? ' (' . $tblYear->getName() . ')' : ''),
                    'Location' => $tblDiary->getLocation(),
                    'Editor' => $displayPerson,
                    'PersonList' => empty($personList) ? '' : implode(' | ', $personList),
                    'Subject' => $tblDiary->getSubject(),
                    'Content' => $tblDiary->getContent(),
                    'Options' =>
                        (new Standard(
                            '',
                            ApiDiary::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenEditDiaryModal($tblDiary->getId()))
                );
            }
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Date' => 'Datum',
                'Division' => 'Klasse',
                'Location' => 'Ort',
                'Editor' => 'Verfasser',
                'PersonList' => 'Schüler',
                'Subject' => 'Titel',
                'Content' => 'Bemerkungen',
                'Options' => ' '
            ),
            array(
                'order' => array(
                    array(0, 'desc')
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                ),
                'responsive' => false
            )
        );
    }
}