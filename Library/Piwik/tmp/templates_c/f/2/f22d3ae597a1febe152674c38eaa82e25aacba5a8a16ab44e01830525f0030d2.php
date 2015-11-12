<?php

/* @CoreHome/_favicon.twig */
class __TwigTemplate_f22d3ae597a1febe152674c38eaa82e25aacba5a8a16ab44e01830525f0030d2 extends Twig_Template
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
        return "@CoreHome/_favicon.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  27 => 4,  21 => 2,  19 => 1,);
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        if ((((isset($context["isCustomLogo"]) ? $context["isCustomLogo"] : $this->getContext($context, "isCustomLogo")) && array_key_exists("customFavicon", $context)) && (isset($context["customFavicon"]) ? $context["customFavicon"] : $this->getContext($context, "customFavicon")))) {
            // line 2
            echo "    <link rel=\"shortcut icon\" href=\"";
            echo twig_escape_filter($this->env, (isset($context["customFavicon"]) ? $context["customFavicon"] : $this->getContext($context, "customFavicon")), "html", null, true);
            echo "\"/>
";
        } else {
            // line 4
            echo "    <link rel=\"shortcut icon\" href=\"plugins/CoreHome/images/favicon.ico\"/>
";
        }
    }
}
