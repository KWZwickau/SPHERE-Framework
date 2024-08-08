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
     * @param TblCertificate|null $tblCertificate
     */
    public function setTblCertificate(TblCertificate $tblCertificate = null)
    {

        $this->tblCertificate = (null === $tblCertificate ? null : $tblCertificate->getId());
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