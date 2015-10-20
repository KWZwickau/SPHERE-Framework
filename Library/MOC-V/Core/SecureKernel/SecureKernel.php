<?php
namespace MOC\V\Core\SecureKernel;

use MOC\V\Core\SecureKernel\Component\Bridge\Repository\SFTP;
use MOC\V\Core\SecureKernel\Component\IBridgeInterface;
use MOC\V\Core\SecureKernel\Component\IVendorInterface;
use MOC\V\Core\SecureKernel\Vendor\Vendor;

/**
 * Class SecureKernel
 *
 * @package MOC\V\Core\SecureKernel
 */
class SecureKernel implements IVendorInterface
{

    /** @var IVendorInterface $VendorInterface */
    private $VendorInterface = null;

    /**
     * @param IVendorInterface $VendorInterface
     */
    public function __construct(IVendorInterface $VendorInterface)
    {

        $this->setVendorInterface($VendorInterface);
    }

    /**
     * @return IBridgeInterface
     */
    public static function getSFTP()
    {

        return self::getPhpSecLibSFTP();
    }

    /**
     * @return IBridgeInterface
     */
    private static function getPhpSecLibSFTP()
    {

        $SFTP = new SecureKernel(
            new Vendor(
                new SFTP()
            )
        );

        return $SFTP->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @return IVendorInterface
     */
    public function getVendorInterface()
    {

        return $this->VendorInterface;
    }

    /**
     * @param IVendorInterface $VendorInterface
     *
     * @return IVendorInterface
     */
    public function setVendorInterface(IVendorInterface $VendorInterface)
    {

        $this->VendorInterface = $VendorInterface;
        return $this;
    }

    /**
     * @param IBridgeInterface $BridgeInterface
     *
     * @return IBridgeInterface
     */
    public function setBridgeInterface(IBridgeInterface $BridgeInterface)
    {

        return $this->VendorInterface->setBridgeInterface($BridgeInterface);
    }
}
