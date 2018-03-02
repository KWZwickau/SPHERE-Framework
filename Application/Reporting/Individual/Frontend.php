<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Api\Reporting\Individual\ApiIndividual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Flexible Auswertung', 'Auswahl der Kategorie');

        $Stage->setContent(
            new Container('&nbsp;')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Kategorien:', new \SPHERE\Common\Frontend\Layout\Repository\Listing(
                                array(
                                    new PullClear(
                                        new PullRight(new Standard('', __NAMESPACE__.'/Group', new Listing()))
                                        .'Allgemeine Auswertung für Personen'
                                        . new Container(new Muted('Auswertung über Personengruppen um weitere allgemeine Informationen zur Person zu erhalten.'))
                                    ),
                                    new PullClear(
                                        new PullRight(new Standard('', __NAMESPACE__.'/Student', new Listing()))
                                        .'Spezifische Auswertung für Schüler'
                                        . new Container(new Muted('Auswertung über Schüler um speziefischere Informationen zum Schüler zu erhalten.'))
                                    ),
//                                    new PullClear(
//                                        new PullRight(new Standard('', __NAMESPACE__.'/Student', new Listing()))
//                                        .'Spezifische Auswertung für Sorgeberechtigte'
//                                        . new Container(new Muted('Auswertung über Sorgeberechtigte um speziefischere Informationen zum Sorgeberechtigten zu erhalten.'))
//                                    ),
//                                    new PullClear(
//                                        new PullRight(new Standard('', __NAMESPACE__.'/Student', new Listing()))
//                                        .'Spezifische Auswertung für Interessenten'
//                                        . new Container(new Muted('Auswertung über Interessenten um speziefischere Informationen zum Interessenten zu erhalten.'))
//                                    )
                                )))
                        , 6),
                    ))
                )
            )
        );

        // $Content = Individual::useService()->getView();

        return $Stage;
    }

    public function frontendStudent()
    {

        $Stage = new Stage('Flexible Auswertung', 'Auswertung über Schüler');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiIndividual::receiverService(),
                            ApiIndividual::receiverModal(),
                            ApiIndividual::receiverNavigation(),
                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_STUDENT)
                        ), 3),
                        new LayoutColumn(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverFilter(),
                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_STUDENT)
                                        )),
                                        new LayoutColumn(new Title('Suchergebnis')),
                                        new LayoutColumn(ApiIndividual::receiverResult()),
                                    ))
                                )
                            )
                            , 9)
                    ))
                )
            ))
        );

        return $Stage;
    }

    public function frontendGroup()
    {

        $Stage = new Stage('Flexible Auswertung', 'Auswertung von Personen und Gruppen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiIndividual::receiverService(),
                            ApiIndividual::receiverModal(),
                            ApiIndividual::receiverNavigation(),
                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_ALL)
                        ), 3),
                        new LayoutColumn(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverFilter(),
                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_ALL)
                                        )),
                                        new LayoutColumn(new Title('Suchergebnis')),
                                        new LayoutColumn(ApiIndividual::receiverResult()),
                                    ))
                                )
                            )
                            , 9)
                    ))
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param string $ViewType
     *
     * @return string
     */
    public function frontendDownload($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        return (new ApiIndividual())->downloadFile($ViewType);
//        $Stage = new Stage('Dokument wird vorbereitet');
//        $Stage->setContent(new Layout(new LayoutGroup(array(
//                new LayoutRow(array(
//                    new LayoutColumn(array(
//                        new Paragraph('Dieser Vorgang kann längere Zeit in Anspruch nehmen.'),
//                        (new ProgressBar(0, 100, 0, 10))->setColor(
//                            ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_STRIPED
//                        ),
//                        new Paragraph('Bitte warten ..'),
//                        "<button type=\"button\" class=\"btn btn-default\" onclick=\"window.open('', '_self', ''); window.close();\">Abbrechen</button>"
//                    ), 4),
//                )),
//                new LayoutRow(
//                    new LayoutColumn(
//                        new RedirectScript($Route, 1, $this->getGlobal()->GET)
//                    )
//                ),
//            )))
//        );
//
//        return $Stage;
    }
}
