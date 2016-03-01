<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

/**
 * Class Map
 *
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Map
{

    /** @var Sprint[] $Sprints */
    private $Sprints = array();
    /** @var null|string $VersionPreview */
    private $VersionPreview = null;
    /** @var null|string $VersionRelease */
    private $VersionRelease = null;

    /**
     * @param Sprint $Sprint
     */
    public function addSprint(Sprint $Sprint)
    {

        $this->Sprints[] = $Sprint;
    }

    /**
     * @return null|string
     */
    public function getVersionPreview()
    {

        if ($this->VersionPreview === null) {
            $Sprints = $this->getSprints();
            /** @var Sprint $Sprint */
            foreach ((array)$Sprints as $Index => $Sprint) {
                if ($Sprint->isDone()) {
                    if (isset( $Sprints[( $Index + 1 )] ) && $Sprints[( $Index + 1 )]->isDone()) {
                        $this->VersionPreview = $Sprints[( $Index + 1 )]->getVersion();
                    } else {
                        $this->VersionPreview = $Sprint->getVersion();
                    }
                    break;
                }
            }
        }
        return $this->VersionPreview;
    }

    /**
     * @return Sprint[]
     */
    public function getSprints()
    {

        Utility::orderIssuesBy($this->Sprints, 'getVersion() ASC');
        return $this->Sprints;
    }

    /**
     * @return null|string
     */
    public function getVersionRelease()
    {

        if ($this->VersionRelease === null) {
            $Sprints = $this->getSprints();
            /** @var Sprint $Sprint */
            foreach ((array)$Sprints as $Index => $Sprint) {
                if ($Sprint->isDone()) {
                    $this->VersionRelease = $Sprint->getVersion();
                    break;
                }
            }
        }
        return $this->VersionRelease;
    }
}
