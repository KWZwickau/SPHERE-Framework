<?php
namespace SPHERE\System\Database\Filter\Link;

use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class AbstractLink
 *
 * @package SPHERE\System\Database\Filter\Link
 */
abstract class AbstractLink
{

    /** @var array $LinkPath */
    protected $LinkPath = array();
    /** @var null|Probe $ProbeLeft */
    private $ProbeLeft = null;
    /** @var null|Probe $ProbeRight */
    private $ProbeRight = null;

    /**
     * @param AbstractService $Service
     * @param string          $GetterMethodAll
     * @param string          $GetterMethodId
     *
     * @return $this
     */
    public function setupProbeLeft(AbstractService $Service, $GetterMethodAll, $GetterMethodId)
    {

        $this->ProbeLeft = new Probe($Service, $GetterMethodAll, $GetterMethodId);
        return $this;
    }

    /**
     * @param AbstractService $Service
     * @param string          $GetterMethodAll
     * @param string          $GetterMethodId
     *
     * @return $this
     */
    public function setupProbeRight(AbstractService $Service, $GetterMethodAll, $GetterMethodId)
    {

        $this->ProbeRight = new Probe($Service, $GetterMethodAll, $GetterMethodId);
        return $this;
    }

    /**
     * @return null|Probe
     */
    public function getProbeLeft()
    {

        return $this->ProbeLeft;
    }

    /**
     * @return null|Probe
     */
    public function getProbeRight()
    {

        return $this->ProbeRight;
    }

    /**
     * @return array
     */
    public function getLinkPath($Index)
    {

        return $this->LinkPath[$Index];
    }

    /**
     * @param string $Left  Property
     * @param string $Right Property
     */
    public function setLinkPath($Left, $Right)
    {

        $this->LinkPath = array($Left, $Right);
    }
}
