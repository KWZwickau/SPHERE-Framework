<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2016
 * Time: 13:09
 */

namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateLevel")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateLevel extends Element
{

    const ATTR_TBL_CERTIFICATE = 'tblCertificate';
    const SERVICE_TBL_LEVEL = 'serviceTblLevel';
    const ATTR_LEVEL = 'Level';

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblLevel;

    /**
     * @Column(type="integer")
     */
    protected ?int $Level;
    
    /**
     * @return bool|TblCertificate
     */
    public function getTblCertificate()
    {

        if (null === $this->tblCertificate) {
            return false;
        } else {
            return Generator::useService()->getCertificateById($this->tblCertificate);
        }
    }

    /**
     * @param TblCertificate|null $tblCertificate
     */
    public function setTblCertificate(TblCertificate $tblCertificate = null)
    {

        $this->tblCertificate = (null === $tblCertificate ? null : $tblCertificate->getId());
    }
    
    /**
     * @deprecated
     *
    * @return bool|TblLevel
    */
    public function getServiceTblLevel()
    {

        if (null === $this->serviceTblLevel) {
            return false;
        } else {
            return Division::useService()->getLevelById($this->serviceTblLevel);
        }
    }

    /**
     * @deprecated
     *
     * @param TblLevel|null $tblLevel
     */
    public function setServiceTblLevel(TblLevel $tblLevel = null)
    {

        $this->serviceTblLevel = (null === $tblLevel ? null : $tblLevel->getId());
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->Level !== null ? $this->Level : 0;
    }

    /**
     * @param int $Level
     */
    public function setLevel(int $Level): void
    {
        $this->Level = $Level;
    }
}