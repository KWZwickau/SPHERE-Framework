<?php
namespace SPHERE\System\Database\Link;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

/**
 * Class Identifier
 *
 * @package SPHERE\System\Database\Link
 */
class Identifier
{

    /** @var string $Identifier */
    private $Identifier = null;
    /** @var string $Cluster */
    private $Cluster = '';
    /** @var string $Application */
    private $Application = '';
    /** @var string $Module */
    private $Module = '';
    /** @var string|null $Service */
    private $Service = null;
    /** @var string|null $Consumer */
    private $Consumer = null;

    /**
     * @param string      $Cluster
     * @param string      $Application
     * @param string      $Module
     * @param string      $Service
     * @param string|null $Consumer
     */
    public function __construct($Cluster, $Application, $Module = null, $Service = null, $Consumer = null)
    {

        if ($Consumer !== null && is_object($Consumer) && $Consumer instanceof TblConsumer) {
            $Consumer = $Consumer->getAcronym();
        }

        $this->Cluster = $Cluster;
        $this->Application = $Application;
        $this->Module = $Module;
        $this->Service = $Service;
        $this->Consumer = $Consumer;
        $this->Identifier = $this->getConfiguration(true);
    }

    /**
     * @param bool $includeConsumer
     *
     * @return string
     */
    public function getConfiguration($includeConsumer = true)
    {

        if ($includeConsumer) {
            return $this->Cluster
            .':'.$this->Application
            .( $this->Module === null ? '' : ':'.$this->Module )
            .( $this->Service === null ? '' : ':'.$this->Service )
            .( $this->Consumer === null ? '' : ':'.$this->Consumer );
        } else {
            return $this->Cluster
            .':'.$this->Application
            .( $this->Module === null ? '' : ':'.$this->Module )
            .( $this->Service === null ? '' : ':'.$this->Service );
        }
    }

    /**
     * @return string
     */
    public function getCluster()
    {

        return $this->Cluster;
    }

    /**
     * @return string
     */
    public function getApplication()
    {

        return $this->Application;
    }

    /**
     * @return string
     */
    public function getModule()
    {

        return $this->Module;
    }

    /**
     * @return string
     */
    public function getService()
    {

        return $this->Service;
    }

    /**
     * @return null|string
     */
    public function getConsumer()
    {

        return $this->Consumer;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }
}
