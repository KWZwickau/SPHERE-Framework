<?php

namespace SPHERE\Application\Document\Custom\Hoga;

use DateTime;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Standard\EnrollmentDocument\Frontend;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Hoga
 *
 * @package SPHERE\Application\Document\Custom\Hoga
 */
class Hoga extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/EnrollmentDocument'), new Link\Name('Schulbescheinigung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/EnrollmentDocument', __CLASS__.'::frontendEnrollmentDocument'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/EnrollmentDocument/Fill', __CLASS__.'::frontendFillEnrollmentDocument'
        ));
    }

    /**
     * @return IServiceInterface|void
     */
    public static function useService()
    {
    }

    /**
     * @return IFrontendInterface|void
     */
    public static function useFrontend()
    {
    }

    /**
     * @return Stage
     */
    public static function frontendEnrollmentDocument(): Stage
    {
        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Frontend::getStudentSelectDataTable('/Document/Custom/Hoga/EnrollmentDocument/Fill')
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
    public function frontendFillEnrollmentDocument($PersonId = null): Stage
    {
        $Stage = new Stage('Schulbescheinigung', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Custom/Hoga/EnrollmentDocument', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        $Gender = false;
        $hasTechnicalSubjectArea = false;
        $salutation = 'Frau/Herr';
        if ($tblPerson) {
            $Global->POST['Data']['PersonId'] = $PersonId;
            $Global->POST['Data']['FirstLastName'] = $tblPerson->getFirstSecondName().' '.$tblPerson->getLastName();
            $Global->POST['Data']['Date'] = (new DateTime())->format('d.m.Y');

            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    $Global->POST['Data']['Birthplace'] = $tblCommonBirthDates->getBirthplace();
                    if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $Global->POST['Data']['Gender'] = $Gender = $tblCommonGender->getName();
                        switch ($tblCommonGender->getName()) {
                            case 'Weiblich': $salutation = 'Frau'; break;
                            case 'Männlich': $salutation = 'Herr'; break;
                        }
                    }
                }
            }

            // Prepare LeaveDate
            $Now = new DateTime('now');
            // increase year if date after 31.07.20xx
            if ($Now > new DateTime('31.07.'.$Now->format('Y'))) {
                $Now->add(new \DateInterval('P1Y'));
            }
            $MaxDate = new DateTime('31.07.'.$Now->format('Y'));
            $DateString = $MaxDate->format('d.m.Y');
            $Global->POST['Data']['LeaveDate'] = $DateString;

            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))) {
                // Schuldaten der Schule des Schülers
                if (($tblCompanySchool = $tblStudentEducation->getServiceTblCompany())) {
                    $Global->POST['Data']['School'] = $tblCompanySchool->getName();
                    $Global->POST['Data']['SchoolExtended'] = $tblCompanySchool->getExtendedName();
                    $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                    if ($tblAddressSchool) {
                        $Global->POST['Data']['SchoolAddressStreet'] = $tblAddressSchool->getStreetName().' '.$tblAddressSchool->getStreetNumber();
                        $tblCitySchool = $tblAddressSchool->getTblCity();
                        if ($tblCitySchool) {
                            $Global->POST['Data']['SchoolAddressDistrict'] = $tblCitySchool->getDistrict();
                            $Global->POST['Data']['SchoolAddressCity'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
                            $Global->POST['Data']['Place'] = $tblCitySchool->getName();
                        }
                    }
                }

                if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                    $Global->POST['Data']['Division'] = $tblDivision->getName();
                    $Global->POST['Data']['DivisionId'] = $tblDivision->getId();
                } elseif (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                    $Global->POST['Data']['Division'] = $tblCoreGroup->getName();
                    $Global->POST['Data']['DivisionId'] = $tblCoreGroup->getId();
                }

                if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && $tblSchoolType->isTechnical()
                ) {
                    $hasTechnicalSubjectArea = true;

                    if ($tblStudent
                        && ($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
                        && ($tblTechnicalSubjectArea = $tblStudentTechnicalSchool->getServiceTblTechnicalSubjectArea())
                    ) {
                        $Global->POST['Data']['TechnicalSubjectArea'] = $tblTechnicalSubjectArea->getName();
                    }

                    $Global->POST['Data']['EducationPay'] = $salutation . ' '
                        . $tblPerson->getLastName() . ' erhält keine Ausbildungsvergütung.';
                }
            }

            if ($tblStudent) {
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent, $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $transferDate = $tblStudentTransfer->getTransferDate();
                    if ($transferDate) {
                        // es soll immer das abgangsdatum verwendet werden
//                        if ($MaxDate > new \DateTime($transferDate)) {
                            $DateString = $transferDate;
                            // correct leaveDate if necessary
                            $Global->POST['Data']['LeaveDate'] = $DateString;
//                        }
                    }
                }

                if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('Arrive'))
                    && ($tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                        $tblStudentTransferType))
                ) {
                    $Global->POST['Data']['ArriveDate'] = $tblStudentTransfer->getTransferDate();
                }
            }

            // Hauptadresse Schüler
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
        }
        $Global->savePost();

        $form = $this->formStudentDocument($Gender, $hasTechnicalSubjectArea);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External(
            'Blanko Schulbescheinigung herunterladen',
            '\Api\Document\Custom\Hoga\EnrollmentDocument\Create',
            new Download(), array('Data' => array('empty')),
            'Schulbescheinigung herunterladen'
        ));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $HeadPanel
                            , 12)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $form
                            , 12),
//                        new LayoutColumn(
//                            new Title('Vorlage des Standard-Dokuments "Schulbescheinigung"')
//                            .new Thumbnail(
//                                FileSystem::getFileLoader('/Common/Style/Resource/Document/Schulbescheinigung.PNG')
//                                , ''
//                            )
//                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param $Gender
     * @param $hasTechnicalSubjectArea
     *
     * @return Form
     */
    private function formStudentDocument($Gender, $hasTechnicalSubjectArea): Form
    {
        if ($hasTechnicalSubjectArea) {
            $layoutRow = new LayoutRow(array(
                new LayoutColumn(
                    new TextField('Data[Division]', 'Besucht zur Zeit die Klasse',
                        'Besucht zur Zeit die Klasse')
                    , 3),
                new LayoutColumn(
                    new TextField('Data[ArriveDate]', 'Besucht Schule seit',
                        'Besucht Schule seit')
                    , 3),
                new LayoutColumn(
                    new TextField('Data[TechnicalSubjectArea]', 'Fachrichtung',
                        'Fachrichtung')
                    , 3),
                new LayoutColumn(
                    new TextField('Data[LeaveDate]', 'Voraussichtlich bis',
                        'Voraussichtlich bis')
                    , 3),
                new LayoutColumn(
                    new TextField('Data[EducationPay]', '', 'Bemerkung')
                    , 12)
            ));
        } else {
            $layoutRow = new LayoutRow(array(
                new LayoutColumn(
                    new TextField('Data[Division]', 'Besucht zur Zeit die Klasse',
                        'Besucht zur Zeit die Klasse')
                    , 4),
                new LayoutColumn(
                    new TextField('Data[ArriveDate]', 'Besucht Schule seit',
                        'Besucht Schule seit')
                    , 4),
                new LayoutColumn(
                    new TextField('Data[LeaveDate]', 'Voraussichtlich bis',
                        'Voraussichtlich bis')
                    , 4)
            ));
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')
                    ),
                    new FormColumn(
                        new HiddenField('Data[DivisionId]')
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[School]', 'Schule',
                                                            'Schule')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolExtended]', 'Zusatz',
                                                            'Zusatz')
                                                        , 6)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressDistrict]', 'Ortsteil',
                                                            'Ortsteil')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                                            'PLZ Ort')
                                                        , 4)
                                                ))
                                            ))
                                        )
                                    )),
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
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressDistrict]', 'Ortsteil',
                                                            'Ortsteil')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                                            'Straße, Hausnummer')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        , 2),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'Ort',
                                                            'Ort')
                                                        , 3),
                                                )),
                                                $layoutRow
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Dokument')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[Place]', 'Ort', 'Ort')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new DatePicker('Data[Date]', 'Datum', 'Datum')
                                                        , 6)
                                                ))
                                            )
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                )),
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Custom\Hoga\EnrollmentDocument\Create'
        );
    }
}