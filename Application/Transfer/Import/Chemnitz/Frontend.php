<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Select
     * @return Stage
     */
    public function frontendStudent($Select = null)
    {

        $View = new Stage();
        $View->setTitle('ESZC Import');
        $View->setDescription('Schülerdaten');

        $tblDivisionAll = Division::useService()->getDivisionAll();

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(array(
                        Chemnitz::useService()->getClass(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(array(
                                        new FormColumn(
                                            new SelectBox('Select[Division]', 'Klasse',
                                                array('{{serviceTblYear.Name}} - {{tblLevel.serviceTblType.Name}} - {{Name}}' => $tblDivisionAll)),
                                            12
                                        )
                                    )),
                                ))
                                , new Primary('Auswählen', new Select())
                            ), $Select
                        )
                    )
                )
            )))
        );

        return $View;
    }

    /**
     * @param UploadedFile|null $File
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendStudentImport(UploadedFile $File = null, $DivisionId = null)
    {

        $View = new Stage();
        $View->setTitle('ESZC Import');
        $View->setDescription('Schülerdaten');

        if ($DivisionId === null) {
            return new Redirect('/Transfer/Import/Chemnitz/Student', 0);
        }

        $tblDivision = Division::useService()->getDivisionById($DivisionId);

        if (!$tblDivision) {
            return new Redirect('/Transfer/Import/Chemnitz/Student', 0);
        }

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Schulart:', $tblDivision->getTblLevel()->getServiceTblType()->getName(),
                        Panel::PANEL_TYPE_SUCCESS), 6
                ),
                new LayoutColumn(
                    new Panel('Klasse:', $tblDivision->getName(),
                        Panel::PANEL_TYPE_SUCCESS), 6
                ),
                new LayoutColumn(array(
                        Chemnitz::useService()->createStudentsFromFile(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                array('showPreview' => false))
                                        )
                                    )
                                ))
                                , new Primary('Hochladen')
                            ), $File, $DivisionId
                        )
                    ,
                        new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                    )
                )
            ))))
        );

        return $View;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendPersonImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('ESZC Import');
        $View->setDescription('Personendaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            Chemnitz::useService()->createPersonsFromFile(new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                array('showPreview' => false))
                                        )
                                    )
                                )
                                , new Primary('Hochladen')
                            ), $File
                            )
                        ,
                            new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                        ))
                    )
                )
            )
        );

        return $View;
    }

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendInterestedPersonImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('ESZC Import');
        $View->setDescription('Interessentendaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            Chemnitz::useService()->createInterestedPersonsFromFile(new Form(
                                new FormGroup(
                                    new FormRow(
                                        new FormColumn(
                                            new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                array('showPreview' => false))
                                        )
                                    )
                                )
                                , new Primary('Hochladen')
                            ), $File
                            )
                        ,
                            new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                        ))
                    )
                )
            )
        );

        return $View;
    }
}
