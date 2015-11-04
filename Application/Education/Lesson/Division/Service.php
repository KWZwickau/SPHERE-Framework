<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\Education\Lesson\Division\Service\Data;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Setup;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return (new Data($this->getBinding()))->getLevelAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return (new Data($this->getBinding()))->getLevelById($Id);
    }

    /**
     * @return bool|TblDivision[]
     */
    public function getDivisionAll()
    {

        return (new Data($this->getBinding()))->getDivisionAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivision
     */
    public function getDivisionById($Id)
    {

        return (new Data($this->getBinding()))->getDivisionById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param null|array     $Level
     *
     * @return IFormInterface|string
     */
    public function createLevel(
        IFormInterface $Form,
        $Level
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Level) {
            return $Form;
        }

        $Error = false;

        $tblType = Type::useService()->getTypeById($Level['Type']);

        if (isset( $Level['Name'] ) && empty( $Level['Name'] )) {
            $Form->setError('Level[Name]', 'Bitte geben Sie einen eineindeutigen Namen in Bezug auf die Schulart an');
            $Error = true;
        } else {
            if ($this->checkLevelExists($tblType, $Level['Name'])) {
                $Form->setError('Level[Name]', 'Dieser Name wird bereits verwendet');
                $Error = true;
            }
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->createLevel(
                $tblType, $Level['Name'], $Level['Description']
            )
            ) {
                return new Success('Die Klassenstufe wurde erfolgreich hinzugefügt')
                .new Redirect($this->getRequest()->getUrl(), 1);
            } else {
                return new Danger('Die Klassenstufe konnte nicht hinzugefügt werden')
                .new Redirect($this->getRequest()->getUrl());
            }
        }
        return $Form;
    }

    /**
     * @param TblType $tblType
     * @param string  $Name
     *
     * @return bool
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        return (new Data($this->getBinding()))->checkLevelExists($tblType, $Name);
    }

    /**
     * @param TblDivision $tblDivision
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getStudentAllByDivision($tblDivision);
    }
}
