<?php
namespace SPHERE\Application\People\Person\Frontend;

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
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Window\Stage;

/**
 * Class FrontendFamily
 *
 * @package SPHERE\Application\People\Person\Frontend
 */
class FrontendFamily extends FrontendReadOnly
{
    /**
     * @return Stage
     */
    public function frontendFamilyCreate()
    {
        $stage = new Stage('Familie', 'Personen anlegen');

        $stage->setContent(new Well($this->formCreateFamily()));

        return $stage;
    }

    /**
     * @return Form
     */
    public function formCreateFamily()
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

        $tblSalutationAll = Person::useService()->getSalutationAll();

        $tblGroupList = array();
        if (($tblGroupStudent = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            $tblGroupList[] = $tblGroupStudent;
        }
        if (($tblGroupProspect = Group::useService()->getGroupByMetaTable('PROSPECT'))) {
            $tblGroupList[] = $tblGroupProspect;

            $global = $this->getGlobal();
            $global->POST['Data']['Child']['Group'] = $tblGroupProspect->getId();
            $global->savePost();
        }

        $panelChild = new Panel(
            'Kind',
            array(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            (new TextField('Data[Child][FirstName]', 'Vorname', 'Vorname'))->setRequired(), 3
                        ),
                        new LayoutColumn(
                            (new TextField('Data[Child][Name]', 'Name', 'Name'))->setRequired(), 3
                        ),
                        new LayoutColumn(
                            new TextField('Data[Child][SecondName]', 'weitere Vornamen', 'Zweiter Vorname'), 3
                        ),
                        new LayoutColumn(
                            new TextField('Data[Child][CallName]', 'Rufname', 'Rufname'), 3
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new SelectBox('Data[Child][Group]', 'Gruppe', array('{{ Name }}' => $tblGroupList)), 3
                        ),
                        new LayoutColumn(
                            new DatePicker('Data[Child][Birthday]', 'Geburtstag', 'Geburtstag', new Calendar()), 3
                        ),
                        new LayoutColumn(
                            new AutoCompleter('Data[Child][Birthplace]', 'Geburtsort', 'Geburtsort', $tblBirthplaceAll, new MapMarker()), 3
                        ),
                        new LayoutColumn(
                            new SelectBox('Data[Child][Gender]', 'Geschlecht', array(
                                TblCommonBirthDates::VALUE_GENDER_NULL => '',
                                TblCommonBirthDates::VALUE_GENDER_MALE => 'Männlich',
                                TblCommonBirthDates::VALUE_GENDER_FEMALE => 'Weiblich'
                            ), new Child()), 3
                        ),
                    ))
                )))
            ),
            Panel::PANEL_TYPE_INFO
        );

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
                        (new Primary('Speichern', '', new Save()))
//                            ApiPersonEdit::getEndpoint(), new Save()))->ajaxPipelineOnClick(ApiPersonEdit::pipelineSaveBasicContent($tblPerson ? $tblPerson->getId() : 0)),
                    ),
                )),
            ))
        ));

        return $form;
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
            new LayoutColumn(new CheckBox('Data[S' . $Ranking . '][SingleParent]', 'alleinerziehend', 1), 3)
        ))));

        return new Panel(
            $title,
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        (new SelectBox('Data[S' . $Ranking . '][Salutation]', 'Anrede', array('Salutation' => $tblSalutationAll),
                            new Conversation())) //->setTabIndex(1)->ajaxPipelineOnChange(ApiPersonEdit::pipelineChangeSelectedGender())
                        , 3
                    ),
                    new LayoutColumn(
                        (new AutoCompleter('Data[S' . $Ranking . '][Title]', 'Titel', 'Titel', array('Dipl.- Ing.'), new Conversation())), 3
                    ),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        (new TextField('Data[S' . $Ranking . '][FirstName]', 'Vorname', 'Vorname'))->setRequired(), 3
                    ),
                    new LayoutColumn(
                        (new TextField('Data[S' . $Ranking . '][Name]', 'Name', 'Name'))->setRequired(), 3
                    ),
                    new LayoutColumn(
                        new TextField('Data[S' . $Ranking . '][BirthName]', 'Geburtsname', 'Geburtsname'), 3
                    ),
                    new LayoutColumn(
                        new SelectBox('Data[S' . $Ranking . '][Gender]', 'Geschlecht', array(
                            TblCommonBirthDates::VALUE_GENDER_NULL => '',
                            TblCommonBirthDates::VALUE_GENDER_MALE => 'Männlich',
                            TblCommonBirthDates::VALUE_GENDER_FEMALE => 'Weiblich'
                        ), new Child()), 3
                    ),
                )),
            ))),
            Panel::PANEL_TYPE_INFO
        );
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