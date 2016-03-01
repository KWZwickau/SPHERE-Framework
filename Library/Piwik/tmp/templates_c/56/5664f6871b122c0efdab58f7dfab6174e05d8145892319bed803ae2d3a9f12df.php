<?php

/* @CoreHome/getDefaultIndexView.twig */
class __TwigTemplate_8355fae845f41648cc76ceabe290ac280c185a81cfab4e3fce024376a2b7c219 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("dashboard.twig", "@CoreHome/getDefaultIndexView.twig", 1);
        $this->blocks = array(
            'topcontrols' => array($this, 'block_topcontrols'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "dashboard.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 2
        $context["ajax"] = $this->loadTemplate("ajaxMacros.twig", "@CoreHome/getDefaultIndexView.twig", 2);
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 4
    public function block_topcontrols($context, array $blocks = array())
    {
        // line 5
        echo "    ";
        $this->loadTemplate("@CoreHome/_siteSelectHeader.twig", "@CoreHome/getDefaultIndexView.twig", 5)->display($context);
        // line 6
        echo "    ";
        $this->loadTemplate("@CoreHome/_periodSelect.twig", "@CoreHome/getDefaultIndexView.twig", 6)->display($context);
        // line 7
        echo "    ";
        echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.nextToCalendar"));
        echo "
    ";
        // line 8
        $this->loadTemplate($context["dashboardSettingsControl"]->getTemplateFile(), "@CoreHome/getDefaultIndexView.twig", 8)->display(array_merge($context, $context["dashboardSettingsControl"]->getTemplateVars()));
        // line 9
        echo "    ";
        $this->loadTemplate("@CoreHome/_headerMessage.twig", "@CoreHome/getDefaultIndexView.twig", 9)->display($context);
    }

    // line 12
    public function block_content($context, array $blocks = array())
    {
        // line 13
        echo "    ";
        echo $context["ajax"]->getrequestErrorDiv(((array_key_exists("emailSuperUser", $context)) ? (_twig_default_filter((isset($context["emailSuperUser"]) ? $context["emailSuperUser"] : $this->getContext($context, "emailSuperUser")), "")) : ("")), (isset($context["arePiwikProAdsEnabled"]) ? $context["arePiwikProAdsEnabled"] : $this->getContext($context, "arePiwikProAdsEnabled")));
        echo "
    ";
        // line 14
        echo $context["ajax"]->getloadingDiv();
        echo "

    <div id=\"content\" class=\"home\">
        ";
        // line 17
        if ((isset($context["content"]) ? $context["content"] : $this->getContext($context, "content"))) {
            echo twig_escape_filter($this->env, (isset($context["content"]) ? $context["content"] : $this->getContext($context, "content")), "html", null, true);
        }
        // line 18
        echo "    </div>
";
    }

    public function getTemplateName()
    {
        return "@CoreHome/getDefaultIndexView.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  71 => 18,  67 => 17,  61 => 14,  56 => 13,  53 => 12,  48 => 9,  46 => 8,  41 => 7,  38 => 6,  35 => 5,  32 => 4,  28 => 1,  26 => 2,  11 => 1,);
    }
}
/* {% extends "dashboard.twig" %}*/
/* {% import 'ajaxMacros.twig' as ajax %}*/
/* */
/* {% block topcontrols %}*/
/*     {% include "@CoreHome/_siteSelectHeader.twig" %}*/
/*     {% include "@CoreHome/_periodSelect.twig" %}*/
/*     {{ postEvent("Template.nextToCalendar") }}*/
/*     {% render dashboardSettingsControl %}*/
/*     {% include "@CoreHome/_headerMessage.twig" %}*/
/* {% endblock %}*/
/* */
/* {% block content %}*/
/*     {{ ajax.requestErrorDiv(emailSuperUser|default(''), arePiwikProAdsEnabled) }}*/
/*     {{ ajax.loadingDiv() }}*/
/* */
/*     <div id="content" class="home">*/
/*         {% if content %}{{ content }}{% endif %}*/
/*     </div>*/
/* {% endblock %}*/
/* */
/* */
