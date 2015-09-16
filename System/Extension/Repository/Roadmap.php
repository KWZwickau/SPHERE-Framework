<?php
namespace SPHERE\System\Extension\Repository;

use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Roadmap\Release;

/**
 * Class Roadmap
 *
 * @package SPHERE\System\Extension\Repository
 */
class Roadmap
{

    /** @var Release[] $Release */
    private $Release = array();

    /**
     * @return Stage
     */
    public function getStage()
    {

        $Stage = new Stage('Roadmap');
        $Stage->setContent(
            implode($this->Release)
        );
        return $Stage;
    }

    public function getPdf()
    {

    }

    /**
     * @param string $Version Semantic-Version-Number (x.x.x)
     * @param string $Description
     * @param bool   $isDone  Stable & Public
     *
     * @return Release
     */
    public function createRelease($Version = '0.1.0', $Description = '', $isDone = null)
    {

        $Release = new Release($Version, $Description, $isDone);
        array_push($this->Release, $Release);
        return $Release;
    }
}

