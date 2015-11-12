<?php

/* @Dashboard/_widgetFactoryTemplate.twig */
class __TwigTemplate_4ca7cf5ffe10f032d988c0e44fc764dfe709ef8b60bed1b3763a7e71aabf8121 extends Twig_Template
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
        return "@Dashboard/_widgetFactoryTemplate.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  63 => 23,  55 => 18,  52 => 17,  48 => 16,  43 => 14,  37 => 11,  31 => 8,  25 => 5,  19 => 1,);
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div id=\"widgetTemplate\" style=\"display:none;\">
    <div class=\"widget\">
        <div class=\"widgetTop\">
            <div class=\"button\" id=\"close\">
                <span class=\"icon-close\" title=\"";
        // line 5
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Close")), "html", null, true);
        echo "\"></span>
            </div>
            <div class=\"button\" id=\"maximise\">
                <span class=\"icon-fullscreen\" title=\"";
        // line 8
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Dashboard_Maximise")), "html", null, true);
        echo "\"></span>
            </div>
            <div class=\"button\" id=\"minimise\">
                <span class=\"icon-minimise\" title=\"";
        // line 11
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Dashboard_Minimise")), "html", null, true);
        echo "\"></span>
            </div>
            <div class=\"button\" id=\"refresh\">
                <span class=\"icon-reload\" title=\"";
        // line 14
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Refresh")), "html", null, true);
        echo "\"></span>
            </div>
            <h3 class=\"widgetName\">";
        // line 16
        if (array_key_exists("widgetName", $context)) {
            echo twig_escape_filter($this->env, (isset($context["widgetName"]) ? $context["widgetName"] : $this->getContext($context, "widgetName")), "html", null, true);
        }
        // line 17
        echo "                <div class=\"widgetNameOffScreen\">
                    ";
        // line 18
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Widget")), "html", null, true);
        echo "
                </div>
            </h3>
        </div>
        <div class=\"widgetContent\">
            <div class=\"widgetLoading\">";
        // line 23
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Dashboard_LoadingWidget")), "html", null, true);
        echo "</div>
        </div>
    </div>
</div>
";
    }
}
