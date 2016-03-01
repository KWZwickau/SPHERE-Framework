<?php

/* @Dashboard/_widgetFactoryTemplate.twig */
class __TwigTemplate_241f94b14adbae70efffa3fd080409d13e26c2117fd009f0bac13ccb349674a3 extends Twig_Template
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
        echo "<div id=\"widgetTemplate\" style=\"display:none;\">
    <div class=\"widget\">
        <div class=\"widgetTop\">
            <div class=\"buttons\">
                <div class=\"button\" id=\"close\">
                    <span class=\"icon-close\" title=\"";
        // line 6
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Close")), "html", null, true);
        echo "\"></span>
                </div>
                <div class=\"button\" id=\"maximise\">
                    <span class=\"icon-fullscreen\" title=\"";
        // line 9
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Dashboard_Maximise")), "html", null, true);
        echo "\"></span>
                </div>
                <div class=\"button\" id=\"minimise\">
                    <span class=\"icon-minimise\" title=\"";
        // line 12
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Dashboard_Minimise")), "html", null, true);
        echo "\"></span>
                </div>
                <div class=\"button\" id=\"refresh\">
                    <span class=\"icon-reload\" title=\"";
        // line 15
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Refresh")), "html", null, true);
        echo "\"></span>
                </div>
            </div>
            <h3 class=\"widgetName\">";
        // line 18
        if (array_key_exists("widgetName", $context)) {
            echo twig_escape_filter($this->env, (isset($context["widgetName"]) ? $context["widgetName"] : $this->getContext($context, "widgetName")), "html", null, true);
        }
        // line 19
        echo "                <div class=\"widgetNameOffScreen\">
                    ";
        // line 20
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Widget")), "html", null, true);
        echo "
                </div>
            </h3>
        </div>
        <div class=\"widgetContent\">
            <div class=\"widgetLoading\">";
        // line 25
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Dashboard_LoadingWidget")), "html", null, true);
        echo "</div>
        </div>
    </div>
</div>
";
    }

    public function getTemplateName()
    {
        return "@Dashboard/_widgetFactoryTemplate.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  65 => 25,  57 => 20,  54 => 19,  50 => 18,  44 => 15,  38 => 12,  32 => 9,  26 => 6,  19 => 1,);
    }
}
/* <div id="widgetTemplate" style="display:none;">*/
/*     <div class="widget">*/
/*         <div class="widgetTop">*/
/*             <div class="buttons">*/
/*                 <div class="button" id="close">*/
/*                     <span class="icon-close" title="{{ 'General_Close'|translate }}"></span>*/
/*                 </div>*/
/*                 <div class="button" id="maximise">*/
/*                     <span class="icon-fullscreen" title="{{ 'Dashboard_Maximise'|translate }}"></span>*/
/*                 </div>*/
/*                 <div class="button" id="minimise">*/
/*                     <span class="icon-minimise" title="{{ 'Dashboard_Minimise'|translate }}"></span>*/
/*                 </div>*/
/*                 <div class="button" id="refresh">*/
/*                     <span class="icon-reload" title="{{ 'General_Refresh'|translate }}"></span>*/
/*                 </div>*/
/*             </div>*/
/*             <h3 class="widgetName">{% if widgetName is defined %}{{ widgetName }}{% endif %}*/
/*                 <div class="widgetNameOffScreen">*/
/*                     {{ 'General_Widget'|translate }}*/
/*                 </div>*/
/*             </h3>*/
/*         </div>*/
/*         <div class="widgetContent">*/
/*             <div class="widgetLoading">{{ 'Dashboard_LoadingWidget'|translate }}</div>*/
/*         </div>*/
/*     </div>*/
/* </div>*/
/* */
