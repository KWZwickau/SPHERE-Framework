<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Piwik;
use Piwik\Plugins\CoreHome\Columns\Metrics\ConversionRate;

class GetMetrics extends Base
{
    public function configureReportMetadata(&$availableReports, $infos)
    {
    }

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Goals_Goals');
        $this->processedMetrics = array(new ConversionRate());
        $this->documentation = ''; // TODO
        $this->order = 1;
        $this->orderGoal = 50;
        $this->metrics = array( 'nb_conversions', 'nb_visits_converted', 'revenue');
        $this->parameters = null;
    }
}
