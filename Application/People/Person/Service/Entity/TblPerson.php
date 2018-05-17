<?php
namespace SPHERE\Application\People\Person\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblPerson extends Element
{

    const ATTR_FIRST_NAME = 'FirstName';
    const ATTR_LAST_NAME = 'LastName';
    const ATTR_IMPORT_ID = 'ImportId';

    /**
     * @Column(type="bigint")
     */
    protected $tblSalutation;
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
    protected $SecondName;
    /**
     * @Column(type="string")
     */
    protected $LastName;
    /**
     * @Column(type="string")
     */
    protected $BirthName;
    /**
     * @Column(type="string")
     */
    protected $ImportId;

    /**
     * @return string (Salutation Title FirstName SecondName LastName)
     */
    public function getFullName()
    {

        return $this->getSalutation()
        .( $this->getTitle() ? ' '.$this->getTitle() : '' )
            .(preg_match('![a-zA-Z]!s', $this->FirstName) ? ' '.$this->getFirstName() : '')
        .( $this->getSecondName() ? ' '.$this->getSecondName() : '' )
        .( $this->getLastName() ? ' '.$this->getLastName() : '' );
    }

    /**
     * @return string (Salutation Title LastName)
     */
    public function getFullNameWithoutFirstName()
    {

        return $this->getSalutation()
            .( $this->getTitle() ? ' '.$this->getTitle() : '' )
            .( $this->getLastName() ? ' '.$this->getLastName() : '' );
    }

    /**
     * @return string
     */
    public function getSalutation()
    {

        if (!( $Salutation = $this->getTblSalutation() )) {
            return '';
        } else {
            return $Salutation->getSalutation();
        }
    }

    /**
     * @return bool|TblSalutation
     */
    public function getTblSalutation()
    {

        if (null === $this->tblSalutation) {
            return false;
        } else {
            return Person::useService()->getSalutationById($this->tblSalutation);
        }
    }

    /**
     * @param null|TblSalutation $tblSalutation
     */
    public function setTblSalutation(TblSalutation $tblSalutation = null)
    {

        $this->tblSalutation = ( null === $tblSalutation ? null : $tblSalutation->getId() );
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
    public function getSecondName()
    {

        return $this->SecondName;
    }

    /**
     * @param string $SecondName
     */
    public function setSecondName($SecondName)
    {

        $this->SecondName = $SecondName;
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
    public function getBirthName()
    {

        return $this->BirthName;
    }

    /**
     * @param string $BirthName
     */
    public function setBirthName($BirthName)
    {

        $this->BirthName = $BirthName;
    }

    /**
     * @return string
     */
    public function getImportId()
    {

        return $this->ImportId;
    }

    /**
     * @param string $ImportId
     */
    public function setImportId($ImportId)
    {

        $this->ImportId = $ImportId;
    }

    /**
     * @return bool|TblAddress
     */
    public function fetchMainAddress()
    {

        return Address::useService()->getAddressByPerson($this);
    }

    /**
     * @return string
     */
    public function getLastFirstName()
    {

        if (preg_match('![a-zA-Z]!s', $this->FirstName)) {
            return trim($this->LastName.', '.$this->FirstName.' '.$this->SecondName);
        }
        return trim($this->LastName);
    }

    /**
     * @return string
     */
    public function getFirstSecondName()
    {

        if (preg_match('![a-zA-Z]!s', $this->FirstName)) {
            return trim($this->FirstName.' '.$this->SecondName);
        }
        return '';
    }

    /**
     * @return bool|TblCommon
     */
    public function getCommon()
    {

        return Common::useService()->getCommonByPerson($this);
    }

    /**
     * @return bool|TblStudent
     */
    public function getStudent()
    {

        return Student::useService()->getStudentByPerson($this);
    }
}
