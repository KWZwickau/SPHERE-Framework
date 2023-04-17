<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.06.2018
 * Time: 10:16
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;

/**
 * Class Filter
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Filter extends Extension
{

    const DESCRIPTION_GROUP = 'Personengruppe';
    const DESCRIPTION_GENDER = 'Personendaten: Geschlecht';
    const DESCRIPTION_COURSE = 'Schülerakte: Bildungsgang';
    const DESCRIPTION_SUBJECT_PROFILE = 'Schülerakte: Profil';
    const DESCRIPTION_SUBJECT_FOREIGN_LANGUAGE = 'Schülerakte: Fremdsprache';
    const DESCRIPTION_SUBJECT_RELIGION = 'Schülerakte: Religion';
    const DESCRIPTION_SUBJECT_ELECTIVE = 'Schülerakte: Wahlfach';

    /**
     * @var bool|TblGroup
     */
    protected $tblGroup = false;

    /**
     * @var bool|TblCommonGender
     */
    protected $tblGender = false;

    /**
     * @var bool|TblCourse
     */
    protected $tblCourse = false;

    /**
     * @var bool|TblSubject
     */
    protected $tblSubjectOrientation = false;

    /**
     * @var bool|TblSubject
     */
    protected $tblSubjectProfile = false;

    /**
     * @var bool|TblSubject
     */
    protected $tblSubjectForeignLanguage = false;

    /**
     * @var bool|TblSubject
     */
    protected $tblSubjectReligion = false;

    /**
     * @var bool|TblSubject
     */
    protected $tblSubjectElective = false;

    /**
     * @var bool|TblDivision
     */
    protected $tblDivision = false;

    /**
     * @var bool|TblDivisionSubject
     */
    protected $tblDivisionSubject = false;

    /**
     * @var bool
     */
    protected $isFilterSet = false;

    /**
     * @var array
     */
    protected $header = array();

    public function __construct(TblDivisionSubject $tblDivisionSubject)
    {
        $this->tblDivisionSubject = $tblDivisionSubject;
        if (($tblDivision = $tblDivisionSubject->getTblDivision())) {
            $this->tblDivision = $tblDivision;
        }
    }

    /**
     * @param $Filter
     */
    public function setFilter($Filter)
    {

        $header = array();

        if (isset($Filter['Group'])
            && ($tblGroup = Group::useService()->getGroupById($Filter['Group']))
        ) {
            $this->tblGroup = $tblGroup;
            $header['Group'] = 'Personengruppe';
        }

        if (isset($Filter['Gender'])
            && ($tblGender = Common::useService()->getCommonGenderById($Filter['Gender']))
        ) {
            $this->tblGender = $tblGender;
            $header['Gender'] = 'Geschlecht';
        }

        if (isset($Filter['Course'])
            && ($tblCourse = Course::useService()->getCourseById($Filter['Course']))
        ) {
            $this->tblCourse = $tblCourse;
            $header['Course'] = 'Bildungsgang';
        }

        if (isset($Filter['SubjectOrientation'])
            && ($tblSubjectOrientation = Subject::useService()->getSubjectById($Filter['SubjectOrientation']))
        ) {
            $this->tblSubjectOrientation = $tblSubjectOrientation;
            $header['SubjectOrientation'] = (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName();
        }

        if (isset($Filter['SubjectProfile'])
            && ($tblSubjectProfile = Subject::useService()->getSubjectById($Filter['SubjectProfile']))
        ) {
            $this->tblSubjectProfile = $tblSubjectProfile;
            $header['SubjectProfile'] = 'Profil';
        }

        if (isset($Filter['SubjectForeignLanguage'])
            && ($tblSubjectForeignLanguage = Subject::useService()->getSubjectById($Filter['SubjectForeignLanguage']))
        ) {
            $this->tblSubjectForeignLanguage = $tblSubjectForeignLanguage;
            $header['SubjectForeignLanguage'] = 'Fremdsprache';
        }

        if (isset($Filter['SubjectReligion'])
            && ($tblSubjectReligion = Subject::useService()->getSubjectById($Filter['SubjectReligion']))
        ) {
            $this->tblSubjectReligion = $tblSubjectReligion;
            $header['SubjectReligion'] = 'Religion';
        }

        if (isset($Filter['SubjectElective'])
            && ($tblSubjectElective = Subject::useService()->getSubjectById($Filter['SubjectElective']))
        ) {
            $this->tblSubjectElective = $tblSubjectElective;
            $header['SubjectElective'] = 'Wahlfach';
        }

        if (!empty($header)) {
            $this->isFilterSet = true;
        }

        $this->header = $header;
    }

    /**
     * @return bool
     */
    public function isFilterSet()
    {

        return $this->isFilterSet;
    }

    /**
     * @return bool|TblSubjectGroup
     */
    private function getTblSubjectGroup()
    {
        if (($tblDivisionSubject = $this->getTblDivisionSubject())
            && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
        ) {
            return $tblSubjectGroup;
        } else {
            return false;
        }
    }

    /**
     * @return bool|TblSubject
     */
    public function getTblSubject()
    {
        if (($tblDivisionSubject = $this->getTblDivisionSubject())) {
            return $tblDivisionSubject->getServiceTblSubject();
        } else {
            return false;
        }

    }

    /**
     *
     */
    public function save()
    {

        if (($tblSubjectGroup =$this->getTblSubjectGroup())) {

            $this->saveFilter($tblSubjectGroup, $this->getTblGroup(), 'Group');
            $this->saveFilter($tblSubjectGroup, $this->getTblGender(), 'Gender');
            $this->saveFilter($tblSubjectGroup, $this->getTblCourse(), 'Course');
            $this->saveFilter($tblSubjectGroup, $this->getTblSubjectOrientation(), 'SubjectOrientation');
            $this->saveFilter($tblSubjectGroup, $this->getTblSubjectProfile(), 'SubjectProfile');
            $this->saveFilter($tblSubjectGroup, $this->getTblSubjectForeignLanguage(), 'SubjectForeignLanguage');
            $this->saveFilter($tblSubjectGroup, $this->getTblSubjectReligion(), 'SubjectReligion');
            $this->saveFilter($tblSubjectGroup, $this->getTblSubjectElective(), 'SubjectElective');
        }
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param $property
     * @param $field
     */
    private function saveFilter(TblSubjectGroup $tblSubjectGroup, $property, $field)
    {
        $tblSubjectGroupFilter = Division::useService()->getSubjectGroupFilterBy($tblSubjectGroup, $field);
        /** @var Element $property */
        if ($property) {
            if ($tblSubjectGroupFilter) {
                Division::useService()->updateSubjectGroupFilter($tblSubjectGroupFilter, $property->getId());
            } else {
                Division::useService()->createSubjectGroupFilter($tblSubjectGroup, $field, $property->getId());
            }
        } else {
            if ($tblSubjectGroupFilter) {
                Division::useService()->destroySubjectGroupFilter($tblSubjectGroupFilter);
            }
        }
    }

    /**
     *
     */
    public function load()
    {
        $Filter = array();
        if (($tblSubjectGroup = $this->getTblSubjectGroup())) {
            // gespeicherten Filter laden
            if (($tblSubjectGroupFilterList = Division::useService()->getSubjectGroupFilterAllBySubjectGroup($tblSubjectGroup))) {
                foreach ($tblSubjectGroupFilterList as $tblSubjectGroupFilter) {
                    $Filter[$tblSubjectGroupFilter->getField()] = $tblSubjectGroupFilter->getValue();
                }
            }
            // bei diesen Fächern kann der Filter nicht komplett gelöscht werden
            // automatischen Filter setzen z.B. bei NK, PRO, FS
            elseif (($tblDivisionSubject = $this->getTblDivisionSubject())
                && ($tblSubject = $this->getTblSubject())
            ) {
                // Fremdsprache
                if (($tblCategoryForeignLanguage = Subject::useService()->getCategoryByIdentifier('FOREIGNLANGUAGE'))
                    && Subject::useService()->existsCategorySubject($tblCategoryForeignLanguage, $tblSubject)
                ) {
                    $Filter['SubjectForeignLanguage'] = $tblSubject->getId();
                }

                // Religion
                if (($tblCategoryReligion = Subject::useService()->getCategoryByIdentifier('RELIGION'))
                    && Subject::useService()->existsCategorySubject($tblCategoryReligion, $tblSubject)
                ) {
                    $Filter['SubjectReligion'] = $tblSubject->getId();
                }

                // Wahlfach nur bei Klasse 10 OS
                if ($this->getTypeName() == 'Mittelschule / Oberschule'
                    && floatval($this->getLevelName()) == 10
                    && Subject::useService()->isElective($tblSubject)
                ) {
                    $Filter['SubjectElective'] = $tblSubject->getId();
                }

                // Neigungskurs
                if ($this->getTypeName() == 'Mittelschule / Oberschule'
                    && preg_match('!(0?(7|8|9))!is', $this->getLevelName())
                    && Subject::useService()->isOrientation($tblSubject)
                ) {
                    $Filter['SubjectOrientation'] = $tblSubject->getId();
                }

                // Profil
                if ($this->getTypeName() == 'Gymnasium'
                    && preg_match('!(0?(8|9|10))!is', $this->getLevelName())
                    && ($tblCategoryProfile = Subject::useService()->getCategoryByIdentifier('PROFILE'))
                    && Subject::useService()->existsCategorySubject($tblCategoryProfile, $tblSubject)
                ) {
                    $Filter['SubjectProfile'] = $tblSubject->getId();
                }
            }
        }

        $this->setFilter($Filter);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function isFilterFulfilledByPerson(TblPerson $tblPerson)
    {
        if ($this->getTblGroup() && !$this->hasGroup($tblPerson)) {
            return false;
        }
        if ($this->getTblGender() && !$this->hasGender($tblPerson)) {
            return false;
        }
        if ($this->getTblCourse() && !$this->hasCourse($tblPerson)) {
            return false;
        }
        if ($this->getTblSubjectOrientation() && !$this->hasSubjectOrientation($tblPerson)) {
            return false;
        }
        if ($this->getTblSubjectProfile() && !$this->hasSubjectProfile($tblPerson)) {
            return false;
        }
        if ($this->getTblSubjectForeignLanguage() && !$this->hasSubjectForeignLanguage($tblPerson)) {
            return false;
        }
        if ($this->getTblSubjectReligion() && !$this->hasSubjectReligion($tblPerson)) {
            return false;
        }
        if ($this->getTblSubjectElective() && !$this->hasSubjectElective($tblPerson)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $list
     *
     * @return array|bool
     */
    public function getPersonAllWhereFilterIsNotFulfilled($list)
    {

        if (($tblDivisionSubject = $this->getTblDivisionSubject())
            && ($tblSubjectGroup = $this->getTblSubjectGroup())
            && ($tblSubject = $this->getTblSubject())
            && ($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                // Validierung Bildungsmodul -> Schülerakte
                if (Division::useService()->exitsSubjectStudent($tblDivisionSubject, $tblPerson)) {

                    $list = $this->getIsNotFulfilledByPerson($tblPerson, $tblSubject, $tblSubjectGroup,
                        $tblDivisionSubject, $list);
                }
                // Validierung Schülerakte -> Bildungsmodul
                else {
                    $list = $this->getIsFulfilledButNotInGroupByPerson($tblPerson, $tblSubject, $tblSubjectGroup,
                        $tblDivisionSubject, $list);
                }
            }
        }

        return $list;
    }

    /**
     * @return bool|Warning
     */
    public function getMessageForSubjectGroup()
    {
        $list = array();
        if ($this->isFilterSet()
            && ($tblDivisionSubject = $this->getTblDivisionSubject())
            && ($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision))
        ) {
            $personInAnotherGroupList = array();
            if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())) {
                $tblDivisionSubjectControlList = Division::useService()->getDivisionSubjectBySubjectAndDivision(
                    $tblSubject,
                    $tblDivision
                );
                if ($tblDivisionSubjectControlList) {
                    foreach ($tblDivisionSubjectControlList as $tblDivisionSubjectControl) {
                        if ($tblDivisionSubjectControl->getId() !== $tblDivisionSubject->getId()) {
                            $tblSubjectStudentList = Division::useService()->getSubjectStudentByDivisionSubject($tblDivisionSubjectControl);
                            if ($tblSubjectStudentList) {
                                foreach ($tblSubjectStudentList as $tblSubjectStudent) {
                                    if (($tblPersonItem = $tblSubjectStudent->getServiceTblPerson())) {
                                        $personInAnotherGroupList[$tblPersonItem->getId()] = $tblPersonItem;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($tblPersonList as $tblPerson) {
                // Validierung Bildungsmodul -> Schülerakte
                if (Division::useService()->exitsSubjectStudent($tblDivisionSubject, $tblPerson)) {
                    if (!$this->isFilterFulfilledByPerson($tblPerson)) {
                        $list[$tblPerson->getId()] = new Exclamation() . ' ' . $tblPerson->getLastFirstName() . ' ist in dieser Fach-Gruppe'
                            . (isset($personInAnotherGroupList[$tblPerson->getId()])
                                ? ' und ist in einer weiteren Fach-Gruppe'
                                : ''
                            );
                    } elseif (isset($personInAnotherGroupList[$tblPerson->getId()])) {
                        $list[$tblPerson->getId()] = new Exclamation() . ' ' . $tblPerson->getLastFirstName() . ' ist in einer weiteren Fach-Gruppe';
                    }
                }
                // Validierung Schülerakte -> Bildungsmodul
                else {
                    if (isset($personInAnotherGroupList[$tblPerson->getId()])) {

                    }
                    elseif ($this->isFilterFulfilledByPerson($tblPerson)) {
                        $list[$tblPerson->getId()] = new Ban() . ' ' . $tblPerson->getLastFirstName() . ' ist ' . new Bold('nicht') . ' in dieser Fach-Gruppe';
                    }
                }
            }
        }

        return empty($list)
            ? null
            : new Warning(
                new Bold(new Exclamation() . ' Folgende Schüler in dieser Fach-Gruppe stimmen nicht mit der Filterung überein:')
                . '<br />'
                . implode('<br />', $list)
            );
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return bool|TblGroup
     */
    public function getTblGroup()
    {
        return $this->tblGroup;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTblGroupsStringByPerson(TblPerson $tblPerson) {
        if (($tblGroupAllByPerson = Group::useService()->getGroupAllByPerson($tblPerson))) {
            $groups = array();
            $tblGroupAllByPerson = $this->getSorter($tblGroupAllByPerson)->sortObjectBy('Name');
            /** @var TblGroup $tblGroupItem */
            foreach ($tblGroupAllByPerson as $tblGroupItem) {
                $groups[] = $tblGroupItem->getName();
            }

            return implode(', ', $groups);
        } else {
            return '';
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasGroup(TblPerson $tblPerson) {
        if ($this->getTblGroup()) {
            return Group::useService()->existsGroupPerson($this->getTblGroup(), $tblPerson)
                ? true : false;
        }

        return false;
    }

    /**
     * @return bool|TblCommonGender
     */
    public function getTblGender()
    {
        return $this->tblGender;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCommonGender
     */
    private function getTblGenderByPerson(TblPerson $tblPerson)
    {
        return $tblPerson->getGender();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|string
     */
    public function getTblGenderStringByPerson(TblPerson $tblPerson)
    {
        return $tblPerson->getGenderString();
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasGender(TblPerson $tblPerson)
    {

        return ($this->getTblGender() && ($tblGender = $this->getTblGenderByPerson($tblPerson))
            && $this->getTblGender()->getId() == $tblGender->getId());
    }

    /**
     * @return bool|TblCourse
     */
    public function getTblCourse()
    {
        return $this->tblCourse;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCourse
     */
    private function getTblCourseByPerson(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())) {
            return Student::useService()->getCourseByStudent($tblStudent);
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|string
     */
    public function getTblCourseStringByPerson(TblPerson $tblPerson)
    {
        if (($tblCourse = $this->getTblCourseByPerson($tblPerson))) {
            return $tblCourse->getName();
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasCourse(TblPerson $tblPerson)
    {

        if ($this->getTblCourse()
            && ($tblCourse = $this->getTblCourseByPerson($tblPerson))
            && $this->getTblCourse()->getId() == $tblCourse->getId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|TblSubject
     */
    public function getTblSubjectOrientation()
    {
        return $this->tblSubjectOrientation;
    }


    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubject
     */
    private function getTblSubjectOrientationByPerson(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTblSubjectOrientationStringByPerson(TblPerson $tblPerson)
    {

        if (($tblSubjectOrientation = $this->getTblSubjectOrientationByPerson($tblPerson))) {
            return $tblSubjectOrientation->getAcronym();
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasSubjectOrientation(TblPerson $tblPerson)
    {

        if ($this->getTblSubjectOrientation()
            && ($tblSubjectOrientation = $this->getTblSubjectOrientationByPerson($tblPerson))
            && $this->getTblSubjectOrientation()->getId() == $tblSubjectOrientation->getId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|TblSubject
     */
    public function getTblSubjectProfile()
    {
        return $this->tblSubjectProfile;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubject
     */
    private function getTblSubjectProfileByPerson(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTblSubjectProfileStringByPerson(TblPerson $tblPerson)
    {

        if (($tblSubjectProfile = $this->getTblSubjectProfileByPerson($tblPerson))) {
            return $tblSubjectProfile->getAcronym();
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasSubjectProfile(TblPerson $tblPerson)
    {

        if ($this->getTblSubjectProfile()
            && ($tblSubjectProfile = $this->getTblSubjectProfileByPerson($tblPerson))
            && $this->getTblSubjectProfile()->getId() == $tblSubjectProfile->getId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|TblSubject
     */
    public function getTblSubjectForeignLanguage()
    {
        return $this->tblSubjectForeignLanguage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubject[]
     */
    private function getTblSubjectForeignLanguagesByPerson(TblPerson $tblPerson)
    {
        $result = array();
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            $tblStudentSubjectList = $this->getSorter($tblStudentSubjectList)->sortObjectBy('TblStudentSubjectRanking');

            /** @var TblStudentSubject $tblStudentSubject */
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    $levelFrom = $tblStudentSubject->getLevelFrom();
                    $levelTill = $tblStudentSubject->getLevelTill();

                    $fromName = ' ';
                    $tillName = ' ';
                    $hasLevel = false;
                    if ($levelFrom) {
                        $hasLevel = true;
                        $fromName = $levelFrom;
                        if (floatval($this->getLevelName()) < $levelFrom) {
                            continue;
                        }
                    }
                    if ($levelTill) {
                        $hasLevel = true;
                        $tillName = $levelTill;
                        if (floatval($this->getLevelName()) > $levelTill) {
                            continue;
                        }
                    }

                    $result[$tblSubject->getId()] = $tblSubject->getAcronym()
                        . ($hasLevel
                            ? ' (' . $fromName . '-' . $tillName . ')'
                            : ''
                        );
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTblSubjectForeignLanguagesStringByPerson(TblPerson $tblPerson)
    {

        if (($tblSubjectForeignLanguages = $this->getTblSubjectForeignLanguagesByPerson($tblPerson))) {
            return implode(', ', $tblSubjectForeignLanguages);
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasSubjectForeignLanguage(TblPerson $tblPerson)
    {

        if ($this->getTblSubjectForeignLanguage()
            && ($tblSubjectForeignLanguages = $this->getTblSubjectForeignLanguagesByPerson($tblPerson))
            && isset($tblSubjectForeignLanguages[$this->getTblSubjectForeignLanguage()->getId()])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|TblSubject
     */
    public function getTblSubjectReligion()
    {
        return $this->tblSubjectReligion;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubject
     */
    private function getTblSubjectReligionByPerson(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTblSubjectReligionStringByPerson(TblPerson $tblPerson)
    {

        if (($tblSubjectReligion = $this->getTblSubjectReligionByPerson($tblPerson))) {
            return $tblSubjectReligion->getAcronym();
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasSubjectReligion(TblPerson $tblPerson)
    {

        if ($this->getTblSubjectReligion()
            && ($tblSubjectReligion = $this->getTblSubjectReligionByPerson($tblPerson))
            && $this->getTblSubjectReligion()->getId() == $tblSubjectReligion->getId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|TblSubject
     */
    public function getTblSubjectElective()
    {
        return $this->tblSubjectElective;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubject[]
     */
    private function getTblSubjectElectivesByPerson(TblPerson $tblPerson)
    {
        $result = array();
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ELECTIVE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                $tblStudentSubjectType))
        ) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
                    $result[$tblSubject->getId()] = $tblSubject->getAcronym();
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTblSubjectElectivesStringByPerson(TblPerson $tblPerson)
    {

        if (($tblSubjectElectives = $this->getTblSubjectElectivesByPerson($tblPerson))) {
            return implode(', ', $tblSubjectElectives);
        }

        return '';
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function hasSubjectElective(TblPerson $tblPerson)
    {

        if ($this->getTblSubjectElective()
            && ($tblSubjectElectives = $this->getTblSubjectElectivesByPerson($tblPerson))
            && isset($tblSubjectElectives[$this->getTblSubjectElective()->getId()])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        if ($this->tblDivision
            && ($tblLevel = $this->tblDivision->getTblLevel())
        ) {
            return $tblLevel->getName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        if ($this->tblDivision
            && ($tblLevel = $this->tblDivision->getTblLevel())
            && ($tblType = $tblLevel->getServiceTblType())
        ) {
            return $tblType->getName();
        }

        return '';
    }

    /**
     * @return bool|TblDivisionSubject
     */
    public function getTblDivisionSubject()
    {
        return $this->tblDivisionSubject;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblDivisionSubject $tblDivisionSubject
     * @param $list
     * @param bool $showDivision
     *
     * @return mixed
     */
    public function getIsNotFulfilledByPerson(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup,
        TblDivisionSubject $tblDivisionSubject,
        $list,
        $showDivision = false
    ) {

        $prefix = new Exclamation() . ' ist in ';
        if ($showDivision && ($tblDivision = $tblDivisionSubject->getTblDivision())) {
            $prefix .= 'Klasse: ' . $tblDivision->getDisplayName() . '&nbsp;&nbsp;&nbsp;';
        }
        if (($tblGroup = $this->getTblGroup()) && !$this->hasGroup($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['Group']['Field'] = Filter::DESCRIPTION_GROUP;
            $list[$tblPerson->getId()]['Filters']['Group']['Value'] = $this->getTblGroupsStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['Group']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblGroup->getName();
        }

        if (($tblGender = $this->getTblGender()) && !$this->hasGender($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['Gender']['Field'] = Filter::DESCRIPTION_GENDER;
            $list[$tblPerson->getId()]['Filters']['Gender']['Value'] = $this->getTblGenderStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['Gender']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblGender->getName();
        }

        if (($tblCourse = $this->getTblCourse()) && !$this->hasCourse($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['Course']['Field'] = Filter::DESCRIPTION_COURSE;
            $list[$tblPerson->getId()]['Filters']['Course']['Value'] = $this->getTblCourseStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['Course']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblCourse->getName();
        }

        if (($tblSubjectOrientation = $this->getTblSubjectOrientation()) && !$this->hasSubjectOrientation($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Field']
                = (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName();
            $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Value'] = $this->getTblSubjectOrientationStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectOrientation->getDisplayName();
        }

        if (($tblSubjectProfile = $this->getTblSubjectProfile()) && !$this->hasSubjectProfile($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Field'] = Filter::DESCRIPTION_SUBJECT_PROFILE;
            $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Value'] = $this->getTblSubjectProfileStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectProfile']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectProfile->getDisplayName();
        }

        if (($tblSubjectForeignLanguage = $this->getTblSubjectForeignLanguage()) && !$this->hasSubjectForeignLanguage($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Field'] = Filter::DESCRIPTION_SUBJECT_FOREIGN_LANGUAGE;
            $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'] = $this->getTblSubjectForeignLanguagesStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectForeignLanguage->getDisplayName();
        }

        if (($tblSubjectReligion = $this->getTblSubjectReligion()) && !$this->hasSubjectReligion($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Field'] = Filter::DESCRIPTION_SUBJECT_RELIGION;
            $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Value'] = $this->getTblSubjectReligionStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectReligion']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectReligion->getDisplayName();
        }

        if (($tblSubjectElective = $this->getTblSubjectElective()) && !$this->hasSubjectElective($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectElective']['Field'] = Filter::DESCRIPTION_SUBJECT_ELECTIVE;
            $list[$tblPerson->getId()]['Filters']['SubjectElective']['Value'] = $this->getTblSubjectElectivesStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectElective']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectElective->getDisplayName();
        }

        return $list;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param TblDivisionSubject $tblDivisionSubject
     * @param $list
     * @param bool $showDivision
     *
     * @return mixed
     */
    public function getIsFulfilledButNotInGroupByPerson(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup,
        TblDivisionSubject $tblDivisionSubject,
        $list,
        $showDivision = false
    ) {

        // meldung bei mehrer Gruppen und der Schüler ist bereits in einer Gruppe ignorieren
        // (z.B. wenn es mehrere Gruppen für eine Fremdsprache  gibt oder in der SEKII bei Kursen)
        if (($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblDivisionSubjectList = Division::useService()->getDivisionSubjectAllWhereSubjectGroupByDivisionAndSubject(
            $tblDivision,
            $tblSubject
        ))) {
            $Filter = array();
            $FilterNew = array();
            foreach ($tblDivisionSubjectList as $tblDivisionSubjectItem) {
                if ($tblDivisionSubjectItem->getId() != $tblDivisionSubject->getId()
                    && (Division::useService()->exitsSubjectStudent($tblDivisionSubjectItem, $tblPerson))
                    && ($tblSubjectGroupItem = $tblDivisionSubjectItem->getTblSubjectGroup())
                ) {
                    // nur bei gleichem Filter ignorieren SSW-244
                    if (($tblSubjectGroupFilterList = Division::useService()->getSubjectGroupFilterAllBySubjectGroup($tblSubjectGroupItem))) {
                        foreach ($tblSubjectGroupFilterList as $tblSubjectGroupFilter) {
                            $FilterNew[$tblSubjectGroupFilter->getField()] = $tblSubjectGroupFilter->getValue();
                        }
                    }
                    if (($tblSubjectGroupFilterList = Division::useService()->getSubjectGroupFilterAllBySubjectGroup($tblSubjectGroup))) {
                        foreach ($tblSubjectGroupFilterList as $tblSubjectGroupFilter) {
                            $Filter[$tblSubjectGroupFilter->getField()] = $tblSubjectGroupFilter->getValue();
                        }
                    }
                    $isContinue = true;
                    if (count($Filter) == count($FilterNew)) {
                        foreach ($Filter as $key => $value) {
                            if (isset($FilterNew[$key]) && $FilterNew[$key] == $value) {

                            } else {
                                $isContinue = false;
                                break;
                            }
                        }
                    }

                    if ($isContinue) {
                        return $list;
                    }
                }
            }
        }

        $prefix = new Ban() .  ' ist ' . new Bold('nicht') . ' in ';
        if ($showDivision && ($tblDivision = $tblDivisionSubject->getTblDivision())) {
            $prefix .= 'Klasse: ' . $tblDivision->getDisplayName() . '&nbsp;&nbsp;&nbsp;';
        }
        if (($tblGroup = $this->getTblGroup()) && $this->hasGroup($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['Group']['Field'] = Filter::DESCRIPTION_GROUP;
            $list[$tblPerson->getId()]['Filters']['Group']['Value'] = $this->getTblGroupsStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['Group']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblGroup->getName();
        }

        if (($tblGender = $this->getTblGender()) && $this->hasGender($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['Gender']['Field'] = Filter::DESCRIPTION_GENDER;
            $list[$tblPerson->getId()]['Filters']['Gender']['Value'] = $this->getTblGenderStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['Gender']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblGender->getName();
        }

        if (($tblCourse = $this->getTblCourse()) && $this->hasCourse($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['Course']['Field'] = Filter::DESCRIPTION_COURSE;
            $list[$tblPerson->getId()]['Filters']['Course']['Value'] = $this->getTblCourseStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['Course']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblCourse->getName();
        }

        if (($tblSubjectOrientation = $this->getTblSubjectOrientation()) && $this->hasSubjectOrientation($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Field']
                = (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName();
            $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['Value'] = $this->getTblSubjectOrientationStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectOrientation']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectOrientation->getDisplayName();
        }

        if (($tblSubjectProfile = $this->getTblSubjectProfile()) && $this->hasSubjectProfile($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Field'] = Filter::DESCRIPTION_SUBJECT_PROFILE;
            $list[$tblPerson->getId()]['Filters']['SubjectProfile']['Value'] = $this->getTblSubjectProfileStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectProfile']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectProfile->getDisplayName();
        }

        if (($tblSubjectForeignLanguage = $this->getTblSubjectForeignLanguage()) && $this->hasSubjectForeignLanguage($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Field'] = Filter::DESCRIPTION_SUBJECT_FOREIGN_LANGUAGE;
            $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['Value'] = $this->getTblSubjectForeignLanguagesStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectForeignLanguage']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectForeignLanguage->getDisplayName();
        }

        if (($tblSubjectReligion = $this->getTblSubjectReligion()) && $this->hasSubjectReligion($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Field'] = Filter::DESCRIPTION_SUBJECT_RELIGION;
            $list[$tblPerson->getId()]['Filters']['SubjectReligion']['Value'] = $this->getTblSubjectReligionStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectReligion']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectReligion->getDisplayName();
        }

        if (($tblSubjectElective = $this->getTblSubjectElective()) && $this->hasSubjectElective($tblPerson)) {
            $list[$tblPerson->getId()]['Filters']['SubjectElective']['Field'] = Filter::DESCRIPTION_SUBJECT_ELECTIVE;
            $list[$tblPerson->getId()]['Filters']['SubjectElective']['Value'] = $this->getTblSubjectElectivesStringByPerson($tblPerson);

            $list[$tblPerson->getId()]['Filters']['SubjectElective']['DivisionSubjects'][$tblDivisionSubject->getId()]
                = $prefix
                . 'Fach: ' . $tblSubject->getDisplayName()  . '&nbsp;&nbsp;&nbsp;'
                . 'Gruppe: ' . $tblSubjectGroup->getName() . '&nbsp;&nbsp;&nbsp;'
                . 'Filter: ' . $tblSubjectElective->getDisplayName();
        }

        return $list;
    }
}