<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESBD;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsbdGymHjInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository\ESBD
 */
class EsbdGymHjInfo extends EsbdStyle
{

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
//        $pageList[] = $this->getPageTwo($personId);

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
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Gymnasium', '(staatlich anerkannte Ersatzschule)'))
            ->addSlice($this->getCertificateHeadConsumer('Halbjahresinformation des Gymnasiums', '5px'))
            ->addSlice($this->getDivisionAndYearConsumer($personId, '20px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSlice($this->getGradeLanes($personId, '14px', false, '5px'))
            ->addSlice($this->getGradeInfo())
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('15px')
                    ->styleTextBold()
                )
            )
            ->addSlice($this->getSubjectLanes($personId, true, array('Lane' => 1, 'Rank' => 3))
                ->styleHeight('270px'))
            ->addSlice(EsbdStyle::getProfile($personId))
            ->addSlice($this->getDescriptionConsumer($personId, '60px', '15px'))
            ->addSlice($this->getMissingConsumer($personId))
            ->addSlice($this->getDateLineConsumer($personId))
            ->addSlice($this->getSignPartConsumer($personId, false))
            ->addSlice($this->getParentSignConsumer())
            ->addSlice($this->getInfoConsumer('20px',
                'Notenerläuterung:',
                '1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)',
                    '¹ &nbsp;&nbsp;&nbsp; Die Bezeichnung des besuchten schulspezifischen Profils ist anzugeben. Beim Erlernen einer 
                    dritten Fremdsprache ist anstelle des Profils die Fremdsprache anzugeben.'
            ))
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
            ->addSlice($this->getHeadConsumer('Evangelisches Schulzentrum Bad Düben - Gymnasium', '(staatlich anerkannte Ersatzschule)'))
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
            ->addSlice($this->getDivisionAndYearConsumer($personId, '10px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentNameConsumer($personId))
            ->addSliceArray($this->getSecondPageDescription($personId))
            ->addSlice($this->getBottomLineConsumer());
    }
}
