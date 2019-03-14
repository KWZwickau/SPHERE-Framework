<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 13.03.2019
 * Time: 13:05
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\Billing;


use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class Billing
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\Billing
 */
class Billing
{

    /** @var null|Frame $Document */
    private $Document = null;

    const TEXT_SIZE = '14px';

    public function createSingleDocument(
        TblItem $tblItem,
        TblDocument $tblDocument,
        TblPerson $tblPersonDebtor,
        TblPerson $tblPersonCauser,
        $Year,
        $From,
        $To
    ) {

        $pageList[] = $this->buildPage($tblItem, $tblDocument, $tblPersonDebtor, $tblPersonCauser, $Year, $From, $To);

        $this->Document = $this->buildDocument($pageList);

        return $this->Document->getTemplate();
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    private function buildDocument($pageList = array())
    {
        $document = new Document();

        foreach ($pageList as $subjectPages) {
            if (is_array($subjectPages)) {
                foreach ($subjectPages as $page) {
                    $document->addPage($page);
                }
            } else {
                $document->addPage($subjectPages);
            }
        }

        $InjectStyle = 'body { margin-left: 1.0cm !important; margin-right: 1.0cm !important; }';

        return (new Frame($InjectStyle))->addDocument($document);
    }

    /**
     * @param $Text
     * @param $ItemName
     * @param $Year
     * @param $TotalPrice
     * @param $DebtorSalutation
     * @param $DebtorFirstName
     * @param $DebtorLastName
     * @param $CauserSalutation
     * @param $CauserFirstName
     * @param $CauserLastName
     * @param $From
     * @param $To
     * @param $Date
     * @param $Location
     * @param $ConsumerName
     * @param $ConsumerExtendedName
     * @param $ConsumerAddress
     *
     * @return string
     */
    private function setPlaceholders(
        $Text,
        $ItemName,
        $Year,
        $TotalPrice,
        $DebtorSalutation,
        $DebtorFirstName,
        $DebtorLastName,
        $CauserSalutation,
        $CauserFirstName,
        $CauserLastName,
        $From,
        $To,
        $Date,
        $Location,
        $ConsumerName,
        $ConsumerExtendedName,
        $ConsumerAddress
    ) {
        $Text = str_replace('[Beitragsart]', $ItemName, $Text);
        $Text = str_replace('[Beitragsjahr]', $Year, $Text);
        $Text = str_replace('[Beitragssumme]', $TotalPrice, $Text);
        $Text = str_replace('[Beitragszahler Anrede]', $DebtorSalutation, $Text);
        $Text = str_replace('[Beitragszahler Vorname]', $DebtorFirstName, $Text);
        $Text = str_replace('[Beitragszahler Nachname]', $DebtorLastName, $Text);
        $Text = str_replace('[Beitragsverursacher Anrede]', $CauserSalutation, $Text);
        $Text = str_replace('[Beitragsverursacher Vorname]', $CauserFirstName, $Text);
        $Text = str_replace('[Beitragsverursacher Nachname]', $CauserLastName, $Text);
        $Text = str_replace('[Zeitraum von]', $From, $Text);
        $Text = str_replace('[Zeitraum bis]', $To, $Text);
        $Text = str_replace('[Datum]', $Date, $Text);
        $Text = str_replace('[Ort]', $Location, $Text);
        $Text = str_replace('[Trägername]', $ConsumerName, $Text);
        $Text = str_replace('[Trägerzusatz]', $ConsumerExtendedName, $Text);
        $Text = str_replace('[Trägeradresse]', $ConsumerAddress, $Text);

        return $Text;
    }

    private function buildPage(
        TblItem $tblItem,
        TblDocument $tblDocument,
        TblPerson $tblPersonDebtor,
        TblPerson $tblPersonCauser,
        $Year,
        $From,
        $To
    ) {

        // todo formular data
        $TotalPrice = '20€';
        $companyAddess = Address::useService()->getAddressById(1);
        $ConsumerName = 'Träger';
        $ConsumerExtendedName = 'Zusatz';
        $ConsumerAddress = $companyAddess->getGuiString(false);
        $Location = 'Zwickau';
        $Date = (new \DateTime())->format('d.m.Y');
        if (($tblDocumentInformation = \SPHERE\Application\Billing\Inventory\Document\Document::useService()->getDocumentInformationBy($tblDocument, 'Subject'))) {
            $Subject = $tblDocumentInformation->getValue();
        } else {
            $Subject = '&nbsp;';
        }
        if (($tblDocumentInformation = \SPHERE\Application\Billing\Inventory\Document\Document::useService()->getDocumentInformationBy($tblDocument, 'Content'))) {
            $Content = $tblDocumentInformation->getValue();
        } else {
            $Content = '&nbsp;';
        }

        $ItemName = $tblItem->getName();
        $DebtorSalutation = $tblPersonDebtor->getSalutation();
        $DebtorFirstName = $tblPersonDebtor->getFirstSecondName();
        $DebtorLastName = $tblPersonDebtor->getLastName();
        $CauserSalutation = $tblPersonCauser->getSalutation();
        $CauserFirstName = $tblPersonCauser->getFirstSecondName();
        $CauserLastName = $tblPersonCauser->getLastName();

        $Subject = $this->setPlaceholders(
            $Subject,
            $ItemName,
            $Year,
            $TotalPrice,
            $DebtorSalutation,
            $DebtorFirstName,
            $DebtorLastName,
            $CauserSalutation,
            $CauserFirstName,
            $CauserLastName,
            $From,
            $To,
            $Date,
            $Location,
            $ConsumerName,
            $ConsumerExtendedName,
            $ConsumerAddress
        );

        $Content = $this->setPlaceholders(
            $Content,
            $ItemName,
            $Year,
            $TotalPrice,
            $DebtorSalutation,
            $DebtorFirstName,
            $DebtorLastName,
            $CauserSalutation,
            $CauserFirstName,
            $CauserLastName,
            $From,
            $To,
            $Date,
            $Location,
            $ConsumerName,
            $ConsumerExtendedName,
            $ConsumerAddress
        );

        // todo logo über Mandanten-Einstellung
        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element()))
                // todo exakte Höhe bestimmen
                ->styleHeight('200px')
            )
            ->addSlice($this->getAddressSlice($ConsumerName, $ConsumerExtendedName, $ConsumerAddress, $tblPersonDebtor))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($Location . ', ' . $Date)
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignRight()
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent($Subject)
                    ->styleTextSize('18px')
                    ->styleTextBold()
                    ->styleMarginTop('30px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent(nl2br($Content))
                    ->styleTextSize(self::TEXT_SIZE)
                    ->styleAlignJustify()
                    ->styleMarginTop('25px')
                )
            );
    }

    /**
     * @param $ConsumerName
     * @param $ConsumerExtendedName
     * @param $ConsumerAddress
     * @param TblPerson $tblPersonDebtor
     * @param string $TextSize
     *
     * @return Slice
     */
    private function getAddressSlice(
        $ConsumerName,
        $ConsumerExtendedName,
        $ConsumerAddress,
        TblPerson $tblPersonDebtor,
        $TextSize = '8px'
    ) {

        $address1 = '&nbsp;';
        $address2 = '&nbsp;';
        if(($tblAddress = Address::useService()->getInvoiceAddressByPerson($tblPersonDebtor))) {
            $address1 = $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber();
            if (($tblCity = $tblAddress->getTblCity())) {
                $address2 = $tblCity->getCode() . ' ' . $tblCity->getDisplayName();
            }
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent($ConsumerName . ($ConsumerExtendedName ?  ' ' . $ConsumerExtendedName : ''))
                ->styleTextSize($TextSize)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($ConsumerAddress)
                    ->styleTextSize($TextSize)
                    ->styleBorderBottom()
                , '25%')
                ->addElementColumn((new Element()))
            )
            ->addElement((new Element())
                ->setContent($tblPersonDebtor->getSalutation())
                ->styleTextSize(self::TEXT_SIZE)
                ->styleMarginTop('14px')
            )
            ->addElement((new Element())
                ->setContent($tblPersonDebtor->getFirstSecondName() . ' ' . $tblPersonDebtor->getLastName())
                ->styleTextSize(self::TEXT_SIZE)
            )
            ->addElement((new Element())
                ->setContent($address1)
                ->styleTextSize(self::TEXT_SIZE)
            )
            ->addElement((new Element())
                ->setContent($address2)
                ->styleTextSize(self::TEXT_SIZE)
            )
        ;
    }
}