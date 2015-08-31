<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Badge
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Badge extends Extension implements ITemplateInterface
{

    const BADGE_TYPE_NORMAL = '';
    const BADGE_TYPE_DEFAULT = 'badge-default';
    const BADGE_TYPE_PRIMARY = 'badge-primary';
    const BADGE_TYPE_SUCCESS = 'badge-success';
    const BADGE_TYPE_INFO = 'badge-info';
    const BADGE_TYPE_WARNING = 'badge-warning';
    const BADGE_TYPE_DANGER = 'badge-danger';

    /** @var string $Content */
    private $Content = '';
    /** @var string $Type */
    private $Type = '';

    /**
     * @param string $Content
     * @param string $Type
     */
    public function __construct($Content, $Type = Badge::BADGE_TYPE_DEFAULT)
    {

        $this->Content = $Content;
        $this->Type = $Type;
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

        return '<span class="badge '.$this->Type.'">'.$this->Content.'</span>';
    }
}
