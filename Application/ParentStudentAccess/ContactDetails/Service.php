<?php

namespace SPHERE\Application\ParentStudentAccess\ContactDetails;

use DateTime;
use SPHERE\Application\ParentStudentAccess\ContactDetails\Service\Data;
use SPHERE\Application\ParentStudentAccess\ContactDetails\Service\Setup;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     * @return string|void
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * eingeloggte Person ist ein Schüler -> nur ab 18 Jahre
     *
     * @return array|false
     */
    public function getPersonListFromStudentLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'OnlineContactDetailsAllowedForSchoolTypes'))
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            if (($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
                && ($tblType = $tblDivision->getType())
                && isset($tblSchoolTypeAllowedList[$tblType->getId()])
                && ($birthday = $tblPerson->getBirthday())
                && (new DateTime($birthday)) <= ((new DateTime('now'))->modify('-18 year'))
            ) {
                $tblPersonList[$tblPerson->getId()] = $tblPerson;

                // und anzeige der Sorgeberechtigt, Bevollmächtigt, Vormund -> erstmal nicht
//                if (($tblPersonRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))) {
//                    foreach ($tblPersonRelationshipList as $relationship) {
//                        if (($tblPersonFrom = $relationship->getServiceTblPersonFrom())
//                            && $tblPersonFrom->getId() != $tblPerson->getId()
//                            && ($relationship->getTblType()->getName() == 'Sorgeberechtigt'
//                                || $relationship->getTblType()->getName() == 'Bevollmächtigt'
//                                || $relationship->getTblType()->getName() == 'Vormund')
//                        ) {
//                            $tblPersonList[$tblPersonFrom->getId()] = $tblPersonFrom;
//                        }
//                    }
//                }
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }

    /**
     * nur eingeloggter Elternteil + Kinder, erstmal nicht die weiteren Elternteile aus Datenschutzgründen
     *
     * @return array|false
     */
    public function getPersonListFromCustodyLogin()
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'Person', 'ContactDetails', 'OnlineContactDetailsAllowedForSchoolTypes'))
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
                        if (($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPersonTo))
                            && ($tblType = $tblDivision->getType())
                            && isset($tblSchoolTypeAllowedList[$tblType->getId()])
                        ) {
                            $tblPersonList[$tblPersonTo->getId()] = $tblPersonTo;
                        }
                    }
                }
            }

            // eingeloggter Elternteil
            if ($tblPersonList) {
                $tblPersonList[$tblPerson->getId()] = $tblPerson;
            }
        }

        return empty($tblPersonList) ? false : $tblPersonList;
    }
}