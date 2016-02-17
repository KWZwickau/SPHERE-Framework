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

    /**
     * @return Sprint[]
     */
    public function getSprints()
    {

        Utility::orderIssuesBy($this->Sprints, 'getVersion() DESC');
        return $this->Sprints;
    }

    /**
     * @param Sprint $Sprint
     */
    public function addSprint(Sprint $Sprint)
    {

        $this->Sprints[] = $Sprint;
    }
}
