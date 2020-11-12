<?php
namespace SPHERE\Application\Education\School\Course\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTechnicalCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblTechnicalCourse extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_GENDER_MALE_NAME = 'GenderMaleName';
    const ATTR_GENDER_FEMALE_NAME = 'GenderFemaleName';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="string")
     */
    protected $GenderMaleName;

    /**
     * @Column(type="string")
     */
    protected $GenderFemaleName;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getGenderMaleName()
    {
        return $this->GenderMaleName;
    }

    /**
     * @param string $GenderMaleName
     */
    public function setGenderMaleName($GenderMaleName)
    {
        $this->GenderMaleName = $GenderMaleName;
    }

    /**
     * @return string
     */
    public function getGenderFemaleName()
    {
        return $this->GenderFemaleName;
    }

    /**
     * @param string $GenderFemaleName
     */
    public function setGenderFemaleName($GenderFemaleName)
    {
        $this->GenderFemaleName = $GenderFemaleName;
    }

    /**
     * @param TblCommonGender|null $tblCommonGender
     *
     * @return string
     */
    public function getDisplayName(TblCommonGender $tblCommonGender = null)
    {
        $result = $this->getName();
        if ($tblCommonGender->getName() == 'Männlich' && $this->getGenderMaleName() != '') {
            $result = $this->getGenderMaleName();
        } elseif ($tblCommonGender->getName() == 'Weiblich' && $this->getGenderFemaleName() != '') {
            $result = $this->getGenderFemaleName();
        }

        return $result;
    }
}