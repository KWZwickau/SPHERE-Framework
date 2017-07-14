<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 23.06.2017
 * Time: 07:33
 */

namespace SPHERE\Application\Document\Standard\KamenzReport;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;

/**
 * Class Service
 *
 * @package SPHERE\Application\Document\Standard\KamenzReport
 */
class KamenzService
{

    /**
     * @param TblType $tblSchoolType
     *
     * @return TableData
     */
    public static function validate(TblType $tblSchoolType)
    {

        $studentList = array();
        if (($tblDivisionList = Division::useService()->getDivisionAll())) {
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblLevel = $tblDivision->getTblLevel())
                    && !$tblLevel->getIsChecked()
                    && ($tblType = $tblLevel->getServiceTblType())
                    && ($tblType->getId() == $tblSchoolType->getId())
                ) {

                    if (($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                        foreach ($tblPersonList as $tblPerson) {

                            $gender = new Warning('Keine Geschlecht hinterlegt.', new Exclamation());
                            $birthday = new Warning('Kein Geburtsdatum hinterlegt.', new Exclamation());
                            $nationality = '';
                            if (($tblCommon = $tblPerson->getCommon())) {
                                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                                    if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                                        $gender = $tblGender->getName();
                                    }
                                    if (($birthdayDate = $tblCommonBirthDates->getBirthday())) {
                                        $birthday = $birthdayDate;
                                    }
                                }
                                if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                                    $nationality = $tblCommonInformation->getNationality();
                                }
                            }

                            $foreignLanguages = self::getForeignLanguages($tblPerson);

                            $studentList[$tblPerson->getId()] = array(
                                'Division' => $tblDivision->getDisplayName(),
                                'Name' => $tblPerson->getLastFirstName(),
                                'Gender' => $gender,
                                'Birthday' => $birthday,
                                'ForeignLanguage1' => isset($foreignLanguages[1]) ? $foreignLanguages[1] : '',
                                'ForeignLanguage2' => isset($foreignLanguages[2]) ? $foreignLanguages[2] : '',
                                'ForeignLanguage3' => isset($foreignLanguages[3]) ? $foreignLanguages[3] : '',
                                'ForeignLanguage4' => isset($foreignLanguages[4]) ? $foreignLanguages[4] : '',
                                'Religion' => self::getReligion($tblPerson),
                                'Nationality' => $nationality
                            );

                            if (($tblSchoolType->getName() == 'Mittelschule / Oberschule')) {
                                if (($orientation = self::getOrientation($tblPerson))) {
                                    $studentList[$tblPerson->getId()]['Orientation'] = $orientation;
                                } elseif (preg_match('!(0?(7|8|9))!is', $tblLevel->getName())) {
                                    $studentList[$tblPerson->getId()]['Orientation']
                                        = new Warning('Kein Neigungskurs hinterlegt.', new Exclamation());
                                }
                            }
                        }
                    }
                }
            }
        }

        $columns =  array(
            'Division' => 'Klasse',
            'Name' => 'Name',
            'Gender' => 'Geschlecht',
            'Birthday' => 'Geburtsdatum',
            'ForeignLanguage1' => '1. FS',
            'ForeignLanguage2' => '2. FS',
            'ForeignLanguage3' => '3. FS',
            'ForeignLanguage4' => '4. FS',
            'Religion' => 'Religion',
            'Nationality' => 'Staatsangehörigkeit'
        );

        if (($tblSchoolType->getName() == 'Mittelschule / Oberschule')) {
            $columns['Orientation'] = 'Neigungskurs';
        }

        return new TableData(
            $studentList,
            null,
            $columns,
            array(
                'paging' => false,
                'iDisplayLength' => -1,
                'order' => array(array(0, 'asc'), array(1, 'asc')),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 0),
                ),
                'responsive' => false
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    private static function getForeignLanguages(TblPerson $tblPerson)
    {
        $subjects = array();
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {

            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    && ($tblStudentSubjectRanking = $tblStudentSubject->getTblStudentSubjectRanking())
                ) {

                    $subjects[$tblStudentSubjectRanking->getIdentifier()] = $tblSubject->getAcronym();
                }
            }
        }

        return $subjects;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getReligion(TblPerson $tblPerson)
    {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && (($religion = self::getSubjectByStudentSubjectType($tblPerson, $tblStudentSubjectType)))
        ) {
            return $religion;
        }

        return new Warning('Keine Religion hinterlegt.', new Exclamation());
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private static function getOrientation(TblPerson $tblPerson)
    {

        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
            && (($religion = self::getSubjectByStudentSubjectType($tblPerson, $tblStudentSubjectType)))
        ) {
            return $religion;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblStudentSubjectType $tblStudentSubjectType
     *
     * @return bool|string
     */
    private static function getSubjectByStudentSubjectType(TblPerson $tblPerson, TblStudentSubjectType $tblStudentSubjectType)
    {

        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType(
                $tblStudent, $tblStudentSubjectType
            ))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            if (($tblStudentSubject = reset($tblStudentSubjectList))
                && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
            ) {
                return $tblSubject->getAcronym();
            }
        }

        return false;
    }
}