<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class External
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class External extends Extension implements ILinkInterface
{

    /** @var string $Name */
    protected $Name;
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var string $Hash */
    protected $Hash = '';

    /** @var string $RedirectRoute */
    private $RedirectRoute = '/';
    /** @var int $RedirectTimeout */
    private $RedirectTimeout = -1;

    const STYLE_BUTTON = 'btn btn-default';
    const STYLE_LINK = '';

    /**
     * @param string $Name
     * @param string $Path
     * @param IIconInterface $Icon
     * @param array $Data
     * @param bool|string $ToolTip
     * @param string $Style
     */
    public function __construct(
        $Name, $Path, IIconInterface $Icon = null, $Data = array(), $ToolTip = true, $Style = self::STYLE_BUTTON
    )
    {

        $this->Name = $Name;
        if (false !== strpos($Path, '\\')) {
            $Path = new Route($Path);
        }
        $this->Template = $this->getTemplate(__DIR__.'/External.twig');
        $this->Template->setVariable('ElementName', $Name);
        $this->Template->setVariable('ElementType', $Style);
        $this->Template->setVariable('ElementIcon', $Icon);
        if (!empty( $Data )) {
            $Signature = (new Authenticator(new Get()))->getAuthenticator();
            $Data = '?'.http_build_query($Signature->createSignature($Data, $Path));
        } else {
            $Data = '';
        }
        $this->Template->setVariable('ElementPath', $Path.$Data);
        if ($ToolTip) {
            if (is_string($ToolTip)) {
                $this->Template->setVariable('ElementToolTip', $ToolTip);
            } else {
                $this->Template->setVariable('ElementToolTip', $Path);
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
     * @param string    $Route
     * @param int $Timeout
     *
     * @return External
     */
    public function setRedirect($Route, $Timeout = Redirect::TIMEOUT_SUCCESS)
    {
        $this->RedirectRoute = $Route;
        $this->RedirectTimeout = $Timeout;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if( $this->RedirectTimeout >= 0 ) {
            $this->Template->setVariable('RedirectRoute', $this->RedirectRoute);
            $this->Template->setVariable('RedirectTimeout', $this->RedirectTimeout);
        }

        $this->Template->setVariable('Hash', $this->getHash());

        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {

        if (empty( $this->Hash )) {
            $this->Hash = 'External-'.crc32( uniqid(__CLASS__, true) );
        }
        return $this->Hash;
    }
}
