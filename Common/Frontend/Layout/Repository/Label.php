<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Label
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Label extends Extension implements ITemplateInterface
{

    const LABEL_TYPE_NORMAL = '';
    const LABEL_TYPE_DEFAULT = 'label-default';
    const LABEL_TYPE_PRIMARY = 'label-primary';
    const LABEL_TYPE_SUCCESS = 'label-success';
    const LABEL_TYPE_INFO = 'label-info';
    const LABEL_TYPE_WARNING = 'label-warning';
    const LABEL_TYPE_DANGER = 'label-danger';

    /** @var string $Content */
    private $Content = '';
    /** @var string $Type */
    private $Type = '';

    /**
     * @param string $Content
     * @param string $Type
     */
    public function __construct($Content, $Type = Label::LABEL_TYPE_DEFAULT)
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

        return '<span class="label '.$this->Type.'">'.$this->Content.'</span>';
    }
}
