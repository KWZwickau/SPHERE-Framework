<?php
namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Api\Reporting\Individual\ApiIndividual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
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
                        new LayoutColumn('&nbsp;', 3),
                        new LayoutColumn(array(
                            new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation())
                            ,new Panel('Kategorien:', new \SPHERE\Common\Frontend\Layout\Repository\Listing(array(
                                new Center('Allgemeine Auswertung für '.new Bold('Personen').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Group', new Listing())),
                                new Center('Spezifische Auswertung für '.new Bold('Schüler').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Student', new Listing())),
                                new Center('Spezifische Auswertung für '.new Bold('Interessenten').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Prospect', new Listing())),
                                new Center('Spezifische Auswertung für '.new Bold('Sorgeberechtigte').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Custody', new Listing())),
                                new Center('Spezifische Auswertung für '.new Bold('Lehrer').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Teacher', new Listing())),
                                new Center('Spezifische Auswertung für '.new Bold('Mitarbeiter').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Staff', new Listing())),
                                new Center('Spezifische Auswertung für '.new Bold('Vereinsmitglieder').'<br/>')
                                .new Center(new Standard('', __NAMESPACE__.'/Club', new Listing())),
                            )))
                        ), 6),
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendGroup()
    {

        $Stage = new Stage('Auswertung von Personen und Gruppen');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
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
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendStudent()
    {

        $Stage = new Stage('Auswertung Schüler');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
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
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendProspect()
    {

        $Stage = new Stage('Auswertung Interessent');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Layout(array(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverService(),
                                            ApiIndividual::receiverModal(),
                                            ApiIndividual::receiverNavigation(),
                                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_PROSPECT)
                                        ), 3),
                                        new LayoutColumn(
                                            new Layout(
                                                new LayoutGroup(
                                                    new LayoutRow(array(
                                                        new LayoutColumn(array(
                                                            ApiIndividual::receiverFilter(),
                                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_PROSPECT)
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
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendCustody()
    {

        $Stage = new Stage('Auswertung Sorgeberechtigte');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Layout(array(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverService(),
                                            ApiIndividual::receiverModal(),
                                            ApiIndividual::receiverNavigation(),
                                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_CUSTODY)
                                        ), 3),
                                        new LayoutColumn(
                                            new Layout(
                                                new LayoutGroup(
                                                    new LayoutRow(array(
                                                        new LayoutColumn(array(
                                                            ApiIndividual::receiverFilter(),
                                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_CUSTODY)
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
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendTeacher()
    {

        $Stage = new Stage('Auswertung Lehrer');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Layout(array(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverService(),
                                            ApiIndividual::receiverModal(),
                                            ApiIndividual::receiverNavigation(),
                                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_TEACHER)
                                        ), 3),
                                        new LayoutColumn(
                                            new Layout(
                                                new LayoutGroup(
                                                    new LayoutRow(array(
                                                        new LayoutColumn(array(
                                                            ApiIndividual::receiverFilter(),
                                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_TEACHER)
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
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendStaff()
    {

        $Stage = new Stage('Auswertung Mitarbeiter');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Layout(array(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverService(),
                                            ApiIndividual::receiverModal(),
                                            ApiIndividual::receiverNavigation(),
                                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_STAFF)
                                        ), 3),
                                        new LayoutColumn(
                                            new Layout(
                                                new LayoutGroup(
                                                    new LayoutRow(array(
                                                        new LayoutColumn(array(
                                                            ApiIndividual::receiverFilter(),
                                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_STAFF)
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
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendClub()
    {

        $Stage = new Stage('Auswertung Vereinsmitglieder');
        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $Stage->setContent(
            new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Layout(array(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ApiIndividual::receiverService(),
                                            ApiIndividual::receiverModal(),
                                            ApiIndividual::receiverNavigation(),
                                            ApiIndividual::pipelineNavigation(false, TblWorkSpace::VIEW_TYPE_CLUB)
                                        ), 3),
                                        new LayoutColumn(
                                            new Layout(
                                                new LayoutGroup(
                                                    new LayoutRow(array(
                                                        new LayoutColumn(array(
                                                            ApiIndividual::receiverFilter(),
                                                            ApiIndividual::pipelineDisplayFilter(TblWorkSpace::VIEW_TYPE_CLUB)
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
                        )
                    )
                )
            )
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
    }

    /**
     * @param string $ViewType
     *
     * @return string
     */
    public function frontendCsvDownload($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        return (new ApiIndividual())->downloadCsvFile($ViewType);
    }
}
