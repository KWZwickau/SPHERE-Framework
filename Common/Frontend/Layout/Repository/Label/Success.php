<?php
namespace SPHERE\Common\Frontend\Layout\Repository\Label;

use SPHERE\Common\Frontend\Layout\Repository\Label;

/**
 * Class Success
 *
 * @package SPHERE\Common\Frontend\Layout\Repository\Label
 */
class Success extends Label
{

    /**
     * @inheritDoc
     */
    public function __construct($Content)
    {

        parent::__construct($Content, self::LABEL_TYPE_SUCCESS);
    }
}
