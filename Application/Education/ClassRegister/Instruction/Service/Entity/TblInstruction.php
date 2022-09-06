<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterInstruction")
 * @Cache(usage="READ_ONLY")
 */
class TblInstruction extends Element
{
    const ATTR_SUBJECT = 'Subject';
    const ATTR_IS_ACTIVE = 'IsActive';

    /**
     * @Column(type="string")
     */
    protected string $Subject;

    /**
     * @Column(type="string")
     */
    protected string $Content;

    /**
     * @Column(type="boolean")
     */
    protected $IsActive;

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->Subject;
    }

    /**
     * @param string $Subject
     */
    public function setSubject(string $Subject): void
    {
        $this->Subject = $Subject;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content): void
    {
        $this->Content = $Content;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->IsActive;
    }

    /**
     * @param bool $IsActive
     */
    public function setIsActive($IsActive): void
    {
        $this->IsActive = $IsActive;
    }
}