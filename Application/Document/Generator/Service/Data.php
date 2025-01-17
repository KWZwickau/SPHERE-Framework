<?php

namespace SPHERE\Application\Document\Generator\Service;

use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocumentSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Document\Standard\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        // Schülerkartei - Grundschule
        if (($tblSchoolType = Type::useService()->getTypeByName('Grundschule'))) {
            $tblDocument = $this->createDocument('Schülerkartei - Grundschule',
                'Standard\Repository\StudentCard\PrimarySchool', $tblSchoolType);
            if ($tblDocument && !$this->getDocumentSubjectListByDocument($tblDocument)) {
                $i = 3;
                $this->setDocumentSubject($tblDocument, 'DE', $i++);
                $this->setDocumentSubject($tblDocument, 'SU', $i++);
                $this->setDocumentSubject($tblDocument, 'KU', $i++);
                $this->setDocumentSubject($tblDocument, 'MU', $i++);
                $this->setDocumentSubject($tblDocument, 'EN', $i++);
                $this->setDocumentSubject($tblDocument, 'MA', $i++);
                $this->setDocumentSubject($tblDocument, 'WE', $i++);
                $this->setDocumentSubject($tblDocument, 'REV', $i++);
                $this->setDocumentSubject($tblDocument, 'SPO', $i);
            }
        }

        // Schülerkartei - Gymnasium
        if (($tblSchoolType = Type::useService()->getTypeByName('Gymnasium'))) {
            $tblDocument = $this->createDocument('Schülerkartei - Gymnasium',
                'Standard\Repository\StudentCard\GrammarSchool', $tblSchoolType);
            if ($tblDocument && !$this->getDocumentSubjectListByDocument($tblDocument)) {
                $i = 1;
                $this->setDocumentSubject($tblDocument, 'DE', $i++);
                $this->setDocumentSubject($tblDocument, 'EN', $i++);
                $this->setDocumentSubject($tblDocument, 'FRZ', $i++, false);
                $this->setDocumentSubject($tblDocument, 'KU', $i++);
                $this->setDocumentSubject($tblDocument, 'MU', $i++);
                $this->setDocumentSubject($tblDocument, 'GE', $i++);
                $this->setDocumentSubject($tblDocument, 'GRW', $i++, false);
                $this->setDocumentSubject($tblDocument, 'GEO', $i++);
                $this->setDocumentSubject($tblDocument, 'MA', $i++);
                $this->setDocumentSubject($tblDocument, 'BIO', $i++);
                $this->setDocumentSubject($tblDocument, 'CH', $i++);
                $this->setDocumentSubject($tblDocument, 'PH', $i++);
                $this->setDocumentSubject($tblDocument, 'TC', $i++, false);
                $this->setDocumentSubject($tblDocument, 'IN', $i++);
                $this->setDocumentSubject($tblDocument, 'REV', $i++, false);
                $this->setDocumentSubject($tblDocument, 'SPO', $i++);
                $this->setDocumentSubject($tblDocument, 'PRO', $i, false);
            }
        }

        // Schülerkartei - Oberschule
        if (($tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE))) {
            $tblDocument = $this->createDocument('Schülerkartei - ' . TblType::IDENT_OBER_SCHULE,
                'Standard\Repository\StudentCard\SecondarySchool', $tblSchoolType);
            if ($tblDocument && !$this->getDocumentSubjectListByDocument($tblDocument)) {
                $i = 1;
                $this->setDocumentSubject($tblDocument, 'DE', $i++);
                $this->setDocumentSubject($tblDocument, 'EN', $i++);
                $this->setDocumentSubject($tblDocument, 'FRZ', $i++, false);
                $this->setDocumentSubject($tblDocument, 'KU', $i++);
                $this->setDocumentSubject($tblDocument, 'MU', $i++);
                $this->setDocumentSubject($tblDocument, 'GE', $i++);
                $this->setDocumentSubject($tblDocument, 'GR', $i++);
                $this->setDocumentSubject($tblDocument, 'GEO', $i++);
                $i++;
                $this->setDocumentSubject($tblDocument, 'MA', $i++);
                $this->setDocumentSubject($tblDocument, 'BIO', $i++);
                $this->setDocumentSubject($tblDocument, 'CH', $i++);
                $this->setDocumentSubject($tblDocument, 'PH', $i++);
                $this->setDocumentSubject($tblDocument, 'REV', $i++, false);
                $this->setDocumentSubject($tblDocument, 'SPO', $i++);
                $this->setDocumentSubject($tblDocument, 'TC', $i++);
                $this->setDocumentSubject($tblDocument, 'IN', $i++);
                $this->setDocumentSubject($tblDocument, 'WTH', $i++);
                $this->setDocumentSubject($tblDocument, 'NK', $i, false);
            }
        }
    }

    /**
     * @param $id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDocument', $id);
    }

    /**
     * @return false|TblDocument[]
     */
    public function getDocumentAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDocument');
    }

    /**
     * @param string $name
     *
     * @return false|TblDocument
     */
    public function getDocumentByName($name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDocument',
            array(TblDocument::ATTR_NAME => $name)
        );
    }

    /**
     * @param string $documentClass
     *
     * @return false|TblDocument
     */
    public function getDocumentByClass($documentClass)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDocument',
            array(TblDocument::ATTR_DOCUMENT_CLASS => $documentClass)
        );
    }

    /**
     * @param TblDocument $tblDocument
     * @return false|TblDocumentSubject[]
     */
    public function getDocumentSubjectListByDocument(TblDocument $tblDocument)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDocumentSubject',
            array(TblDocumentSubject::ATTR_TBL_DOCUMENT => $tblDocument->getId())
        );
    }

    /**
     * @param TblDocument $tblDocument
     * @param $ranking
     *
     * @return false|TblDocumentSubject
     */
    public function getDocumentSubjectByDocumentAndRanking(TblDocument $tblDocument, $ranking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDocumentSubject',
            array(
                TblDocumentSubject::ATTR_TBL_DOCUMENT => $tblDocument->getId(),
                TblDocumentSubject::ATTR_RANKING => $ranking
            )
        );
    }

    /**
     * @param string $name
     * @param string $documentClass
     * @param TblType $tblSchoolType
     *
     * @return TblDocument
     */
    public function createDocument(
        $name,
        $documentClass,
        TblType $tblSchoolType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblDocument')->findOneBy(array(
            TblDocument::ATTR_NAME => $name
        ));

        if (null === $Entity) {
            $Entity = new TblDocument();
            $Entity->setName($name);
            $Entity->setDocumentClass($documentClass);
            $Entity->setServiceTblSchoolType($tblSchoolType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDocument $tblDocument
     * @param $ranking
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     *
     * @return TblDocumentSubject
     */
    public function createDocumentSubject(
        TblDocument $tblDocument,
        $ranking,
        TblSubject $tblSubject,
        $IsEssential = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDocumentSubject')->findOneBy(array(
            TblDocumentSubject::ATTR_TBL_DOCUMENT => $tblDocument->getId(),
            TblDocumentSubject::ATTR_RANKING => $ranking
        ));
        if (null === $Entity) {
            $Entity = new TblDocumentSubject();
            $Entity->setTblDocument($tblDocument);
            $Entity->setRanking($ranking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setEssential($IsEssential);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDocumentSubject $tblDocumentSubject
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     *
     * @return bool
     */
    public function updateDocumentSubject(
        TblDocumentSubject $tblDocumentSubject,
        TblSubject $tblSubject,
        $IsEssential = false
    ) {

        $Manager = $this->getEntityManager();

        /** @var TblDocumentSubject $Entity */
        $Entity = $Manager->getEntityById('TblDocumentSubject', $tblDocumentSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setEssential($IsEssential);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDocumentSubject $tblDocumentSubject
     *
     * @return bool
     */
    public function destroyDocumentSubject(TblDocumentSubject $tblDocumentSubject)
    {

        $Manager = $this->getEntityManager();

        /** @var TblDocumentSubject $Entity */
        $Entity = $Manager->getEntityById('TblDocumentSubject', $tblDocumentSubject->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDocument $tblDocument
     * @param string $subjectAcronym
     * @param integer $ranking
     *
     * @param bool $IsEssential
     */
    private function setDocumentSubject(
        TblDocument $tblDocument,
        $subjectAcronym,
        $ranking,
        $IsEssential = true
    ) {

        // Chemnitz abweichende Fächer
        if ($subjectAcronym == 'DE' || $subjectAcronym == 'D') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('DE');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('D');
            }
        } elseif ($subjectAcronym == 'BI' || $subjectAcronym == 'BIO') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('BI');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('BIO');
            }
        } elseif ($subjectAcronym == 'REV' || $subjectAcronym == 'RELI') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('RELI');
            }
        } elseif (($tblSubject = Subject::useService()->getSubjectByAcronym($subjectAcronym))) {

        }

        if ($tblSubject) {
            $this->createDocumentSubject($tblDocument, $ranking, $tblSubject, $IsEssential);
        }
    }
}