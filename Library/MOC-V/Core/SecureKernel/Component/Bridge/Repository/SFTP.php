<?php
namespace MOC\V\Core\SecureKernel\Component\Bridge\Repository;

use MOC\V\Core\SecureKernel\Component\Bridge\Bridge;
use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP\Directory;
use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP\File;
use MOC\V\Core\SecureKernel\Component\Exception\ComponentException;
use MOC\V\Core\SecureKernel\Component\IBridgeInterface;

/**
 * Class SFTP
 *
 * @package MOC\V\Core\SecureKernel\Component\Bridge\Repository
 */
class SFTP extends Bridge implements IBridgeInterface
{

    /** @var null|\Net_SFTP */
    private $Connection = null;
    /** @var string $Host */
    private $Host = 'localhost';
    /** @var int $Port */
    private $Port = 22;
    /** @var int $Timeout */
    private $Timeout = 10;
    /** @var string $Username */
    private $Username = '';
    /** @var null|string $Password */
    private $Password = null;
    /** @var null|string $Key */
    private $Key = null;

    function __construct()
    {

        require_once( __DIR__.'/../../../Vendor/PhpSecLib/0.3.9/vendor/autoload.php' );
    }

    /**
     * @return SFTP
     */
    public function closeConnection()
    {

        if ($this->Connection->isConnected()) {
            $this->Connection->disconnect();
        }
        $this->Connection = null;
        return $this;
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
     * @return SFTP
     * @throws ComponentException
     */
    public function persistConnection()
    {

        if (!$this->Connection->isConnected()) {
            $this->openConnection($this->Host, $this->Port, $this->Timeout);
            if (null === $this->Key) {
                $this->loginCredential($this->Username, $this->Password);
            } else {
                $this->loginCredentialKey($this->Username, $this->Key, $this->Password);
            }
        }
        return $this;
    }

    /**
     * @param string $Host
     * @param int    $Port
     * @param int    $Timeout
     *
     * @return SFTP
     */
    public function openConnection($Host, $Port = 22, $Timeout = 10)
    {

        $this->Host = $Host;
        $this->Port = $Port;
        $this->Timeout = $Timeout;
        $this->Connection = new \Net_SFTP($Host, $Port, $Timeout);
        return $this;
    }

    /**
     * @param string      $Username
     * @param null|string $Password
     *
     * @return SFTP
     * @throws ComponentException
     */
    public function loginCredential($Username, $Password = null)
    {

        $this->Username = $Username;
        $this->Password = $Password;
        if (null === $Password) {
            if (!$this->Connection->login($Username)) {
                throw new ComponentException(__METHOD__.': Login failed');
            }
        } else {
            if (!$this->Connection->login($Username, $Password)) {
                throw new ComponentException(__METHOD__.': Login failed');
            }
        }
        return $this;
    }

    /**
     * @param string      $Username
     * @param string      $File
     * @param null|string $Password
     *
     * @return SFTP
     * @throws ComponentException
     */
    public function loginCredentialKey($Username, $File, $Password = null)
    {

        $this->Username = $Username;
        $this->Key = $File;
        $this->Password = $Password;

        $Key = new \Crypt_RSA();
        if (null !== $Password) {
            $Key->setPassword($Password);
        }
        if (!$Key->loadKey(file_get_contents($File))) {
            throw new ComponentException(__METHOD__.': Key failed');
        }
        if (!$this->Connection->login($Username, $Key)) {
            throw new ComponentException(__METHOD__.': Login failed');
        }
        return $this;
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

    public function uploadFile( $File )
    {

        $this->persistConnection();

        if (!$this->Connection->put(basename($File), file_get_contents($File))) {
            throw new ComponentException(__METHOD__.': Failed');
        }
        return $this;
    }

    public function downloadFile( $File )
    {

        $this->persistConnection();

        if (!$this->Connection->get( $File, $File )) {
            throw new ComponentException(__METHOD__.': Failed');
        }
        return $this;
    }
}
