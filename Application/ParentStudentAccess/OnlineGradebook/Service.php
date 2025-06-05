<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use DateTime;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\Application\Setting\User\Account\Account as UserAccount;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

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
        if (($tblPersonList = OnlineGradebook::useService()->getPersonListFromAccountBySession())
            && ($tblAccount = Account::useService()->getAccountBySession())
        ) {
            $dateTimeNow = new DateTime('now');
            foreach ($tblPersonList as $tblPerson) {
                $tblPersonAccountList = Account::useService()->getAccountAllByPerson($tblPerson);
                if ($tblPersonAccountList && current($tblPersonAccountList)->getId() != $tblAccount->getId()) {
                    // Schüler überspringen, wenn Sorgeberechtigter geblockt ist
                    if (Consumer::useService()->getStudentCustodyByStudentAndCustody(current($tblPersonAccountList),
                        $tblAccount)) {
                        // Merken des geblockten Accounts
                        $BlockedList[] = current($tblPersonAccountList);
                        continue;
                    }
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

    /**
     * @param IFormInterface $Form
     * @param                $ParentAccount
     * @param TblAccount     $tblAccountStudent
     *
     * @return IFormInterface|string
     */
    public function setDisableParent(
        IFormInterface $Form,
        $ParentAccount,
        TblAccount $tblAccountStudent
    ) {
        /**
         * Skip to Frontend
         */
        if ($ParentAccount === null) {
            return $Form;
        }

        if (isset($ParentAccount['IsSubmit'])) {
            unset($ParentAccount['IsSubmit']);
        }

        $Error = false;

        // Remove old Link
        $tblStudentCustodyList = Consumer::useService()->getStudentCustodyByStudent($tblAccountStudent);
        if ($tblStudentCustodyList) {
            array_walk($tblStudentCustodyList, function (TblStudentCustody $tblStudentCustody) use (&$Error) {
                if (!Consumer::useService()->removeStudentCustody($tblStudentCustody)) {
                    $Error = false;
                }
            });
        }

        if ($ParentAccount) {
            // Add new Link
            array_walk($ParentAccount, function ($AccountId) use (&$Error, $tblAccountStudent) {
                $tblAccountCustody = Account::useService()->getAccountById($AccountId);
                if ($tblAccountCustody) {
                    Consumer::useService()->createStudentCustody($tblAccountStudent, $tblAccountCustody,
                        $tblAccountStudent);
                } else {
                    $Error = false;
                }
            });
        }

        if (!$Error) {
            return new Success('Einstellungen wurden erfolgreich gespeichert')
                .new Redirect(Extension::getRequest()->getUrl(), Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Einstellungen konnten nicht gespeichert werden')
                .new Redirect(Extension::getRequest()->getUrl(), Redirect::TIMEOUT_ERROR);
        }
    }
}