<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblSubject extends Element
{

    const ATTR_ACRONYM = 'Acronym';
    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';

    const PSEUDO_ORIENTATION_ID = -1;
    const PSEUDO_PROFILE_ID = -2;

    /**
     * @Column(type="string")
     */
    protected $Acronym;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @param int $id
     * @param string $acronym
     * @param string $name
     *
     * @return TblSubject
     */
    public static function withParameter(int $id, string $acronym, string $name): TblSubject
    {
        $instance = new self();

        $instance->setId($id);
        $instance->setAcronym($acronym);
        $instance->setName($name);

        return  $instance;
    }

    /**
     * @return string
     */
    public function getAcronym()
    {

        return $this->Acronym;
    }

    /**
     * @param string $Acronym
     */
    public function setAcronym($Acronym)
    {

        $this->Acronym = $Acronym;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * Acronym-Name
     * @return string
     */
    public function getDisplayName()
    {

        return $this->getAcronym() . '-' . $this->getName();
    }

    /**
     * @return string
     */
    public function getTechnicalAcronymForCertificateFromName()
    {
        $name = $this->getName();
        if (strpos($name, 'LF') === 0) {
            $split = explode(' ', $name);

            if (isset($split[0])) {
                $result = $split[0];
                if (strlen($result > 2)) {
                    return $result;
                } else {
                    if (isset($split[1])) {
                        $result .= ' ' . $split[1];
                        return  $result;
                    }
                }
            }
        }

        // fallback
        return $this->getAcronym();
    }
}
