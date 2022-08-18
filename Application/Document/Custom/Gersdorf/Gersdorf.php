<?php
namespace SPHERE\Application\Document\Custom\Gersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType as TblTypePhone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
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
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Gersdorf extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Emergency'), new Link\Name('Notarzt'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/MetaDataComparison'), new Link\Name('Stammdatenabfrage'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Emergency', __CLASS__.'::frontendEmergency'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Emergency/Fill', __CLASS__.'::frontendFillEmergency'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MetaDataComparison', __CLASS__.'::frontendMetaDataComparison'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MetaDataComparison/Division', __CLASS__.'::frontendMetaDataComparisonDivision'
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
    public static function frontendEmergency()
    {

        $Stage = new Stage('Notarzt', 'Schüler auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $Division = '';
                    if(($tblDivision =  Student::useService()->getCurrentDivisionByPerson($tblPerson))){
                        $Division = $tblDivision->getDisplayName();
                    }
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => $Division,
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Emergency/Fill', null,
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
                                    'Division' => 'aktuelle Klasse',
                                    'Option'   => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                    ),
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
    public function frontendFillEmergency($PersonId = null)
    {

        $Stage = new Stage('Notarzt', 'Dokument erstellen');
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        $Gender = false;
        if ($tblPerson) {
            $Global->POST['Data']['PersonId'] = $PersonId;
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastName().', '.$tblPerson->getFirstName();
//            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');

            // Allgemeine Daten der Person
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    $Global->POST['Data']['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                    if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $Global->POST['Data']['Gender'] = $Gender = $tblCommonGender->getName();
                    }
                    if(($CommonInformation = $tblCommon->getTblCommonInformation()))
                        $Global->POST['Data']['Nationality'] = $CommonInformation->getNationality();
                }
                if($tblStudent = Student::useService()->getStudentByPerson($tblPerson)){
                    if(($tblMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){
                        $Global->POST['Data']['Disease'] = $tblMedicalRecord->getDisease();
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
                    $Global->POST['Data']['AddressPLZCity'] = $tblCity->getCode().' '.$tblCity->getName();
                    $Global->POST['Data']['AddressDistrict'] = $tblCity->getDistrict();
                }
            }
            if(($tblToPersonList = Phone::useService()->getPhoneAllByPerson($tblPerson))){
                $PhoneString = '';
                foreach($tblToPersonList as $tblToPerson){
                    $PhoneString .= $tblToPerson->getTblPhone()->getNumber().' ';
                }
                $Global->POST['Data']['Phone'] = $PhoneString;
            }

            $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
            $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType);
            if($tblRelationshipList){
                foreach($tblRelationshipList as $tblRelationship){
                    $PhoneString = '';
                    $S = $tblRelationship->getRanking();
                    $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
//                    $Global->POST['Data']['SalutationCustody'.$S] = $tblPersonCustody->getSalutation();
                    $Global->POST['Data']['S'.$S]['LastFirstName'] = ($tblPersonCustody->getTitle() ? $tblPersonCustody->getTitle().' ':'')
                        .$tblPersonCustody->getLastName().', '
                        .$tblPersonCustody->getFirstName();

                    if(($tblAddress = Address::useService()->getAddressByPerson($tblPersonCustody))){
                        $Global->POST['Data']['S'.$S]['Address'] = $tblAddress->getGuiString();
                    }

                    if(($tblToPersonPhoneList = Phone::useService()->getPhoneAllByPerson($tblPersonCustody))){
                        foreach($tblToPersonPhoneList as $tblToPersonPhone){
                            if(($tblToPersonPhone->getTblType()->getName() == TblTypePhone::VALUE_NAME_PRIVATE)
                                || $tblToPersonPhone->getTblType()->getName() == TblTypePhone::VALUE_NAME_EMERCENCY){
                                if(($tblPhone =  $tblToPersonPhone->getTblPhone())){
                                    $PhoneString .= $tblPhone->getNumber().' ';
                                }
                            }
                        }
                    }
                    $Global->POST['Data']['S'.$S]['Phone'] = $PhoneString;
                }
            }
        }
        $Global->savePost();

        $form = $this->formEmergencyDocument($Gender);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External('Blanko Notarzt Dokument herunterladen',
            'SPHERE\Application\Api\Document\Custom\Gersdorf\Emergency\Create',
            new Download(), array('Data' => array('empty')),
            'Notarzt Dokument herunterladen'));

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
                            new Title('Vorlage Dokument "Notarzt"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/Notarzt_EVOSG.png')
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
    private function formEmergencyDocument($Gender)
    {
        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(array(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Informationen Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(new LayoutGroup(array(
                                            new LayoutRow(array(
                                                new LayoutColumn(
                                                    new TextField('Data[LastFirstName]', 'Name, Vorname',
                                                        'Name, Vorname '.
                                                        ($Gender == 'Männlich'
                                                            ? 'des Schülers'
                                                            : ($Gender == 'Weiblich'
                                                                ? 'der Schülerin'
                                                                : 'des Schülers/der Schülerin')
                                                        ))
                                                , 6),
                                                new LayoutColumn(
                                                    new TextField('Data[Gender]', 'Männlich / Weiblich / Divisers / Ohne Angabe', 'Geschlecht')
                                                , 6),
                                            )),
                                            new LayoutRow(array(
                                                new LayoutColumn(
                                                    new TextField('Data[Birthday]', '01.01.2010', 'Geboren am')
                                                , 6),
                                                new LayoutColumn(
                                                    new TextField('Data[Birthplace]', 'Geburtsort',
                                                        'Geboren in')
                                                , 6),
                                            )),
                                            new LayoutRow(array(
                                                new LayoutColumn(
                                                    new TextField('Data[AddressPLZCity]', 'PLZ Ort', 'Wohnort')
                                                , 6),
                                                new LayoutColumn(
                                                    new TextField('Data[AddressStreet]', 'Straße, Nr.', 'Straße, Nr.')
                                                , 6),
                                            )),
                                            new LayoutRow(array(
                                                new LayoutColumn('', 6),
                                                new LayoutColumn(
                                                    new TextField('Data[Nationality]', 'Deutsch', 'Staatsangehörigkeit')
                                                , 6),
                                            )),
                                            new LayoutRow(
                                                new LayoutColumn(
                                                    new TextArea('Data[Disease]', '', 'Behinderungen bzw. Krankheiten')
                                                )
                                            ),
                                            new LayoutRow(
                                                new LayoutColumn(
                                                    new TextField('Data[Phone]', 'Telefon', 'Telefon Schüler')
                                                )
                                            )
                                        )))
                                    )),
                                )),
                                new LayoutRow(
                                    new LayoutColumn(
                                        new Title('Eltern/Personensorgeberechtigte')
                                    )
                                ),
                                new LayoutRow(new LayoutColumn(new Well(
                                    new Layout(new LayoutGroup(array(
                                        new LayoutRow(array(
                                            new LayoutColumn(
                                                new TextField('Data[S1][LastFirstName]', 'Name, Vorname', 'S1 Name, Vorname')
                                            ),
                                            new LayoutColumn(
                                                new TextField('Data[S1][Address]', 'PLZ Ort Straße + Nr.', 'S1 Anschrift')
                                            ),
                                            new LayoutColumn(
                                                new TextField('Data[S1][Phone]', 'Telefon', 'S1 Telefon')
                                            ),
                                        )),
                                        new LayoutRow(array(
                                            new LayoutColumn(
                                                new TextField('Data[S2][LastFirstName]', 'Name, Vorname', 'S2 Name, Vorname')
                                            ),
                                            new LayoutColumn(
                                                new TextField('Data[S2][Address]', 'PLZ Ort Straße + Nr.', 'S2 Anschrift')
                                            ),
                                            new LayoutColumn(
                                                new TextField('Data[S2][Phone]', 'Telefon', 'S2 Telefon')
                                            ),
                                        ))
                                    )))
                                ))),
                            ))
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Custom\Gersdorf\Emergency\Create'
        );
    }

    /**
     * @param Stage $Stage
     */
    private static function setButtonList(Stage $Stage)
    {
        $Stage->addButton(new Standard('Schüler', '/Document/Custom/Gersdorf/MetaDataComparison', new \SPHERE\Common\Frontend\Icon\Repository\Person(), array(),
            'Stammdatenabfrage eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];
        if(strpos($Url, '/EnrollmentDocument/Division')){
            $Stage->addButton(new Standard(new Info(new Bold('Klasse')), '/Document/Custom/Gersdorf/MetaDataComparison/Division',
                new PersonGroup(), array(), 'Stammdatenabfrage einer Klasse'));
        } else {
            $Stage->addButton(new Standard('Klasse', '/Document/Custom/Gersdorf/MetaDataComparison/Division', new PersonGroup(),
                array(), 'Stammdatenabfrage einer Klasse'));
        }
    }

    /**
     * @return Stage
     */
    public static function frontendMetaDataComparison(): Stage
    {

        $Stage = new Stage('Stammdatenabfrage', 'Schüler auswählen');
        self::setButtonList($Stage);

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new External('','/Api/Document/Custom/Gersdorf/MetaDataComparison/Create', new Download(),
                            array('Data' => array('Person' => array('Id' => $tblPerson->getId()))), 'Stammdatenabfrage herunterladen')
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
                                    'Division' => 'Klasse',
                                    'Option'   => ''
                                ),
                                array(
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                    ),
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
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public static function frontendMetaDataComparisonDivision(): Stage
    {
        $Stage = new Stage('Stammdatenabfrage', 'Klasse auswählen');
        self::setButtonList($Stage);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
//                    new LayoutRow(new LayoutColumn(
//                        empty($yearButtonList) ? '' : $yearButtonList
//                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
//                            self::loadDivisionTable($filterYearList)
                            self::loadDivisionTable()
                        )
                    )),
                    new Title(new Listing() . ' Übersicht')
                ))
            ));

        return $Stage;
    }

    /**
     * @return Warning|TableData
     */
    public static function loadDivisionTable()
    {
        $TableContent = array();
        $tblYearList = Term::useService()->getYearByNow();
        if(!$tblYearList){
            return new Warning('kein aktuelles Schuljahr verfügbar');
        }
        $tblDivisionAll = array();
        foreach($tblYearList as $tblYear){
            if(($tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear))){
                foreach($tblDivisionList as $tblDivision){
                    $tblDivisionAll[$tblDivision->getId()] = $tblDivision;
                }
            }
        }
        if(empty($tblDivisionAll)){
            return new Warning('aktuelles Schuljahr enthält keine Klassen');
        }

        foreach ($tblDivisionAll as $tblDivision) {
//            // Schuljahre filtern
//            if (!empty($filterYearList)
//                && ($tblYearDivision = $tblDivision->getServiceTblYear())
//                && !isset($filterYearList[$tblYearDivision->getId()]))
//            {
//                continue;
//            }
            $count = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
            $Item['Year'] = '';
            $Item['Division'] = $tblDivision->getDisplayName();
            $Item['Type'] = $tblDivision->getTypeName();
            if ($tblDivision->getServiceTblYear()) {
                $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
            }
            $Item['Count'] = $count;

            if ($count > 0) {
                $Item['Option'] = (new External(
                    '',
                    '/Api/Document/Custom/Gersdorf/MetaDataComparison/Division/CreateMulti',
                    new Download(),
                    array(
                        'DivisionId' => $tblDivision->getId()
                    ),
                    'Stammdatenabfrage herunterladen'
                ))->__toString();
            } else {
                $Item['Option'] = '';
            }

            array_push($TableContent, $Item);
        }

        return new TableData($TableContent, null,
            array(
                'Year' => 'Jahr',
                'Division' => 'Klasse',
                'Type' => 'Schulart',
                'Count' => 'Schüler',
                'Option' => '',
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(1,3)),
                    array('orderable' => false, 'targets'   => -1),
                ),
                'order' => array(
                    array(0, 'desc'),
                    array(2, 'asc'),
                    array(1, 'asc')
                ),
                'responsive' => false
            ));
    }

}