<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

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
