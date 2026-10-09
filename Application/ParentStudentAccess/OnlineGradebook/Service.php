<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use DateTime;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;

class Service
{
    /**
     * eingeloggte Person ist ein Schüler, wo die Notenübersicht nicht gesperrt ist
     *
     * @return array|false
     */
    public function getPersonListFromStudentLogin(): bool|array
    {
        $tblPersonList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $tblSchoolTypeAllowedList = false;
            if (($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType'))
                && ($tblSetting->getValue())
            ) {
                $tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
            }

            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
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
    public function getPersonListFromCustodyLogin(): bool|array
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
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPersonTo))
                            && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
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

    /**
     * @return bool|array
     */
    public function getPersonListFromAccountBySession(): bool|array
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

        return $tblPersonList;
    }

    /**
     * @return array
     */
    public function getOnlineGradeBookYearAndBlockedAndDataList(): array
    {
        $tblDisplayYearList = array();
        $BlockedList = array();
        $data = array();

        // Schuljahre Anzeigen ab:
        $startYear = '';
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'YearOfUserView');
        if($tblSetting){
            $YearTempId = $tblSetting->getValue();
            if ($YearTempId && ($tblYearTemp = Term::useService()->getYearById($YearTempId))){
                $startYear = ($tblYearTemp->getYear() ? $tblYearTemp->getYear() : $tblYearTemp->getName());
            }
        }

        // Jahre ermitteln, in denen Schüler in einer Klasse ist
        if (($tblPersonList = OnlineGradebook::useService()->getPersonListFromAccountBySession())) {
            $dateTimeNow = new DateTime('now');
            foreach ($tblPersonList as $tblPerson) {
                // Schüler überspringen, wenn der Sorgeberechtigte durch den volljährigen Schüler gesperrt ist
                if (MyAccount::useService()->isPersonBlockedForCustody($tblPerson)) {
                    $BlockedList[$tblPerson->getId()] = $tblPerson;
                    continue;
                }
                if ($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson)) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if ($tblStudentEducation->getLeaveDate()) {
                            continue;
                        }

                        if (($tblYear = $tblStudentEducation->getServiceTblYear())) {
                            // Anzeige nur für Schuljahre die nach dem "Startschuljahr"(Veröffentlichung) liegen
                            if($tblYear->getYear() >= $startYear){
                                // keine zukünftigen Schuljahre anzeigen SSWHD-1751
                                list($startDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                                if ($startDate < $dateTimeNow) {
                                    $tblDisplayYearList[$tblYear->getId()] = $tblYear;
                                    $data[$tblYear->getId()][$tblPerson->getId()] = $tblStudentEducation;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($tblDisplayYearList, $BlockedList, $data);
    }
}