<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommonInformation")
 * @Cache(usage="READ_ONLY")
 */
class TblCommonInformation extends Element
{

    const VALUE_IS_ASSISTANCE_NULL = 0;
    const VALUE_IS_ASSISTANCE_YES = 1;
    const VALUE_IS_ASSISTANCE_NO = 2;
    const ATTR_NATIONALITY = 'Nationality';
    const ATTR_DENOMINATION = 'Denomination';
    const ATTR_ASSISTANCE_ACTIVITY = 'AssistanceActivity';
    const ATTR_IS_ASSISTANCE = 'IsAssistance';
    /**
     * @Column(type="string")
     */
    protected $Nationality;
    /**
     * @Column(type="string")
     */
    protected $Denomination;
    /**
     * @Column(type="text")
     */
    protected $AssistanceActivity;
    /**
     * @Column(type="smallint")
     */
    protected $IsAssistance;

    /**
     * @return string
     */
    public function getDenomination()
    {

        return $this->Denomination;
    }

    /**
     * @param string $Denomination
     */
    public function setDenomination($Denomination)
    {

        $this->Denomination = $Denomination;
    }

    /**
     * @return string
     */
    public function getAssistanceActivity()
    {

        return $this->AssistanceActivity;
    }

    /**
     * @param string $AssistanceActivity
     */
    public function setAssistanceActivity($AssistanceActivity)
    {

        $this->AssistanceActivity = $AssistanceActivity;
    }

    /**
     * @return int
     */
    public function isAssistance()
    {

        return $this->IsAssistance;
    }

    /**
     * @param int $IsAssistance
     */
    public function setAssistance($IsAssistance)
    {

        $this->IsAssistance = $IsAssistance;
    }

    /**
     * @return string
     */
    public function getNationality()
    {

        return $this->Nationality;
    }

    /**
     * @param string $Nationality
     */
    public function setNationality($Nationality)
    {

        $this->Nationality = $Nationality;
    }
}
