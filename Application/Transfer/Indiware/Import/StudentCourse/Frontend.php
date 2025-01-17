<?php

namespace SPHERE\Application\Transfer\Indiware\Import\StudentCourse;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param $File
     * @param $Data
     *
     * @return Stage
     */
    public function frontendUpload($File = null, $Data = null): Stage
    {
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $YearId = null;
        if(($tblYearListNow = Term::useService()->getYearByNow())){
            // Vorauswahl nur wenn das Jahr eindeutig ist
            if(count($tblYearListNow) == 1){
                $YearId = current($tblYearListNow)->getId();
            }
        }

        $Global = $this->getGlobal();
        $Global->POST['Data']['YearId'] = $YearId;
        $Global->savePost();

        if (!($tblYearList = Term::useService()->getYearAllSinceYears(1))) {
            $tblYearList = array();
        }

        $tblImportList = false;
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            $tblImportList = Education::useService()->getImportByAccountAndExternSoftwareNameAndTypeIdentifier(
                $tblAccount, TblImport::EXTERN_SOFTWARE_NAME_INDIWARE, TblImport::TYPE_IDENTIFIER_STUDENT_COURSE
            );
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ($tblImportList
                                ? new WarningMessage(new WarningIcon().' Vorsicht vorhandene Importdaten werden entfernt!') : '')
                            , 6, array(LayoutColumn::GRID_OPTION_HIDDEN_SM)
                        )),
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            StudentCourse::useService()->createStudentCourseFromFile(
                                new Form(
                                    new FormGroup(array(
                                        new FormRow(
                                            new FormColumn(
                                                new Panel(
                                                    'Import',
                                                    array(
                                                        (new SelectBox(
                                                            'Data[YearId]',
                                                            'Schuljahr auswählen',
                                                            array('{{ Year }} {{ Description }}' => $tblYearList)
                                                        ))->setRequired(),
                                                        (new FileUpload(
                                                            'File',
                                                            'Datei auswählen',
                                                            'Datei auswählen ' . new ToolTip(new InfoIcon(), 'Schueler.csv'),
                                                            null,
                                                            array('showPreview' => false)
                                                        ))->setRequired()
                                                    ),
                                                    Panel::PANEL_TYPE_INFO
                                                )
                                            )
                                        ),
                                    )),
                                    new Primary('Hochladen und Voransicht', new Upload()),
                                ),
                                $File,
                                $Data
                            )
                        ), 6)
                    )
                ), new TitleLayout('Schülerkurse', 'importieren'))
            )
        );

        return $Stage;
    }

    /**
     * @param null $ImportId
     * @param string $Tab
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendShow($ImportId = null, string $Tab = 'Schüler', $Data = null): Stage
    {
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
        $Stage->setMessage('Importvorbereitung / Daten überprüfen und mappen');

        if (($tblImport = Education::useService()->getImportById($ImportId))) {
            $content = Education::useFrontend()->getStudentCourseContent($tblImport, $Tab, $Data);
        } else {
            $content = (new Danger('Der Import wurde nicht gefunden', new Exclamation()));
        }
        $Stage->setContent($content);

        return $Stage;
    }

    /**
     * @param $Confirm
     *
     * @return Stage|string
     */
    public function frontendStudentCourseDestroy($Confirm = null)
    {
        $Stage = new Stage('Importvorbereitung', 'Leeren');
        $Stage->setMessage('Hierbei werden alle nicht importierte Daten der letzten Importvorbereitung gelöscht.');

        $tblAccount = Account::useService()->getAccountBySession();
        $tblImport = Education::useService()->getImportByAccountAndExternSoftwareNameAndTypeIdentifier(
            $tblAccount, TblImport::EXTERN_SOFTWARE_NAME_INDIWARE, TblImport::TYPE_IDENTIFIER_STUDENT_COURSE
        );
        if (!$tblImport) {
            $Stage->setContent(new Warning('Keine Restdaten eines Import\s vorhanden'));

            return $Stage . new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR);
        }

        if (!$Confirm) {

            $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question() . ' Vorhandene Importvorbereitung der Schüler-Kurse wirklich löschen? ',
                        array(
                            'Schuljahr: ' . (($tblYear = $tblImport->getServiceTblYear()) ? $tblYear->getDisplayName() : ''),
                            'Dateiname: ' . $tblImport->getFileName()
                        ),
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Transfer/Indiware/Import/StudentCourse/Destroy', new Ok(),
                            array('Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Transfer/Indiware/Import', new Disable()
                        )
                    )
                    , 6))))
            );
        } else {
            // Destroy Import
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            (Education::useService()->destroyImport($tblImport)
                                ? new SuccessMessage('Der Import ist nun leer')
                                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_SUCCESS)
                                : new WarningMessage('Der Import konnte nicht vollständig gelöscht werden')
                                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR)
                            )
                        ))
                    ))
                )
            );
        }

        return $Stage;
    }

    /**
     * @param null $File
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendImportSelectedCourse($File = null, $Data = null) : Stage
    {
        $stage = new Stage('Import', 'Abitur Kurseinbringung und Prüfungsnoten');
        $stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));

        $list = array();
        $tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy');

        if (($tblYearList = Term::useService()->getYearByNow())) {
//        if (($tblYearList = Term::useService()->getYearAllByDate(new \DateTime('01.06.2024')))) {
            foreach ($tblYearList as $tblYear) {
                if (($tblGenerateCertificateList = Generate::useService()->getGenerateCertificateAllByYear($tblYear))) {
                    foreach ($tblGenerateCertificateList as $tblGenerateCertificate) {
                        if (($tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType())
                            && $tblCertificateType->getIdentifier() == 'DIPLOMA'
                        ) {
                            if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                                foreach ($tblPrepareList as $tblPrepare) {
                                    if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
                                        && ($tblSchoolTypeListByGenerateCertificate = $tblDivisionCourse->getSchoolTypeListFromStudents())
                                        && isset($tblSchoolTypeListByGenerateCertificate[$tblSchoolTypeGy->getId()])
                                    ) {
                                        $list[] = $tblGenerateCertificate;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($list)) {
            $stage->setContent(
                new \SPHERE\Common\Frontend\Message\Repository\Warning('Es wurde im aktuellen Schuljahr kein Zeugnisauftrag
                 für Abiturzeugnisse gefunden!<br /> Bitte legen Sie erst unter: Bildung->Zeugnisse->Zeugnisse generieren, einen
                 Zeugnisauftrag für die 12. Klasse Gymnasium an.<br /> Danach können Sie die Kurseinbringung aus Indiware in die
                 Schulsoftware importieren.')
            );
        } else {
            $stage->setContent(
                new Well(
                    StudentCourse::useService()->createSelectedCourseFromFile(
                        new Form(new FormGroup(array(
                            new FormRow(array(
                                new FormColumn(
                                    (new FileUpload(
                                        'File',
                                        'Datei auswählen',
                                        'Datei auswählen ' . new ToolTip(new InfoIcon(), 'Schueler.csv'),
                                        null,
                                        array('showPreview' => false)
                                    ))->setRequired()
                                )
                            )),
                            new FormRow(array(
                                new FormColumn(
                                    (new SelectBox('Data[GenerateCertificateId]', 'Zeugnisauftrag',
                                        array('{{ Name }}' => $list)
                                    ))->setRequired()
                                ),
                            )),
                        )), new Primary('Hochladen')),
                        $File,
                        $Data
                    )
                )
            );
        }

        return $stage;
    }
}