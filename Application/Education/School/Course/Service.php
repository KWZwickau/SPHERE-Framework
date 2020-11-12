<?php
namespace SPHERE\Application\Education\School\Course;

use SPHERE\Application\Education\School\Course\Service\Data;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Setup;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\School\Course
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
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
     * @return bool|TblCourse[]
     */
    public function getCourseAll()
    {

        return (new Data($this->getBinding()))->getCourseAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCourse
     */
    public function getCourseById($Id)
    {

        return (new Data($this->getBinding()))->getCourseById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCourse
     */
    public function getCourseByName($Name)
    {

        return (new Data($this->getBinding()))->getCourseByName($Name);
    }

    /**
     * @param $Id
     *
     * @return bool|TblSchoolDiploma
     */
    public function getSchoolDiplomaById($Id)
    {
        return (new Data($this->getBinding()))->getSchoolDiplomaById($Id);
    }

    /**
     * @return bool|TblSchoolDiploma[]
     */
    public function getSchoolDiplomaAll()
    {
        return (new Data($this->getBinding()))->getSchoolDiplomaAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalDiploma
     */
    public function getTechnicalDiplomaById($Id)
    {
        return (new Data($this->getBinding()))->getTechnicalDiplomaById($Id);
    }

    /**
     * @return bool|TblTechnicalDiploma[]
     */
    public function getTechnicalDiplomaAll()
    {
        return (new Data($this->getBinding()))->getTechnicalDiplomaAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblTechnicalCourse
     */
    public function getTechnicalCourseById($Id)
    {
        return (new Data($this->getBinding()))->getTechnicalCourseById($Id);
    }

    /**
     * @return bool|TblTechnicalCourse[]
     */
    public function getTechnicalCourseAll()
    {
        return (new Data($this->getBinding()))->getTechnicalCourseAll();
    }

    /**
     * @param $Data
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     * @return false|Form
     */
    public function checkFormTechnicalCourse(
        $Data,
        TblTechnicalCourse $tblTechnicalCourse = null
    ) {
        $error = false;

        $form = Course::useFrontend()->formTechnicalCourse($tblTechnicalCourse ? $tblTechnicalCourse->getId() : null);
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        } else {
            $form->setSuccess('Data[Name]');
        }

        return $error ? $form : false;
    }
    
    /**
     * @param $Name
     * @param $GenderMaleName
     * @param $GenderFemaleName
     *
     * @return TblTechnicalCourse
     */
    public function createTechnicalCourse($Name, $GenderMaleName, $GenderFemaleName)
    {
        return (new Data($this->getBinding()))->createTechnicalCourse($Name, $GenderMaleName, $GenderFemaleName);
    }

    /**
     * @param TblTechnicalCourse $tblTechnicalCourse
     * @param $Name
     * @param $GenderMaleName
     * @param $GenderFemaleName
     *
     * @return bool
     */
    public function updateTechnicalCourse(
        TblTechnicalCourse $tblTechnicalCourse,
        $Name,
        $GenderMaleName,
        $GenderFemaleName
    ) {
        return (new Data($this->getBinding()))->updateTechnicalCourse($tblTechnicalCourse, $Name, $GenderMaleName, $GenderFemaleName);
    }
}
