<?php
namespace SPHERE\Application\Setting\Consumer\School;


use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Setting\Consumer\School\Service\Data;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\School\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\Setting\Consumer\School
 */
class Service extends Extension implements IServiceInterface
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
    public function __construct(
        Identifier $Identifier,
        $EntityPath,
        $EntityNamespace
    ) {

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
     * @return bool|TblSchool[]
     */
    public function getSchoolAll()
    {

        return (new Data($this->Binding))->getSchoolAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblSchool
     */
    public function getSchoolById($Id )
    {

        return (new Data($this->Binding))->getSchoolById($Id);
    }

    /**
     * @param IFormInterface $Form
     * @param integer        $School
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createSchool(
        IFormInterface $Form,
        $Type,
        $School
    ) {

        /**
         * Skip to Frontend
         */

        if (null === $Type) {
            return $Form;
        }

        $Error = false;

        if (null === $School) {
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie eine Schule aus')))));
            $Error = true;
        }

        if (!$Error) {
            $tblCompany = Company::useService()->getCompanyById($School);
            $tblType = Type::useService()->getTypeById($Type['Type']);

            if ((new Data($this->Binding))->addSchool($tblCompany, $tblType )
            ) {
                return new Success('Die Schule wurde erfolgreich hinzugefügt')
                .new Redirect('/Setting/Consumer/School', 1, array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Die Schule konnte nicht hinzugefügt werden')
                .new Redirect('/Setting/Consumer/School', 10, array('Id' => $tblCompany->getId()));
            }
        }

        return $Form;
    }

    /**
     * @param TblSchool $tblSchool
     *
     * @return bool
     */
    public function destroySchool(TblSchool $tblSchool)
    {

        return (new Data($this->Binding))->removeSchool($tblSchool);
    }
}
