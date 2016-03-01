<?php
namespace SPHERE\Common\Frontend\Link\Repository\Backward;

use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Extension\Extension;

/**
 * Class Step
 *
 * @package SPHERE\Common\Frontend\Link\Repository\Backward
 */
class Step extends Extension
{

    /** @var string $Route */
    private $Route = '';
    /** @var string $Path */
    private $Path = '/';
    /** @var array $Data */
    private $Data = array();

    /**
     * Step constructor.
     *
     * @param string $Route
     */
    public function __construct($Route)
    {

        $this->Route = $Route;

        $this->Path = parse_url($this->Route, PHP_URL_PATH);
        $Query = parse_url($this->Route, PHP_URL_QUERY);
        parse_str($Query, $this->Data);
    }

    /**
     * @return bool
     */
    public function isValid()
    {

        $Authenticator = (new Authenticator(new Get()))->getAuthenticator();

        if (empty( $this->Data )) {
            return true;
        } else {
            if (isset( $this->Data['_Sign'] )) {
                $Signature = $this->Data['_Sign'];
                $Data = $this->Data;
                unset( $Data['_Sign'] );
                $Check = $Authenticator->createSignature($Data, $this->Path);
                if ($Check['_Sign'] == $Signature) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * @return string
     */
    public function getRoute()
    {

        return $this->Route;
    }

    /**
     * @return string
     */
    public function getPath()
    {

        return $this->Path;
    }

    /**
     * @return array
     */
    public function getData()
    {

        $Data = $this->Data;
        unset( $Data['_Sign'] );
        return $Data;
    }

    /**
     * @return array
     */
    public function getCleanData()
    {

        $Data = $this->Data;
        unset( $Data['_Sign'] );
        unset( $Data['_goBack'] );
        return $Data;
    }

    /**
     * Mark as goBack Route
     */
    public function setGoBack()
    {

        $this->Data['_goBack'] = true;
    }

    /**
     * @return bool
     */
    public function isGoBack()
    {

        return isset( $this->Data['_goBack'] );
    }
}
