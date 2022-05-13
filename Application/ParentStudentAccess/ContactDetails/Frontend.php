<?php

namespace SPHERE\Application\ParentStudentAccess\ContactDetails;

use SPHERE\Application\Api\Education\ClassRegister\ApiAbsenceOnline;
use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineContactDetails;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
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
    public function frontendAbsenceOnline(): Stage
    {
        $stage = new Stage('Online Kontakt-Daten', 'Übersicht');

        $layoutGroupList = array();

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
            && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
        ) {
            // Schüler-Zugang
            $tblPersonList = ContactDetails::useService()->getPersonListFromStudentLogin();
        } else {
            // Mitarbeiter oder Eltern-Zugang
            $tblPersonList = ContactDetails::useService()->getPersonListFromCustodyLogin();
        }

        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $layoutGroupList[] = $this->getPersonContactDetailsLayoutGroup($tblPerson);
            }
        }

        $stage->setContent(ApiAbsenceOnline::receiverModal() . new Layout($layoutGroupList));

        return $stage;
    }


    private function getPersonContactDetailsLayoutGroup(TblPerson $tblPerson): ?LayoutGroup
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
                ApiOnlineContactDetails::receiverBlock($this->loadContactDetailsContent($tblPerson), 'ContactDetailsContent_' . $tblPerson->getId())
            ))
        ));
    }

    public function loadContactDetailsContent(TblPerson $tblPerson): string
    {
//        todo anzeige bereits eingereichte Änderungswünsche und von wem, die noch nicht von der schule angenommen wurden
        // es darf bei einer vorhandenen Hauptadresse keine neue Hauptadresse vorgeschlagen werden dann nur Änderung der bestehenden
        $dataList = array();
        if (($tblAddressList = Address::useService()->getAddressAllByPerson($tblPerson))) {
            foreach ($tblAddressList as $tblAddressToPerson) {
                $dataList[] = array(
                    'Category' => 'Adresse',
                    'Type' => $tblAddressToPerson->getTblType()->getName(),
                    'Content' => $tblAddressToPerson->getTblAddress()->getGuiString(),
                    // todo weitere Personen
                );
            }
        }

        if (($tblPhoneList = Phone::useService()->getPhoneAllByPerson($tblPerson))) {
            foreach ($tblPhoneList as $tblPhoneToPerson) {
                $dataList[] = array(
                    'Category' => 'Telefonnummer',
                    'Type' => $tblPhoneToPerson->getTblType()->getName(),
                    'Content' => $tblPhoneToPerson->getTblPhone()->getNumber(),
                    // todo weitere Personen
                );
            }
        }

        if (($tblMailList = Mail::useService()->getMailAllByPerson($tblPerson))) {
            foreach ($tblMailList as $tblMailToPerson) {
                $dataList[] = array(
                    'Category' => 'Telefonnummer',
                    'Type' => $tblMailToPerson->getTblType()->getName(),
                    'Content' => $tblMailToPerson->getTblMail()->getAddress(),
                    // todo weitere Personen
                );
            }
        }


        $columns = array(
            'Category' => 'Kategorie',
            'Type' => 'Typ',
            'Content' => 'Inhalt'
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
}