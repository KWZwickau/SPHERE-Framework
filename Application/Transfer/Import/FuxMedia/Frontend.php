<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.11.2015
 * Time: 13:28
 */

namespace SPHERE\Application\Transfer\Import\FuxMedia;

use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        $tblYearAll = Term::useService()->getYearAll();
        $tblTypeAll = Type::useService()->getTypeAll();

        $View->setContent(
            new Layout(new LayoutGroup(new LayoutRow(
                new LayoutColumn(array(
                        FuxSchool::useService()->getTypeAndYear(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(array(
                                        new FormColumn(
                                            new SelectBox('Select[Year]', 'Schuljahr',
                                                array('{{Name}}' => $tblYearAll)),
                                            6
                                        ),
                                        new FormColumn(
                                            new SelectBox('Select[Type]', 'Schulart',
                                                array('{{Name}}' => $tblTypeAll)),
                                            6
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
     *
     * @return Stage
     */
    public function frontendStudentImport(UploadedFile $File = null)
    {

        $View = new Stage();
        $View->setTitle('ESZC Import');
        $View->setDescription('Schülerdaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                                FuxSchool::useService()->createStudentsFromFile(
                                    new Form(
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
                            )
                        ))
                )
            )
        );

        return $View;
    }
}