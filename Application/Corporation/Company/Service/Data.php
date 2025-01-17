<?php
namespace SPHERE\Application\Corporation\Company\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\ColumnHydrator;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Company\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany', $Id);
    }

    /**
     * @param $Name
     * @param $ExtendedName
     *
     * @return false|TblCompany
     */
    public function getCompanyByName($Name, $ExtendedName)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany',
            array(
                TblCompany::ATTR_NAME          => $Name,
                TblCompany::ATTR_EXTENDED_NAME => $ExtendedName
            ));
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany');
    }

    /**
     * @param $Name
     *
     * @return false|TblCompany[]
     */
    public function getCompanyListLike($Name)
    {
        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $split = explode(' ', $Name);

        $and = $queryBuilder->expr()->andX();
        $count = 0;
        foreach ($split as $item) {
            $count++;
            $or = $queryBuilder->expr()->orX();
            $or->add($queryBuilder->expr()->like('t.Name', '?' . $count));
            $or->add($queryBuilder->expr()->like('t.ExtendedName', '?' . $count));
            $or->add($queryBuilder->expr()->like('t.Description', '?' . $count));

            $and->add($or);

            $queryBuilder->setParameter($count, '%' . $item . '%');
        }

        $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblCompany', 't')
            ->where($and);

        $query = $queryBuilder->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * @param integer $ImportId
     *
     * @return bool|TblCompany
     */
    public function getCompanyByImportId($ImportId)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany',
            array(
                TblCompany::ATTR_IMPORT_ID => $ImportId
            ));
    }

    /**
     * @param integer $ImportId
     *
     * @return bool|TblCompany[]
     */
    public function getCompanyListByImportId($ImportId)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany',
            array(
                TblCompany::ATTR_IMPORT_ID => $ImportId
            ));
    }

    /**
     * @param array $FilterList
     *
     * @return
     */
    public function fetchIpCompanyByFilter(array $FilterList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $Group = new TblCompany();
        $query = $queryBuilder->select('tC.Id as CompanyId')
            ->from($Group->getEntityFullName(), 'tC')
            ->where($queryBuilder->expr()->isNull('tC.EntityRemove'));
        $ParameterCount = 1;
        foreach($FilterList as $FilterName => $FilterValue){
            if($FilterValue != null){
                if($FilterName == 'TblCompany_Name'){
                    $query->andWhere($queryBuilder->expr()->eq('tC.Name', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
                if($FilterName == 'TblCompany_ExtendedName'){
                    $query->andWhere($queryBuilder->expr()->eq('tC.ExtendedName', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
            }
        }
        $query = $query->getQuery();
        $result = array();
        if(($tempList = $query->getResult(ColumnHydrator::HYDRATION_MODE))){
            foreach($tempList as $temp){
                $result[$temp] = $temp;
            }
        }
        return $result;
    }

    /**
     * @param string $Name
     * @param string $ExtendedName
     * @param string $Description
     * @param string $ImportId
     * @param string $ContactNumber
     *
     * @return null|object|TblCompany
     */
    public function createCompany($Name, $ExtendedName = '', $Description = '', $ImportId = '', $ContactNumber = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCompany')->findOneBy(array(
            TblCompany::ATTR_NAME          => $Name,
            TblCompany::ATTR_EXTENDED_NAME => $ExtendedName,
            'EntityRemove'                 => null
        ));
        if (null === $Entity) {
            $Entity = new TblCompany();
            $Entity->setName($Name);
            $Entity->setExtendedName($ExtendedName);
            $Entity->setDescription($Description);
            $Entity->setImportId($ImportId);
            $Entity->setContactNumber($ContactNumber);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCompany $tblCompany
     * @param string     $Name
     * @param string     $ExtendedName
     * @param string     $Description
     *
     * @return bool
     */
    public function updateCompany(
        TblCompany $tblCompany,
        $Name,
        $ExtendedName = '',
        $Description = '',
        $ContactNumber = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setExtendedName($ExtendedName);
            $Entity->setDescription($Description);
            $Entity->setContactNumber($ContactNumber);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $ImportId
     *
     * @return bool
     */
    public function updateCompanyImportId(
        TblCompany $tblCompany,
        $ImportId
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setImportId($ImportId);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Description
     *
     * @return bool
     */
    public function updateCompanyDescriptionWithoutForm(TblCompany $tblCompany, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param array   $ProcessList
     *
     * @return bool
     */
    public function updateCompanyAnonymousBulk($ProcessList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if(!empty($ProcessList)){
            foreach($ProcessList as $Company){
                /** @var TblCompany $tblCompany */
                $tblCompany = $Company['tblCompany'];
                $Name = $Company['Name'];
                /** @var TblCompany $Entity */
                $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
                //                $Protocol = clone $Entity;
                if (null !== $Entity) {
                    $Entity->setName($Name);
                    $Entity->setDescription('');
                    $Entity->setExtendedName('');
                    $Manager->bulkSaveEntity($Entity);
                    // no Protocol necessary
                    //                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                    //                    $Protocol,
                    //                    $Entity);
                }
            }
            $Manager->flushCache();
            return true;
        }
        return false;
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function removeCompany(TblCompany $tblCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }
}
