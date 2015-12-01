<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:40
 */

namespace SPHERE\Application\Reporting\CheckList\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Reporting\CheckList\CheckList;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblList")
 * @Cache(usage="READ_ONLY")
 */
class TblList extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'DESCRIPTION';
    const ATTR_TBL_LIST_TYPE = 'tblListType';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @Column(type="bigint")
     */
    protected $tblListType;

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
     * @return bool|TblListType
     */
    public function getTblListType()
    {
        if (null === $this->tblListType) {
            return false;
        } else {
            return CheckList::useService()->getListTypeById($this->tblListType);
        }
    }

    /**
     * @param TblListType|null $tblListType
     */
    public function setTblListType($tblListType)
    {
        $this->tblListType = (null === $tblListType ? null : $tblListType->getId());
    }
}