<?php
namespace SPHERE\Application\People\Meta\Agreement\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPersonAgreementType")
 * @Cache(usage="READ_ONLY")
 */
class TblPersonAgreementType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_PERSON_AGREEMENT_CATEGORY = 'tblPersonAgreementCategory';

    /**
     * @Column(type="bigint")
     */
    protected $tblPersonAgreementCategory;
    /**
     * @Column(type="text")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblPersonAgreementCategory
     */
    public function getTblPersonAgreementCategory()
    {

        if (null === $this->tblPersonAgreementCategory) {
            return false;
        } else {
            return Agreement::useService()->getPersonAgreementCategoryById($this->tblPersonAgreementCategory);
        }
    }

    /**
     * @param TblPersonAgreementCategory|null $tblPersonAgreementCategory
     */
    public function setTblPersonAgreementCategory(
        TblPersonAgreementCategory $tblPersonAgreementCategory = null
    ) {

        $this->tblPersonAgreementCategory = ( null === $tblPersonAgreementCategory ? null : $tblPersonAgreementCategory->getId() );
    }
}
