<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateSubject extends Element
{

    const ATTR_LANE = 'Lane';
    const ATTR_RANKING = 'Ranking';
    const ATTR_TBL_CERTIFICATE = 'tblCertificate';
    const SERVICE_TBL_SUBJECT = 'serviceTblSubject';

    /**
     * @Column(type="bigint")
     */
    protected $tblCertificate;
    /**
     * @Column(type="boolean")
     */
    protected $IsEssential;
    /**
     * @Column(type="integer")
     */
    protected $Lane;
    /**
     * @Column(type="integer")
     */
    protected $Ranking;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentLiberationType;

    /**
     * @return boolean
     */
    public function isEssential()
    {

        return (bool)$this->IsEssential;
    }

    /**
     * @param boolean $IsEssential
     */
    public function setEssential($IsEssential)
    {

        $this->IsEssential = (bool)$IsEssential;
    }

    /**
     * @return int
     */
    public function getLane()
    {

        return $this->Lane;
    }

    /**
     * @param int $Index
     */
    public function setLane($Index)
    {

        $this->Lane = $Index;
    }

    /**
     * @return int
     */
    public function getRanking()
    {

        return $this->Ranking;
    }

    /**
     * @param int $Index
     */
    public function setRanking($Index)
    {

        $this->Ranking = $Index;
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblStudentLiberationType
     */
    public function getServiceTblStudentLiberationType()
    {

        if (null === $this->serviceTblStudentLiberationType) {
            return false;
        } else {
            return Student::useService()->getStudentLiberationTypeById($this->serviceTblStudentLiberationType);
        }
    }

    /**
     * @param TblStudentLiberationType|null $tblStudentLiberationType
     */
    public function setServiceTblStudentLiberationType(TblStudentLiberationType $tblStudentLiberationType = null)
    {

        $this->serviceTblStudentLiberationType = ( null === $tblStudentLiberationType ? null : $tblStudentLiberationType->getId() );
    }

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
     * @param null|TblCertificate $tblCertificate
     */
    public function setTblCertificate(TblCertificate $tblCertificate = null)
    {

        $this->tblCertificate = ( null === $tblCertificate ? null : $tblCertificate->getId() );
    }
}
