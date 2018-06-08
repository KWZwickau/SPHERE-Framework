<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.06.2018
 * Time: 10:16
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;


use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Filter
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Filter
{

    /**
     * @var bool|\SPHERE\Application\People\Group\Service\Entity\TblGroup
     */
    protected $tblGroup = false;

    /**
     * @var bool|\SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender
     */
    protected $tblGender = false;

    /**
     * @var bool|\SPHERE\Application\Education\School\Course\Service\Entity\TblCourse
     */
    protected $tblCourse = false;

    /**
     * @var bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    protected $tblSubjectOrientation = false;

    /**
     * @var bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    protected $tblSubjectProfile = false;

    /**
     * @var bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    protected $tblSubjectForeignLanguage = false;

    /**
     * @var bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    protected $tblSubjectReligion = false;

    /**
     * @var bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    protected $tblSubjectElective = false;

    /**
     * @var array
     */
    protected $header = array();

    public function __construct($Filtered) {

        $header = array();
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
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return bool|\SPHERE\Application\People\Group\Service\Entity\TblGroup
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
     * @return bool|\SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender
     */
    public function getTblGender()
    {
        return $this->tblGender;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|\SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender
     */
    public function getTblGenderByPerson(TblPerson $tblPerson)
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
     * @return bool|\SPHERE\Application\Education\School\Course\Service\Entity\TblCourse
     */
    public function getTblCourse()
    {
        return $this->tblCourse;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|\SPHERE\Application\Education\School\Course\Service\Entity\TblCourse
     */
    public function getTblCourseByPerson(TblPerson $tblPerson)
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
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectOrientation()
    {
        return $this->tblSubjectOrientation;
    }


    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectOrientationByPerson(TblPerson $tblPerson)
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
            return $tblSubjectOrientation->getAcronym() . ' ' . $tblSubjectOrientation->getName();
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
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectProfile()
    {
        return $this->tblSubjectProfile;
    }

    /**
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectForeignLanguage()
    {
        return $this->tblSubjectForeignLanguage;
    }

    /**
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectReligion()
    {
        return $this->tblSubjectReligion;
    }

    /**
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectElective()
    {
        return $this->tblSubjectElective;
    }
}