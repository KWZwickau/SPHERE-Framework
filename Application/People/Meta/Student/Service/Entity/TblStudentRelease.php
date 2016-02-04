<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 03.02.2016
 * Time: 08:22
 */

namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentRelease")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentRelease extends Element
{
    const VALUE_SPORT_RELEASE_NULL = 0;
    const VALUE_SPORT_NO_RELEASE = 1;
    const VALUE_SPORT_PART_RELEASE = 2;
    const VALUE_SPORT_FULL_RELEASE = 3;

    /**
     * @Column(type="smallint")
     */
    protected $SportRelease;

    /**
     * @return mixed
     */
    public function getSportRelease()
    {
        return $this->SportRelease;
    }

    /**
     * @param mixed $SportRelease
     */
    public function setSportRelease($SportRelease)
    {
        $this->SportRelease = $SportRelease;
    }
}