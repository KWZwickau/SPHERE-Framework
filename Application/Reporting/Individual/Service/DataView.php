<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class DataView
 *
 * @package SPHERE\Application\Reporting\Individual\Service
 */
class DataView extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /** @return false|array */
//    public function getViewEducationStudentAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewEducationStudent');}
//    public function getViewGroupAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroup');}
//    public function getViewGroupClubAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupClub');}
//    public function getViewGroupCustodyAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupCustody');}
//    public function getViewGroupProspectAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupProspect');}
//    public function getViewGroupStudentBasicAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentBasic');}
//    public function getViewGroupStudentIntegrationAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentIntegration');}
//    public function getViewGroupStudentSubjectAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentSubject');}
//    public function getViewGroupStudentTransferAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupStudentTransfer');}
//    public function getViewGroupTeacherAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewGroupTeacher');}
//    public function getViewPersonAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewPerson');}
    //ToDO Funktion könnte für Adressen hinzufügen/bearbeiten verwendet werden. (Key's anpassen!)
    public function getViewContactAddressAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewContactAddress');}

    /**
     *  schnelle gruppierte Liste nach Städtenamen
     * @return false|array
     */
    public function getCityNameGroupByCityName()
    {
        $queryBuilder = $this->getConnection()->getEntityManager()->getQueryBuilder();

        $queryBuilder->select('vCA.TblCity_Name')
            ->from(__NAMESPACE__ . '\Entity\ViewContactAddress', 'vCA')
            ->groupBy('vCA.TblCity_Name');

        $query = $queryBuilder->getQuery();
        $resultList = $query->getResult();
        $result = array();
        if(!empty($resultList)){
            foreach($resultList as $resultSingle){
                $result[] = $resultSingle['TblCity_Name'];
            }
        }

        return (!empty($result) ? $result : false);
    }

//    public function getViewProspectCustodyAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewProspectCustody');}
//    public function getViewStudentAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudent');}
//    public function getViewStudentAuthorizedAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentAuthorized');}
//    public function getViewStudentCustodyAll(){return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudentCustody');}
}
