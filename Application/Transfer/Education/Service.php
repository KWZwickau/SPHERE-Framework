<?php

namespace SPHERE\Application\Transfer\Education;

use DateTime;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Transfer\Education\Service\Data;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudent;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudentCourse;
use SPHERE\Application\Transfer\Education\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Fitting\Element;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->createEntityListBulk($tblEntityList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->updateEntityListBulk($tblEntityList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->deleteEntityListBulk($tblEntityList);
    }

    /**
     * @param $id
     *
     * @return false|TblImport
     */
    public function getImportById($id)
    {
        return (new Data($this->getBinding()))->getImportById($id);
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $ExternSoftwareName
     * @param string $TypeIdentifier
     *
     * @return false|TblImport
     */
    public function getImportByAccountAndExternSoftwareNameAndTypeIdentifier(TblAccount $tblAccount, string $ExternSoftwareName, string $TypeIdentifier)
    {
        return (new Data($this->getBinding()))->getImportByAccountAndExternSoftwareNameAndTypeIdentifier($tblAccount, $ExternSoftwareName, $TypeIdentifier);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return TblImport
     */
    public function createImport(TblImport $tblImport): TblImport
    {
        return (new Data($this->getBinding()))->createImport($tblImport);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return bool
     */
    public function destroyImport(TblImport $tblImport): bool
    {
        if ($tblImport->getTypeIdentifier() == TblImport::TYPE_IDENTIFIER_LECTURESHIP) {
            (new Data($this->getBinding()))->destroyImportLectureshipAllByImport($tblImport);
        } else {
            $deleteImportStudentList = array();
            if (($tblImportStudentList = $this->getImportStudentListByImport($tblImport))) {
                foreach ($tblImportStudentList as $tblImportStudent) {
                    (new Data($this->getBinding()))->destroyImportStudentCourseAllByImportStudent($tblImportStudent);

                    $deleteImportStudentList[] = $tblImportStudent;
                }
            }

            if ($deleteImportStudentList) {
                $this->deleteEntityListBulk($deleteImportStudentList);
            }
        }

        return (new Data($this->getBinding()))->destroyImport($tblImport);
    }

    /**
     * @param $Id
     *
     * @return false|TblImportLectureship
     */
    public function getImportLectureshipById($Id)
    {
        return (new Data($this->getBinding()))->getImportLectureshipById($Id);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return false|TblImportLectureship[]
     */
    public function getImportLectureshipListByImport(TblImport $tblImport)
    {
        return (new Data($this->getBinding()))->getImportLectureshipListByImport($tblImport);
    }

    /**
     * @param string $Type
     * @param string $Original
     *
     * @return false|TblImportMapping
     */
    public function getImportMappingBy(string $Type, string $Original)
    {
        return (new Data($this->getBinding()))->getImportMappingBy($Type, $Original);
    }

    /**
     * @param string $Type
     * @param string $Original
     * @param TblYear|null $tblYear
     *
     * @return false|Element
     */
    public function getImportMappingValueBy(string $Type, string $Original, ?TblYear $tblYear = null)
    {
        if (($tblImportMapping = $this->getImportMappingBy($Type, $Original))) {
            switch ($Type) {
                case TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID:
                    return Subject::useService()->getSubjectById($tblImportMapping->getMapping());
                case TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID:
                    return Person::useService()->getPersonById($tblImportMapping->getMapping());
                case TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME:
                    return Education::useService()->getDivisionCourseByDivisionNameAndYear($tblImportMapping->getMapping(), $tblYear);
                case TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME:
                    return Education::useService()->getDivisionCourseCourseSystemByCourseNameAndYear($tblImportMapping->getMapping(), $tblYear);
                default: return $tblImportMapping->getMapping();
            }
        }

        return false;
    }

    /**
     * @param string $Type
     * @param string $Original
     * @param string $Mapping
     *
     * @return TblImportMapping
     */
    public function updateImportMapping(string $Type, string $Original, string $Mapping): TblImportMapping
    {
        return (new Data($this->getBinding()))->updateImportMapping($Type, $Original, $Mapping);
    }

    /**
     * @param TblImportMapping $tblImportMapping
     *
     * @return bool
     */
    public function destroyImportMapping(TblImportMapping $tblImportMapping): bool
    {
        return (new Data($this->getBinding()))->destroyImportMapping($tblImportMapping);
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return IFormInterface|string|null
     */
    public function saveMappingSubject(?IFormInterface $Form, TblImport $tblImport, string $NextTab, $Data)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        foreach ($Data as $ImportItemId => $SubjectId) {
            $tblImportItem = $tblImport->getTypeIdentifier() == TblImport::TYPE_IDENTIFIER_LECTURESHIP
                ? $this->getImportLectureshipById($ImportItemId)
                : $this->getImportStudentCourseById($ImportItemId);
            if ($tblImportItem
                && ($subjectAcronym = $tblImportItem->getSubjectAcronym())
            ) {
                // Fach in Schulsoftware gefunden
                if (($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))
                    && $tblSubject->getId() == $SubjectId
                ) {
                    continue;
                // Fach wird gemappt
                } elseif (($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
                    $this->updateImportMapping(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $subjectAcronym, $tblSubject->getId());
                // vorhandenes Mapping löschen
                } elseif (($tblImportMapping = $this->getImportMappingBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $subjectAcronym))) {
                    $this->destroyImportMapping($tblImportMapping);
                }
            }
        }

        return new Success('Die Fächer wurden erfolgreich gemappt.', new Check())
            . new Redirect($tblImport->getShowRoute(), Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId(), 'Tab' => $NextTab));
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return IFormInterface|string|null
     */
    public function saveMappingTeacher(?IFormInterface $Form, TblImport $tblImport, string $NextTab, $Data)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        foreach ($Data as $ImportLectureshipId => $PersonId) {
            if (($tblImportLectureship = $this->getImportLectureshipById($ImportLectureshipId))
                && ($teacherAcronym = $tblImportLectureship->getTeacherAcronym())
            ) {
                // Lehrer in Schulsoftware gefunden
                if (($tblTeacher = Teacher::useService()->getTeacherByAcronym($teacherAcronym))
                    && ($tblPerson = $tblTeacher->getServiceTblPerson())
                    && $tblPerson->getId() == $PersonId
                ) {
                    continue;
                // Lehrer wird gemappt
                } elseif (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                    $this->updateImportMapping(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $teacherAcronym, $tblPerson->getId());
                // vorhandenes Mapping löschen
                } elseif (($tblImportMapping = $this->getImportMappingBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $teacherAcronym))) {
                    $this->destroyImportMapping($tblImportMapping);
                }
            }
        }

        return new Success('Die Lehrer wurden erfolgreich gemappt.', new Check())
            . new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId(), 'Tab' => $NextTab));
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string|null
     */
    public function saveMappingDivisionCourse(?IFormInterface $Form, TblImport $tblImport, string $NextTab, $Data, TblYear $tblYear)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        foreach ($Data as $ImportLectureshipId => $DivisionCourseId) {
            if (($tblImportLectureship = $this->getImportLectureshipById($ImportLectureshipId))
                && ($divisionName = $tblImportLectureship->getDivisionName())
            ) {
                // Kurs in Schulsoftware gefunden
                if (($tblDivisionCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($divisionName, $tblYear))
                    && $tblDivisionCourse->getId() == $DivisionCourseId
                ) {
                    continue;
                // Kurs wird gemappt
                } elseif (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                    $this->updateImportMapping(TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $divisionName, $tblDivisionCourse->getName());
                // vorhandenes Mapping löschen
                } elseif (($tblImportMapping = $this->getImportMappingBy(TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $divisionName))) {
                    $this->destroyImportMapping($tblImportMapping);
                }
            }
        }

        return new Success('Die Klassen wurden erfolgreich gemappt.', new Check())
            . new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId(), 'Tab' => $NextTab));
    }

    /**
     * @param string $divisionName
     * @param TblYear $tblYear
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByDivisionNameAndYear(string $divisionName, TblYear $tblYear)
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseByNameAndYear($divisionName, $tblYear))
            && ($tblDivisionCourse->getIsDivisionOrCoreGroup() || $tblDivisionCourse->getType() == TblDivisionCourseType::TYPE_TEACHING_GROUP)
        ) {
            return $tblDivisionCourse;
        }

        // ohne Leerzeichen suchen
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseByNameAndYear(str_replace(' ', '', $divisionName), $tblYear))
            && ($tblDivisionCourse->getIsDivisionOrCoreGroup() || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHING_GROUP)
        ) {
            return $tblDivisionCourse;
        }

        return false;
    }

    /**
     * @param string $courseName
     * @param TblYear $tblYear
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseCourseSystemByCourseNameAndYear(string $courseName, TblYear $tblYear)
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseByNameAndYear($courseName, $tblYear))
            && ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE
                || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE
            )
        ) {
            return $tblDivisionCourse;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblImportStudent
     */
    public function getImportStudentById($Id)
    {
        return (new Data($this->getBinding()))->getImportStudentById($Id);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return false|TblImportStudent[]
     */
    public function getImportStudentListByImport(TblImport $tblImport)
    {
        return (new Data($this->getBinding()))->getImportStudentListByImport($tblImport);
    }

    /**
     * @param TblImportStudent $tblImportStudent
     *
     * @return TblImportStudent
     */
    public function createImportStudent(TblImportStudent $tblImportStudent): TblImportStudent
    {
        return (new Data($this->getBinding()))->createImportStudent($tblImportStudent);
    }

    /**
     * @param $Id
     *
     * @return false|TblImportStudentCourse
     */
    public function getImportStudentCourseById($Id)
    {
        return (new Data($this->getBinding()))->getImportStudentCourseById($Id);
    }

    /**
     * @param TblImport $tblImport
     *
     * @return false|TblImportStudentCourse[]
     */
    public function getImportStudentCourseListByImport(TblImport $tblImport)
    {
        return (new Data($this->getBinding()))->getImportStudentCourseListByImport($tblImport);
    }

    /**
     * @param TblImportStudent $tblImportStudent
     *
     * @return false|TblImportStudentCourse[]
     */
    public function getImportStudentCourseListByImportStudent(TblImportStudent $tblImportStudent)
    {
        return (new Data($this->getBinding()))->getImportStudentCourseListByImportStudent($tblImportStudent);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param TblYear $tblYear
     * @param DateTime|null $birthday
     *
     * @return false|TblPerson
     */
    public function getPersonIsInCourseSystemByFristNameAndLastName(string $firstName, string $lastName, TblYear $tblYear, ?DateTime $birthday)
    {
        $tblPersonInCourseSystemList = array();
        if (($tblPersonList = Person::useService()->getPersonListLikeFirstNameAndLastName($firstName, $lastName))) {
            foreach ($tblPersonList as $tblPerson) {
                // Schüler muss im Kurs-System sein im Schuljahr
                if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                    $tblPersonInCourseSystemList[$tblPerson->getId()] = $tblPerson;
                }
            }
        }

        if (count($tblPersonInCourseSystemList) == 1) {
            return current($tblPersonInCourseSystemList);
        } elseif($birthday) {
            foreach ($tblPersonInCourseSystemList as $tblPersonTemp) {
                if (($temp = $tblPersonTemp->getBirthday())
                    && $temp == $birthday->format('d.m.Y')
                ) {
                    return $tblPersonTemp;
                }
            }
        }

        return false;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function getPersonListByIsInCourseSystem(TblYear $tblYear): array
    {
        $tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy');
        $tblSchoolTypeBGy = Type::useService()->getTypeByShortName('BGy');

        $tblPersonList = array();

        $this->setPersonListBySchoolTypeAndLevel($tblPersonList, $tblYear, $tblSchoolTypeGy, 11);
        $this->setPersonListBySchoolTypeAndLevel($tblPersonList, $tblYear, $tblSchoolTypeGy, 12);
        $this->setPersonListBySchoolTypeAndLevel($tblPersonList, $tblYear, $tblSchoolTypeBGy, 12);
        $this->setPersonListBySchoolTypeAndLevel($tblPersonList, $tblYear, $tblSchoolTypeBGy, 13);

        return $tblPersonList;
    }

    /**
     * @param array $tblPersonList
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     *
     * @return void
     */
    private function setPersonListBySchoolTypeAndLevel(array &$tblPersonList, TblYear $tblYear, TblType $tblSchoolType, int $level)
    {
        if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYear, $tblSchoolType, $level))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (!$tblStudentEducation->getLeaveDateTime()
                    && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
                ) {
                    $tblPersonList[$tblPerson->getId()] = $tblPerson;
                }
            }
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return IFormInterface|string|null
     */
    public function saveMappingPerson(?IFormInterface $Form, TblImport $tblImport, string $NextTab, $Data)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        if (($tblYear = $tblImport->getServiceTblYear())) {
            $updateImportStudentList = array();
            foreach ($Data as $ImportStudentId => $PersonId) {
                if (($tblImportStudent = $this->getImportStudentById($ImportStudentId))) {
                    // Schüler in Schulsoftware gefunden
                    if (($tblPerson = Education::useService()->getPersonIsInCourseSystemByFristNameAndLastName(
                            $tblImportStudent->getFirstName(),
                            $tblImportStudent->getLastName(),
                            $tblYear,
                            $tblImportStudent->getBirthday() ?: null
                        ))
                        && $tblPerson->getId() == $PersonId
                    ) {
                        continue;
                    // Person wird gemappt
                    } elseif (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                        $tblImportStudent->setServiceTblPerson($tblPerson);
                        $updateImportStudentList[$tblImportStudent->getId()] = $tblImportStudent;
                    // vorhandenes Personen-Mapping löschen
                    } elseif ($tblImportStudent->getServiceTblPerson()) {
                        $tblImportStudent->setServiceTblPerson();
                        $updateImportStudentList[$tblImportStudent->getId()] = $tblImportStudent;
                    }
                }
            }

            if (!empty($updateImportStudentList)) {
                $this->updateEntityListBulk($updateImportStudentList);
            }
        }

        return new Success('Die Schüler wurden erfolgreich gemappt.', new Check())
            . new Redirect($tblImport->getShowRoute(), Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId(), 'Tab' => $NextTab));
    }

    /**
     * @param string $externSoftwareName
     * @param string $courseName
     *
     * @return bool
     */
    public function getIsAdvancedCourse(string $externSoftwareName, string $courseName): bool
    {
        // Untis: EN-L-1
        if ($externSoftwareName == TblImport::EXTERN_SOFTWARE_NAME_UNTIS) {
            if (strpos($courseName, '-L-') !== false) {
                return true;
            } else {
                return false;
            }
        // Indiware: BIO1
        } else {
            if (preg_match('!^([a-zA-Z]+)!', $courseName, $Match)) {
                if (ctype_upper($Match[1])) {
                    return true;
                } else {
                    return false;
                }
            }

            return false;
        }
    }

    /**
     * @param string $externSoftwareName
     * @param string $courseName
     * @param int $level
     * @param TblType $tblSchoolType
     *
     * @return string
     */
    public function getCourseNameForSystem(string $externSoftwareName, string $courseName, int $level, TblType $tblSchoolType): string
    {
        $isAdvancedCourse = Education::useService()->getIsAdvancedCourse($externSoftwareName, $courseName);
        // Untis: 11Gy EN-L-1
        if ($externSoftwareName == TblImport::EXTERN_SOFTWARE_NAME_UNTIS) {
            $courseName = $level . $tblSchoolType->getShortName() . ' ' . $courseName;
            // Inidware: 11Gy L-BIO1
        } else {
            $courseName = $level . $tblSchoolType->getShortName() . ' ' . ($isAdvancedCourse ? 'L-' : 'G-') . $courseName;
        }

        return $courseName;
    }

    /**
     * @param IFormInterface|null $Form
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     * @param TblYear $tblYear
     *
     * @return IFormInterface|string|null
     */
    public function saveMappingStudentCourse(?IFormInterface $Form, TblImport $tblImport, string $NextTab, $Data, TblYear $tblYear)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $Form;
        }

        $createDivisionCourseList = array();
        if (isset($Data['Select'])) {
            foreach ($Data['Select'] as $ImportStudentCourseId => $DivisionCourseId) {
                if (($tblImportStudentCourse = $this->getImportStudentCourseById($ImportStudentCourseId))
                    && ($courseName = $tblImportStudentCourse->getCourseName())
                    && ($tblImportStudent = $tblImportStudentCourse->getTblImportStudent())
                    && ($tblStudentEducation = $tblImportStudent->getStudentEducation())
                    && ($level = $tblStudentEducation->getLevel())
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                ) {
                    $courseName = Education::useService()->getCourseNameForSystem($tblImport->getExternSoftwareName(), $courseName, $level, $tblSchoolType);

                    // Kurs in Schulsoftware gefunden
                    if (($tblDivisionCourse = Education::useService()->getDivisionCourseCourseSystemByCourseNameAndYear($courseName, $tblYear))
                        && $tblDivisionCourse->getId() == $DivisionCourseId
                    ) {
                        continue;
                    // Kurs wird gemappt
                    } elseif (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                        $this->updateImportMapping(TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $courseName, $tblDivisionCourse->getName());
                    // Kurs wird in der Schulsoftware neu angelegt
                    } elseif (isset($Data['Check'][$ImportStudentCourseId])) {
                        // Fach
                        $subjectAcronym = $tblImportStudentCourse->getSubjectAcronym();
                        if (($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $subjectAcronym))) {

                        } elseif (($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))) {

                        }

                        $isAdvanceCourse = $this->getIsAdvancedCourse($tblImport->getExternSoftwareName(), $tblImportStudentCourse->getCourseName());
                        $createDivisionCourseList[] = TblDivisionCourse::withParameter(
                            DivisionCourse::useService()->getDivisionCourseTypeByIdentifier(
                                $isAdvanceCourse ? TblDivisionCourseType::TYPE_ADVANCED_COURSE : TblDivisionCourseType::TYPE_BASIC_COURSE
                            ),
                            $tblYear,
                            $courseName,
                            '',
                            $isAdvanceCourse,
                            $isAdvanceCourse,
                            $tblSubject ?: null
                        );
                    // vorhandenes Mapping löschen
                    } elseif (($tblImportMapping = $this->getImportMappingBy(TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $courseName))) {
                        $this->destroyImportMapping($tblImportMapping);
                    }
                }
            }
        }

        if ($createDivisionCourseList) {
            DivisionCourse::useService()->createEntityListBulk($createDivisionCourseList);
        }

        return new Success('Die Sek-Kurse wurden erfolgreich gemappt bzw. neu angelegt.', new Check())
            . new Redirect($tblImport->getShowRoute(), Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId(), 'Tab' => $NextTab));
    }

    /**
     * @param IFormInterface $form
     * @param TblImport $tblImport
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function saveLectureshipFromImport(IFormInterface $form, TblImport $tblImport, $Data)
    {
        if ($Data === null) {
            return $form;
        }

        list($saveCreateTeacherLectureshipList, $saveDeleteTeacherLectureshipList) = Education::useFrontend()->getImportLectureshipPreviewData($tblImport, false);

        if ($saveCreateTeacherLectureshipList) {
            DivisionCourse::useService()->createEntityListBulk($saveCreateTeacherLectureshipList);
        }
        if ($saveDeleteTeacherLectureshipList) {
            DivisionCourse::useService()->deleteEntityListBulk($saveDeleteTeacherLectureshipList);
        }

        Education::useService()->destroyImport($tblImport);

        return new SuccessMessage('Die Lehraufträge wurden erfolgreich aktualisiert.', new SuccessIcon())
            . new Redirect($tblImport->getBackRoute(), Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param IFormInterface $form
     * @param TblImport $tblImport
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function saveStudentCoursesFromImport(IFormInterface $form, TblImport $tblImport, $Data)
    {
        if ($Data === null) {
            return $form;
        }

        list($saveCreateStudentSubjectList, $saveDeleteStudentSubjectList) = Education::useFrontend()->getImportStudentCoursePreviewData($tblImport, false);

        if ($saveCreateStudentSubjectList) {
            DivisionCourse::useService()->createEntityListBulk($saveCreateStudentSubjectList);
        }
        if ($saveDeleteStudentSubjectList) {
            DivisionCourse::useService()->deleteEntityListBulk($saveDeleteStudentSubjectList);
        }

        Education::useService()->destroyImport($tblImport);

        return new SuccessMessage('Die Schüler-Fächer wurden erfolgreich aktualisiert.', new SuccessIcon())
            . new Redirect($tblImport->getBackRoute(), Redirect::TIMEOUT_SUCCESS);
    }
}