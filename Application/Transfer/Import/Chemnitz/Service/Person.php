<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Person\Service;

/**
 * Class Person
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz\Service
 */
class Person extends Service
{

    /**
     * @param Service\Entity\TblSalutation $tblSalutation
     * @param                              $Title
     * @param                              $FirstName
     * @param                              $SecondName
     * @param                              $LastName
     *
     * @return Service\Entity\TblPerson
     */
    public function createPersonFromImport(
        Service\Entity\TblSalutation $tblSalutation,
        $Title,
        $FirstName,
        $SecondName,
        $LastName,
        $GroupList = null
    ) {

        $tblPerson = $this->insertPerson($tblSalutation, $Title, $FirstName, $SecondName, $LastName, $GroupList);

        return $tblPerson;
    }

    /**
     * @param $FirstName
     * @param $LastName
     *
     * @return bool|Service\Entity\TblPerson[]
     */
    public function getPersonAllByFirstNameAndLastName($FirstName, $LastName)
    {

        return (new Service\Data($this->getBinding()))->getPersonAllByFirstNameAndLastName($FirstName, $LastName);
    }

    /**
     * @param string $FirstName
     * @param string $LastName
     * @param string $ZipCode
     * @return bool|Service\Entity\TblPerson
     */
    public function  getPersonExists($FirstName, $LastName, $ZipCode)
    {
        $exists = false;

        if ($persons = $this->getPersonAllByFirstNameAndLastName($FirstName, $LastName)
        ) {
            foreach ($persons as $person) {
                if ($addresses = Address::useService()->getAddressAllByPerson($person)) {
                    if ($addresses[0]->getTblAddress()->getTblCity()->getCode() == $ZipCode) {
                        $exists = $person;
                    }
                }
            }
        }

        return $exists;
    }
}
