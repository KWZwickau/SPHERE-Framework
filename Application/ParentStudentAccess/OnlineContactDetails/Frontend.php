<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails;

use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineContactDetails;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
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

        $stage->setContent(ApiOnlineContactDetails::receiverBlock($this->loadContactDetailsStageContent(), 'ContactDetailsStageContent'));

        return $stage;
    }

    public function loadContactDetailsStageContent(): string
    {
        $layoutGroupList = array();

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

        return ApiOnlineContactDetails::receiverModal() . new Layout($layoutGroupList);
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
                    $tblPerson->getLastFirstName()
                    . (($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
                        ? ' ' . new Small(new Muted($tblDivision->getDisplayName()))
                        : '')
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
        $dataList = array();

        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
            foreach ($tblAddressList as $tblAddressToPerson) {
                $list = OnlineContactDetails::useService()->getPersonListWithFilter(
                    Address::useService()->getPersonAllByAddress($tblAddressToPerson->getTblAddress()),
                    $personIdList,
                );
                $dataList[] = array(
                    'Category' => 'Adresse',
                    'Type' => $tblAddressToPerson->getTblType()->getName(),
                    'Content' => $tblAddressToPerson->getTblAddress()->getGuiString(),
                    'OtherPersons' => OnlineContactDetails::useService()->getNameStringFromPersonIdList($list),
                    'OnlineContactDetails' => OnlineContactDetails::useService()->getOnlineContactStringByToPerson(TblOnlineContact::VALUE_TYPE_ADDRESS, $tblAddressToPerson),
                    'Options' => (new Standard('', ApiOnlineContactDetails::getEndpoint(), new Edit(), array(), 'Änderungswunsch für diese Telefonnummer abgeben'))
                        ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateAddressModal(
                            $tblPerson->getId(), $tblAddressToPerson->getId(), $list))
                );
            }
        }

        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
            foreach ($tblPhoneList as $tblPhoneToPerson) {
                $list = OnlineContactDetails::useService()->getPersonListWithFilter(
                    Phone::useService()->getPersonAllByPhone($tblPhoneToPerson->getTblPhone()),
                    $personIdList,
                );
                $dataList[] = array(
                    'Category' => 'Telefonnummer',
                    'Type' => $tblPhoneToPerson->getTblType()->getName(),
                    'Content' => $tblPhoneToPerson->getTblPhone()->getNumber(),
                    'OtherPersons' => OnlineContactDetails::useService()->getNameStringFromPersonIdList($list),
                    'OnlineContactDetails' => OnlineContactDetails::useService()->getOnlineContactStringByToPerson(TblOnlineContact::VALUE_TYPE_PHONE, $tblPhoneToPerson),
                    'Options' => (new Standard('', ApiOnlineContactDetails::getEndpoint(), new Edit(), array(), 'Änderungswunsch für diese Telefonnummer abgeben'))
                        ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreatePhoneModal(
                            $tblPerson->getId(), $tblPhoneToPerson->getId(), $list))
                );
            }
        }

        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
            foreach ($tblMailList as $tblMailToPerson) {
                $list = OnlineContactDetails::useService()->getPersonListWithFilter(
                    Mail::useService()->getPersonAllByMail($tblMailToPerson->getTblMail()),
                    $personIdList,
                );
                $dataList[] = array(
                    'Category' => 'E-Mail-Adresse',
                    'Type' => $tblMailToPerson->getTblType()->getName(),
                    'Content' => $tblMailToPerson->getTblMail()->getAddress(),
                    'OtherPersons' => OnlineContactDetails::useService()->getNameStringFromPersonIdList($list),
                    'OnlineContactDetails' => OnlineContactDetails::useService()->getOnlineContactStringByToPerson(TblOnlineContact::VALUE_TYPE_MAIL, $tblMailToPerson),
                    'Options' => (new Standard('', ApiOnlineContactDetails::getEndpoint(), new Edit(), array(), 'Änderungswunsch für diese E-Mail-Adresse abgeben'))
                        ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineOpenCreateMailModal(
                            $tblPerson->getId(), $tblMailToPerson->getId(), $list))
                );
            }
        }

        // neue Kontaktdaten, welche noch nicht angenommen wurden
        if (($tblOnlineContactList = OnlineContactDetails::useService()->getOnlineContactAllByPerson($tblPerson))) {
            foreach($tblOnlineContactList as $tblOnlineContact) {
                if (!$tblOnlineContact->getServiceTblToPerson()) {
                    $dataList[] = array(
                        'Category' => $tblOnlineContact->getContactTypeName(),
                        'Type' => '',
                        'Content' => $tblOnlineContact->getContactString(),
                        'OtherPersons' => '',
                        'OnlineContactDetails' => '',
                        'Options' => ''
                    );
                }
            }
        }

        $columns = array(
            'Category' => 'Kategorie',
            'Type' => 'Typ',
            'Content' => 'Inhalt',
            'OtherPersons' => 'weitere Personen',
            'OnlineContactDetails' => 'Änderungswünsche',
            'Options' => ''
        );

        return (new TableData($dataList, null, $columns,
            array(
                'order' => array(
                    array(0, 'desc'),
                    array(1, 'desc'),
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('type' => 'de_date', 'targets' => 1),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'searching' => false,
                'responsive' => false
            )
        ))->setHash('ContactDetails-' . $tblPerson->getId());
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

        $rows[] = new FormRow(array(
            new FormColumn(
                new Panel(
                    $titleEditPanel,
                    array(
                        (new TextField('Data[Number]', 'Telefonnummer', 'Telefonnummer', new PhoneIcon()))->setRequired(),
                        new TextArea('Data[Remark]', $remarkLabel, $remarkLabel, new Comment())
                    ),
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

        $rows[] = new FormRow(array(
            new FormColumn(
                new Panel(
                    $titleEditPanel,
                    array(
                        (new TextField('Data[Address]', 'E-Mail-Adresse', 'E-Mail-Adresse', new MailIcon()))->setRequired(),
                        new TextArea('Data[Remark]', $remarkLabel, $remarkLabel, new Comment())
                    ),
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
}