<?php
namespace SPHERE\Application\Document\Standard\StaffAccidentReport;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Sup;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class StaffAccidentReport
 *
 * @package SPHERE\Application\Document\Standard\StaffAccidentReport
 */

class StaffAccidentReport extends Extension
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__), new Link\Name('Unfallanzeige Mitarbeiter')));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__, __CLASS__.'::frontendSelectTeacher'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/Fill', __CLASS__.'::frontendFillAccidentReportTeacher'));
    }

    public static function frontendSelectTeacher()
    {

        $Stage = new Stage('Unfallanzeige', 'Mitarbeiter auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$dataList) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('Id' => $tblPerson->getId()))
                    );
                });
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
                                    'Option'   => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        array('width' => '1%', 'targets' => -1),
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

    public function frontendFillAccidentReportTeacher($Id = null)
    {

        $Stage = new Stage('Unfallanzeige', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/StaffAccidentReport', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($Id);
        $Global = $this->getGlobal();

        // Sachsen Standard
        $Global->POST['Data']['AddressTarget'] = 'VBG-Bezirksverwaltung Dresden';
        $Global->POST['Data']['TargetAddressStreet'] = 'Wiener Platz 6';
        $Global->POST['Data']['TargetAddressCity'] = '01069 Dresden';
//        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
//        // ist Adresse der Schüler gewesen
//            $Global->POST['Data']['AddressTarget'] = 'Unfallkasse Berlin';
//            $Global->POST['Data']['TargetAddressStreet'] = 'Culemeyerstraße 2';
//            $Global->POST['Data']['TargetAddressCity'] = '12277 Berlin';
//        }

        if($tblPerson) {
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastFirstName();
            $Global->POST['Data']['Date'] = (new DateTime())->format('d.m.Y');
            $Global->POST['Data']['Gender'] = $tblPerson->getGenderString();
            $Global->POST['Data']['Birthday'] = $tblPerson->getBirthday();
            if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if(($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                    $Global->POST['Data']['Nationality'] = $tblCommonInformation->getNationality();
                }
            }

            $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
            if ($tblResponsibilityList) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = current($tblResponsibilityList);
                $Global->POST['Data']['CompanyNumber'] = $tblResponsibility->getCompanyNumber();
//                $tblResponsibilityCompany = $tblResponsibility->getServiceTblCompany();
//                if ($tblResponsibilityCompany) {
//                    $Global->POST['Data']['SchoolResponsibility'] = $tblResponsibilityCompany->getDisplayName();
//                }
            }

            // Hauptadresse Person
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['AddressStreet'] = $tblAddress->getStreetName().', '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if ($tblCity) {
                    $Global->POST['Data']['AddressPLZ'] = $tblCity->getCode();
                    $Global->POST['Data']['AddressCity'] = $tblCity->getDisplayName();
                }
            }

        }
        $Global->savePost();
        $form = $this->formPersonTransfer();
        $HeadPanel = new Panel('Mitarbeiter/Lehrer', $tblPerson->getLastFirstName());
        $Stage->addButton(new External('Blanko Unfallanzeige herunterladen', 'SPHERE\Application\Api\Document\Standard\StaffAccidentReport\Create',
            new Download(), array('Data' => array('empty')), 'Unfallanzeige herunterladen'));

        $Stage->setContent(new Layout(new LayoutGroup(array(
            new LayoutRow(
                new LayoutColumn($HeadPanel, 7)
            ),
            new LayoutRow(array(
                new LayoutColumn($form, 7),
                new LayoutColumn(
                    new Title('Vorlage des Standard-Dokuments "Unfallanzeige"')
                    .new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Document/AccidentReportStaff.png'), '')
                , 5),
            ))
        ))));
        return $Stage;
    }

    /**
     * @return Form
     */
    private function formPersonTransfer()
    {
        return new Form(new FormGroup(new FormRow(new FormColumn(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Title('Einrichtung')
                ),
                new LayoutColumn(new Well(
                    new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[School]', 'Schule',
                                        new Sup(1).' Schule')
                                    , 6),
                                new LayoutColumn(
                                    new TextField('Data[SchoolExtended]', 'Zusatz',
                                        new Sup(1).' Zusatz')
                                    , 6)
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                        new Sup(1).' Straße Hausnummer')
                                    , 6),
                                new LayoutColumn(
                                    new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                        new Sup(1).' PLZ Ort')
                                    , 6)
                            ))
                        ))
                    )
                )),
                new LayoutColumn(
                    new Title('Informationen Träger')
                ),
                new LayoutColumn(new Well(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[CompanyNumber]', 'Unternehmensnr.',
                                        new Sup(2).' Unternehmensnummer des Unfallversicherungsträgers')
                                    , 6)
                            ))
                        )
                    )
                )),
                new LayoutColumn(
                    new Title('Empfänger')
                ),
                new LayoutColumn(new Well(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[AddressTarget]', 'Empfänger',
                                        new Sup(3).' Empfänger')
                                    , 4),
                                new LayoutColumn(
                                    new TextField('Data[TargetAddressStreet]', 'Straße Hausnummer',
                                        new Sup(3).' Straße Hausnummer')
                                    , 4),
                                new LayoutColumn(
                                    new TextField('Data[TargetAddressCity]', 'PLZ Ort',
                                        new Sup(3).' PLZ Ort')
                                    , 4)
                            ))
                        )
                    )
                )),
                new LayoutColumn(
                    new Title('Informationen Versicherter')
                ),
                new LayoutColumn(new Well(
                    new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[LastFirstName]', 'Name, Vorname',
                                        new Sup(4).' Name, Vorname des Mitarbeiters/der Mitarbeiterin')
                                    , 8),
                                new LayoutColumn(
                                    new TextField('Data[Birthday]', 'Geburtstag',
                                        new Sup(5).' Geburtstag')
                                    , 4),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                        new Sup(6).' Straße, Hausnummer')
                                    , 6),
                                new LayoutColumn(
                                    new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                        'Postleitzahl')
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[AddressCity]', 'Ort',
                                        'Ort')
                                    , 3),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Bold(new Sup(7).' Geschlecht').
                                    new Listing(array(
                                        new RadioBox('Data[Gender]', 'Männlich',
                                            'Männlich'),
                                        new RadioBox('Data[Gender]', 'Weiblich',
                                            'Weiblich')
                                    ))
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[Nationality]', 'Staatsangehörigkeit',
                                        new Sup(8).' Staatsangehörigkeit')
                                    , 3),
                                new LayoutColumn(
                                    new PullClear(new Bold(new Sup(9).' Leiharbeitnehmer/in?')).
                                    new PullLeft(new CheckBox('Data[TemporaryWorkYes]',
                                        'Ja &nbsp;&nbsp;&nbsp;&nbsp;', true)).
                                    new PullLeft(new CheckBox('Data[TemporaryWorkNo]',
                                        'Nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                    , 3),
                                new LayoutColumn(
                                    new PullClear(new Bold(new Sup(10).' Auszubildender?')).
                                    new PullLeft(new CheckBox('Data[ApprenticeYes]',
                                        'Ja &nbsp;&nbsp;&nbsp;&nbsp;', true)).
                                    new PullLeft(new CheckBox('Data[ApprenticeNo]',
                                        'Nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                    , 3),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Bold(new Sup(11).' Versicherte Person ist:').
                                    new Listing(array(
                                        new RadioBox('Data[MartialStatusEmployer]', 'Unternehmer',
                                            'Unternehmer'),
                                        new RadioBox('Data[MartialStatusFamily]', 'mit Unternehmer verwandt',
                                            'mit Unternehmer verwandt'),
                                        new RadioBox('Data[MartialStatusSpouse]', 'Ehegatte des Unternehmers',
                                            'Ehegatte des Unternehmers'),
                                        new RadioBox('Data[MartialStatusManager]', 'Gesellschafter oder Geschäftsführer',
                                            'Gesellschafter/Geschäftsführer'),
                                    ))
                                    , 5),
                                new LayoutColumn(
                                    new TextField('Data[ContinuePayment]', '',
                                        new Sup(12). ' Anspruch auf Entgeltfortzahlung in Wochen:')
                                    , 7),
                                new LayoutColumn(
                                    new TextField('Data[HealthInsurance]', 'Krankenkasse',
                                        new Sup(13).' Krankenkasse')
                                    , 7),
                            )),
                        ))
                    )
                )),
                new LayoutColumn(
                    new Title('Informationen Unfall')
                ),
                new LayoutColumn(new Well(
                    new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                new LayoutColumn(
                                    new PullClear(new Bold(new Sup(14).' Tödlicher Unfall?')).
                                    new PullLeft(new CheckBox('Data[DeathAccidentYes]',
                                        'Ja &nbsp;&nbsp;&nbsp;&nbsp;', true)).
                                    new PullLeft(new CheckBox('Data[DeathAccidentNo]',
                                        'Nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                    , 3)
                            ),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[AccidentDate]',
                                        (new DateTime())->format('d.m.Y'),
                                        new Sup(15).' Datum des Unfalls')
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[AccidentHour]', 'Stunde',
                                        new Sup(15).' Stunde Unfallzeitpunkt')
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[AccidentMinute]', 'Minute',
                                        new Sup(15).' Minute Unfallzeitpunkt')
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[AccidentPlace]', 'Ort',
                                        new Sup(16).' Unfallort')
                                    , 3),
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    new TextArea('Data[AccidentDescription]', 'Beschreibung',
                                        new Sup(17).' Ausführliche Beschreibung des Unfallhergangs')
                                )
                            ),
                            new LayoutRow(
                                new LayoutColumn(
                                    new PullLeft(new Header(new Bold(new Sup(17).' Die Angaben beruhen auf der Schilderung &nbsp;&nbsp;&nbsp;&nbsp;')))
                                    .new Container(
                                        new PullLeft(new CheckBox('Data[DescriptionActive]',
                                            'des Versicherten &nbsp;&nbsp;&nbsp;&nbsp;'
                                            , true))
                                        .new PullLeft(new CheckBox('Data[DescriptionPassive]',
                                            'andere Personen'
                                            , true))
                                    )
                                )
                            ),
                            new LayoutRow(
                                new LayoutColumn(
                                    new Title('')
                                )
                            ),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[AccidentBodyParts]',
                                        'Kopf, Bein, Arm, etc.',
                                        new Sup(18).' Verletzte Körperteile')
                                    , 6),
                                new LayoutColumn(
                                    new TextField('Data[AccidentType]', 'Art',
                                        new Sup(19).' Art der Verletzung')
                                    , 6),
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    new Title('')
                                )
                            ),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[WitnessInfo]', 'Name, Vorname Adresse',
                                        new Sup(20).' Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift des Zeugen)')
                                    , 9),
                                new LayoutColumn(
                                    new Bold( new Sup(20).' War diese Person Augenzeuge?')
                                    .new PullClear(
                                        new PullLeft(new CheckBox('Data[EyeWitnessYes]',
                                            'ja &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                        .new PullLeft(new CheckBox('Data[EyeWitnessNo]',
                                            'nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                    )
                                    , 3),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[Doctor]', 'Name',
                                        new Sup(21).' Name des erstbehandelnden Arztes / Krankenhaus')
                                    .new TextField('Data[DoctorAddress]', 'Adresse',
                                        new Sup(21).' Adresse des erstbehandelnden Arztes / Krankenhaus')
                                    , 4),
                                new LayoutColumn(
                                    new Header(new Bold(new Sup(22).' Beginn des Besuchs der Einrichtung'))
                                    .new TextField('Data[LocalStartHour]', 'Stunde', 'Stunde')
                                    .new TextField('Data[LocalStartMinute]', 'Minute', 'Minute')
                                    , 4),
                                new LayoutColumn(
                                    new Header(new Bold(new Sup(22).' Ende des Besuchs der Einrichtung'))
                                    .new TextField('Data[LocalEndHour]', 'Stunde', 'Stunde')
                                    .new TextField('Data[LocalEndMinute]', 'Minute', 'Minute')
                                    , 4),
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    new Title('')
                                )
                            ),

                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[WorkAtAccident]', 'Beschäftigung',
                                        new Sup(23).' Zum Unfallzeitpunkt beschäftigt als:')
                                    , 4),
                                new LayoutColumn(
                                    new TextField('Data[LocationSince]', 'Tätigkeit',
                                        new Sup(24).' Seit wann in dieser Tätigkeit?')
                                    , 4),
                                new LayoutColumn(
                                    new TextField('Data[WorkArea]', 'Teil des Unternehmens',
                                        new Sup(25).' In welchem Teil des Unternehmens tätig')
                                    , 4),
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    new Title('')
                                )
                            ),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Bold(new Sup(26).' Hat der Versicherte den Besuch der Einrichtung unterbrochen?')
                                    .new PullClear(
                                        new PullLeft(new CheckBox('Data[BreakNo]',
                                            'nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                        .new PullLeft(new CheckBox('Data[BreakYes]',
                                            'sofort &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                        .new PullLeft(new CheckBox('Data[BreakAt]',
                                            'später am &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                    )
                                    , 6),
                                new LayoutColumn(
                                    new TextField('Data[BreakDate]',
                                        (new DateTime())->format('d.m.Y'),
                                        'Datum der Unterbrechung')
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[BreakHour]', 'Stunde',
                                        'Zeitpunkt der Unterbrechung')
                                    , 3)
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    new Title('')
                                )
                            ),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Bold(new Sup(27).' Hat der Versicherte den Besuch der Einrichtung wieder aufgenommen?')
                                    .new PullClear(
                                        new PullLeft(new CheckBox('Data[ReturnNo]',
                                            'nein &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                        .new PullLeft(new CheckBox('Data[ReturnYes]',
                                            'ja, am &nbsp;&nbsp;&nbsp;&nbsp;', true))
                                    )
                                    , 8),
                                new LayoutColumn(
                                    new TextField('Data[ReturnDate]',
                                        (new DateTime())->format('d.m.Y'),
                                        new Sup(28).' Datum der Wiederaufnahme')
                                    , 4),
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    new Title('')
                                )
                            ),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new TextField('Data[Date]', (new DateTime())->format('d.m.Y'),
                                        'Datum')
                                    , 2),
                                new LayoutColumn(
                                    new TextField('Data[LocalLeader]', 'Leiter',
                                        'Leiter (Beauftragter) der Einrichtung')
                                    , 4),
                                new LayoutColumn(
                                    new TextField('Data[Council]', 'Betriebsrat',
                                        'Betriebsrat (Personalrat)')
                                    , 3),
                                new LayoutColumn(
                                    new TextField('Data[Recall]', 'Telefonnummer',
                                        'Telefon-Nr. für Rückfragen (Ansprechpartner)')
                                    , 3),

                            )),
                        ))
                    )
                ))
            ))))
        )),), new Primary('Download', new Download(), true), '\Api\Document\Standard\StaffAccidentReport\Create');
    }

}

