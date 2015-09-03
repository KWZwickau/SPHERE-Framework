<?php
namespace MOC\V\Component\Template;

use MOC\V\Component\Template\Component\Bridge\Repository\SmartyTemplate;
use MOC\V\Component\Template\Component\Bridge\Repository\TwigTemplate;
use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Component\IVendorInterface;
use MOC\V\Component\Template\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Template\Exception\TemplateTypeException;
use MOC\V\Component\Template\Vendor\Vendor;

/**
 * Class Template
 *
 * @package MOC\V\Component\Template
 */
class Template implements IVendorInterface
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
     * @param string $Location
     *
     * @throws TemplateTypeException
     * @return IBridgeInterface
     */
    public static function getTemplate($Location)
    {

        switch ($Type = strtoupper(pathinfo($Location, PATHINFO_EXTENSION))) {
            case 'TWIG': {
                return self::getTwigTemplate($Location);
                break;
            }
            case 'TPL': {
                return self::getSmartyTemplate($Location);
                break;
            }
            default: {
                throw new TemplateTypeException(( $Type ? $Type : '-NA-' ));
                break;
            }
        }
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getTwigTemplate($Location)
    {

        $Template = new Template(
            new Vendor(
                new TwigTemplate()
            )
        );

        $Template->getBridgeInterface()->loadFile(new FileParameter($Location), false);

        return $Template->getBridgeInterface();
    }

    /**
     * @return IBridgeInterface
     */
    public function getBridgeInterface()
    {

        return $this->VendorInterface->getBridgeInterface();
    }

    /**
     * @param string $Location
     *
     * @return IBridgeInterface
     */
    public static function getSmartyTemplate($Location)
    {

        $Template = new Template(
            new Vendor(
                new SmartyTemplate()
            )
        );

        $Template->getBridgeInterface()->loadFile(new FileParameter($Location), false);

        return $Template->getBridgeInterface();
    }

    /**
     * @param string $String
     *
     * @return IBridgeInterface
     */
    public static function getTwigTemplateString($String)
    {

        $Template = new Template(
            new Vendor(
                new TwigTemplate()
            )
        );
        $Template->getBridgeInterface()->loadString($String, true);

        return $Template->getBridgeInterface();
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
