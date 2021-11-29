<?php
namespace SPHERE\Application\People\Person\Frontend;

use SPHERE\Application\Api\People\Person\ApiFamilyEdit;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\MailField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Map;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;

/**
 * Class FrontendFamily
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendFamily extends FrontendReadOnly
{
    /**
     * @param array $Data
     *
     * @return Stage
     */
    public function frontendFamilyCreate($Data = array())
    {
        $stage = new Stage('Familie', 'Personen anlegen');

        $stage->setContent(new Well(Person::useService()->createFamily($this->formCreateFamily($Data, array()), $Data)));

        return $stage;
    }

    /**
     * @param $Person
     * @param $key
     *
     * @return string
     */
    public function loadSimilarPersonContent($Person, $key)
    {
        if ((!isset($Person['FirstName']) || empty($Person['FirstName']))
            || (!isset($Person['LastName']) || empty($Person['LastName']))
        ) {

            return '';
        } else {
            // dynamic search
            $Pile = new Pile();
            $Pile->addPile(Person::useService(), new ViewPerson());
            // find Input fields in ViewPerson
            $Result = $Pile->searchPile(array(
                array(
                    ViewPerson::TBL_PERSON_FIRST_NAME => explode(' ', $Person['FirstName']),
                    ViewPerson::TBL_PERSON_LAST_NAME => explode(' ', $Person['LastName'])
                )
            ));

            if (!empty($Result)) { // show Person

                $TableList = array();
                /** @var ViewPerson[] $ViewPerson */
                foreach ($Result as $Index => $ViewPerson) {
                    $TableList[$Index] = current($ViewPerson)->__toArray();

                    $PersonId = $PersonName = '';
                    $Address = new Warning('Keine Adresse hinterlegt');
                    $BirthDay = new Warning('Kein Datum hinterlegt');
                    if (isset($TableList[$Index]['TblPerson_Id'])) {
                        $PersonId = $TableList[$Index]['TblPerson_Id'];
                        $tblPerson = Person::useService()->getPersonById($PersonId);
                        if ($tblPerson) {
                            $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                            if ($tblCommon) {
                                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                                if ($tblCommonBirthDates) {
                                    if ($tblCommonBirthDates->getBirthday() != '') {
                                        $BirthDay = $tblCommonBirthDates->getBirthday();
                                    }
                                }
                            }

                            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                            if ($tblAddress) {
                                $Address = $tblAddress->getGuiString();
                            }
                        }
                    }
                    $TableList[$Index]['BirthDay'] = $BirthDay;
                    $TableList[$Index]['Address'] = $Address;
                    $TableList[$Index]['Option'] = new Standard('', '/People/Person', new \SPHERE\Common\Frontend\Icon\Repository\Person()
                        , array('Id' => $PersonId), 'Zur Person');
                }

                $Table = new TableData($TableList, new Title('Ähnliche Personen'), array(
                    ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
                    ViewPerson::TBL_PERSON_TITLE => 'Titel',
                    ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
                    ViewPerson::TBL_PERSON_SECOND_NAME => 'Zweiter Vorname',
                    ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                    ViewPerson::TBL_PERSON_BIRTH_NAME => 'Geburtsname',
                    'BirthDay' => 'Geburtstag',
                    'Address' => 'Adresse',
                    'Option' => '',
                ), array('order'      => array(
                    array(4, 'asc'),
                    array(2, 'asc')
                ),
                    'columnDefs' => array(
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                    )));

                $Table->setHash('Ähnliche Personen' . $key);

                return new Danger(new Bold(count($TableList) . ' Personen mit ähnlichem Namen gefunden. Ist diese Person schon angelegt?'))
                    . (string) $Table;
            }

            return new Success('Keine Personen zu ' . $Person['FirstName'] . ' ' . $Person['LastName'] . ' gefunden');
        }
    }

    /**
     * @param $Data
     * @param $Errors
     *
     * @return Form
     */
    public function formCreateFamily($Data, $Errors)
    {
        $tblSalutationList[] = new TblSalutation('');
        $tblSalutationList[] = Person::useService()->getSalutationByName('Herr');
        $tblSalutationList[] = Person::useService()->getSalutationByName('Frau');
//        $tblSalutationAll = Person::useService()->getSalutationAll();

        $formRows = array();
        if (isset($Errors['Person'])) {
            $formRows[] = new FormRow(new FormColumn(new Danger(implode('</br>', $Errors['Person']))));
        } elseif ($Errors) {
            $formRows[] = new FormRow(new FormColumn(
                new Danger('Die Daten wurden nicht gespeichert. Bitte überprüfen Sie die unteren Fehlermeldungen.', new Exclamation())
            ));
        }

        if ($Data) {
            $countPersons = $this->getCountPersons($Data);
            foreach($Data as $key => $item) {
                $type = substr($key, 0, 1);
                $ranking = substr($key, 1);

                if ($type == 'C') {
                    // Schüler / Interessent

                    $formRows[] = new FormRow(new FormColumn(
                        ApiFamilyEdit::receiverBlock($this->getChildContent($ranking, $Data, $Errors,
                            $countPersons[$type] == $ranking, $countPersons['C'] != 1), 'ChildContent_' . $ranking)
                    ));
                }
            }
        } else {
            $formRows[] = new FormRow(new FormColumn(
                ApiFamilyEdit::receiverBlock($this->getChildContent(1, $Data, $Errors, true, false), 'ChildContent_1')
            ));
        }

        $formRows[] = new FormRow(array(
            new FormColumn(
                $this->getPanelCustody(1, $tblSalutationList, $Errors)
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                $this->getPanelCustody(2, $tblSalutationList, $Errors)
            ),
        ));

        $formRows[] = new FormRow(array(
            new FormColumn(
                (new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()))
            ),
        ));

        return new Form(new FormGroup($formRows));
    }

    /**
     * @param $Data
     *
     * @return array
     */
    private function getCountPersons($Data)
    {
        $count['C'] = 0;
        $count['S'] = 0;

        foreach($Data as $key => $item) {
            $type = substr($key, 0, 1);
            $count[$type]++;
        }

        return $count;
    }

    /**
     * @param $Ranking
     * @param $Data
     * @param $Errors
     * @param bool $hasAddButton
     * @param bool $hasSiblingOption
     *
     * @return string
     */
    public function getChildContent($Ranking, $Data, $Errors, $hasAddButton = true, $hasSiblingOption = true)
    {
        $key = 'C' . $Ranking;

        $tblCommonBirthDatesAll = Common::useService()->getCommonBirthDatesAll();
        $tblBirthplaceAll = array();
        if ($tblCommonBirthDatesAll) {
            array_walk($tblCommonBirthDatesAll,
                function (TblCommonBirthDates $tblCommonBirthDates) use (&$tblBirthplaceAll) {

                    if ($tblCommonBirthDates->getBirthplace()) {
                        if (!in_array($tblCommonBirthDates->getBirthplace(), $tblBirthplaceAll)) {
                            array_push($tblBirthplaceAll, $tblCommonBirthDates->getBirthplace());
                        }
                    }
                });
        }

        list($tblNationalityAll, $tblDenominationAll) = Person::useService()->getCommonInformationForAutoComplete();

        $tblGroupList = array();
        if (($tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            $tblGroupList[] = $tblGroupStudent;
        }
        if (($tblGroupProspect = Group::useService()->getGroupByMetaTable('PROSPECT'))) {
            $tblGroupList[] = $tblGroupProspect;

            $global = $this->getGlobal();
            $global->POST['Data'][$key]['Group'] = $tblGroupProspect->getId();

            $global->savePost();
        }

        $firstNameInput = $this->getInputField('TextField', $key, 'FirstName', 'Vorname', 'Vorname', true, $Errors);
        $lastNameInput = $this->getInputField('TextField', $key, 'LastName', 'Nachname', 'Nachname', true, $Errors);
        $tblSalutationAll = Person::useService()->getSalutationAll(true);

        $salutationSelectBox = (new SelectBox('Data['.$key.'][Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
            new Conversation(), true, null));

        $firstNameInput->ajaxPipelineOnKeyUp(ApiFamilyEdit::pipelineLoadSimilarPersonContent($key));
        $lastNameInput->ajaxPipelineOnKeyUp(ApiFamilyEdit::pipelineLoadSimilarPersonContent($key));
        $salutationSelectBox->ajaxPipelineOnChange(ApiFamilyEdit::pipelineChangeSelectedGender($Ranking, 'C'));

        $tblCommonGenderAll = Common::useService()->getCommonGenderAll();

        if ($hasSiblingOption) {
            $title = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Kind', 3),
                new LayoutColumn($this->getSiblingCheckBox($Ranking), 3)
            ))));
        } else {
            $title = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn('Kind', 3),
                new LayoutColumn(ApiFamilyEdit::receiverBlock('', 'SiblingCheckBox_' . $Ranking), 3)
            ))));
        }

        $genderReceiver = ApiFamilyEdit::receiverBlock($this->getGenderSelectBox(0, $Ranking, 'C'), 'SelectedGenderChild' . $Ranking);

        return new Panel(
            $title,
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $salutationSelectBox, 2
                    )
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $firstNameInput, 3
                    ),
                    new LayoutColumn(
                        $lastNameInput, 3
                    ),
                    new LayoutColumn(
                        new TextField('Data[C' . $Ranking . '][SecondName]', 'weitere Vornamen', 'Zweiter Vorname'), 3
                    ),
                    new LayoutColumn(
                        new TextField('Data[C' . $Ranking . '][CallName]', 'Rufname', 'Rufname'), 3
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new SelectBox('Data[C' . $Ranking . '][Group]', 'Gruppe', array('{{ Name }}' => $tblGroupList)), 3
                    ),
                    new LayoutColumn(
                        new DatePicker('Data[C' . $Ranking . '][Birthday]', 'Geburtstag', 'Geburtstag', new Calendar()), 3
                    ),
                    new LayoutColumn(
                        new AutoCompleter('Data[C' . $Ranking . '][Birthplace]', 'Geburtsort', 'Geburtsort', $tblBirthplaceAll, new MapMarker()), 3
                    ),
                    new LayoutColumn(
                        $genderReceiver, 3
//                        new SelectBox('Data[C' . $Ranking . '][Gender]', 'Geschlecht', array('{{ Name }}' => $tblCommonGenderAll), new Child()), 3
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new AutoCompleter('Data[C' . $Ranking . '][Nationality]', 'Staatsangehörigkeit',
                            'Staatsangehörigkeit', $tblNationalityAll, new Nameplate()), 3
                    ),
                    new LayoutColumn(
                        new AutoCompleter('Data[C' . $Ranking . '][Denomination]', 'Konfession',
                            'Konfession', $tblDenominationAll, new TempleChurch()), 3
                    ),
                )),
                new LayoutRow((array(
                    new LayoutColumn(
                        ApiFamilyEdit::receiverBlock('', 'SimilarPersonContent_' . $key)
                    )
                )))
            ))),
            Panel::PANEL_TYPE_INFO
        )
        . ($hasAddButton
            ? ApiFamilyEdit::receiverBlock(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    (new Primary('', ApiFamilyEdit::getEndpoint(), new Plus(), array(), 'Ein weiteres Kind hinzufügen'))
                        ->ajaxPipelineOnClick(
                            (new ApiFamilyEdit)->pipelineLoadChildContent(($Ranking + 1), $Data, $Errors)
                        )
                , 1),
                new LayoutColumn(
                    new Container('&nbsp;')
                )
            )))),
            'ChildContent_' . ($Ranking + 1))
            : '');
    }

    /**
     * @param $Ranking
     *
     * @return CheckBox
     */
    public function getSiblingCheckBox($Ranking)
    {
        $key = 'C' . $Ranking;

        $global = $this->getGlobal();
        $global->POST['Data'][$key]['IsSibling'] = 1;
        $global->savePost();

        return new CheckBox('Data[C' . $Ranking . '][IsSibling]', 'Geschwisterkind', 1);
    }

    /**
     * @param int $Ranking
     * @param TblSalutation[]|false $tblSalutationAll
     * @param $Errors
     *
     * @return Panel
     */
    private function getPanelCustody($Ranking, $tblSalutationAll, $Errors)
    {
        $key = 'S' . $Ranking;

        $title = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn('Sorgeberechtigter', 3),
            new LayoutColumn(new CheckBox('Data[S' . $Ranking . '][IsSingleParent]', 'alleinerziehend', 1), 3)
        ))));

        $genderReceiver = ApiFamilyEdit::receiverBlock($this->getGenderSelectBox(0, $Ranking, 'S'), 'SelectedGender' . $Ranking);

        $firstNameInput = $this->getInputField('TextField', $key, 'FirstName', 'Vorname', 'Vorname', true, $Errors);
        $lastNameInput = $this->getInputField('TextField', $key, 'LastName', 'Nachname', 'Nachname', true, $Errors);

        $firstNameInput->ajaxPipelineOnKeyUp(ApiFamilyEdit::pipelineLoadSimilarPersonContent($key));
        $lastNameInput->ajaxPipelineOnKeyUp(ApiFamilyEdit::pipelineLoadSimilarPersonContent($key));

        return new Panel(
            $title,
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        (new SelectBox('Data[S' . $Ranking . '][Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll)
                            , new Conversation(), true, null
                            ))->ajaxPipelineOnChange(ApiFamilyEdit::pipelineChangeSelectedGender($Ranking, 'S'))
                        , 3
                    ),
                    new LayoutColumn(
                        (new AutoCompleter('Data[S' . $Ranking . '][Title]', 'Titel', 'Titel', array('Dipl.- Ing.'), new Conversation())), 3
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $firstNameInput
                        , 3),
                    new LayoutColumn(
                        $lastNameInput
                        , 3),
                    new LayoutColumn(
                        new TextField('Data[S' . $Ranking . '][BirthName]', 'Geburtsname', 'Geburtsname'), 3
                    ),
                    new LayoutColumn(
                        $genderReceiver, 3
                    ),
                )),
                new LayoutRow((array(
                    new LayoutColumn(
                        ApiFamilyEdit::receiverBlock('', 'SimilarPersonContent_' . $key)
                    )
                )))
            ))),
            Panel::PANEL_TYPE_INFO
        );
    }


    /**
     * @param $GenderId
     * @param $Ranking
     *
     * @return SelectBox
     */
    public function getGenderSelectBox($GenderId, $Ranking, $Type)
    {
        $global = $this->getGlobal();
        $global->POST['Data'][$Type . $Ranking]['Gender'] = $GenderId;
        $global->savePost();

        $tblCommonGenderAll = Common::useService()->getCommonGenderAll(true);

        if($Type == 'C'){
            return new SelectBox('Data[C' . $Ranking . '][Gender]', 'Geschlecht', array('{{ Name }}' => $tblCommonGenderAll)
                , null, true, null);
        }
        return new SelectBox('Data[S' . $Ranking . '][Gender]', 'Geschlecht', array('{{ Name }}' => $tblCommonGenderAll)
            , null, true, null);
    }

    /**
     * @param array $PersonIdList
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendFamilyAddressCreate($PersonIdList = array(), $Data = null)
    {
        $stage = new Stage('Familie', 'Kontaktdaten anlegen');

        $columns = array();
        foreach ($PersonIdList as $Id) {
            if (($tblPerson = Person::useService()->getPersonById($Id))) {
                if (Group::useService()->existsGroupPerson(Group::useService()->getGroupByMetaTable('STUDENT'), $tblPerson)) {
                    $title = 'Schüler';
                } elseif (Group::useService()->existsGroupPerson(Group::useService()->getGroupByMetaTable('PROSPECT'), $tblPerson)) {
                    $title = 'Interessent';
                } else {
                    $title = 'Sorgeberechtigter';
                }

                $columns[] = new LayoutColumn(
                    new Panel(
                        $title,
                        $tblPerson->getFullName(),
                        Panel::PANEL_TYPE_INFO
                    )
                , 3);
            }
        }

        $form = $this->getFamilyAddressForm($PersonIdList, null, null);

        $stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow($columns)))
            . new Well(Person::useService()->createFamilyContact($form, $PersonIdList, $Data))
        );

        return $stage;
    }

    /**
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     *
     * @return Form
     */
    public function getFamilyAddressForm($PersonIdList, $Data, $Errors)
    {
        if ($Data) {
            if ($Errors) {
                $formRows[] = new FormRow(new FormColumn(
                    new Danger('Die Daten wurden nicht gespeichert. Bitte überprüfen Sie die unteren Fehlermeldungen.', new Exclamation())
                ));
            }

            $countContactTypes = $this->getCountContactTypes($Data);
            foreach($Data as $key => $item) {
                $type = substr($key, 0, 1);
                $ranking = substr($key, 1);

                if ($type == 'A') {
                    // Adressdaten
                    if ($ranking == 1) {
                        $formRows[] = new FormRow(new FormColumn(
                            new \SPHERE\Common\Frontend\Form\Repository\Title(new MapMarker() . ' Adressdaten')
                        ));
                    }
                    $formRows[] = new FormRow(new FormColumn(
                        ApiFamilyEdit::receiverBlock($this->getAddressContent($ranking, $PersonIdList, $Data, $Errors,
                        $countContactTypes[$type] == $ranking), 'AddressContent_' . $ranking)
                    ));
                }

                if ($type == 'P') {
                    // Telefonnummern
                    if ($ranking == 1) {
                        $formRows[] = new FormRow(new FormColumn(
                            new \SPHERE\Common\Frontend\Form\Repository\Title(new \SPHERE\Common\Frontend\Icon\Repository\Phone() . ' Telefonnummern')
                        ));
                    }
                    $formRows[] = new FormRow(new FormColumn(
                        ApiFamilyEdit::receiverBlock($this->getPhoneContent($ranking, $PersonIdList, $Data, $Errors,
                            $countContactTypes[$type] == $ranking), 'PhoneContent_' . $ranking)
                    ));
                }

                if ($type == 'M') {
                    // Emailadressen
                    if ($ranking == 1) {
                        $formRows[] = new FormRow(new FormColumn(
                            new \SPHERE\Common\Frontend\Form\Repository\Title(new Envelope() . ' E-Mail Adressen')
                        ));
                    }
                    $formRows[] = new FormRow(new FormColumn(
                        ApiFamilyEdit::receiverBlock($this->getMailContent($ranking, $PersonIdList, $Data, $Errors,
                            $countContactTypes[$type] == $ranking), 'MailContent_' . $ranking)
                    ));
                }
            }
        } else {
            $formRows[] = new FormRow(new FormColumn(
                new \SPHERE\Common\Frontend\Form\Repository\Title(new MapMarker() . ' Adressdaten')
            ));
            $formRows[] = new FormRow(new FormColumn(
                ApiFamilyEdit::receiverBlock($this->getAddressContent(1, $PersonIdList, $Data, $Errors), 'AddressContent_1')
            ));

            $formRows[] = new FormRow(new FormColumn(
                new \SPHERE\Common\Frontend\Form\Repository\Title(new \SPHERE\Common\Frontend\Icon\Repository\Phone() . ' Telefonnummern')
            ));
            $formRows[] = new FormRow(new FormColumn(
                ApiFamilyEdit::receiverBlock($this->getPhoneContent(1, $PersonIdList, $Data, $Errors), 'PhoneContent_1')
            ));

            $formRows[] = new FormRow(new FormColumn(
                new \SPHERE\Common\Frontend\Form\Repository\Title(new Envelope() . ' E-Mail Adressen')
            ));
            $formRows[] = new FormRow(new FormColumn(
                ApiFamilyEdit::receiverBlock($this->getMailContent(1, $PersonIdList, $Data, $Errors), 'MailContent_1')
            ));
        }

        $formRows[] = new FormRow(new FormColumn(
            new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save())
        ));

        return new Form(new FormGroup($formRows));
    }

    /**
     * @param $Data
     *
     * @return array
     */
    private function getCountContactTypes($Data)
    {
        $count['A'] = 0;
        $count['P'] = 0;
        $count['M'] = 0;

        foreach($Data as $key => $item) {
            $type = substr($key, 0, 1);
            $count[$type]++;
        }

        return $count;
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     * @param boolean $hasAddButton
     *
     * @return string
     */
    public function getAddressContent($Ranking, $PersonIdList, $Data, $Errors, $hasAddButton = true)
    {
        $tblType = Address::useService()->getTypeAll();
        $tblViewAddressToPersonAll = Address::useService()->getViewAddressToPersonAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));

        $key = 'A' . $Ranking;

        $layoutLeft = new Layout(array(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    $this->getInputField('SelectBox', $key, 'Type', 'Typ', '', true, $Errors,
                        array('{{ Name }} {{ Description }}' => $tblType), new TileBig())
                    , 4),
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'StreetName', 'Straße', 'Straße', true, $Errors,
                        array('AddressStreetName' => $tblViewAddressToPersonAll), new MapMarker())
                    , 4),
                new LayoutColumn(
                    $this->getInputField('TextField', $key, 'StreetNumber', 'Hausnummer', 'Hausnummer', true, $Errors,
                        array(), new MapMarker())
                    , 4),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'CityCode', 'Postleitzahl', 'Postleitzahl', true, $Errors,
                        array('CityCode' => $tblViewAddressToPersonAll), new MapMarker())
                    , 4),
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'CityName', 'Ort', 'Ort', true, $Errors,
                        array('CityName' => $tblViewAddressToPersonAll), new MapMarker())
                    , 4),
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'CityDistrict', 'Ortsteil', 'Ortsteil', false, $Errors,
                        array('CityDistrict' => $tblViewAddressToPersonAll), new MapMarker())
                    , 4),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'County', 'Landkreis', 'Landkreis', false, $Errors,
                        array('AddressCounty' => $tblViewAddressToPersonAll), new Map())
                    , 4),
                new LayoutColumn(
                    $this->getInputField('SelectBox', $key, 'State', 'Bundesland', '', false, $Errors,
                        array('Name' => $tblState), new Map())
                    , 4),
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'Nation', 'Land', 'Land', false, $Errors,
                        array('AddressNation' => $tblViewAddressToPersonAll), new Map())
                    , 4),
            ))
        ))));

        return new Panel(
            'Neue Adresse',
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($layoutLeft, 9),
                    new LayoutColumn(
                        new TextArea('Data[A' . $Ranking . '][Remark]', 'Bemerkungen', 'Bemerkungen', new Edit(), 8)
                    , 3),
                )),
                new LayoutRow(new LayoutColumn($this->getPersonOptions('Data[A' . $Ranking . '][PersonList]', $PersonIdList)))
            )))
            . (isset($Errors[$key]['Message'])
                ? new Danger($Errors[$key]['Message'], new Exclamation())
                : ''
            ),
            Panel::PANEL_TYPE_INFO
        )
        . ($hasAddButton && isset($Errors['Address']) ? new Danger(implode('</br>', $Errors['Address'])) : '')
        . ($hasAddButton
            ? ApiFamilyEdit::receiverBlock(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        (new Primary('', ApiFamilyEdit::getEndpoint(), new Plus(), array(), 'Eine weitere Adresse hinzufügen'))
                            ->ajaxPipelineOnClick(
                                (new ApiFamilyEdit)->pipelineLoadAddressContent(($Ranking + 1), $PersonIdList, $Data, $Errors)
                            )
                        , 1),
                    new LayoutColumn(
                        new Container('&nbsp;')
                    )
                )))),
                'AddressContent_' . ($Ranking + 1))
            : '');
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     * @param boolean $hasAddButton
     *
     * @return string
     */
    public function getPhoneContent($Ranking, $PersonIdList, $Data, $Errors, $hasAddButton = true)
    {
        $tblPhoneAll = Phone::useService()->getPhoneAll();
        $tblTypeAll = Phone::useService()->getTypeAll();

        $key = 'P' . $Ranking;

        return new Panel(
            'Neue Telefonnummer',
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $this->getInputField('SelectBox', $key, 'Type', 'Typ', '', true, $Errors,
                        array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig())
                    , 3),
                new LayoutColumn(
                    $this->getInputField('AutoCompleter', $key, 'Number', 'Telefonnummer', 'Telefonnummer', true, $Errors,
                        array('Number' => $tblPhoneAll), new \SPHERE\Common\Frontend\Icon\Repository\Phone())
                    , 3),
                new LayoutColumn(
                    $this->getPersonOptions('Data[P' . $Ranking . '][PersonList]', $PersonIdList)
                    , 3),
                new LayoutColumn(
                    new TextArea('Data[P' . $Ranking . '][Remark]', 'Bemerkungen', 'Bemerkungen', new Edit(), 2)
                , 3),
            ))))
            . (isset($Errors[$key]['Message'])
                ? new Danger($Errors[$key]['Message'], new Exclamation())
                : ''
            ),
            Panel::PANEL_TYPE_INFO
        )
        . ($hasAddButton
            ? ApiFamilyEdit::receiverBlock(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        (new Primary('', ApiFamilyEdit::getEndpoint(), new Plus(), array(), 'Eine weitere Telefonnummer hinzufügen'))
                            ->ajaxPipelineOnClick(
                                (new ApiFamilyEdit)->pipelineLoadPhoneContent(($Ranking + 1), $PersonIdList, $Data, $Errors)
                            )
                        , 1),
                    new LayoutColumn(
                        new Container('&nbsp;')
                    )
                )))),
                'PhoneContent_' . ($Ranking + 1))
            : ''
        );
    }

    /**
     * @param $Ranking
     * @param $PersonIdList
     * @param $Data
     * @param $Errors
     * @param boolean $hasAddButton
     *
     * @return string
     */
    public function getMailContent($Ranking, $PersonIdList, $Data, $Errors, $hasAddButton = true)
    {
        $tblTypeAll = Mail::useService()->getTypeAll();

        $key = 'M' . $Ranking;

        if(($tblConsumer = GatekeeperConsumer::useService()->getConsumerBySession())
            && GatekeeperConsumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)
        ){
            $hasAccountOptions = true;
        } else {
            $hasAccountOptions = false;
        }

        return new Panel(
            'Neue E-Mail Adresse',
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $this->getInputField('SelectBox', $key, 'Type', 'Typ', '', true, $Errors,
                            array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig())
                        , 3),
                    new LayoutColumn(
                        $this->getInputField('MailField', $key, 'Address', 'E-Mail Adresse', 'E-Mail Adresse', true, $Errors,
                            array(), new \SPHERE\Common\Frontend\Icon\Repository\Mail())
                        , 3),
                    new LayoutColumn(
                        $this->getPersonOptions('Data[M' . $Ranking . '][PersonList]', $PersonIdList)
                        , 3),
                    new LayoutColumn(
                        new TextArea('Data[M' . $Ranking . '][Remark]', 'Bemerkungen', 'Bemerkungen', new Edit(), 2)
                        , 3),
                )),
                $hasAccountOptions
                    ? new LayoutRow(array(
                        new LayoutColumn(
                            new CheckBox('Data[M' . $Ranking . '][IsAccountUserAlias]', 'E-Mail als späteren UCS Benutzernamen verwenden', 1)
                            , 3),
                        new LayoutColumn(
                            new CheckBox('Data[M' . $Ranking . '][IsAccountRecoveryMail]', 'E-Mail als späteres UCS "Passwort vergessen" verwenden', 1)
                            , 3)
                    ))
                    : null
            )))
            . (isset($Errors[$key]['Message'])
                ? new Danger($Errors[$key]['Message'], new Exclamation())
                : ''
            ),
            Panel::PANEL_TYPE_INFO
        )
        . ($hasAddButton
            ? ApiFamilyEdit::receiverBlock(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        (new Primary('', ApiFamilyEdit::getEndpoint(), new Plus(), array(), 'Eine weitere E-Mail Adresse hinzufügen'))
                            ->ajaxPipelineOnClick(
                                (new ApiFamilyEdit)->pipelineLoadMailContent(($Ranking + 1), $PersonIdList, $Data, $Errors)
                            )
                        , 1),
                    new LayoutColumn(
                        new Container('&nbsp;')
                    )
                )))),
                'MailContent_' . ($Ranking + 1))
            : '');
    }

    /**
     * @param $inputType
     * @param $key
     * @param $identifier
     * @param $label
     * @param $placeholder
     * @param $isRequired
     * @param $Errors
     * @param array $data
     * @param null $icon
     *
     * @return AbstractField
     */
    public function getInputField($inputType, $key, $identifier, $label, $placeholder, $isRequired, $Errors, $data = array(), $icon = null)
    {
        switch ($inputType) {
            case 'SelectBox': $inputField = new SelectBox('Data[' . $key . '][' . $identifier . ']',
                $label . ($isRequired ? ' ' . new DangerText('*') : ''), $data, $icon);
                break;
            case 'AutoCompleter': $inputField = new AutoCompleter('Data[' . $key . '][' . $identifier . ']',
                $label . ($isRequired ? ' ' . new DangerText('*') : ''), $placeholder, $data, $icon);
                break;
            case 'MailField': $inputField = new MailField('Data[' . $key . '][' . $identifier . ']', $placeholder,
                $label . ($isRequired ? ' ' . new DangerText('*') : ''), $icon);
                break;
            case 'TextField':
            default: $inputField = new TextField('Data[' . $key . '][' . $identifier . ']', $placeholder,
                $label . ($isRequired ? ' ' . new DangerText('*') : ''), $icon);
        }

        if (isset($Errors[$key][$identifier])) {
            if ($Errors[$key][$identifier]['IsError'] == true) {
                $inputField->setError($Errors[$key][$identifier]['Message']);
            } else {
                $inputField->setSuccess($Errors[$key][$identifier]['Message']);
            }
        }

        return $inputField;
    }

    /**
     * @param $Identifier
     * @param $PersonIdList
     *
     * @return Layout
     */
    private function getPersonOptions($Identifier, $PersonIdList)
    {
        $rows = array();
        foreach($PersonIdList as $Id) {
            if (($tblPerson = Person::useService()->getPersonById($Id))) {
                $rows[] = new LayoutRow(new LayoutColumn(
                    new CheckBox($Identifier . '[' . $tblPerson->getId() . ']', $tblPerson->getFullName(), 1)
                ));
            }
        }

        return new Layout(new LayoutGroup($rows));
    }
}