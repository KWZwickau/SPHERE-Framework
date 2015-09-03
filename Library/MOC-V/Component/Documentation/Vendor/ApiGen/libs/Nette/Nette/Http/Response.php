<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Http;

use Nette;

/**
 * HttpResponse class.
 *
 * @author     David Grudl
 *
 * @property   int      $code
 * @property-read bool  $sent
 * @property-read array $headers
 */
final class Response extends Nette\Object implements IResponse
{

    /** @var bool  Send invisible garbage for IE 6? */
    private static $fixIE = true;

    /** @var string The domain in which the cookie will be available */
    public $cookieDomain = '';

    /** @var string The path in which the cookie will be available */
    public $cookiePath = '/';

    /** @var string Whether the cookie is available only through HTTPS */
    public $cookieSecure = false;

    /** @var string Whether the cookie is hidden from client-side */
    public $cookieHttpOnly = true;

    /** @var int HTTP response code */
    private $code = self::S200_OK;

    /**
     * Returns HTTP response code.
     *
     * @return int
     */
    public function getCode()
    {

        return $this->code;
    }

    /**
     * Sets HTTP response code.
     *
     * @param  int
     *
     * @return Response  provides a fluent interface
     * @throws Nette\InvalidArgumentException  if code is invalid
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function setCode($code)
    {

        $code = (int)$code;

        static $allowed = array(
            200 => 1,
            201 => 1,
            202 => 1,
            203 => 1,
            204 => 1,
            205 => 1,
            206 => 1,
            300 => 1,
            301 => 1,
            302 => 1,
            303 => 1,
            304 => 1,
            307 => 1,
            400 => 1,
            401 => 1,
            403 => 1,
            404 => 1,
            405 => 1,
            406 => 1,
            408 => 1,
            410 => 1,
            412 => 1,
            415 => 1,
            416 => 1,
            500 => 1,
            501 => 1,
            503 => 1,
            505 => 1
        );

        if (!isset( $allowed[$code] )) {
            throw new Nette\InvalidArgumentException("Bad HTTP response '$code'.");

        } elseif (headers_sent($file, $line)) {
            throw new Nette\InvalidStateException("Cannot set HTTP code after HTTP headers have been sent".( $file ? " (output started at $file:$line)." : "." ));

        } else {
            $this->code = $code;
            $protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($protocol.' '.$code, true, $code);
        }
        return $this;
    }

    /**
     * Adds HTTP header.
     *
     * @param  string  header name
     * @param  string  header value
     *
     * @return Response  provides a fluent interface
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function addHeader($name, $value)
    {

        if (headers_sent($file, $line)) {
            throw new Nette\InvalidStateException("Cannot send header after HTTP headers have been sent".( $file ? " (output started at $file:$line)." : "." ));
        }

        header($name.': '.$value, false, $this->code);
        return $this;
    }

    /**
     * Sends a Content-type HTTP header.
     *
     * @param  string  mime-type
     * @param  string  charset
     *
     * @return Response  provides a fluent interface
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function setContentType($type, $charset = null)
    {

        $this->setHeader('Content-Type', $type.( $charset ? '; charset='.$charset : '' ));
        return $this;
    }

    /**
     * Sends a HTTP header and replaces a previous one.
     *
     * @param  string  header name
     * @param  string  header value
     *
     * @return Response  provides a fluent interface
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function setHeader($name, $value)
    {

        if (headers_sent($file, $line)) {
            throw new Nette\InvalidStateException("Cannot send header after HTTP headers have been sent".( $file ? " (output started at $file:$line)." : "." ));
        }

        if ($value === null && function_exists('header_remove')) {
            header_remove($name);
        } else {
            header($name.': '.$value, true, $this->code);
        }
        return $this;
    }

    /**
     * Redirects to a new URL. Note: call exit() after it.
     *
     * @param  string  URL
     * @param  int     HTTP code
     *
     * @return void
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function redirect($url, $code = self::S302_FOUND)
    {

        if (isset( $_SERVER['SERVER_SOFTWARE'] ) && preg_match('#^Microsoft-IIS/[1-5]#', $_SERVER['SERVER_SOFTWARE'])
            && $this->getHeader('Set-Cookie') !== null
        ) {
            $this->setHeader('Refresh', "0;url=$url");
            return;
        }

        $this->setCode($code);
        $this->setHeader('Location', $url);
        echo "<h1>Redirect</h1>\n\n<p><a href=\"".htmlSpecialChars($url)."\">Please click here to continue</a>.</p>";
    }

    /**
     * Return the value of the HTTP header.
     *
     * @param  string
     * @param  mixed
     *
     * @return mixed
     */
    public function getHeader($header, $default = null)
    {

        $header .= ':';
        $len = strlen($header);
        foreach (headers_list() as $item) {
            if (strncasecmp($item, $header, $len) === 0) {
                return ltrim(substr($item, $len));
            }
        }
        return $default;
    }

    /**
     * Sets the number of seconds before a page cached on a browser expires.
     *
     * @param  string|int|DateTime time , value 0 means "until the browser is closed"
     *
     * @return Response  provides a fluent interface
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function setExpiration($time)
    {

        if (!$time) { // no cache
            $this->setHeader('Cache-Control', 's-maxage=0, max-age=0, must-revalidate');
            $this->setHeader('Expires', 'Mon, 23 Jan 1978 10:00:00 GMT');
            return $this;
        }

        $time = Nette\DateTime::from($time);
        $this->setHeader('Cache-Control', 'max-age='.( $time->format('U') - time() ));
        $this->setHeader('Expires', self::date($time));
        return $this;
    }

    /**
     * Returns HTTP valid date format.
     *
     * @param  string|int|DateTime
     *
     * @return string
     */
    public static function date($time = null)
    {

        $time = Nette\DateTime::from($time);
        $time->setTimezone(new \DateTimeZone('GMT'));
        return $time->format('D, d M Y H:i:s \G\M\T');
    }

    /**
     * Checks if headers have been sent.
     *
     * @return bool
     */
    public function isSent()
    {

        return headers_sent();
    }

    /**
     * Returns a list of headers to sent.
     *
     * @return array
     */
    public function getHeaders()
    {

        $headers = array();
        foreach (headers_list() as $header) {
            $a = strpos($header, ':');
            $headers[substr($header, 0, $a)] = (string)substr($header, $a + 2);
        }
        return $headers;
    }

    /**
     * @return void
     */
    public function __destruct()
    {

        if (self::$fixIE && isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos($_SERVER['HTTP_USER_AGENT'],
                'MSIE ') !== false
            && in_array($this->code, array(400, 403, 404, 405, 406, 408, 409, 410, 500, 501, 505), true)
            && $this->getHeader('Content-Type', 'text/html') === 'text/html'
        ) {
            echo Nette\Utils\Strings::random(2e3, " \t\r\n"); // sends invisible garbage for IE
            self::$fixIE = false;
        }
    }

    /**
     * Deletes a cookie.
     *
     * @param  string name of the cookie.
     * @param  string
     * @param  string
     * @param  bool
     *
     * @return void
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function deleteCookie($name, $path = null, $domain = null, $secure = null)
    {

        $this->setCookie($name, false, 0, $path, $domain, $secure);
    }

    /**
     * Sends a cookie.
     *
     * @param                      string     name of the cookie
     * @param                      string     value
     * @param  string|int|DateTime expiration time, value 0 means "until the browser is closed"
     * @param                      string
     * @param                      string
     * @param                      bool
     * @param                      bool
     *
     * @return Response  provides a fluent interface
     * @throws Nette\InvalidStateException  if HTTP headers have been sent
     */
    public function setCookie($name, $value, $time, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {

        if (headers_sent($file, $line)) {
            throw new Nette\InvalidStateException("Cannot set cookie after HTTP headers have been sent".( $file ? " (output started at $file:$line)." : "." ));
        }

        setcookie(
            $name,
            $value,
            $time ? Nette\DateTime::from($time)->format('U') : 0,
            $path === null ? $this->cookiePath : (string)$path,
            $domain === null ? $this->cookieDomain : (string)$domain,
            $secure === null ? $this->cookieSecure : (bool)$secure,
            $httpOnly === null ? $this->cookieHttpOnly : (bool)$httpOnly
        );

        if (ini_get('suhosin.cookie.encrypt')) {
            return $this;
        }

        $flatten = array();
        foreach (headers_list() as $header) {
            if (preg_match('#^Set-Cookie: .+?=#', $header, $m)) {
                $flatten[$m[0]] = $header;
                if (PHP_VERSION_ID < 50300) { // multiple deleting due PHP bug #61605
                    header('Set-Cookie:');
                } else {
                    header_remove('Set-Cookie');
                }
            }
        }
        foreach (array_values($flatten) as $key => $header) {
            header($header, $key === 0);
        }

        return $this;
    }

}
