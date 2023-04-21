<?php
namespace SPHERE\Application\Document\Custom\Gersdorf;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType as TblTypePhone;
use SPHERE\Application\Document\Standard\EnrollmentDocument\Frontend;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
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
    public static function frontendEmergency(): Stage
    {
        $Stage = new Stage('Notarzt', 'Schüler auswählen');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Frontend::getStudentSelectDataTable('/Document/Custom/Gersdorf/Emergency/Fill')
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
    public function frontendFillEmergency($PersonId = null): Stage
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

        $Stage->addButton(new External(
            'Blanko Notarzt Dokument herunterladen',
            'SPHERE\Application\Api\Document\Custom\Gersdorf\Emergency\Create',
            new Download(), array('Data' => array('empty')),
            'Notarzt Dokument herunterladen'
        ));

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
    private function formEmergencyDocument($Gender): Form
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
        $Stage->addButton(new Standard('Schüler', '/Document/Custom/Gersdorf/MetaDataComparison', new \SPHERE\Common\Frontend\Icon\Repository\Person(),
            array(), 'Stammdatenabfrage eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];
        if(strpos($Url, '/EnrollmentDocument/Division')){
            $Stage->addButton(new Standard(new Info(new Bold('Kurs')), '/Document/Custom/Gersdorf/MetaDataComparison/Division', new PersonGroup(),
                array(), 'Stammdatenabfrage eines Kurses'));
        } else {
            $Stage->addButton(new Standard('Kurs', '/Document/Custom/Gersdorf/MetaDataComparison/Division', new PersonGroup(),
                array(), 'Stammdatenabfrage eines Kurses'));
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
        $showDivision = false;
        $showCoreGroup = false;
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                $displayDivision = '';
                $displayCoreGroup = '';
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if (($tblDivision = $tblStudentEducation->getTblDivision())
                        && ($displayDivision = $tblDivision->getName())
                    ) {
                        $showDivision = true;
                    }
                    if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                        && ($displayCoreGroup = $tblCoreGroup->getName())
                    ) {
                        $showCoreGroup = true;
                    }
                }
                $tblAddress = $tblPerson->fetchMainAddress();
                $dataList[] = array(
                    'Name'     => $tblPerson->getLastFirstName(),
                    'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                    'Division' => $displayDivision,
                    'CoreGroup' => $displayCoreGroup,
                    'Option'   => new External('','/Api/Document/Custom/Gersdorf/MetaDataComparison/Create', new Download(),
                        array('Data' => array('Person' => array('Id' => $tblPerson->getId()))), 'Stammdatenabfrage herunterladen')
                );
            }
        }

        $columnList['Name'] = 'Name';
        $columnList['Address'] = 'Adresse';
        if ($showDivision) {
            $columnList['Division'] = 'Klasse';
        }
        if ($showCoreGroup) {
            $columnList['CoreGroup'] = 'Stammgruppe';
        }
        $columnList['Option'] = '';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                $columnList,
                                array(
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        array('orderable' => false, 'width' => '30px', 'targets' => -1),
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
     * @return Stage
     */
    public static function frontendMetaDataComparisonDivision(): Stage
    {
        $Stage = new Stage('Stammdatenabfrage', 'Kurs auswählen');
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
        $dataList = array();
        $tblDivisionCourseList = array();
        $tblYearList = Term::useService()->getYearByNow();
        if(!$tblYearList){
            return new Warning('kein aktuelles Schuljahr verfügbar');
        }
        foreach($tblYearList as $tblYear){
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
        }
        if(empty($tblDivisionCourseList)){
            return new Warning('aktuelles Schuljahr enthält keine Klassen');
        }

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $count = $tblDivisionCourse->getCountStudents();
            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Count' => $count,
                'Option' => $count > 0
                    ? (new External(
                        '',
                        '/Api/Document/Custom/Gersdorf/MetaDataComparison/Division/CreateMulti',
                        new Download(),
                        array(
                            'DivisionCourseId' => $tblDivisionCourse->getId()
                        ),
                        'Stammdatenabfrage herunterladen'
                    ))->__toString()
                    : ''
            );
        }

        return new TableData($dataList, null,
            array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'DivisionCourseType' => 'Kurs-Typ',
                'SchoolTypes' => 'Schularten',
                'Count' => 'Schüler',
                'Option' => '',
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
                'responsive' => false
            ));
    }

}