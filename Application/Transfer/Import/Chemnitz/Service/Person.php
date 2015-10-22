<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz\Service;

use Doctrine\ORM\Query\Expr\Join;
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
     * @param $FirstName
     * @param $LastName
     * @param $Code
     *
     * @return array
     */
    public function getPersonAllByFirstNameAndLastNameAndZipCode($FirstName, $LastName, $Code)
    {

        $QueryBuilder = $this->getQueryBuilder();
        return $QueryBuilder->select('P')
            ->from('\SPHERE\Application\People\Person\Service\Entity\TblPerson', 'P')
            ->leftJoin('\SPHERE\Application\Contact\Address\Service\Entity\TblToPerson', 'AP',
                Join::WITH, 'P.Id = AP.serviceTblPerson'
            )
            ->leftJoin('\SPHERE\Application\Contact\Address\Service\Entity\TblAddress', 'A',
                Join::WITH, 'A.Id = AP.tblAddress'
            )
            ->leftJoin('\SPHERE\Application\Contact\Address\Service\Entity\TblCity', 'C',
                Join::WITH, 'C.Id = A.tblCity'
            )
            ->where('P.FirstName = ?1')
            ->andWhere('P.LastName = ?2')
            ->andWhere('C.Code = ?3')
            ->setParameter(1, $FirstName)
            ->setParameter(2, $LastName)
            ->setParameter(3, $Code)
            ->getQuery()
            ->getResult();

//        ->where(
//                $QueryBuilder->expr()->andX(
//                    $QueryBuilder->expr()->eq('P.FirstName', $FirstName),
//                    $QueryBuilder->expr()->eq('P.LastName', $LastName),
//                    $QueryBuilder->expr()->eq('C.Code', $Code)
//                )
    }
}
