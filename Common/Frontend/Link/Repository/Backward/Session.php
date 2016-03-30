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

        $this->SessionKey = sha1(__CLASS__);
    }

    /**
     * @return History
     */
    public function loadHistory()
    {

        $Cache = $this->getCache(new CookieHandler());
        if (!( $Stack = $Cache->getValue($this->SessionKey, __CLASS__) )) {
            $History = new History();
        } else {
            $History = new History();
            foreach ($Stack as $Route) {
                $History->setStep(new Step($Route));
            }
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
        $Cache->setValue($this->SessionKey, $History->getStack(), ( 60 * 60 * 12 ), __CLASS__);
        return $History;
    }

    public function clearCache()
    {

        $this->getCache(new CookieHandler())->clearCache();
    }
}
