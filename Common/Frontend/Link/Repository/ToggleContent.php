<?php
namespace SPHERE\Common\Frontend\Link\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class ToggleContent
 *
 * @package SPHERE\Common\Frontend\Form\Structure
 */
class ToggleContent extends Extension implements ITemplateInterface
{

    /** @var string $Hash */
    protected $Hash = '';
    /** @var string $Data */
    protected $Data;
    /** @var array $DataList */
    protected $DataList;
    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * @param array $Data
     */
    public function __construct($Data = array())
    {

        $this->Template = $this->getTemplate(__DIR__.'/ToggleContent.twig');
        if(is_array($Data)){
            $this->DataList = $Data;
//            $this->Data = $Data;
            $this->Data = implode('',$Data);
        } else {
            $this->DataList = array($Data);
            $this->Data = $Data;
        }
        $this->Template->setVariable('Data', $this->Data);
        $this->Hash = $this->getHash();
        $this->Template->setVariable('Hash', $this->Hash);
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

        $this->Template->setVariable('Hash', $this->Hash);
        $this->Template->setVariable('Data', $this->Data);
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {
        $HashList = array();

        if (empty($this->Hash)) {
            $DataList = $this->DataList;
            array_walk($DataList, function ($class) use (&$HashList) {
                if (is_object($class)) {
                    $HashList[] = get_class($class);
                    if(strpos(get_class($class), 'CheckBox'))
                    {
                        $HashList[] = $class->getName();
                    }
                } elseif(is_array($class) && empty($class)) {
                    $HashList[] = date('Ymd');
                } elseif(is_array($class)) {
                    $HashList[] = count($class);
                } else {
                    $HashList[] = substr($class, 0, 10);
                }
            });
            $this->Hash = md5(json_encode($HashList) . date('Ymd'));
        }
        return $this->Hash;
    }


}
