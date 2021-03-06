<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2018
 * Time: 11:32
 */

namespace SPHERE\Application\Transfer\Import\BadDueben;

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
 * @package SPHERE\Application\Transfer\Import\BadDueben
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $File
     * @param bool $IsNextYear
     *
     * @return Stage
     */
    public function frontendStudentImport($File = null)
    {

        $View = new Stage('Import Bad Düben', 'Schüler-Daten');
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
                                BadDueben::useService()->createStudentsFromFile(new Form(
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
                                . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX) ' . new Exclamation())
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
        $View->setTitle('Bad Düben Import');
        $View->setDescription('Interessentendaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                BadDueben::useService()->createInterestedPersonsFromFile(new Form(
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
                                . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX) ' . new Exclamation())
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
    public function frontendStaffImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Bad Düben');
        $View->setDescription('Mitarbeiter');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                BadDueben::useService()->createStaffsFromFile(new Form(
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
                                . new Warning('Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    )
                )
            )
        );

        return $View;
    }
}