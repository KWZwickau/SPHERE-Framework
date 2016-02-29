<?php
namespace SPHERE\Application\Platform\System\Cache\Frontend;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\System\Cache\CacheStatus;
use SPHERE\System\Extension\Extension;

/**
 * Class Status
 *
 * @package SPHERE\Application\System\Platform\Cache\Frontend
 */
class Status extends Extension implements ITemplateInterface
{

    /** @var string $Stage */
    private $Stage = '';

    /**
     * @param CacheStatus $Cache
     */
    public function __construct(CacheStatus $Cache)
    {

        if (
            ( $Cache->getHitCount() != -1 || $Cache->getMissCount() != -1 )
            && ( $Cache->getHitCount() + $Cache->getMissCount() > 0 )
        ) {
            $HitCount = 100 / ( $Cache->getHitCount() + $Cache->getMissCount() ) * $Cache->getHitCount();
            $MissCount = 100 / ( $Cache->getHitCount() + $Cache->getMissCount() ) * $Cache->getMissCount();
            $Quality = new Header('Hits: '.number_format($HitCount, 2, ',', '.').'%')
                . (new ProgressBar($HitCount, $MissCount, 0, 3))
                    ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_DANGER)
                    ->getContent();
        } else {
            $Quality = '';
        }

        $Used = 100 / $Cache->getAvailableSize() * $Cache->getUsedSize();
        $Free = 100 / $Cache->getAvailableSize() * $Cache->getFreeSize();
        $Wasted = 100 / $Cache->getAvailableSize() * $Cache->getWastedSize();

        if ($Cache->getAvailableSize() != -1) {
            $Size = new Header('Memory: '.
                    $this->formatBytes($Cache->getUsedSize())
                    .' / '
                    .$this->formatBytes($Cache->getAvailableSize() - $Cache->getWastedSize())
                    .' ~ '
                    .$this->formatBytes($Cache->getWastedSize())
                    , number_format($Used, 2, ',', '.').'%').
                (new ProgressBar($Used, $Free, $Wasted, 5))
                    ->setColor(
                        ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_STRIPED
                    )
                    ->getContent();
        } else {
            $Size = new Header(new Danger('Not available'))
                .(new ProgressBar(0, 0, 100))
                    ->getContent();
        }
        $this->Stage = $Quality.$Size;
    }

    /**
     * @param int $Bytes
     * @param int $usePrecision
     * @return string
     */
    private function formatBytes($Bytes, $usePrecision = 2)
    {

        $UnitList = array('B', 'KB', 'MB', 'GB', 'TB');

        $Bytes = max($Bytes, 0);
        $Power = floor(( $Bytes ? log($Bytes) : 0 ) / log(1024));
        $Power = min($Power, count($UnitList) - 1);

        // Uncomment one of the following alternatives
        $Bytes /= pow(1024, $Power);
        // $bytes /= (1 << (10 * $pow));

        if ($Power < 0) {
            $Power = 0;
        }
        return round($Bytes, $usePrecision).' '.$UnitList[$Power];
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Stage;
    }
}
