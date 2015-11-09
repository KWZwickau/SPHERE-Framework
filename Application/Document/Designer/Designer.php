<?php
namespace SPHERE\Application\Document\Designer;

use SPHERE\Application\Document\Designer\Repository\Element\Document;
use SPHERE\Application\Document\Designer\Repository\Element\Element;
use SPHERE\Application\Document\Designer\Repository\Element\Page;
use SPHERE\Application\Document\Designer\Repository\Repository;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Script;
use SPHERE\Common\Style;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Designer
 *
 * @package SPHERE\Application\Document\Designer
 */
class Designer implements IApplicationInterface
{

    public static function registerApplication()
    {

        Repository::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Designer'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage();

        Style::getManager()->setSource('/Application/Document/Designer/Designer.css');
        Script::getManager()->setSource('SDD.Document', '/Application/Document/Designer/Gui/Document.js',
            "'undefined' !== typeof jQuery.fn.SDDDocument");
        Script::getManager()->setSource('SDD.Panel', '/Application/Document/Designer/Gui/Panel.js',
            "'undefined' !== typeof jQuery.fn.SDDPanel");
        Script::getManager()->setSource('SDD.Page', '/Application/Document/Designer/Gui/Page.js',
            "'undefined' !== typeof jQuery.fn.SDDPage");
        Script::getManager()->setSource('SDD.Element', '/Application/Document/Designer/Gui/Element.js',
            "'undefined' !== typeof jQuery.fn.SDDElement");
        Script::getManager()->setModule('ModSDDGui',
            array('SDD.Element', 'SDD.Page', 'SDD.Panel', 'SDD.Document', 'jQuery'));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Document(array(
                                new \SPHERE\Application\Document\Designer\Repository\Panel\Repository(

                                    new Panel('Elemente', array(
                                        new PullClear(new Element(
                                            'Element Z'
                                        ))
                                    ))

                                ),
                                new Page(array(
                                    new Element('Element A'),
                                    new Element('Element B')
                                )),
                                new Page(),
                                new Page(
                                    new Element('Element C')
                                )
                            )),
                        )),
                    ))
                )
            )
        );

        return $Stage;
    }
}
