<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;

/**
 * Class Exchange
 *
 * @package SPHERE\Common\Frontend\Link\Repository
 */
class Exchange extends Extension implements ILinkInterface
{

    const EXCHANGE_TYPE_PLUS = 0;
    const EXCHANGE_TYPE_MINUS = 1;
    /** @var string $Content */
    private $Content = '';

    /**
     * Exchange constructor.
     *
     * @param int   $Type EXCHANGE_TYPE_{PLUS|MINUS}
     * @param array $Data
     */
    public function __construct($Type, $Data = array())
    {

        switch ($Type) {
            case Exchange::EXCHANGE_TYPE_MINUS:
                $Handler = new Center(new MinusSign());
                break;
            case Exchange::EXCHANGE_TYPE_PLUS:
                $Handler = new Center(new PlusSign());
                break;
            default:
                $Handler = new Center(new PlusSign());
                break;
        }

        $this->Content = $Handler.'<span class="ExchangeData" style="display: none;">'.json_encode($Data, JSON_FORCE_OBJECT).'</span>';
    }


    /**
     * @return string
     */
    public function getName()
    {

        return '';
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

        return $this->Content;
    }

}
