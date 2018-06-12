<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 24.04.2018
 * Time: 10:19
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\System\Database\Fitting\Element;


/**
 * @Entity()
 * @Table(name="tblGenerateCertificateSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblGenerateCertificateSetting extends Element
{

    const ATTR_TBL_GENERATE_CERTIFICATE = 'tblGenerateCertificate';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_FIELD = 'Field';

    /**
     * @Column(type="bigint")
     */
    protected $tblGenerateCertificate;

    /**
     * @Column(type="text")
     */
    protected $Value;

    /**
     * @Column(type="string")
     */
    protected $Field;

    /**
     * @return false|TblGenerateCertificate
     */
    public function getTblGenerateCertificate()
    {

        if (null === $this->tblGenerateCertificate) {
            return false;
        } else {
            return Generate::useService()->getGenerateCertificateById($this->tblGenerateCertificate);
        }
    }

    /**
     * @param TblGenerateCertificate|null $tblGenerateCertificate
     */
    public function setTblGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate = null)
    {

        $this->tblGenerateCertificate = (null === $tblGenerateCertificate ? null : $tblGenerateCertificate->getId());
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {
        $this->Field = $Field;
    }
}