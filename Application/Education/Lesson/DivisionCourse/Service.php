<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Setup;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

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
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseById($Id);
    }

    /**
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseAll(?string $TypeIdentifier = '')
    {
        return (new Data($this->getBinding()))->getDivisionCourseAll($TypeIdentifier);
    }

    /**
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListBy(TblYear $tblYear = null, ?string $TypeIdentifier = '')
    {
        return (new Data($this->getBinding()))->getDivisionCourseListBy($tblYear, $TypeIdentifier);
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
    public function getDivisionCourseTypeAll()
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getMemberTypeById($Id)
    {
        return (new Data($this->getBinding()))->getMemberTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getMemberTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getMemberTypeByIdentifier($Identifier);
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
        $form = DivisionCourse::useFrontend()->formDivisionCourse($tblDivisionCourse ? $tblDivisionCourse->getId() : null, $Filter);

        $tblYear = false;
        $tblType = false;
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

        if (!isset($Data['Name']) || empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name ein');
            $error = true;
        }
        if (isset($Data['Name']) && $Data['Name'] != '') {
            // Name Zeicheneingrenzung für Klassen und Stammgruppen, falls diese an angeschlossene Systeme übertragen werden müssen
            if ($tblType && ($tblType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)) {
                if (!preg_match('!^[\w\-,\/ ]+$!', $Data['Name'])) {
                    $form->setError('Data[Name]', 'Erlaubte Zeichen [a-zA-Z0-9, -_/]');
                    $error = true;
                }
            }
            // Prüfung ob name schon mal verwendet wird
            if ($tblYear && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                    if ($tblDivisionCourse && $tblDivisionCourse->getId() == $tblDivisionCourseItem->getId()) {
                        continue;
                    }

                    if ($Data['Name'] == $tblDivisionCourseItem->getName()) {
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
            return (new Data($this->getBinding()))->createDivisionCourse($tblType, $tblYear, $Data['Name'], $Data['Description'],
                isset($Data['IsShownInPersonData']), isset($Data['IsReporting']), isset($Data['IsUcs']));
        } else {
            return false;
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $Data
     *
     * @return bool
     */
    public function updateDivisionCourse(TblDivisionCourse $tblDivisionCourse, array $Data): bool
    {
        return (new Data($this->getBinding()))->updateDivisionCourse($tblDivisionCourse, $Data['Name'], $Data['Description'],
            isset($Data['IsShownInPersonData']), isset($Data['IsReporting']), isset($Data['IsUcs']));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function destroyDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        // todo Mitglieder und Co löschen

        return (new Data($this->getBinding()))->destroyDivisionCourse($tblDivisionCourse);
    }
}