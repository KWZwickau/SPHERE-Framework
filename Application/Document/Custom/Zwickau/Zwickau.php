<?php
namespace SPHERE\Application\Document\Custom\Zwickau;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Zwickau
 *
 * @package SPHERE\Application\Document\Standard\Zwickau
 */
class Zwickau extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schulvertrag'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSchoolContract'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __CLASS__.'::frontendFillSchoolContract'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public static function frontendSchoolContract()
    {

        $Stage = new Stage('Schulvertrag', 'Interessent auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $ReservationYear = '';
                    if(($tblProspect = Prospect::useService()->getProspectByPerson($tblPerson))){
                        if(($tblProspectReservation = $tblProspect->getTblProspectReservation())){
                            $ReservationYear = $tblProspectReservation->getReservationDivision();
                        }
                    }

                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Reservation' => $ReservationYear,
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('PersonId' => $tblPerson->getId()))
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Reservation' => 'für Klassenstufe',
                                    'Option'   => ''
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendFillSchoolContract($PersonId = null)
    {

        $Stage = new Stage('Schulvertrag', 'Erstellen');
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        $Gender = false;
        if ($tblPerson) {
            $Global->POST['Data']['PersonId'] = $PersonId;
            $Global->POST['Data']['FirstLastName'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');

            // Allgemeine Daten der Person
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    $Global->POST['Data']['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                    if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $Global->POST['Data']['Gender'] = $Gender = $tblCommonGender->getName();
                    }
                    if(($CommonInformation = $tblCommon->getTblCommonInformation()))
                    $Global->POST['Data']['Denomination'] = $CommonInformation->getDenomination();
                }
            }

            // Daten des Interessenten
            $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
            if ($tblProspect) {
                if(($tblReservation = $tblProspect->getTblProspectReservation())){
                    $Global->POST['Data']['ReservationDivision'] = $tblReservation->getReservationDivision();
                    if($tblReservation->getReservationYear()){
                        $Global->POST['Data']['ReservationDate'] = '01.08.'.substr($tblReservation->getReservationYear(), 0, 4);
                    }
                }
            }

            // Hauptadresse der Person
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['AddressStreet'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if ($tblCity) {
                    $Global->POST['Data']['AddressPLZ'] = $tblCity->getCode();
                    $Global->POST['Data']['AddressCity'] = $tblCity->getName();
                    $Global->POST['Data']['AddressDistrict'] = $tblCity->getDistrict();
                }
            }

            $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType);
            if($tblRelationshipList){
                $i = 1;
                foreach($tblRelationshipList as $tblRelationship){
                    $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                    $Global->POST['Data']['SalutationCustody'.$i] = $tblPersonCustody->getSalutation();
                    $Global->POST['Data']['FirstLastNameCustody'.$i] = ($tblPersonCustody->getTitle() ? $tblPersonCustody->getTitle().' ':'')
                        .$tblPersonCustody->getFirstName().' '
                        .$tblPersonCustody->getLastName();
                    $i++;
                }
            }
        }
        $Global->savePost();

        $form = $this->formStudentDocument($Gender);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External('Blanko Schulvertrag herunterladen',
            'SPHERE\Application\Api\Document\Custom\Zwickau\SchoolContract\Create',
            new Download(), array('Data' => array('empty')),
            'Schulvertrag herunterladen'));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $HeadPanel
                            , 7)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $form
                            , 7),
                        new LayoutColumn(
                            new Title('Vorlage des Standard-Dokuments "Schulvertrag"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/SchulVertragCMS.png')
                                , ''
                            )
                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param $Gender
     *
     * @return Form
     */
    private function formStudentDocument($Gender)
    {
//        $Data[] = 'BohrEy';

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastName]', 'Vorname, Name',
                                                            'Vorname, Name '.
                                                            ($Gender == 'Männlich'
                                                                ? 'des Schülers'
                                                                : ($Gender == 'Weiblich'
                                                                    ? 'der Schülerin'
                                                                    : 'des Schülers/der Schülerin')
                                                            ))
                                                    , 12)
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthday]', 'Geboren am',
                                                            'Geburtstag')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[Birthplace]', 'Geboren in',
                                                            'Geburtsort')
                                                        , 6),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[Denomination]', 'Konfession',
                                                            'Konfession')
                                                        , 4)
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'Ort',
                                                            'Ort')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                                            'Straße, Hausnummer')
                                                        , 4),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[ReservationDate]', '01.08.XXXX',
                                                            'Vorraussichtlicher Schulbeginn')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[ReservationDivision]', 'Vorraussichtliche Klasse',
                                                            'Vorraussichtliche Klasse')
                                                        , 4),
                                                ))
                                            ))
                                        )
                                    )),

                                    new LayoutColumn(
                                        new Title('Eltern/Personensorgeberechtigte')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup( array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Center(new Title('1'))
                                                    , 1),
                                                    new LayoutColumn(
                                                        new TextField('Data[SalutationCustody1]', 'Anrede', 'Anrede')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastNameCustody1]', 'Name', 'Name')
                                                    , 7)
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup( array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Center(new Title('2'))
                                                    , 1),
                                                    new LayoutColumn(
                                                        new TextField('Data[SalutationCustody2]', 'Anrede', 'Anrede')
                                                    , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[FirstLastNameCustody2]', 'Name', 'Name')
                                                    , 7)
                                                ))
                                            ))
                                        )
                                    )),

//                                    new LayoutColumn(
//                                        new Title('Dokument')
//                                    ),
//                                    new LayoutColumn(new Well(
//                                        new Layout(
//                                            new LayoutGroup(
//                                                new LayoutRow(array(
//                                                    new LayoutColumn(
//                                                        new TextField('Data[Place]', 'Ort', 'Ort')
//                                                        , 6),
//                                                    new LayoutColumn(
//                                                        new DatePicker('Data[Date]', 'Datum', 'Datum')
//                                                        , 6)
//                                                ))
//                                            )
//                                        )
//                                    )),
                                ))
                            )
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Custom\Zwickau\SchoolContract\Create' // ToDo zusätzliche Daten mitgeben
        );
    }
}