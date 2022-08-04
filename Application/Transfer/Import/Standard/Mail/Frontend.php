<?php

namespace SPHERE\Application\Transfer\Import\Standard\Mail;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
 * @package SPHERE\Application\Transfer\Import\Standard\Mail
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $File
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendMailImport($File = null, $Data = null)
    {

        $Stage = new Stage('Import', 'Standard für Emailadressen');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel(
                                'Excel-Header',
                                array(
                                    0 => "Pflichtfelder: Vorname, Nachname",
                                    1 => "Wahlpflichtfelder (1 Feld): Emailadresse, Benutzer-Alias-Mail, Recovery-Mail",
                                    2 => "optionales Feld für besser Personenerkennung: Geburtsdatum",
                                    3 => "optionales Feld vorhandener Inhalt setzt die Schülernummer: Identifikation"
                                ),
                                Panel::PANEL_TYPE_INFO
                            )
                        ),

                        new LayoutColumn(array(
                            new Well(
                                Mail::useService()->createMailsFromFile(
                                    new Form(new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )),
                                        new FormRow(array(
                                            new FormColumn(
                                                (new SelectBox('Data[Type]', 'Emailadress-Typ',
                                                    array('{{ Name }} {{ Description }}' =>\SPHERE\Application\Contact\Mail\Mail::useService()->getTypeAll())
                                                ))->setRequired()
                                            ),
                                        )),
                                        new FormRow(array(
                                            new FormColumn(
                                                new RadioBox('Data[Radio]', 'Nur Emailadressen importieren', 1)
                                            ),
                                        )),
                                        new FormRow(array(
                                            new FormColumn(
                                                new RadioBox('Data[Radio]', 'Emailadresse als Account-Alias verwenden', 2)
                                            ),
                                        )),
                                        new FormRow(array(
                                            new FormColumn(
                                                new RadioBox('Data[Radio]', '"Passwort vergessen" E-Mail-Adressen importieren', 3)
                                            ),
                                        )),
                                        new FormRow(array(
                                            new FormColumn(
                                                new CheckBox('Data[IsTest]', 'Test-Run -> es werden keine Daten übernommen', 1)
                                            ),
                                        )),
                                    )), new Primary('Hochladen'))
                                    , $File, $Data
                                )
                                . new Warning(new Exclamation().' Erlaubte Dateitypen: Excel (XLS,XLSX)')
                            )
                        ))
                    ))
                )
            )
        );

        return $Stage;
    }
}