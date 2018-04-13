<?php
namespace SPHERE\Application\Transfer\Import\Meerane;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Tharandt
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|string
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {


        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File nicht gefunden')))));
            return $Form;
        }

        /**
         * Prepare
         */
        $File = $File->move($File->getPath(),
            $File->getFilename().'.'.$File->getClientOriginalExtension());

        /**
         * Read
         */
        //$File->getMimeType()
        /** @var PhpExcel $Document */
        $Document = Document::getDocument($File->getPathname());
        if (!$Document instanceof PhpExcel) {
            $Form->setError('File', 'Fehler');
            return $Form;
        }

        $X = $Document->getSheetColumnCount();
        $Y = $Document->getSheetRowCount();

        /**
         * Header -> Location
         */
        $Location = array(
            'Klasse'         => null,
            'Name'           => null,
            'Vorname'        => null,
            'Geburtsdatum'   => null,
            'Geburtsort'     => null,
            'PLZ'            => null,
            'Ort'            => null,
            'Straße'         => null,
            'Name_Vater'     => null,
            'Vorname_Vater'  => null,
            'Name_Mutter'    => null,
            'Vorname_Mutter' => null,
            'TelPrivat'      => null,
            'HandyM'         => null,
            'HandyV'         => null,
            'Konfession'     => null,
            'Mail_1'         => null,
            'Mail_2'         => null
        );

        for ($RunX = 0; $RunX < $X; $RunX++) {
            $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
            if (array_key_exists($Value, $Location)) {
                $Location[$Value] = $RunX;
            }
        }

        /**
         * Import
         */
        if (!in_array(null, $Location, true)) {
            $countStudent = 0;
            $countFather = 0;
            $countMother = 0;
            $countFatherExists = 0;
            $countMotherExists = 0;

            $error = array();
            for ($RunY = 1; $RunY < $Y; $RunY++) {
                set_time_limit(300);
                // Student
                $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                if ($firstName === '' || $lastName === '') {
                    $error[] = 'Zeile: '.($RunY + 1).' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                    continue;
                }

                $tblPerson = Person::useService()->insertPerson(
                    Person::useService()->getSalutationById(3),    //Schüler
                    '',
                    $firstName,
                    '',
                    $lastName,
                    array(
                        0 => Group::useService()->getGroupByMetaTable('COMMON'),
                        1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                    )
                );

                if ($tblPerson === false) {
                    $error[] = 'Zeile: '.($RunY + 1).' Der Schüler konnte nicht angelegt werden.';
                    continue;
                }
                $countStudent++;

                // Student Birthday
                $day = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                    $RunY)));
                if ($day !== '') {
                    try {
                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                    } catch (\Exception $ex) {
                        $birthday = '';
                        $error[] = 'Zeile: '.($RunY + 1).' Ungültiges Geburtsdatum: '.$ex->getMessage();
                    }

                } else {
                    $birthday = '';
                }

                Common::useService()->insertMeta(
                    $tblPerson,
                    $birthday,
                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                    '',
                    '',
                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY))),
                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                    '',
                    ''
                );

                // division
//                $tblSchoolType = false;
                $tblDivision = false;
                $year = 17;
                $division = trim($Document->getValue($Document->getCell($Location['Klasse'],
                    $RunY)));
                if ($division !== '') {
                    $tblYear = Term::useService()->insertYear('20'.$year.'/'.($year + 1));
                    if ($tblYear) {
                        $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                        if (!$tblPeriodList) {
                            // firstTerm
                            $tblPeriod = Term::useService()->insertPeriod(
                                '1. Halbjahr',
                                '01.08.20'.$year,
                                '31.01.20'.($year + 1)
                            );
                            if ($tblPeriod) {
                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                            }

                            // secondTerm
                            $tblPeriod = Term::useService()->insertPeriod(
                                '2. Halbjahr',
                                '01.02.20'.($year + 1),
                                '31.07.20'.($year + 1)
                            );
                            if ($tblPeriod) {
                                Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                            }
                        }

                        if (strlen($division) > 1) {
                            if (is_numeric(substr($division, 0, 2))) {
                                $pos = 2;
                                $level = substr($division, 0, $pos);
                                // remove the "-"
                                if (substr($division, $pos, 1) == '-') {
                                    $pos = 3;
                                    $division = trim(substr($division, $pos));
                                } else {
                                    $division = trim(substr($division, $pos));
                                }
                            } else {
                                $pos = 1;
                                $level = substr($division, 0, $pos);
                                $division = trim(substr($division, $pos));
                            }
                        } else {
                            $level = $division;
                            $division = '';
                        }

//                            $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
//                            $tblSchoolType = Type::useService()->getTypeById(8); // Oberschule
                        $tblSchoolType = Type::useService()->getTypeById(7); // Gymnasium

                        $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                        if ($tblLevel) {
                            $tblDivision = Division::useService()->insertDivision(
                                $tblYear,
                                $tblLevel,
                                $division
                            );
                        }
                    }

                    if ($tblDivision) {
                        Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
                    } else {
                        $error[] = 'Zeile: '.($RunY + 1).' Der Schüler konnte keiner Klasse zugeordnet werden.';
                    }
                }

                // Address

                $CityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));

                $cityDistrict = '';
                $City = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                if (preg_match('!(\w*\s)(OT\s\w*)!is', $City, $Found)) {
                    $CityName = $Found[1];
                    $cityDistrict = $Found[2];
                } else {
                    $CityName = $City;
                }

                $streetName = '';
                $streetNumber = '';
                $street = trim($Document->getValue($Document->getCell($Location['Straße'],
                    $RunY)));
                if (preg_match_all('!\d+!', $street, $matches)) {
                    $pos = strpos($street, $matches[0][0]);
                    if ($pos !== null) {
                        $streetName = trim(substr($street, 0, $pos));
                        $streetNumber = trim(substr($street, $pos));
                    }
                }
                if ($streetName !== '' && $streetNumber !== ''
                    && $CityCode && $CityName
                ) {
                    Address::useService()->insertAddressToPerson(
                        $tblPerson, $streetName, $streetNumber, $CityCode, $CityName,
                        $cityDistrict, '', '', '', null
                    );
                }

                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1); // Sorgeberechtigt;

                // Mother
                $tblPersonMother = null;
                $motherLastName = trim($Document->getValue($Document->getCell($Location['Name_Mutter'],
                    $RunY)));
                $motherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname_Mutter'],
                    $RunY)));
                $title = '';
                if (preg_match('!(\w+\.\s)(\w*)!', $motherFirstName, $FoundTitle)) {
                    if (isset($FoundTitle[1])) {
                        $title = trim($FoundTitle[1]);
                    }
                    if (isset($FoundTitle[2])) {
                        $motherFirstName = $FoundTitle[2];
                    }
                }

                $tblPersonMotherExists = false;
                if ($CityCode !== '' && $motherLastName != '') {
                    $tblPersonMotherExists = Person::useService()->existsPerson(
                        $motherFirstName,
                        $motherLastName,
                        $CityCode
                    );
                }

                if (!$tblPersonMotherExists && $motherLastName != '' && $motherFirstName != '') {
                    $tblGender = Common::useService()->getCommonGenderByName('Weiblich');
                    if ($tblGender) {
                        $gender = $tblGender->getId();
                    } else {
                        $gender = 0;
                    }
                    $tblSalutation = Person::useService()->getSalutationById(2); // Frau

                    $tblPersonMother = Person::useService()->insertPerson(
                        $tblSalutation,
                        $title,
                        $motherFirstName,
                        '',
                        $motherLastName,
                        array(
                            0 => Group::useService()->getGroupByMetaTable('COMMON'),
                            1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                        )
                    );

                    if ($tblPersonMother) {
                        Common::useService()->insertMeta(
                            $tblPersonMother,
                            '',
                            '',
                            $gender,
                            '',
                            '',
                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                            '',
                            ''
                        );
                    }

                    Relationship::useService()->insertRelationshipToPerson(
                        $tblPersonMother,
                        $tblPerson,
                        $tblRelationshipTypeCustody,
                        ''
                    );

                    if ($streetName !== '' && $streetNumber !== ''
                        && $CityCode && $CityName
                    ) {
                        Address::useService()->insertAddressToPerson(
                            $tblPersonMother, $streetName, $streetNumber, $CityCode, $CityName,
                            $cityDistrict, '', '', '', null
                        );
                    }

                    $countMother++;
                } elseif ($tblPersonMotherExists) {

                    Relationship::useService()->insertRelationshipToPerson(
                        $tblPersonMotherExists,
                        $tblPerson,
                        $tblRelationshipTypeCustody,
                        ''
                    );

                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte1 wurde nicht angelegt, da schon eine 
                    Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                    Person verknüpft';

                    $countMotherExists++;
                }

                // Father
                $tblPersonFather = null;
                $fatherLastName = trim($Document->getValue($Document->getCell($Location['Name_Vater'],
                    $RunY)));
                $fatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname_Vater'],
                    $RunY)));
                $title = '';
                if (preg_match('!(\w+\.\s)(\w*)!', $fatherFirstName, $FoundTitle)) {
                    if (isset($FoundTitle[1])) {
                        $title = trim($FoundTitle[1]);
                    }
                    if (isset($FoundTitle[2])) {
                        $fatherFirstName = $FoundTitle[2];
                    }
                }

                $tblPersonFatherExists = false;
                if ($CityCode !== '' && $fatherLastName != '') {
                    $tblPersonFatherExists = Person::useService()->existsPerson(
                        $fatherFirstName,
                        $fatherLastName,
                        $CityCode
                    );
                }

                if (!$tblPersonFatherExists && $fatherLastName != '' && $fatherFirstName != '') {

                    $tblGender = Common::useService()->getCommonGenderByName('Männlich');
                    if ($tblGender) {
                        $gender = $tblGender->getId();
                    } else {
                        $gender = 0;
                    }
                    $tblSalutation = Person::useService()->getSalutationById(1); // Herr

                    $tblPersonFather = Person::useService()->insertPerson(
                        $tblSalutation,
                        $title,
                        $fatherFirstName,
                        '',
                        $fatherLastName,
                        array(
                            0 => Group::useService()->getGroupByMetaTable('COMMON'),
                            1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                        )
                    );

                    if ($tblPersonFather) {
                        Common::useService()->insertMeta(
                            $tblPersonFather,
                            '',
                            '',
                            $gender,
                            '',
                            '',
                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                            '',
                            ''
                        );
                    }

                    Relationship::useService()->insertRelationshipToPerson(
                        $tblPersonFather,
                        $tblPerson,
                        $tblRelationshipTypeCustody,
                        ''
                    );

                    if ($streetName !== '' && $streetNumber !== ''
                        && $CityCode && $CityName
                    ) {
                        Address::useService()->insertAddressToPerson(
                            $tblPersonFather, $streetName, $streetNumber, $CityCode, $CityName,
                            $cityDistrict, '', '', '', null
                        );
                    }

                    $countFather++;
                } elseif ($tblPersonFatherExists) {

                    Relationship::useService()->insertRelationshipToPerson(
                        $tblPersonFatherExists,
                        $tblPerson,
                        $tblRelationshipTypeCustody,
                        ''
                    );

                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte2 wurde nicht angelegt, da schon eine 
                    Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits 
                    existierenden Person verknüpft';

                    $countFatherExists++;
                }

                // Create Student
                Student::useService()->insertStudent($tblPerson, '');
                // PhoneNumber Student
                $phonePrivate = trim($Document->getValue($Document->getCell($Location['TelPrivat'], $RunY)));
                if ($phonePrivate != '' && $tblPersonFather) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phonePrivate, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPerson,
                        $phonePrivate,
                        $tblType,
                        ''
                    );
                }


                // PhoneNumber by "Father"
                $phoneHandyV = trim($Document->getValue($Document->getCell($Location['HandyV'], $RunY)));

                if ($phonePrivate != '' && $tblPersonFather) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phonePrivate, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonFather,
                        $phonePrivate,
                        $tblType,
                        ''
                    );
                }
                if ($phoneHandyV != '' && $tblPersonFather) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phoneHandyV, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonFather,
                        $phoneHandyV,
                        $tblType,
                        ''
                    );
                }

                // PhoneNumber by "Mother"
                $phoneHandyM = trim($Document->getValue($Document->getCell($Location['HandyM'], $RunY)));
                if ($phonePrivate != '' && $tblPersonMother) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phonePrivate, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonMother,
                        $phonePrivate,
                        $tblType,
                        ''
                    );
                }
                if ($phoneHandyM != '' && $tblPersonMother) {
                    $tblType = Phone::useService()->getTypeById(1);
                    if (0 === strpos($phoneHandyM, '01')) {
                        $tblType = Phone::useService()->getTypeById(2);
                    }
                    Phone::useService()->insertPhoneToPerson(
                        $tblPersonMother,
                        $phoneHandyM,
                        $tblType,
                        ''
                    );
                }

                // E-Mail
                $mail1 = trim($Document->getValue($Document->getCell($Location['Mail_1'], $RunY)));
                $mail2 = trim($Document->getValue($Document->getCell($Location['Mail_2'], $RunY)));
                if ($mail1 != '') {
                    if($tblPersonMother){
                        $tblType = Mail::useService()->getTypeById(1);
                        Mail::useService()->insertMailToPerson(
                            $tblPersonMother,
                            $mail1,
                            $tblType,
                            ''
                        );
                    }
                    if($tblPersonFather){
                        $tblType = Mail::useService()->getTypeById(1);
                        Mail::useService()->insertMailToPerson(
                            $tblPersonFather,
                            $mail1,
                            $tblType,
                            ''
                        );
                    }
                }
                if ($mail2 != '') {
                    if($tblPersonMother){
                        $tblType = Mail::useService()->getTypeById(1);
                        Mail::useService()->insertMailToPerson(
                            $tblPersonMother,
                            $mail2,
                            $tblType,
                            ''
                        );
                    }
                    if($tblPersonFather){
                        $tblType = Mail::useService()->getTypeById(1);
                        Mail::useService()->insertMailToPerson(
                            $tblPersonFather,
                            $mail2,
                            $tblType,
                            ''
                        );
                    }
                }
            }

            return
                new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.').
                new Success('Es wurden '.$countMother.' Weibliche Sorgeberechtigte erfolgreich angelegt.').
                ($countMotherExists > 0 ?
                    new Warning($countMotherExists.' Weibliche Sorgeberechtigte exisistieren bereits.') : '').
                new Success('Es wurden '.$countFather.' Männliche Sorgeberechtigte erfolgreich angelegt.').
                ($countFatherExists > 0 ?
                    new Warning($countFatherExists.' Männliche Sorgeberechtigte exisistieren bereits.') : '')
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(
                        'Fehler',
                        $error,
                        Panel::PANEL_TYPE_DANGER
                    )
                ))));

        } else {
            Debugger::screenDump($Location);

            return new Warning(json_encode($Location)).new Danger(
                    "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }
}