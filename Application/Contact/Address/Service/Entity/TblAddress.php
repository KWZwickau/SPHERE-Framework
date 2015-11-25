<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Common\Frontend\Layout\Repository\Address as LayoutAddress;
use SPHERE\System\Cache\Handler\MemcachedHandler;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblAddress")
 * @Cache(usage="READ_ONLY")
 */
class TblAddress extends Element
{

    const ATTR_STREET_NAME = 'StreetName';
    const ATTR_STREET_NUMBER = 'StreetNumber';
    const ATTR_POST_OFFICE_BOX = 'PostOfficeBox';
    const ATTR_TBL_CITY = 'tblCity';
    const ATTR_TBL_STATE = 'tblState';

    /**
     * @Column(type="string")
     */
    protected $StreetName;
    /**
     * @Column(type="string")
     */
    protected $StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $PostOfficeBox;
    /**
     * @ManyToOne(targetEntity="TblCity",fetch="EAGER",cascade={"persist"})
     * @JoinColumn(name="tblCity",referencedColumnName="Id")
     */
    protected $tblCity;
    /**
     * @Column(nullable=true)
     * @ManyToOne(targetEntity="TblState",fetch="EAGER",cascade={"persist"})
     * @JoinColumn(name="tblState",referencedColumnName="Id")
     */
    protected $tblState;

    /**
     * @return string
     */
    public function getPostOfficeBox()
    {

        return $this->PostOfficeBox;
    }

    /**
     * @param string $PostOfficeBox
     */
    public function setPostOfficeBox($PostOfficeBox)
    {

        $this->PostOfficeBox = $PostOfficeBox;
    }

    /**
     * @return LayoutAddress
     */
    public function getGuiLayout()
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ($Return = $Cache->getValue(__METHOD__ . $this->getId(), __CLASS__))) {
            $Return = new LayoutAddress($this);
            $Cache->setValue(__METHOD__ . $this->getId(), (string)$Return, 0, __CLASS__);
        }
        return $Return;
    }

    /**
     * @return string
     */
    public function getGuiString()
    {

        $Cache = $this->getCache(new MemcachedHandler());
        if (null === ($Return = $Cache->getValue(__METHOD__ . $this->getId(), __CLASS__))) {

            $Return = $this->getStreetName()
                . ' ' . $this->getStreetNumber()
                . ', ' . $this->getTblCity()->getCode()
                . ' ' . $this->getTblCity()->getName()
                . ($this->getTblState() ? ' (' . $this->getTblState()->getName() . ')' : '');

            $Cache->setValue(__METHOD__ . $this->getId(), $Return, 0, __CLASS__);
        }
        return $Return;
    }

    /**
     * @return string
     */
    public function getStreetName()
    {

        return $this->StreetName;
    }

    /**
     * @param string $StreetName
     */
    public function setStreetName($StreetName)
    {

        $this->StreetName = $StreetName;
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {

        return $this->StreetNumber;
    }

    /**
     * @param string $StreetNumber
     */
    public function setStreetNumber($StreetNumber)
    {

        $this->StreetNumber = $StreetNumber;
    }

    /**
     * @return bool|TblCity
     */
    public function getTblCity()
    {

        if (null === $this->tblCity) {
            return false;
        } else {
            if (is_object($this->tblCity)) {
                return $this->tblCity;
            } else {
                return Address::useService()->getCityById($this->tblCity);
            }
        }
    }

    /**
     * @param null|TblCity $tblCity
     */
    public function setTblCity(TblCity $tblCity = null)
    {

        $this->tblCity = ( null === $tblCity ? null : $tblCity );
    }

    /**
     * @return bool|TblState
     */
    public function getTblState()
    {

        if (null === $this->tblState) {
            return false;
        } else {
            if (is_object($this->tblState)) {
                return $this->tblState;
            } else {
                return Address::useService()->getStateById($this->tblState);
            }
        }
    }

    /**
     * @param null|TblState $tblState
     */
    public function setTblState(TblState $tblState = null)
    {

        $this->tblState = ( null === $tblState ? null : $tblState );
    }
}
