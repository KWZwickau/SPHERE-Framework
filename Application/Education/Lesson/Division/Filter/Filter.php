<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.06.2018
 * Time: 10:16
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;


use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
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
use SPHERE\System\Extension\Extension;

/**
 * Class Filter
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Filter extends Extension
{

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
     * @var array
     */
    protected $header = array();

    public function __construct($Filtered, TblDivision $tblDivision) {

        $header = array();
        $this->tblDivision = $tblDivision;
        if (isset($Filtered['Group'])
            && ($tblGroup = Group::useService()->getGroupById($Filtered['Group']))
        ) {
            $this->tblGroup = $tblGroup;
            $header['Group'] = 'Personengruppe';
        }

        if (isset($Filtered['Gender'])
            && ($tblGender = Common::useService()->getCommonGenderById($Filtered['Gender']))
        ) {
            $this->tblGender = $tblGender;
            $header['Gender'] = 'Geschlecht';
        }

        if (isset($Filtered['Course'])
            && ($tblCourse = Course::useService()->getCourseById($Filtered['Course']))
        ) {
            $this->tblCourse = $tblCourse;
            $header['Course'] = 'Bildungsgang';
        }

        if (isset($Filtered['SubjectOrientation'])
            && ($tblSubjectOrientation = Subject::useService()->getSubjectById($Filtered['SubjectOrientation']))
        ) {
            $this->tblSubjectOrientation = $tblSubjectOrientation;
            $header['SubjectOrientation'] = 'Neigungskurs';
        }

        if (isset($Filtered['SubjectProfile'])
            && ($tblSubjectProfile = Subject::useService()->getSubjectById($Filtered['SubjectProfile']))
        ) {
            $this->tblSubjectProfile = $tblSubjectProfile;
            $header['SubjectProfile'] = 'Profil';
        }

        if (isset($Filtered['SubjectForeignLanguage'])
            && ($tblSubjectForeignLanguage = Subject::useService()->getSubjectById($Filtered['SubjectForeignLanguage']))
        ) {
            $this->tblSubjectForeignLanguage = $tblSubjectForeignLanguage;
            $header['SubjectForeignLanguage'] = 'Fremdsprache';
        }

        if (isset($Filtered['SubjectReligion'])
            && ($tblSubjectReligion = Subject::useService()->getSubjectById($Filtered['SubjectReligion']))
        ) {
            $this->tblSubjectReligion = $tblSubjectReligion;
            $header['SubjectReligion'] = 'Religion';
        }

        if (isset($Filtered['SubjectElective'])
            && ($tblSubjectElective = Subject::useService()->getSubjectById($Filtered['SubjectElective']))
        ) {
            $this->tblSubjectElective = $tblSubjectElective;
            $header['SubjectElective'] = 'Wahlfach';
        }

        $this->header = $header;
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
                    $from = $tblStudentSubject->getServiceTblLevelFrom();
                    $till = $tblStudentSubject->getServiceTblLevelTill();

                    $fromName = ' ';
                    $tillName = ' ';
                    $hasLevel = false;
                    if ($from) {
                        $hasLevel = true;
                        $fromName = $from->getName();
                        if (floatval($this->getLevelName()) < floatval($fromName)) {
                            continue;
                        }
                    }
                    if ($till) {
                        $hasLevel = true;
                        $tillName = $till->getName();
                        if (floatval($this->getLevelName()) > floatval($tillName)) {
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
}