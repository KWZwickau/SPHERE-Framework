<?php
namespace SPHERE\Application\Document\Search\FileSystem;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\FileExtension;
use SPHERE\Common\Frontend\Icon\Repository\FileType;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Frontend
 * @package SPHERE\Application\Document\Search\FileSystem
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param array $Search
     *
     * @return Stage
     */
    public function frontendSearch($Search = null)
    {
        $Stage = new Stage('Suche', 'Dateien');

        Debugger::screenDump($Search);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                $this->formFile()
                                    ->appendFormButton(
                                        new Primary('Suchen', new Search())
                                    )
                            )
                        )
                    ), new Title('Suche nach')),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ($Search
                                ? new TableData(array(), null, array('Verzeichnis', 'Datei', 'Größe', 'Datum'))
                                : new Info('Keine Suche durchgeführt')
                            )
                        )
                    ), new Title('Suchergebnis')),
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    public function formFile()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Metadaten', array(
                            new TextField('Search[Name]', 'Name', 'Name', new Nameplate()),
                            new TextField('Search[Description]', 'Beschreibung', 'Beschreibung', new Comment()),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Dateiname', array(
                            new TextField('Search[FileName]', 'Datei-Name', 'Datei-Name', new FileType()),
                            new TextField('Search[FileExtension]', 'Datei-Endung', 'Datei-Endung', new FileExtension()),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Attributen', array(
                            new DatePicker('Search[EntityCreate]', 'Datum', 'Datum', new Calendar())
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                ))
            )
        );
    }
}
