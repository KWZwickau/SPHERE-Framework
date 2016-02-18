<?php
namespace SPHERE\Application\Platform\Roadmap\Youtrack;

/**
 * Class Issue
 *
 * @package SPHERE\Application\Platform\Roadmap\Youtrack
 */
class Issue
{

    /** @var null|\SimpleXMLElement $Issue */
    private $Issue = null;

    /**
     * Issue constructor.
     *
     * @param \SimpleXMLElement $Issue
     */
    public function __construct(\SimpleXMLElement $Issue)
    {

        $this->Issue = $Issue;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return (string)current($this->Issue->xpath('field[@name="description"]/value'));
    }

    /**
     * @return string
     */
    public function getTimestampCreate()
    {

        return date('d.m.Y H:i:s', substr((string)current($this->Issue->xpath('field[@name="created"]/value')), 0, -3));
    }

    /**
     * @return string
     */
    public function getTimestampUpdate()
    {

        return date('d.m.Y H:i:s', substr((string)current($this->Issue->xpath('field[@name="updated"]/value')), 0, -3));
    }

    /**
     * Full Name
     *
     * @return string
     */
    public function getPersonUpdate()
    {

        return (string)current($this->Issue->xpath('field[@name="updaterFullName"]/value'));
    }

    /**
     * Full Name
     *
     * @return string
     */
    public function getPersonCreate()
    {

        return (string)current($this->Issue->xpath('field[@name="reporterFullName"]/value'));
    }

    /**
     * @return int
     */
    public function getCommentCount()
    {

        return (int)(string)current($this->Issue->xpath('field[@name="commentsCount"]/value'));
    }

    public function getCommentList()
    {

        return (string)current($this->Issue->xpath('comment'));
    }

    /**
     * @return int
     */
    public function getVoteCount()
    {

        return (int)(string)current($this->Issue->xpath('field[@name="votes"]/value'));
    }

    /**
     * Short Name
     *
     * @return string
     */
    public function getAssignee()
    {

        return (string)current($this->Issue->xpath('field[@name="Assignee"]/value'));
    }

    /**
     * Version Number
     *
     * @return string
     */
    public function getVersionAffected()
    {

        return (string)current($this->Issue->xpath('field[@name="Affected versions"]/value'));
    }

    /**
     * Version Number
     *
     * @return string
     */
    public function getVersionFixed()
    {

        return (string)current($this->Issue->xpath('field[@name="Fix versions"]/value'));
    }

    /**
     * @return float
     */
    public function getTimePercent()
    {

        if ($this->getTimeEstimation()) {
            if ($this->getTimeSpent() >= $this->getTimeEstimation()) {
                return (float)100;
            }
            return (float)( 100 / $this->getTimeEstimation() * $this->getTimeSpent() );
        } else {
            return (float)( 0 );
        }
    }

    /**
     * Minutes
     *
     * @return int
     */
    public function getTimeEstimation()
    {

        return (int)(string)current($this->Issue->xpath('field[@name="Estimation"]/value'));
    }

    /**
     * Minutes
     *
     * @return int
     */
    public function getTimeSpent()
    {

        return (int)(string)current($this->Issue->xpath('field[@name="Spent time"]/value'));
    }

    function __toString()
    {

        return
            str_pad($this->getPriority(), 10, ' ', STR_PAD_RIGHT).
//            str_pad( $this->getVersionAffected(), 8, ' ', STR_PAD_RIGHT ).
            str_pad($this->getSubsystem(), 20, ' ', STR_PAD_RIGHT).
            str_pad($this->getType(), 12, ' ', STR_PAD_BOTH).
            str_pad($this->getState(), 22, ' ', STR_PAD_BOTH).
            str_pad($this->getId(), 12, ' ', STR_PAD_RIGHT).
            $this->getTitle();//.' # '.
//            $this->getAssignee().' # '.
//            $this->getPersonCreate().' # '.
//            $this->getPersonUpdate().' # '.
//            $this->getTimeEstimation().' # '.
//            $this->getTimeSpent().' # '.
//            $this->getTimestampCreate().' # '.
//            $this->getTimestampUpdate().' # '.
//            $this->getCommentCount().' # '.
//            $this->getVoteCount();
    }

    /**
     * @return string
     */
    public function getPriority()
    {

        return (string)current($this->Issue->xpath('field[@name="Priority"]/value'));
    }

    /**
     * @return string
     */
    public function getSubsystem()
    {

        return (string)current($this->Issue->xpath('field[@name="Subsystem"]/value'));
    }

    /**
     * @return string
     */
    public function getType()
    {

        return (string)current($this->Issue->xpath('field[@name="Type"]/value'));
    }

    /**
     * @return string
     */
    public function getState()
    {

        return (string)current($this->Issue->xpath('field[@name="State"]/value'));
    }

    /**
     * Project Id
     *
     * @return string
     */
    public function getId()
    {

        $Project = (string)current($this->Issue->xpath('field[@name="projectShortName"]/value'));
        $Number = (string)current($this->Issue->xpath('field[@name="numberInProject"]/value'));

        return $Project.'-'.$Number;
    }

    /**
     * @return string
     */
    public function getTitle()
    {

        return (string)current($this->Issue->xpath('field[@name="summary"]/value'));
    }
}
