<?php
namespace MOC\V\Core\SecureKernel\Component\Bridge\Repository;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP\Connection;
use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP\Directory;
use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP\File;
use MOC\V\Core\SecureKernel\Component\Exception\ComponentException;

/**
 * Class SFTP
 *
 * @package MOC\V\Core\SecureKernel\Component\Bridge\Repository
 */
class SFTP extends Connection
{

    /**
     *
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('phpseclib', __DIR__.'/../../../Vendor/PhpSecLib/2.0.0');
    }

    /**
     * @param string $Name
     *
     * @return bool
     */
    public function existsDirectory($Name)
    {

        $List = $this->listDirectory();
        if (array_key_exists($Name, $List)) {
            if ($List[$Name] instanceof Directory) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param null|string $Name
     *
     * @return SFTP\Directory[]|SFTP\File[]
     */
    public function listDirectory($Name = null)
    {

        $this->persistConnection();

        if (null === $Name) {
            $Name = $this->Connection->pwd();
        }

        $List = $this->Connection->rawlist($Name);
        $Return = array();

        foreach ($List as $Item => $Attributes) {
            if ($this->Connection->is_dir($Item)) {
                $Return[$Item] = new Directory($this, $Attributes);
            }
            if ($this->Connection->is_file($Item)) {
                $Return[$Item] = new File($this, $Attributes);
            }
        }

        return $Return;
    }

    /**
     * @param string $Name
     *
     * @return bool
     */
    public function existsFile($Name)
    {

        $List = $this->listDirectory();
        if (array_key_exists($Name, $List)) {
            if ($List[$Name] instanceof File) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $Name
     *
     * @return SFTP
     * @throws ComponentException
     */
    public function changeDirectory($Name)
    {

        $this->persistConnection();

        if (!$this->Connection->chdir($Name)) {
            throw new ComponentException(__METHOD__.': Failed');
        }
        return $this;
    }

    /**
     * @param string $Name
     *
     * @return SFTP
     * @throws ComponentException
     */
    public function createDirectory($Name)
    {

        $this->persistConnection();

        if (!$this->Connection->mkdir($Name)) {
            throw new ComponentException(__METHOD__.': Failed');
        }
        return $this;
    }

    /**
     * @param $File
     *
     * @return SFTP
     * @throws ComponentException
     */
    public function uploadFile($File)
    {

        $this->persistConnection();

        if (!$this->Connection->put(basename($File), file_get_contents($File))) {
            throw new ComponentException(__METHOD__.': Failed');
        }
        return $this;
    }

    /**
     * @param $File
     *
     * @return SFTP
     * @throws ComponentException
     */
    public function downloadFile($File)
    {

        $this->persistConnection();

        if (!$this->Connection->get($File, $File)) {
            throw new ComponentException(__METHOD__.': Failed');
        }
        return $this;
    }
}
