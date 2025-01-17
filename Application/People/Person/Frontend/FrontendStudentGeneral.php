<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.12.2018
 * Time: 09:15
 */

namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationCategory;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\TemplateReadOnly;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Bus;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\StopSign;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\System\Extension\Extension;

/**
 * Class FrontendStudentGeneral
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendStudentGeneral extends FrontendReadOnly
{
    const TITLE = 'Schülerakte - Allgemeines';

    /**
     * @param null $PersonId
     * @param int  $AllowEdit
     *
     * @return string
     */
    public static function getStudentGeneralContent($PersonId = null, $AllowEdit = 1)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
//            $billingSiblingRank = '';

            $lockerNumber = '';
            $lockerLocation = '';
            $lockerKeyNumber = '';
            $lockerCombinationLockNumber = '';

            $baptismDate = '';
            $baptismLocation = '';

            $transportRoute = '';
            $transportStationEntrance = '';
            $transportStationExit = '';
            $transportRemark = '';
            $transportIsDriverStudent = '';

            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
//                if (($tblStudentBilling = $tblStudent->getTblStudentBilling())) {
//                    $billingSiblingRank = $tblStudentBilling->getServiceTblSiblingRank() ? $tblStudentBilling->getServiceTblSiblingRank()->getName() : '';
//                }

                if (($tblStudentLocker = $tblStudent->getTblStudentLocker())) {
                    $lockerNumber = $tblStudentLocker->getLockerNumber();
                    $lockerLocation = $tblStudentLocker->getLockerLocation();
                    $lockerKeyNumber = $tblStudentLocker->getKeyNumber();
                    $lockerCombinationLockNumber = $tblStudentLocker->getCombinationLockNumber();
                }

                if (($tblStudentBaptism = $tblStudent->getTblStudentBaptism())) {
                    $baptismDate = $tblStudentBaptism->getBaptismDate();
                    $baptismLocation = $tblStudentBaptism->getLocation();
                }

                if (($tblStudentTransport = $tblStudent->getTblStudentTransport())) {
                    $transportIsDriverStudent = $tblStudentTransport->getIsDriverStudent() ? 'Ja' : 'Nein';
                    $transportRoute = $tblStudentTransport->getRoute();
                    $transportStationEntrance = $tblStudentTransport->getStationEntrance();
                    $transportStationExit = $tblStudentTransport->getStationExit();
                    $transportRemark = $tblStudentTransport->getRemark();
                }
            }

            $LiberationPanel = array();
            if (($tblLiberationCategoryAll = Student::useService()->getStudentLiberationCategoryAll())) {
                array_walk($tblLiberationCategoryAll,
                    function (TblStudentLiberationCategory $tblStudentLiberationCategory) use (&$LiberationPanel, $tblStudent) {
                        if ($tblStudent && ($tblStudentLiberationList = Student::useService()->getStudentLiberationAllByStudent($tblStudent))) {
                            $text = reset($tblStudentLiberationList)->getTblStudentLiberationType()->getName();
                        } else {
                            $text = '';
                        }

                        array_push($LiberationPanel,
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel($tblStudentLiberationCategory->getName(), 6),
                                    self::getLayoutColumnValue($text, 6),
                                )),
                            )))
                        );
                    }
                );
            }

            $content = new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
//                        FrontendReadOnly::getSubContent(
//                            'Geschwisterkind',
//                            new Layout(new LayoutGroup(array(
//                                new LayoutRow(array(
////                                    self::getLayoutColumnLabel('Geschwisterkind', 6),
//                                    self::getLayoutColumnValue($billingSiblingRank, 12),
//                                ))
//                            )))
//                        ),
                        FrontendReadOnly::getSubContent(
                            'Schließfach',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Schließfachnummer', 6),
                                    self::getLayoutColumnValue($lockerNumber, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Schließfach Standort', 6),
                                    self::getLayoutColumnValue($lockerLocation, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Schlüssel Nummer', 6),
                                    self::getLayoutColumnValue($lockerKeyNumber, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Zahlenschloss Nummer', 6),
                                    self::getLayoutColumnValue($lockerCombinationLockNumber, 6),
                                )),
                            )))
                        ),
                    ), 4),
                    new LayoutColumn(array(
                        FrontendReadOnly::getSubContent(
                            'Taufe',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Taufdatum', 6),
                                    self::getLayoutColumnValue($baptismDate, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Taufort', 6),
                                    self::getLayoutColumnValue($baptismLocation, 6),
                                )),
                            )))
                        ),
                        FrontendReadOnly::getSubContent('Unterrichtsbefreiung', $LiberationPanel)
                    ), 4),
                    new LayoutColumn(
                        FrontendReadOnly::getSubContent(
                            'Schulbeförderung',
                            new Layout(new LayoutGroup(array(
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Fahrschüler', 6),
                                    self::getLayoutColumnValue($transportIsDriverStudent, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Buslinie', 6),
                                    self::getLayoutColumnValue($transportRoute, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Einstiegshaltestelle', 6),
                                    self::getLayoutColumnValue($transportStationEntrance, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Ausstiegshaltestelle', 6),
                                    self::getLayoutColumnValue($transportStationExit, 6),
                                )),
                                new LayoutRow(array(
                                    self::getLayoutColumnLabel('Bemerkungen', 6),
                                    self::getLayoutColumnValue($transportRemark, 6),
                                )),
                            )))
                        )
                    , 4),
                )),
            )));

            $editLink = '';
            if($AllowEdit == 1){
                $editLink = (new Link(new Edit() . ' Bearbeiten', ApiPersonEdit::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiPersonEdit::pipelineEditStudentGeneralContent($PersonId));
            }
            $DivisionString = FrontendReadOnly::getDivisionString($tblPerson);

            return TemplateReadOnly::getContent(
                self::TITLE,
                $content,
                array($editLink),
                'der Person ' . new Bold(new Success($tblPerson->getFullName())).$DivisionString,
                new TileSmall()
            );
        }

        return '';
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function getEditStudentGeneralContent($PersonId = null)
    {

        $tblPerson = false;
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $Global = $this->getGlobal();
            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {

                if (($tblStudentLocker = $tblStudent->getTblStudentLocker())) {
                    $Global->POST['Meta']['Additional']['Locker']['Number'] = $tblStudentLocker->getLockerNumber();
                    $Global->POST['Meta']['Additional']['Locker']['Location'] = $tblStudentLocker->getLockerLocation();
                    $Global->POST['Meta']['Additional']['Locker']['Key'] = $tblStudentLocker->getKeyNumber();
                    $Global->POST['Meta']['Additional']['Locker']['CombinationLockNumber'] = $tblStudentLocker->getCombinationLockNumber();
                }

                if (($tblStudentBaptism = $tblStudent->getTblStudentBaptism())) {
                    $Global->POST['Meta']['Additional']['Baptism']['Date'] = $tblStudentBaptism->getBaptismDate();
                    $Global->POST['Meta']['Additional']['Baptism']['Location'] = $tblStudentBaptism->getLocation();
                }

                if (($tblStudentTransport = $tblStudent->getTblStudentTransport())) {
                    $Global->POST['Meta']['Transport']['IsDriverStudent'] = $tblStudentTransport->getIsDriverStudent() ? 1 : 0;
                    $Global->POST['Meta']['Transport']['Route'] = $tblStudentTransport->getRoute();
                    $Global->POST['Meta']['Transport']['Station']['Entrance'] = $tblStudentTransport->getStationEntrance();
                    $Global->POST['Meta']['Transport']['Station']['Exit'] = $tblStudentTransport->getStationExit();
                    $Global->POST['Meta']['Transport']['Remark'] = $tblStudentTransport->getRemark();
                }

//                if ($tblStudentBilling = $tblStudent->getTblStudentBilling()) {
//                    if ($tblStudentBilling->getServiceTblSiblingRank()) {
//                        $Global->POST['Meta']['Billing'] = $tblStudentBilling->getServiceTblSiblingRank()->getId();
//                    }
//                }

                if (($tblStudentLiberationAll = Student::useService()->getStudentLiberationAllByStudent($tblStudent))) {
                    foreach ($tblStudentLiberationAll as $tblStudentLiberation) {
                        $Global->POST['Meta']['Liberation']
                        [$tblStudentLiberation->getTblStudentLiberationType()->getTblStudentLiberationCategory()->getId()]
                            = $tblStudentLiberation->getTblStudentLiberationType()->getId();
                    }
                }

                $Global->savePost();
            }
        }

        return $this->getEditStudentGeneralTitle($tblPerson ? $tblPerson : null)
            . new Well($this->getEditStudentGeneralForm($tblPerson ? $tblPerson : null));
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    private function getEditStudentGeneralTitle(TblPerson $tblPerson = null)
    {
        return new Title(new TileSmall() . ' ' . self::TITLE, self::getEditTitleDescription($tblPerson))
            . self::getDataProtectionMessage();
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Form
     */
    private function getEditStudentGeneralForm(TblPerson $tblPerson = null)
    {

        /**
         * Panel: Liberation
         */
        $tblLiberationCategoryAll = Student::useService()->getStudentLiberationCategoryAll();
        $LiberationPanel = array();
        array_walk($tblLiberationCategoryAll,
            function (TblStudentLiberationCategory $tblStudentLiberationCategory) use (&$LiberationPanel) {

                $tblLiberationTypeAll = Student::useService()->getStudentLiberationTypeAllByCategory($tblStudentLiberationCategory);
                array_push($LiberationPanel,
                    new SelectBox('Meta[Liberation]['.$tblStudentLiberationCategory->getId().']',
                        $tblStudentLiberationCategory->getName(), array(
                            '{{ Name }}' => $tblLiberationTypeAll
                        ))
                );
            }
        );
        $LiberationPanel = new Panel('Unterrichtsbefreiung', $LiberationPanel, Panel::PANEL_TYPE_INFO);

        $Form = (new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
//                        new Panel('Geschwisterkind', array(
//                            new SelectBox('Meta[Billing]', '', array('{{Name}}' => Relationship::useService()->getSiblingRankAll()),
//                                new Child()),
//                        ), Panel::PANEL_TYPE_INFO),
                        new Panel('Schließfach', array(
                            new TextField('Meta[Additional][Locker][Number]', 'Schließfachnummer', 'Schließfachnummer',
                                new Lock()),
                            new TextField('Meta[Additional][Locker][Location]', 'Schließfach Standort',
                                'Schließfach Standort', new MapMarker()),
                            new TextField('Meta[Additional][Locker][Key]', 'Schlüssel Nummer', 'Schlüssel Nummer',
                                new Key()),
                            new TextField('Meta[Additional][Locker][CombinationLockNumber]', 'Zahlenschloss Nummer', 'Zahlenschloss Nummer',
                                new Key())
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Taufe', array(
                            new DatePicker('Meta[Additional][Baptism][Date]', 'Taufdatum', 'Taufdatum',
                                new TempleChurch()
                            ),
                            new TextField('Meta[Additional][Baptism][Location]', 'Taufort', 'Taufort', new MapMarker()),
                        ), Panel::PANEL_TYPE_INFO),
                        $LiberationPanel
                    ), 4),
                    new FormColumn(
                        new Panel('Schulbeförderung', array(
                            new CheckBox('Meta[Transport][IsDriverStudent]', 'Fahrschüler', 1),
                            new TextField('Meta[Transport][Route]', 'Buslinie', 'Buslinie', new Bus()),
                            new TextField('Meta[Transport][Station][Entrance]', 'Einstiegshaltestelle',
                                'Einstiegshaltestelle', new StopSign()),
                            new TextField('Meta[Transport][Station][Exit]', 'Ausstiegshaltestelle',
                                'Ausstiegshaltestelle', new StopSign()),
                            new TextArea('Meta[Transport][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO)
                    , 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        (new Primary('Speichern', ApiPersonEdit::getEndpoint(), new Save()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveStudentGeneralContent($tblPerson ? $tblPerson->getId() : 0)),
                        (new Primary('Abbrechen', ApiPersonEdit::getEndpoint(), new Disable()))
                            ->ajaxPipelineOnClick(ApiPersonEdit::pipelineCancelStudentGeneralContent($tblPerson ? $tblPerson->getId() : 0))
                    ))
                ))
            ))
        )))->disableSubmitAction();

        return $Form;
    }
}