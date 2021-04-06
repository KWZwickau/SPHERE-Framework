<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\Account;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
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
                ->setContent(new Bold($this->tblCompany->getName()))
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
                    ? $this->tblAddress->getGuiTwoRowString(false, false)
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
        return (new Slice())
            ->addElement((new Element())
                ->setContent('Benutzername: ' . ($this->tblAccount ? $this->tblAccount->getUsername() : '-'))
                ->styleMarginTop($marginTop)
            )
            ->addElement((new Element())
                ->setContent('Passwort: ...')
            )
            ->addElement((new Element())
                ->setContent('Die Live-Version der Schulsoftware erreichen Sie unter folgender Internetadresse:
                    https://schulsoftware.schule<br>
                    Die Demo-Version der Schulsoftware erreichen Sie unter folgender Internetadresse:
                    https://demo.schulsoftware.schule'
                )
                ->styleMarginTop('25px')
            )
            ->addElement((new Element())
                ->setContent('Sie gelangen automatisch zu folgender Anmeldeoberfläche. Bitte geben Sie im 1. Schritt
                    Ihren Benutzernamen und Ihr Passwort ein.')
                ->styleMarginTop('15px')
            )
            ->addElement((new Element\Image('/Common/Style/Resource/Document/login_username.png')));
    }
}