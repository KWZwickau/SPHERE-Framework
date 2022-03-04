<?php

namespace SPHERE\Application\Transfer\Import\Standard\Mail;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use PHPExcel_Shared_Date;
use SPHERE\Application\Contact\Mail\Mail as MailAlias;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Gateway\Converter\Sanitizer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Standard\Mail
 */
class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     * @param null $Data
     *
     * @return IFormInterface|Danger|string
     */
    public function createMailsFromFile(IFormInterface $Form = null, UploadedFile $File = null, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (!($tblType = MailAlias::useService()->getTypeById($Data['Type']))) {
            $Form->setError('Data[Type]', 'Bitte geben Sie einen Typ an');
            return $Form;
        }

        $isTest = isset($Data['IsTest']);

        $isOnlyEmail = false;
        $isAccountAlias = false;
        $isAccountRecoveryMail = false;

        $emailFieldName = '';

        if (isset($Data['Radio'])) {
            switch ($Data['Radio']) {
                case 1: $isOnlyEmail = true; break;
                case 2: $isAccountAlias = true; break;
                case 3: $isAccountRecoveryMail = true; break;
            }
        } else {
            return $Form . new Danger('Bitte wählen Sie ein Variante (Radio) aus');
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Vorname' => null,
                    'Nachname' => null
                );

                if ($isOnlyEmail) {
                    $Location['Emailadresse'] = null;
                    $emailFieldName = 'Emailadresse';
                } elseif ($isAccountAlias) {
                    $Location['Benutzer-Alias-Mail'] = null;
                    $emailFieldName = 'Benutzer-Alias-Mail';
                } elseif ($isAccountRecoveryMail) {
                    $Location['Recovery-Mail'] = null;
                    $emailFieldName = 'Recovery-Mail';
                }

                $OptionalLocation = array(
                    'Geburtsdatum' => null
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    } elseif (array_key_exists($Value, $OptionalLocation)) {
                        $OptionalLocation[$Value] = $RunX;
                    }
                }

                $countPersons = 0;
                $countMissingPersons = 0;
                $countDuplicatePersons = 0;
                $countAccounts = 0;
//                $countMissingAccounts = 0;
                $countMultipleAccounts = 0;
                $countAddMail = 0;

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));

                        $mail = trim($Document->getValue($Document->getCell($Location[$emailFieldName], $RunY)));
                        $mail = str_replace(' ', '', $mail);

                        $birthday = $OptionalLocation['Geburtsdatum'] == null
                            ? ''
                            : trim($Document->getValue($Document->getCell($OptionalLocation['Geburtsdatum'], $RunY)));
                        if ($birthday) {
                            if (strpos($birthday, '.') === false) {
                                $birthday = date('d.m.Y', PHPExcel_Shared_Date::ExcelToPHP($birthday));
                            }
                        }

                        $addMail = false;
                        $tblPerson = false;
                        if ($firstName !== '' && $lastName !== '' && $mail != '') {
                            if (($tblPersonList = Person::useService()->getPersonAllByFirstNameAndLastName($firstName, $lastName))) {
                                $tblPerson = $this->getPersonByList($tblPersonList, $firstName, $lastName, $birthday,
                                    $RunY, $error, $countPersons, $countDuplicatePersons, $addMail);
                            } elseif (($tblPersonList = Person::useService()->getPersonAllByFirstNameAndLastName($this->refactorName($firstName), $this->refactorName($lastName)))) {
                                $tblPerson = $this->getPersonByList($tblPersonList, $firstName, $lastName, $birthday,
                                    $RunY, $error, $countPersons, $countDuplicatePersons, $addMail);
                            } elseif (($tblPersonList = Person::useService()->getPersonListLikeFirstNameAndLastName($this->refactorName($firstName), $this->refactorName($lastName)))) {
                                $tblPerson = $this->getPersonByList($tblPersonList, $firstName, $lastName, $birthday,
                                    $RunY, $error, $countPersons, $countDuplicatePersons, $addMail);
                            } else {
                                $countMissingPersons++;
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde nicht gefunden';
                            }

                            if ($addMail && $tblPerson) {
                                $personMailIsAccountAlias = false;
                                $personMailIsRecoveryMail = false;

                                if ($isAccountAlias || $isAccountRecoveryMail) {
                                    $addMail = false;
                                    // findAccounts
//                                    if (($tblAccountList = Account::useService()->getAccountAllByPersonForUCS($tblPerson))) {
                                    if (($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))) {
                                        if (count($tblAccountList) == 1) {
                                            $countAccounts++;
                                            $tblAccount = current($tblAccountList);
                                            if ($isAccountAlias) {
                                                if (!$isTest) {
                                                    if (Account::useService()->changeUserAlias($tblAccount, $mail)) {
                                                        $addMail = true;
                                                        $personMailIsAccountAlias = true;
                                                    } else {
                                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                                            . ' Alias konnte nicht am Benutzerkonto gespeichert werden.';
                                                    }
                                                }
                                            } elseif ($isAccountRecoveryMail) {
                                                if (!$isTest) {
                                                    if (Account::useService()->changeRecoveryMail($tblAccount, $mail)) {
                                                        $addMail = true;
                                                        $personMailIsRecoveryMail = true;
                                                    } else {
                                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                                            . ' Passwort vergessen E-Mail konnte nicht am Benutzerkonto gespeichert werden.';
                                                    }
                                                }
                                            }
                                        } else {
                                            $countMultipleAccounts++;
                                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
                                                . ' besitzt mehrere Benutzerkonten';
                                        }
                                    } else {
//                                        $countMissingAccounts++;
//                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName
//                                            . ' besitzt kein Benutzerkonto';

                                        // Email-Adresse vormerken
                                        $errorMessage = '';
                                        if (Account::useService()->isUserAliasUnique($tblPerson, $mail, $errorMessage)) {
                                            $addMail = true;
                                            $personMailIsAccountAlias = $isAccountAlias;
                                            $personMailIsRecoveryMail = $isAccountRecoveryMail;
                                        } else {
                                            $error[] = 'Zeile: ' . ($RunY + 1) . $errorMessage;
                                        }
                                    }
                                } elseif ($isOnlyEmail) {
                                    $addMail = true;
                                }

                                // Fehlerhafte E-Mail (Umlaute vor dem @, nur ein zeichen nach dem letzten Punkt, etc.)
                                $Sanitizer = new Sanitizer();
                                if(!$Sanitizer->validateMailAddress($mail)){
                                    $addMail = false;
                                    $error[] = 'Zeile: '.($RunY + 1).' '.$mail.' ist als E-Mail Adresse nicht gültig';
                                }

                                if ($addMail && !$isTest) {
                                    // alle Emailadressen der Person mit isAccountUserAlias zurücksetzen
                                    if ($isAccountAlias
                                        && (($tblMailToPersonList = MailAlias::useService()->getMailAllByPerson($tblPerson)))
                                    ) {
                                        foreach ($tblMailToPersonList as $tblToPerson) {
                                            if ($tblToPerson->isAccountUserAlias()) {
                                                MailAlias::useService()->updateMailToPersonService(
                                                    $tblToPerson, $tblToPerson->getTblMail()->getAddress(),
                                                    $tblToPerson->getTblType(), $tblToPerson->getRemark(),
                                                    false, $tblToPerson->isAccountRecoveryMail()
                                                );
                                            }
                                        }
                                    }
                                    // alle Emailadressen der Person mit isAccountRecoveryMail zurücksetzen
                                    if ($isAccountRecoveryMail
                                        && (($tblMailToPersonList = MailAlias::useService()->getMailAllByPerson($tblPerson)))
                                    ) {
                                        foreach ($tblMailToPersonList as $tblToPerson) {
                                            if ($tblToPerson->isAccountRecoveryMail()) {
                                                MailAlias::useService()->updateMailToPersonService(
                                                    $tblToPerson, $tblToPerson->getTblMail()->getAddress(),
                                                    $tblToPerson->getTblType(), $tblToPerson->getRemark(),
                                                    $tblToPerson->isAccountUserAlias(), false
                                                );
                                            }
                                        }
                                    }

                                    if (MailAlias::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $mail,
                                        $tblType,
                                        '',
                                        $personMailIsAccountAlias,
                                        $personMailIsRecoveryMail
                                    )) {
                                        $countAddMail++;
                                    } else {
                                        $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Emailadresse konnte nicht angelegt werden.';
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Emailadresse wurde nicht angelegt, da sie nicht vollständig ist.';
                        }
                    }

                    return
                        new Success('Es wurden ' . $countPersons . ' Personen erfolgreich gefunden.') .
                            ($countAccounts > 0 ? new Success('Es wurden ' . $countAccounts . ' Benutzerkonten gefunden') : '') .
                            ($countAddMail > 0 ? new Success('Es wurden ' . $countAddMail . ' Emailadressen erfolgreich angelegt') : '') .
                            ($countDuplicatePersons > 0 ? new Warning($countDuplicatePersons . ' Doppelte Personen gefunden') : '') .
                            ($countMissingPersons > 0 ? new Warning($countMissingPersons . ' Personen nicht gefunden') : '') .
//                            ($countMissingAccounts > 0 ? new Warning($countMissingAccounts . ' Benutzerkonten nicht gefunden') : '') .
                            ($countMultipleAccounts > 0 ? new Warning($countMultipleAccounts . ' für Personen wurde mehrere Benutzerkonten gefunden') : '') .
                            (empty($error)
                                ? ''
                                : new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                    new Panel(
                                        'Fehler',
                                        $error,
                                        Panel::PANEL_TYPE_DANGER
                                    )
                                )))))
                        ;
                } else {
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function refactorName($name)
    {
        $name = str_replace('ae', 'ä', $name);
        $name = str_replace('ue', 'ü', $name);
        $name = str_replace('oe', 'ö', $name);
        $name = str_replace('ss', 'ß', $name);

        $name = str_replace('Ae', 'Ä', $name);
        $name = str_replace('Ue', 'Ü', $name);
        $name = str_replace('Oe', 'Ö', $name);

        return $name;
    }

    /**
     * @param $tblPersonList
     * @param $firstName
     * @param $lastName
     * @param $birthday
     * @param $RunY
     * @param $error
     * @param $countPersons
     * @param $countDuplicatePersons
     * @param $addMail
     *
     * @return false|TblPerson
     */
    private function getPersonByList($tblPersonList, $firstName, $lastName, $birthday, $RunY, &$error, &$countPersons, &$countDuplicatePersons, &$addMail)
    {
        if ($birthday == '') {
            if (count($tblPersonList) == 1) {
                $countPersons++;
                $addMail = true;

                return current($tblPersonList);
            } else {
                $countDuplicatePersons++;
                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' wurde mehrmals gefunden';
            }
        } else {
            $result = array();
            foreach ($tblPersonList as $tblPerson) {
                $tblCommon = Common::useService()->getCommonByPerson($tblPerson);
                if (!$tblCommon) {
                    continue;
                }
                $tblCommonBirthDates = $tblCommon->getTblCommonBirthDates();
                if (!$tblCommonBirthDates) {
                    continue;
                }

                if ($birthday == $tblCommonBirthDates->getBirthday()) {
                    $result[] = $tblPerson;
                }
            }

            $count = count($result);
            if ($count == 1) {
                $countPersons++;
                $addMail = true;

                return $result[0];
            } elseif ($count == 0) {
                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' mit dem Geburtsdatum: '
                    . $birthday . ' wurde nicht gefunden';
            } else {
                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person ' . $firstName . ' ' . $lastName . ' mit dem Geburtsdatum: '
                    . $birthday . ' wurde mehrmals gefunden';
                $countDuplicatePersons++;
            }
        }

        return false;
    }
}