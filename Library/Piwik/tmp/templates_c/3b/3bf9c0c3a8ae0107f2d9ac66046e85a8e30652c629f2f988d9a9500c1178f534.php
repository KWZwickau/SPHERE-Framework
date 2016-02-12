<?php

/* @CoreHome/_topScreen.twig */
class __TwigTemplate_f1d64ddba6bfaebb610738803a3b33d2bc98e9406d8e201409f0915d846f4afd extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div id=\"header\" class=\"container-fluid\">
    <a href=\"#main\" tabindex=\"1\" class=\"accessibility-skip-to-content\">";
        // line 2
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CoreHome_SkipToContent")), "html", null, true);
        echo "</a>
    <div id=\"topRightBar\" class=\"navbar row\">
        <div class=\"navbar-header col-md-3\">
            <span class=\"toggle-second-menu icon-menu-hamburger\"></span>
            ";
        // line 6
        $this->loadTemplate("@CoreHome/_logo.twig", "@CoreHome/_topScreen.twig", 6)->display($context);
        // line 7
        echo "
            <!-- we need to put button to toggle nav for responsiveness here -->

        </div>
        <div class=\"collapse navbar-collapse col-md-9\" id=\"navbar-collapse1\">
            ";
        // line 12
        $this->loadTemplate("@CoreHome/_topBar.twig", "@CoreHome/_topScreen.twig", 12)->display($context);
        // line 13
        echo "        </div>
    </div>
</div>";
    }

    public function getTemplateName()
    {
        return "@CoreHome/_topScreen.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  40 => 13,  38 => 12,  31 => 7,  29 => 6,  22 => 2,  19 => 1,);
    }
}
/* <div id="header" class="container-fluid">*/
/*     <a href="#main" tabindex="1" class="accessibility-skip-to-content">{{'CoreHome_SkipToContent'|translate}}</a>*/
/*     <div id="topRightBar" class="navbar row">*/
/*         <div class="navbar-header col-md-3">*/
/*             <span class="toggle-second-menu icon-menu-hamburger"></span>*/
/*             {% include "@CoreHome/_logo.twig" %}*/
/* */
/*             <!-- we need to put button to toggle nav for responsiveness here -->*/
/* */
/*         </div>*/
/*         <div class="collapse navbar-collapse col-md-9" id="navbar-collapse1">*/
/*             {% include "@CoreHome/_topBar.twig" %}*/
/*         </div>*/
/*     </div>*/
/* </div>*/
