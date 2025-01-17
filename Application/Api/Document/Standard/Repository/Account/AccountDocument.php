<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\Account;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Common\Frontend\Text\Repository\Bold;

/**
 * Class AccountDocument
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Account
 */
abstract class AccountDocument extends AbstractDocument
{
    /** @var TblAccount $tblAccount  */
    protected $tblAccount;

    /** @var TblCompany $tblCompany */
    protected $tblCompany;

    /** @var TblAddress $tblAddress */
    protected $tblAddress;

    protected function setResponsibilityCompany()
    {
        if (($tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll())
            && ($tblResponsibility = current($tblResponsibilityAll))
        ) {
            $this->tblCompany = $tblResponsibility->getServiceTblCompany();
        } else {
            $this->tblCompany = new TblCompany();
            $this->tblCompany->setName('Schulträger');
        }

        if ($this->tblCompany) {
            $this->tblAddress = $this->tblCompany->fetchMainAddress();
        }
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function setHeader($marginTop = '160px')
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent(new Bold($this->tblCompany ? $this->tblCompany->getName() : ''))
                ->styleMarginTop($marginTop)
            )
            ->addElement((new Element())
                ->setContent('z.Hd. '
                    . ($this->getTblPerson()
                        ? $this->getTblPerson()->getSalutation() . ' ' . $this->getTblPerson()->getLastName()
                        : ''
                    )
                )
            )
            ->addElement((new Element())
                ->setContent($this->tblAddress
                    ? $this->tblAddress->getStreetName().' '.$this->tblAddress->getStreetNumber().'<br/>'.
                    $this->tblAddress->getTblCity()->getCode().' '.$this->tblAddress->getTblCity()->getDisplayName()
                    : ''
                )
            )
            ->addElement((new Element())
                ->setContent(
                    ($this->tblAddress ? $this->tblAddress->getTblCity()->getName() : 'Ort')
                    . ', '
                    . date('d.m.Y')
                )
                ->styleAlignRight()
                ->styleMarginTop('26px')
            )
        ;
    }

    /**
     * @param string $subject
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function setSubject($subject, $marginTop = '40px')
    {
        return (new Slice())
            ->addElement((new Element())
                ->setContent(new Bold($subject))
                ->styleTextSize('16px')
                ->styleMarginTop($marginTop)
            );
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function setSalutation($marginTop = '30px')
    {
        if (($tblPerson = $this->getTblPerson())) {
            switch ($tblPerson->getSalutation()) {
                case 'Frau': $text = 'Sehr geehrte Frau ' . $tblPerson->getLastName(); break;
                case 'Herr': $text = 'Sehr geehrter Herr ' . $tblPerson->getLastName(); break;
                default: $text = 'Sehr geehrte(r) ' . $tblPerson->getLastName();
            }
        } else {
            $text = 'Sehr geehrte(r) Nutzer(in)';
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent($text . ',')
                ->styleMarginTop($marginTop)
            );
    }

    /**
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function setAccountInformation($marginTop = '15px')
    {

        $Live = 'https://schulsoftware.schule';
        $Demo = 'https://demo.schulsoftware.schule';

        $tblConsumer = GatekeeperConsumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_BERLIN && $tblConsumer->getAcronym() !== 'SSB') {
            $Live = 'https://ekbo.schulsoftware.schule';
            $Demo = 'https://ekbodemo.schulsoftware.schule';
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Benutzername: ' . ($this->tblAccount ? $this->tblAccount->getUsername() : '-'))
                ->styleMarginTop($marginTop)
            )
            ->addElement((new Element())
                ->setContent('Passwort: ...')
            )
            ->addElement((new Element())
                ->setContent('Die Live-Version der Schulsoftware erreichen Sie unter folgender Internetadresse: '.$Live.'<br>
                    Die Demo-Version der Schulsoftware erreichen Sie unter folgender Internetadresse: '.$Demo
                )
                ->styleMarginTop('15px')
            )
            ->addElement((new Element())
                ->setContent('Sie gelangen automatisch zu folgender Anmeldeoberfläche. Bitte geben Sie im 1. Schritt
                    Ihren Benutzernamen und Ihr Passwort ein.')
                ->styleMarginTop('15px')
            );
//            ->addElement((new Element\Image('/Common/Style/Resource/Document/login_username.png')));
    }
}