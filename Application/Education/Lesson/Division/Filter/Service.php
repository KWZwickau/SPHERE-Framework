<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2018
 * Time: 15:44
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Service
{

    /**
     * @param IFormInterface $form
     * @param TblDivisionSubject $tblDivisionSubject
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public static function setFilter(IFormInterface $form, TblDivisionSubject $tblDivisionSubject, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $filter = new Filter($tblDivisionSubject);
        $filter->setFilter($Data);
        $filter->save();

        return new Success('Die verfügbaren Schüler werden gefiltert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Lesson/Division/SubjectStudent/Add', Redirect::TIMEOUT_SUCCESS, array(
                'Id' => ($tblDivision = $tblDivisionSubject->getTblDivision()) ? $tblDivision->getId() : 0,
                'DivisionSubjectId' => $tblDivisionSubject->getId()
            ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool        $isAccordion
     * @param integer     $totalCount
     * @param bool        $IsTableAccordion
     *
     * @return array|bool|Warning
     */
    public static function getDivisionMessageTable(TblDivision $tblDivision, $isAccordion = false, &$totalCount, $IsTableAccordion = false)
    {

        $list = array();
        $missingGroupList = array();
        $profileList = array();
        $orientationList = array();

        $isSekTwo = false;
        $isLevelTen = false;
        $checkOrientation = false;
        $checkProfile = false;
        if (($tblLevel = $tblDivision->getTblLevel())
            && ($tblType = $tblLevel->getServiceTblType())
        ) {
            if ($tblType->getName() == 'Mittelschule / Oberschule') {
                if (floatval($tblLevel->getName()) == 10) {
                    $isLevelTen = true;
                } elseif (preg_match('!(0?(7|8|9))!is', $tblLevel->getName())) {
                    $checkOrientation = true;
                }
            } elseif ($tblType->getName() == 'Gymnasium') {
                if (floatval($tblLevel->getName()) == 11
                    || floatval($tblLevel->getName()) == 12
                ) {
                    $isSekTwo = true;
                } elseif (preg_match('!(0?(8|9|10))!is', $tblLevel->getName())) {
                    $checkProfile = true;
                }
            }
        }

        $divisionSubjectList = array();
        if (($tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($tblDivision, true))) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    $filter = new Filter($tblDivisionSubject);
                    $filter->load();

                    if ($filter->isFilterSet()) {
                        $list = $filter->getPersonAllWhereFilterIsNotFulfilled($list);
                    }
                } else {
                    $divisionSubjectList[] = $tblDivisionSubject;
                }
            }
        }

        if (!empty($divisionSubjectList)) {
            /** @var TblDivisionSubject $item */
            foreach ($divisionSubjectList as $item) {
                $tblSubject = $item->getServiceTblSubject();
                // in der Sekundarstufe II müssen alle Fächer mindestens eine Gruppe besitzen -> da so die Unterscheidung zwischen
                // Grundkurs und Leistungskurs erfolgt
                if ($isSekTwo) {
                    if ($tblSubject
                        && !Division::useService()->exitsSubjectGroup($tblDivision, $tblSubject)
                    ) {
                        $missingGroupList[] = new Exclamation() .
                            ($isAccordion ? ' In der Klasse ' . $tblDivision->getDisplayName()
                                . ' im Fach ' . $tblSubject->getDisplayName() . ' wurde keine Gruppe angelegt.'
                                : ' Im Fach ' . $tblSubject->getDisplayName() . ' wurde keine Gruppe angelegt.'
                            );
                    }
                // in der Klasse 10 Oberschule müssen Gruppen bei den Wahlfächern angelegt sein
                } elseif ($isLevelTen) {
                    if ($tblSubject
                        && Subject::useService()->isElective($tblSubject)
                        && !Division::useService()->exitsSubjectGroup($tblDivision, $tblSubject)
                    ) {
                        $missingGroupList[] = new Exclamation() .
                            ($isAccordion ? ' In der Klasse ' . $tblDivision->getDisplayName()
                                . ' im Fach ' . $tblSubject->getDisplayName() . ' wurde keine Gruppe angelegt.'
                                : ' Im Fach ' . $tblSubject->getDisplayName() . ' wurde keine Gruppe angelegt.'
                            );
                    }
                }

                if ($checkProfile && $tblSubject) {
                    if (Subject::useService()->isProfile($tblSubject)) {
                        $profileList[$tblSubject->getId()] = $tblSubject;
                    }
                }

                if ($checkOrientation && $tblSubject) {
                    if (Subject::useService()->isOrientation($tblSubject)) {
                        $orientationList[$tblSubject->getId()] = $tblSubject;
                    }
                }
            }

            if ($checkProfile && !empty($profileList) && count($profileList) > 1) {
                foreach ($profileList as $tblSubjectItem) {
                    if (!Division::useService()->exitsSubjectGroup($tblDivision, $tblSubjectItem)) {
                        $missingGroupList[] = new Exclamation() .
                            ($isAccordion ? ' In der Klasse ' . $tblDivision->getDisplayName()
                                . ' im Fach ' . $tblSubjectItem->getDisplayName() . ' wurde keine Gruppe angelegt.'
                                : ' Im Fach ' . $tblSubjectItem->getDisplayName() . ' wurde keine Gruppe angelegt.'
                            );
                    }
                }
            }
            if ($checkOrientation && !empty($orientationList) && count($orientationList) > 1) {
                foreach ($orientationList as $tblSubjectItem) {
                    if (!Division::useService()->exitsSubjectGroup($tblDivision, $tblSubjectItem)) {
                        $missingGroupList[] = new Exclamation() .
                            ($isAccordion ? ' In der Klasse ' . $tblDivision->getDisplayName()
                                . ' im Fach ' . $tblSubjectItem->getDisplayName() . ' wurde keine Gruppe angelegt.'
                                : ' Im Fach ' . $tblSubjectItem->getDisplayName() . ' wurde keine Gruppe angelegt.'
                            );
                    }
                }
            }
        }

        // Validierung "Stundentaffel"
        $list = self::hasDivisionAllObligations($tblDivision, $list);

        if (!empty($list) || !empty($missingGroupList)) {
            $contentTable = array();
            $count = 1;
            $countMessages = 0;

            $tableOption = array(
                "paging" => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
                "info" => false,  // Deaktivieren Such-Info
                "sort" => false,
                "responsive" => false
            );

            if(!empty($list)){
                list($contentTable, $countMessages) = self::formatFilterListMessages($list, $contentTable, $count,
                    $countMessages);
            }

            if (!empty($missingGroupList)) {
                foreach ($missingGroupList as $value) {
                    $contentTable[] = array(
                        'Name' => '&nbsp;',
                        'Field' => '&nbsp;',
                        'Value' => '&nbsp;',
                        'DivisionSubjects' => $value,
                    );

                    $countMessages++;
                }
            }

            $totalCount += $countMessages;

            if ($isAccordion) {
                return array(
                    'Header' => 'Klasse ' . $tblDivision->getDisplayName() . ' (' . $countMessages . ' Meldungen)',
                    'Content' => (new TableData(
                    $contentTable,
                    null,
                    array(
                        'Name' => 'Schüler',
                        'Field' => 'Eigenschaft / Feld',
                        'Value' => 'Personenverwaltung',
                        'DivisionSubjects' => 'Bildungsmodul'
                    ),
                    $tableOption
                    ))->setHash('/Education/Lesson/Division' . 'InterfaceMessages' . $tblDivision->getName() . $tblDivision->getId())
                );
            } else {
                return new Warning(
                    new Exclamation() . new Bold(' Folgende Einstellungen stimmen nicht zwischen der Personenverwaltung und dem Bildungsmodul überein:')
                    . '</br></br>'
                .($IsTableAccordion
                    ? (new Accordion())->addItem('Warnungen '.new Small('('.$totalCount.')'),
                            new TableData(
                                $contentTable,
                                null,
                                array(
                                    'Name' => 'Schüler',
                                    'Field' => 'Eigenschaft / Feld',
                                    'Value' => 'Personenverwaltung',
                                    'DivisionSubjects' => 'Bildungsmodul'
                                ),
                                $tableOption
                            ))
                    : new TableData(
                        $contentTable,
                        null,
                        array(
                            'Name' => 'Schüler',
                            'Field' => 'Eigenschaft / Feld',
                            'Value' => 'Personenverwaltung',
                            'DivisionSubjects' => 'Bildungsmodul'
                        ),
                        $tableOption
                    )
                ));
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|Warning
     */
    public static function getPersonMessageTable(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblDivisionList = $tblStudent->getCurrentDivisionList())
        ) {
            $list = array();
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                    foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                        if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                            && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                        ){
                            $filter = new Filter($tblDivisionSubject);
                            $filter->load();

                            if ($filter->isFilterSet()) {
                                // Validierung Bildungsmodul -> Schülerakte
                                if (Division::useService()->exitsSubjectStudent($tblDivisionSubject, $tblPerson)) {
                                    $list = $filter->getIsNotFulfilledByPerson(
                                        $tblPerson,
                                        $tblSubject,
                                        $tblSubjectGroup,
                                        $tblDivisionSubject,
                                        $list,
                                        true
                                    );
                                } // Validierung Schülerakte -> Bildungsmodul
                                else {
                                    $list = $filter->getIsFulfilledButNotInGroupByPerson($tblPerson, $tblSubject,
                                        $tblSubjectGroup,
                                        $tblDivisionSubject, $list);
                                }
                            }
                        }
                    }
                }

                // Validierung "Stundentaffel"
                if (($tblLevel = $tblDivision->getTblLevel())
                    && ($tblSchoolType = $tblLevel->getServiceTblType())
                ) {
                    if (($tblSetting = Consumer::useService()->getSetting(
                            'Reporting', 'KamenzReport', 'Validation', 'FirstForeignLanguageLevel'))
                        && $tblSetting->getValue()
                    ) {
                        $firstForeignLanguageLevel = $tblSetting->getValue();
                    } else {
                        $firstForeignLanguageLevel = 1;
                    }

                    if (floatval($tblLevel->getName() >= $firstForeignLanguageLevel)) {
                        $validateFirstLanguage = true;
                    } else {
                        $validateFirstLanguage = false;
                    }

                    $list = self::hasPersonAllObligations(
                        $tblPerson,
                        $tblSchoolType,
                        $tblLevel,
                        $list,
                        $validateFirstLanguage
                    );
                }
            }

            if (!empty($list)) {
                $contentTable = array();
                $count = 1;
                $countMessages = 0;

                /** @noinspection PhpUnusedLocalVariableInspection */
                list($contentTable, $countMessages) = self::formatFilterListMessages($list, $contentTable, $count,
                    $countMessages);

                return new Warning(
                    new Exclamation() . new Bold(' Folgende Einstellungen stimmen nicht zwischen der Personenverwaltung und dem Bildungsmodul überein:')
                    . '</br></br>'
                    . new TableData(
                        $contentTable,
                        null,
                        array(
                            'Field' => 'Eigenschaft / Feld',
                            'Value' => 'Personenverwaltung',
                            'DivisionSubjects' => 'Bildungsmodul'
                        ),
                        false
                    )
                );
            }
        }

        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param $list
     *
     * @return array
     */
    public static function hasDivisionAllObligations(TblDivision $tblDivision, $list)
    {
        if (($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {

            if (($tblSetting = Consumer::useService()->getSetting(
                    'Reporting', 'KamenzReport', 'Validation', 'FirstForeignLanguageLevel'))
                && $tblSetting->getValue()
            ) {
                $firstForeignLanguageLevel = $tblSetting->getValue();
            } else {
                $firstForeignLanguageLevel = 1;
            }

            if (floatval($tblLevel->getName() >= $firstForeignLanguageLevel)) {
                $validateFirstLanguage = true;
            } else {
                $validateFirstLanguage = false;
            }

            foreach ($tblPersonList as $tblPerson) {
                $list = self::hasPersonAllObligations($tblPerson, $tblSchoolType, $tblLevel, $list, $validateFirstLanguage);
            }
        }

        return $list;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType $tblSchoolType
     * @param TblLevel $tblLevel
     * @param $list
     * @param bool $validateFirstLanguage
     *
     * @return array
     */
    public static function hasPersonAllObligations(TblPerson $tblPerson, TblType $tblSchoolType, TblLevel $tblLevel, $list, $validateFirstLanguage)
    {
        $tblStudent = $tblPerson->getStudent();

        // Keine 1. Fremdsprache hinterlegt.
        if ($validateFirstLanguage
            && (!$tblStudent
            || !$tblStudent->getTblSubjectForeignLanguage(1))
        ) {
            $field = Filter::DESCRIPTION_SUBJECT_FOREIGN_LANGUAGE;
            $value = new Exclamation() . ' Keine 1. Fremdsprache hinterlegt.';
            if (!isset($list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage'])) {
                $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Field'] = $field;
                $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'] = $value;
                $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['DivisionSubjects'] = '';
            } else {
                $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'] .=
                    (empty($list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'])
                        ? ''
                        : '</br>')
                    . $value;
            }
        }

        // Keine Religion hinterlegt.
        if (!$tblStudent
            || !$tblStudent->getTblSubjectReligion()
        ) {
            $field = Filter::DESCRIPTION_SUBJECT_RELIGION;
            $value = new Exclamation() . ' Keine Religion hinterlegt.';
            if (!isset($list[$tblPerson->getId()]['Filters']['SubjectReligion'])) {
                $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Field'] = $field;
                $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Value'] = $value;
                $list[$tblPerson->getId()]['Filters']['SubjectReligion']['DivisionSubjects'] = '';
            } else {
                $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Value'] .=
                    (empty($list[$tblPerson->getId()]['Filters']['SubjectReligion']['Value'])
                        ? ''
                        : '</br>')
                    . $value;
            }
        }

        if (($tblSchoolType->getName() == 'Mittelschule / Oberschule')) {

            // OS/MS in Klassen 7-9 muss ein Neigungskurs oder eine 2. Fremdsprache hinterlegt sein
            if (preg_match('!(0?(7|8|9))!is', $tblLevel->getName())) {
                if (!$tblStudent
                    || (!$tblStudent->getTblSubjectOrientation() && !$tblStudent->getTblSubjectForeignLanguage(2))
                ) {
                    $field = Filter::DESCRIPTION_SUBJECT_ORIENTATION;
                    $value = new Exclamation() . ' Kein Neigungskurs/2.FS hinterlegt.';
                    if (!isset($list[$tblPerson->getId()]['Filters']['SubjectOrientation'])) {
                        $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Field'] = $field;
                        $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Value'] = $value;
                        $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['DivisionSubjects'] = '';
                    } else {
                        $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Value'] .=
                            (empty($list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Value'])
                                ? ''
                                : '</br>')
                            . $value;
                    }
                }
            }

            // OS/MS in Klassen 7-10 muss ein Bildungsgang hinterlegt sein
            if (preg_match('!(0?(7|8|9|10))!is', $tblLevel->getName())) {
                if (!$tblStudent
                    || (!$tblStudent->getCourse())
                ) {
                    $field = Filter::DESCRIPTION_COURSE;
                    $value = new Exclamation() . ' Kein Bildungsgang hinterlegt.';
                    if (!isset($list[$tblPerson->getId()]['Filters']['Course'])) {
                        $list[$tblPerson->getId()]['Filters']['Course']['Field'] = $field;
                        $list[$tblPerson->getId()]['Filters']['Course']['Value'] = $value;
                        $list[$tblPerson->getId()]['Filters']['Course']['DivisionSubjects'] = '';
                    } else {
                        $list[$tblPerson->getId()]['Filters']['Course']['Value'] .=
                            (empty($list[$tblPerson->getId()]['Filters']['Course']['Value'])
                                ? ''
                                : '</br>')
                            . $value;
                    }
                }
            }

            // todo Wahlfächer nur Klasse 10?
        }

        if (($tblSchoolType->getName() == 'Gymnasium')) {

            // Gym in Klassen 6-10 muss eine 2. Fremdsprache hinterlegt sein
            if (preg_match('!(0?(6|7|8|9|10))!is', $tblLevel->getName())) {
                if (!$tblStudent
                    || !$tblStudent->getTblSubjectForeignLanguage(2)
                ) {
                    $field = Filter::DESCRIPTION_SUBJECT_FOREIGN_LANGUAGE;
                    $value = new Exclamation() . ' Keine 2. Fremdsprache hinterlegt.';
                    if (!isset($list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage'])) {
                        $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Field'] = $field;
                        $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'] = $value;
                        $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['DivisionSubjects'] = '';
                    } else {
                        $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'] .=
                            (empty($list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'])
                                ? ''
                                : '</br>')
                            . $value;
                    }
                }
            }

            // Gym in Klassen 8-10 muss ein Profil hinterlegt sein
            if (preg_match('!(0?(8|9|10))!is', $tblLevel->getName())) {
                if (!$tblStudent
                    || !$tblStudent->getTblSubjectProfile()
                ) {
                    $field = Filter::DESCRIPTION_SUBJECT_PROFILE;
                    $value = new Exclamation() . ' Kein Profil hinterlegt.';
                    if (!isset($list[$tblPerson->getId()]['Filters']['SubjectProfile'])) {
                        $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Field'] = $field;
                        $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Value'] = $value;
                        $list[$tblPerson->getId()]['Filters']['SubjectProfile']['DivisionSubjects'] = '';
                    } else {
                        $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Value'] .=
                            (empty($list[$tblPerson->getId()]['Filters']['SubjectProfile']['Value'])
                                ? ''
                                : '</br>')
                            . $value;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @param $list
     * @param $contentTable
     * @param $count
     * @param $countMessages
     *
     * @return array
     */
    private static function formatFilterListMessages($list, $contentTable, &$count, $countMessages)
    {

        $hasEditButton = true;
        $tblStudentGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        foreach ($list as $personId => $filters) {
            if (($tblPerson = Person::useService()->getPersonById($personId))
                && is_array($filters)
            ) {
                foreach ($filters as $identifier => $filterArray) {
                    if (is_array($filterArray)) {
                        foreach ($filterArray as $item) {
                            $contentTable[$count]['Name'] = $tblPerson->getLastFirstName()
                                . ($hasEditButton
                                    ? new PullRight(new Standard(
                                        '',
                                        '/People/Person',
                                        new \SPHERE\Common\Frontend\Icon\Repository\Person(),
                                        array(
                                            'Id' => $tblPerson->getId(),
                                            'Group' => $tblStudentGroup ? $tblStudentGroup->getId() : 0
                                        ),
                                        'Zur Person wechseln'
                                    ))
                                    : '');

                            if (isset($item['Field'])) {
                                $contentTable[$count]['Field'] = $item['Field'];
                            } else {
                                $contentTable[$count]['Field'] = '';
                            }

                            if (isset($item['Value'])) {
                                $contentTable[$count]['Value'] = $item['Value'];
                            } else {
                                $contentTable[$count]['Value'] = '';
                            }

                            if (isset($item['DivisionSubjects']) && is_array($item['DivisionSubjects'])) {
                                foreach ($item['DivisionSubjects'] as $divisionSubjectId => $text) {
                                    // links zu den Gruppen
                                    if($hasEditButton
                                        && ($tblDivisionSubject = Division::useService()->getDivisionSubjectById($divisionSubjectId))
                                        && ($tblDivision = $tblDivisionSubject->getTblDivision())
                                    ){
                                        $text .= new PullRight(new Standard(
                                            '',
                                            '/Education/Lesson/Division/SubjectStudent/Add',
                                            new Edit(),
                                            array(
                                                'Id' => $tblDivision->getId(),
                                                'DivisionSubjectId' => $tblDivisionSubject->getId()
                                            ),
                                            'Schüler zuordnen'
                                        ));

                                        $text = new PullClear($text);
                                    }

                                    $countMessages++;
                                    if (isset($contentTable[$count]['DivisionSubjects'])) {
                                        $contentTable[$count]['DivisionSubjects'] .= new Container($text);
                                    } else {
                                        $contentTable[$count]['DivisionSubjects'] = new Container($text);
                                    }
                                }
                            } else {
                                $countMessages++;
                                $contentTable[$count]['DivisionSubjects'] = '';
                            }

                            $count++;
                        }
                    }
                }
            }
        }

        return array($contentTable, $countMessages);
    }
}