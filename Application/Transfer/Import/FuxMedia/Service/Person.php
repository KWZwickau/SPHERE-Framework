<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 24.11.2015
 * Time: 09:11
 */

namespace SPHERE\Application\Transfer\Import\FuxMedia\Service;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service;

class Person extends Service
{
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

    /**
     * @param $Remark
     *
     * @return bool|Service\Entity\TblPerson
     */
    public function getTeacherByRemark($Remark)
    {
        $tblStaffAll = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('STAFF'));

        if ($tblStaffAll){
            foreach ($tblStaffAll as $tblPerson){
                $common = Common::useService()->getCommonByPerson($tblPerson);
                if ($common){
                    if (strtolower($common->getRemark()) === strtolower($Remark)){
                        return $tblPerson;
                    }
                }
            }
        }

        return false;
    }
}