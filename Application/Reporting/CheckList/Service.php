<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:33
 */

namespace SPHERE\Application\Reporting\CheckList;

use SPHERE\Application\Reporting\CheckList\Service\Data;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblElementType;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListElementList;
use SPHERE\Application\Reporting\CheckList\Service\Entity\TblListType;
use SPHERE\Application\Reporting\CheckList\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Reporting\CheckList
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
     * @param $Id
     *
     * @return bool|TblList
     */
    public function getListById($Id)
    {

        return (new Data($this->getBinding()))->getListById($Id);
    }

    /**
     * @return bool|TblList[]
     */
    public function getListAll()
    {

        return (new Data($this->getBinding()))->getListAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblListType
     */
    public function getListTypeById($Id)
    {

        return (new Data($this->getBinding()))->getListTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblListType
     */
    public function getListTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getListTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblListType[]
     */
    public function getListTypeAll()
    {

        return (new Data($this->getBinding()))->getListTypeAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblListElementList
     */
    public function getListElementListById($Id)
    {

        return (new Data($this->getBinding()))->getListElementListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblElementType
     */
    public function getElementTypeById($Id)
    {

        return (new Data($this->getBinding()))->getElementTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblElementType
     */
    public function getElementTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getElementTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblElementType[]
     */
    public function getElementTypeAll()
    {

        return (new Data($this->getBinding()))->getElementTypeAll();
    }

    /**
     * @param IFormInterface|null $Stage
     * @param $List
     *
     * @return IFormInterface|string
     */
    public function createList(IFormInterface $Stage = null, $List)
    {

        /**
         * Skip to Frontend
         */
        if (null === $List) {
            return $Stage;
        }

        $Error = false;
        if (isset($List['Name']) && empty($List['Name'])) {
            $Stage->setError('List[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createList(
                $this->getListTypeById($List['Type']),
                $List['Name'],
                $List['Description']
            );
            return new Stage('Die Check-Liste ist erfasst worden')
            . new Redirect('/Reporting/CheckList', 0);
        }

        return $Stage;
    }
}