<?php
namespace SPHERE\Application\Setting\Authorization\Token;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\PasswordField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Token
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param null|string $CredentialKey
     *
     * @return Stage
     */
    public function frontendYubiKey($CredentialKey = null)
    {

        $Stage = new Stage('Hardware-Schlüssel');
        $Stage->setMessage('Bestehende Hardware-Schlüssel');

        $Stage->setContent(
            $this->frontendLayoutToken().
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Token::useService()->createToken(
                                $this->formYubiKey()
                                    ->appendFormButton(new Primary('Hardware-Schlüssel hinzufügen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $CredentialKey, Consumer::useService()->getConsumerBySession())
                        )
                    ), new Title('Hardware-Schlüssel', 'Hinzufügen')
                ),
            ))
        );
        return $Stage;
    }

    /**
     * @return Layout
     */
    public function frontendLayoutToken()
    {

        $tblTokenAll = Token::useService()->getTokenAllByConsumer(Consumer::useService()->getConsumerBySession());
        if ($tblTokenAll !== false) {
            array_walk($tblTokenAll, function (TblToken &$tblToken) {

                $Serial = $tblToken->getSerial();
                $Serial = substr($Serial, 0, 4).' '.substr($Serial, 4, 4);

                $Content = array();
                $tblAccountAll = $tblToken->getAccountAllByToken();
                if (!empty( $tblAccountAll )) {
                    array_walk($tblAccountAll, function (TblAccount &$tblAccount) {

                        $tblAccount = new PullClear(
                            new PullLeft(new PersonKey().' '.$tblAccount->getUsername())
                            .new PullRight(new Standard('',
                                    '/Setting/Authorization/Account',
                                    new PersonKey(), array('Id' => $tblAccount->getId()),
                                    'zu '.$tblAccount->getUsername().' wechseln'
                                )
                            ));
                    });
                    $Content = array_merge($Content, $tblAccountAll);
                    $Content = array_filter($Content);
                    array_unshift($Content, new Info(new Exclamation().' '.new Small('Benutzerkonten verknüpft')));
                } else {
                    $Content = array(
                        new Muted(new Small('Keine Benutzerkonten verknüpft')),
                        new Muted(new Small('Der Schlüssel kann gefahrlos entfernt werden'))
                    );
                }

                $tblToken = new LayoutColumn(
                    new Panel(
                        new YubiKey().' '.$Serial, $Content, Panel::PANEL_TYPE_INFO,
                        ( empty( $tblAccountAll )
                            ? new Standard('',
                                '/Setting/Authorization/Token/Destroy',
                                new Remove(), array('Id' => $tblToken->getId()),
                                'Schlüssel '.$Serial.' löschen'
                            )
                            : new Muted(new Small('Der Schlüssel kann nicht entfernt werden'))
                        )
                    )
                    , 3);
            });
        } else {
            $tblTokenAll = array(
                new LayoutColumn(
                    new Warning('Keine Hardware-Schlüssel hinterlegt')
                )
            );
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblToken
         */
        foreach ($tblTokenAll as $tblToken) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($tblToken);
            $LayoutRowCount++;
        }

        return new Layout(new LayoutGroup($LayoutRowList));
    }

    /**
     * @return Form
     */
    private function formYubiKey()
    {

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Neuer Hardware-Schlüssel', array(
                            new PasswordField('CredentialKey', 'YubiKey', 'YubiKey'),
                        ), Panel::PANEL_TYPE_INFO)
                    )),
                )),
            ))
        );
    }

    /**
     * @param int  $Id
     * @param bool $Confirm
     *
     * @return Stage
     */
    public function frontendDestroyToken($Id, $Confirm = false)
    {

        $Stage = new Stage('Hardware-Schlüssel', 'Löschen');
        if ($Id) {
            $tblToken = Token::useService()->getTokenById($Id);
            $tblAccountAll = $tblToken->getAccountAllByToken();
            if (empty( $tblAccountAll )) {
                if (!$Confirm) {

                    $Serial = $tblToken->getSerial();
                    $Serial = substr($Serial, 0, 4).' '.substr($Serial, 4, 4);

                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel(new YubiKey().' Hardware-Schlüssel', array(
                                $Serial,
                                strtoupper($tblToken->getIdentifier())
                            ), Panel::PANEL_TYPE_SUCCESS),
                            new Panel(new Question().' Diesen Hardware-Schlüssel wirklich löschen?', array(),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Setting/Authorization/Token/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                .new Standard(
                                    'Nein', '/Setting/Authorization/Token', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( Token::useService()->destroyToken($tblToken)
                                    ? new Success('Der Hardware-Schlüssel wurde gelöscht')
                                    : new Danger('Der Hardware-Schlüssel konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Setting/Authorization/Token', 1)
                            )))
                        )))
                    );
                }
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger('Der Hardware-Schlüssel kann nicht gelöscht werden'),
                            new Redirect('/Setting/Authorization/Token')
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Hardware-Schlüssel konnte nicht gefunden werden'),
                        new Redirect('/Setting/Authorization/Token')
                    )))
                )))
            );
        }
        return $Stage;
    }
}
