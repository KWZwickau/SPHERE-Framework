<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Frontend;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Exception\TemplateTypeException;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Summary
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Frontend
 */
class Summary extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * @param array $AccountRoleList
     *
     * @throws TemplateTypeException
     */
    function __construct($AccountRoleList)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Summary.twig');

        $this->Template->setVariable('AccountRoleList', $AccountRoleList);
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->getContent();
    }
}
