<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

/**
 * Class Sprint
 *
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Sprint
{

    /** @var null|\SimpleXMLElement $Issue */
    private $Sprint = null;
    /** @var Issue[] $Issues */
    private $Issues = array();

    /**
     * Issue constructor.
     *
     * @param \SimpleXMLElement $Sprint
     */
    public function __construct(\SimpleXMLElement $Sprint)
    {

        $this->Sprint = $Sprint;
    }

    /**
     * @return bool|string
     */
    public function getTimestampStart()
    {

        return date('d.m.Y', substr((string)current($this->Sprint->xpath('start')), 0, -3));
    }

    /**
     * @return bool|string
     */
    public function getTimestampFinish()
    {

        return date('d.m.Y', substr((string)current($this->Sprint->xpath('finish')), 0, -3));
    }

    /**
     * @return Issue[]
     */
    public function getIssues()
    {

        Utility::orderIssuesBy($this->Issues, 'getPriority() ASC, getSubsystem() ASC, getState() DESC');
        return $this->Issues;
    }

    /**
     * @param Issue $Issue
     */
    public function addIssue(Issue $Issue)
    {

        if ($Issue->getVersionFixed() == $this->getVersion()) {
            $this->Issues[] = $Issue;
        }
    }

    /**
     * @return string
     */
    public function getVersion()
    {

        return (string)current($this->Sprint->xpath('version'));
    }

}
