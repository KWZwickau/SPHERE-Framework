<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails;

use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineContactDetails;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblAddressToPerson;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblMailToPerson;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblPhoneToPerson;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\PhoneFax;
use SPHERE\Common\Frontend\Icon\Repository\PhoneMobil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendOnlineContactDetails(): Stage
    {
        $stage = new Stage('Kontakt-Daten', 'Übersicht');

        $stage->setContent(
            ApiOnlineContactDetails::receiverModal()
            . ApiOnlineContactDetails::receiverBlock($this->loadContactDetailsStageContent(), 'ContactDetailsStageContent')
        );

        return $stage;
    }

    public function loadContactDetailsStageContent(): string
    {
        $layoutGroupList = array();

        // Legende
        $layoutGroupList[] = new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(new Panel('Bestehende Kontakt-Daten', '', Panel::PANEL_TYPE_INFO), 12),
            new LayoutColumn(new Panel('Bestehende Kontakt-Daten mit noch nicht übernommenen Änderungswünschen', '', Panel::PANEL_TYPE_WARNING), 12),
            new LayoutColumn(new Panel('Neue Kontakt-Daten welche noch nicht übernommenen wurden', '', Panel::PANEL_TYPE_DEFAULT), 12)
        )), new Title('Legende'));

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
            && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
        ) {
            // Schüler-Zugang
            $tblPersonList = OnlineContactDetails::useService()->getPersonListFromStudentLogin();
        } else {
            // Mitarbeiter oder Eltern-Zugang
            $tblPersonList = OnlineContactDetails::useService()->getPersonListFromCustodyLogin();
        }

        if ($tblPersonList) {
            $personIdList = OnlineContactDetails::useService()->getPersonIdListFromPersonList($tblPersonList);
            foreach ($tblPersonList as $tblPerson) {
                $layoutGroupList[] = $this->getPersonContactDetailsLayoutGroup($tblPerson, $personIdList);
            }
        }

        return new Layout($layoutGroupList);
    }

    /**
     * @param TblPerson $tblPerson
     * @param array $personIdList
     *
     * @return LayoutGroup|null
     */
    private function getPersonContactDetailsLayoutGroup(TblPerson $tblPerson, array $personIdList): ?LayoutGroup
    {
        return new LayoutGroup(array(
            new LayoutRow(new LayoutColumn(
                new Title(
                    $tblPerson->getLastFirstName() . ' ' .
                    new Small(new Muted(DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson)))
                )
            )),
            new LayoutRow(new LayoutColumn(
                (new PrimaryLink('Neue Adresse hinzufügen', ApiOnlineContactDetails::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateAddressModal($tblPerson->getId(), null, $personIdList))
                . (new PrimaryLink('Neue Telefonnummer hinzufügen', ApiOnlineContactDetails::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreatePhoneModal($tblPerson->getId(), null, $personIdList))
                . (new PrimaryLink('Neue E-Mail-Adresse hinzufügen', ApiOnlineContactDetails::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateMailModal($tblPerson->getId(), null, $personIdList))
            )),
            new LayoutRow(new LayoutColumn(
                $this->loadContactDetailsContent($tblPerson, $personIdList)
            ))
        ));
    }

    private function loadContactDetailsContent(TblPerson $tblPerson, array $personIdList): string
    {
        if (isset($personIdList[$tblPerson->getId()])) {
            unset($personIdList[$tblPerson->getId()]);
        }

        $addressPanelList = array();
        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
            foreach ($tblAddressList as $tblAddressToPerson) {
                $list = OnlineContactDetails::useService()->getPersonListWithFilter(
                    Address::useService()->getPersonAllByAddress($tblAddressToPerson->getTblAddress()),
                    $personIdList,
                );
                $addressPanelList[] = new LayoutColumn($this->getAddressPanel($tblPerson, $tblAddressToPerson, $list), 3);
            }
        }

        $phonePanelList = array();
        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
            foreach ($tblPhoneList as $tblPhoneToPerson) {
                $list = OnlineContactDetails::useService()->getPersonListWithFilter(
                    Phone::useService()->getPersonAllByPhone($tblPhoneToPerson->getTblPhone()),
                    $personIdList,
                );
                $phonePanelList[] = new LayoutColumn($this->getPhonePanel($tblPerson, $tblPhoneToPerson, $list), 3);
            }
        }

        $mailPanelList = array();
        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
            foreach ($tblMailList as $tblMailToPerson) {
                $list = OnlineContactDetails::useService()->getPersonListWithFilter(
                    Mail::useService()->getPersonAllByMail($tblMailToPerson->getTblMail()),
                    $personIdList,
                );
                $mailPanelList[] = new LayoutColumn($this->getMailPanel($tblPerson, $tblMailToPerson, $list), 3);
            }
        }

        // neue Kontaktdaten, welche noch nicht angenommen wurden
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByPerson($tblPerson))) {
            foreach($tblOnlineContactList as $tblOnlineContact) {
                if (!$tblOnlineContact->getServiceTblToPerson()) {
                    switch ($tblOnlineContact->getContactType()) {
                        case TblOnlineContact::VALUE_TYPE_ADDRESS: $addressPanelList[] = new LayoutColumn($this->getOnlineContactPanel($tblOnlineContact), 3); break;
                        case TblOnlineContact::VALUE_TYPE_PHONE: $phonePanelList[] = new LayoutColumn($this->getOnlineContactPanel($tblOnlineContact), 3); break;
                        case TblOnlineContact::VALUE_TYPE_MAIL: $mailPanelList[] = new LayoutColumn($this->getOnlineContactPanel($tblOnlineContact), 3); break;
                    }
                }
            }
        }

//        $rows[] = new LayoutRow(new LayoutColumn(
//            (new PrimaryLink('Neue Adresse hinzufügen', ApiOnlineContactDetails::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateAddressModal($tblPerson->getId(), null, $personIdList)) . new Container('&nbsp;')
//        ));
//        if (!empty($addressPanelList)) {
//            $layoutRowCount = 0;
//            $layoutRow = null;
//            foreach ($addressPanelList as $addressColumn) {
//                if ($layoutRowCount % 4 == 0) {
//                    $layoutRow = new LayoutRow(array());
//                    $rows[] = $layoutRow;
//                }
//                $layoutRow->addColumn($addressColumn);
//                $layoutRowCount++;
//            }
//        } else {
//            $rows[] = new LayoutRow(new LayoutColumn(new Warning('Keine Adresse hinterlegt.', new Exclamation())));
//        }
//
//        $rows[] = new LayoutRow(new LayoutColumn(
//            (new PrimaryLink('Neue Telefonnummer hinzufügen', ApiOnlineContactDetails::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreatePhoneModal($tblPerson->getId(), null, $personIdList)) . new Container('&nbsp;')
//        ));
//        if (!empty($phonePanelList)) {
//            $layoutRowCount = 0;
//            $layoutRow = null;
//            foreach ($phonePanelList as $phoneColumn) {
//                if ($layoutRowCount % 4 == 0) {
//                    $layoutRow = new LayoutRow(array());
//                    $rows[] = $layoutRow;
//                }
//                $layoutRow->addColumn($phoneColumn);
//                $layoutRowCount++;
//            }
//        } else {
//            $rows[] = new LayoutRow(new LayoutColumn(new Warning('Keine Telefonnummern hinterlegt.', new Exclamation())));
//        }
//
//        $rows[] = new LayoutRow(new LayoutColumn(
//            (new PrimaryLink('Neue E-Mail-Adresse hinzufügen', ApiOnlineContactDetails::getEndpoint(), new Plus()))
//                ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateMailModal($tblPerson->getId(), null, $personIdList)) . new Container('&nbsp;')
//        ));
//        if (!empty($mailPanelList)) {
//            $layoutRowCount = 0;
//            $layoutRow = null;
//            foreach ($mailPanelList as $mailColumn) {
//                if ($layoutRowCount % 4 == 0) {
//                    $layoutRow = new LayoutRow(array());
//                    $rows[] = $layoutRow;
//                }
//                $layoutRow->addColumn($mailColumn);
//                $layoutRowCount++;
//            }
//        } else {
//            $rows[] = new LayoutRow(new LayoutColumn(new Warning('Keine E-Mail-Adresse hinterlegt.', new Exclamation())));
//        }

        $rows[] = new LayoutRow(new LayoutColumn('&nbsp;'));
        $allList = array_merge($addressPanelList, $phonePanelList, $mailPanelList);
        if (!empty($allList)) {
            $layoutRowCount = 0;
            $layoutRow = null;
            foreach ($allList as $layoutColumn) {
                if ($layoutRowCount % 4 == 0) {
                    $layoutRow = new LayoutRow(array());
                    $rows[] = $layoutRow;
                }
                $layoutRow->addColumn($layoutColumn);
                $layoutRowCount++;
            }
        } else {
            $rows[] = new LayoutRow(new LayoutColumn(new Warning('Keine E-Mail-Adresse hinterlegt.', new Exclamation())));
        }


        return new Layout(new LayoutGroup($rows));
    }

    /**
     * @param $PersonId
     * @param null $ToPersonId
     * @param $PersonIdList
     *
     * @return Form
     */
    public function formPhone($PersonId, $ToPersonId, $PersonIdList): Form
    {
        $panelContent = array();
        if ($PersonIdList) {
            foreach ($PersonIdList as $value) {
                if (($tblPersonItem = Person::useService()->getPersonById($value)) && $tblPersonItem->getId() != $PersonId) {
                   $panelContent[] = new CheckBox('Data[PersonList][' . $value . ']', $tblPersonItem->getFullName(), 1);
                }
            }
        }

        if ($ToPersonId) {
            $titleEditPanel = 'Änderungswunsch für bestehende Telefonnummer';
            $remarkLabel = 'Änderungsbemerkung';
            $titlePersonPanel = 'Änderungswunsch für weitere Personen übernehmen';
        } else {
            $titleEditPanel = 'Neue Telefonnummer';
            $remarkLabel = 'Bemerkung';
            $titlePersonPanel = 'Neue Telefonnummer für weitere Personen übernehmen';
        }

        $inputList = array();
        if (!$ToPersonId) {
            $inputList[] = (new SelectBox('Data[Type]', 'Typ', array('{{ Name }} {{ Description }}' => Phone::useService()->getTypeAll()), new TileBig()))->setRequired();
        }
        $inputList[] = (new TextField('Data[Number]', 'Telefonnummer', 'Telefonnummer', new PhoneIcon()))->setRequired();
        $inputList[] = new TextArea('Data[Remark]', $remarkLabel, $remarkLabel, new Comment());

        $rows[] = new FormRow(array(
            new FormColumn(
                new Panel(
                    $titleEditPanel,
                    $inputList,
                    Panel::PANEL_TYPE_INFO
                )
            )
        ));
        if ($panelContent) {
            $rows[] = new FormRow(new FormColumn(
                new Panel($titlePersonPanel, $panelContent, Panel::PANEL_TYPE_INFO)
            ));
        }
        $rows[] = new FormRow(array(
            new FormColumn(
                (new PrimaryLink(new Save() . ' Speichern', ApiOnlineContactDetails::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineCreatePhoneSave($PersonId, $ToPersonId, $PersonIdList))
            )
        ));

        return (new Form(new FormGroup($rows)))->disableSubmitAction();
    }

    /**
     * @param $PersonId
     * @param $ToPersonId
     * @param $PersonIdList
     *
     * @return Form
     */
    public function formAddress($PersonId, $ToPersonId, $PersonIdList): Form
    {
        $panelContent = array();
        if ($PersonIdList) {
            foreach ($PersonIdList as $value) {
                if (($tblPersonItem = Person::useService()->getPersonById($value)) && $tblPersonItem->getId() != $PersonId) {
                    $panelContent[] = new CheckBox('Data[PersonList][' . $value . ']', $tblPersonItem->getFullName(), 1);
                }
            }
        }

        if ($ToPersonId) {
            $titleEditPanel = 'Änderungswunsch für bestehende Adresse';
            $remarkLabel = 'Änderungsbemerkung';
            $titlePersonPanel = 'Änderungswunsch für weitere Personen übernehmen';
        } else {
            $titleEditPanel = 'Neue Adresse';
            $remarkLabel = 'Bemerkung';
            $titlePersonPanel = 'Neue Adresse für weitere Personen übernehmen';
        }

        $rows[] = new FormRow(array(
            new FormColumn(
                new Panel(
                    $titleEditPanel,
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                (new TextField('Data[Street][Name]', 'Straße', 'Straße'))->setRequired()
                                , 8),
                            new LayoutColumn(
                                (new TextField('Data[Street][Number]', 'Hausnummer', 'Hausnummer'))->setRequired()
                                , 4)
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                (new TextField('Data[City][Code]', 'Postleitzahl', 'Postleitzahl'))->setRequired()
                                , 3),
                            new LayoutColumn(
                                (new TextField('Data[City][Name]', 'Ort', 'Ort'))->setRequired()
                                , 5),
                            new LayoutColumn(
                                new TextField('Data[City][District]', 'Ortsteil', 'Ortsteil')
                                , 4)
                        )),
//                                new LayoutRow(array(
//                                    new LayoutColumn(
//                                        new TextField('Data[County]', 'Landkreis', 'Landkreis')
//                                    , 4),
//                                    new LayoutColumn(
//                                        new SelectBox('Data[State]', 'Bundesland', array('Name' => Address::useService()->getStateAll()))
//                                    , 4),
//                                    new LayoutColumn(
//                                        new TextField('Data[Nation]', 'Land', 'Land')
//                                    , 4)
//                                )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TextArea('Data[Remark]', $remarkLabel, $remarkLabel, new Comment())
                            )
                        ))
                    ))),
                    Panel::PANEL_TYPE_INFO
                )
            )
        ));
        if ($panelContent) {
            $rows[] = new FormRow(new FormColumn(
                new Panel($titlePersonPanel, $panelContent, Panel::PANEL_TYPE_INFO)
            ));
        }
        $rows[] = new FormRow(array(
            new FormColumn(
                (new PrimaryLink(new Save() . ' Speichern', ApiOnlineContactDetails::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineCreateAddressSave($PersonId, $ToPersonId, $PersonIdList))
            )
        ));

        return (new Form(new FormGroup($rows)))->disableSubmitAction();
    }

    /**
     * @param $PersonId
     * @param null $ToPersonId
     * @param $PersonIdList
     *
     * @return Form
     */
    public function formMail($PersonId, $ToPersonId, $PersonIdList): Form
    {
        $panelContent = array();
        if ($PersonIdList) {
            foreach ($PersonIdList as $value) {
                if (($tblPersonItem = Person::useService()->getPersonById($value)) && $tblPersonItem->getId() != $PersonId) {
                    $panelContent[] = new CheckBox('Data[PersonList][' . $value . ']', $tblPersonItem->getFullName(), 1);
                }
            }
        }

        if ($ToPersonId) {
            $titleEditPanel = 'Änderungswunsch für bestehende E-Mail-Adresse';
            $remarkLabel = 'Änderungsbemerkung';
            $titlePersonPanel = 'Änderungswunsch für weitere Personen übernehmen';
        } else {
            $titleEditPanel = 'Neue E-Mail-Adresse';
            $remarkLabel = 'Bemerkung';
            $titlePersonPanel = 'Neue E-Mail-Adresse für weitere Personen übernehmen';
        }

        $inputList = array();
        if (!$ToPersonId) {
            $inputList[] = (new SelectBox('Data[Type]', 'Typ', array('{{ Name }} {{ Description }}' => Mail::useService()->getTypeAll()), new TileBig()))->setRequired();
        }
        $inputList[] = (new TextField('Data[Address]', 'E-Mail-Adresse', 'E-Mail-Adresse', new MailIcon()))->setRequired();
        $inputList[] = new TextArea('Data[Remark]', $remarkLabel, $remarkLabel, new Comment());

        $rows[] = new FormRow(array(
            new FormColumn(
                new Panel(
                    $titleEditPanel,
                    $inputList,
                    Panel::PANEL_TYPE_INFO
                )
            )
        ));
        if ($panelContent) {
            $rows[] = new FormRow(new FormColumn(
                new Panel($titlePersonPanel, $panelContent, Panel::PANEL_TYPE_INFO)
            ));
        }
        $rows[] = new FormRow(array(
            new FormColumn(
                (new PrimaryLink(new Save() . ' Speichern', ApiOnlineContactDetails::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineCreateMailSave($PersonId, $ToPersonId, $PersonIdList))
            )
        ));

        return (new Form(new FormGroup($rows)))->disableSubmitAction();
    }

    /**
     * @param TblOnlineContact $tblOnlineContact
     *
     * @return Panel
     */
    private function getOnlineContactPanel(TblOnlineContact $tblOnlineContact): Panel
    {
        $content[] = $tblOnlineContact->getContactContent();
        $content[] = $tblOnlineContact->getContactCreate();
        return new Panel(
            $tblOnlineContact->getContactTypeIcon() . $tblOnlineContact->getContactTypeName(),
            $content,
            Panel::PANEL_TYPE_DEFAULT
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblPhoneToPerson $tblPhoneToPerson
     * @param array $personIdList
     *
     * @return Panel
     */
    private function getPhonePanel(TblPerson $tblPerson, TblPhoneToPerson $tblPhoneToPerson, array $personIdList): Panel
    {
        $tblType = $tblPhoneToPerson->getTblType();
        if ($tblType->getName() == 'Fax') {
            $icon = new PhoneFax();
        } elseif ($tblType->getDescription() == 'Mobil') {
            $icon = new PhoneMobil();
        } else {
            $icon = new PhoneIcon();
        }

        $editLink = (new Link(new Edit() . ' Bearbeiten', ApiOnlineContactDetails::getEndpoint(), null, array(), 'Änderungswunsch für diese Telefonnummer abgeben'))
            ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreatePhoneModal(
                $tblPerson->getId(), $tblPhoneToPerson->getId(), $personIdList));
        $content[] = $tblPhoneToPerson->getTblPhone()->getNumber() . new PullRight($editLink);

        $hasOnlineContacts = false;
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByToPerson(TblOnlineContact::VALUE_TYPE_PHONE, $tblPhoneToPerson))) {
            $hasOnlineContacts = true;
            foreach ($tblOnlineContactList as $tblOnlineContact) {
                $content[] = new Container($tblOnlineContact->getContactContent()) . new Container($tblOnlineContact->getContactCreate());
            }
        }

//        if (!empty($personIdList)) {
//            $nameList = '';
//            foreach (OnlineContactDetails::useService()->getNameListFromPersonIdList($personIdList) as $name) {
//                $nameList .= new Container($name);
//            }
//            $content[] = new Container('weitere Personen: ') . $nameList;
//        }

        return new Panel(
            $icon . ' Telefonnummer',
            $content,
            $hasOnlineContacts ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_INFO,
            !empty($personIdList) ? 'weitere Personen: ' . implode(', ' , OnlineContactDetails::useService()->getNameListFromPersonIdList($personIdList)) : null
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblAddressToPerson $tblAddressToPerson
     * @param array $personIdList
     *
     * @return Panel
     */
    private function getAddressPanel(TblPerson $tblPerson, TblAddressToPerson $tblAddressToPerson, array $personIdList): Panel
    {
        $editLink = (new Link(new Edit() . ' Bearbeiten', ApiOnlineContactDetails::getEndpoint(), null, array(), 'Änderungswunsch für diese Adresse abgeben'))
            ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateAddressModal(
                $tblPerson->getId(), $tblAddressToPerson->getId(), $personIdList));
        $content[] = $tblAddressToPerson->getTblAddress()->getGuiTwoRowString() . new PullRight($editLink);

        $hasOnlineContacts = false;
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByToPerson(TblOnlineContact::VALUE_TYPE_ADDRESS, $tblAddressToPerson))) {
            $hasOnlineContacts = true;
            foreach ($tblOnlineContactList as $tblOnlineContact) {
                $content[] = new Container($tblOnlineContact->getContactContent()) . new Container($tblOnlineContact->getContactCreate());
            }
        }

        return new Panel(
            new MapMarker() . ' ' . $tblAddressToPerson->getTblType()->getName(),
            $content,
            $hasOnlineContacts ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_INFO,
            !empty($personIdList) ? 'weitere Personen: ' . implode(', ' , OnlineContactDetails::useService()->getNameListFromPersonIdList($personIdList)) : null
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblMailToPerson $tblMailToPerson
     * @param array $personIdList
     *
     * @return Panel
     */
    private function getMailPanel(TblPerson $tblPerson, TblMailToPerson $tblMailToPerson, array $personIdList): Panel
    {
        $editLink = (new Link(new Edit() . ' Bearbeiten', ApiOnlineContactDetails::getEndpoint(), null, array(), 'Änderungswunsch für diese E-Mail-Adresse abgeben'))
            ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateMailModal(
                $tblPerson->getId(), $tblMailToPerson->getId(), $personIdList));
        $content[] = $tblMailToPerson->getTblMail()->getAddress() . new PullRight($editLink);

        $hasOnlineContacts = false;
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByToPerson(TblOnlineContact::VALUE_TYPE_MAIL, $tblMailToPerson))) {
            $hasOnlineContacts = true;
            foreach ($tblOnlineContactList as $tblOnlineContact) {
                $content[] = new Container($tblOnlineContact->getContactContent()) . new Container($tblOnlineContact->getContactCreate());
            }
        }

        return new Panel(
            new MailIcon() . ' E-Mail-Adresse',
            $content,
            $hasOnlineContacts ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_INFO,
            !empty($personIdList) ? 'weitere Personen: ' . implode(', ' , OnlineContactDetails::useService()->getNameListFromPersonIdList($personIdList)) : null
        );
    }
}