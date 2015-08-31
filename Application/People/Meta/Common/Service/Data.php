<?php
namespace SPHERE\Application\People\Meta\Common\Service;

use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Common\Service
 */
class Data
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCommon
     */
    public function getCommonByPerson(TblPerson $tblPerson)
    {

        /** @var TblCommon $Entity */
        $Entity = $this->Connection->getEntityManager()->getEntity('TblCommon')->findOneBy(array(
            TblCommon::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
        return ( null === $Entity ? false : $Entity );
    }
}
