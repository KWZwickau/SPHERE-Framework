<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.04.2019
 * Time: 11:47
 */

namespace SPHERE\Application\People\Group;

use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;

class Custody
{
    /** @var TblToPerson $tblToPerson */
    protected $tblToPerson;

    /** @var bool $isModified */
    protected $isModified = false;

    /** @var bool $isWarning */
    protected $isWarning = false;

    /**
     * Custody constructor.
     *
     * @param TblToPerson $tblToPerson
     * @param bool $isModified
     */
    public function __construct(TblToPerson $tblToPerson, $isModified = false)
    {
        $this->tblToPerson = $tblToPerson;
        $this->isModified = $isModified;
    }

    /**
     * @return bool|string
     */
    public function getGenderName()
    {
        return $this->tblToPerson->getServiceTblPersonFrom()->getGenderNameFromGenderOrSalutation();
    }

    public function getRanking()
    {
        return $this->tblToPerson->getRanking();
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return $this->isModified;
    }

    /**
     * @param bool $isModified
     */
    public function setIsModified($isModified)
    {
        $this->isModified = $isModified;
    }

    /**
     * @return bool
     */
    public function isWarning()
    {
        return $this->isWarning;
    }

    /**
     * @param bool $isWarning
     */
    public function setIsWarning($isWarning)
    {
        $this->isWarning = $isWarning;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->isWarning) {
            return new Warning($this->tblToPerson->getServiceTblPersonFrom()->getLastFirstName());
        } elseif ($this->isModified) {
            return new Success($this->tblToPerson->getServiceTblPersonFrom()->getLastFirstName());
        } else {
            return $this->tblToPerson->getServiceTblPersonFrom()->getLastFirstName();
        }
    }
}