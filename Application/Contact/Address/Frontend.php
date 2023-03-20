<?php
namespace SPHERE\Application\Contact\Address;

use SPHERE\Application\Api\Contact\ApiAddressToCompany;
use SPHERE\Application\Api\Contact\ApiAddressToPerson;
use SPHERE\Application\Api\Contact\ApiContactDetails;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Map;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Address
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $PersonId
     * @param null $ToPersonId
     * @param bool $setPost
     * @param bool $showRelationships
     * @param null $OnlineContactId
     * @param bool $isOnlineContactPosted
     *
     * @return Form|Danger
     */
    public function formAddressToPerson($PersonId, $ToPersonId = null, $setPost = false, $showRelationships = false,
        $OnlineContactId = null, $isOnlineContactPosted = false)
    {

        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Die Person wurde nicht gefunden', new Exclamation());
        }

        // meldung nach der Überprüfung der Input-Felder (Pflichtfelder) weiterhin anzeigen
        $Relationship = null;
        if ($showRelationships) {
            $global = $this->getGlobal();
            if (isset($global->POST['Relationship'])) {
                $Relationship = $global->POST['Relationship'];
            }
        }

        $tblOnlineContact = $OnlineContactId ? OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId) : false;

        if ($ToPersonId && ($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();

                if ($isOnlineContactPosted) {
                    $tblAddress = $tblOnlineContact->getServiceTblContact();
                } else {
                    $tblAddress =  $tblToPerson->getTblAddress();
                }
                $Global->POST['Street']['Name'] = $tblAddress->getStreetName();
                $Global->POST['Street']['Number'] = $tblAddress->getStreetNumber();
                $Global->POST['City']['Code'] = $tblAddress->getTblCity()->getCode();
                $Global->POST['City']['Name'] = $tblAddress->getTblCity()->getName();
                $Global->POST['City']['District'] = $tblAddress->getTblCity()->getDistrict();

                if ($tblToPerson->getTblAddress()->getTblState()) {
                    $Global->POST['State'] = $tblToPerson->getTblAddress()->getTblState()->getId();
                }
                $Global->POST['Region'] = $tblAddress->getRegion();
                $Global->POST['County'] = $tblAddress->getCounty();
                $Global->POST['Nation'] = $tblAddress->getNation();

                $Global->savePost();
            }
        } elseif ($tblOnlineContact) {
            if ($setPost) {
                $Global = $this->getGlobal();
                /** @var TblAddress $tblAddress */
                if (($tblAddress = $tblOnlineContact->getServiceTblContact())) {
                    $Global->POST['Street']['Name'] = $tblAddress->getStreetName();
                    $Global->POST['Street']['Number'] = $tblAddress->getStreetNumber();
                    $Global->POST['City']['Code'] = $tblAddress->getTblCity()->getCode();
                    $Global->POST['City']['Name'] = $tblAddress->getTblCity()->getName();
                    $Global->POST['City']['District'] = $tblAddress->getTblCity()->getDistrict();
                }
                $Global->savePost();
            }
        }

        $tblViewAddressToPersonAll = Address::useService()->getViewAddressToPersonAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));
        $tblType = Address::useService()->getTypeAll();

        if ($ToPersonId) {
            $saveButton = (new PrimaryLink('Speichern', ApiAddressToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineEditAddressToPersonSave($PersonId, $ToPersonId, $OnlineContactId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiAddressToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineCreateAddressToPersonSave($PersonId, $OnlineContactId));
        }

        $leftPanel = new Panel('Anschrift', array(
            (new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblType),
                new TileBig(), true))
                ->setRequired()
                ->ajaxPipelineOnChange(ApiAddressToPerson::pipelineLoadRelationshipsContent($PersonId,
                    $isOnlineContactPosted && $tblOnlineContact ? $OnlineContactId : null)),
            (new AutoCompleter('Street[Name]', 'Straße', 'Straße',
                array('AddressStreetName' => $tblViewAddressToPersonAll), new MapMarker()
            ))->setRequired(),
            (new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker()))->setRequired()
        ), Panel::PANEL_TYPE_INFO);

        $centerPanelContent[] = (new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl',array('CityCode' => $tblViewAddressToPersonAll), new MapMarker()
        ))->setRequired();
        $centerPanelContent[] = (new AutoCompleter('City[Name]', 'Ort', 'Ort', array('CityName' => $tblViewAddressToPersonAll), new MapMarker()))->setRequired();
        $centerPanelContent[] = new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil', array('CityDistrict' => $tblViewAddressToPersonAll), new MapMarker());
        $centerPanelContent[] = new AutoCompleter('County', 'Landkreis', 'Landkreis', array('AddressCounty' => $tblViewAddressToPersonAll), new Map());
        $centerPanelContent[] = new SelectBox('State', 'Bundesland', array('Name' => $tblState), new Map());
        $centerPanelContent[] = new AutoCompleter('Nation', 'Land', 'Land', array('AddressNation' => $tblViewAddressToPersonAll), new Map());
        $centerPanel = new Panel('Stadt', $centerPanelContent, Panel::PANEL_TYPE_INFO);

        if($setPost && isset($tblToPerson) && $tblToPerson && isset($tblAddress)){
            // Verwendbar nur bei vorhandenen Bezirken
            if(($tblCity = $tblAddress->getTblCity()) && ($tblRegionList = Address::useService()->getRegionListByCode($tblCity->getCode()))){
                $RegionList = array();
                foreach ($tblRegionList as $tblRegion){
                    $RegionList[] = $tblRegion->getName();
                }
                $tblRegionAll = Address::useService()->getRegionAll();
                $rightPanelContent[] = new AutoCompleter('Region', 'Bezirk', implode(', ', $RegionList), array('Name' => $tblRegionAll), new Map());
            }
        }
        $rightPanelContent[] = new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit());
        $rightPanel = new Panel('Sonstiges', $rightPanelContent, Panel::PANEL_TYPE_INFO);


        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $leftPanel
                        , 4),
                    new FormColumn(
                        $centerPanel
                        , 4),
                    new FormColumn(
                        $rightPanel
                        , 4),
                )),
                new FormRow(array(
                    new FormColumn(
                        ApiAddressToPerson::receiverBlock(
                            $showRelationships
                                ? Address::useFrontend()->getRelationshipsContent($tblPerson,
                                    $isOnlineContactPosted && $tblOnlineContact ? $tblOnlineContact : null)
                                : ''
                            , 'RelationshipsContent'
                        )
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        ApiAddressToPerson::receiverBlock(
                            (new ApiAddressToPerson())->loadRelationshipsMessage($Relationship),
                            'RelationshipsMessage'
                        )
                    )
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                ))
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param $CompanyId
     * @param null $ToCompanyId
     ** @param bool $setPost
     * @return Form
     */
    public function formAddressToCompany($CompanyId, $ToCompanyId = null, $setPost = false)
    {

        if ($ToCompanyId && ($tblToCompany = Address::useService()->getAddressToCompanyById($ToCompanyId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->POST['Street']['Name'] = $tblToCompany->getTblAddress()->getStreetName();
                $Global->POST['Street']['Number'] = $tblToCompany->getTblAddress()->getStreetNumber();
                $Global->POST['City']['Code'] = $tblToCompany->getTblAddress()->getTblCity()->getCode();
                $Global->POST['City']['Name'] = $tblToCompany->getTblAddress()->getTblCity()->getName();
                $Global->POST['City']['District'] = $tblToCompany->getTblAddress()->getTblCity()->getDistrict();
                if ($tblToCompany->getTblAddress()->getTblState()) {
                    $Global->POST['State'] = $tblToCompany->getTblAddress()->getTblState()->getId();
                }
                $Global->POST['County'] = $tblToCompany->getTblAddress()->getCounty();
                $Global->POST['Nation'] = $tblToCompany->getTblAddress()->getNation();

                $Global->savePost();
            }
        }

        $tblViewAddressToCompanyAll = Address::useService()->getViewAddressToCompanyAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));
        $tblType = Address::useService()->getTypeAll();

        if ($ToCompanyId) {
            $saveButton = (new PrimaryLink('Speichern', ApiAddressToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAddressToCompany::pipelineEditAddressToCompanySave($CompanyId, $ToCompanyId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiAddressToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAddressToCompany::pipelineCreateAddressToCompanySave($CompanyId));
        }
        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anschrift', array(
                            (new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblType),
                                new TileBig(), true))->setRequired(),
                            (new AutoCompleter('Street[Name]', 'Straße', 'Straße',
                                array('AddressStreetName' => $tblViewAddressToCompanyAll), new MapMarker()
                            ))->setRequired(),
                            (new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker()))->setRequired()
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Stadt', array(
                            (new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl',
                                array('CityCode' => $tblViewAddressToCompanyAll), new MapMarker()
                            ))->setRequired(),
                            (new AutoCompleter('City[Name]', 'Ort', 'Ort',
                                array('CityName' => $tblViewAddressToCompanyAll), new MapMarker()
                            ))->setRequired(),
                            new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil',
                                array('CityDistrict' => $tblViewAddressToCompanyAll), new MapMarker()
                            ),
                            new AutoCompleter('County', 'Landkreis', 'Landkreis',
                                array('AddressCounty' => $tblViewAddressToCompanyAll), new Map()
                            ),
                            new SelectBox('State', 'Bundesland',
                                array('Name' => $tblState), new Map()
                            ),
                            new AutoCompleter('Nation', 'Land', 'Land',
                                array('AddressNation' => $tblViewAddressToCompanyAll), new Map()
                            ),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function frontendLayoutPersonNew(TblPerson $tblPerson)
    {

        $addressList = array();
        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson))){
            foreach ($tblAddressList as $tblToPerson) {
                if (($tblAddress = $tblToPerson->getTblAddress())) {
                    $addressList[$tblAddress->getId()][$tblToPerson->getTblType()->getId()][$tblPerson->getId()] = $tblToPerson;
                }
            }
        }

        if (($tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                    if ($tblPerson->getId() != $tblRelationship->getServiceTblPersonFrom()->getId()) {
                        $tblPersonRelationship = $tblRelationship->getServiceTblPersonFrom();
                    } else {
                        $tblPersonRelationship = $tblRelationship->getServiceTblPersonTo();
                    }
                    $tblRelationshipAddressAll = Address::useService()->getAddressAllByPerson($tblPersonRelationship);
                    if ($tblRelationshipAddressAll) {
                        foreach ($tblRelationshipAddressAll as $tblToPerson) {
                            if (($tblAddress = $tblToPerson->getTblAddress())) {
                                $addressList[$tblAddress->getId()][$tblToPerson->getTblType()->getId()][$tblPersonRelationship->getId()] = $tblToPerson;
                            }
                        }
                    }
                }
            }
        }

        if (empty($addressList)) {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine Adressen hinterlegt')))));
        } else {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            foreach ($addressList as $addressId => $typeArray) {
                if (($tblAddress = Address::useService()->getAddressById($addressId))) {
                    foreach ($typeArray as $typeId => $personArray) {
                        if (($tblType = Address::useService()->getTypeById($typeId))) {
                            $content = array();
                            $hasOnlineContacts = false;
                            if (isset($personArray[$tblPerson->getId()])) {
                                /** @var TblToPerson $tblToPerson */
                                $tblToPerson = $personArray[$tblPerson->getId()];
                                $panelType = Panel::PANEL_TYPE_SUCCESS;
                                $options =
                                    (new Link(
                                        new Edit(),
                                        ApiAddressToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Bearbeiten'
                                    ))->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenEditAddressToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ))
                                    . ' | '
                                    . (new Link(
                                        new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                                        ApiAddressToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Löschen'
                                    ))->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenDeleteAddressToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ));
                                $hasOnlineContactsOptions = true;
                            } else {
                                $panelType = Panel::PANEL_TYPE_DEFAULT;

                                // Adresse einer anderen Person hinzufügen
                                $tblToPerson = current($personArray);
                                if ($tblType->getName() != 'Hauptadresse' || !$tblPerson->fetchMainAddress()) {
                                    $options = (new Link(
                                        new Plus(),
                                        ApiAddressToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Diese Adresse der aktuellen Person hinzufügen'
                                    ))->ajaxPipelineOnClick(ApiAddressToPerson::pipelineAddAddressToPerson(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ));
                                } else {
                                    // das Überschreiben der Hauptadresse muss per Modal bestätigt werden
                                    $options = (new Link(
                                        new Plus(),
                                        ApiAddressToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Diese Adresse der aktuellen Person hinzufügen'
                                    ))->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenAddAddressToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ));
                                }
                                $tblToPerson = false;
                                $hasOnlineContactsOptions = false;
                            }

                            $Address = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber().' '.$tblAddress->getPostOfficeBox();
                            if(($tblCity = $tblAddress->getTblCity())){
                                $Address .= new Container($tblCity->getCode().' '.$tblCity->getDisplayName().' '.new Italic($tblAddress->getRegionString()));
                                $Address .= new Container($tblAddress->getLocation());
                            }
                            $content[] = $Address;
                            /**
                             * @var TblToPerson $tblToPersonTemp
                             */
                            foreach ($personArray as $personId => $tblToPersonTemp) {
                                if (($tblPersonAddress = Person::useService()->getPersonById($personId))) {
                                    $content[] = ($tblPerson->getId() != $tblPersonAddress->getId()
                                            ? new Link(
                                                new PersonIcon() . ' ' . $tblPersonAddress->getFullName(),
                                                '/People/Person',
                                                null,
                                                array('Id' => $tblPersonAddress->getId()),
                                                'Zur Person'
                                            )
                                            : $tblPersonAddress->getFullName())
                                        . Relationship::useService()->getRelationshipInformationForContact($tblPerson, $tblPersonAddress, $tblToPersonTemp->getRemark());
                                    if (!$tblToPerson) {
                                        $tblToPerson = $tblToPersonTemp;
                                    }
                                }
                            }

                            if ($tblToPerson
                                && ($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByToPerson(TblOnlineContact::VALUE_TYPE_ADDRESS, $tblToPerson))
                            ) {
                                foreach ($tblOnlineContactList as $tblOnlineContact) {
                                    $hasOnlineContacts = true;
                                    if ($hasOnlineContactsOptions) {
                                        $links = (new Link(new Edit(), ApiAddressToPerson::getEndpoint(), null, array(), 'Bearbeiten'))
                                                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineOpenEditAddressToPersonModal($tblPerson->getId(), $tblToPerson->getId(), $tblOnlineContact->getId()))
                                            . ' | '
                                            . (new Link(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()), ApiContactDetails::getEndpoint(), null,
                                                array(), 'Löschen'))
                                                ->ajaxPipelineOnClick(ApiContactDetails::pipelineOpenDeleteContactDetailModal($tblPerson->getId(), $tblOnlineContact->getId()));
                                    } else{
                                        $links = '';
                                    }
                                    $content[] = new Container(
                                            'Änderungswunsch für ' . OnlineContactDetails::useService()->getPersonListForOnlineContact($tblOnlineContact, true) .  ': '
                                        )
                                        . new Container(new MapMarker() . ' ' . $tblOnlineContact->getContactContent() . new PullRight($links))
                                        . new Container($tblOnlineContact->getContactCreate());
                                }
                            }

                            $panel = FrontendReadOnly::getContactPanel(
                                new MapMarker() . ' ' . $tblType->getName(),
                                $content,
                                $options,
                                $hasOnlineContacts ? Panel::PANEL_TYPE_WARNING : $panelType
                            );

                            if ($LayoutRowCount % 4 == 0) {
                                $LayoutRow = new LayoutRow(array());
                                $LayoutRowList[] = $LayoutRow;
                            }
                            $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                            $LayoutRowCount++;
                        }
                    }
                }
            }

            return (string) (new Layout(new LayoutGroup($LayoutRowList)));
        }
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return string
     */
    public function frontendLayoutCompanyNew(TblCompany $tblCompany)
    {

        if (($tblAddressList = Address::useService()->getAddressAllByCompany($tblCompany))){
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            foreach ($tblAddressList as $tblToCompany) {
                if (($tblAddress = $tblToCompany->getTblAddress())
                    && ($tblType = $tblToCompany->getTblType())
                ) {
                    $content = array();

                    $options =
                        (new Link(
                            new Edit(),
                            ApiAddressToCompany::getEndpoint(),
                            null,
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiAddressToCompany::pipelineOpenEditAddressToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ))
                        . ' | '
                        . (new Link(
                            new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                            ApiAddressToCompany::getEndpoint(),
                            null,
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiAddressToCompany::pipelineOpenDeleteAddressToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ));

                    $content[] = $tblAddress->getGuiLayout();
                    if (($remark = $tblToCompany->getRemark())) {
                        $content[] = new Muted($remark);
                    }

                    $panel = FrontendReadOnly::getContactPanel(
                        new MapMarker() . ' ' . $tblType->getName(),
                        $content,
                        $options,
                        Panel::PANEL_TYPE_SUCCESS
                    );

                    if ($LayoutRowCount % 4 == 0) {
                        $LayoutRow = new LayoutRow(array());
                        $LayoutRowList[] = $LayoutRow;
                    }
                    $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                    $LayoutRowCount++;
                }
            }

            return (string) (new Layout(new LayoutGroup($LayoutRowList)));
        } else {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine Adressen hinterlegt')))));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblOnlineContact|null $tblOnlineContact
     *
     * @return string
     */
    public function getRelationshipsContent(TblPerson $tblPerson, TblOnlineContact $tblOnlineContact = null)
    {
        if ($tblOnlineContact
            && ($tblAddressPersonList = OnlineContactDetails::useService()->getPersonListForOnlineContact($tblOnlineContact, false))
        ) {
            $Global = $this->getGlobal();
            foreach ($tblAddressPersonList as $tblAddressPerson) {
                $Global->POST['Relationship'][$tblAddressPerson->getId()] = $tblAddressPerson->getId();
            }
            $Global->savePost();
        }

        $list = array();
        $list = $this->getRelationshipList($tblPerson, $list, true);
        // eigene Person, falls diese über die Drei-Ecks-Beziehung kommt wieder entfernen
        if (isset($list[$tblPerson->getId()])) {
            unset($list[$tblPerson->getId()]);
        }

        if (empty($list)) {
            return '';
        } else {
            return new Panel(
                'Übernehmen für aktuelle Hauptadresse ' . new Small('in Beziehung stehen'),
                $list,
                Panel::PANEL_TYPE_INFO
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param array $list
     * @param bool $isDeepSearch
     *
     * @return array
     */
    private function getRelationshipList(TblPerson $tblPerson, $list, $isDeepSearch)
    {
        if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
            foreach ($tblRelationshipList as $tblToPerson) {
                if (($tblType = $tblToPerson->getTblType())
                    && ($tblType->getName() == 'Sorgeberechtigt'
                        || $tblType->getName() == 'Bevollmächtigt'
                        || $tblType->getName() == 'Geschwisterkind'
                        || $tblType->getName() == 'Ehepartner'
                        || $tblType->getName() == 'Lebenspartner'
                    )
                ) {
                    if (($tblPersonFrom = $tblToPerson->getServiceTblPersonFrom())
                        && ($tblPersonTo = $tblToPerson->getServiceTblPersonTo())
                    ) {
                        if ($tblPersonFrom->getId() == $tblPerson->getId()) {
                            $tblPersonShow = $tblPersonTo;
                        } else {
                            $tblPersonShow = $tblPersonFrom;
                        }

                        if ($isDeepSearch) {
                            if ($tblPersonShow->getId() == $tblPersonTo->getId()
                                && ($tblType->getName() == 'Bevollmächtigt' || $tblType->getName() == 'Sorgeberechtigt')
                            ) {
                                $type = 'Kind';
                            } else {
                                $type = $tblType->getName();
                            }
                        } else {
//                            $type = 'Drei-Ecks-Beziehung';
                            $type = '';
                        }

//                        if ($tblAddress
//                            && ($tblAddressMain = $tblPersonShow->fetchMainAddress())
//                            && $tblAddress->getId() == $tblAddressMain->getId()
//                        ) {
//                            $global = $this->getGlobal();
//                            $global->POST['Relationship'][$tblPersonShow->getId()] = $tblPersonShow->getId();
//                            $global->savePost();
//                        }

                        // es soll die direkte Beziehung, statt der indirekten Beziehung (Drei-Ecks-Beziehung) angezeigt werden
                        if ($isDeepSearch || !isset($list[$tblPersonShow->getId()])) {
                            $list[$tblPersonShow->getId()] =
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        (new CheckBox(
                                            'Relationship[' . $tblPersonShow->getId() . ']',
                                            $tblPersonShow->getFullName() . ($type  ?' (' . $type . ')' : ''),
                                            $tblPersonShow->getId()
                                        ))->ajaxPipelineOnClick(ApiAddressToPerson::pipelineLoadRelationshipsMessage())
                                        , 6),
                                    new LayoutColumn(
                                        ($tblAddressPerson = $tblPersonShow->fetchMainAddress())
                                            ? $tblAddressPerson->getGuiString()
                                            : new \SPHERE\Common\Frontend\Text\Repository\Warning(new Ban() . ' Keine Hauptadresse vorhanden')
                                        , 6)
                                ))));
                        }

                        if ($isDeepSearch) {
                            $list = $this->getRelationshipList($tblPersonShow, $list, false);
                        }
                    }
                }
            }
        }

        return $list;
    }
}
