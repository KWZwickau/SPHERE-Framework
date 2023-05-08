<?php

namespace SPHERE\Application\Transfer\Indiware\Import\StudentCourse;

use SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\ApiAppointmentGrade;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Indiware\Import\Lectureship\Lectureship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
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
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
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

        // todo wird die Schulart und Periode überhaupt benötigt

        $SchoolType = array();
        $tblType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
        $PreselectId = $tblType->getId();
        $SchoolType[$tblType->getId()] = $tblType->getName();
        $tblType = Type::useService()->getTypeByName(TblType::IDENT_BERUFLICHES_GYMNASIUM);
        $SchoolType[$tblType->getId()] = $tblType->getName();
        $ReceiverPeriod = ApiAppointmentGrade::receiverFormSelect((new ApiAppointmentGrade())->reloadPeriodSelect($PreselectId), 'Period');

        $Global = $this->getGlobal();
        $Global->POST['Data']['YearId'] = $YearId;
        $Global->POST['Data']['SchoolTypeId'] = $PreselectId;
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
                                                        (new SelectBox(
                                                            'Data[SchoolTypeId]',
                                                            'Schulart',
                                                            $SchoolType,
                                                            null,
                                                            false,
                                                            null
                                                        ))->ajaxPipelineOnChange(ApiAppointmentGrade::pipelineCreatePeriodSelect($ReceiverPeriod))->setRequired(),
                                                        $ReceiverPeriod,
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
}