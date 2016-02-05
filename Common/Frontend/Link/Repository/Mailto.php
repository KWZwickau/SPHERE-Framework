<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class Mailto
 *
 * @package SPHERE\Common\Frontend\Mailto\Repository
 */
class Mailto extends Extension implements ILinkInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var string $Path */
    protected $Path;
    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * @param string         $Name
     * @param                $Path
     * @param IIconInterface $Icon
     * @param array          $Data
     * @param bool|string    $ToolTip
     */
    public function __construct($Name, $Path, IIconInterface $Icon = null, $Data = array(), $ToolTip = false)
    {

        $this->Name = $Name;
        $this->Path = $Path;
        $this->Template = $this->getTemplate(__DIR__.'/Mailto.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementType', 'italic');
        if (null !== $Icon) {
            $this->Template->setVariable('ElementIcon', $Icon);
        }
        if (!empty( $Data )) {
            $Signature = (new Authenticator(new Get()))->getAuthenticator();
            $Data = '?'.http_build_query($Signature->createSignature($Data, $this->Path));
        } else {
            $Data = '';
        }
        $this->Template->setVariable('ElementPath', $this->Path.$Data);
        $this->Template->setVariable('UrlBase', $this->getRequest()->getUrlBase());
        if ($ToolTip) {
            if (is_string($ToolTip)) {
                $this->Template->setVariable('ElementToolTip', $ToolTip);
            } else {
                $this->Template->setVariable('ElementToolTip', $Name);
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        if (Access::useService()->hasAuthorization($this->Path)) {
            return $this->Template->getContent();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {

        return $this->Path;
    }
}
