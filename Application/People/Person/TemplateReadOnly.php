<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 10.12.2018
 * Time: 14:43
 */

namespace SPHERE\Application\People\Person;

use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Link\Repository\Link;

/**
 * Class TemplateReadOnly
 *
 * @package SPHERE\Application\People\Person
 */
class TemplateReadOnly
{
    const USE_WELL = true;
    const LINK_POSITION_LEFT = true;

    /**
     * @param string $titleName
     * @param string $content
     * @param Link[] $linkList
     * @param string $titleDescription
     * @param IIconInterface|null $titleIcon
     *
     * @return string
     */
    public static function getContent(
        $titleName,
        $content,
        $linkList = array(),
        $titleDescription = '',
        IIconInterface $titleIcon = null
    ) {

        $titlePrefix = $titleIcon ? $titleIcon . ' ' : '';
        if (!empty($linkList)) {
            $links = '&nbsp;' . implode('&nbsp;', $linkList);
            if (self::LINK_POSITION_LEFT) {
                $titleDescription .= $links;
            } else {
                $titleDescription  = new PullRight($links);
            }
        }
        $title = new Title($titlePrefix . $titleName, $titleDescription);

        if (self::USE_WELL && $content != '') {
            $content = new Well($content);
        }

        return $title . $content;
    }
}