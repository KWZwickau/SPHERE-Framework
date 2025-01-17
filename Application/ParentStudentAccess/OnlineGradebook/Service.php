<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineGradebook;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblStudentCustody;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;

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