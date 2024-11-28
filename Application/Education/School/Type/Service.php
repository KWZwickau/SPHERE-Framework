<?php
namespace SPHERE\Application\Education\School\Type;

use SPHERE\Application\Education\School\Type\Service\Data;
use SPHERE\Application\Education\School\Type\Service\Entity\TblCategory;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\School\Type
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
     * liefert alle Standard-Schularten
     *
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {
        return (new Data($this->getBinding()))->getTypeBasicAll();
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|TblType[]
     */
    public function getTypeAllByCategory(TblCategory $tblCategory)
    {
        return (new Data($this->getBinding()))->getTypeAllByCategory($tblCategory);
    }

    /**
     * @param $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTypeById($Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getTypeByName($Name);
    }

    /**
     * @param string $ShortName
     *
     * @return bool|TblType
     */
    public function getTypeByShortName($ShortName)
    {
        return (new Data($this->getBinding()))->getTypeByShortName($ShortName);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCategory
     */
    public function getCategoryById($Id)
    {
        return (new Data($this->getBinding()))->getCategoryById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblCategory
     */
    public function getCategoryByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getCategoryByIdentifier($Identifier);
    }

    /**
     * @param TblType $tblType
     *
     * @return false|int
     */
    public function getMaxLevelByType(TblType $tblType)
    {
        switch ($tblType->getShortName()) {
            case 'GS': return GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN) ? 6 : 4;
            case 'ISS':
            case 'RS':
            case 'OS': return 10;
            case 'GMS':
            case 'Gy': return 12;

            case 'BGy': return 13;
            case 'FöS':
            case 'FOS': return 12;
            case 'BFS': return 3;
            case 'BGJ':
            case 'BVJ':
            case 'VKlbA': return 1;

            default: return 4;
        }
    }

    /**
     * @param TblType $tblType
     *
     * @return false|int
     */
    public function getMinLevelByType(TblType $tblType)
    {
        switch ($tblType->getShortName()) {
            case 'ISS':
            case 'RS':
            case 'OS':
            case 'Gy': return 5;

            case 'BGy':
            case 'FOS': return 11;
            default: return 1;
        }
    }
}
