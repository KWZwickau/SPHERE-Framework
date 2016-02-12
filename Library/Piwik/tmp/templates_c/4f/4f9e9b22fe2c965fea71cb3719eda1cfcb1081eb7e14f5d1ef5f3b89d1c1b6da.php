<?php

/* dashboard.twig */
class __TwigTemplate_410b39cdd932147335aa16a0a28abac31463b03fa9c00051a603dd33f21f1de4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("layout.twig", "dashboard.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'pageDescription' => array($this, 'block_pageDescription'),
            'body' => array($this, 'block_body'),
            'root' => array($this, 'block_root'),
            'topcontrols' => array($this, 'block_topcontrols'),
            'notification' => array($this, 'block_notification'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 11
        ob_start();
        echo (isset($context["siteName"]) ? $context["siteName"] : $this->getContext($context, "siteName"));
        if (array_key_exists("prettyDateLong", $context)) {
            echo " - ";
            echo twig_escape_filter($this->env, (isset($context["prettyDateLong"]) ? $context["prettyDateLong"] : $this->getContext($context, "prettyDateLong")), "html", null, true);
        }
        echo " - ";
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CoreHome_WebAnalyticsReports")), "html", null, true);
        $context["title"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 15
        $context["bodyClass"] = call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.bodyClass", "dashboard"));
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        // line 4
        echo "    ";
        $this->displayParentBlock("head", $context, $blocks);
        echo "

    <!--[if lt IE 9]>
    <script language=\"javascript\" type=\"text/javascript\" src=\"libs/jqplot/excanvas.min.js\"></script>
    <![endif]-->
";
    }

    // line 13
    public function block_pageDescription($context, array $blocks = array())
    {
        echo "Web Analytics report for ";
        echo twig_escape_filter($this->env, (isset($context["siteName"]) ? $context["siteName"] : $this->getContext($context, "siteName")), "html_attr");
        echo " - Piwik";
    }

    // line 17
    public function block_body($context, array $blocks = array())
    {
        // line 18
        echo "    ";
        $this->displayParentBlock("body", $context, $blocks);
        echo "
    ";
        // line 19
        echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.footer"));
        echo "
";
    }

    // line 22
    public function block_root($context, array $blocks = array())
    {
        // line 23
        echo "    ";
        $this->loadTemplate("@CoreHome/_warningInvalidHost.twig", "dashboard.twig", 23)->display($context);
        // line 24
        echo "    ";
        $this->loadTemplate("@CoreHome/_topScreen.twig", "dashboard.twig", 24)->display($context);
        // line 25
        echo "
    <div class=\"ui-confirm\" id=\"alert\">
        <h2></h2>
        <input role=\"yes\" type=\"button\" value=\"";
        // line 28
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Ok")), "html", null, true);
        echo "\"/>
    </div>

    ";
        // line 31
        echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.beforeContent", "dashboard", (isset($context["currentModule"]) ? $context["currentModule"] : $this->getContext($context, "currentModule"))));
        echo "

    <div class=\"page\">

        ";
        // line 35
        if ((array_key_exists("menu", $context) && (isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")))) {
            // line 36
            echo "            ";
            $context["menuMacro"] = $this->loadTemplate("@CoreHome/_menu.twig", "dashboard.twig", 36);
            // line 37
            echo "            ";
            echo $context["menuMacro"]->getmenu((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), true, "Menu--dashboard");
            echo "
        ";
        }
        // line 39
        echo "
        <div class=\"pageWrap\">


            <div class=\"top_controls\">
                ";
        // line 44
        $this->displayBlock('topcontrols', $context, $blocks);
        // line 46
        echo "            </div>

            <a name=\"main\"></a>
            ";
        // line 49
        $this->displayBlock('notification', $context, $blocks);
        // line 52
        echo "
            ";
        // line 53
        $this->displayBlock('content', $context, $blocks);
        // line 55
        echo "
            <div class=\"clear\"></div>
        </div>

    </div>
";
    }

    // line 44
    public function block_topcontrols($context, array $blocks = array())
    {
        // line 45
        echo "                ";
    }

    // line 49
    public function block_notification($context, array $blocks = array())
    {
        // line 50
        echo "                ";
        $this->loadTemplate("@CoreHome/_notifications.twig", "dashboard.twig", 50)->display($context);
        // line 51
        echo "            ";
    }

    // line 53
    public function block_content($context, array $blocks = array())
    {
        // line 54
        echo "            ";
    }

    public function getTemplateName()
    {
        return "dashboard.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  171 => 54,  168 => 53,  164 => 51,  161 => 50,  158 => 49,  154 => 45,  151 => 44,  142 => 55,  140 => 53,  137 => 52,  135 => 49,  130 => 46,  128 => 44,  121 => 39,  115 => 37,  112 => 36,  110 => 35,  103 => 31,  97 => 28,  92 => 25,  89 => 24,  86 => 23,  83 => 22,  77 => 19,  72 => 18,  69 => 17,  61 => 13,  50 => 4,  47 => 3,  43 => 1,  41 => 15,  31 => 11,  11 => 1,);
    }
}
/* {% extends 'layout.twig' %}*/
/* */
/* {% block head %}*/
/*     {{ parent() }}*/
/* */
/*     <!--[if lt IE 9]>*/
/*     <script language="javascript" type="text/javascript" src="libs/jqplot/excanvas.min.js"></script>*/
/*     <![endif]-->*/
/* {% endblock %}*/
/* */
/* {% set title %}{{ siteName|raw }}{% if prettyDateLong is defined %} - {{ prettyDateLong }}{% endif %} - {{ 'CoreHome_WebAnalyticsReports'|translate }}{% endset %}*/
/* */
/* {% block pageDescription %}Web Analytics report for {{ siteName|escape("html_attr") }} - Piwik{% endblock %}*/
/* */
/* {% set bodyClass = postEvent('Template.bodyClass', 'dashboard') %}*/
/* */
/* {% block body %}*/
/*     {{ parent() }}*/
/*     {{ postEvent("Template.footer") }}*/
/* {% endblock %}*/
/* */
/* {% block root %}*/
/*     {% include "@CoreHome/_warningInvalidHost.twig" %}*/
/*     {% include "@CoreHome/_topScreen.twig" %}*/
/* */
/*     <div class="ui-confirm" id="alert">*/
/*         <h2></h2>*/
/*         <input role="yes" type="button" value="{{ 'General_Ok'|translate }}"/>*/
/*     </div>*/
/* */
/*     {{ postEvent("Template.beforeContent", "dashboard", currentModule) }}*/
/* */
/*     <div class="page">*/
/* */
/*         {% if (menu is defined and menu) %}*/
/*             {% import '@CoreHome/_menu.twig' as menuMacro %}*/
/*             {{ menuMacro.menu(menu, true, 'Menu--dashboard') }}*/
/*         {% endif %}*/
/* */
/*         <div class="pageWrap">*/
/* */
/* */
/*             <div class="top_controls">*/
/*                 {% block topcontrols %}*/
/*                 {% endblock %}*/
/*             </div>*/
/* */
/*             <a name="main"></a>*/
/*             {% block notification %}*/
/*                 {% include "@CoreHome/_notifications.twig" %}*/
/*             {% endblock %}*/
/* */
/*             {% block content %}*/
/*             {% endblock %}*/
/* */
/*             <div class="clear"></div>*/
/*         </div>*/
/* */
/*     </div>*/
/* {% endblock %}*/
/* */
