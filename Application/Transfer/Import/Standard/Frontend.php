<?php
namespace SPHERE\Application\Transfer\Import\Standard;

use DateTime;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Icon;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\Tharandt
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStudentImport($File = null, $Data = null)
    {

        $Stage = new Stage('Import', 'Standard für Schüler');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
        $Stage->addButton(
            new External('Importvorlage', '/Api/Transfer/Standard/DownloadTemplateStudent',
                new Download(), array(), false)
        );

        $Now = new DateTime();
        $Year = (int)$Now->format('Y');
        $YearShort = (int)$Now->format('y');
        $YearList = array(
            ($Year - 2).'/'.($YearShort - 1) => ($Year - 2).'/'.($YearShort - 1),
            ($Year - 1).'/'.$YearShort => ($Year - 1).'/'.$YearShort,
            $Year.'/'.($YearShort + 1) => $Year.'/'.($YearShort + 1),
            ($Year + 1).'/'.($YearShort + 2) => ($Year + 1).'/'.($YearShort + 2),
            );

        if((new DateTime())->format('m') < 8) {
            $_POST['Data']['Year'] = ($Year - 1).'/'.$YearShort;
        } else {
            $_POST['Data']['Year'] = $Year.'/'.($YearShort + 1);
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                ImportStandard::useService()->createStudentsFromFile(
                                    new Form(new FormGroup(new FormRow(array(
                                        new FormColumn(
                                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                array('showPreview' => false))
                                            , 8),
                                        new FormColumn(
                                            new SelectBox('Data[Year]', 'Für welches Schuljahr gilt der Import', $YearList, null, false)
                                            , 4)
                                    ))), new Primary('Hochladen'))
                                    , $File, $Data
                                )
                                .new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendInterestedImport($File = null)
    {

        $Stage = new Stage('Import', 'Standard für Interessenten');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
            $Stage->addButton(
                new External('Importvorlage', '/Api/Transfer/Standard/DownloadTemplateInteressent',
                    new Download(), array(), false)
            );
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                ImportStandard::useService()->createInterestedFromFile(
                                    new Form(new FormGroup(new FormRow(new FormColumn(
                                        new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                            array('showPreview' => false))
                                    ))), new Primary('Hochladen'))
                                    , $File
                                )
                                .new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendStuffImport($File = null)
    {

        $Stage = new Stage('Import', 'Standard für Mitarbeiter/Lehrer');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
        $Stage->addButton(
            new External('Importvorlage', '/Api/Transfer/Standard/DownloadTemplateTeacher',
                new Download(), array(), false)
        );
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                ImportStandard::useService()->createStaffFromFile(
                                    new Form(new FormGroup(new FormRow(new FormColumn(
                                        new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                            array('showPreview' => false))
                                    ))), new Primary('Hochladen'))
                                    , $File
                                )
                                .new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendCompanyImport($File = null)
    {

        $Stage = new Stage('Import', 'Standard für Institutionen');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
        $Stage->addButton(
            new External('Importvorlage', '/Api/Transfer/Standard/DownloadTemplateSchool',
            new Download(), array(), false)
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                ImportStandard::useService()->createCompanyFromFile(
                                    new Form(new FormGroup(new FormRow(new FormColumn(
                                        new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                            array('showPreview' => false))
                                    ))), new Primary('Hochladen'))
                                    , $File
                                )
                                .new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $Stage;
    }
}