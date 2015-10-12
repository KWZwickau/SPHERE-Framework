<?php
namespace SPHERE\Application\Platform\System\Cache\Frontend;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Cache\ITypeInterface;
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
     * @param ITypeInterface $Cache
     */
    public function __construct(ITypeInterface $Cache)
    {

        $Rate = $this->getTemplate(__DIR__.'/Rate.twig');
        $Rate->setVariable('CountHits', $Cache->getHitCount());
        $Rate->setVariable('CountMisses', $Cache->getMissCount());

        $Memory = $this->getTemplate(__DIR__.'/Memory.twig');
        $Memory->setVariable('SizeAvailable', $Cache->getAvailableSize());
        $Memory->setVariable('SizeUsed', $Cache->getUsedSize());
        $Memory->setVariable('SizeFree', $Cache->getFreeSize());
        $Memory->setVariable('SizeWasted', $Cache->getWastedSize());

        $this->Stage = $Rate->getContent().$Memory->getContent();
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
