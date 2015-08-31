<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommonBirthDates")
 * @Cache(usage="READ_ONLY")
 */
class TblCommonBirthDates extends Element
{

    const VALUE_GENDER_NULL = 0;
    const VALUE_GENDER_MALE = 1;
    const VALUE_GENDER_FEMALE = 2;
    /**
     * @Column(type="datetime")
     */
    protected $Birthday;
    /**
     * @Column(type="string")
     */
    protected $Birthplace;
    /**
     * @Column(type="string")
     */
    protected $Nationality;
    /**
     * @Column(type="smallint")
     */
    protected $Gender;

    /**
     * @return mixed
     */
    public function getBirthday()
    {

        return $this->Birthday;
    }

    /**
     * @param mixed $Birthday
     */
    public function setBirthday(\DateTime $Birthday)
    {

        $this->Birthday = $Birthday;
    }

    /**
     * @return mixed
     */
    public function getBirthplace()
    {

        return $this->Birthplace;
    }

    /**
     * @param mixed $Birthplace
     */
    public function setBirthplace($Birthplace)
    {

        $this->Birthplace = $Birthplace;
    }

    /**
     * @return mixed
     */
    public function getNationality()
    {

        return $this->Nationality;
    }

    /**
     * @param mixed $Nationality
     */
    public function setNationality($Nationality)
    {

        $this->Nationality = $Nationality;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {

        return $this->Gender;
    }

    /**
     * @param mixed $Gender
     */
    public function setGender($Gender)
    {

        $this->Gender = $Gender;
    }
}
