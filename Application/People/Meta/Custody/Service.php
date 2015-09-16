<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Meta\Custody\Service\Data;
use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Application\People\Meta\Custody\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Custody
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

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Meta
     *
     * @return IFormInterface|Redirect
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblCustody = $this->getCustodyByPerson($tblPerson);
        if ($tblCustody) {
            (new Data($this->Binding))->updateCustody(
                $tblCustody,
                $Meta['Remark'],
                $Meta['Occupation'],
                $Meta['Employment']
            );
        } else {
            (new Data($this->Binding))->createCustody(
                $tblPerson,
                $Meta['Remark'],
                $Meta['Occupation'],
                $Meta['Employment']
            );
        }
        return new Success('Die Daten wurde erfolgreich gespeichert')
        .new Redirect('/People/Person', 3, array('Id' => $tblPerson->getId()));
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCustody
     */
    public function getCustodyByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->Binding))->getCustodyByPerson($tblPerson);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCustody
     */
    public function getCustodyById($Id)
    {

        return (new Data($this->Binding))->getCustodyById($Id);
    }
}
