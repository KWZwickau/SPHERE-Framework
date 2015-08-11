<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;

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
    public function frontendMeta( TblPerson $tblPerson = null, $Meta = array() )
    {
        Debugger::screenDump( __METHOD__, $Meta );

        $Stage = new Stage();

        return $Stage;
    }
}
