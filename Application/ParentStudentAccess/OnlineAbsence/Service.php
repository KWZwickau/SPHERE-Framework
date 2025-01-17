<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineAbsence;

use DateTime;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;

class Service
{
    /**
     * eingeloggte Person ist ein Schüler -> nur ab 18 Jahre
     *
     * @return array|false
     */
    public function getPersonListFromStudentLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'OnlineAbsenceAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
                && isset($tblSchoolTypeAllowedList[$tblType->getId()])
                && ($birthday = $tblPerson->getBirthday())
                && (new DateTime($birthday)) <= ((new DateTime('now'))->modify('-18 year'))
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
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'OnlineAbsenceAllowedForSchoolTypes'))
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
}