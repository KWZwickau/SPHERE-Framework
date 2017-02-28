<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.02.2017
 * Time: 08:43
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateSetting extends Element
{

    /**
     * @Column(type="boolean")
     */
    protected $UseCourseForCertificateChoosing;

    /**
     * Wird der Bildungsgang bei der automatischen Zeugniszuordnung mit berücksichtigt
     *
     * @return boolean
     */
    public function getUseCourseForCertificateChoosing()
    {
        return $this->UseCourseForCertificateChoosing;
    }

    /**
     * @param boolean $UseCourseForCertificateChoosing
     */
    public function setUseCourseForCertificateChoosing($UseCourseForCertificateChoosing)
    {
        $this->UseCourseForCertificateChoosing = (boolean) $UseCourseForCertificateChoosing;
    }
}