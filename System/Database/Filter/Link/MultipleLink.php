<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class MultipleLink
 *
 * @package SPHERE\System\Database\Filter\Link
 */
class MultipleLink extends AbstractLink
{

    /** @var null|Probe $ProbeCenter */
    private $ProbeCenter = null;

    public function setupProbeCenter(AbstractService $Service, $GetterMethodAll, $GetterMethodId)
    {

        $this->ProbeCenter = new Probe($Service, $GetterMethodAll, $GetterMethodId);
        return $this;
    }

    /**
     * @return null|Probe
     */
    public function getProbeCenter()
    {

        return $this->ProbeCenter;
    }

    /**
     * @param string $Left
     * @param string $CenterLeft
     * @param string $CenterRight
     * @param string $Right
     */
    public function setLinkPath($Left, $CenterLeft, $CenterRight, $Right)
    {

        $this->LinkPath = array($Left, $CenterLeft, $CenterRight, $Right);
    }


}
