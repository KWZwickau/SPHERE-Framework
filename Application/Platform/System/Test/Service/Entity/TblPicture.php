<?php
namespace SPHERE\Application\Platform\System\Test\Service\Entity;

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
 * @Table(name="tblPicture")
 * @Cache(usage="READ_ONLY")
 */
class TblPicture extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="blob")
     */
    protected $File;

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
    public function getFile($Height = 'auto', $Width = 'auto')
    {

        return '<img height='.$Height.' width='.$Width.' src="data:image/jpeg;base64,'.base64_encode(stream_get_contents($this->File)).'" style="border-radius: 15px;"/>';
//        return '<object data="data:application/pdf;base64,'.base64_encode(stream_get_contents($this->File)).'" type="application/pdf" style="width: 100%; height: 100%; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;" +"">';
//        return '<object data="data:application/pdf;base64,'.base64_encode(stream_get_contents($this->File)).'">';
//        return '<object data="data:image/jpg;base64,'.base64_encode(stream_get_contents($this->File)).'" style="width: '.$Width.'; height: '.$Height.'>';

    }

    /**
     * @param UploadedFile $File
     */
    public function setFile($File): void
    {
        $this->File = $File;
    }
}
