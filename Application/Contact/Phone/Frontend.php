<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Api\Contact\ApiContactDetails;
use SPHERE\Application\Api\Contact\ApiPhoneToCompany;
use SPHERE\Application\Api\Contact\ApiPhoneToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
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
use SPHERE\Common\Frontend\Link\Repository\PhoneLink;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Phone
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $PersonId
     * @param null $ToPersonId
     * @param bool $setPost
     * @param null $OnlineContactId
     * @param bool $isOnlineContactPosted
     *
     * @return Form
     */
    public function formNumberToPerson($PersonId, $ToPersonId = null, $setPost = false, $OnlineContactId = null, $isOnlineContactPosted = false): Form
    {
        $tblOnlineContact = $OnlineContactId ? OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId) : false;

        if ($ToPersonId && ($tblToPerson = Phone::useService()->getPhoneToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                if ($isOnlineContactPosted) {
                    $Global->POST['Number'] = $tblOnlineContact->getContactContent();
                } else {
                    $Global->POST['Number'] = $tblToPerson->getTblPhone()->getNumber();
                }

                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->savePost();
            }
        } elseif ($tblOnlineContact) {
            if ($setPost) {
                $Global = $this->getGlobal();
                /** @var TblPhone $tblContact */
                $Global->POST['Number'] = ($tblContact = $tblOnlineContact->getServiceTblContact()) ? $tblContact->getNumber() : '';
                $Global->POST['Type']['Type'] = ($tblNewContactType = $tblOnlineContact->getServiceTblNewContactType()) ? $tblNewContactType->getId() : 0;
                $Global->savePost();
            }
        }

        $tblTypeAll = Phone::useService()->getTypeAll();

        if ($ToPersonId) {
            $saveButton = (new PrimaryLink('Speichern', ApiPhoneToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineEditPhoneToPersonSave($PersonId, $ToPersonId, $OnlineContactId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiPhoneToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineCreatePhoneToPersonSave($PersonId, $OnlineContactId));
        }

        $typeSelectBox = (new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()))->setRequired();
        $numberTextField = (new TextField('Number', 'Telefonnummer', 'Telefonnummer', new PhoneIcon()))->setRequired();
        $remarkTextArea = new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit());

        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Telefonnummer',
                            array(
                                $typeSelectBox,
                                $numberTextField
                            ), Panel::PANEL_TYPE_INFO
                        ), 6
                    ),
                    new FormColumn(new Panel('Sonstiges', $remarkTextArea, Panel::PANEL_TYPE_INFO), 6),
                    new FormColumn($saveButton)
                )),
            ))
        );

        return $form->disableSubmitAction();
    }

    /**
     * @param int $CompanyId
     * @param int|null $ToCompanyId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formNumberToCompany($CompanyId, $ToCompanyId = null, $setPost = false)
    {
        if ($ToCompanyId && ($tblToCompany = Phone::useService()->getPhoneToCompanyById($ToCompanyId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Number'] = $tblToCompany->getTblPhone()->getNumber();
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->savePost();
            }
        }

        $tblTypeAll = Phone::useService()->getTypeAll();

        if ($ToCompanyId) {
            $saveButton = (new PrimaryLink('Speichern', ApiPhoneToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiPhoneToCompany::pipelineEditPhoneToCompanySave($CompanyId, $ToCompanyId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiPhoneToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiPhoneToCompany::pipelineCreatePhoneToCompanySave($CompanyId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Telefonnummer',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new TextField('Number', 'Telefonnummer', 'Telefonnummer', new PhoneIcon()
                                ))->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                        ), 6
                    ),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit())
                            , Panel::PANEL_TYPE_INFO
                        ), 6
                    ),
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

        $phoneList = array();
        $phoneEmergencyList = array();
        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
            foreach ($tblPhoneList as $tblToPerson) {
                $tblType = $tblToPerson->getTblType();
                if (($tblPhone = $tblToPerson->getTblPhone())) {
                    if ($tblToPerson->getTblType()->getName() == 'Notfall') {
                        $phoneEmergencyList[$tblPhone->getId().'#'.$tblType->getId()][$tblPerson->getId()] = $tblToPerson;
                    } else {
                        $phoneList[$tblPhone->getId().'#'.$tblType->getId()][$tblPerson->getId()] = $tblToPerson;
                    }
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
                    $tblRelationshipPhoneAll = Phone::useService()->getPhoneAllByPerson($tblPersonRelationship);
                    if ($tblRelationshipPhoneAll) {
                        foreach ($tblRelationshipPhoneAll as $tblToPerson) {
                            $tblType = $tblToPerson->getTblType();
                            if (($tblPhone = $tblToPerson->getTblPhone())) {
                                if ($tblToPerson->getTblType()->getName() == 'Notfall') {
                                    $phoneEmergencyList[$tblPhone->getId().'#'.$tblType->getId()][$tblPersonRelationship->getId()] = $tblToPerson;
                                } else {
                                    $phoneList[$tblPhone->getId().'#'.$tblType->getId()][$tblPersonRelationship->getId()] = $tblToPerson;
                                }
                            }
                        }
                    }
                }
            }
        }

        $tblSetting = Consumer::useService()->getSetting('Setting', 'Consumer', 'Service', 'EmergencyNumber');
        if($tblSetting && $tblSetting->getValue() == '1'){
            // Notfall Kontakte an den Schluss hängen
            $phoneList = $phoneList + $phoneEmergencyList;
        } else {
            // Notfall Kontakte zuerst anzeigen (Standard [Value = 0])
            $phoneList = $phoneEmergencyList + $phoneList;
        }


        if (empty($phoneList)) {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine Telefonnummern hinterlegt')))));
        } else {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            foreach ($phoneList as $phoneIdAndTypeId => $personArray) {
                $PhoneAndTypeId = explode('#', $phoneIdAndTypeId);
                $phoneId = $PhoneAndTypeId[0];
                $typeId = $PhoneAndTypeId[1];
                if (($tblPhone = Phone::useService()->getPhoneById($phoneId))) {
                    if (($tblType = Phone::useService()->getTypeById($typeId))) {
                        $content = array();
                        $hasOnlineContacts = false;
                        if (isset($personArray[$tblPerson->getId()])) {
                            /** @var TblToPerson $tblToPerson */
                            $tblToPerson = $personArray[$tblPerson->getId()];
                            $panelType = (preg_match('!Notfall!is',
                                $tblType->getName() . ' ' . $tblType->getDescription())
                                ? Panel::PANEL_TYPE_DANGER
                                : Panel::PANEL_TYPE_SUCCESS
                            );
                            $options =
                                (new Link(
                                    new Edit(),
                                    ApiPhoneToPerson::getEndpoint(),
                                    null,
                                    array(),
                                    'Bearbeiten'
                                ))->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineOpenEditPhoneToPersonModal(
                                    $tblPerson->getId(),
                                    $tblToPerson->getId()
                                ))
                                . ' | '
                                . (new Link(
                                    new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                                    ApiPhoneToPerson::getEndpoint(),
                                    null,
                                    array(),
                                    'Löschen'
                                ))->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineOpenDeletePhoneToPersonModal(
                                    $tblPerson->getId(),
                                    $tblToPerson->getId()
                                ));
                            $hasOnlineContactsOptions = true;
                        } else {
                            $tblToPerson = false;
                            $panelType = (preg_match('!Notfall!is',
                                $tblType->getName() . ' ' . $tblType->getDescription())
                                ? Panel::PANEL_TYPE_DANGER
                                : Panel::PANEL_TYPE_DEFAULT
                            );
                            $options = '';
                            $hasOnlineContactsOptions = false;
                        }

                        $content[] = new PhoneLink($tblPhone->getNumber(), $tblPhone->getNumber(), new PhoneIcon());
                        /**
                         * @var TblToPerson $tblToPersonTemp
                         */
                        foreach ($personArray as $personId => $tblToPersonTemp) {
                            if (($tblPersonPhone = Person::useService()->getPersonById($personId))) {
                                $content[] = ($tblPerson->getId() != $tblPersonPhone->getId()
                                        ? new Link(
                                            new PersonIcon() . ' ' . $tblPersonPhone->getFullName(),
                                            '/People/Person',
                                            null,
                                            array('Id' => $tblPersonPhone->getId()),
                                            'Zur Person'
                                        )
                                        : $tblPersonPhone->getFullName())
                                    . Relationship::useService()->getRelationshipInformationForContact($tblPerson, $tblPersonPhone, $tblToPersonTemp->getRemark());
                                if (!$tblToPerson) {
                                    $tblToPerson = $tblToPersonTemp;
                                }
                            }
                        }

                        if ($tblToPerson
                            && ($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByToPerson(TblOnlineContact::VALUE_TYPE_PHONE, $tblToPerson))
                        ) {
                            foreach ($tblOnlineContactList as $tblOnlineContact) {
                                $hasOnlineContacts = true;
                                if ($hasOnlineContactsOptions) {
                                    $links = (new Link(new Edit(), ApiPhoneToPerson::getEndpoint(), null, array(), 'Bearbeiten'))
                                            ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineOpenEditPhoneToPersonModal($tblPerson->getId(), $tblToPerson->getId(), $tblOnlineContact->getId()))
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
                                    . new Container(new PhoneIcon() . ' ' . $tblOnlineContact->getContactContent() . new PullRight($links))
                                    . new Container($tblOnlineContact->getContactCreate());
                            }
                        }

                        $panel = FrontendReadOnly::getContactPanel(
                            (preg_match('!Fax!is',
                                $tblType->getName() . ' ' . $tblType->getDescription())
                                ? new PhoneFax()
                                : (preg_match('!Mobil!is',
                                    $tblType->getName() . ' ' . $tblType->getDescription())
                                    ? new PhoneMobil()
                                    : new PhoneIcon()
                                )
                            )
                            . ' ' . $tblType->getName() . ' ' . $tblType->getDescription(),
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

        $phoneBusiness = array();
        if (($tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany))){
            foreach($tblRelationshipAll as $tblRelationship) {
                if(($tblPerson = $tblRelationship->getServiceTblPerson())){
                    $tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson);
                    if($tblToPersonList){
                        foreach($tblToPersonList as $tblToPerson) {
                            if(($tblPhone = $tblToPerson->getTblPhone())){
                                if($tblToPerson->getTblType()->getName() == 'Geschäftlich'){
                                    $phoneBusiness[$tblPhone->getId()] = $tblToPerson;
                                }
                                if($tblToPerson->getTblType()->getName() == 'Fax'
                                && $tblToPerson->getTblType()->getDescription() == 'Geschäftlich'){
                                    $phoneBusiness[$tblPhone->getId()] = $tblToPerson;
                                }
                            }
                        }
                    }
                }
            }
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;

        if (($tblPhoneList = Phone::useService()->getPhoneAllByCompany($tblCompany))){

            foreach($tblPhoneList as $tblToCompany) {
                if(($tblPhone = $tblToCompany->getTblPhone())
                    && ($tblType = $tblToCompany->getTblType())
                ){
                    $content = array();

                    $panelType = (preg_match('!Notfall!is',
                        $tblType->getName().' '.$tblType->getDescription())
                        ? Panel::PANEL_TYPE_DANGER
                        : Panel::PANEL_TYPE_SUCCESS
                    );

                    $options =
                        (new Link(
                            new Edit(),
                            ApiPhoneToCompany::getEndpoint(),
                            null,
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiPhoneToCompany::pipelineOpenEditPhoneToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ))
                        .' | '
                        .(new Link(
                            new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                            ApiPhoneToCompany::getEndpoint(),
                            null,
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiPhoneToCompany::pipelineOpenDeletePhoneToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ));

                    $content[] = new PhoneLink($tblToCompany->getTblPhone()->getNumber(),
                        $tblToCompany->getTblPhone()->getNumber(), new PhoneIcon());
                    if(($remark = $tblToCompany->getRemark())){
                        $content[] = new Muted($remark);
                    }

                    $panel = FrontendReadOnly::getContactPanel(
                        (preg_match('!Fax!is',
                            $tblType->getName().' '.$tblType->getDescription())
                            ? new PhoneFax()
                            : (preg_match('!Mobil!is',
                                $tblType->getName().' '.$tblType->getDescription())
                                ? new PhoneMobil()
                                : new PhoneIcon()
                            )
                        )
                        .' '.$tblType->getName().' '.$tblType->getDescription(),
                        $content,
                        $options,
                        $panelType
                    );

                    if($LayoutRowCount % 4 == 0){
                        $LayoutRow = new LayoutRow(array());
                        $LayoutRowList[] = $LayoutRow;
                    }
                    $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                    $LayoutRowCount++;
                }
            }
        }

        if(!empty($phoneBusiness)){
            /**
             * @var TblToPerson $tblToPerson
             */
            foreach($phoneBusiness as $tblToPerson) {
                $content = array();
                $tblPerson = $tblToPerson->getServiceTblPerson();
                $tblPhone = $tblToPerson->getTblPhone();
                $tblType = $tblToPerson->getTblType();

                $content[] = new PhoneLink($tblPhone->getNumber(), $tblPhone->getNumber(), new PhoneIcon());
                if(($remark = $tblToPerson->getRemark())){
                    $content[] = new Muted($remark);
                }
                $content[] = new Link(
                    new PersonIcon().' '.$tblPerson->getFullName(),
                    '/People/Person',
                    null,
                    array('Id' => $tblPerson->getId()),
                    'Zur Person'
                );

                $panel = FrontendReadOnly::getContactPanel(
                    (preg_match('!Fax!is',
                        $tblType->getName().' '.$tblType->getDescription())
                        ? new PhoneFax()
                        : new PhoneIcon()
                    )
                    .' '.$tblType->getName().' '.$tblType->getDescription(),
                    $content,
                    '',
                    Panel::PANEL_TYPE_DEFAULT
                );

                if($LayoutRowCount % 4 == 0){
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn(new LayoutColumn($panel, 3));
                $LayoutRowCount++;
            }
        }
        if(!empty($LayoutRowList)){
            return (string) (new Layout(new LayoutGroup($LayoutRowList)));
        }
        return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine Telefonnummern hinterlegt')))));
    }
}
