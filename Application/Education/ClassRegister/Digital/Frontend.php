<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\Certificate\Prepare\View;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Home;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/ClassRegister/Digital';

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
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendTeacherSelectDivision(bool $IsAllYears = false, bool $IsGroup = false, $YearId = null): Stage
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Klasse auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::TEACHER, self::BASE_ROUTE);

        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Teacher',
            $IsAllYears, $IsGroup, $YearId, false, true, $yearFilterList);

        $table = false;
        $divisionTable = array();
        if ($tblPerson) {
            $divisionList = array();

            // Klassenlehrer
            if (($tblDivisionList = Division::useService()->getDivisionTeacherAllByTeacher($tblPerson))) {
                foreach ($tblDivisionList as $tblDivisionTeacher) {
                    if (($tblDivision = $tblDivisionTeacher->getTblDivision())
                        && ($tblYear = $tblDivision->getServiceTblYear())
                    ) {
                        if ($yearFilterList && !isset($yearFilterList[$tblYear->getId()])) {
                            continue;
                        }

                        $divisionList[$tblDivision->getId()] = $tblDivision;
                    }
                }
            }

            // Fachlehrer
            if (($tblSubjectTeacherAllByTeacher = Division::useService()->getSubjectTeacherAllByTeacher($tblPerson))) {
                foreach ($tblSubjectTeacherAllByTeacher as $tblSubjectTeacher) {
                    if (($tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject())
                        && ($tblDivisionItem = $tblDivisionSubject->getTblDivision())
                        && ($tblYearItem = $tblDivisionItem->getServiceTblYear())
                    ) {
                        if ($yearFilterList && !isset($yearFilterList[$tblYearItem->getId()])) {
                            continue;
                        }

                        $divisionList[$tblDivisionItem->getId()] = $tblDivisionItem;
                    }
                }
            }

            if ($IsGroup) {
                if (($tblTudorGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TUDOR))
                    && Group::useService()->existsGroupPerson($tblTudorGroup, $tblPerson)
                ) {
                    $isTudor = true;
                } else {
                    $isTudor = false;
                }

                if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                    foreach ($tblGroupAll as $tblGroup) {
                        // ist Tudor in Stammgruppe
                        $addGroup = false;
                        if ($isTudor && Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                            $addGroup = true;
                        // oder ist Klassenlehrer oder Fachlehrer
                        } else {
                            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                                foreach ($tblPersonList as $tblPersonStudent) {
                                    if (($tblStudent = $tblPersonStudent->getStudent())
                                        && ($tblDivisionMain = $tblStudent->getCurrentMainDivision())
                                        && isset($divisionList[$tblDivisionMain->getId()])
                                    ) {
                                        $addGroup = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if ($addGroup) {
                            $divisionTable[] = array(
                                'Group' => $tblGroup->getName(),
                                'Option' => new Standard(
                                    '', self::BASE_ROUTE . '/LessonContent', new Select(),
                                    array(
                                        'GroupId' => $tblGroup->getId(),
                                        'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                                    ),
                                    'Auswählen'
                                )
                            );
                        }
                    }
                }

                if (empty($divisionTable)) {
                    $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
                } else {
                    $table = new TableData($divisionTable, null, array(
                        'Group' => 'Gruppe',
                        'Option' => ''
                    ), array(
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    ));
                }
            } else {
                foreach ($divisionList as $item) {
                    $divisionTable[] = array(
                        'Year' => $item->getServiceTblYear() ? $item->getServiceTblYear()->getDisplayName() : '',
                        'Type' => $item->getTypeName(),
                        'Division' => $item->getDisplayName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/LessonContent', new Select(),
                            array(
                                'DivisionId' => $item->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Teacher'
                            ),
                            'Auswählen'
                        )
                    );
                }

                if (empty($divisionTable)) {
                    $table = new Warning('Keine entsprechenden Lehraufträge vorhanden.', new Exclamation());
                } else {
                    $table = new TableData($divisionTable, null, array(
                        'Year' => 'Schuljahr',
                        'Type' => 'Schulart',
                        'Division' => 'Klasse',
                        'Option' => ''
                    ), array(
                        'order' => array(
                            array('0', 'desc'),
                            array('1', 'asc'),
                            array('2', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 2),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1)
                        ),
                    ));
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        empty($buttonList)
                            ? null
                            : new LayoutColumn($buttonList),
                        $table
                            ? new LayoutColumn(array($table))
                            : null
                    ))
                ), new Title(new Select() . ' Auswahl'))
            ))
        );

        return $Stage;
    }

    /**
     * @param bool $IsAllYears
     * @param bool $IsGroup
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendHeadmasterSelectDivision(bool $IsAllYears = false, bool $IsGroup = false, $YearId = null): Stage
    {

        $Stage = new Stage('Digitales Klassenbuch', 'Klasse auswählen');
        Digital::useService()->setHeaderButtonList($Stage, View::HEADMASTER, self::BASE_ROUTE);

        $tblDivisionList = Division::useService()->getDivisionAll();

        $yearFilterList = array();
        $buttonList = Digital::useService()->setYearGroupButtonList(self::BASE_ROUTE . '/Headmaster',
            $IsAllYears, $IsGroup, $YearId, true, true, $yearFilterList);

        $divisionTable = array();
        if ($IsGroup) {
            // tudorGroups
            if (($tblGroupAll = Group::useService()->getTudorGroupAll())) {
                foreach ($tblGroupAll as $tblGroup) {
                    $divisionTable[] = array(
                        'Group' => $tblGroup->getName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/LessonContent', new Select(),
                            array(
                                'GroupId' => $tblGroup->getId(),
                                'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                            ),
                            'Auswählen'
                        )
                    );
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Group' => 'Gruppe',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            ));
        } else {
            if ($tblDivisionList) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (($tblYear = $tblDivision->getServiceTblYear())) {
                        // Bei einem ausgewähltem Schuljahr die anderen Schuljahre ignorieren
                        if ($yearFilterList && !isset($yearFilterList[$tblYear->getId()])) {
                            continue;
                        }

                        $divisionTable[] = array(
                            'Year' => $tblYear->getDisplayName(),
                            'Type' => $tblDivision->getTypeName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Option' => new Standard(
                                '', self::BASE_ROUTE . '/LessonContent', new Select(),
                                array(
                                    'DivisionId' => $tblDivision->getId(),
                                    'BasicRoute' => self::BASE_ROUTE . '/Headmaster'
                                ),
                                'Auswählen'
                            )
                        );
                    }
                }
            }

            $table = new TableData($divisionTable, null, array(
                'Year' => 'Schuljahr',
                'Type' => 'Schulart',
                'Division' => 'Klasse',
                'Option' => ''
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('2', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 2),
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
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendLessonContent(
        $DivisionId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Übersicht');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));

        $tblYear = null;
//        $tblPerson = Account::useService()->getPersonByLogin();
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblDivision || $tblGroup) {


            $stage->setContent(
                ApiDigital::receiverModal()
                . new Layout(new LayoutGroup(array(
                    Digital::useService()->getHeadColumnRow(
                        $tblDivision ?: null, $tblGroup ?: null, $tblYear
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            (new Primary(
                                new Plus() . ' Unterrichtseinheit hinzufügen',
                                ApiDigital::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateLessonContentModal($DivisionId, $GroupId))
                        )
                    ))
                )))
                . ApiDigital::receiverBlock($this->loadLessonContentTable($tblDivision ?: null, $tblGroup ?: null), 'LessonContentContent')
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return string
     */
    public function loadLessonContentTable(TblDivision $tblDivision = null, TblGroup $tblGroup = null): string
    {
        return 'Hallo';
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param null $LessonContentId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formLessonContent(TblDivision $tblDivision = null, TblGroup $tblGroup = null, $LessonContentId = null,
        bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $LessonContentId
            && ($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblLessonContent->getDate();
            $Global->POST['Data']['Subject'] =
                ($tblSubject = $tblLessonContent->getServiceTblSubject()) ? $tblSubject->getId() : 0;
            $Global->POST['Data']['Lesson'] = $tblLessonContent->getLesson();
            $Global->POST['Data']['Subject'] =
                ($tblPerson = $tblLessonContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblLessonContent->getContent();
            $Global->POST['Data']['Homework'] = $tblLessonContent->getHomework();

            $Global->savePost();
        }

        if ($LessonContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditLessonContentSave($LessonContentId));
        } else {
            // todo befüllen bei neuen Einträge aus dem importierten Stundenplan
            // todo eingeloggten Lehrer vorsetzen falls er das Fach unterrichtet

            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateLessonContentSave(
                    $tblDivision ? $tblDivision->getId() : null,
                    $tblGroup ? $tblGroup->getId() : null
                ));
        }

        // todo Gruppen auswahl?

        // todo Lehrer vorauswahl -> eventuell dynamische abhängig
        $tblTeacherList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER'));

        // todo Fächer eingrenzen
        $tblSubjectList = Subject::useService()->getSubjectAll();

        for ($i = 0; $i < 13; $i++) {
            $lessons[] = new SelectBoxItem($i, $i . '. Unterrichtseinheit');
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[Lesson]', 'Unterrichtseinheit', array('{{ Name }}' => $lessons)))->setRequired()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Data[serviceTblSubject]', 'Fach', array('{{ Name }}' => $tblSubjectList))
                        , 6),
                    new FormColumn(
                        new SelectBox('Data[serviceTblPerson]', 'Lehrer', array('{{ FullName }}' => $tblTeacherList))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Content]', 'Thema/Inhalt', 'Thema/Inhalt', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Homework]', 'Hausaufgaben', 'Hausaufgaben', new Home())
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