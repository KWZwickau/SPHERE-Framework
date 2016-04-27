<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:54
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter;
use SPHERE\System\Database\Fitting\Element;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity()
 * @Table(name="tblSerialLetter")
 * @Cache(usage="READ_ONLY")
 */
class TblAddressPerson extends Element
{

    const ATTR_TBL_SERIAL_LETTER = 'tblSerialLetter';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_TO_PERSON = 'serviceTblToPerson';
    const ATTR_TBL_TYPE = 'serviceTblType';

    /**
     * @Column(type="bigint")
     */
    protected $tblSerialLetter;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToPerson;

    /**
     * @Column(type="bigint")
     */
    protected $tblType;

    /**
     * @return bool|TblType
     */
    public function getTblType()
    {

        if (null === $this->tblType) {
            return false;
        } else {
            return SerialLetter::useService()->getTypeById($this->tblType);
        }
    }

    /**
     * @param null|TblType $tblType
     */
    public function setTblType(TblType $tblType = null)
    {

        $this->tblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblSerialLetter
     */
    public function getTblSerialLetter()
    {

        if (null === $this->tblSerialLetter) {
            return false;
        } else {
            return SerialLetter::useService()->getSerialLetterById($this->tblSerialLetter);
        }
    }

    /**
     * @param null|TblSerialLetter $tblSerialLetter
     */
    public function setTblSerialLetter(TblSerialLetter $tblSerialLetter = null)
    {

        $this->tblSerialLetter = ( null === $tblSerialLetter ? null : $tblSerialLetter->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblToPerson
     */
    public function getServiceTblToPerson()
    {

        if (null === $this->serviceTblToPerson) {
            return false;
        } else {
            return Address::useService()->getAddressToPersonById($this->serviceTblToPerson);
        }
    }

    /**
     * @param TblToPerson|null $tblToPerson
     */
    public function setServiceTblToPerson(TblToPerson $tblToPerson = null)
    {

        $this->serviceTblToPerson = ( null === $tblToPerson ? null : $tblToPerson->getId() );
    }
}