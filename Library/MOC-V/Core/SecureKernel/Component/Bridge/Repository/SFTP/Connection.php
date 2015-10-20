<?php
namespace MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;

use MOC\V\Core\SecureKernel\Component\Bridge\Bridge;
use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;
use MOC\V\Core\SecureKernel\Component\Exception\ComponentException;
use MOC\V\Core\SecureKernel\Component\IBridgeInterface;
use phpseclib\Crypt\RSA;

/**
 * Class Connection
 *
 * @package MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP
 */
abstract class Connection extends Bridge implements IBridgeInterface
{

    /** @var null|\phpseclib\Net\SFTP */
    protected $Connection = null;

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
        $this->Connection = new \phpseclib\Net\SFTP($Host, $Port, $Timeout);
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

        $Key = new RSA();
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
}
