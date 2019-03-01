<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.02.2019
 * Time: 13:42
 */

namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateReferenceForLanguages")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateReferenceForLanguages extends Element
{

    const ATTR_TBL_CERTIFICATE = 'tblCertificate';
    const ATTR_LANGUAGE_RANKING = 'LanguageRanking';

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificate;

    /**
     * @Column(type="integer")
     */
    protected $LanguageRanking;

    /**
     * @Column(type="string")
     */
    protected $ToLevel10;

    /**
     * @Column(type="string")
     */
    protected $AfterBasicCourse;

    /**
     * @Column(type="string")
     */
    protected $AfterAdvancedCourse;


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
     * @return integer
     */
    public function getLanguageRanking()
    {
        return $this->LanguageRanking;
    }

    /**
     * @param integer $LanguageRanking
     */
    public function setLanguageRanking($LanguageRanking)
    {
        $this->LanguageRanking = $LanguageRanking;
    }

    /**
     * @return string
     */
    public function getToLevel10()
    {
        return $this->ToLevel10;
    }

    /**
     * @param string $ToLevel10
     */
    public function setToLevel10($ToLevel10)
    {
        $this->ToLevel10 = $ToLevel10;
    }

    /**
     * @return string
     */
    public function getAfterBasicCourse()
    {
        return $this->AfterBasicCourse;
    }

    /**
     * @param string $AfterBasicCourse
     */
    public function setAfterBasicCourse($AfterBasicCourse)
    {
        $this->AfterBasicCourse = $AfterBasicCourse;
    }

    /**
     * @return string
     */
    public function getAfterAdvancedCourse()
    {
        return $this->AfterAdvancedCourse;
    }

    /**
     * @param string $AfterAdvancedCourse
     */
    public function setAfterAdvancedCourse($AfterAdvancedCourse)
    {
        $this->AfterAdvancedCourse = $AfterAdvancedCourse;
    }
}