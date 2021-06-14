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
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\MailField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
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
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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

        $stage->setContent(new Well(Person::useService()->createFamily($this->formCreateFamily(), $Data)));

        return $stage;
    }

    /**
     * @param $Person
     *
     * @return string
     */
    public function loadSimilarPersonContent($Person)
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

                return ApiFamilyEdit::pipelineLoadSimilarPersonMessage(count($TableList), '', $Table->getHash()) . (string)$Table; // . $InfoPersonPipeline;
            }

            return ApiFamilyEdit::pipelineLoadSimilarPersonMessage(0, $Person['FirstName'] . ' ' . $Person['LastName'], '') . '';
        }
    }

    /**
     * @param $countSimilarPerson
     * @param $name
     * @param $hash
     *
     * @return Danger|Success
     */
    public static function getSimilarPersonMessage($countSimilarPerson, $name, $hash) {
        if ($countSimilarPerson > 0) {
            return new Danger(new Bold($countSimilarPerson . ' Personen mit ähnlichem Namen gefunden. Ist diese Person schon angelegt?')
                . new Link('Zur Liste springen', null, null, array(), false, $hash)
            );
        } else {
            return new Success('Keine Personen zu ' . $name . ' gefunden');
        }
    }

    /**
     * @return Form
     */
    public function formCreateFamily()
    {
        $tblSalutationAll = Person::useService()->getSalutationAll();

        $panelChild = ApiFamilyEdit::receiverBlock($this->getChildContent(1), 'ChildContent_1');
        $form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $panelChild
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $this->getPanelCustody(1, $tblSalutationAll)
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $this->getPanelCustody(2, $tblSalutationAll)
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()))
                    ),
                )),
            ))
        ));

        return $form;
    }

    /**
     * @param $Ranking
     *
     * @return string
     */
    public function getChildContent($Ranking)
    {
        $tblCommonBirthDatesAll = Common::useService()->getCommonBirthDatesAll();
        $tblBirthplaceAll = array();
        if ($tblCommonBirthDatesAll) {
            array_walk($tblCommonBirthDatesAll,
                function (TblCommonBirthDates &$tblCommonBirthDates) use (&$tblBirthplaceAll) {

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
            $global->POST['Data']['C' . $Ranking]['Group'] = $tblGroupProspect->getId();
            $global->POST['Data']['C' . $Ranking]['IsSibling'] = 1;

            $global->savePost();
        }

        $firstNameInput = (new TextField('Data[C' . $Ranking . '][FirstName]', 'Vorname', 'Vorname'));
        $lastNameInput = (new TextField('Data[C' . $Ranking . '][LastName]', 'Nachname', 'Nachname'));

        $firstNameInput->ajaxPipelineOnKeyUp(ApiFamilyEdit::pipelineLoadSimilarPersonContent());
        $lastNameInput->ajaxPipelineOnKeyUp(ApiFamilyEdit::pipelineLoadSimilarPersonContent());

        $tblCommonGenderAll = Common::useService()->getCommonGenderAll();

        $title = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn('Kind', 3),
            new LayoutColumn(new CheckBox('Data[C' . $Ranking . '][IsSibling]', 'Geschwisterkind', 1), 3)
        ))));

        return new Panel(
            $title,
            new Layout(new LayoutGroup(array(
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
                        new SelectBox('Data[C' . $Ranking . '][Gender]', 'Geschlecht', array('{{ Name }}' => $tblCommonGenderAll), new Child()), 3
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
                        ApiFamilyEdit::receiverBlock('', 'SimilarPersonMessage')
                    )
                )))
            ))),
            Panel::PANEL_TYPE_INFO
        )
            . ApiFamilyEdit::receiverBlock(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        (new Primary('', ApiFamilyEdit::getEndpoint(), new Plus(), array(), 'Ein weiteres Kind hinzufügen'))
                            ->ajaxPipelineOnClick(
                                (new ApiFamilyEdit)->pipelineLoadChildContent(($Ranking + 1))
                            )
                    , 1),
                    new LayoutColumn(
                        new Container('&nbsp;')
                    )
                )))),
                'ChildContent_' . ($Ranking + 1)
            );
    }

    /**
     * @param int $Ranking
     * @param TblSalutation|false $tblSalutationAll
     * @return Panel
     */
    private function getPanelCustody($Ranking, $tblSalutationAll)
    {
        $title = new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn('Sorgeberechtigter - S' . $Ranking, 3),
            new LayoutColumn(new CheckBox('Data[S' . $Ranking . '][IsSingleParent]', 'alleinerziehend', 1), 3)
        ))));

        $genderReceiver = ApiFamilyEdit::receiverBlock($this->getGenderSelectBox(0, $Ranking), 'SelectedGender' . $Ranking);

        return new Panel(
            $title,
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        (new SelectBox('Data[S' . $Ranking . '][Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
                            new Conversation()))->ajaxPipelineOnChange(ApiFamilyEdit::pipelineChangeSelectedGender($Ranking))
                        , 3
                    ),
                    new LayoutColumn(
                        (new AutoCompleter('Data[S' . $Ranking . '][Title]', 'Titel', 'Titel', array('Dipl.- Ing.'), new Conversation())), 3
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        (new TextField('Data[S' . $Ranking . '][FirstName]', 'Vorname', 'Vorname')), 3
//                        (new TextField('Data[S' . $Ranking . '_FirstName]', 'Vorname', 'Vorname')), 3
                    ),
                    new LayoutColumn(
                        (new TextField('Data[S' . $Ranking . '][LastName]', 'Nachname', 'Nachname')), 3
                    ),
                    new LayoutColumn(
                        new TextField('Data[S' . $Ranking . '][BirthName]', 'Geburtsname', 'Geburtsname'), 3
                    ),
                    new LayoutColumn(
                        $genderReceiver, 3
                    ),
                )),
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
    public function getGenderSelectBox($GenderId, $Ranking)
    {
        $global = $this->getGlobal();
        $global->POST['Data']['S' . $Ranking]['Gender'] = $GenderId;
        $global->savePost();

        $tblCommonGenderAll = Common::useService()->getCommonGenderAll();

        return new SelectBox('Data[S' . $Ranking . '][Gender]', 'Geschlecht', array('{{ Name }}' => $tblCommonGenderAll), new Child());
    }

    /**
     * @return Stage
     */
    public function frontendFamilyAddressCreate()
    {
        $stage = new Stage('Familie', 'Kontaktdaten anlegen');

        $stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel(
                        'Interessent',
                        'Max Mustermann',
                        Panel::PANEL_TYPE_INFO
                    )
                , 3),
                new LayoutColumn(
                    new Panel(
                        'S1',
                        'Frau Theresa Mustermann',
                        Panel::PANEL_TYPE_INFO
                    )
                , 3),
                new LayoutColumn(
                    new Panel(
                        'S2',
                        'Herr Theo Mustermann',
                        Panel::PANEL_TYPE_INFO
                    )
                , 3),
            ))))
            . new Well(
                new Form(new FormGroup(array(
                    new FormRow(new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new MapMarker() . ' Adressdaten')
//                            , new Link('weitere Adresse hinzufügen', '', new Plus()))
                    )),
                    new FormRow(new FormColumn(
                         $this->getAddressPanel()
                    )),
                    new FormRow(new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new \SPHERE\Common\Frontend\Icon\Repository\Phone() . ' Telefonnummern')
                    )),
                    new FormRow(new FormColumn(
                        $this->getPhonePanel()
                    )),
                    new FormRow(new FormColumn(
                        $this->getPhonePanel()
                    )),
                    new FormRow(new FormColumn(
                        $this->getPhonePanel()
                    )),
                    new FormRow(new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Title(new \SPHERE\Common\Frontend\Icon\Repository\Mail() . ' E-Mail Adressen')
                    )),
                    new FormRow(new FormColumn(
                        $this->getMailPanel()
                    )),
                    new FormRow(new FormColumn(
                        $this->getMailPanel()
                    )),
                    new FormRow(new FormColumn(
                        $this->getMailPanel()
                    )),
                    new FormRow(new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save())
                    ))
                )))
            )
        );

        return $stage;
    }

    /**
     * @return Panel
     */
    private function getAddressPanel()
    {
        $tblType = Address::useService()->getTypeAll();
        $tblViewAddressToPersonAll = Address::useService()->getViewAddressToPersonAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));

        $layoutLeft = new Layout(array(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    (new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblType), new TileBig(), true))->setRequired(), 4
                ),
                new LayoutColumn(
                    (new AutoCompleter('Street[Name]', 'Straße', 'Straße', array('AddressStreetName' => $tblViewAddressToPersonAll), new MapMarker()))->setRequired(), 4
                ),
                new LayoutColumn(
                    (new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker()))->setRequired(), 4
                ),
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    (new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl', array('CityCode' => $tblViewAddressToPersonAll), new MapMarker()))->setRequired(), 4
                ),
                new LayoutColumn(
                    (new AutoCompleter('City[Name]', 'Ort', 'Ort', array('CityName' => $tblViewAddressToPersonAll), new MapMarker()))->setRequired(), 4
                ),
                new LayoutColumn(
                    new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil', array('CityDistrict' => $tblViewAddressToPersonAll), new MapMarker()), 4
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new AutoCompleter('County', 'Landkreis', 'Landkreis', array('AddressCounty' => $tblViewAddressToPersonAll), new Map()), 4
                ),
                new LayoutColumn(
                    new SelectBox('State', 'Bundesland', array('Name' => $tblState), new Map()), 4
                ),
                new LayoutColumn(
                    new AutoCompleter('Nation', 'Land', 'Land', array('AddressNation' => $tblViewAddressToPersonAll), new Map()), 4
                )
            ))
        ))));

        return new Panel(
            'Neue Adresse',
            array(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn($layoutLeft, 9),
                    new LayoutColumn(
//                        new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit(), 8)
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(
                                new CheckBox('Data[Id]', 'Max Mustermann', 1)
                            )),
                            new LayoutRow(new LayoutColumn(
                                new CheckBox('Data[Id]', 'Frau Theresa Mustermann (S1)', 1)
                            )),
                            new LayoutRow(new LayoutColumn(
                                new CheckBox('Data[Id]', 'Herr Theo Mustermann (S2)', 1)
                            )),
                        )))
                    , 3),
                )))),
            ),
            Panel::PANEL_TYPE_INFO
        );
    }

    private function getPhonePanel()
    {
        $tblPhoneAll = Phone::useService()->getPhoneAll();
        $tblTypeAll = Phone::useService()->getTypeAll();

        return new Panel(
            'Neue Telefonnummer',
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    (new SelectBox('Type[Type]', 'Typ',
                        array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                    ))->setRequired()
                , 3),
                new LayoutColumn(
                    (new AutoCompleter('Number', 'Telefonnummer', 'Telefonnummer',
                        array('Number' => $tblPhoneAll), new \SPHERE\Common\Frontend\Icon\Repository\Phone()
                    ))->setRequired()
                , 3),
                new LayoutColumn(
                    '&nbsp;'
                    , 3),
                new LayoutColumn(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            new CheckBox('Data[Id]', 'Max Mustermann', 1)
                        )),
                        new LayoutRow(new LayoutColumn(
                            new CheckBox('Data[Id]', 'Frau Theresa Mustermann (S1)', 1)
                        )),
                        new LayoutRow(new LayoutColumn(
                            new CheckBox('Data[Id]', 'Herr Theo Mustermann (S2)', 1)
                        )),
                    )))
                , 3),
//                new LayoutColumn(
//                    new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
//                , 3),
            )))),
            Panel::PANEL_TYPE_INFO
        );
    }

    private function getMailPanel()
    {
        $tblTypeAll = Mail::useService()->getTypeAll();

        return new Panel(
            'Neue Email-Adresse',
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    (new SelectBox('Type[Type]', 'Typ',
                        array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                    ))->setRequired()
                    , 3),
                new LayoutColumn(
                    (new MailField('Address', 'E-Mail Adresse', 'E-Mail Adresse',
                        new \SPHERE\Common\Frontend\Icon\Repository\Mail()))->setRequired()
                    , 3),
                new LayoutColumn(
                    '&nbsp;'
                , 3),
                new LayoutColumn(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            new CheckBox('Data[Id]', 'Max Mustermann', 1)
                        )),
                        new LayoutRow(new LayoutColumn(
                            new CheckBox('Data[Id]', 'Frau Theresa Mustermann (S1)', 1)
                        )),
                        new LayoutRow(new LayoutColumn(
                            new CheckBox('Data[Id]', 'Herr Theo Mustermann (S2)', 1)
                        )),
                    )))
                    , 3),
//                new LayoutColumn(
//                    new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
//                    , 3),
            )))),
            Panel::PANEL_TYPE_INFO
        );
    }
}