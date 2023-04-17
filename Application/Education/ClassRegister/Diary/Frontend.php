<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Diary\Diary;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
 */
class Frontend extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/Diary';

    /**
     * @return Stage
     */
    public function frontendSelectDivision(): Stage
    {
        $hasHeadmasterRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Headmaster');
        $hasTeacherRight = Access::useService()->hasAuthorization(self::BASE_ROUTE . '/Teacher');

        if ($hasHeadmasterRight) {
            if ($hasTeacherRight) {
                return $this->frontendTeacherSelectDivision();
            } else {
                return $this->frontendHeadmasterSelectDivision();
            }
        } else {
            return $this->frontendTeacherSelectDivision();
        }
    }

    /**
     * @param null $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision($IsAllYears = null, $YearId = null): Stage
    {

        $Stage = new Stage('Pädagogisches Tagebuch', 'Kurs auswählen');
        $this->setHeaderButtonList($Stage, View::TEACHER);

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher', $IsAllYears, $YearId, false, true, $yearFilterList);

        $dataList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && $yearFilterList
        ) {
            foreach ($yearFilterList as $tblYear) {
                // Klassenlehrer und Tutoren
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPerson, $tblYear))) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if ($tblDivisionCourse->getIsDivisionOrCoreGroup()) {
                            $dataList[] = array(
                                'Year' => $tblDivisionCourse->getYearName(),
                                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                                'Option' => new Standard(
                                    '',  self::BASE_ROUTE . '/Selected', new Select(),
                                    array(
                                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                                        'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }
            }
        }

        if (empty($dataList)) {
            $table = new Warning('Das pädagogisches Tagebuch steht nur Klassenlehrern und Tutoren zur Verfügung.', new Exclamation());
        } else {
            $table = new TableData($dataList, null, array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'DivisionCourseType' => 'Kurs-Typ',
                'SchoolTypes' => 'Schularten',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            ));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn($table)
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $IsAllYears
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision($IsAllYears = null, $YearId = null): Stage
    {
        $Stage = new Stage('Pädagogisches Tagebuch', 'Kurs auswählen');
        $this->setHeaderButtonList($Stage, View::HEADMASTER);

        $yearFilterList = array();
        // nur Schulleitung darf History (Alle Schuljahre) sehen
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster', $IsAllYears, $YearId, true, true, $yearFilterList);

        $dataList = array();
        $tblDivisionCourseList = array();
        if ($IsAllYears) {
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
        } elseif ($yearFilterList) {
            foreach ($yearFilterList as $tblYear) {
                if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                    $tblDivisionCourseList = $tblDivisionCourseListDivision;
                }
                if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                    TblDivisionCourseType::TYPE_CORE_GROUP))) {
                    $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
                }
            }
        }

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Option' => new Standard(
                    '', self::BASE_ROUTE . '/Selected', new Select(),
                    array(
                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                        'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                    ),
                    'Auswählen'
                )
            );
        }

        $table = new TableData($dataList, null, array(
            'Year' => 'Schuljahr',
            'DivisionCourse' => 'Kurs',
            'DivisionCourseType' => 'Kurs-Typ',
            'SchoolTypes' => 'Schularten',
            'Option' => ''
        ), array(
            'order' => array(
                array('0', 'desc'),
                array('1', 'asc'),
            ),
            'columnDefs' => array(
                array('type' => 'natural', 'targets' => 1),
                array('orderable' => false, 'width' => '1%', 'targets' => -1)
            ),
        ));

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        new LayoutColumn($table)
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param int $view
     */
    private function setHeaderButtonList(Stage $Stage, int $view)
    {
        $hasTeacherRight = Access::useService()->hasAuthorization('/Education/Diary/Teacher');
        $hasHeadmasterRight = Access::useService()->hasAuthorization('/Education/Diary/Headmaster');

        $countRights = 0;
        if ($hasTeacherRight) {
            $countRights++;
        }
        if ($hasHeadmasterRight) {
            $countRights++;
        }

        if ($countRights > 1) {
            if ($hasTeacherRight) {
                if ($view == View::TEACHER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Lehrer')),
                        self::BASE_ROUTE . '/Teacher', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Lehrer',
                        self::BASE_ROUTE . '/Teacher'));
                }
            }
            if ($hasHeadmasterRight) {
                if ($view == View::HEADMASTER) {
                    $Stage->addButton(new Standard(new Info(new Bold('Ansicht: Leitung')),
                        self::BASE_ROUTE . '/Headmaster', new Edit()));
                } else {
                    $Stage->addButton(new Standard('Ansicht: Leitung',
                        self::BASE_ROUTE . '/Headmaster'));
                }
            }
        }
    }

    /**
     * @param null $DivisionCourseId
     * @param string $BasicRoute
     * @param null $StudentId
     *
     * @return Stage|string
     */
    public function frontendDiary(
        $DivisionCourseId = null,
        string $BasicRoute = '/Education/Diary/Teacher',
        $StudentId = null
    ) {
        $stage = new Stage('Pädagogisches Tagebuch');
        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));

        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {

            return $stage . new Danger('Kurs nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $tblPersonTeacher = Account::useService()->getPersonByLogin();
        $tblStudent = false;
        $buttonName = $tblDivisionCourse->getTypeName() . 'nansicht';

        if ($StudentId && ($tblStudent = Person::useService()->getPersonById($StudentId))) {
            $buttonList = array(
                new Standard(
                    $buttonName, self::BASE_ROUTE . '/Selected', null,
                    array(
                        'DivisionCourseId' => $DivisionCourseId,
                        'BasicRoute' => self::BASE_ROUTE
                    )
                ),
                ApiDiary::receiverModal()
                . (new Standard(
                    new Edit() . new Info(new Bold('Schüleransicht')),
                    ApiDiary::getEndpoint()
                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenSelectStudentModal($DivisionCourseId))
            );
        } else {
            $buttonList = array(
                new Standard(
                    new Info(new Bold($buttonName)), self::BASE_ROUTE . '/Selected', new Edit(),
                    array(
                        'DivisionCourseId' => $DivisionCourseId,
                        'BasicRoute' => self::BASE_ROUTE
                    )
                ),
                ApiDiary::receiverModal()
                . (new Standard(
                    'Schüleransicht',
                    ApiDiary::getEndpoint()
                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenSelectStudentModal($DivisionCourseId))
            );
        }

        // Abstandszeile
        $buttonList[] = new Container('&nbsp;');

        $tblYear = $tblDivisionCourse->getServiceTblYear();

        $receiver = ApiDiary::receiverBlock($this->loadDiaryTable($tblDivisionCourse, $tblStudent ?: null), 'DiaryContent');

        $stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Danger('
                                Das pädagogische Tagebuch unterliegt dem Auskunfts-, Berichtigungs- und
                                Löschungsrecht durch die betroffenen Personen und deren Sorgeberechtigten. Aus
                                diesem Grund sind in diesem Tagebuch in der Spalte <b>"Inhalt"</b> nur
                                objektivierbare Sachverhalte und keine Klarnamen zu vermerken.
                            ', new Exclamation())
                        ))
                    )),
                    new LayoutRow(
                        new LayoutColumn($buttonList)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                $tblStudent ? 'Schüler' : $tblDivisionCourse->getTypeName(),
                                $tblStudent ? $tblStudent->getFullName() : $tblDivisionCourse->getDisplayName(),
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
                            $tblPersonTeacher
                                ? ($tblStudent
                                    ? ''
                                    : ApiDiary::receiverModal()
                                        . (new Primary(
                                            new Plus() . ' Eintrag hinzufügen',
                                            ApiDiary::getEndpoint()
                                        ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenCreateDiaryModal($tblDivisionCourse->getId())))
                                : new Warning(
                                    'Für Ihren Account ist keine Person ausgewählt. Sie können keine neuen Einträge zum pädagogischen Tagebuch hinzufügen',
                                    new Exclamation()
                                )
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            '&nbsp;'
                        )
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $receiver
                        )
                    ))
                ))
            ))
        );

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $DiaryId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formDiary(TblDivisionCourse $tblDivisionCourse, $DiaryId = null, bool $setPost = false): Form
    {
        $setStudents = array();
        if ($DiaryId && ($tblDiary = Diary::useService()->getDiaryById($DiaryId))) {
            // beim Checken, der Input-Feldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Date'] = $tblDiary->getDate();
                // im Chrome automatisches autocomplete von Ländern (welche im Browser eingetragen wurden, Formulardaten)
                $Global->POST['Data']['Place'] = $tblDiary->getLocation();
                $Global->POST['Data']['Subject'] = $tblDiary->getSubject();
                $Global->POST['Data']['Content'] = $tblDiary->getContent();

                if (($tblDiaryStudentList = Diary::useService()->getDiaryStudentAllByDiary($tblDiary))) {
                    foreach ($tblDiaryStudentList as $tblDiaryStudent) {
                        if (($tblPersonItem = $tblDiaryStudent->getServiceTblPerson())) {
                            $Global->POST['Data']['Students'][$tblPersonItem->getId()] = 1;
                            $setStudents[$tblPersonItem->getId()] = $tblPersonItem;
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
                ->ajaxPipelineOnClick(ApiDiary::pipelineCreateDiarySave($tblDivisionCourse->getId()));
        }

        $columns = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                $columns[$tblPerson->getId()] = new FormColumn(new CheckBox('Data[Students][' . $tblPerson->getId() . ']',
                    $tblPerson->getLastFirstName(), 1), 4);
            }
        }
        // deaktivierte ausgewählte Schüler hinzufügen
        if (!empty($setStudents)) {
            foreach ($setStudents as $personId => $tblStudent) {
                if (!isset($columns[$personId])) {
                    $columns[$tblStudent->getId()] = new FormColumn(new CheckBox('Data[Students][' . $tblStudent->getId() . ']',
                        new Strikethrough($tblStudent->getLastFirstName()), 1), 4);
                }
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                    , 6),
                    new FormColumn(
                        new TextField('Data[Place]', '', 'Ort')
                    , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Subject]', 'Titel', 'Titel', new Calendar())
                    ),
                )),
                new FormRow(array(
                   new FormColumn(array(
                       new Warning('Wenn kein Schüler ausgewählt wird, handelt es sich um einen ' . $tblDivisionCourse->getTypeName() . 'neintrag.')
                   ))
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson|null $tblStudentPerson
     *
     * @return TableData
     */
    public function loadDiaryTable(TblDivisionCourse $tblDivisionCourse, TblPerson $tblStudentPerson = null): TableData
    {
        $dataList = array();
        $diaryList = array();

        if (($tblDiaryList = Diary::useService()->getDiaryAllByDivisionCourse($tblDivisionCourse, true))) {
            foreach ($tblDiaryList as $tblDiary) {
                $diaryList[$tblDiary->getId()] = $tblDiary;
            }
        }

        // zusätzliche Schülereintrage (z.B. vom Klassenwechsel)
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblDiaryListByStudent = Diary::useService()->getDiaryAllByStudent($tblPerson))) {
                    foreach ($tblDiaryListByStudent as $item) {
                        if (!isset($diaryList[$item->getId()])) {
                            $diaryList[$item->getId()] = $item;
                        }
                    }
                }
            }
        }

        // sortieren nach Datum
        $diaryList = $this->getSorter($diaryList)->sortObjectBy('Date', new DateTimeSorter(), Sorter::ORDER_DESC);
        $count = 0;
        /** @var TblDiary $tblDiaryItem */
        foreach ($diaryList as $tblDiaryItem) {
            if ($tblStudentPerson) {
                if (!Diary::useService()->getDiaryStudentAllByDiary($tblDiaryItem)
                    || Diary::useService()->existsDiaryStudent($tblDiaryItem, $tblStudentPerson)
                ) {
                    // nur Klasseneinträge und welche mit dem ausgewählten Schüler
                    $count++;
                    $dataList[] = $this->setDiaryItem($tblDiaryItem, $count, $tblStudentPerson);
                }
            } else {
                $count++;
                $dataList[] = $this->setDiaryItem($tblDiaryItem, $count);
            }
        }

        $columns = array(
            'Number' => '#',
            'Information' => 'Information',
            'PersonList' => 'Schüler',
            'Content' => 'Inhalt'
        );

        if (!$tblStudentPerson) {
            $columns['Options'] = ' ';
        }
        return new TableData(
            $dataList,
            null,
            $columns,
            array(
                'order' => array(
                    array(0, 'asc')
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param TblDiary $tblDiary
     * @param int $count
     * @param TblPerson|null $tblStudentPerson
     *
     * @return array
     */
    private function setDiaryItem(TblDiary $tblDiary, int $count, TblPerson $tblStudentPerson = null): array
    {
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
                    if ($tblStudentPerson
                        && $tblStudentPerson->getId() != $tblPersonItem->getId()
                    ) {
                        $personList[] = '****';
                    } else {
                        $personList[] = $tblPersonItem->getLastFirstName();
                    }
                }
            }
        }

        // Optionen
        if ($tblStudentPerson) {
            $options = '';
        } else {
            // SSW-1156 Einträge die älter als 3 Monate sind dürfen nicht mehr bearbeitet werden
            $now = new DateTime('now');
            $date = new DateTime($tblDiary->getDate());
            $date->add(new DateInterval('P3M'));
            if ($date >= $now) {
                $options = (new Standard(
                    '',
                    ApiDiary::getEndpoint(),
                    new Edit(),
                    array(),
                    'Bearbeiten'
                ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenEditDiaryModal($tblDiary->getId()));
            } else {
                $options = '';
            }

            $options .= (new Standard(
                '',
                ApiDiary::getEndpoint(),
                new Remove(),
                array(),
                'Löschen'
            ))->ajaxPipelineOnClick(ApiDiary::pipelineOpenDeleteDiaryModal($tblDiary->getId()));
        }

        return array(
            'Number' => $count,
            'Information' => $tblDiary->getDate()
                . (($tblDivisionCourse = $tblDiary->getServiceTblDivisionCourse()) ? '<br> ' . $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getName() : '')
                . ($tblDivisionCourse && ($tblYear = $tblDivisionCourse->getServiceTblYear()) ? ' (' . $tblYear->getName() . ')' : '')
                . (($location = $tblDiary->getLocation()) ? '<br>' . $location : '')
                . '<br>' . $displayPerson,
            'PersonList' => empty($personList) ? '' : implode('<br>', $personList),
            'Content' => ($tblDiary->getSubject() ? new Bold($tblDiary->getSubject()) . '<br><br>' : '')
                // Zeilenumbrüche berücksichtigen
                . str_replace("\n", '<br>', $tblDiary->getContent()),
            'Options' => $options
        );
    }
}