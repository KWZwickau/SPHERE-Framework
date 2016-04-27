<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.04.2016
 * Time: 08:10
 */

namespace SPHERE\Application\Reporting\SerialLetter;


use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Reporting\SerialLetter\Service\Data;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblType;
use SPHERE\Application\Reporting\SerialLetter\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return (new Data($this->getBinding()))->getSerialLetterById($Id);
    }

    /**
     * @param $Identifier
     * @return bool|TblType
     */
    public function getTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getTypeByIdentifier($Identifier);
    }


    /**
     * @param IFormInterface $Form
     * @param $Check
     * @param $RadioStudent
     * @param $RadioCustody1
     * @param $RadioCustody2
     * @param $RadioFamily
     *
     * @return IFormInterface
     */
    public function setPersonAddressSelection(
        IFormInterface $Form,
        $Check,
        $RadioStudent,
        $RadioCustody1,
        $RadioCustody2,
        $RadioFamily
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Check) {
            return $Form;
        }

        $dataList = array();
        $columnList = array(
            'Student' => 'Schüler',
            'Person' => 'Person',
            'Address' => 'Adresse'
        );
        if (!empty($Check)) {
            foreach ($Check as $personId => $item) {
                $tblPerson = Person::useService()->getPersonById($personId);
                $isPersonSelected = false;

                if (isset($item['Student'])) {
                    $isPersonSelected = true;
                    $data = array();
                    $data['Student'] = $tblPerson->getLastFirstName();
                    $data['Person'] = $tblPerson->getLastFirstName();
                    if (isset($RadioStudent[$personId])) {
                        $tblAddressToPerson = Address::useService()->getAddressToPersonById($RadioStudent[$personId]);
                        $data['Address'] = $tblAddressToPerson->getTblAddress()->getGuiString();
                    } else {
                        $data['Address'] = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[] = $data;
                }

                if (isset($item['Family'])) {
                    $isPersonSelected = true;
                    $data = array();
                    $data['Student'] = $tblPerson->getLastFirstName();
                    $data['Person'] = 'Familie';
                    if (isset($RadioFamily[$personId])) {
                        $tblAddressToPerson = Address::useService()->getAddressToPersonById($RadioFamily[$personId]);
                        $data['Address'] = $tblAddressToPerson->getTblAddress()->getGuiString();
                    } else {
                        $data['Address'] = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[] = $data;
                }

                if (isset($item['Custody1'])) {
                    $isPersonSelected = true;
                    $data = array();
                    $data['Student'] = $tblPerson->getLastFirstName();
                    $tblPersonCustody = Person::useService()->getPersonById($item['Custody1']);
                    $data['Person'] = $tblPersonCustody->getLastFirstName();
                    if (isset($RadioCustody1[$personId])) {
                        $tblAddressToPerson = Address::useService()->getAddressToPersonById($RadioCustody1[$personId]);
                        $data['Address'] = $tblAddressToPerson->getTblAddress()->getGuiString();
                    } else {
                        $data['Address'] = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[] = $data;
                }

                if (isset($item['Custody2'])) {
                    $isPersonSelected = true;
                    $data = array();
                    $data['Student'] = $tblPerson->getLastFirstName();
                    $tblPersonCustody = Person::useService()->getPersonById($item['Custody2']);
                    $data['Person'] = $tblPersonCustody->getLastFirstName();
                    if (isset($RadioCustody2[$personId])) {
                        $tblAddressToPerson = Address::useService()->getAddressToPersonById($RadioCustody2[$personId]);
                        $data['Address'] = $tblAddressToPerson->getTblAddress()->getGuiString();
                    } else {
                        $data['Address'] = new Warning(new Exclamation() . ' Keine Adresse hinterlegt.');
                    }
                    $dataList[] = $data;
                }

                if (!$isPersonSelected) {
                    $data = array();
                    $data['Student'] = new Warning($tblPerson->getLastFirstName());
                    $data['Person'] = new Warning(new Exclamation() . ' Keine Person ausgewählt.');
                    $data['Address'] = '';
                    $dataList[] = $data;
                }
            }
        }

        $Form->appendGridGroup(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TableData($dataList, null, $columnList, false)
                    )
                )
            )
        );

        return $Form;
    }

}