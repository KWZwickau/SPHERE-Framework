<?php

/* @CoreHome/_siteSelectHeader.twig */
class __TwigTemplate_f5fb043fafd05cc8f8b1fa8b47ca2e7b23901884a265e0d7909e14c4b8acf37d extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    public function getTemplateName()
    {
        return "@CoreHome/_siteSelectHeader.twig";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div class=\"top_bar_sites_selector piwikTopControl\">
    <div piwik-siteselector class=\"sites_autocomplete\"></div>
</div>";
    }
}
