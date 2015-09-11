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
     * @Column(type="smallint")
     */
    protected $Gender;

    /**
     * @return string
     */
    public function getBirthday()
    {

        if (null === $this->Birthday) {
            return false;
        }
        /** @var \DateTime $Birthday */
        $Birthday = $this->Birthday;
        if ($Birthday instanceof \DateTime) {
            return $Birthday->format('d.m.Y');
        } else {
            return (string)$Birthday;
        }
    }

    /**
     * @param null|\DateTime $Birthday
     */
    public function setBirthday(\DateTime $Birthday = null)
    {

        $this->Birthday = $Birthday;
    }

    /**
     * @return string
     */
    public function getBirthplace()
    {

        return $this->Birthplace;
    }

    /**
     * @param string $Birthplace
     */
    public function setBirthplace($Birthplace)
    {

        $this->Birthplace = $Birthplace;
    }

    /**
     * @return int
     */
    public function getGender()
    {

        return $this->Gender;
    }

    /**
     * @param int $Gender
     */
    public function setGender($Gender)
    {

        $this->Gender = $Gender;
    }
}
