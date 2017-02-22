<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;


/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Import implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Show', __NAMESPACE__.'/Frontend::frontendLectureshipShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Edit', __NAMESPACE__.'/Frontend::frontendLectureshipEdit'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Destroy', __NAMESPACE__.'/Frontend::frontendLectureshipDestroy'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship/Ignore', __NAMESPACE__.'/Frontend::frontendIgnoreImport'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity',
            __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Untis', 'Datentransfer');

        // load if Lectureship exist (by Account)
        $PanelContent = array();
        $tblUntisImportLectureship = Import::useService()->getUntisImportLectureshipByAccount();
        if ($tblUntisImportLectureship) {
            $PanelContent[] = 'Lehraufträge: '
                .new Standard('', '/Transfer/Untis/Import/Lectureship/Show', new Edit(), array(), 'Bearbeiten')
                .new Standard('', '/Transfer/Untis/Import/Lectureship/Destroy', new Remove(), array(), 'Löschen');
        }

        $Stage->setMessage('Importvorbereitung / Daten importieren');
        $tblYearAll = Term::useService()->getYearAll();
        if (!$tblYearAll) {
            $tblYearAll = array();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ( $tblUntisImportLectureship
                                ? new Panel('Vorhandener Untis-Import:', $PanelContent
                                    , Panel::PANEL_TYPE_SUCCESS)
                                : ''
                            )
                            , 6)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Title('Importieren neuer Lehraufträge')
                        ),
                        new LayoutColumn(new Well(array(
                            new Title('Lehraufträge', 'importieren'),
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Import',
                                                array(
                                                    ( new SelectBox('tblYear', 'Schuljahr auswählen', array(
                                                        '{{ Year }} {{ Description }}' => $tblYearAll
                                                    )) )->setRequired(),
                                                    ( new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null, array('showPreview' => false)) )->setRequired()
                                                ), Panel::PANEL_TYPE_INFO)

                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Link\Route(__NAMESPACE__.'/Lectureship')
                            )
                        )), 6),
                    )),
                ))
            )
        );

        return $Stage;
    }
}