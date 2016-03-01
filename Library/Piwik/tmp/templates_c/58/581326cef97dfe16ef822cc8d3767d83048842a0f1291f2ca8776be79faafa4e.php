<?php

/* ajaxMacros.twig */
class __TwigTemplate_a938403a1587e7dee3427aa1274a535f7addb5b5ad0f3a48cc53460b4b1c7743 extends Twig_Template
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
        // line 4
        echo "
";
        // line 15
        echo "
";
    }

    // line 1
    public function geterrorDiv($__id__ = "ajaxError")
    {
        $context = $this->env->mergeGlobals(array(
            "id" => $__id__,
            "varargs" => func_num_args() > 1 ? array_slice(func_get_args(), 1) : array(),
        ));

        $blocks = array();

        ob_start();
        try {
            // line 2
            echo "    <div id=\"";
            echo twig_escape_filter($this->env, (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id")), "html", null, true);
            echo "\" style=\"display:none\"></div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 5
    public function getloadingDiv($__id__ = "ajaxLoadingDiv")
    {
        $context = $this->env->mergeGlobals(array(
            "id" => $__id__,
            "varargs" => func_num_args() > 1 ? array_slice(func_get_args(), 1) : array(),
        ));

        $blocks = array();

        ob_start();
        try {
            // line 6
            echo "<div id=\"";
            echo twig_escape_filter($this->env, (isset($context["id"]) ? $context["id"] : $this->getContext($context, "id")), "html", null, true);
            echo "\" style=\"display:none;\">
    <div class=\"loadingPiwik\">
        <img src=\"plugins/Morpheus/images/loading-blue.gif\" alt=\"";
            // line 8
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_LoadingData")), "html", null, true);
            echo "\" />";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_LoadingData")), "html", null, true);
            echo "
    </div>
    <div class=\"loadingSegment\">
        ";
            // line 11
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("SegmentEditor_LoadingSegmentedDataMayTakeSomeTime")), "html", null, true);
            echo "
    </div>
</div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 16
    public function getrequestErrorDiv($__emailSuperUser__ = null, $__arePiwikProAdsEnabled__ = false)
    {
        $context = $this->env->mergeGlobals(array(
            "emailSuperUser" => $__emailSuperUser__,
            "arePiwikProAdsEnabled" => $__arePiwikProAdsEnabled__,
            "varargs" => func_num_args() > 2 ? array_slice(func_get_args(), 2) : array(),
        ));

        $blocks = array();

        ob_start();
        try {
            // line 17
            echo "    <div id=\"loadingError\">
        <div class=\"alert alert-danger\">

            ";
            // line 20
            if ((array_key_exists("emailSuperUser", $context) && (isset($context["emailSuperUser"]) ? $context["emailSuperUser"] : $this->getContext($context, "emailSuperUser")))) {
                // line 21
                echo "                ";
                echo call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ErrorRequest", (("<a href=\"mailto:" . (isset($context["emailSuperUser"]) ? $context["emailSuperUser"] : $this->getContext($context, "emailSuperUser"))) . "\">"), "</a>"));
                echo "
            ";
            } else {
                // line 23
                echo "                ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ErrorRequest", "", "")), "html", null, true);
                echo "
            ";
            }
            // line 25
            echo "
            <br /><br />
            ";
            // line 27
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_NeedMoreHelp")), "html", null, true);
            echo "

            <a rel=\"noreferrer\" target=\"_blank\" href=\"https://piwik.org/faq/troubleshooting/faq_19489/\">";
            // line 29
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Faq")), "html", null, true);
            echo "</a> –
            <a rel=\"noreferrer\" target=\"_blank\" href=\"http://forum.piwik.org/\">";
            // line 30
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Feedback_CommunityHelp")), "html", null, true);
            echo "</a>

            ";
            // line 32
            if ((isset($context["arePiwikProAdsEnabled"]) ? $context["arePiwikProAdsEnabled"] : $this->getContext($context, "arePiwikProAdsEnabled"))) {
                // line 33
                echo "                –
                <a rel=\"noreferrer\" target=\"_blank\" href=\"";
                // line 34
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('piwikProCampaignParameters')->getCallable(), array("https://piwik.pro/", "Help", "AjaxError")), "html_attr");
                echo "\">";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Feedback_ProfessionalHelp")), "html", null, true);
                echo "</a>";
            }
            // line 35
            echo ".
        </div>
    </div>
";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "ajaxMacros.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  155 => 35,  149 => 34,  146 => 33,  144 => 32,  139 => 30,  135 => 29,  130 => 27,  126 => 25,  120 => 23,  114 => 21,  112 => 20,  107 => 17,  94 => 16,  79 => 11,  71 => 8,  65 => 6,  53 => 5,  39 => 2,  27 => 1,  22 => 15,  19 => 4,);
    }
}
/* {% macro errorDiv(id='ajaxError') %}*/
/*     <div id="{{ id }}" style="display:none"></div>*/
/* {% endmacro %}*/
/* */
/* {% macro loadingDiv(id='ajaxLoadingDiv') %}*/
/* <div id="{{ id }}" style="display:none;">*/
/*     <div class="loadingPiwik">*/
/*         <img src="plugins/Morpheus/images/loading-blue.gif" alt="{{ 'General_LoadingData'|translate }}" />{{ 'General_LoadingData'|translate }}*/
/*     </div>*/
/*     <div class="loadingSegment">*/
/*         {{ 'SegmentEditor_LoadingSegmentedDataMayTakeSomeTime'|translate }}*/
/*     </div>*/
/* </div>*/
/* {% endmacro %}*/
/* */
/* {% macro requestErrorDiv(emailSuperUser, arePiwikProAdsEnabled = false) %}*/
/*     <div id="loadingError">*/
/*         <div class="alert alert-danger">*/
/* */
/*             {% if emailSuperUser is defined and emailSuperUser %}*/
/*                 {{ 'General_ErrorRequest'|translate('<a href="mailto:' ~ emailSuperUser ~ '">', '</a>')|raw }}*/
/*             {% else %}*/
/*                 {{ 'General_ErrorRequest'|translate('', '') }}*/
/*             {% endif %}*/
/* */
/*             <br /><br />*/
/*             {{ 'General_NeedMoreHelp'|translate }}*/
/* */
/*             <a rel="noreferrer" target="_blank" href="https://piwik.org/faq/troubleshooting/faq_19489/">{{ 'General_Faq'|translate }}</a> –*/
/*             <a rel="noreferrer" target="_blank" href="http://forum.piwik.org/">{{ 'Feedback_CommunityHelp'|translate }}</a>*/
/* */
/*             {% if arePiwikProAdsEnabled %}*/
/*                 –*/
/*                 <a rel="noreferrer" target="_blank" href="{{ 'https://piwik.pro/'|piwikProCampaignParameters('Help', 'AjaxError')|e('html_attr') }}">{{ 'Feedback_ProfessionalHelp'|translate }}</a>*/
/*             {%- endif %}.*/
/*         </div>*/
/*     </div>*/
/* {% endmacro %}*/
