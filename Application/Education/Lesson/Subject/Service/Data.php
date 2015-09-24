<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategorySubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroupCategory;
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

        $tblGroupOrientation = $this->createGroup('Neigungskurs', '', true, 'ORIENTATION');
        $tblGroupAdvanced = $this->createGroup('Vertiefungskurs', '', true, 'ADVANCED');
        $tblGroupElective = $this->createGroup('Wahlfach', '', true, 'ELECTIVE');
        $tblGroupStandard = $this->createGroup('Standardfach', '', true, 'STANDARD');

        // Profil
        $tblCategory = $this->createCategory('Profil', '', true, 'PROFILE');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
        $tblSubject = $this->createSubject('KPR', 'Künstlerisches Profil');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('SPR', 'Sprachliches Profil');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('NPR', 'Naturwissenschaftliches Profil');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('GPR', 'Geisteswissenschaftliches Profil');
        $this->addCategorySubject($tblCategory, $tblSubject);

        // Neigungskurs
        $tblCategory = $this->createCategory('Kunst und Kultur');
        $this->addGroupCategory($tblGroupOrientation, $tblCategory);
        $this->addGroupCategory($tblGroupAdvanced, $tblCategory);
        $tblSubject = $this->createSubject('SZSP', 'Szenisches Spiel');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblCategory = $this->createCategory('Soziales und gesellschaftliches Handeln');
        $this->addGroupCategory($tblGroupOrientation, $tblCategory);
        $this->addGroupCategory($tblGroupAdvanced, $tblCategory);
        $tblSubject = $this->createSubject('KRHA', 'Kreatives Handwerken');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblCategory = $this->createCategory('Technik');
        $this->addGroupCategory($tblGroupOrientation, $tblCategory);
        $this->addGroupCategory($tblGroupAdvanced, $tblCategory);
        $tblSubject = $this->createSubject('TECH', 'Technik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('SCHW', 'Schrauberwerkstatt');
        $this->addCategorySubject($tblCategory, $tblSubject);

        // Wahlfach
        $tblCategoryElective = $tblCategory = $this->createCategory('Wahlfach');
        $this->addGroupCategory($tblGroupElective, $tblCategoryElective);

        // Muttersprache
        $tblCategory = $this->createCategory('Muttersprache');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
        $tblSubject = $this->createSubject('DE', 'Deutsch');
        $this->addCategorySubject($tblCategory, $tblSubject);

        // Fremdsprache
        $tblCategory = $this->createCategory('Fremdsprachen', '', true, 'FOREIGNLANGUAGE');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
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

        // Musische Fächer
        $tblCategory = $this->createCategory('Musische Fächer');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
        $tblSubject = $this->createSubject('MU', 'Musik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $this->addCategorySubject($tblCategoryElective, $tblSubject);
        $tblSubject = $this->createSubject('KU', 'Kunst');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $this->addCategorySubject($tblCategoryElective, $tblSubject);
        $tblSubject = $this->createSubject('DASP', 'Darstellendes Spiel');
        $this->addCategorySubject($tblCategory, $tblSubject);

        // Naturwissenschaften/Technik
        $tblCategory = $this->createCategory('Naturwissenschaften/Technik');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
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

        // Gesellschaftswissenschaften
        $tblCategory = $this->createCategory('Gesellschaftswissenschaften');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
        $tblSubject = $this->createSubject('GE', 'Geschichte');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $this->addCategorySubject($tblCategoryElective, $tblSubject);
        $tblSubject = $this->createSubject('GEO', 'Geographie');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $this->addCategorySubject($tblCategoryElective, $tblSubject);
        $tblSubject = $this->createSubject('SOP', 'Sozialkunde/Politik');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('WI', 'Wirtschaft');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('RE', 'Recht');
        $this->addCategorySubject($tblCategory, $tblSubject);
        $tblSubject = $this->createSubject('ER', 'Erziehungswissenschaften');
        $this->addCategorySubject($tblCategory, $tblSubject);

        // Religionsunterricht
        $tblCategory = $this->createCategory('Religionsunterricht', '', true, 'RELIGION');
        $this->addGroupCategory($tblGroupStandard, $tblCategory);
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
     * @param string        $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string        $Identifier
     *
     * @return TblGroup
     */
    public function createGroup($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->Connection->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
                TblGroup::ATTR_IS_LOCKED  => $IsLocked,
                TblGroup::ATTR_IDENTIFIER => $Identifier
            ));
        } else {
            $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
                TblGroup::ATTR_NAME => $Name
            ));
        }
        if (null === $Entity) {
            $Entity = new TblGroup();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string $Identifier
     *
     * @return TblCategory
     */
    public function createCategory($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->Connection->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblCategory')->findOneBy(array(
                TblCategory::ATTR_IS_LOCKED  => $IsLocked,
                TblCategory::ATTR_IDENTIFIER => $Identifier
            ));
        } else {
            $Entity = $Manager->getEntity('TblCategory')->findOneBy(array(
                TblCategory::ATTR_NAME => $Name
            ));
        }
        if (null === $Entity) {
            $Entity = new TblCategory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblGroup    $tblGroup
     * @param TblCategory $tblCategory
     *
     * @return TblGroupCategory
     */
    public function addGroupCategory(TblGroup $tblGroup, TblCategory $tblCategory)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblGroupCategory')
            ->findOneBy(array(
                TblGroupCategory::ATTR_TBL_GROUP    => $tblGroup->getId(),
                TblGroupCategory::ATTR_TBL_CATEGORY => $tblCategory->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblGroupCategory();
            $Entity->setTblGroup($tblGroup);
            $Entity->setTblCategory($tblCategory);
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
     * @return TblCategorySubject
     */
    public function addCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblCategorySubject')
            ->findOneBy(array(
                TblCategorySubject::ATTR_TBL_CATEGORY => $tblCategory->getId(),
                TblCategorySubject::ATTR_TBL_SUBJECT  => $tblSubject->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblCategorySubject();
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
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblGroup');
    }

    /**
     * @return bool|TblCategory[]
     */
    public function getCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblCategory');
    }

    /**
     * @return bool|TblSubject[]
     */
    public function getSubjectAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblSubject');
    }

    /**
     * @param TblGroup    $tblGroup
     * @param TblCategory $tblCategory
     *
     * @return bool
     */
    public function removeGroupCategory(TblGroup $tblGroup, TblCategory $tblCategory)
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblGroupCategory $Entity */
        $Entity = $Manager->getEntity('TblGroupCategory')
            ->findOneBy(array(
                TblGroupCategory::ATTR_TBL_GROUP    => $tblGroup->getId(),
                TblGroupCategory::ATTR_TBL_CATEGORY => $tblCategory->getId()
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
     * @param TblSubject  $tblSubject
     *
     * @return bool
     */
    public function removeCategorySubject(TblCategory $tblCategory, TblSubject $tblSubject)
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblCategorySubject $Entity */
        $Entity = $Manager->getEntity('TblCategorySubject')
            ->findOneBy(array(
                TblCategorySubject::ATTR_TBL_CATEGORY => $tblCategory->getId(),
                TblCategorySubject::ATTR_TBL_SUBJECT  => $tblSubject->getId()
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

        /** @var TblCategorySubject[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity('TblCategorySubject')->findBy(array(
            TblCategorySubject::ATTR_TBL_CATEGORY => $tblCategory->getId()
        ));
        array_walk($EntityList, function (TblCategorySubject &$V) {

            $V = $V->getTblSubject();
        });
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCategory[]
     */
    public function getCategoryAllByGroup(TblGroup $tblGroup)
    {

        /** @var TblGroupCategory[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity('TblGroupCategory')->findBy(array(
            TblGroupCategory::ATTR_TBL_GROUP => $tblGroup->getId()
        ));
        array_walk($EntityList, function (TblGroupCategory &$V) {

            $V = $V->getTblCategory();
        });
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblGroup', $Id);
    }

    /**
     * @param int $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblGroup', array(
            TblGroup::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
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
     * @param int $Identifier
     *
     * @return bool|TblCategory
     */
    public function getCategoryByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblCategory', array(
            TblCategory::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
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
