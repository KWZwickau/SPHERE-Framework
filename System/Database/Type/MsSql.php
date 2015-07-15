<?php
namespace SPHERE\System\Database\Type;

use SPHERE\System\Database\ITypeInterface;

/**
 * Class MsSql
 *
 * @package SPHERE\System\Database\Type
 */
class MsSql implements ITypeInterface
{

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return 'pdo_sqlsrv';
    }
}
