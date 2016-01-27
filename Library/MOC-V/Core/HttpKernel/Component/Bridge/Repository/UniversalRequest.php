<?php
namespace MOC\V\Core\HttpKernel\Component\Bridge\Repository;

use MOC\V\Core\HttpKernel\Component\Bridge\Bridge;
use MOC\V\Core\HttpKernel\Component\IBridgeInterface;
use MOC\V\Core\HttpKernel\Vendor\Universal\Request;

/**
 * Class UniversalRequest
 *
 * @package MOC\V\Core\HttpKernel\Component\Bridge
 */
class UniversalRequest extends Bridge implements IBridgeInterface
{

    /** @var null|array $ParameterArray */
    private static $ParameterArray = null;
    /** @var Request $Instance */
    private static $Instance = null;

    /**
     *
     */
    public function __construct()
    {

        if (null === self::$Instance) {
            self::$Instance = new Request();
        }
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     * - http://localhost/index.php returns an empty string
     * - http://localhost/index.php/page returns an empty string
     * - http://localhost/web/index.php returns '/web'
     * - http://localhost/we%20b/index.php returns '/we%20b'
     *
     * @return string
     */
    public function getPathBase()
    {

        return (string)self::$Instance->getSymfonyRequest()->getBasePath();
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     * - http://localhost/mysite returns an empty string
     * - http://localhost/mysite/about returns '/about'
     * - http://localhost/mysite/enco%20ded returns '/enco%20ded'
     * - http://localhost/mysite/about?var=1 returns '/about'
     *
     * @return string
     */
    public function getPathInfo()
    {

        return (string)self::$Instance->getSymfonyRequest()->getPathInfo();
    }

    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getPathBase(),
     * except that it also includes the script filename (e.g. index.php) if one exists.
     *
     * @return string
     */
    public function getUrlBase()
    {

        return (string)self::$Instance->getSymfonyRequest()->getBaseUrl();
    }

    /**
     * Returns the requested URI.
     *
     * @return string
     */
    public function getUrl()
    {

        return (string)self::$Instance->getSymfonyRequest()->getRequestUri();
    }

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header,
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-Port",
     * configure it via "setTrustedHeaderName()" with the "client-port" key.
     *
     * @return string
     */
    public function getPort()
    {

        return (string)self::$Instance->getSymfonyRequest()->getPort();
    }

    /**
     * Returns the host name.
     *
     * This method can read the client port from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-Host",
     * configure it via "setTrustedHeaderName()" with the "client-host" key.
     *
     * @return string
     */
    public function getHost()
    {

        return (string)self::$Instance->getSymfonyRequest()->getHost();
    }

    /**
     * @return array
     */
    public function getParameterArray()
    {

        if (null === self::$ParameterArray) {
            self::$ParameterArray = array_merge(
                $this->getRequestGETArray(),
                $this->getRequestFILESArray(),
                $this->getRequestPOSTArray(),
                $this->getRequestCUSTOMArray()
            );
        }
        return self::$ParameterArray;
    }

    /**
     * @return array
     */
    public function getRequestGETArray()
    {

        return (array)self::$Instance->getSymfonyRequest()->query->all();
    }

    /**
     * @return array
     */
    public function getRequestFILESArray()
    {

        return (array)self::$Instance->getSymfonyRequest()->files->all();
    }

    /**
     * @return array
     */
    public function getRequestPOSTArray()
    {

        return (array)self::$Instance->getSymfonyRequest()->request->all();
    }

    /**
     * @return array
     */
    public function getRequestCUSTOMArray()
    {

        return (array)self::$Instance->getSymfonyRequest()->attributes->all();
    }
}
