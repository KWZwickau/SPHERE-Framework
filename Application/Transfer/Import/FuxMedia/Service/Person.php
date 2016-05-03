<?php


namespace SPHERE\Application\Transfer\Import\FuxMedia\Service;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service;

/**
 * Class Person
 *
 * @package SPHERE\Application\Transfer\Import\FuxMedia\Service
 */
class Person extends Service
{

    /**
     * @param string $FirstName
     * @param string $LastName
     * @param string $ZipCode
     *
     * @return bool|Service\Entity\TblPerson
     */
    public function  getPersonExists($FirstName, $LastName, $ZipCode)
    {

        $exists = false;

        if (( $persons = $this->getPersonAllByFirstNameAndLastName($FirstName, $LastName) )
        ) {
            foreach ($persons as $person) {
                if (( $addresses = Address::useService()->getAddressAllByPerson($person) )) {
                    if ($addresses[0]->getTblAddress()->getTblCity()->getCode() == $ZipCode) {
                        $exists = $person;
                    }
                }
            }
        }

        return $exists;
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
     * @param $Remark
     *
     * @return bool|Service\Entity\TblPerson
     */
    public function getTeacherByRemark($Remark)
    {

        $tblStaffAll = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('STAFF'));

        if ($tblStaffAll) {
            foreach ($tblStaffAll as $tblPerson) {
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common) {
                    if (strtolower($common->getRemark()) === strtolower($Remark)) {
                        return $tblPerson;
                    }
                }
            }
        }

        return false;
    }
}
