<?php

namespace SPHERE\Application\Transfer\Import\Competence;

use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param null $File
     * @param null $Data
     *
     * @return Stage
     * @noinspection PhpUnused
     */
    public function frontendSkillGridImport($File = null, $Data = null): Stage
    {

        $Stage = new Stage('Import', 'Kompetenzraster');
        $Stage->addButton(
            new Standard(
                'Zurück',
                '/Transfer/Import',
                new ChevronLeft()
            )
        );

        $doc = new Title("Import Dokumentation");
        $doc .= new Container(' • Es werden leere Zeilen in der Import-Datei ignoriert (Spalte A - C ist leer)');
        $doc .= new Container(' • Beginnt die Spalte A mit "Fach " oder "Fach:" wird ein neuer Gültigkeitsbereich festgelegt.
            Somit wird ein neues Kompetenzraster angelegt.');
        $doc .= new Container(' • In diesen Fall muss die Spalte B mit "Klassenstufe" oder "Klassenstufe:" beginnen.');
        $doc .= new Container(' • Optional kann in der Spalte C mit "Bildungsgang" oder "Bildungsgang:" beginnend der Bildungsgang angegeben werden 
            (Ansonsten auch komplett leer möglich).');
        $doc .= new Container(' • Optional kann in der Spalte D mit "Primärer Förderschwerpunkt" oder "Primärer Förderschwerpunkt:" beginnend 
            der Primärer Förderschwerpunkt angegeben werden (Ansonsten auch komplett leer möglich).');
        $doc .= new Container('&nbsp;');
        $doc .= new Container(' • Wenn in der einer Zeile kein Gültigkeitsbereich angegeben wird, wird in Spalte A ein neuer Kompetenzbereich angegeben.
            Es wird folgendes vom Kompetenzbereich entfernt: Kompetenzbereich : „ “ "');
        $doc .= new Container(' • Ist Spalte A leer wird die Kompetenz in Spalte B angegeben und optional das Niveau in Spalte C.
            Es wird folgendes von Kompetenz und Niveau entfernt: •');

        $Stage->setContent(
            $doc
            . new Container('&nbsp;')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(
                                Competence::useService()->createSkillGridsFromFile(
                                    new Form(new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                    array('showPreview' => false))
                                            )
                                        )),
                                        new FormRow(array(
                                            new FormColumn(
                                                (new SelectBox('Data[TypeId]', 'Schulart',
                                                    array('{{ Name }}' => SkillGrid::useService()->getAvailableSchoolTypeList())
                                                ))->setRequired()
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