<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_10_1 extends Updates
{
    public static function isMajorUpdate()
    {
        return false;
    }

    public function doUpdate(Updater $updater)
    {
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Overlay');
        } catch (\Exception $e) {
            // pass
        }
    }
}
