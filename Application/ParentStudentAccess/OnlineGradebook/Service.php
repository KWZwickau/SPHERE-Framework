<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;

class Service
{
    /**
     * @return false|TblPerson[]
     */
    public function getPersonListForStudent()
    {
        $tblPerson = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPerson = $tblPersonAllByAccount[0];
            }
        }

        $tblPersonList = array();
        if ($tblPerson) {
            $tblPersonList[] = $tblPerson;

            $tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
            if ($tblPersonRelationshipList) {
                foreach ($tblPersonRelationshipList as $relationship) {
                    if ($relationship->getServiceTblPersonTo()
                        && ($relationship->getTblType()->getName() == 'Sorgeberechtigt'
                            || $relationship->getTblType()->getName() == 'Bevollmächtigt'
                            || $relationship->getTblType()->getName() == 'Vormund')
                    ) {
                        $tblPersonList[] = $relationship->getServiceTblPersonTo();
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * eingeloggte Person ist ein Schüler, wo die Notenübersicht nicht gesperrt ist
     *
     * @return array|false
     */
    public function getPersonListFromStudentLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $tblSchoolTypeAllowedList = false;
            if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType'))
                && ($tblSetting->getValue())
            ) {
                $tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
            }

            if (($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
                && ($tblType = $tblDivision->getType())
                && (!$tblSchoolTypeAllowedList || isset($tblSchoolTypeAllowedList[$tblType->getId()]))
            ) {
                $tblPersonList[$tblPerson->getId()] = $tblPerson;
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * Kinder des Elternteils
     *
     * @return array|false
     */
    public function getPersonListFromCustodyLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $tblSchoolTypeAllowedList = false;
            if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType'))
                && ($tblSetting->getValue())
            ) {
                $tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
            }

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
                        if (($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPersonTo))
                            && ($tblType = $tblDivision->getType())
                            && (!$tblSchoolTypeAllowedList || isset($tblSchoolTypeAllowedList[$tblType->getId()]))
                        ) {
                            $tblPersonList[$tblPersonTo->getId()] = $tblPersonTo;
                        }
                    }
                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }
}