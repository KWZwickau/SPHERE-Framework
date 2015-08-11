<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
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
