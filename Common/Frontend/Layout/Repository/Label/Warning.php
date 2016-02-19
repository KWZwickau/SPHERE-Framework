<?php
namespace SPHERE\Common\Frontend\Layout\Repository\Label;

use SPHERE\Common\Frontend\Layout\Repository\Label;

/**
 * Class Warning
 *
 * @package SPHERE\Common\Frontend\Layout\Repository\Label
 */
class Warning extends Label
{

    /**
     * @inheritDoc
     */
    public function __construct($Content)
    {

        parent::__construct($Content, self::LABEL_TYPE_WARNING);
    }
}
