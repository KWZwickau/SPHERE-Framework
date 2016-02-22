<?php
namespace SPHERE\Common\Frontend\Layout\Repository\Label;

use SPHERE\Common\Frontend\Layout\Repository\Label;

/**
 * Class Info
 *
 * @package SPHERE\Common\Frontend\Layout\Repository\Label
 */
class Info extends Label
{

    /**
     * @inheritDoc
     */
    public function __construct($Content)
    {

        parent::__construct($Content, self::LABEL_TYPE_INFO);
    }
}
