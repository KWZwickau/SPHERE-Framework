<?php
namespace SPHERE\System\Cache;

/**
 * Class CacheStatus
 *
 * @package SPHERE\System\Cache
 */
class CacheStatus
{

    /** @var int $CountHit */
    private $CountHit;
    /** @var int $CountMiss */
    private $CountMiss;
    /** @var float $SizeAvailable */
    private $SizeAvailable;
    /** @var float $SizeUsed */
    private $SizeUsed;
    /** @var float $SizeFree */
    private $SizeFree;
    /** @var float $SizeWasted */
    private $SizeWasted;

    /**
     * CacheStatus constructor.
     *
     * @param $CountHit
     * @param $CountMiss
     * @param $SizeAvailable
     * @param $SizeUsed
     * @param $SizeFree
     * @param $SizeWasted
     */
    public function __construct(
        $CountHit = -1,
        $CountMiss = -1,
        $SizeAvailable = -1,
        $SizeUsed = -1,
        $SizeFree = -1,
        $SizeWasted = -1
    ) {

        $this->CountHit = (int)$CountHit;
        $this->CountMiss = (int)$CountMiss;
        $this->SizeAvailable = (float)$SizeAvailable;
        $this->SizeUsed = (float)$SizeUsed;
        $this->SizeFree = (float)$SizeFree;
        $this->SizeWasted = (float)$SizeWasted;
    }

    /**
     * @return integer
     */
    public function getHitCount()
    {

        return $this->CountHit;
    }

    /**
     * @return integer
     */
    public function getMissCount()
    {

        return $this->CountMiss;
    }

    /**
     * @return float
     */
    public function getAvailableSize()
    {

        return $this->SizeAvailable;
    }

    /**
     * @return float
     */
    public function getUsedSize()
    {

        return $this->SizeUsed;
    }

    /**
     * @return float
     */
    public function getFreeSize()
    {

        return $this->SizeFree;
    }

    /**
     * @return float
     */
    public function getWastedSize()
    {

        return $this->SizeWasted;
    }
}
