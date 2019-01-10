<?php
namespace SPHERE\Application\Contact\Address;

use SPHERE\Application\Api\Contact\ApiAddressToCompany;
use SPHERE\Application\Api\Contact\ApiAddressToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Map;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\Repository\Warning;
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
     *
     * @return Form
     */
    public function formAddressToPerson($PersonId, $ToPersonId = null, $setPost = false)
    {

        if ($ToPersonId && ($tblToPerson = Address::useService()->getAddressToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->POST['Street']['Name'] = $tblToPerson->getTblAddress()->getStreetName();
                $Global->POST['Street']['Number'] = $tblToPerson->getTblAddress()->getStreetNumber();
                $Global->POST['City']['Code'] = $tblToPerson->getTblAddress()->getTblCity()->getCode();
                $Global->POST['City']['Name'] = $tblToPerson->getTblAddress()->getTblCity()->getName();
                $Global->POST['City']['District'] = $tblToPerson->getTblAddress()->getTblCity()->getDistrict();
                if ($tblToPerson->getTblAddress()->getTblState()) {
                    $Global->POST['State'] = $tblToPerson->getTblAddress()->getTblState()->getId();
                }
                $Global->POST['County'] = $tblToPerson->getTblAddress()->getCounty();
                $Global->POST['Nation'] = $tblToPerson->getTblAddress()->getNation();

                $Global->savePost();
            }
        }

        $tblAddress = Address::useService()->getAddressAll();
        $tblCity = Address::useService()->getCityAll();
        $tblState = Address::useService()->getStateAll();
        array_push($tblState, new TblState(''));
        $tblType = Address::useService()->getTypeAll();

        if ($ToPersonId) {
            $saveButton = (new PrimaryLink('Speichern', ApiAddressToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineEditAddressToPersonSave($PersonId, $ToPersonId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiAddressToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAddressToPerson::pipelineCreateAddressToPersonSave($PersonId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Anschrift', array(
                            (new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblType),
                                new TileBig(), true))->setRequired(),
                            (new AutoCompleter('Street[Name]', 'Straße', 'Straße',
                                array('StreetName' => $tblAddress), new MapMarker()
                            ))->setRequired(),
                                (new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker()))->setRequired()
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Stadt', array(
                            (new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl',
                                array('Code' => $tblCity), new MapMarker()
                            ))->setRequired(),
                            (new AutoCompleter('City[Name]', 'Ort', 'Ort',
                                array('Name' => $tblCity), new MapMarker()
                            ))->setRequired(),
                            new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil',
                                array('District' => $tblCity), new MapMarker()
                            ),
                            new AutoCompleter('County', 'Landkreis', 'Landkreis',
                                array('County' => $tblAddress), new Map()
                            ),
                            new SelectBox('State', 'Bundesland',
                                array('Name' => $tblState), new Map()
                            ),
                            new AutoCompleter('Nation', 'Land', 'Land',
                                array('Nation' => $tblAddress), new Map()
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

        $tblAddress = Address::useService()->getAddressAll();
        $tblCity = Address::useService()->getCityAll();
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
                                array('StreetName' => $tblAddress), new MapMarker()
                            ))->setRequired(),
                            (new TextField('Street[Number]', 'Hausnummer', 'Hausnummer', new MapMarker()))->setRequired()
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Stadt', array(
                            (new AutoCompleter('City[Code]', 'Postleitzahl', 'Postleitzahl',
                                array('Code' => $tblCity), new MapMarker()
                            ))->setRequired(),
                            (new AutoCompleter('City[Name]', 'Ort', 'Ort',
                                array('Name' => $tblCity), new MapMarker()
                            ))->setRequired(),
                            new AutoCompleter('City[District]', 'Ortsteil', 'Ortsteil',
                                array('District' => $tblCity), new MapMarker()
                            ),
                            new AutoCompleter('County', 'Landkreis', 'Landkreis',
                                array('County' => $tblAddress), new Map()
                            ),
                            new SelectBox('State', 'Bundesland',
                                array('Name' => $tblState), new Map()
                            ),
                            new AutoCompleter('Nation', 'Land', 'Land',
                                array('Nation' => $tblAddress), new Map()
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
                            } else {
                                $panelType = Panel::PANEL_TYPE_DEFAULT;
                                $options = '';
                            }

                            $content[] = $tblAddress->getGuiLayout();
                            /**
                             * @var TblToPerson $tblToPerson
                             */
                            foreach ($personArray as $personId => $tblToPerson) {
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
//                                        . (($remark = $tblToPerson->getRemark())  ? ' ' . new ToolTip(new Info(), $remark) : '');
                                        . (($remark = $tblToPerson->getRemark())  ? ' ' . new Small(new Muted($remark)) : '');
                                }
                            }

                            $panel = FrontendReadOnly::getContactPanel(
                                new MapMarker() . ' ' . $tblType->getName(),
                                $content,
                                $options,
                                $panelType
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
}
