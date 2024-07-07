<?php

namespace SPHERE\Application\Document\Standard\SignOutCertificate;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Standard\EnrollmentDocument\EnrollmentDocument;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
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
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    private static function setButtonList(Stage $Stage)
    {
        $Stage->addButton(new Standard('Schüler', '/Document/Standard/SignOutCertificate', new \SPHERE\Common\Frontend\Icon\Repository\Person(), array(), 'Abmeldebescheinigung eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];
        if(strpos($Url, '/SignOutCertificate/Division')){
            $Stage->addButton(new Standard(new Info(new Bold('Kurs')), '/Document/Standard/SignOutCertificate/Division', new PersonGroup(),
                array(), 'Abmeldebescheinigung eines Kurses'));
        } else {
            $Stage->addButton(new Standard('Kurs', '/Document/Standard/SignOutCertificate/Division', new PersonGroup(),
                array(), 'Abmeldebescheinigung eines Kurses'));
        }
    }

    /**
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public static function frontendSelectDivision(bool $IsAllYears = false, ?string $YearId = null): Stage
    {
        $Stage = new Stage('Abmeldebescheinigung', 'Kurs auswählen');
        self::setButtonList($Stage);

        list($yearButtonList, $filterYearList)
            = Term::useFrontend()->getYearButtonsAndYearFilters('/Document/Standard/SignOutCertificate/Division', $IsAllYears, $YearId);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(
                        empty($yearButtonList) ? '' : $yearButtonList
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            EnrollmentDocument::useFrontend()->loadDivisionTable($filterYearList, 'SignOutCertificate')
                        )
                    )), new Title(new Listing() . ' Übersicht')
                ))
            ));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Data
     *
     * @return string
     */
    public function frontendDivisionInput($Id = null, $Data = null): string
    {
        $Stage = new Stage('Abmeldebescheinigung', 'Erstellen für Kurs');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/SignOutCertificate/Division', new ChevronLeft()));
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($Id))) {
            $global = $this->getGlobal();
            $global->POST['Data']['Date'] = (new DateTime('now'))->format('d.m.Y');
            $global->savePost();

            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Kurs', $tblDivisionCourse->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            , 6),
                        new LayoutColumn(
                            new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                            , 6)
                    ))
                )))
                . new Well(
                    new Form(
                        new FormGroup(new FormRow(array(
                            new FormColumn(
                                new DatePicker('Data[Date]', '', 'Datum der Ausstellung (Dokument - Datum)', new Calendar())
                                , 6)
                        ))),
                        new Primary('Download', new Download(), true),
                        '/Api/Document/Standard/SignOutCertificate/CreateMulti',
                        array('DivisionCourseId' => $tblDivisionCourse->getId())
                    )
                )
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Kurs wurde nicht gefunden', new Exclamation())
                . new Redirect('/Document/Standard/SignOutCertificate/Division', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @return Stage
     */
    public static function frontendSignOutCertificate(): Stage
    {
        $Stage = new Stage('Abmeldebescheinigung', 'Schüler auswählen');
        self::setButtonList($Stage);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            \SPHERE\Application\Document\Standard\EnrollmentDocument\Frontend::getStudentSelectDataTable('/Document/Standard/SignOutCertificate/Fill')
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
    public function frontendFillSignOutCertificate($PersonId = null): Stage
    {
        $Stage = new Stage('Abmeldebescheinigung', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/SignOutCertificate', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        if ($tblPerson) {
            $Data = SignOutCertificate::useService()->getSignOutCertificateData($tblPerson);
            $Global->POST['Data'] = $Data;
        }
        $Global->savePost();

        $form = $this->formSignOut();

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External(
            'Blanko Abmeldebescheinigung herunterladen',
            'SPHERE\Application\Api\Document\Standard\SignOutCertificate\Create',
            new Download(),
            array('Data' => array('empty')),
            'Abmeldebescheinigung herunterladen'
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
                            new Title('Vorlage des Standard-Dokuments "Abmeldebescheinigung"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/SignOutCertificate_V2.png')
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
     * @return Form
     */
    private function formSignOut(): Form
    {
        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')   //ToDO Hidden ersetzen
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Abgebende Schule:')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[School1]', 'Name',
                                                            'Name')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[School2]', 'Zusatz',
                                                            'Zusatz')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Nr.')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                                            'PLZ Ort')
                                                        , 6),
                                                ))
                                            )
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
                                                        new TextField('Data[FirstLastName]', 'Name',
                                                            'Vor- und Zuname')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[BirthDate]', 'Geboren am', 'Geboren am')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[BirthPlace]', 'Geboren in', 'Geboren in')
                                                        , 6),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße Nr.',
                                                            'Straße Nr.')
                                                    )
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'PLZ Ort', 'PLZ Ort')
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolEntry]', 'Datum', 'Schulbesuch von')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolUntil]', 'Datum', 'Schulbesuch bis')
                                                        , 6)
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[PlaceDate]', 'Ort, Datum', 'Ort, Datum')
                                                    )
                                                ),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Aufnehmende Schule:')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchool1]', 'Name',
                                                            'Name')
                                                    ),
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchool2]', 'Zusatz',
                                                            'Zusatz')
                                                    ),
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Nr.')
                                                    ),
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchoolAddressCity]', 'PLZ Ort',
                                                            'PLZ Ort')
                                                    ),
                                                ))
                                            )
                                        )
                                    )),
                                ))
                            )
                        )
                    )
                )),

//                new FormRow(array(
//                    new FormColumn(
//                        ApiSignOutCertificate::receiverService(ApiSignOutCertificate::pipelineButtonRefresh($PersonId))
//                    )
//                ))
            )), new Primary('Download', new Download(), true),
            '\Api\Document\Standard\SignOutCertificate\Create' //, array('PersonId' => 15)
        );
    }
}