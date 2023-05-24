<?php
namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use DateInterval;
use DateTime;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Setup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group as GroupPerson;
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as ConsumerGatekeeper;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

class Service extends ServiceYearChange
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
     * @return array
     */
    public function migrateTblDivisionToTblDivisionCourse(): array
    {
        return (new Data($this->getBinding()))->migrateTblDivisionToTblDivisionCourse();
    }

    /**
     * @return array
     */
    public function migrateTblGroupToTblDivisionCourse(): array
    {
        return (new Data($this->getBinding()))->migrateTblGroupToTblDivisionCourse();
    }

    /**
     * @param TblYear $tblYear
     *
     * @return float
     */
    public function migrateYear(TblYear $tblYear): float
    {
        return (new Data($this->getBinding()))->migrateDivisionContent($tblYear);
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
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->deleteEntityListBulk($tblEntityList);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByMigrateGroupId($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseByMigrateGroupId($Id);
    }

    /**
     * @param $string
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByMigrateSekCourse($string)
    {
        return (new Data($this->getBinding()))->getDivisionCourseByMigrateSekCourse($string);
    }

    /**
     * @param string|null $TypeIdentifier
     * @param bool $isReporting
     * @param bool $isShowInPersonData
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseAll(?string $TypeIdentifier = '', $isReporting = false, $isShowInPersonData = false)
    {
        return (new Data($this->getBinding()))->getDivisionCourseAll($TypeIdentifier, $isReporting, $isShowInPersonData);
    }

    /**
     * $isSubjectCourseIgnore ignoriert Lerngruppen und SEKII-Kurse
     *
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     * @param bool $isSubjectCourseIgnore
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListBy(TblYear $tblYear = null, ?string $TypeIdentifier = '', bool $isSubjectCourseIgnore = false)
    {
        $list = (new Data($this->getBinding()))->getDivisionCourseListBy($tblYear, $TypeIdentifier);
        if ($isSubjectCourseIgnore && $list) {
            $dataList = array();
            foreach($list as $tblDivisionCourse) {
                if (($identifier = $tblDivisionCourse->getType()->getIdentifier())
                    && ($identifier == TblDivisionCourseType::TYPE_TEACHER_GROUP
                        || $identifier == TblDivisionCourseType::TYPE_BASIC_COURSE
                        || $identifier == TblDivisionCourseType::TYPE_ADVANCED_COURSE
                    )
                ) {
                    continue;
                }
                $dataList[] = $tblDivisionCourse;
            }

            return empty($dataList) ? false : $dataList;
        } else {
            return $list;
        }
    }

    /**
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByIsShownInPersonData(TblYear $tblYear = null, ?string $TypeIdentifier = '')
    {
        return (new Data($this->getBinding()))->getDivisionCourseListByIsShownInPersonData($tblYear, $TypeIdentifier);
    }

    /**
     * @param string $name
     * @param array|null $tblYearList
     * @param bool $isOnlyTypeDivisionOrCoreGroup
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByLikeName(string $name, ?array $tblYearList = null, bool $isOnlyTypeDivisionOrCoreGroup = false)
    {
        $tempList = (new Data($this->getBinding()))->getDivisionCourseListByLikeName($name, $tblYearList);
        if ($isOnlyTypeDivisionOrCoreGroup && $tempList) {
            $resultList = array();
            foreach ($tempList as $tblDivisionCourse) {
                if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION
                    || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP
                ) {
                    $resultList[] = $tblDivisionCourse;
                }
            }

            return empty($resultList) ? false : $resultList;
        } else {
            return $tempList;
        }
    }

    /**
     * @param TblYear $tblYear
     * @param bool $isReporting
     * @param bool $isShowInPersonData
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByYear(TblYear $tblYear, $isReporting = false, $isShowInPersonData = false)
    {
        return (new Data($this->getBinding()))->getDivisionCourseListByYear($tblYear, $isReporting, $isShowInPersonData);
    }

    /**
     * @param $name
     * @param TblYear $tblYear
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByNameAndYear($name, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getDivisionCourseByNameAndYear($name, $tblYear);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return TblDivisionCourseLink
     */
    public function addSubDivisionCourseToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse)
    {
        return (new Data($this->getBinding()))->addSubDivisionCourseToDivisionCourse($tblDivisionCourse, $tblSubDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return bool
     */
    public function removeSubDivisionCourseFromDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse): bool
    {
        return (new Data($this->getBinding()))->removeSubDivisionCourseFromDivisionCourse($tblDivisionCourse, $tblSubDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getSubDivisionCourseListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getAboveDivisionCourseListBySubDivisionCourse(TblDivisionCourse $tblSubDivisionCourse)
    {
        return (new Data($this->getBinding()))->getAboveDivisionCourseListBySubDivisionCourse($tblSubDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $resultList
     *
     * @return bool
     */
    public function getSubDivisionCourseRecursiveListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, array &$resultList): bool
    {
        if (($tblDivisionCourseList = $this->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (isset($resultList[$tblDivisionCourse->getId()])) {
                    return false;
                }
                $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                $this->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $resultList);
            }
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseLink
     */
    public function getDivisionCourseLinkById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseLinkById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeById($Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeByIdentifier(string $Identifier)
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblDivisionCourseType[]
     */
    public function getDivisionCourseTypeListWithoutTeacherGroup()
    {
        $resultList = array();
        if (($tempList = (new Data($this->getBinding()))->getDivisionCourseTypeAll())) {
            foreach ($tempList as $tblDivisionCourseType) {
                if ($tblDivisionCourseType->getIdentifier() != TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                    $resultList[$tblDivisionCourseType->getId()] = $tblDivisionCourseType;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberTypeByIdentifier($Identifier);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     *
     * @return bool
     */
    public function getIsCourseSystemBySchoolTypeAndLevel(TblType $tblSchoolType, int $level): bool
    {
        return ($tblSchoolType->getShortName() == 'Gy' && preg_match('!(11|12)!is', $level))
            || ($tblSchoolType->getShortName() == 'BGy' && preg_match('!(12|13)!is', $level));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function getIsCourseSystemByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear): bool
    {
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            return $this->getIsCourseSystemByStudentEducation($tblStudentEducation);
        }

        return false;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return bool
     */
    public function getIsCourseSystemByStudentEducation(TblStudentEducation $tblStudentEducation): bool
    {
        if (($level = $tblStudentEducation->getLevel())
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
        ) {
            return $this->getIsCourseSystemBySchoolTypeAndLevel($tblSchoolType, $level);
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function getIsCourseSystemByStudentsInDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        // bei Stammgruppe und Klasse query direkt über datenbank
        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION
            || $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP
        ) {
            return  (new Data($this->getBinding()))->getIsCourseSystemByStudentsInDivisionOrCoreGroup($tblDivisionCourse);
        }

        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($this->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     *
     * @return bool
     */
    public function getIsShortYearBySchoolTypeAndLevel(TblType $tblSchoolType, int $level): bool
    {
        return ($tblSchoolType->getShortName() == 'Gy' && preg_match('!(12)!is', $level))
            || ($tblSchoolType->getShortName() == 'BGy' && preg_match('!(13)!is', $level));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return bool
     */
    public function getIsShortYearByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear): bool
    {
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
        ) {
            return $this->getIsShortYearBySchoolTypeAndLevel($tblSchoolType, $tblStudentEducation->getLevel());
        }

        return false;
    }

    /**
     * @param TblYear $tblYear
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblType|null $tblTypeSchool
     * @param string $level
     *
     * @return array|tblPerson[]
     */
    public function getPersonListByYear(TblYear $tblYear, ?TblDivisionCourse $tblDivisionCourse = null,
    ?TblType $tblTypeSchool = null, string $level = '')
    {

        $returnPersonList = array();
//        if($tblDivisionCourse &&
//            !($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION
//          || $tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
//        ){
//            $this->getStudentListByDivisionCourseByFilter($returnPersonList, $tblYear, $tblDivisionCourse, $tblTypeSchool, $level);
//        } elseif ($tblDivisionCourse &&
//            ($tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE
//                || $tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE)
//        ) {
//            if(($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListBySubjectDivisionCourse($tblDivisionCourse))){
//                foreach($tblStudentSubjectList as $tblStudentSubject){
//                    if(($tblPersonSubject = $tblStudentSubject->getServiceTblPerson())){
//                        $returnPersonList[$tblPersonSubject->getId()] = $tblPersonSubject;
//                    }
//                }
//            }
//        } else {
        $ResultList = (new Data($this->getBinding()))->getDivisionCourseListByYearAndDivisionCourseAndTypeAndLevel($tblYear, $tblDivisionCourse,
            $tblTypeSchool, $level);
        if(!empty($ResultList)){
            foreach($ResultList as $Row){
                if(($tblPersonResult = Person::useService()->getPersonById($Row['PersonId']))){
                    $returnPersonList[$tblPersonResult->getId()] = $tblPersonResult;
                }
            }
        }
        // Personen aus verlinkten Kursen hinzufügen
        if($tblDivisionCourse){
            $this->getStudentListByDivisionCourseByFilter($returnPersonList, $tblYear, $tblDivisionCourse, $tblTypeSchool, $level);
        }
//        }
        return $returnPersonList;
    }

    /**
     * @param array             $returnPersonList
     * @param TblYear           $tblYear
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblType|null      $tblTypeSchool
     * @param string            $level
     *
     * @return void
     */
    private function getStudentListByDivisionCourseByFilter(&$returnPersonList, TblYear $tblYear, TblDivisionCourse $tblDivisionCourse, TblType $tblTypeSchool = null, $level = '')
    {

        if(($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())){
            foreach($tblPersonList as $tblPerson){
                if($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)){
                    if($tblTypeSchool){
                        if(($tblStudentSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            && $tblStudentSchoolType->getId() == $tblTypeSchool->getId()) {
                            if($level) {
                                if ($tblStudentEducation->getLevel() == $level) {
                                    $returnPersonList[$tblPerson->getId()] = $tblPerson;
                                }
                            } else {
                                $returnPersonList[$tblPerson->getId()] = $tblPerson;
                            }
                        }
                    } elseif ($level){
                        if($tblStudentEducation->getLevel() == $level) {
                            $returnPersonList[$tblPerson->getId()] = $tblPerson;
                        }
                    } else {
                        $returnPersonList[$tblPerson->getId()] = $tblPerson;
                    }
                }
            }
        }
    }

    /**
     * @param string $Date
     *
     * @return array|TblDivisionCourse[]
     */
    public function getDivisionCourseListByDate(string $Date = 'now'):array
    {
        $result = array();
        if(($tblYearList = Term::useService()->getYearAllByDate(new DateTime($Date)))) {
            foreach ($tblYearList as $tblYear) {
                if(($tblDivisionCourseList = $this->getDivisionCourseListByYear($tblYear))) {
                    $result = array_merge($result, $tblDivisionCourseList);
                }
            }
        }
        return $result;
    }

    /**
     * @param $Filter
     * @param $Data
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return false|Form
     */
    public function checkFormDivisionCourse($Filter, $Data, TblDivisionCourse $tblDivisionCourse = null)
    {
        $error = false;
        $form = DivisionCourse::useFrontend()->formDivisionCourse($tblDivisionCourse ? $tblDivisionCourse->getId() : null, $Filter, false, $Data);

        $tblYear = $tblDivisionCourse ? $tblDivisionCourse->getServiceTblYear() : false;
        $tblType = $tblDivisionCourse ? $tblDivisionCourse->getType() : false;
        if (!$tblDivisionCourse) {
            if (!isset($Data['Year']) || !($tblYear = Term::useService()->getYearById($Data['Year']))) {
                $form->setError('Data[Year]', 'Bitte wählen Sie ein Schuljahr aus');
                $error = true;
            }
            if (!isset($Data['Type']) || !($tblType = DivisionCourse::useService()->getDivisionCourseTypeById($Data['Type']))) {
                $form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
                $error = true;
            }
        }

        if ($tblType && $tblType->getIsCourseSystem()) {
            if (!isset($Data['Subject']) || !(Subject::useService()->getSubjectById($Data['Subject']))) {
                $form->setError('Data[Subject]', 'Bitte wählen Sie ein Fach aus');
                $error = true;
            }
        }

        if (!isset($Data['Name']) || empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name ein');
            $error = true;
        }
        if (isset($Data['Name']) && $Data['Name'] != '') {
            // ist ein UCS Mandant?
            $IsUCSMandant = false;
            if(($tblConsumer = ConsumerGatekeeper::useService()->getConsumerBySession())
                && ConsumerGatekeeper::useService()->getConsumerLoginByConsumerAndSystem($tblConsumer, TblConsumerLogin::VALUE_SYSTEM_UCS)
            ){
                $IsUCSMandant = true;
            }
            // Name Zeicheneingrenzung für Klassen und Stammgruppen, falls diese an angeschlossene Systeme übertragen werden müssen (UCS)
            if ($IsUCSMandant && $tblType && ($tblType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)) {
                // Gleiche Logik für Klassen und Stammgruppen
                // erlaubte Zeichen: [a-zA-Z0-9 -]
                // am Anfang und Ende dürfen nur Zahlen und Buchstaben sein
                if (!preg_match('!^[\w \-]+$!', $Data['Name'])) {
                    $form->setError('Data[Name]', 'Erlaubte Zeichen [a-zA-Z0-9 -]');
                    $error = true;
                } else {
                    if (preg_match('!^[ \-]!', $Data['Name'])) {
                        $form->setError('Data[Name]', 'Darf nicht mit einem "-" beginnen');
                        $error = true;
                    } elseif (preg_match('![ \-]$!', $Data['Name'])) {
                        $form->setError('Data[Name]', 'Darf nicht mit einem "-" aufhören');
                        $error = true;
                    }
                }
            }
            // Prüfung ob name schon mal verwendet wird
            if ($tblYear && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                    if ($tblDivisionCourse && $tblDivisionCourse->getId() == $tblDivisionCourseItem->getId()) {
                        continue;
                    }

                    if (strtolower($Data['Name']) == strtolower($tblDivisionCourseItem->getName())) {
                        $form->setError('Data[Name]', 'Ein Kurs mit diesem Name existiert bereits im Schuljahr');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param array $Data
     *
     * @return false|TblDivisionCourse
     */
    public function createDivisionCourse(array $Data)
    {
        if (($tblYear = Term::useService()->getYearById($Data['Year']))
            && ($tblType = DivisionCourse::useService()->getDivisionCourseTypeById($Data['Type']))
        ) {
            $tblSubject = isset($Data['Subject']) ? Subject::useService()->getSubjectById($Data['Subject']) : null;
            return (new Data($this->getBinding()))->createDivisionCourse($tblType, $tblYear, $Data['Name'], $Data['Description'],
                isset($Data['IsShownInPersonData']), isset($Data['IsReporting']), $tblSubject);
        } else {
            return false;
        }
    }

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param TblSubject|null $tblSubject
     *
     * @return TblDivisionCourse
     */
    public function insertDivisionCourse(
        TblDivisionCourseType $tblType, TblYear $tblYear, string $name, string $description, bool $isShownInPersonData, bool $isReporting, ?TblSubject $tblSubject
    ): TblDivisionCourse {
        return (new Data($this->getBinding()))->createDivisionCourse($tblType, $tblYear, $name, $description, $isShownInPersonData, $isReporting, $tblSubject);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $Data
     *
     * @return bool
     */
    public function updateDivisionCourse(TblDivisionCourse $tblDivisionCourse, array $Data): bool
    {
        $tblSubject = isset($Data['Subject']) ? Subject::useService()->getSubjectById($Data['Subject']) : null;
        return (new Data($this->getBinding()))->updateDivisionCourse($tblDivisionCourse, $Data['Name'], $Data['Description'],
            isset($Data['IsShownInPersonData']), isset($Data['IsReporting']), $tblSubject);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function destroyDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        // Verknüpfungen mit anderen Kursen löschen
        if (($tblSubDivisionCourseList = $this->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
            foreach ($tblSubDivisionCourseList as $tblSubDivisionCourse) {
                $this->removeSubDivisionCourseFromDivisionCourse($tblDivisionCourse, $tblSubDivisionCourse);
            }
        }

        // alle Mitglieder löschen
        (new Data($this->getBinding()))->removeDivisionCourseMemberAllFromDivisionCourse($tblDivisionCourse);

        // alle Schüler-Fächer verknüpfungen löschen (SekII)
        if (($tblStudentSubjectList = $this->getStudentSubjectListBySubjectDivisionCourse($tblDivisionCourse))) {
            (new Data($this->getBinding()))->destroyStudentSubjectBulkList($tblStudentSubjectList);
        }

        // alle Lehraufträge löschen
        if (($tblTeacherLectureshipList = $this->getTeacherLectureshipListBy(null, null, $tblDivisionCourse, null))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                (new Data($this->getBinding()))->destroyTeacherLectureship($tblTeacherLectureship);
            }
        }

        return (new Data($this->getBinding()))->destroyDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberById($Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberByPerson(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblMemberType, $tblPerson);
    }

    /**
     * Alle Schüler eines Kurses inklusive aller verknüpften Sub-Kurse
     *
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $withInActive
     * @param bool $isResultPersonList
     *
     * @return false|TblDivisionCourseMember[]|TblPerson[]
     */
    public function getStudentListBy(TblDivisionCourse $tblDivisionCourse, bool $withInActive = false, bool $isResultPersonList = true)
    {
        $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
        DivisionCourse::useService()->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $tblDivisionCourseList);

        $studentList = array();
        foreach ($tblDivisionCourseList as $item) {
            if (($list = $this->getDivisionCourseMemberListBy($item, TblDivisionCourseMemberType::TYPE_STUDENT, $withInActive, $isResultPersonList))) {
                $studentList = array_merge($studentList, $list);
            }
        }

        return empty($studentList) ? false : $studentList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $memberTypeIdentifier
     * @param bool $withInActive
     * @param bool $isResultPersonList
     *
     * @return TblDivisionCourseMember[]|TblPerson[]|false
     */
    public function getDivisionCourseMemberListBy(TblDivisionCourse $tblDivisionCourse, string $memberTypeIdentifier, bool $withInActive = false, bool $isResultPersonList = true)
    {
        $memberList = false;
        $tblMemberType = $this->getDivisionCourseMemberTypeByIdentifier($memberTypeIdentifier);
        if ($memberTypeIdentifier == TblDivisionCourseMemberType::TYPE_STUDENT && ($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                $memberList = (new Data($this->getBinding()))->getDivisionCourseMemberStudentByDivision($tblDivisionCourse);
            } elseif ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP) {
                $memberList = (new Data($this->getBinding()))->getDivisionCourseMemberStudentByCoreGroup($tblDivisionCourse);
            }
        } elseif ($tblMemberType) {
            $memberList = (new Data($this->getBinding()))->getDivisionCourseMemberListBy($tblDivisionCourse, $tblMemberType);
        }

        if (!empty ($memberList)) {
            // inaktive aussortieren
            if (!$withInActive) {
                $list = array();
                $now = new DateTime('now');
                foreach ($memberList as $tblDivisionCourseMember) {
                    if ($tblDivisionCourseMember->getLeaveDateTime() !== null && $now > $tblDivisionCourseMember->getLeaveDateTime()) {
                        // inaktiv
                    } else {
                        $list[] = $tblDivisionCourseMember;
                    }
                }

                $memberList = $list;
            }

            // ist Kursliste sortiert
            $isSorted = false;
            foreach ($memberList as $tblDivisionCourseMember) {
                if ($tblDivisionCourseMember->getSortOrder() !== null) {
                    $isSorted = true;
                    break;
                }
            }

            if ($isSorted) {
                $memberList = $this->getSorter($memberList)->sortObjectBy('SortOrder');
            } else {
                $memberList = $this->getSorter($memberList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            }

            if ($isResultPersonList) {
                $personList = array();
                foreach ($memberList as $tblDivisionCourseMember) {
                    if ($tblDivisionCourseMember->getServiceTblPerson()) {
                        $personList[] = $tblDivisionCourseMember->getServiceTblPerson();
                    }
                }

                return empty($personList) ? false : $personList;
            } else {
                return empty($memberList) ? false : $memberList;
            }
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     * @param string $description
     *
     * @return TblDivisionCourseMember
     */
    public function addDivisionCourseMemberToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType,
        TblPerson $tblPerson, string $description): TblDivisionCourseMember
    {
        $maxSortOrder = $this->sortDivisionCourseMember($tblDivisionCourse, $tblMemberType);

        return (new Data($this->getBinding()))->addDivisionCourseMemberToDivisionCourse($tblDivisionCourse, $tblMemberType, $tblPerson, $description, $maxSortOrder);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function addStudentToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson): bool
    {
        $maxSortOrder = $this->sortDivisionCourseMember($tblDivisionCourse, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT));

        // Schüler in Klassen und Stammgruppen werden anders gespeichert (TblStudentEducation)
        if (($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                    $tblStudentEducation->setTblDivision($tblDivisionCourse);
                    $tblStudentEducation->setDivisionSortOrder($maxSortOrder);
                } else {
                    $tblStudentEducation->setTblCoreGroup($tblDivisionCourse);
                    $tblStudentEducation->setCoreGroupSortOrder($maxSortOrder);
                }

                return (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
            } else {
                // Interessent
                $tblStudentEducation = new TblStudentEducation();
                $tblStudentEducation->setServiceTblPerson($tblPerson);
                $tblStudentEducation->setServiceTblYear($tblYear);
                if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                    $tblStudentEducation->setTblDivision($tblDivisionCourse);
                } else {
                    $tblStudentEducation->setTblCoreGroup($tblDivisionCourse);
                }

                if ((new Data($this->getBinding()))->createStudentEducation($tblStudentEducation)) {
                    // falls Interessent → dann Schüler draus machen
                    if (($tblProspectGroup = PersonGroup::useService()->getGroupByMetaTable('PROSPECT'))
                        && ($tblStudentGroup = PersonGroup::useService()->getGroupByMetaTable('STUDENT'))
                        && PersonGroup::useService()->existsGroupPerson($tblProspectGroup, $tblPerson)
                    ) {
                        PersonGroup::useService()->removeGroupPerson($tblProspectGroup, $tblPerson);
                        PersonGroup::useService()->addGroupPerson($tblStudentGroup, $tblPerson);
                    }

                    return true;
                }
            }
        } else {
           if ((new Data($this->getBinding()))->addDivisionCourseMemberToDivisionCourse(
               $tblDivisionCourse, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT), $tblPerson, '', $maxSortOrder
           )) {
               return true;
           }
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblDivisionCourseMemberType
     *
     * @return int|null
     */
    private function sortDivisionCourseMember(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblDivisionCourseMemberType): ?int
    {
        $maxSortOrder = $this->getDivisionCourseMemberMaxSortOrder($tblDivisionCourse, $tblDivisionCourseMemberType);
        // Kurs ist noch nicht sortiert
        if ($maxSortOrder === null) {
            // ist der Kurs im aktuellen Schuljahr und Schuljahr noch nicht älter als 1 Monat → Schüler sortieren und neuen Schüler hinten anfügen
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                $today = new DateTime('today');
                /** @var DateTime $startDate */
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDate && $endDate
                    && $today > $startDate
                    && $today < $endDate
                    && ($firstMonthDate = clone $startDate)
                    && $today > ($firstMonthDate->add(new DateInterval('P1M')))
                ) {
                    if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
                        $tblDivisionCourse, $tblDivisionCourseMemberType->getIdentifier(), true, false
                    ))) {
                        $tblMemberList = (new Extension())->getSorter($tblMemberList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                        $count = 1;
                        /** @var TblDivisionCourseMember $tblMember */
                        foreach ($tblMemberList as $tblMember) {
                            $tblMember->setSortOrder($count++);
                        }
                        DivisionCourse::useService()->updateDivisionCourseMemberBulkSortOrder(
                            $tblMemberList, $tblDivisionCourseMemberType->getIdentifier(), $tblDivisionCourse->getType() ?: null
                        );
                        $maxSortOrder = $count;
                    }
                }
            }
        } else {
            $maxSortOrder++;
        }

        return $maxSortOrder;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return int|null
     */
    private function getDivisionCourseMemberMaxSortOrder(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType): ?int
    {
        if ($tblMemberType->getIdentifier() == TblDivisionCourseMemberType::TYPE_STUDENT
            && ($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                return (new Data($this->getBinding()))->getStudentEducationDivisionMaxSortOrder($tblDivisionCourse);
            } else {
                return (new Data($this->getBinding()))->getStudentEducationCoreGroupMaxSortOrder($tblDivisionCourse);
            }
        } else {
            return (new Data($this->getBinding()))->getDivisionCourseMemberMaxSortOrder($tblDivisionCourse, $tblMemberType);
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return false|TblDivisionCourseMember[]
     */
    public function getDivisionCourseMemberListByPersonAndYearAndMemberType(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourseMemberType $tblMemberType)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberListByPersonAndYearAndMemberType($tblPerson, $tblYear, $tblMemberType);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool $withTeacherGroup
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByDivisionTeacher(TblPerson $tblPerson, TblYear $tblYear, bool $withTeacherGroup = false)
    {
        $resultList = array();
        if (($tblMemberType = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
            && ($tblDivisionCourseMemberList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType(
                $tblPerson, $tblYear, $tblMemberType
            ))
        ) {
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                if (($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())) {
                    if (!$withTeacherGroup && $tblDivisionCourse->getType()->getIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                        continue;
                    }

                    $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * Lerngruppen eines Lehrers
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblDivisionCourse[]
     */
    public function getTeacherGroupListByTeacherAndYear(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject = null)
    {
        return (new Data($this->getBinding()))->getTeacherGroupListByTeacherAndYear($tblPerson, $tblYear, $tblSubject);
    }

    /**
     * Lerngruppen eines Schülers für ein Fach
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblDivisionCourse[]
     */
    public function getTeacherGroupListByStudentAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTeacherGroupListByStudentAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
    }

    /**
     * @param TblDivisionCourseMember $tblDivisionCourseMember
     *
     * @return bool
     */
    public function removeDivisionCourseMemberFromDivisionCourse(TblDivisionCourseMember $tblDivisionCourseMember): bool
    {
        return (new Data($this->getBinding()))->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeStudentFromDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson): bool
    {
        // Schüler in Klassen und Stammgruppen werden anders gespeichert (TblStudentEducation)
        if (($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                if (($tblStudentEducationList = (new Data($this->getBinding()))->getStudentEducationListByDivision($tblDivisionCourse, $tblPerson))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        $tblStudentEducation->setTblDivision(null);
                        $tblStudentEducation->setDivisionSortOrder(null);

                        (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
                    }

                    return true;
                }
            } elseif ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP) {
                if (($tblStudentEducationList = (new Data($this->getBinding()))->getStudentEducationListByCoreGroup($tblDivisionCourse, $tblPerson))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        $tblStudentEducation->setTblCoreGroup(null);
                        $tblStudentEducation->setCoreGroupSortOrder(null);

                        (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
                    }

                    return true;
                }
            }
        } else {
            if (($tblDivisionCourseMember = (new Data($this->getBinding()))->getDivisionCourseMemberByPerson(
                $tblDivisionCourse, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT), $tblPerson
            ))) {
                return (new Data($this->getBinding()))->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember);
            }
        }

        return false;
    }

    /**
     * @param array $tblDivisionCourseMemberList
     * @param string $MemberTypeIdentifier
     * @param TblDivisionCourseType|null $tblDivisionCourseType
     *
     * @return bool
     */
    public function updateDivisionCourseMemberBulkSortOrder(array $tblDivisionCourseMemberList, string $MemberTypeIdentifier, ?TblDivisionCourseType $tblDivisionCourseType): bool
    {
        // Schüler in Klassen und Stammgruppen werden anders gespeichert (TblStudentEducation)
        if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_STUDENT && $tblDivisionCourseType
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            $updateStudentEducationList = array();
            /** @var TblDivisionCourseMember $tblDivisionCourseMember */
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION){
                    if (($tblPerson = $tblDivisionCourseMember->getServiceTblPerson())
                        && ($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())
                        && ($tblStudentEducation = (new Data($this->getBinding()))->getStudentEducationByDivision(
                            $tblDivisionCourse, $tblPerson, $tblDivisionCourseMember->getLeaveDateTime() ?: null
                        ))
                    ) {
                        $tblStudentEducation->setDivisionSortOrder($tblDivisionCourseMember->getSortOrder());
                        $updateStudentEducationList[] = $tblStudentEducation;
                    }
                } elseif ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP) {
                    if (($tblPerson = $tblDivisionCourseMember->getServiceTblPerson())
                        && ($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())
                        && ($tblStudentEducation = (new Data($this->getBinding()))->getStudentEducationByCoreGroup(
                            $tblDivisionCourse, $tblPerson, $tblDivisionCourseMember->getLeaveDateTime() ?: null
                        ))
                    ) {
                        $tblStudentEducation->setCoreGroupSortOrder($tblDivisionCourseMember->getSortOrder());
                        $updateStudentEducationList[] = $tblStudentEducation;
                    }
                }
            }

            return (new Data($this->getBinding()))->updateStudentEducationBulk($updateStudentEducationList);
        } else {
            return (new Data($this->getBinding()))->updateDivisionCourseMemberBulk($tblDivisionCourseMemberList);
        }
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function createDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        return (new Data($this->getBinding()))->createDivisionCourseMemberBulk($tblDivisionCourseMemberList);
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function updateDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        return (new Data($this->getBinding()))->updateDivisionCourseMemberBulk($tblDivisionCourseMemberList);
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function removeDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        return (new Data($this->getBinding()))->removeDivisionCourseMemberBulk($tblDivisionCourseMemberList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function getDivisionCourseHeader(TblDivisionCourse $tblDivisionCourse): string
    {
        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Kurs', $tblDivisionCourse->getName() . ' ' . new Small(new Muted($tblDivisionCourse->getTypeName())), Panel::PANEL_TYPE_INFO)
                    , 6),
                new LayoutColumn(
                    new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                    , 6)
            ))
        )));
    }

    /**
     * zählt die aktiven Schüler
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tblType = $tblDivisionCourse->getType())) {
            switch ($tblType->getIdentifier()) {
                case TblDivisionCourseType::TYPE_DIVISION: return (new Data($this->getBinding()))->getCountStudentByDivision($tblDivisionCourse);
                case TblDivisionCourseType::TYPE_CORE_GROUP: return (new Data($this->getBinding()))->getCountStudentByCoreGroup($tblDivisionCourse);
                default: return (new Data($this->getBinding()))->getCountStudentByDivisionCourse($tblDivisionCourse);
            }
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tblType = $tblDivisionCourse->getType())) {
            switch ($tblType->getIdentifier()) {
                case TblDivisionCourseType::TYPE_DIVISION: return (new Data($this->getBinding()))->getCountInActiveStudentByDivision($tblDivisionCourse);
                case TblDivisionCourseType::TYPE_CORE_GROUP: return (new Data($this->getBinding()))->getCountInActiveStudentByCoreGroup($tblDivisionCourse);
                default: return (new Data($this->getBinding()))->getCountInActiveStudentByDivisionCourse($tblDivisionCourse);
            }
        }

        return 0;
    }

    /**
     * @param array $tblDivisionCourseList
     * @return array
     */
    public function getStudentInfoByDivisionCourseList(array $tblDivisionCourseList): array
    {
        $countActive = 0;
        $countInActive = 0;
        $genderList = array();
        $genders = '';

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
            ) {
                foreach ($tblStudentMemberList as $tblStudentMember) {
                    if (($tblPerson = $tblStudentMember->getServiceTblPerson())) {
                        if ($tblStudentMember->isInActive()) {
                            $countInActive++;
                        } else {
                            $countActive++;
                        }

                        if (($tblGender = $tblPerson->getGender())) {
                            if (isset($genderList[$tblGender->getShortName()])) {
                                $genderList[$tblGender->getShortName()]++;
                            } else {
                                $genderList[$tblGender->getShortName()] = 1;
                            }
                        }
                    }
                }
            }
        }

        foreach ($genderList as $key => $count) {
            $genders .= ($genders ? '<br/>' : '') . $key . ': ' . $count;
        }

        $toolTip = $countInActive . ($countInActive == 1 ? ' deaktivierter Schüler' : ' deaktivierte Schüler');
        $students = $countActive . ($countInActive > 0 ? ' + ' . new ToolTip('(' . $countInActive . new Info() . ')', $toolTip) : '');

        return array($students, $genders);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsForced
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByPerson(TblPerson $tblPerson, bool $IsForced = false)
    {
        return (new Data($this->getBinding()))->getStudentEducationListByPerson($tblPerson, $IsForced);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getStudentEducationListByPersonAndYear($tblPerson, $tblYear);
    }

    /**
     * @return array
     */
    public function getStudentEducationLevelListForSelectbox()
    {

        $LevelList = (new Data($this->getBinding()))->getStudentEducationLevelList();
        $LevelListing[] = '-[ Nicht ausgewählt ]-';
        if(!empty($LevelList)){
            foreach($LevelList as $Level){
                $LevelListing[] = current($Level);
            }
            sort($LevelListing);
        }
        return $LevelListing;
    }

    /**
     * @param TblYear $tblYear
     * @param TblType|null $tblSchoolType
     * @param null $level
     * @param TblDivisionCourse|null $tblDivision
     * @param TblDivisionCourse|null $tblCoreGroup
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListBy(TblYear $tblYear, TblType $tblSchoolType = null, $level = null, TblDivisionCourse $tblDivision = null,
        TblDivisionCourse $tblCoreGroup = null)
    {
        return (new Data($this->getBinding()))->getStudentEducationListBy($tblYear, $tblSchoolType, $level, $tblDivision, $tblCoreGroup);
    }

    /**
     * @param TblYear $tblYearSelected
     *
     * @return array
     */
    public function getLeaveStudents(TblYear $tblYear): array
    {

        $personList = array();
        $split = explode('/', $tblYear->getName());
        $tblYearNextList = Term::useService()->getYearByName(
            ((int) $split[0] + 1) . '/' . ((int) $split[1] + 1)
        );
        if (($tblYearList = Term::useService()->getYearsByYear($tblYear))
            && ($tblYearNextList)
        ) {
            foreach ($tblYearList as $tblYear) {
                if (($tblStudentEducationList = $this->getStudentEducationListBy($tblYear))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        if (($tblStudentEducation->getTblDivision() || $tblStudentEducation->getTblCoreGroup())
                            && ($tblPerson = $tblStudentEducation->getServiceTblPerson())
                        ) {
                            $tblDivisionCourseDivision = $tblStudentEducation->getTblDivision();
                            $tblDivisionCourseCoreGroup = $tblStudentEducation->getTblCoreGroup();
                            $isAddPerson = false;
                            foreach ($tblYearNextList as $tblYearNext) {
                                $isAddPerson = $this->getStudentEducationListByPersonAndYear($tblPerson, $tblYearNext) == false;
                                if ($isAddPerson) {
                                    break;
                                }
                            }
                            if ($isAddPerson) {
                                $personList[$tblPerson->getId()] = array(
                                    'tblPerson'                  => $tblPerson,
                                    'tblDivisionCourseDivision'  => $tblDivisionCourseDivision ? $tblDivisionCourseDivision : null,
                                    'tblDivisionCourseCoreGroup' => $tblDivisionCourseCoreGroup ? $tblDivisionCourseCoreGroup : null,
                                    'tblStudentEducation'        => $tblStudentEducation,
                                );
                            }
                        }
                    }
                }
            }
        }
        return $personList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $date
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByPersonAndDate(TblPerson $tblPerson, string $date = 'now')
    {
        $dateTime = new DateTime($date);
        if (($tblYearList = Term::useService()->getYearAllByDate($dateTime))) {
            foreach ($tblYearList as $tblYear) {
                if (($tblStudentEducation = $this->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    return $tblStudentEducation;
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $date
     *
     * @return string
     */
    public function getCurrentMainCoursesByPersonAndDate(TblPerson $tblPerson, string $date = 'now'): string
    {
        $result = '';
        if (($tblStudentEducation = $this->getStudentEducationByPersonAndDate($tblPerson, $date)))
        {
            $result = $this->getCurrentMainCoursesByStudentEducation($tblStudentEducation);
        }

        return $result;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function getCurrentMainCoursesByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear): string
    {
        $result = '';
        if (($tblStudentEducation = $this->getStudentEducationByPersonAndYear($tblPerson, $tblYear)))
        {
            $result = $this->getCurrentMainCoursesByStudentEducation($tblStudentEducation);
        }

        return $result;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return string
     */
    public function getCurrentMainCoursesByStudentEducation(TblStudentEducation $tblStudentEducation): string
    {
        $result = '';
        if (($tblDivision = $tblStudentEducation->getTblDivision())
            && ($displayDivision = $tblDivision->getName())
        ) {
            $result = 'Klasse: ' . $displayDivision;
        }
        if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
            && ($displayCoreGroup = $tblCoreGroup->getName())
        ) {
            $result .= ($result ? ', ': '') . 'Stammgruppe: ' . $displayCoreGroup;
        }

        return $result;
    }

    /**
     * @param $Id
     *
     * @return TblStudentEducation|false
     */
    public function getStudentEducationById($Id)
    {
        return (new Data($this->getBinding()))->getStudentEducationById($Id);
    }

    /**
     * @return array|bool|TblCompany[]
     */
    public function getSchoolListForStudentEducation()
    {
        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        $tblCompanyAllOwn = array();

        // Normaler Inhalt
        $tblSchoolList = School::useService()->getSchoolAll();
        if ($tblSchoolList) {
            foreach ($tblSchoolList as $tblSchool) {
                if ($tblSchool->getServiceTblCompany()) {
                    $tblCompanyAllOwn[] = $tblSchool->getServiceTblCompany();
                }
            }
        }

        if (empty($tblCompanyAllOwn)) {
            $resultList = $tblCompanyAllSchool;
        } else {
            $resultList = $tblCompanyAllOwn;
        }

        return $resultList;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return false|Form
     */
    public function checkFormChangeDivisionCourse($Data, TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson)
    {
        $form = DivisionCourse::useFrontend()->formChangeDivisionCourse($tblDivisionCourse, $tblPerson, $tblDivisionCourse->getServiceTblYear());

        $error = $this->checkStudentEducationData($Data, $form);

        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
            if (($tblDivisionCourseNew = DivisionCourse::useService()->getDivisionCourseById($Data['Division']))
                && $tblDivisionCourseNew->getId() == $tblDivisionCourse->getId())
            {
                $form->setError('Data[Division]', 'Bitte wählen Sie eine neue Klasse aus');
                $error = true;
            }
        } else {
            if (($tblDivisionCourseNew = DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']))
                && $tblDivisionCourseNew->getId() == $tblDivisionCourse->getId()
            ) {
                $form->setError('Data[CoreGroup]', 'Bitte wählen Sie eine neue Stammgruppe aus');
                $error = true;
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblStudentEducation|null $tblStudentEducation
     *
     * @return false|Form
     */
    public function checkFormEditStudentEducation($Data, TblPerson $tblPerson, ?TblDivisionCourse $tblDivisionCourse, ?TblStudentEducation $tblStudentEducation)
    {
        $form = DivisionCourse::useFrontend()->formEditStudentEducation($tblPerson, $tblDivisionCourse, $tblStudentEducation);

        $error = $this->checkStudentEducationData($Data, $form, 'StudentEducationData');

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param Form $form
     * @param string $DataName
     *
     * @return bool
     */
    private function checkStudentEducationData($Data, Form $form, string $DataName = 'Data'): bool
    {
        $error = false;
        $tblSchoolType = false;
        if (!isset($Data['SchoolType']) || !($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            $form->setError($DataName . '[SchoolType]', 'Bitte wählen Sie eine Schulart aus');
            $error = true;
        } else {
            $form->setSuccess($DataName . '[SchoolType]');
        }
        if (!isset($Data['Level']) || empty($Data['Level']) || !intval($Data['Level'])) {
            $form->setError($DataName . '[Level]', 'Bitte geben Sie eine Klassenstufe an');
            $error = true;
        } elseif ($tblSchoolType) {
            $level = $Data['Level'];
            if ($level < 1) {
                $form->setError($DataName . '[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                $error = true;
            // in Berlin sind die Klassenstufen Zuordnungen zu den Schularten anders
            } elseif (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)) {
                switch ($tblSchoolType->getShortName()) {
                    case 'GS':
                        if ($level > 4) {
                            $form->setError($DataName . '[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        } else {
                            $form->setSuccess($DataName . '[Level]');
                        }
                        break;
                    case 'OS':
                        if ($level < 5 || $level > 10) {
                            $form->setError($DataName . '[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        } else {
                            $form->setSuccess($DataName . '[Level]');
                        }
                        break;
                    case 'Gy':
                        if ($level < 5 || $level > 12) {
                            $form->setError($DataName . '[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        } else {
                            $form->setSuccess($DataName . '[Level]');
                        }
                        break;
                    default:
                        if ($level > 13) {
                            $form->setError($DataName . '[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        } else {
                            $form->setSuccess($DataName . '[Level]');
                        }
                }
            }
        } else {
            $form->setSuccess($DataName . '[Level]');
        }
        if (!isset($Data['Company']) || !(Company::useService()->getCompanyById($Data['Company']))) {
            $form->setError($DataName . '[Company]', 'Bitte wählen Sie eine Schule aus');
            $error = true;
        } else {
            $form->setSuccess($DataName . '[Company]');
        }

        return $error;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param $Data
     *
     * @return bool
     */
    public function changeDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, $Data): bool
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducationOld = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))
            && ($tblCompany = Company::useService()->getCompanyById($Data['Company']))
        ) {
            $leaveDate = new DateTime('now');
            if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                $tblDivisionCourseCoreGroupOld = $tblStudentEducationOld->getTblCoreGroup();
                $tblDivisionCourseCoreGroupNew = DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']);
                if ($tblDivisionCourseCoreGroupNew && $tblDivisionCourseCoreGroupOld
                    && $tblDivisionCourseCoreGroupNew->getId() == $tblDivisionCourseCoreGroupOld->getId()
                ) {
                    $coreGroupSortOrder = $tblStudentEducationOld->getCoreGroupSortOrder();
                    // Stammgruppe bleibt → am alten TblStudentEducation Eintrag löschen
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        $tblStudentEducationOld->getTblDivision() ?: null,
                        $tblStudentEducationOld->getDivisionSortOrder(),
                        null,
                        null,
                        $leaveDate
                    );
                } else {
                    $coreGroupSortOrder = null;
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        $tblStudentEducationOld->getTblDivision() ?: null,
                        $tblStudentEducationOld->getDivisionSortOrder(),
                        $tblStudentEducationOld->getTblCoreGroup() ?: null,
                        $tblStudentEducationOld->getCoreGroupSortOrder(),
                        $leaveDate
                    );
                }

                $tblStudentEducationNew = new TblStudentEducation();
                $tblStudentEducationNew->setTblDivision(DivisionCourse::useService()->getDivisionCourseById($Data['Division']) ?: null);
                $tblStudentEducationNew->setTblCoreGroup($tblDivisionCourseCoreGroupNew ?: null);
                $tblStudentEducationNew->setCoreGroupSortOrder($coreGroupSortOrder);
            } else {
                $tblDivisionCourseDivisionOld = $tblStudentEducationOld->getTblDivision();
                $tblDivisionCourseDivisionNew = DivisionCourse::useService()->getDivisionCourseById($Data['Division']);
                if ($tblDivisionCourseDivisionNew && $tblDivisionCourseDivisionOld
                    && $tblDivisionCourseDivisionNew->getId() == $tblDivisionCourseDivisionOld->getId()
                ) {
                    $divisionSortOrderNew = $tblStudentEducationOld->getDivisionSortOrder();
                    // Klasse bleibt → am alten TblStudentEducation Eintrag löschen
                    $tblStudentEducationOld->setTblDivision(null);
                    $tblStudentEducationOld->setDivisionSortOrder(null);
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        null,
                        null,
                        $tblStudentEducationOld->getTblCoreGroup() ?: null,
                        $tblStudentEducationOld->getCoreGroupSortOrder(),
                        $leaveDate
                    );
                } else {
                    $divisionSortOrderNew = null;
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        $tblStudentEducationOld->getTblDivision() ?: null,
                        $tblStudentEducationOld->getDivisionSortOrder(),
                        $tblStudentEducationOld->getTblCoreGroup() ?: null,
                        $tblStudentEducationOld->getCoreGroupSortOrder(),
                        $leaveDate
                    );
                }

                $tblStudentEducationNew = new TblStudentEducation();
                $tblStudentEducationNew->setTblCoreGroup(DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']) ?: null);
                $tblStudentEducationNew->setTblDivision($tblDivisionCourseDivisionNew ?: null);
                $tblStudentEducationNew->setDivisionSortOrder($divisionSortOrderNew);
            }

            $tblStudentEducationNew->setServiceTblPerson($tblPerson);
            $tblStudentEducationNew->setServiceTblYear($tblYear);
            $tblStudentEducationNew->setLevel($Data['Level']);
            $tblStudentEducationNew->setServiceTblSchoolType($tblSchoolType);
            $tblStudentEducationNew->setServiceTblCompany($tblCompany);
            $tblStudentEducationNew->setServiceTblCourse(Course::useService()->getCourseById($Data['Course']) ?: null);

            if ((new Data($this->getBinding()))->createStudentEducation($tblStudentEducationNew)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param $Data
     *
     * @return bool
     */
    public function updateStudentEducation(TblStudentEducation $tblStudentEducation, $Data): bool
    {
        $tblStudentEducation->setServiceTblSchoolType(Type::useService()->getTypeById($Data['SchoolType']) ?: null);
        $tblStudentEducation->setServiceTblCompany(Company::useService()->getCompanyById($Data['Company']) ?: null);
        $tblStudentEducation->setLevel($Data['Level']);
        $tblStudentEducation->setServiceTblCourse(Course::useService()->getCourseById($Data['Course']) ?: null);

        return (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
    }

    /**
     * @param array $tblStudentEducationList
     *
     * @return bool
     */
    public function updateStudentEducationBulk(array $tblStudentEducationList): bool
    {
        return (new Data($this->getBinding()))->updateStudentEducationBulk($tblStudentEducationList);
    }

    /**
     * @param $Data
     * @param TblPerson $tblPerson
     *
     * @return false|Form
     */
    public function checkFormCreateStudentEducation($Data, TblPerson $tblPerson)
    {
        $form = DivisionCourse::useFrontend()->formCreateStudentEducation($tblPerson);

        $error = $this->checkStudentEducationData($Data, $form);
        if (!isset($Data['Year']) || !(Company::useService()->getCompanyById($Data['Year']))) {
            $form->setError('Data[Year]', 'Bitte wählen Sie ein Schuljahr aus');
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblPerson $tblPerson
     *
     * @return false|TblStudentEducation
     */
    public function createStudentEducation($Data, TblPerson $tblPerson)
    {
        $tblStudentEducation = new TblStudentEducation();
        $tblStudentEducation->setServiceTblPerson($tblPerson);
        if (($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $tblStudentEducation->setServiceTblYear($tblYear);
            $tblStudentEducation->setServiceTblSchoolType(Type::useService()->getTypeById($Data['SchoolType']) ?: null);
            $tblStudentEducation->setServiceTblCompany(Company::useService()->getCompanyById($Data['Company']) ?: null);
            $tblStudentEducation->setLevel($Data['Level']);
            $tblStudentEducation->setServiceTblCourse(Course::useService()->getCourseById($Data['Course']) ?: null);

            return (new Data($this->getBinding()))->createStudentEducation($tblStudentEducation);
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $separator
     *
     * @return string
     */
    public function getDivisionTeacherNameListString(TblDivisionCourse $tblDivisionCourse, string $separator = '<br/>'): string
    {
        $resultList = array();
        if (($tblTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))) {
            foreach ($tblTeacherList as $tblPersonTeacher) {
                if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonTeacher))
                    && ($acronym = $tblTeacher->getAcronym())
                ) {
                    $name = $tblPersonTeacher->getLastName() . ' (' . $acronym . ')';
                } else {
                    $name = $tblPersonTeacher->getLastName();
                }
                $resultList[] = $name;
            }
        }

        return empty($resultList) ? '' : implode($separator, $resultList);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return int
     */
    public function getCountStudentsByYear(TblYear $tblYear): int
    {
        if (($tblStudentEducationList = $this->getStudentEducationListBy($tblYear))) {
            return count($tblStudentEducationList);
        }

        return 0;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function getCountStudentsDetailsByYear(TblYear $tblYear): string
    {
        $countSchoolTypeList = array();
        $countTotal = 0;
        $missingStudentGroup = array();
        $tblGroupStudent = GroupPerson::useService()->getGroupByMetaTable('STUDENT');
        if (($tblStudentEducationList = $this->getStudentEducationListBy($tblYear))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    $countTotal++;
                    $schoolTypeId = ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType()) ? $tblSchoolType->getId() : 0;
                    $companyId = ($tblCompany = $tblStudentEducation->getServiceTblCompany()) ? $tblCompany->getId() : 0;
                    if (isset($countSchoolTypeList[$schoolTypeId][$companyId])) {
                        $countSchoolTypeList[$schoolTypeId][$companyId]++;
                    } else {
                        $countSchoolTypeList[$schoolTypeId][$companyId] = 1;
                    }

                    if (!GroupPerson::useService()->existsGroupPerson($tblGroupStudent, $tblPerson)) {
                        $missingStudentGroup[$tblPerson->getId()] = $tblPerson->getLastFirstName();
                    }
                }
            }
        }

        $panelList = array();
        foreach ($countSchoolTypeList as $schoolTypeId => $companyList) {
            if (($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))) {
                $nameSchoolType = $tblSchoolType->getName();
            } else {
                $nameSchoolType = 'Keine Schulart';
            }

            $countSchoolType = 0;
            $content = array();
            foreach ($companyList as $companyId => $value) {
                $countSchoolType += $value;
                if (($tblCompany = Company::useService()->getCompanyById($companyId))) {
                    $nameCompany = $tblCompany->getDisplayName();
                } else {
                    $nameCompany = new Warning('Keine Schule');
                }
                $content[] = $nameCompany . new PullRight(new Muted($value . ' Schüler'));
            }

            $panelList[$schoolTypeId] = new Panel(
                $nameSchoolType . new PullRight(new Muted($countSchoolType . ' Schüler')),
                $content,
                $nameSchoolType == 'Keine Schulart' ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_INFO
            );
        }

        asort($panelList);
        asort($missingStudentGroup);

        return new Title(new Calendar() . ' Schuljahresübersicht für: ' . new Bold($tblYear->getDisplayName()) . new PullRight(new Muted($countTotal . ' Schüler')))
            . implode('<br/>', $panelList)
            . (empty($missingStudentGroup) ? '' : new Panel(
                'Personen, welche nicht mehr in der Personengruppe Schüler sind:',
                $missingStudentGroup,
                Panel::PANEL_TYPE_WARNING
            ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $isString
     *
     * @return Type[]|string|false
     */
    public function getSchoolTypeListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, bool $isString = false)
    {
        switch ($tblDivisionCourse->getTypeIdentifier()) {
            case TblDivisionCourseType::TYPE_DIVISION:
                $schoolTypeIdList = (new Data($this->getBinding()))->getSchoolTypeIdListByTypeDivision($tblDivisionCourse);
                break;
            case TblDivisionCourseType::TYPE_CORE_GROUP:
                $schoolTypeIdList = (new Data($this->getBinding()))->getSchoolTypeIdListByTypeCoreGroup($tblDivisionCourse);
                break;
            case TblDivisionCourseType::TYPE_ADVANCED_COURSE:
            case TblDivisionCourseType::TYPE_BASIC_COURSE:
                $schoolTypeIdList = (new Data($this->getBinding()))->getSchoolTypeIdListByStudentSubject($tblDivisionCourse);
                break;
            default:
                $schoolTypeIdList = (new Data($this->getBinding()))->getSchoolTypeIdListByDivisionCourseWithMember($tblDivisionCourse);
        }

        $resultList = array();
        if ($schoolTypeIdList) {
            foreach ($schoolTypeIdList as $item) {
                if (isset($item['SchoolTypeId']) && ($tblSchoolType = Type::useService()->getTypeById($item['SchoolTypeId']))) {
                    if ($isString) {
                        $resultList[$tblSchoolType->getId()] = $tblSchoolType->getShortName() ?: $tblSchoolType->getName();
                    } else {
                        $resultList[$tblSchoolType->getId()] = $tblSchoolType;
                    }
                }
            }
        }

        return empty($resultList)
            ? false
            : ($isString ? implode(", ", $resultList) : $resultList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $isString
     *
     * @return Type[]|string|false
     */
    public function getCompanyListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, bool $isString = false)
    {
        switch ($tblDivisionCourse->getTypeIdentifier()) {
            case TblDivisionCourseType::TYPE_DIVISION:
                $companyIdList = (new Data($this->getBinding()))->getCompanyIdListByTypeDivision($tblDivisionCourse);
                break;
            case TblDivisionCourseType::TYPE_CORE_GROUP:
                $companyIdList = (new Data($this->getBinding()))->getCompanyIdListByTypeCoreGroup($tblDivisionCourse);
                break;
            case TblDivisionCourseType::TYPE_ADVANCED_COURSE:
            case TblDivisionCourseType::TYPE_BASIC_COURSE:
                $companyIdList = (new Data($this->getBinding()))->getCompanyIdListByStudentSubject($tblDivisionCourse);
                break;
            default:
                $companyIdList = (new Data($this->getBinding()))->getCompanyIdListByDivisionCourseWithMember($tblDivisionCourse);
        }

        $resultList = array();
        if ($companyIdList) {
            foreach ($companyIdList as $item) {
                if (isset($item['CompanyId']) && ($tblCompany = Company::useService()->getCompanyById($item['CompanyId']))) {
                    if ($isString) {
                        $resultList[$tblCompany->getId()] = $tblCompany->getName();
                    } else {
                        $resultList[$tblCompany->getId()] = $tblCompany;
                    }
                }
            }
        }

        return empty($resultList)
            ? false
            : ($isString ? implode(", ", $resultList) : $resultList);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByStudentAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        $tblDivisionCourseList = array();
        if (($tblStudentEducation = $this->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
            if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                $tblDivisionCourseList[$tblDivision->getId()] = $tblDivision;
            }
            if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                $tblDivisionCourseList[$tblCoreGroup->getId()] = $tblCoreGroup;
            }
        }
        if (($tblDivisionCourseMemberList = $this->getDivisionCourseMemberListByPersonAndYearAndMemberType(
            $tblPerson, $tblYear, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT)
        ))) {
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                if (($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())) {
                    $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                }
            }
        }

        return empty($tblDivisionCourseList) ? false : $tblDivisionCourseList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByStudentsInDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $tblDivisionCourseList = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tempList = $this->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear))) {
                    foreach ($tempList as $item) {
                        if (!isset($tblDivisionCourseList[$item->getId()])) {
                            $tblDivisionCourseList[$item->getId()] = $item;
                        }
                    }
                }
            }
        }

        return empty($tblDivisionCourseList) ? false : $tblDivisionCourseList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function getHasSaturdayLessonsByDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if (Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removePerson(TblPerson $tblPerson, bool $IsSoftRemove)
    {
        (new Data($this->getBinding()))->removePerson($tblPerson, $IsSoftRemove);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function restorePerson(TblPerson $tblPerson)
    {
        (new Data($this->getBinding()))->restorePerson($tblPerson);
    }
}