<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Prospect
 */
class Frontend implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendMeta()
    {

        $Stage = new Stage();

        return $Stage;
    }
}
