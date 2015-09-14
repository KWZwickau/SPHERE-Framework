<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblMember;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\DataCacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Subject\Service
 */
class Data extends DataCacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        $tblCategory = $this->createCategory('Muttersprachen');
        // Muttersprachen
        $tblSubject = $this->createSubject('DE', 'Deutsch');
        $this->addCategorySubject($tblCategory, $tblSubject);

        $tblCategory = $this->createCategory('Fremdsprachen');
        // Fremdsprachen
        $tblSubject = $this->createSubject('EN', 'Englisch');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('LA', 'Latein');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('FR', 'Französisch');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('RU', 'Russisch');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('PO', 'Polnisch');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('SP', 'Spanisch');
        $this->addCategorySubject($tblCategory, $tblSubject);

        $tblCategory = $this->createCategory('Musische Fächer');
        // Musische Fächer
        $tblSubject = $this->createSubject('MU', 'Musik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('KU', 'Kunst');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('DS', 'Darstellendes Spiel');
        $this->addCategorySubject($tblCategory, $tblSubject);

        $tblCategory = $this->createCategory('Naturwissenschaften/Technik');
        // Naturwissenschaften/Technik
        $tblSubject = $this->createSubject('MA', 'Mathematik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('IN', 'Informatik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('BI', 'Biologie');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('CH', 'Chemie');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('PH', 'Physik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('TE', 'Technik');
        $this->addCategorySubject($tblCategory, $tblSubject);

        $tblCategory = $this->createCategory('Gesellschaftswissenschaften');
        // Gesellschaftswissenschaften
        $tblSubject = $this->createSubject('GE', 'Geschichte');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('GEO', 'Geographie');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('SOP', 'Sozialkunde/Politik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('WI', 'Wirtschaft');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('RE', 'Recht');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('ER', 'Erziehungswissenschaften');
        $this->addCategorySubject($tblCategory, $tblSubject);

        $tblCategory = $this->createCategory('Religionsunterricht');
        // Religionsunterricht
        $tblSubject = $this->createSubject('RKA', 'Katholische Religionslehre');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('REV', 'Evangelische Religionslehre');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('ETH', 'Ethik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('PHI', 'Philosophie');
        $this->addCategorySubject($tblCategory, $tblSubject);
    }

    /**
     * @param        $Name
     * @param string $Description
     * @param bool   $IsLocked
     *
     * @return TblCategory
     */
    public function createCategory($Name, $Description = '', $IsLocked = false)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblCategory')->findOneBy(array(
            TblCategory::ATTR_NAME => $Name,
        ));
        if (null === $Entity) {
            $Entity = new TblCategory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param        $Acronym
     * @param        $Name
     * @param string $Description
     *
     * @return TblSubject
     */
    public function createSubject($Acronym, $Name, $Description = '')
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblSubject')->findOneBy(array(
            TblSubject::ATTR_ACRONYM => $Acronym,
        ));
        if (null === $Entity) {
            $Entity = new TblSubject();
            $Entity->setAcronym($Acronym);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return TblMember
     */
    public function addCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblMember')
            ->findOneBy(array(
                TblMember::ATTR_TBL_CATEGORY => $tblCategory->getId(),
                TblMember::ATTR_TBL_SUBJECT  => $tblSubject->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblMember();
            $Entity->setTblCategory($tblCategory);
            $Entity->setTblSubject($tblSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @return int
     */
    public function countSubjectAll()
    {

        return $this->Connection->getEntityManager()->getEntity('TblSubject')->count();
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblSubject');
    }

    /**
     * @param TblCategory $tblCategory
     * @param TblSubject  $tblSubject
     *
     * @return bool
     */
    public function removeCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntity('TblMember')
            ->findOneBy(array(
                TblMember::ATTR_TBL_CATEGORY => $tblCategory->getId(),
                TblMember::ATTR_TBL_SUBJECT  => $tblSubject->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCategory $tblCategory
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByCategory(TblCategory $tblCategory)
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity('TblMember')->findBy(array(
            TblMember::ATTR_TBL_CATEGORY => $tblCategory->getId()
        ));
        array_walk($EntityList, function (TblMember &$V) {

            $V = $V->getTblSubject();
        });
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCategory
     */
    public function getCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblCategory', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSubject
     */
    public function getSubjectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblSubject', $Id);
    }
}
