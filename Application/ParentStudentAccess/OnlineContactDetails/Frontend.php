<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails;

use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineContactDetails;
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
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
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
        // todo muss als ganze seite neu geladen werden
        $stage = new Stage('Kontakt-Daten', 'Übersicht');

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

        $stage->setContent(ApiOnlineContactDetails::receiverModal() . new Layout($layoutGroupList));

        return $stage;
    }


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
                ApiOnlineContactDetails::receiverBlock($this->loadContactDetailsContent($tblPerson, $personIdList), 'ContactDetailsContent_' . $tblPerson->getId())
            ))
        ));
    }

    public function loadContactDetailsContent(TblPerson $tblPerson, array $personIdList): string
    {
//        todo anzeige bereits eingereichte Änderungswünsche und von wem, die noch nicht von der schule angenommen wurden

        if (isset($personIdList[$tblPerson->getId()])) {
            unset($personIdList[$tblPerson->getId()]);
        }

        $dataList = array();
//        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
//            foreach ($tblAddressList as $tblAddressToPerson) {
//                $dataList[] = array(
//                    'Category' => 'Adresse',
//                    'Type' => $tblAddressToPerson->getTblType()->getName(),
//                    'Content' => $tblAddressToPerson->getTblAddress()->getGuiString(),
//                    'OtherPersons' => OnlineContactDetails::useService()->getPersonListWithFilter(
//                        Address::useService()->getPersonAllByAddress($tblAddressToPerson->getTblAddress()),
//                        $personIdList,
//                        true
//                    )
//                );
//            }
//        }

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

//        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
//            foreach ($tblMailList as $tblMailToPerson) {
//                $dataList[] = array(
//                    'Category' => 'Telefonnummer',
//                    'Type' => $tblMailToPerson->getTblType()->getName(),
//                    'Content' => $tblMailToPerson->getTblMail()->getAddress(),
//                    'OtherPersons' => OnlineContactDetails::useService()->getPersonListWithFilter(
//                        Mail::useService()->getPersonAllByMail($tblMailToPerson->getTblMail()),
//                        $personIdList,
//                        true
//                    ),
//
//                );
//            }
//        }


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

    public function formPhone($PersonId, $ToPersonId = null, $PersonIdList = array()): Form
    {

//        if ($ToPersonId) {
//            $saveButton = (new PrimaryLink('Speichern', ApiPhoneToPerson::getEndpoint(), new Save()))
//                ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineEditPhoneToPersonSave($PersonId, $ToPersonId));
//        } else {
//            $saveButton = (new PrimaryLink('Speichern', ApiPhoneToPerson::getEndpoint(), new Save()))
//                ->ajaxPipelineOnClick(ApiPhoneToPerson::pipelineCreatePhoneToPersonSave($PersonId));
//        }

        // todo delete button

        $panelContent = array();
        if ($PersonIdList) {
            foreach ($PersonIdList as $value) {
                if (($tblPersonItem = Person::useService()->getPersonById($value))) {
                   $panelContent[] = new CheckBox('Data[PersonList][' . $value . ']', $tblPersonItem->getFullName(), 1);
                }
            }
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Änderungswunsch für Telefonnummer',
                            array(
                                (new TextField('Data[Number]', 'Telefonnummer', 'Telefonnummer', new PhoneIcon()))->setRequired(),
                                new TextArea('Data[Remark]', 'Änderungsbemerkung', 'Änderungsbemerkung', new Edit())
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                    ),
                    $panelContent
                        ? new FormColumn(new Panel(
                            'Änderungswunsch für weitere Personen übernehmen',
                            $panelContent,
                            Panel::PANEL_TYPE_INFO
                        )) : null
                )),
                new FormRow(array(
                    new FormColumn(
                        (new PrimaryLink(new Save() . ' Speichern', ApiOnlineContactDetails::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiOnlineContactDetails::pipelineCreatePhoneSave($PersonId, $ToPersonId, $PersonIdList))
                    )
                ))
            ))
        ))->disableSubmitAction();
    }
}