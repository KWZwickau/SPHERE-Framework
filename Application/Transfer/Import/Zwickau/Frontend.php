<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.02.2017
 * Time: 08:42
 */

namespace SPHERE\Application\Transfer\Import\Zwickau;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Transfer\Import\Zwickau
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $File
     *
     * @return Stage
     */
    public function frontendCompanyImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Import Zwickau');
        $View->setDescription('Schulen (Firmen)');
        $View->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Zwickau::useService()->createCompaniesFromFile(
                                    new Form(
                                        new FormGroup(
                                            new FormRow(
                                                new FormColumn(
                                                    new FileUpload('File', 'Datei auswählen', 'Datei auswählen',
                                                        null,
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
            )
        );

        return $View;
    }

    /**
     * @param null $File
     * @param bool $IsNextYear
     *
     * @return Stage
     */
    public function frontendStudentImport($File = null, $IsNextYear = false)
    {

        $View = new Stage('Import Zwickau', 'Schüler-Daten Grundschule');
        $View->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Zwickau::useService()->createStudentsFromFile(new Form(
                                    new FormGroup(
                                        new FormRow(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )
                                    )
                                    , new Primary('Hochladen')
                                ), $File, $IsNextYear
                                )
                                ,
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
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
        $View->setTitle('Zwickau Import');
        $View->setDescription('Interessentendaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Zwickau::useService()->createInterestedPersonsFromFile(new Form(
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
                                new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)', new Exclamation())
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }
}