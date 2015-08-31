<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Common\Frontend\Layout\Repository\Address as LayoutAddress;
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
     * @Column(type="bigint")
     */
    protected $tblCity;
    /**
     * @Column(type="bigint")
     */
    protected $tblState;

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
     * @return bool|TblCity
     */
    public function getTblCity()
    {

        if (null === $this->tblCity) {
            return false;
        } else {
            return Address::useService()->getCityById($this->tblCity);
        }
    }

    /**
     * @param null|TblCity $tblCity
     */
    public function setTblCity(TblCity $tblCity = null)
    {

        $this->tblCity = ( null === $tblCity ? null : $tblCity->getId() );
    }

    /**
     * @return bool|TblState
     */
    public function getTblState()
    {

        if (null === $this->tblState) {
            return false;
        } else {
            return Address::useService()->getStateById($this->tblState);
        }
    }

    /**
     * @param null|TblState $tblState
     */
    public function setTblState(TblState $tblState = null)
    {

        $this->tblState = ( null === $tblState ? null : $tblState->getId() );
    }

    /**
     * @return LayoutAddress
     */
    public function getLayout()
    {

        return new LayoutAddress($this);
    }
}
