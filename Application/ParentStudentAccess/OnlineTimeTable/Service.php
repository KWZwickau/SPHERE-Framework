<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineTimeTable;

use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;

class Service
{
    /**
     * @return array
     */
    public function getPersonListFromAccountBySession(): array
    {
        $tblPersonList = array();
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
            && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
        ) {
            // Schüler-Zugang
            if (($tblPerson = Account::useService()->getPersonByLogin())) {
                $tblPersonList[$tblPerson->getId()] = $tblPerson;
            }
        } else {
            // Mitarbeiter oder Eltern-Zugang
            $tblPersonList = $this->getPersonListFromCustodyLogin();
        }

        return $tblPersonList;
    }

    /**
     * Kinder des Elternteils
     *
     * @return array|false
     */
    public function getPersonListFromCustodyLogin(): bool|array
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            // Kinder des Elternteils
            if (($tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                foreach ($tblPersonRelationshipList as $relationship) {
                    if (($tblPersonTo = $relationship->getServiceTblPersonTo())
                        && $tblPersonTo->getId() != $tblPerson->getId()
                        && ($relationship->getTblType()->getName() == 'Sorgeberechtigt'
                            || $relationship->getTblType()->getName() == 'Bevollmächtigt'
                            || $relationship->getTblType()->getName() == 'Vormund')
                    ) {
                        $tblPersonList[$tblPersonTo->getId()] = $tblPersonTo;
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }
}