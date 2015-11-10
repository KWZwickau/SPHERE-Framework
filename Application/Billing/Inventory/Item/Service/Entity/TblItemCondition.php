<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemCondition")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblItemCondition extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceManagement_Student;
    /**
     * @Column(type="bigint")
     */
    protected $tblItem;

    /**
     * @return bool|TblStudent
     */
    public function getServiceManagementStudent()
    {

        if (null === $this->serviceManagement_Student) {
            return false;
        } else {
            return Management::serviceStudent()->entityStudentByNumber($this->serviceManagement_Student); //todo
        }
    }

    /**
     * @param TblStudent $tblStudent
     */
    public function setServiceManagementStudent(TblStudent $tblStudent = null)
    {

        $this->serviceManagement_Student = ( null === $tblStudent ? null : $tblStudent->getStudentNumber() );
    }

    /**
     * @return bool|TblItem
     */
    public function getTblItem()
    {

        if (null === $this->tblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->tblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem = null)
    {

        $this->tblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }
}
