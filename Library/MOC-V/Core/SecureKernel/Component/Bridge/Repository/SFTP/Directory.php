<?php
namespace MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

/**
 * Class Directory
 *
 * @package MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP
 */
class Directory
{

    /** @var string $Name */
    private $Name = '';
    /** @var string $Permission */
    private $Permission = '';
    /** @var string $Mode */
    private $Mode = '';
    /** @var int $LastAccess */
    private $LastAccess = 0;
    /** @var int $LastChange */
    private $LastChange = 0;

    /** @var null|SFTP $Connection */
    private $Connection = null;

    /**
     * @param SFTP $Connection
     * @param array $Attributes
     */
    public function __construct(SFTP $Connection, $Attributes)
    {

        $this->Name = $Attributes['filename'];
        $this->Permission = substr(decoct($Attributes['permissions']), -4);
        $this->Mode = substr(decoct($Attributes['mode']), -4);
        $this->LastAccess = $Attributes['atime'];
        $this->LastChange = $Attributes['mtime'];
        $this->Connection = $Connection;
    }

    /**
     * @return string
     */
    public function getPermission()
    {

        return $this->Permission;
    }

    /**
     * @return string
     */
    public function getMode()
    {

        return $this->Mode;
    }

    /**
     * @return int
     */
    public function getLastAccess()
    {

        return $this->LastAccess;
    }

    /**
     * @return int
     */
    public function getLastChange()
    {

        return $this->LastChange;
    }

    /**
     * @return SFTP\Directory[]|SFTP\File[]
     */
    public function listDirectory()
    {

        return $this->Connection->listDirectory($this->getName());
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }
}
