<?php
namespace SPHERE\Application\People\Meta\Masern\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Masern\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();

        $this->setTablePersonMasern($Schema);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPersonAgreementType
     *
     * @return Table
     */
    private function setTablePersonMasern(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPersonMasern');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'MasernDate', Type::DATETIME, true);
        $this->createColumn($Table, 'MasernDocumentType', Type::BIGINT, true);
        $this->createColumn($Table, 'MasernCreatorType', Type::BIGINT, true);

        return $Table;
    }
}
