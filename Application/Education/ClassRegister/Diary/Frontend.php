<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
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
        $BasicRoute = '/Education/ClassRegister/Teacher',
        $Data = null
    ) {
        $stage = new Stage('Klassenbuch', 'pädagogisches Tagebuch');
        if ($tblDivision = Division::useService()->getDivisionById($DivisionId)) {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute . '/Selected', new ChevronLeft(),
                array('DivisionId' => $tblDivision->getId())
            ));

            $tblYear = $tblDivision->getServiceTblYear();
            // todo verknüpfte Klassen
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

                    $dataList[] = array(
                        'Date' => $tblDiary->getDate(),
                        'Location' => $tblDiary->getLocation(),
                        'Editor' => $displayPerson,
                        'PersonList' => '',
                        'Subject' => $tblDiary->getSubject(),
                        'Content' => $tblDiary->getContent(),
                    );
                }
            }

            $tableData = new TableData(
                $dataList,
                null,
                array(
                    'Date' => 'Datum',
                    'Location' => 'Ort',
                    'Editor' => 'Verfasser',
                    'PersonList' => 'Schüler',
                    'Subject' => 'Titel',
                    'Content' => 'Bemerkung',
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
                                $tableData
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

    public function formDiary($DivisionId, $DiaryId = null, $setPost = false)
    {

        if ($DiaryId && ($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
//                $Global->POST['Address'] = $tblDiary->getTblMail()->getAddress();
//                $Global->POST['Type']['Type'] = $tblDiary->getTblType()->getId();
//                $Global->POST['Type']['Remark'] = $tblDiary->getRemark();
                $Global->savePost();
            }
        }

        if ($DiaryId) {
            $saveButton = (new Primary('Speichern', ApiDiary::getEndpoint(), new Save()));
//                ->ajaxPipelineOnClick(ApiDiary::pipelineEditDiarySave($DivisionId, $DiaryId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDiary::getEndpoint(), new Save()));
//                ->ajaxPipelineOnClick(ApiDiary::pipelineCreateDiarySave($DivisionId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new DatePicker('Data[Date]', '', 'Datum', new Calendar())
                    , 6),
                    new FormColumn(
                        new TextField('Data[Subject]', '', 'Titel')
                    , 6),
//                    new FormColumn(
//                        new Panel('Sonstiges',
//                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
//                            , Panel::PANEL_TYPE_INFO
//                        ), 6
//                    ),
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }
}