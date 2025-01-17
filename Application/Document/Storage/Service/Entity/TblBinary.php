<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBinary")
 * @Cache(usage="READ_ONLY")
 */
class TblBinary extends Element
{

    const ATTR_HASH = 'Hash';
    /**
     * @Column(type="blob")
     */
    protected $BinaryBlob;
    /**
     * @Column(type="string")
     */
    protected $Hash;

    /**
     * @Column(type="integer")
     */
    protected int $FileSizeKiloByte;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblPersonPrinter = null;

    /**
     * @return string|resource
     */
    public function getBinaryBlob()
    {

        return $this->BinaryBlob;
    }

    /**
     * @param string $BinaryBlob
     */
    public function setBinaryBlob($BinaryBlob)
    {

        $this->BinaryBlob = $BinaryBlob;
        // der Hash stimmt bei identischen Pdfs nicht überein, da auch z.B.: das Erstellungsdatum mit im Binary steht
//        $this->Hash = hash_hmac('sha256', $this->getBinaryBlob(),
//            'HbGQLxc378gOWqiA9YR0QMV36boVRmZ5wD69pILKlChtAO1c1kOvuXzGM5zKVIn' // WPA-2
//        );
    }

    /**
     * @return string
     */
    public function getHash()
    {

        return $this->Hash;
    }

    /**
     * @return int
     */
    public function getFileSizeKiloByte(): int
    {
        return $this->FileSizeKiloByte;
    }

    /**
     * @param int $FileSizeKiloByte
     *
     * @return void
     */
    public function setFileSizeKiloByte(int $FileSizeKiloByte): void
    {
        $this->FileSizeKiloByte = $FileSizeKiloByte;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public static function getHashByContent(string $content): string
    {
        return hash_hmac('sha256', $content,
            'HbGQLxc378gOWqiA9YR0QMV36boVRmZ5wD69pILKlChtAO1c1kOvuXzGM5zKVIn' // WPA-2
        );
    }

    /**
     * @param string $Hash
     */
    public function setHash(string $Hash): void
    {
        $this->Hash = $Hash;
    }

    /**
     * @return false|TblPerson
     */
    public function getServiceTblPersonPrinter()
    {
        return $this->serviceTblPersonPrinter ? Person::useService()->getPersonById($this->serviceTblPersonPrinter) : false;
    }

    /**
     * @param ?TblPerson $tblPerson
     */
    public function setServiceTblPersonPrinter(?TblPerson $tblPerson)
    {
        $this->serviceTblPersonPrinter = $tblPerson ? $tblPerson->getId() : null;
    }
}
