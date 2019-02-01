<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 17.05.2016
 * Time: 08:25
 */

namespace SPHERE\Application\People\Meta\Club\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Club\Service\Entity\TblClub;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 * @package SPHERE\Application\People\Meta\Club\Service
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

        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableClub($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPeopleMetaClub') )
                ->addLink(new TblClub(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableClub(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblClub');
        if (!$this->getConnection()->hasColumn('tblClub', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblClub', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblClub', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblClub', 'EntryDate')) {
            $Table->addColumn('EntryDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblClub', 'ExitDate')) {
            $Table->addColumn('ExitDate', 'datetime', array('notnull' => false));
        }

        return $Table;
    }
}
