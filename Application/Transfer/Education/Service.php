<?php

namespace SPHERE\Application\Transfer\Education;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Transfer\Education\Service\Data;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Application\Transfer\Education\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Message\Repository\Success;
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
        (new Data($this->getBinding()))->destroyImportLectureshipAllByImport($tblImport);

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
     *
     * @return false|Element
     */
    public function getImportMappingValueBy(string $Type, string $Original)
    {
        if (($tblImportMapping = $this->getImportMappingBy($Type, $Original))) {
            switch ($Type) {
                case TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID:
                    return Subject::useService()->getSubjectById($tblImportMapping->getMapping());
                case TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID:
                    return Person::useService()->getPersonById($tblImportMapping->getMapping());
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

        foreach ($Data as $ImportLectureshipId => $SubjectId) {
            if (($tblImportLectureship = $this->getImportLectureshipById($ImportLectureshipId))
                && ($subjectAcronym = $tblImportLectureship->getSubjectAcronym())
            ) {
                // Fach in Schulsoftware gefunden
                if (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym))
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
            . new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS, array('ImportId' => $tblImport->getId(), 'Tab' => $NextTab));
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
            && ($tblDivisionCourse->getIsDivisionOrCoreGroup() || $tblDivisionCourse->getType() == TblDivisionCourseType::TYPE_TEACHING_GROUP)
        ) {
            return $tblDivisionCourse;
        }

        return false;
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
}