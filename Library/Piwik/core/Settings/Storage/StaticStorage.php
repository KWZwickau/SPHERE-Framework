<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\Settings\Storage;

/**
 * Static / temporary storage where a value will never be persisted meaning it will use the default value
 * for each request until configured differently. Useful for tests.
 *
 * @api
 */
class StaticStorage extends Storage
{

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
    }

    protected function loadSettings()
    {
        return array();
    }
}
