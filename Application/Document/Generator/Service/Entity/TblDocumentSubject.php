<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 14:52
 */

namespace SPHERE\Application\Document\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblDocumentSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblDocumentSubject extends Element
{

    const ATTR_RANKING = 'Ranking';
    const ATTR_TBL_DOCUMENT = 'tblDocument';
    const SERVICE_TBL_SUBJECT = 'serviceTblSubject';

    /**
     * @Column(type="bigint")
     */
    protected $tblDocument;

    /**
     * @Column(type="boolean")
     */
    protected $IsEssential;

    /**
     * @Column(type="integer")
     */
    protected $Ranking;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

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
     * @return bool|TblDocument
     */
    public function getTblDocument()
    {

        if (null === $this->tblDocument) {
            return false;
        } else {
            return Generator::useService()->getDocumentById($this->tblDocument);
        }
    }

    /**
     * @param null|TblDocument $tblDocument
     */
    public function setTblDocument(TblDocument $tblDocument = null)
    {

        $this->tblDocument = ( null === $tblDocument ? null : $tblDocument->getId() );
    }
}
