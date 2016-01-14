<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Debugger\Logger\BenchmarkLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param bool|false|int $Id
     *
     * @return Stage
     */
    public function frontendSearch($Id = false)
    {

        $Stage = new Stage('Suche', 'nach Gruppe');

        $tblGroupAll = Group::useService()->getGroupAll();
        if (!empty($tblGroupAll)) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) use ($Stage) {

                $Stage->addButton(
                    new Standard(
                        $tblGroup->getName() . '&nbsp;&nbsp;' . new Label(Group::useService()->countPersonAllByGroup($tblGroup)),
                        new Route(__NAMESPACE__), new PersonGroup(),
                        array(
                            'Id' => $tblGroup->getId()
                        ), $tblGroup->getDescription())
                );
            });
        }

        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

//            $idPersonAll = Group::useService()->fetchIdPersonAllByGroup($tblGroup);
//            $tblPersonAll = Person::useService()->fetchPersonAllByIdList($idPersonAll);
            $tblPersonAll = Group::useService()->getPersonAllByGroup($tblGroup);

//            $Cache = $this->getCache(new MemcachedHandler());
//            if (null === ($Result = $Cache->getValue($Id, __METHOD__))) {

            // Check ESZC
            $Acronym = Consumer::useService()->getConsumerBySession()->getAcronym();

            if ($tblGroup->getMetaTable() == 'STUDENT') {
                $tblYearList = Term::useService()->getYearByNow();
            } else {
                $tblYearList = false;
            }

            $Result = array();
            if ($tblPersonAll) {
                $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__ . ':StartRun');
                array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($tblGroup, &$Result, $Acronym, $tblYearList) {

                    $idAddressAll = Address::useService()->fetchIdAddressAllByPerson($tblPerson);
                    $tblAddressAll = Address::useService()->fetchAddressAllByIdList($idAddressAll);
                    if (!empty($tblAddressAll)) {
                        $tblAddress = current($tblAddressAll)->getGuiString();
                    } else {
                        $tblAddress = false;
                    }

                    // Division
                    $tblDivision = false;
                    if ($tblGroup->getMetaTable() == 'STUDENT'){
                        if ($tblYearList) {
                            $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                            if ($tblDivisionStudentList) {
                                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                                    foreach ($tblYearList as $tblYear){
                                        $divisionYear = $tblDivisionStudent->getTblDivision()->getServiceTblYear();
                                        if ($divisionYear && $divisionYear->getId() == $tblYear->getId()){
                                            $tblDivision = $tblDivisionStudent->getTblDivision();
                                            break;
                                        }
                                    }

                                    if ($tblDivision){
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // Prospect
                    $level = false;
                    $year = false;
                    $option = false;
                    if ($tblGroup->getMetaTable() == 'PROSPECT'){
                        $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                        if ($tblProspect){
                            $tblProspectReservation = $tblProspect->getTblProspectReservation();
                            if ($tblProspectReservation){
                                $level = $tblProspectReservation->getReservationDivision();
                                $year = $tblProspectReservation->getReservationYear();
                                $optionA = $tblProspectReservation->getServiceTblTypeOptionA();
                                $optionB = $tblProspectReservation->getServiceTblTypeOptionB();
                                if ($optionA && $optionB){
                                    $option = $optionA->getName() . ', ' . $optionB->getName();
                                } elseif ($optionA) {
                                    $option = $optionA->getName();
                                } elseif ($optionB) {
                                    $option = $optionB->getName();
                                }
                            }
                        }
                    }

                    array_push($Result, array(
                        'FullName' => $tblPerson->getLastFirstName(),
                        'Address' => ($tblAddress
                            ? $tblAddress
                            : new Warning('Keine Adresse hinterlegt')
                        ),
                        'Option' => new Standard('', '/People/Person', new Pencil(), array(
                            'Id' => $tblPerson->getId(),
                            'Group' => $tblGroup->getId()
                        ), 'Bearbeiten'),
                        'Remark' => (
                        $Acronym == 'ESZC' && $tblGroup->getMetaTable() == 'CUSTODY'
                            ? (($Common = Common::useService()->getCommonByPerson($tblPerson)) ? $Common->getRemark() : '')
                            : ''
                        ),
                        'Division' => ($tblDivision ? $tblDivision->getDisplayName() : ''),
                        'Year' => ($year ? $year :''),
                        'Level' => ($level ? $level :''),
                        'SchoolOption' => ($option ? $option : '')
                    ));
                });
                $this->getLogger(new BenchmarkLogger())->addLog(__METHOD__ . ':StopRun');

//                    $Cache->setValue($Id, $Result, 0, __METHOD__);
//                }
            }

            if ($Acronym == 'ESZC' && $tblGroup->getMetaTable() == 'CUSTODY') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Remark' => 'Bemerkung',
                    'Option' => '',
                );
            } elseif ($tblGroup->getMetaTable() == 'STUDENT') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Division' => 'Klasse',
                    'Option' => '',
                );
            } elseif ($tblGroup->getMetaTable() == 'PROSPECT') {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Year' => 'Schuljahr',
                    'Level' => 'Klassenstufe',
                    'SchoolOption' => 'Schulart',
                    'Option' => '',
                );
            } else {
                $ColumnArray = array(
                    'FullName' => 'Name',
                    'Address' => 'Adresse',
                    'Option' => '',
                );
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        new Panel(new PersonGroup() . ' Gruppe', array(
                            new Bold($tblGroup->getName()),
                            ($tblGroup->getDescription() ? new Small($tblGroup->getDescription()) : ''),
                            ($tblGroup->getRemark() ? new Danger(new Italic(nl2br($tblGroup->getRemark()))) : '')
                        ), Panel::PANEL_TYPE_INFO
                        )
                    )),
                    new LayoutRow(new LayoutColumn(array(
                        new Headline('Verfügbare Personen', 'in dieser Gruppe'),
                        new TableData($Result, null, $ColumnArray)
                    )))
                )))
            );
        } else {
            $Stage->setMessage('Bitte wählen Sie eine Gruppe');
        }

        return $Stage;
    }
}
