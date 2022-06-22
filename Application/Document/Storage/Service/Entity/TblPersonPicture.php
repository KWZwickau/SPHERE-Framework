<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Entity
 * @Table(name="tblPersonPicture")
 * @Cache(usage="READ_ONLY")
 */
class TblPersonPicture extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="blob")
     */
    protected $Picture;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        return Person::useService()->getPersonById($this->serviceTblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson)
    {

        $this->serviceTblPerson = $tblPerson->getId();
    }

    /**
     * @return string
     */
    public function getPicture($Height = '70px', $borderRadius = '10px', $marginTop = '0px')
    {

        return '<img height='.$Height.' width=auto src="data:image/jpeg;base64,'
            .base64_encode(stream_get_contents($this->Picture)).'" style="border-radius: '.$borderRadius.'; margin-top: '.$marginTop.';"/>';

    }

    /**
     * @param UploadedFile $Picture
     */
    public function setPicture($Picture): void
    {
        $this->Picture = $Picture;
    }
}
