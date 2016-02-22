<?php

/* @CoreHome\_uiControl.twig */
class __TwigTemplate_b9bdb63daa2b2a5631f94bb18699cc48f03d4592811ec2aaafb8235df6ed2b11 extends Twig_Template
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
        echo "<div class=\"";
        echo twig_escape_filter($this->env, (isset($context["cssIdentifier"]) ? $context["cssIdentifier"] : $this->getContext($context, "cssIdentifier")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["cssClass"]) ? $context["cssClass"] : $this->getContext($context, "cssClass")), "html", null, true);
        echo "\"
    ";
        // line 2
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["htmlAttributes"]) ? $context["htmlAttributes"] : $this->getContext($context, "htmlAttributes")));
        foreach ($context['_seq'] as $context["name"] => $context["value"]) {
            // line 3
            echo "         ";
            echo twig_escape_filter($this->env, $context["name"], "html", null, true);
            echo "=\"";
            echo twig_escape_filter($this->env, $context["value"], "html_attr");
            echo "\"
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['name'], $context['value'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 5
        echo "    data-props=\"";
        echo twig_escape_filter($this->env, twig_jsonencode_filter((isset($context["clientSideProperties"]) ? $context["clientSideProperties"] : $this->getContext($context, "clientSideProperties"))), "html", null, true);
        echo "\"
    data-params=\"";
        // line 6
        echo twig_escape_filter($this->env, twig_jsonencode_filter((isset($context["clientSideParameters"]) ? $context["clientSideParameters"] : $this->getContext($context, "clientSideParameters"))), "html", null, true);
        echo "\">
";
        // line 7
        $this->loadTemplate($context["implView"]->getTemplateFile(), "@CoreHome\\_uiControl.twig", 7)->display(array_merge($context, $context["implView"]->getTemplateVars((isset($context["implOverride"]) ? $context["implOverride"] : $this->getContext($context, "implOverride")))));
        // line 8
        echo "</div>
<script>\$(document).ready(function () { require('";
        // line 9
        echo twig_escape_filter($this->env, (isset($context["jsNamespace"]) ? $context["jsNamespace"] : $this->getContext($context, "jsNamespace")), "html", null, true);
        echo "').";
        echo twig_escape_filter($this->env, (isset($context["jsClass"]) ? $context["jsClass"] : $this->getContext($context, "jsClass")), "html", null, true);
        echo ".initElements(); });</script>";
    }

    public function getTemplateName()
    {
        return "@CoreHome\\_uiControl.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  55 => 9,  52 => 8,  50 => 7,  46 => 6,  41 => 5,  30 => 3,  26 => 2,  19 => 1,);
    }
}
/* <div class="{{ cssIdentifier }} {{ cssClass }}"*/
/*     {% for name,value in htmlAttributes %}*/
/*          {{ name }}="{{ value|e('html_attr') }}"*/
/*     {% endfor %}*/
/*     data-props="{{ clientSideProperties|json_encode }}"*/
/*     data-params="{{ clientSideParameters|json_encode }}">*/
/* {% render implView with implOverride %}*/
/* </div>*/
/* <script>$(document).ready(function () { require('{{ jsNamespace }}').{{ jsClass }}.initElements(); });</script>*/
