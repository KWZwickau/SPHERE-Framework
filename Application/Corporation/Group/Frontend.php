<?php
namespace SPHERE\Application\Corporation\Group;

use SPHERE\Application\Corporation\Company\Company as CorporationCompany;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Corporation\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param array $Group
     *
     * @return Stage
     */
    public function frontendGroup($Group)
    {

        $Stage = new Stage('Gruppen');
        $tblGroupAll = Group::useService()->getGroupAll();
        if ($tblGroupAll) {
            array_walk($tblGroupAll, function (TblGroup &$tblGroup) {

                $Content = array(
                    ( $tblGroup->getDescription() ? new Small(new Muted($tblGroup->getDescription())) : false ),
                    ( $tblGroup->getRemark() ? nl2br($tblGroup->getRemark()) : false ),
                );
                $Content = array_filter($Content);
                $Type = ( $tblGroup->getIsLocked() ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT );
                $Footer = new PullLeft(
                    new Standard('', '/Corporation/Group/Edit', new Edit(),
                        array('Id' => $tblGroup->getId()), 'Daten ändern'
                    )
                    .( $tblGroup->getIsLocked()
                        ? ''
                        : new Standard('', '/Corporation/Group/Destroy', new Remove(),
                            array('Id' => $tblGroup->getId()), 'Gruppe löschen'
                        )
                    )
                );
                $Footer .= new PullRight(
                    new Label(CorporationCompany::useService()->countCompanyAllByGroup($tblGroup).' Firmen',
                        Label::LABEL_TYPE_INFO)
                );
                $tblGroup = new LayoutColumn(
                    new Panel($tblGroup->getName(), $Content, $Type, new PullClear($Footer))
                    , 4);
            });

            $LayoutRowList = array();
            $LayoutRowCount = 0;
            $LayoutRow = null;
            /**
             * @var LayoutColumn $tblGroup
             */
            foreach ($tblGroupAll as $tblGroup) {
                if ($LayoutRowCount % 3 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($tblGroup);
                $LayoutRowCount++;
            }
        } else {
            $LayoutRowList = new LayoutRow(
                new LayoutColumn(
                    new Warning('Keine Gruppen vorhanden')
                )
            );
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    $LayoutRowList
                    , new Title('Gruppen')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Group::useService()->createGroup(
                                $this->formGroup()
                                    ->appendFormButton(new Primary('Hinzufügen'))
                                    ->setConfirm('Die neue Gruppe wurde noch nicht gespeichert')
                                , $Group
                            )
                        )
                    ), new Title('Gruppe hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    private function formGroup()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Gruppe', array(
                            new TextField('Group[Name]', 'Name', 'Name'),
                            new TextField('Group[Description]', 'Beschreibung', 'Beschreibung')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Group[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil())
                        ), Panel::PANEL_TYPE_INFO)
                        , 8),
                ))
            )
        );
    }

    /**
     * @param int        $Id
     * @param null|array $Group
     *
     * @return Stage
     */
    public function frontendEditGroup($Id, $Group = null)
    {

        $Stage = new Stage('Gruppe', 'Bearbeiten');
        $tblGroup = Group::useService()->getGroupById($Id);
        if ($tblGroup) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Group']['Name'] = $tblGroup->getName();
                $Global->POST['Group']['Description'] = $tblGroup->getDescription();
                $Global->POST['Group']['Remark'] = $tblGroup->getRemark();
                $Global->savePost();
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                Group::useService()->updateGroup(
                                    $this->formGroup()
                                        ->appendFormButton(new Primary('Änderungen speichern'))
                                        ->setConfirm('Die Änderungen wurden noch nicht gespeichert')
                                    , $tblGroup, $Group
                                )
                            )
                        ), new Title('Gruppe ändern')
                    ),
                ))
            );
        } else {
            // TODO: Error-Message
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Danger(
                                    'Die Gruppe konnte nicht gefunden werden'
                                )
                            )
                        ), new Title('Gruppe ändern')
                    )
                )
            );
        }
        return $Stage;
    }

    /**
     * @param int  $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyGroup($Id, $Confirm = false)
    {

        $Stage = new Stage('Gruppe', 'Löschen');
        if ($Id) {
            $tblGroup = Group::useService()->getGroupById($Id);
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diese Gruppe wirklich löschen?', array(
                            $tblGroup->getName().' '.$tblGroup->getDescription(),
                            new Muted(new Small($tblGroup->getRemark()))
                        ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Corporation/Group/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Corporation/Group', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Remove Group-Member
                $tblCompanyAll = Group::useService()->getCompanyAllByGroup($tblGroup);
                /** @noinspection PhpUnusedParameterInspection */
                array_walk($tblCompanyAll, function (TblCompany $tblCompany, $Index, TblGroup $tblGroup) {

                    Group::useService()->removeGroupCompany($tblGroup, $tblCompany);
                }, $tblGroup);

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Group::useService()->destroyGroup($tblGroup)
                                ? new Success('Die Gruppe wurde gelöscht')
                                .new Redirect('/Corporation/Group', 0)
                                : new Danger('Die Gruppe konnte nicht gelöscht werden')
                                .new Redirect('/Corporation/Group', 3)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Die Gruppe konnte nicht gefunden werden'),
                        new Redirect('/Corporation/Group', 3)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
