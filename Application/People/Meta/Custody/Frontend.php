<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Custody
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array     $Meta
     *
     * @return Stage
     */
    public function frontendMeta( TblPerson $tblPerson = null, $Meta = array() )
    {

        $Stage = new Stage( 'Sorgeberechtigt', '' );

        return $Stage;
    }
}
