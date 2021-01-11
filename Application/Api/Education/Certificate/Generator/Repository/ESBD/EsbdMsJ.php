<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdMsJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository\ESBD
 */
class EsbdMsJ extends EsbdStyle
{

    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     * @return Page[]
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = $this->getPageOne($personId);
        $pageList[] = $this->getPageTwo($personId);

        return $pageList;
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getPageOne($personId)
    {

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Oberschule', '(staatlich anerkannte Ersatzschule)'))
            ->addSlice($this->getCertificateHeadConsumer('Jahreszeugnis der Oberschule', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '20px'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSlice($this->getCourseConsumer($personId))
            ->addSlice($this->getGradeLanes($personId))
            ->addSlice($this->getGradeInfo())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes(
                $personId,
                true,
                array(),
                '14px',
                false,
                false,
                true
            )->styleHeight('310px'))
//            ->addSlice($this->getOrientationStandard($personId))
            ->addSlice($this->getDescriptionConsumer($personId, '80px', '15px'))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getTransferConsumer($personId, '15px'))
            ->addSlice($this->getDateLineConsumer($personId, '15px'))
            ->addSlice($this->getSignPartConsumer($personId, true, '25px'))
            ->addSlice($this->getParentSignConsumer('25px'))
            ->addSlice($this->getInfoConsumer('10px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft; 6 = ungenügend
                (6 = ungenügend nur bei der Bewertung der Leistungen)'))
            ->addSlice($this->getBottomLineConsumer());
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    public function getPageTwo($personId)
    {

        return (new Page())
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Oberschule', '(staatlich anerkannte Ersatzschule)'))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('DIALOGUS')
                    ->styleTextSize('28pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('5px')
                )
            )
//            ->addSlice($this->getCertificateHead('Halbjahreszeugnis der Oberschule', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '10px', 'Schuljahr'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSliceArray($this->getSecondPageDescription($personId))
            ->addSlice($this->getBottomLineConsumer('25px'));
    }
}
