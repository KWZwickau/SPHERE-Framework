<?php
namespace SPHERE\System\Cache\Handler;

use SPHERE\System\Cache\CacheStatus;

/**
 * Class CookieHandler
 * @package SPHERE\System\Cache\Handler
 */
class CookieHandler extends AbstractFastCacheAdapter
{
    /**
     * CookieHandler constructor.
     */
    public function __construct()
    {
        $this->setCacheType(self::CACHE_TYPE_COOKIE);
    }

    /**
     * @return CacheStatus
     */
    public function getStatus()
    {
        $Status = $this->getCacheStatus();
        if ($Status) {
            $Status = $this->grepStatusKey('!^phpFastCache!is', $Status['data']);
            array_walk($Status, function (&$Item) {
                preg_match('!' . preg_quote('s:4:"size";i:', '!') . '([0-9]+);!is', $Item, $Match);
                if (isset($Match[1])) {
                    $Item = $Match[1];
                } else {
                    $Item = false;
                }
            });
            $Status = array_values(array_filter($Status));
            $Size = array_sum($Status);
            return new CacheStatus(0, 0, 3072, $Size, 3072 - $Size, 0);
        }
        return new CacheStatus();
    }

    /**
     * @param string $Pattern
     * @param array $Status
     * @param int $Flags
     * @return array
     */
    private function grepStatusKey($Pattern, $Status, $Flags = 0)
    {
        return array_intersect_key($Status, array_flip(preg_grep($Pattern, array_keys($Status), $Flags)));
    }
}
