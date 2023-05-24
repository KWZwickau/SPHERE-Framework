<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param $Id
     * @param bool|false $Confirm
     * @param null $Group
     * @return Stage
     */
    public function frontendDestroyPerson($Id = null, $Confirm = false, $Group = null)
    {

        $Stage = new Stage('Person', 'Löschen');
        if ($Id) {
            if ($Group) {
                $Stage->addButton(new Standard('Zurück', '/People', new ChevronLeft(), array('Id' => $Group)));
            }
            $tblPerson = Person::useService()->getPersonById($Id);
            if (!$tblPerson){
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Die Person konnte nicht gefunden werden.', new Ban()),
                            new Redirect('/People', Redirect::TIMEOUT_ERROR, array('PseudoId' => $Group))
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    // Personen (Schüler) dürfen aktuell nicht gelöscht werden wenn sie Zensuren oder Zeugnisse besitzen SSW-115
                    $canRemove = true;
                    $descriptionList = array();
                    if (Grade::useService()->getCountPersonTestGrades($tblPerson)) {
                        $canRemove = false;
                        $descriptionList[] = 'Diese Person kann aktuell nicht gelöscht werden, da zu dieser Person Zensuren und/oder Zeugnisse existieren.';
                    } elseif (($tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson))) {
                        $canRemove = false;
                        $descriptionList[] = 'Diese Person kann aktuell nicht gelöscht werden, da zu dieser Person Zensuren und/oder Zeugnisse existieren.';
                    }
                    // Person mit Account darf nicht gelöscht werden
                    if (($AccountList = Account::useService()->getAccountAllByPerson($tblPerson))) {
                        foreach($AccountList as &$Account){
                            $Account = $Account->getUsername();
                        }
                        $canRemove = false;
                        $descriptionList[] = 'Diese Person kann aktuell nicht gelöscht werden, da ein Account für diese Person Exisitert ('.implode(',', $AccountList).')';
                    }

                    if ($canRemove) {
                        $buttonList =
                            new Standard(
                                'Ja', '/People/Person/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true, 'Group' => $Group)
                            )
                            . new Standard(
                                'Nein', '/People', new Disable(), array('PseudoId' => $Group)
                            );
                    } else {
                        $buttonList =
                            new Standard(
                                'Nein', '/People', new Disable(), array('PseudoId' => $Group)
                            );
                    }

                    $Stage->setContent(
                        ($canRemove
                            ? ''
                            : new Warning(
                                implode('<br/>', $descriptionList)
                            )
                        )
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Person', new Bold($tblPerson->getLastFirstName()),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(
                                new Question() . ' Diese Person wirklich löschen?',
                                Person::useService()->getDestroyDetailList($tblPerson),
                                Panel::PANEL_TYPE_DANGER,
                                $buttonList
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (Person::useService()->destroyPerson($tblPerson)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Person wurde gelöscht.')
                                    : new Danger(new Ban() . ' Die Person konnte nicht gelöscht werden.')
                                ),
                                new Redirect('/People', Redirect::TIMEOUT_SUCCESS, array('PseudoId' => $Group))
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Daten nicht abrufbar.', new Ban()),
                        new Redirect('/People', Redirect::TIMEOUT_ERROR, array('PseudoId' => $Group))
                    )))
                )))
            );
        }
        return $Stage;
    }
}
