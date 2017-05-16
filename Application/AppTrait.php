<?php
namespace SPHERE\Application;

use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class AppTrait
 * @package SPHERE\Application
 */
trait AppTrait
{

    /**
     * @param string $Route
     * @param string $Class
     * @param string $Method
     * @param string $Title
     * @param IIconInterface|null $Icon
     * @param string|null $ToolTip
     */
    public static function createCluster( $Route, $Class, $Method, $Title, IIconInterface $Icon = null, $ToolTip = null )
    {
        self::createRouting( $Route, $Class, $Method );
        Main::getDisplay()->addClusterNavigation(
            self::createNavigation( $Route, $Title, $Icon, $ToolTip )
        );
    }

    /**
     * @param string $Route
     * @param string $Class
     * @param string $Method
     * @param string $Title
     * @param IIconInterface|null $Icon
     * @param string|null $ToolTip
     */
    public static function createApplication( $Route, $Class, $Method, $Title, IIconInterface $Icon = null, $ToolTip = null )
    {
        self::createRouting( $Route, $Class, $Method );
        Main::getDisplay()->addApplicationNavigation(
            self::createNavigation( $Route, $Title, $Icon, $ToolTip )
        );
    }

    /**
     * @param string $Route
     * @param string $Class
     * @param string $Method
     * @param string $Title
     * @param IIconInterface|null $Icon
     * @param string|null $ToolTip
     */
    public static function createModule( $Route, $Class, $Method, $Title, IIconInterface $Icon = null, $ToolTip = null )
    {
        self::createRouting( $Route, $Class, $Method );
        Main::getDisplay()->addModuleNavigation(
            self::createNavigation( $Route, $Title, $Icon, $ToolTip )
        );
    }

    /**
     * @param string $Route
     * @param string $Class
     * @param string $Method
     * @param string $Title
     * @param IIconInterface|null $Icon
     * @param string|null $ToolTip
     */
    public static function createService( $Route, $Class, $Method, $Title, IIconInterface $Icon = null, $ToolTip = null )
    {
        self::createRouting( $Route, $Class, $Method );
        Main::getDisplay()->addServiceNavigation(
            self::createNavigation( $Route, $Title, $Icon, $ToolTip )
        );
    }

    /**
     * @param string $Route
     * @param string $Class
     * @param string $Method
     * @param string $Title
     * @param IIconInterface|null $Icon
     * @param string|null $ToolTip
     */
    public static function createFooter( $Route, $Class, $Method, $Title, IIconInterface $Icon = null, $ToolTip = null )
    {
        self::createRouting( $Route, $Class, $Method );
        Main::getDisplay()->addServiceNavigation(
            self::createNavigation( $Route, $Title, $Icon, $ToolTip )
        );
    }

    /**
     * @param string $Route
     * @param string $Title
     * @param IIconInterface|null $Icon
     * @param string|null $ToolTip
     * @return Link
     */
    private static function createNavigation($Route, $Title, IIconInterface $Icon = null, $ToolTip = null )
    {
        if( null !== $Icon && null === $ToolTip ) {
            return new Link(new Link\Route($Route), new Link\Name($Title), new Link\Icon($Icon));
        }
        if( null === $Icon && null !== $ToolTip ) {
            return new Link(new Link\Route($Route), new Link\Name($Title), null, false, $ToolTip);
        }
        if( null !== $Icon && null !== $ToolTip ) {
            return new Link(new Link\Route($Route), new Link\Name($Title), new Link\Icon($Icon), false, $ToolTip);
        }
        return new Link(new Link\Route($Route), new Link\Name( $Title ));
    }

    /**
     * @param string $Route
     * @param string $Class
     * @param string $Method
     * @return RouteParameter
     */
    private static function createRouting( $Route, $Class, $Method )
    {
        $Routing = Main::getDispatcher()->createRoute( $Route, $Class.'::'.$Method);
        Main::getDispatcher()->registerRoute( $Routing );
        return $Routing;
    }
}
