<?php
namespace MOC\V\Core\HttpKernel\Component;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Core\HttpKernel\Component
 */
interface IBridgeInterface
{

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
    public function getPathBase();

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
    public function getPathInfo();

    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getPathBase(), except that it also includes the script filename (e.g. index.php) if one exists.
     *
     * @return string
     */
    public function getUrlBase();

    /**
     * Returns the requested URI.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header when trusted proxies were set via "setTrustedProxies()".
     * The "X-Forwarded-Port" header must contain the client port.
     * If your reverse proxy uses a different header name than "X-Forwarded-Port", configure it via "setTrustedHeaderName()" with the "client-port" key.
     *
     * @return int
     */
    public function getPort();

    /**
     * @return array
     */
    public function getParameterArray();

    /*
      *   * getRequestUri
      *   * getUri
      *   * getUriForPath
     */
}
