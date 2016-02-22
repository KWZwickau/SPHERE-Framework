<?php

/* @CoreHome/_topBar.twig */
class __TwigTemplate_863a99a05bea5b4fdeb6ba7d82b4f3141fd78c9cf37c8be2d0e655594465c58f extends Twig_Template
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
        echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.beforeTopBar", (isset($context["userAlias"]) ? $context["userAlias"] : $this->getContext($context, "userAlias")), (isset($context["userLogin"]) ? $context["userLogin"] : $this->getContext($context, "userLogin")), (isset($context["topMenu"]) ? $context["topMenu"] : $this->getContext($context, "topMenu")), (isset($context["userMenu"]) ? $context["userMenu"] : $this->getContext($context, "userMenu"))));
        echo "
<ul role=\"menubar\" class=\"navbar-right\">

    ";
        // line 11
        echo "
    ";
        // line 22
        echo "
    ";
        // line 23
        if ( !array_key_exists("topMenuModule", $context)) {
            // line 24
            echo "        ";
            $context["topMenuModule"] = (isset($context["currentModule"]) ? $context["currentModule"] : $this->getContext($context, "currentModule"));
            // line 25
            echo "        ";
            $context["topMenuAction"] = (isset($context["currentAction"]) ? $context["currentAction"] : $this->getContext($context, "currentAction"));
            // line 26
            echo "    ";
        }
        // line 27
        echo "
    ";
        // line 28
        ob_start();
        // line 29
        echo "        ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["topMenu"]) ? $context["topMenu"] : $this->getContext($context, "topMenu")));
        foreach ($context['_seq'] as $context["label"] => $context["menu"]) {
            // line 30
            echo "            <li role=\"menuitem\">";
            echo $this->getAttribute($this, "topMenuItem", array(0 => $context["label"], 1 => $context["menu"], 2 => (isset($context["topMenuModule"]) ? $context["topMenuModule"] : $this->getContext($context, "topMenuModule")), 3 => (isset($context["topMenuAction"]) ? $context["topMenuAction"] : $this->getContext($context, "topMenuAction"))), "method");
            echo "</li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['label'], $context['menu'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 32
        echo "    ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        // line 33
        echo "
</ul>";
    }

    // line 4
    public function getmenuItemLabel($__label__ = null, $__icon__ = null)
    {
        $context = $this->env->mergeGlobals(array(
            "label" => $__label__,
            "icon" => $__icon__,
            "varargs" => func_num_args() > 2 ? array_slice(func_get_args(), 2) : array(),
        ));

        $blocks = array();

        ob_start();
        try {
            // line 5
            echo "        ";
            if (((array_key_exists("icon", $context) && (isset($context["icon"]) ? $context["icon"] : $this->getContext($context, "icon"))) && (is_string($__internal_4e48aa8884934ec55ad99a89e823c3c1c4e391d8996067b2cd8c2c6ca8bea0c8 = (isset($context["icon"]) ? $context["icon"] : $this->getContext($context, "icon"))) && is_string($__internal_b6720f806e7539fd67878c45ea3b406ab4f978a8c7f9c08d3da0d624131b2f32 = "icon-") && ('' === $__internal_b6720f806e7539fd67878c45ea3b406ab4f978a8c7f9c08d3da0d624131b2f32 || 0 === strpos($__internal_4e48aa8884934ec55ad99a89e823c3c1c4e391d8996067b2cd8c2c6ca8bea0c8, $__internal_b6720f806e7539fd67878c45ea3b406ab4f978a8c7f9c08d3da0d624131b2f32))))) {
                // line 6
                echo "            <span class=\"";
                echo twig_escape_filter($this->env, strip_tags((isset($context["icon"]) ? $context["icon"] : $this->getContext($context, "icon"))), "html", null, true);
                echo "\"></span>
        ";
            } else {
                // line 8
                echo "            ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array((isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")))), "html", null, true);
                echo "
        ";
            }
            // line 10
            echo "    ";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    // line 12
    public function gettopMenuItem($__label__ = null, $__menu__ = null, $__currentModule__ = null, $__currentAction__ = null)
    {
        $context = $this->env->mergeGlobals(array(
            "label" => $__label__,
            "menu" => $__menu__,
            "currentModule" => $__currentModule__,
            "currentAction" => $__currentAction__,
            "varargs" => func_num_args() > 4 ? array_slice(func_get_args(), 4) : array(),
        ));

        $blocks = array();

        ob_start();
        try {
            // line 13
            echo "        ";
            if ($this->getAttribute((isset($context["menu"]) ? $context["menu"] : null), "_html", array(), "any", true, true)) {
                // line 14
                echo "            ";
                echo $this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_html", array());
                echo "
        ";
            } else {
                // line 16
                echo "            <a ";
                if ($this->getAttribute((isset($context["menu"]) ? $context["menu"] : null), "_tooltip", array(), "any", true, true)) {
                    echo "title=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_tooltip", array()), "html", null, true);
                    echo "\"";
                }
                // line 17
                echo "               class=\"topBarElem ";
                if ((($this->getAttribute($this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_url", array()), "module", array()) == (isset($context["currentModule"]) ? $context["currentModule"] : $this->getContext($context, "currentModule"))) && (twig_test_empty($this->getAttribute($this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_url", array()), "action", array())) || ($this->getAttribute($this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_url", array()), "action", array()) == (isset($context["currentAction"]) ? $context["currentAction"] : $this->getContext($context, "currentAction")))))) {
                    echo "active";
                }
                echo "\"
               id=\"topmenu-";
                // line 18
                echo twig_escape_filter($this->env, twig_lower_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_url", array()), "module", array())), "html", null, true);
                echo "\"
               href=\"index.php";
                // line 19
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('urlRewriteWithParameters')->getCallable(), array($this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_url", array()))), "html", null, true);
                echo "\" tabindex=\"3\">";
                echo $this->getAttribute($this, "menuItemLabel", array(0 => (isset($context["label"]) ? $context["label"] : $this->getContext($context, "label")), 1 => $this->getAttribute((isset($context["menu"]) ? $context["menu"] : $this->getContext($context, "menu")), "_icon", array())), "method");
                echo "</a>
        ";
            }
            // line 21
            echo "    ";
        } catch (Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
    }

    public function getTemplateName()
    {
        return "@CoreHome/_topBar.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  157 => 21,  150 => 19,  146 => 18,  139 => 17,  132 => 16,  126 => 14,  123 => 13,  108 => 12,  97 => 10,  91 => 8,  85 => 6,  82 => 5,  69 => 4,  64 => 33,  61 => 32,  52 => 30,  47 => 29,  45 => 28,  42 => 27,  39 => 26,  36 => 25,  33 => 24,  31 => 23,  28 => 22,  25 => 11,  19 => 1,);
    }
}
/* {{ postEvent("Template.beforeTopBar", userAlias, userLogin, topMenu, userMenu) }}*/
/* <ul role="menubar" class="navbar-right">*/
/* */
/*     {% macro menuItemLabel(label, icon) %}*/
/*         {% if icon is defined and icon and icon starts with 'icon-' %}*/
/*             <span class="{{ icon|striptags }}"></span>*/
/*         {% else %}*/
/*             {{ label|translate }}*/
/*         {% endif %}*/
/*     {% endmacro %}*/
/* */
/*     {% macro topMenuItem(label, menu, currentModule, currentAction) %}*/
/*         {% if menu._html is defined %}*/
/*             {{ menu._html|raw }}*/
/*         {% else %}*/
/*             <a {% if menu._tooltip is defined %}title="{{ menu._tooltip }}"{% endif %}*/
/*                class="topBarElem {% if (menu._url.module == currentModule and (menu._url.action is empty or menu._url.action == currentAction)) %}active{% endif %}"*/
/*                id="topmenu-{{ menu._url.module|lower }}"*/
/*                href="index.php{{ menu._url|urlRewriteWithParameters }}" tabindex="3">{{ _self.menuItemLabel(label, menu._icon) }}</a>*/
/*         {% endif %}*/
/*     {% endmacro %}*/
/* */
/*     {% if topMenuModule is not defined %}*/
/*         {% set topMenuModule = currentModule %}*/
/*         {% set topMenuAction = currentAction %}*/
/*     {% endif %}*/
/* */
/*     {% spaceless %}*/
/*         {% for label,menu in topMenu %}*/
/*             <li role="menuitem">{{ _self.topMenuItem(label, menu, topMenuModule, topMenuAction) }}</li>*/
/*         {% endfor %}*/
/*     {% endspaceless %}*/
/* */
/* </ul>*/
