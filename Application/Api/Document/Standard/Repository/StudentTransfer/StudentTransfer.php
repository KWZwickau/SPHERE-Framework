<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Text\Repository\Code;

/**
 * Class StudentTransfer
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer
 */
class StudentTransfer extends AbstractDocument
{
    /**
     * StudentTransfer constructor.
     *
     * @param array $Data
     */
    function __construct($Data)
    {
        $this->setData($Data);
        $PersonId = false;
        if (isset($this->Data['PersonId'])) {
            $PersonId = $this->Data['PersonId'];
        }
        if ($PersonId && ($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $this->tblPerson = $tblPerson;
        }
    }

    /**
     * @var array
     */
    private $Data = array();

    /**
     * @var TblPerson|false
     */
    private $tblPerson = false;

    private function setData($Data)
    {

        $this->Data = $Data;
    }

//    /**
//     * @return false|TblDivision
//     */
//    public function getTblDivision()
//    {
//        if (null === $this->tblDivision) {
//            return false;
//        } else {
//            return $this->tblDivision;
//        }
//    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerüberweisung';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Slice
     */
    public function getStudentTransfer()
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Test')
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    public function getStudentTransferHead()
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Abgebende Schule')
            ->styleBorderTop()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent('Evangelische Schulgemeinschaft Erzgebierge Staatlich anerkanntes Gymnasium / Stattlich')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addElement((new Element())
            ->setContent(new Code(print_r($this->Data, true)))
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );

        return $Slice;
    }

    /**
     * @return Page
     */
    public function buildPage()
    {
        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn(
                        $this->getStudentTransferHead()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
            )
            ->addSlice($this->getStudentTransfer());
    }
}