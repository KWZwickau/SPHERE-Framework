<?php
namespace SPHERE\Common\Frontend\Link\Repository\Backward;

use SPHERE\System\Cache\Handler\CookieHandler;
use SPHERE\System\Extension\Extension;

/**
 * Class Session
 *
 * @package SPHERE\Common\Frontend\Link\Repository\Backward
 */
class Session extends Extension
{

    /** @var string $SessionKey */
    private $SessionKey = '';

    /**
     * Session constructor.
     */
    public function __construct()
    {

        $this->SessionKey = sha1(session_id().'#'.date('d.m.Y H', time()));
    }

    /**
     * @return History
     */
    public function loadHistory()
    {

        $Cache = $this->getCache(new CookieHandler());
        if (!( $History = $Cache->getValue($this->SessionKey, __CLASS__) )) {
            $History = new History();
        }
        return $History;
    }

    /**
     * @param History $History
     *
     * @return History
     */
    public function saveHistory(History $History)
    {

        $Cache = $this->getCache(new CookieHandler());
        $Cache->setValue($this->SessionKey, $History, ( 60 * 60 * 24 ), __CLASS__);
        return $History;
    }

    public function clearCache()
    {

        $this->getCache(new CookieHandler())->clearCache();
    }
}
