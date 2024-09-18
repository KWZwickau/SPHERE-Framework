<?php
namespace SPHERE\Application\Platform\Assistance\Support;

use MOC\V\Component\Mail\Component\Bridge\Repository\EdenPhpSmtp;
use MOC\V\Component\Mail\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Mail\Mail;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Support\Type\YouTrackMail;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Assistance\Support\Ticket
 */
class Service
{

    /**
     * @param IFormInterface $Form
     * @param null|string    $TicketSubject
     * @param null|string    $TicketMessage
     *
     * @return IMessageInterface|IFormInterface
     */
    public function executeCreateTicket(IFormInterface &$Form, $TicketSubject, $TicketMessage)
    {

//        $Error = false;
//        if (empty( $TicketSubject ) && null !== $TicketSubject) {
//            $Form->setError('TicketSubject', 'Bitte geben Sie ein Thema ein');
//            $Error = true;
//        } elseif (null === $TicketSubject) {
//            $Error = true;
//        } else {
//            $Form->setSuccess('TicketSubject', '');
//        }
//        if (empty( $TicketMessage ) && null !== $TicketMessage) {
//            $Form->setError('TicketMessage', 'Bitte geben Sie ein Mitteilung ein');
//            $Error = true;
//        } elseif (null === $TicketMessage) {
//            $Error = true;
//        } else {
//            $Form->setSuccess('TicketMessage', '');
//        }


//        if ($Error) {
//            /**
//             * Nothing to do
//             */
//            try {
//                /** @var YouTrack $YouTrack */
//                $YouTrack = (new SupportSystem(new YouTrack()))->getSupport();
//                $Form->prependGridGroup($YouTrack->ticketCurrent());
//
//                return $Form;
//            } catch (\Exception $E) {
//                return new Danger('Das Support-System konnte nicht geladen werden');
//            }
//        } else {
            try {
                /** @var YouTrackMail $Config */
                $Config = (new \SPHERE\System\Support\Support(new YouTrackMail()))->getSupport();
                /** @var EdenPhpSmtp $Mail */
                $Mail = Mail::getSmtpMail()->connectServer(
                    $Config->getHost(), $Config->getUsername(), $Config->getPassword(), 465, true
                );

                $Mail->setMailSubject(urldecode($TicketSubject) . ' (' . (new \DateTime('now'))->format('d.m.Y H:i:s') . ')');

                $info = '';
                // extra information
                try  {
                    $info .= 'Host: ' .  Extension::getRequest()->getHost() . '<br>';

                    if (($tblConsumer = Consumer::useService()->getConsumerBySession())) {
                        $info .= 'Mandant: ' . $tblConsumer->getAcronym() . ' (' . $tblConsumer->getType() . ')' . ' - ' . $tblConsumer->getName() . '<br>';
                    }

                    if (($tblAccount = Account::useService()->getAccountBySession())) {
                        $info .= 'Account: ' . $tblAccount->getUsername() . '<br>';
                        if (($tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount))) {
                            $tblPerson = $tblPersonAllByAccount[0];
                            $info .= 'Person: ' . $tblPerson->getFullName() . '<br>';
                        }

                    }

                    $info .= '<br>';
                } catch (\Exception $Exception) {

                }

                $Mail->setMailBody($info . urldecode($TicketMessage));
                $Mail->addRecipientTO($Config->getMail());
                if (isset( $Upload )) {
                    $Mail->addAttachment(new FileParameter($Upload->getLocation().DIRECTORY_SEPARATOR.$Upload->getFilename()));
                }
                $Mail->setFromHeader($Config->getMail());
                $Mail->sendMail();
                $Mail->disconnectServer();

            } catch (\Exception $Exception) {
                return new Danger('Die Fehlermeldung konnte nicht übertragen werden');
            }

            return new Success('Die Fehlermeldung wurde erfolgreich dem Support mitgeteilt');
//        }
    }
}
