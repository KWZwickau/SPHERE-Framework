<?php
namespace SPHERE\Application\Platform\System\Archive;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Archive\Service\Data;
use SPHERE\Application\Platform\System\Archive\Service\Entity\TblArchive;
use SPHERE\Application\Platform\System\Archive\Service\Setup;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\Platform\System\Archive
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        return (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
    }

    /**
     * @param string           $DatabaseName
     * @param null|TblAccount  $tblAccount
     * @param null|TblConsumer $tblConsumer
     * @param null|Element     $Entity
     * @param int              $Type
     *
     * @return false|TblArchive
     */
    public function createArchiveEntry(
        $DatabaseName,
        TblAccount $tblAccount = null,
        TblConsumer $tblConsumer = null,
        Element $Entity = null,
        $Type = TblArchive::ARCHIVE_TYPE_CREATE
    ) {

        (new Data($this->Binding))->createArchiveEntry(
            $DatabaseName, $tblAccount, $tblConsumer, $Entity, $Type
        );
    }

    /**
     * @return TblArchive[]|bool
     */
    public function getArchiveAll()
    {

        return (new Data($this->Binding))->getArchiveAll();
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblArchive[]
     */
    public function getArchiveAllByConsumer(TblConsumer $tblConsumer)
    {

        return (new Data($this->Binding))->getArchiveAllByConsumer($tblConsumer);
    }

    /**
     * Takes an __PHP_Incomplete_Class and casts it to a stdClass object.
     * All properties will be made public in this step.
     *
     * @param  object $Object __PHP_Incomplete_Class
     *
     * @return object
     */
    public function fixArchive($Object)
    {

        if (!is_object($Object) && gettype($Object) == 'object') {
            // preg_replace_callback handler. Needed to calculate new key-length.
            $Fix = create_function(
                '$matches',
                'return ":" . strlen( $matches[1] ) . ":\"" . $matches[1] . "\"";'
            );
            // 1. Serialize the object to a string.
            $Dump = serialize($Object);
            // 2. Change class-type to 'stdClass'.
            preg_match('/^O:\d+:"[^"]++"/', $Dump, $match);
            $Dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $Dump);
            // 3. Make private and protected properties public.
            $Dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', $Fix, $Dump);
            // 4. Unserialize the modified object again.
            $Dump = unserialize($Dump);
            $Dump->ERROR = new Danger("Structure mismatch!<br/>".$match[0]."<br/>Please delete this Item");
            return $Dump;
        } else {
            if (is_string($Object)) {
                // preg_replace_callback handler. Needed to calculate new key-length.
                $Fix = create_function(
                    '$matches',
                    'return ":" . strlen( $matches[1] ) . ":\"" . $matches[1] . "\"";'
                );
                // 1. Serialize the object to a string.
                $Dump = $Object;
                // 2. Change class-type to 'stdClass'.
                preg_match('/^O:\d+:"[^"]++"/', $Dump, $match);
                $Dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $Dump);
                // 3. Make private and protected properties public.
                $Dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', $Fix, $Dump);
                // 4. Unserialize the modified object again.
                $Dump = unserialize($Dump);
                return $Dump;
            }
            return $Object;
        }
    }
}
