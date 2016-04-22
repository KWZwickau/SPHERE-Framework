<?php

namespace SPHERE\Application\Transfer\Import\Hormersdorf;

use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

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
        $View->setTitle('Hormersdorf Import');
        $View->setDescription('Interessentendaten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Hormersdorf::useService()->createInterestedPersonsFromFile(new Form(
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

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendClubMemberImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Hormersdorf Import');
        $View->setDescription('Schulverein-Daten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Hormersdorf::useService()->createClubMembersFromFile(new Form(
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

    /**
     * @param null $File
     *
     * @return Stage
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function frontendDonorImport($File = null)
    {

        $View = new Stage();
        $View->setTitle('Hormersdorf Import');
        $View->setDescription('Spender-Daten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Hormersdorf::useService()->createDonorsFromFile(new Form(
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

    /**
     * @param null $File
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendStudentImport($File = null, $Data = null)
    {

        $tblTypeAll = Type::useService()->getTypeAll();

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            $Global->POST['Data']['Type'] = 6;
            $Global->savePost();
        }

        $View = new Stage();
        $View->setTitle('Hormersdorf Import');
        $View->setDescription('Schüler-Daten');
        $View->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Well(
                                Hormersdorf::useService()->createStudentsFromFile(
                                    new Form(
                                        new FormGroup(array(
                                            new FormRow(
                                                new FormColumn(
                                                    new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                        array('showPreview' => false))
                                                )
                                            ),
                                            new FormRow(array(
                                                new FormColumn(
                                                    new TextField('Data[Year]', 'z.B. 15 für 2015/16', 'Jahr'), 3
                                                ),
                                                new FormColumn(
                                                    new TextField('Data[Level]', 'z.B. 1', 'Klassenstufe'), 3
                                                ),
                                                new FormColumn(
                                                    new TextField('Data[Division]', 'z.B. a', 'Klassengruppenname'), 3
                                                ),
                                                new FormColumn(
                                                    new SelectBox('Data[Type]', 'Schulart', array('Name' => $tblTypeAll)), 3
                                                )

                                            )),
                                        ))
                                        , new Primary('Hochladen')
                                    ), $File, $Data
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
