<?php

/* @SitesManager/index.twig */
class __TwigTemplate_47d01dfab970d4ab8469651e3b9a7e783f0b22b31b98d34f95ed01cae8e11a38 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("admin.twig", "@SitesManager/index.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    public function block_content($context, array $blocks = array())
    {
        // line 6
        echo "
    <div ng-include=\"'plugins/SitesManager/templates/index.html?cb=";
        // line 7
        echo twig_escape_filter($this->env, (isset($context["cacheBuster"]) ? $context["cacheBuster"] : $this->getContext($context, "cacheBuster")), "html", null, true);
        echo "'\"></div>

";
    }

    public function getTemplateName()
    {
        return "@SitesManager/index.twig";
    }

    // line 5

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  39 => 7,  36 => 6,  33 => 5,  29 => 1,  25 => 3,  11 => 1,);
    }

    protected function doGetParent(array $context)
    {
        return "admin.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 3
        ob_start();
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("SitesManager_WebsitesManagement")), "html", null, true);
        $context["title"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }
}
