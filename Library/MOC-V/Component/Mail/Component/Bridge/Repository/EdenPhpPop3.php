<?php
namespace MOC\V\Component\Mail\Component\Bridge\Repository;

use Eden\Mail\Pop3;
use MOC\V\Component\Mail\Component\Bridge\Bridge;
use MOC\V\Component\Mail\Component\IBridgeInterface;
use MOC\V\Component\Mail\Exception\MailException;
use MOC\V\Core\AutoLoader\AutoLoader;

/**
 * Class EdenPhpPop3
 *
 * @package MOC\V\Component\Mail\Component\Bridge\Repository
 */
class EdenPhpPop3 extends Bridge implements IBridgeInterface
{

    /** @var null|Pop3 $Instance */
    private $Instance = null;

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
     * @return EdenPhpPop3
     * @throws MailException
     */
    public function connectServer($Host, $Username, $Password, $Port = null, $useSSL = false, $useTLS = false)
    {

        try {
            $this->Instance = new Pop3($Host, $Username, $Password, $Port, $useSSL, $useTLS);
            $this->Instance->connect();
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }

    /**
     * @return EdenPhpPop3
     * @throws MailException
     */
    public function disconnectServer()
    {

        try {
            $this->Instance->disconnect();
        } catch (\Exception $Exception) {
            throw new MailException($Exception->getMessage(), $Exception->getCode(), $Exception);
        }
        return $this;
    }
}
