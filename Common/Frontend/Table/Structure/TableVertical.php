<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\System\Extension\Extension;

/**
 * Class TableVertical
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class TableVertical extends Extension implements ITemplateInterface
{

    /** @var TableHead[] $TableHead */
    protected $TableHead = array();
    /** @var TableBody[] $TableBody */
    protected $TableBody = array();
    /** @var TableFoot[] $TableFoot */
    protected $TableFoot = array();
    /** @var IBridgeInterface $Template */
    protected $Template = null;
    /** @var string $Hash */
    protected $Hash = '';

    /**
     * @param Object[] $DataList
     * @param Title    $TableTitle
     */
    public function __construct(
        $DataList,
        Title $TableTitle = null
    ) {

        if (!is_array($DataList)) {
            $DataList = array($DataList);
        }

        /** @var TableRow[] $DataList */
        array_walk($DataList, function (&$Row) {

            array_walk($Row, function (&$Column, $Index) {

                if (!is_object($Column) || !$Column instanceof TableColumn) {
                    if ($Index == 0) {
                        $Column = new TableColumn($Column, 1, '1%');
                    } else {
                        $Column = new TableColumn($Column);
                    }
                }
            });
            // Convert to Array
            if (is_object($Row)) {
                /** @var Object $Row */
                $Row = array_filter($Row->__toArray());
            } else {
                $Row = array_filter($Row);
            }
            /** @noinspection PhpParamsInspection */
            $Row = new TableRow($Row);
        });

        $this->TableRow = array(new TableBody($DataList));

        $this->Template = $this->getTemplate(__DIR__.'/TableVertical.twig');
        $this->Template->setVariable('TableTitle', $TableTitle);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable('BodyList', $this->TableRow);
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {

        if (empty( $this->Hash )) {
            $HeadList = $this->TableHead;
            array_walk($HeadList, function (&$H) {

                if (is_object($H)) {
                    $H = serialize($H);
                }
            });
            $BodyList = $this->TableBody;
            array_walk($BodyList, function (&$H) {

                if (is_object($H)) {
                    $H = serialize($H);
                }
            });
            $FootList = $this->TableFoot;
            array_walk($FootList, function (&$H) {

                if (is_object($H)) {
                    $H = serialize($H);
                }
            });
            $this->Hash = md5(json_encode($HeadList) . json_encode($BodyList) . json_encode($FootList));
        }
        return $this->Hash;
    }

    /**
     * @param TableHead $TableHead
     */
    public function appendHead(TableHead $TableHead)
    {

        array_push($this->TableHead, $TableHead);
    }

    /**
     * @param TableHead $TableHead
     */
    public function prependHead(TableHead $TableHead)
    {

        array_unshift($this->TableHead, $TableHead);
    }

    /**
     * @param TableBody $TableBody
     */
    public function appendBody(TableBody $TableBody)
    {

        array_push($this->TableBody, $TableBody);
    }

    /**
     * @param TableBody $TableBody
     */
    public function prependBody(TableBody $TableBody)
    {

        array_unshift($this->TableBody, $TableBody);
    }

    /**
     * @param TableFoot $TableFoot
     */
    public function appendFoot(TableFoot $TableFoot)
    {

        array_push($this->TableFoot, $TableFoot);
    }

    /**
     * @param TableFoot $TableFoot
     */
    public function prependFoot(TableFoot $TableFoot)
    {

        array_unshift($this->TableFoot, $TableFoot);
    }

    /**
     * @return string
     */
    public function getName()
    {

        return '';
    }
}

