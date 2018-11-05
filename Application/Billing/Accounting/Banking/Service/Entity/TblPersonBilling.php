<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPersonBilling")
 * @Cache(usage="READ_ONLY")
 */
class TblPersonBilling extends Element
{
    
    const ATTR_SALUTATION = 'Salutation';
    const ATTR_TITLE = 'Title';
    const ATTR_FIRST_NAME = 'FirstName';
    const ATTR_LAST_NAME = 'LastName';
    const ATTR_STREET = 'Street';
    const ATTR_STREET_NUMBER = 'StreetNumber';
    const ATTR_CODE = 'Code';
    const ATTR_CITY = 'City';

    /**
     * @Column(type="string")
     */
    protected $Salutation;
    /**
     * @Column(type="string")
     */
    protected $Title;
    /**
     * @Column(type="string")
     */
    protected $FirstName;
    /**
     * @Column(type="string")
     */
    protected $LastName;
    /**
     * @Column(type="string")
     */
    protected $Street;
    /**
     * @Column(type="string")
     */
    protected $StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $Code;
    /**
     * @Column(type="string")
     */
    protected $City;

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->Salutation;
    }

    /**
     * @param string $Salutation
     */
    public function setSalutation($Salutation)
    {
        $this->Salutation = $Salutation;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->Title;
    }

    /**
     * @param string $Title
     */
    public function setTitle($Title)
    {
        $this->Title = $Title;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param string $FirstName
     */
    public function setFirstName($FirstName)
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param string $LastName
     */
    public function setLastName($LastName)
    {
        $this->LastName = $LastName;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->Street;
    }

    /**
     * @param string $Street
     */
    public function setStreet($Street)
    {
        $this->Street = $Street;
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
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param string $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->City;
    }

    /**
     * @param string $City
     */
    public function setCity($City)
    {
        $this->City = $City;
    }
}
