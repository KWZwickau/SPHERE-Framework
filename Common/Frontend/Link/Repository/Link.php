<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;

/**
 * Class Link
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class Link extends AbstractLink implements ILinkInterface
{

    /**
     * Link constructor.
     *
     * Without authorization the link will not be displayed
     *
     * @param string $Name
     * @param string $Path
     * @param IIconInterface|null $Icon
     * @param array $Data
     * @param bool|string $ToolTip
     * @param null|string $Anchor
     * @param string $type
     */
    public function __construct($Name, $Path, IIconInterface $Icon = null, $Data = array(), $ToolTip = false, $Anchor = null,
        $type = self::TYPE_LINK
    ){
        $this->setType($type);
        parent::__construct($Name, $Path, $Icon, $Data, $ToolTip, $Anchor);
    }
}
