<?php
namespace MOC\V\Component\Mail\Component\Bridge\Repository;

use Eden\Mail\Smtp;
use MOC\V\Component\Mail\Component\Bridge\Bridge;
use MOC\V\Component\Mail\Component\IBridgeInterface;
use MOC\V\Component\Mail\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Mail\Exception\MailException;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Class EdenPhpSmtp
 *
 * @package MOC\V\Component\Mail\Component\Bridge\Repository
 */
class EdenPhpSmtp extends Bridge implements IBridgeInterface
{

    /** @var null|Smtp $Instance */
    private $Instance = null;
    /** @var array $Header */
    private $Header = array();

    /**
     *
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader('Eden\Mail',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master',
            'Eden\Mail'
        );
        AutoLoader::getNamespaceAutoLoader('Eden\Core',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master/vendor/eden/core/Eden/Core',
            'Eden\Core'
        );
        AutoLoader::getNamespaceAutoLoader('Eden\System',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master/vendor/eden/system/Eden/System',
            'Eden\System'
        );
        AutoLoader::getNamespaceAutoLoader('Eden\Type',
            __DIR__.'/../../../Vendor/EdenPhpMail/1.0.3-Master/vendor/eden/type/Eden/Type',
            'Eden\Type'
        );
    }

    /**
     * @param string   $Host
     * @param string   $Username
     * @param string   $Password
     * @param null|int $Port
     * @param bool     $useSSL
     * @param bool     $useTLS
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function connectServer($Host, $Username, $Password, $Port = null, $useSSL = false, $useTLS = false)
    {

        try {
            $this->Instance = new Smtp($Host, $Username, $Password, $Port, $useSSL, $useTLS);
            $this->Instance->connect();
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function disconnectServer()
    {

        try {
            $this->Instance->disconnect();
            $this->Instance->reset();
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param $Address
     *
     * @return EdenPhpSmtp
     */
    public function setFromHeader($Address)
    {

        $this->Header['From'] = $Address;
        return $this;
    }

    /**
     * @param $Address
     *
     * @return EdenPhpSmtp
     */
    public function setReplyHeader($Address)
    {

        $this->Header['Reply-To'] = $Address;
        return $this;
    }

    /**
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function sendMail()
    {

        try {
            $this->Instance->send($this->Header);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string $Content
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function setMailSubject($Content)
    {

        try {
            $this->Instance->setSubject($Content);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string $Content
     * @param bool   $useHtml
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function setMailBody($Content, $useHtml = true)
    {

        try {
            $this->Instance->setBody($Content, $useHtml);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string      $Address
     * @param null|string $Name
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addRecipientTO($Address, $Name = null)
    {

        try {
            $this->Instance->addTo($Address, $Name);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string      $Address
     * @param null|string $Name
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addRecipientCC($Address, $Name = null)
    {

        try {
            $this->Instance->addCC($Address, $Name);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param string      $Address
     * @param null|string $Name
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addRecipientBCC($Address, $Name = null)
    {

        try {
            $this->Instance->addBCC($Address, $Name);
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @param FileParameter $File
     *
     * @return EdenPhpSmtp
     * @throws MailException
     */
    public function addAttachment(FileParameter $File)
    {

        try {
            $this->Instance->addAttachment($File->getFileInfo()->getRealPath(),
                file_get_contents($File->getFileInfo()->getRealPath()));
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }
}
