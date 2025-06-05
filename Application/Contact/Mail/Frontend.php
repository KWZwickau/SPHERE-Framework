<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Api\Contact\ApiContactDetails;
use SPHERE\Application\Api\Contact\ApiMailToCompany;
use SPHERE\Application\Api\Contact\ApiMailToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\OnlineContactDetails;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\People\Person\FrontendReadOnly;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\MailField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
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
use SPHERE\Common\Frontend\Link\Repository\Mailto;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Contact\Mail
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
    public function formAddressToPerson($PersonId, $ToPersonId = null, $setPost = false, $OnlineContactId = null, $isOnlineContactPosted = false): Form
    {
        $tblOnlineContact = $OnlineContactId ? OnlineContactDetails::useService()->getOnlineContactById($OnlineContactId) : false;

        if ($ToPersonId && ($tblToPerson = Mail::useService()->getMailToPersonById($ToPersonId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                if ($isOnlineContactPosted) {
                    $Global->POST['Address']['Mail'] = $tblOnlineContact->getContactContent();
                } else {
                    $Global->POST['Address']['Mail'] = $tblToPerson->getTblMail()->getAddress();
                }
                $Global->POST['Address']['Alias'] = $tblToPerson->isAccountUserAlias();
                $Global->POST['Address']['IsRecoveryMail'] = $tblToPerson->isAccountRecoveryMail();
                $Global->POST['Type']['Type'] = $tblToPerson->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToPerson->getRemark();
                $Global->savePost();
            }
        } elseif ($tblOnlineContact) {
            if ($setPost) {
                $Global = $this->getGlobal();
                /** @var TblMail $tblContact */
                $Global->POST['Address']['Mail'] = ($tblContact = $tblOnlineContact->getServiceTblContact()) ? $tblContact->getAddress() : '';
                $Global->POST['Type']['Type'] = ($tblNewContactType = $tblOnlineContact->getServiceTblNewContactType()) ? $tblNewContactType->getId() : 0;
                $Global->savePost();
            }
        }

        if ($ToPersonId) {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineEditMailToPersonSave($PersonId, $ToPersonId, $OnlineContactId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToPerson::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineCreateMailToPersonSave($PersonId, $OnlineContactId));
        }

        $tblTypeAll = Mail::useService()->getTypeAll();

        // Consumer with DLLP?
        $isDLLP = false;
        if(($tblConsumer = Consumer::useService()->getConsumerBySession())){
            if(Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_DLLP)){
                $isDLLP = true;
            }
        }

        $CheckBoxAlias = '';
        $CheckBoxRecoveryMail = '';
        if($isDLLP){
            $tblPerson = Person::useService()->getPersonById($PersonId);
            $hasAccount = Account::useService()->getAccountAllByPerson($tblPerson);

            $CheckBoxAlias = new CheckBox('Address[Alias]', 'E-Mail als '
                . ($hasAccount ? '' : new Bold('späteren')) . ' DLLP Benutzername verwenden', 1);
            $CheckBoxRecoveryMail = new CheckBox('Address[IsRecoveryMail]', 'E-Mail als '
                . ($hasAccount ? '' : new Bold('späteres')) . ' DLLP "Passwort vergessen" verwenden', 1);
        }

        $typeSelectBox = (new SelectBox('Type[Type]', 'Typ', array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()))->setRequired();
        $mailField = (new MailField('Address[Mail]', 'E-Mail Adresse', 'E-Mail Adresse', new MailIcon() ))->setRequired();
        $remarkTextArea = new TextArea('Type[Remark]', 'Bemerkungen', 'Bemerkungen', new Edit());

        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('E-Mail Adresse',
                            array(
                                $typeSelectBox,
                                $mailField,
                                $CheckBoxAlias,
                                $CheckBoxRecoveryMail
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
     * @param $CompanyId
     * @param null $ToCompanyId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formAddressToCompany($CompanyId, $ToCompanyId = null, $setPost = false)
    {

        if ($ToCompanyId && ($tblToCompany = Mail::useService()->getMailToCompanyById($ToCompanyId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Address'] = $tblToCompany->getTblMail()->getAddress();
                $Global->POST['Type']['Type'] = $tblToCompany->getTblType()->getId();
                $Global->POST['Type']['Remark'] = $tblToCompany->getRemark();
                $Global->savePost();
            }
        }

        if ($ToCompanyId) {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToCompany::pipelineEditMailToCompanySave($CompanyId, $ToCompanyId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiMailToCompany::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiMailToCompany::pipelineCreateMailToCompanySave($CompanyId));
        }

        $tblTypeAll = Mail::useService()->getTypeAll();

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('E-Mail Adresse',
                            array(
                                (new SelectBox('Type[Type]', 'Typ',
                                    array('{{ Name }} {{ Description }}' => $tblTypeAll), new TileBig()
                                ))->setRequired(),
                                (new MailField('Address', 'E-Mail Adresse', 'E-Mail Adresse', new MailIcon() ))->setRequired()
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
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

        $hasAccount = Account::useService()->getAccountAllByPerson($tblPerson);

        $mailList = array();
        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))){
            foreach ($tblMailList as $tblToPerson) {
                if (($tblMail = $tblToPerson->getTblMail())) {
                    $mailList[$tblMail->getId()][$tblToPerson->getTblType()->getId()][$tblPerson->getId()] = $tblToPerson;
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
                    $tblRelationshipMailAll = Mail::useService()->getMailAllByPerson($tblPersonRelationship);
                    if ($tblRelationshipMailAll) {
                        foreach ($tblRelationshipMailAll as $tblToPerson) {
                            if (($tblMail = $tblToPerson->getTblMail())) {
                                $mailList[$tblMail->getId()][$tblToPerson->getTblType()->getId()][$tblPersonRelationship->getId()] = $tblToPerson;
                            }
                        }
                    }
                }
            }
        }

        if (empty($mailList)) {
            return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine E-Mail Adressen hinterlegt')))));
        } else {
            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;

            // Consumer with DLLP?
            $isDLLP = false;
            if(($tblConsumer = Consumer::useService()->getConsumerBySession())){
                if(Consumer::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_DLLP)){
                    $isDLLP = true;
                }
            }

            foreach ($mailList as $mailId => $typeArray) {
                if (($tblMail = Mail::useService()->getMailById($mailId))) {
                    foreach ($typeArray as $typeId => $personArray) {
                        if (($tblType = Mail::useService()->getTypeById($typeId))) {
                            $content = array();
                            $hasOnlineContacts = false;
                            if (isset($personArray[$tblPerson->getId()])) {
                                /** @var TblToPerson $tblToPerson */
                                $tblToPerson = $personArray[$tblPerson->getId()];
                                $panelType = Panel::PANEL_TYPE_SUCCESS;
                                $options =
                                    (new Link(
                                        new Edit(),
                                        ApiMailToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Bearbeiten'
                                    ))->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenEditMailToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ))
                                    . ' | '
                                    . (new Link(
                                        new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                                        ApiMailToPerson::getEndpoint(),
                                        null,
                                        array(),
                                        'Löschen'
                                    ))->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenDeleteMailToPersonModal(
                                        $tblPerson->getId(),
                                        $tblToPerson->getId()
                                    ));
                                $hasOnlineContactsOptions = true;
                            } else {
                                $tblToPerson = false;
                                $panelType = Panel::PANEL_TYPE_DEFAULT;
                                $options = '';
                                $hasOnlineContactsOptions = false;
                            }

                            $content[] = new Mailto($tblMail->getAddress(), $tblMail->getAddress(), new Envelope());
                            if ($isDLLP && isset($personArray[$tblPerson->getId()])) {
                                if(($tblToPersonCurrent =  $personArray[$tblPerson->getId()])){
                                    /** @var $tblToPersonCurrent TblToPerson */
                                    if($tblToPersonCurrent->isAccountUserAlias()){
                                        $content[] = new Check() . ' E-Mail als '
                                            . ($hasAccount ? '' : new Bold('späteren'))
                                            . ' DLLP Benutzername verwenden';
                                    }
                                    if($tblToPersonCurrent->isAccountRecoveryMail()){
                                        $content[] = new Check() . ' E-Mail als '
                                            . ($hasAccount ? '' : new Bold('späteres'))
                                            . ' DLLP "Passwort vergessen" verwenden';
                                    }
                                }
                            }

                            /**
                             * @var TblToPerson $tblToPersonTemp
                             */
                            foreach ($personArray as $personId => $tblToPersonTemp) {
                                if (($tblPersonMail = Person::useService()->getPersonById($personId))) {
                                    $content[] = ($tblPerson->getId() != $tblPersonMail->getId()
                                            ? new Link(
                                                new PersonIcon() . ' ' . $tblPersonMail->getFullName(),
                                                '/People/Person',
                                                null,
                                                array('Id' => $tblPersonMail->getId()),
                                                'Zur Person'
                                            )
                                            : $tblPersonMail->getFullName())
                                        . Relationship::useService()->getRelationshipInformationForContact($tblPerson, $tblPersonMail, $tblToPersonTemp->getRemark());
                                    if (!$tblToPerson) {
                                        $tblToPerson = $tblToPersonTemp;
                                    }
                                }
                            }

                            if ($tblToPerson
                                && ($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByToPerson(TblOnlineContact::VALUE_TYPE_MAIL, $tblToPerson))
                            ) {
                                foreach ($tblOnlineContactList as $tblOnlineContact) {
                                    $hasOnlineContacts = true;
                                    if ($hasOnlineContactsOptions) {
                                        $links = (new Link(new Edit(), ApiMailToPerson::getEndpoint(), null, array(), 'Bearbeiten'))
                                                ->ajaxPipelineOnClick(ApiMailToPerson::pipelineOpenEditMailToPersonModal($tblPerson->getId(), $tblToPerson->getId(), $tblOnlineContact->getId()))
                                            . ' | '
                                            . (new Link(new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()), ApiContactDetails::getEndpoint(),
                                                null, array(), 'Löschen'))
                                                ->ajaxPipelineOnClick(ApiContactDetails::pipelineOpenDeleteContactDetailModal($tblPerson->getId(), $tblOnlineContact->getId()));
                                    } else {
                                        $links = '';
                                    }
                                    $content[] = new Container(
                                            'Änderungswunsch für ' . OnlineContactDetails::useService()->getPersonListForOnlineContact($tblOnlineContact, true) .  ': '
                                        )
                                        . new Container(new MailIcon() . ' ' . $tblOnlineContact->getContactContent() . new PullRight($links))
                                        . new Container($tblOnlineContact->getContactCreate());
                                }
                            }

                            $panel = FrontendReadOnly::getContactPanel(
                                new MailIcon() . ' ' . $tblType->getName(),
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

        $mailBusiness = array();
        if (($tblRelationshipAll = Relationship::useService()->getCompanyRelationshipAllByCompany($tblCompany))) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                if(($tblPerson = $tblRelationship->getServiceTblPerson())){
                    $tblRelationshipMailAll = Mail::useService()->getMailAllByPerson($tblPerson);
                    if ($tblRelationshipMailAll) {
                        foreach ($tblRelationshipMailAll as $tblToPerson) {
                            if (($tblMail = $tblToPerson->getTblMail())
                            && $tblToPerson->getTblType()->getName() == 'Geschäftlich') {
                                $mailBusiness[$tblMail->getId()] = $tblToPerson;
                            }
                        }
                    }
                }
            }
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        if (($tblMailList = Mail::useService()->getMailAllByCompany($tblCompany))){
            foreach($tblMailList as $tblToCompany) {
                if(($tblMail = $tblToCompany->getTblMail())
                    && ($tblType = $tblToCompany->getTblType())
                ){
                    $content = array();

                    $panelType = Panel::PANEL_TYPE_SUCCESS;

                    $options =
                        (new Link(
                            new Edit(),
                            ApiMailToCompany::getEndpoint(),
                            null,
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiMailToCompany::pipelineOpenEditMailToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ))
                        .' | '
                        .(new Link(
                            new \SPHERE\Common\Frontend\Text\Repository\Warning(new Remove()),
                            ApiMailToCompany::getEndpoint(),
                            null,
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiMailToCompany::pipelineOpenDeleteMailToCompanyModal(
                            $tblCompany->getId(),
                            $tblToCompany->getId()
                        ));

                    $content[] = new Mailto($tblMail->getAddress(), $tblMail->getAddress(), new Envelope());
                    if(($remark = $tblToCompany->getRemark())){
                        $content[] = new Muted($remark);
                    }

                    $panel = FrontendReadOnly::getContactPanel(
                        new MailIcon().' '.$tblType->getName(),
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
        if(!empty($mailBusiness)){
            /**
             * @var TblToPerson $tblToPerson
             */
            foreach($mailBusiness as $tblToPerson) {
                $content = array();
                $tblPerson = $tblToPerson->getServiceTblPerson();
                $tblMail = $tblToPerson->getTblMail();
                $tblType = $tblToPerson->getTblType();

                $content[] = new Mailto($tblMail->getAddress(), $tblMail->getAddress(), new Envelope());
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
                    new MailIcon().' '.$tblType->getName().' '.$tblType->getDescription(),
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
        return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine E-Mail Adressen hinterlegt')))));
    }
}
