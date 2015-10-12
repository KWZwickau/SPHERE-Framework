<?php

namespace SPHERE\Application\Education\Graduation\ScoreType;

use SPHERE\Application\Education\Graduation\ScoreType\Service\Data;
use SPHERE\Application\Education\Graduation\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\ScoreType\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Graduation\Score\ScoreType
 */
class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblScoreType
     */
    public function getScoreTypeById($Id)
    {

        return (new Data($this->getBinding()))->getScoreTypeById($Id);
    }

    /**
     * @param $Name
     * @param $Short
     *
     * @return null|object|TblScoreType
     */
    public function setTblScoreType($Name, $Short)
    {

        return (new Data($this->getBinding()))->createScoreType($Name, $Short);
    }

    /**
     * @return bool|Service\Entity\TblScoreType[]
     */
    public function getScoreTypeAll()
    {

        return (new Data($this->getBinding()))->getScoreTypeAll();
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return string
     */
    public function removeScoreType(TblScoreType $tblScoreType)
    {

        if (null === $tblScoreType) {
            return '';
        }

        if ((new Data($this->getBinding()))->removeScoreTypeByEntity($tblScoreType)) {
            return new Success('Der Zensurentyp wurde erfolgreich gelöscht')
            .new Redirect('/Education/Graduation/ScoreType', 0);
        } else {
            return new Danger('Der Zensurentyp konnte nicht gelöscht werden')
            .new Redirect('/Education/Graduation/ScoreType', 2);
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param                $ScoreType
     *
     * @return IFormInterface|string
     */
    public function setScoreType(IFormInterface &$Stage = null, $ScoreType)
    {

        /**
         * Skip to Frontend
         */
        if (null === $ScoreType) {
            return $Stage;
        }
        $Error = false;
        if (isset( $ScoreType['Name'] ) && empty( $ScoreType['Name'] )) {
            $Stage->setError('ScoreType[Name]', 'Bitte geben sie einen Zenzurentypnamen an');
            $Error = true;
        }
        if (isset( $ScoreType['Short'] ) && empty( $ScoreType['Short'] )) {
            $Stage->setError('ScoreType[Short]', 'Bitte geben sie eine Abkürzung an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreType($ScoreType['Name'], $ScoreType['Short']);
            return new Stage('Das Konto ist erfasst worden')
            .new Redirect('/Education/Graduation/ScoreType', 0);
        }

        return $Stage;
    }

}
