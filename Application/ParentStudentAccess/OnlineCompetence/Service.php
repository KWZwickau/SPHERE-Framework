<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineCompetence;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;

class Service
{
    /**
     * @return bool
     */
    public function getIsModuleRegistered(): bool
    {
        // nur registrieren, wenn über die Mandanteneinstellung freigeschaltet ist und Personen angezeigt würden
        // oder wenn System-Account fürs Sperren der Routen
        $isRegistered = false;
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            if ($tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)) {
                // System-Account
                $isRegistered = true;
            } elseif (($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
                && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
            ) {
                // Schüler-Zugang
                $isRegistered = $this->getPersonListFromStudentLogin();
            } else {
                // Mitarbeiter oder Eltern-Zugang
                $isRegistered = $this->getPersonListFromCustodyLogin();
            }
        }

        return (bool) $isRegistered;
    }

    /**
     * eingeloggte Person ist ein Schüler
     *
     * @return TblPerson[]|false
     */
    public function getPersonListFromStudentLogin(): bool|array
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Competence', 'OnlineCompetenceAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
                && isset($tblSchoolTypeAllowedList[$tblType->getId()])
            ) {
                $tblPersonList[$tblPerson->getId()] = $tblPerson;
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * Kinder des Elternteils
     *
     * @return TblPerson[]|false
     */
    public function getPersonListFromCustodyLogin(): bool|array
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Competence', 'OnlineCompetenceAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            // Kinder des Elternteils
            if (($tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
                foreach ($tblPersonRelationshipList as $relationship) {
                    if (($tblPersonTo = $relationship->getServiceTblPersonTo())
                        && $tblPersonTo->getId() != $tblPerson->getId()
                        && ($relationship->getTblType()->getName() == 'Sorgeberechtigt'
                            || $relationship->getTblType()->getName() == 'Bevollmächtigt'
                            || $relationship->getTblType()->getName() == 'Vormund')
                    ) {
                        // prüfen: ob die Schulart freigeben ist
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonTo))
                            && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
                            && isset($tblSchoolTypeAllowedList[$tblType->getId()])
                        ) {
                            $tblPersonList[$tblPersonTo->getId()] = $tblPersonTo;
                        }
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * @return TblPerson[]
     */
    public function getPersonListAndSourceFromAccountBySession(): array
    {
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblUserAccount = UserAccount::useService()->getUserAccountByAccount($tblAccount))
            && $tblUserAccount->getType() == TblUserAccount::VALUE_TYPE_STUDENT
        ) {
            // Schüler-Zugang
            $tblPersonList = $this->getPersonListFromStudentLogin();
        } else {
            // Mitarbeiter oder Eltern-Zugang
            $tblPersonList = $this->getPersonListFromCustodyLogin();
        }

        return $tblPersonList ?: [];
    }
}