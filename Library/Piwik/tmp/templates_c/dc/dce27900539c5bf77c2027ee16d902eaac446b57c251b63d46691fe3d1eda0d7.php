<?php

/* @CoreHome/_periodSelect.twig */
class __TwigTemplate_430d892a2fbeeb12942ab59489a72b61ebec4d46f20f7508c7e93c812c7e52dc extends Twig_Template
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
        echo "<div id=\"periodString\" piwik-expand-on-click class=\"piwikTopControl piwikSelector borderedControl periodSelector\">
    <a id=\"date\" class=\"title\" title=\"";
        // line 2
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ChooseDate", ((array_key_exists("prettyDateLong", $context)) ? (_twig_default_filter((isset($context["prettyDateLong"]) ? $context["prettyDateLong"] : $this->getContext($context, "prettyDateLong")), "")) : ("")))), "html_attr");
        echo "\" tabindex=\"4\">
        <span class=\"icon icon-calendar\"></span>
        ";
        // line 4
        echo twig_escape_filter($this->env, (isset($context["prettyDate"]) ? $context["prettyDate"] : $this->getContext($context, "prettyDate")), "html", null, true);
        echo "
    </a>
    <div id=\"periodMore\" class=\"dropdown\">
        <div class=\"period-date\">
            <div id=\"datepicker\"></div>
        </div>
        <div class=\"period-range\" style=\"display:none;\">
            <div id=\"calendarRangeFrom\">
                <h6>";
        // line 12
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_DateRangeFrom")), "html", null, true);
        echo "<input tabindex=\"4\" type=\"text\" id=\"inputCalendarFrom\" name=\"inputCalendarFrom\"/></h6>

                <div id=\"calendarFrom\"></div>
            </div>
            <div id=\"calendarRangeTo\">
                <h6>";
        // line 17
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_DateRangeTo")), "html", null, true);
        echo "<input tabindex=\"4\" type=\"text\" id=\"inputCalendarTo\" name=\"inputCalendarTo\"/></h6>

                <div id=\"calendarTo\"></div>
            </div>
        </div>
        <div class=\"period-type\">
            <h6>";
        // line 23
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Period")), "html", null, true);
        echo "</h6>
\t\t\t<span id=\"otherPeriods\">
            ";
        // line 25
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["periodsNames"]) ? $context["periodsNames"] : $this->getContext($context, "periodsNames")));
        foreach ($context['_seq'] as $context["label"] => $context["thisPeriod"]) {
            // line 26
            echo "                <input type=\"radio\" name=\"period\" id=\"period_id_";
            echo twig_escape_filter($this->env, $context["label"], "html", null, true);
            echo "\" value=\"";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('linkTo')->getCallable(), array(array("period" => $context["label"]))), "html", null, true);
            echo "\"";
            if (($context["label"] == (isset($context["period"]) ? $context["period"] : $this->getContext($context, "period")))) {
                echo " checked=\"checked\"";
            }
            echo " />
                <label for=\"period_id_";
            // line 27
            echo twig_escape_filter($this->env, $context["label"], "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($context["thisPeriod"], "singular", array()), "html", null, true);
            echo "</label>
                <br/>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['label'], $context['thisPeriod'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 30
        echo "\t\t\t</span>
            <input tabindex=\"4\" type=\"submit\" value=\"";
        // line 31
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Apply")), "html", null, true);
        echo "\" id=\"calendarApply\" class=\"btn\"/>
            ";
        // line 32
        $context["ajax"] = $this->loadTemplate("ajaxMacros.twig", "@CoreHome/_periodSelect.twig", 32);
        // line 33
        echo "            ";
        echo $context["ajax"]->getloadingDiv("ajaxLoadingCalendar");
        echo "
        </div>
    </div>
    <div class=\"period-click-tooltip\" style=\"display:none;\">";
        // line 36
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ClickToChangePeriod")), "html", null, true);
        echo "</div>
</div>
";
    }

    public function getTemplateName()
    {
        return "@CoreHome/_periodSelect.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  102 => 36,  95 => 33,  93 => 32,  89 => 31,  86 => 30,  75 => 27,  64 => 26,  60 => 25,  55 => 23,  46 => 17,  38 => 12,  27 => 4,  22 => 2,  19 => 1,);
    }
}
/* <div id="periodString" piwik-expand-on-click class="piwikTopControl piwikSelector borderedControl periodSelector">*/
/*     <a id="date" class="title" title="{{ 'General_ChooseDate'|translate(prettyDateLong|default(''))|e('html_attr') }}" tabindex="4">*/
/*         <span class="icon icon-calendar"></span>*/
/*         {{ prettyDate }}*/
/*     </a>*/
/*     <div id="periodMore" class="dropdown">*/
/*         <div class="period-date">*/
/*             <div id="datepicker"></div>*/
/*         </div>*/
/*         <div class="period-range" style="display:none;">*/
/*             <div id="calendarRangeFrom">*/
/*                 <h6>{{ 'General_DateRangeFrom'|translate }}<input tabindex="4" type="text" id="inputCalendarFrom" name="inputCalendarFrom"/></h6>*/
/* */
/*                 <div id="calendarFrom"></div>*/
/*             </div>*/
/*             <div id="calendarRangeTo">*/
/*                 <h6>{{ 'General_DateRangeTo'|translate }}<input tabindex="4" type="text" id="inputCalendarTo" name="inputCalendarTo"/></h6>*/
/* */
/*                 <div id="calendarTo"></div>*/
/*             </div>*/
/*         </div>*/
/*         <div class="period-type">*/
/*             <h6>{{ 'General_Period'|translate }}</h6>*/
/* 			<span id="otherPeriods">*/
/*             {% for label,thisPeriod in periodsNames %}*/
/*                 <input type="radio" name="period" id="period_id_{{ label }}" value="{{ linkTo( { 'period': label} ) }}"{% if label==period %} checked="checked"{% endif %} />*/
/*                 <label for="period_id_{{ label }}">{{ thisPeriod.singular }}</label>*/
/*                 <br/>*/
/*             {% endfor %}*/
/* 			</span>*/
/*             <input tabindex="4" type="submit" value="{{ 'General_Apply'|translate }}" id="calendarApply" class="btn"/>*/
/*             {% import 'ajaxMacros.twig' as ajax %}*/
/*             {{ ajax.loadingDiv('ajaxLoadingCalendar') }}*/
/*         </div>*/
/*     </div>*/
/*     <div class="period-click-tooltip" style="display:none;">{{ 'General_ClickToChangePeriod'|translate }}</div>*/
/* </div>*/
/* */
