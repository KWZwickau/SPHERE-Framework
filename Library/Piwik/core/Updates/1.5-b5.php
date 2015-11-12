<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_5_b5 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }

    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'CREATE TABLE `' . Common::prefixTable('session') . '` (
								id CHAR(32) NOT NULL,
								modified INTEGER,
								lifetime INTEGER,
								data TEXT,
								PRIMARY KEY ( id )
								)  DEFAULT CHARSET=utf8' => 1050,
        );
    }
}
