<?php

/* @Login/login.twig */
class __TwigTemplate_681d1ac5f4ffd01ce62f85d47df48c7f8d19ce55a373bd087922ef9fae750bba extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@Morpheus/layout.twig", "@Login/login.twig", 1);
        $this->blocks = array(
            'meta' => array($this, 'block_meta'),
            'head' => array($this, 'block_head'),
            'pageDescription' => array($this, 'block_pageDescription'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@Morpheus/layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 26
        ob_start();
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LogIn")), "html", null, true);
        $context["title"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 30
        $context["bodyId"] = "loginPage";
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_meta($context, array $blocks = array())
    {
        // line 4
        echo "    <meta name=\"robots\" content=\"index,follow\">
";
    }

    // line 7
    public function block_head($context, array $blocks = array())
    {
        // line 8
        echo "    ";
        $this->displayParentBlock("head", $context, $blocks);
        echo "

    <script type=\"text/javascript\" src=\"libs/bower_components/jquery-placeholder/jquery.placeholder.js\"></script>
    <!--[if lt IE 9]>
    <script src=\"libs/bower_components/html5shiv/dist/html5shiv.min.js\"></script>
    <![endif]-->
    <script type=\"text/javascript\" src=\"libs/jquery/jquery.smartbanner.js\"></script>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"libs/jquery/stylesheets/jquery.smartbanner.css\" />

    <script type=\"text/javascript\">
        \$(function () {
            \$('#form_login').focus();
            \$('input').placeholder();
            \$.smartbanner({title: \"Piwik Mobile 2\", author: \"Piwik team\", hideOnInstall: false, layer: true, icon: \"plugins/CoreHome/images/googleplay.png\"});
        });
    </script>
";
    }

    // line 28
    public function block_pageDescription($context, array $blocks = array())
    {
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_OpenSourceWebAnalytics")), "html", null, true);
    }

    // line 32
    public function block_body($context, array $blocks = array())
    {
        // line 33
        echo "
    ";
        // line 34
        echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.beforeTopBar", "login"));
        echo "
    ";
        // line 35
        echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.beforeContent", "login"));
        echo "

    ";
        // line 37
        $this->loadTemplate("_iframeBuster.twig", "@Login/login.twig", 37)->display($context);
        // line 38
        echo "
    <div id=\"notificationContainer\">
    </div>

    <div id=\"logo\">
        ";
        // line 43
        if (((isset($context["isCustomLogo"]) ? $context["isCustomLogo"] : $this->getContext($context, "isCustomLogo")) == false)) {
            // line 44
            echo "            <a href=\"http://piwik.org\" title=\"";
            echo twig_escape_filter($this->env, (isset($context["linkTitle"]) ? $context["linkTitle"] : $this->getContext($context, "linkTitle")), "html", null, true);
            echo "\">
        ";
        }
        // line 46
        echo "        ";
        if ((isset($context["hasSVGLogo"]) ? $context["hasSVGLogo"] : $this->getContext($context, "hasSVGLogo"))) {
            // line 47
            echo "            <img src='";
            echo twig_escape_filter($this->env, (isset($context["logoSVG"]) ? $context["logoSVG"] : $this->getContext($context, "logoSVG")), "html", null, true);
            echo "' title=\"";
            echo twig_escape_filter($this->env, (isset($context["linkTitle"]) ? $context["linkTitle"] : $this->getContext($context, "linkTitle")), "html", null, true);
            echo "\" alt=\"Piwik\" class=\"ie-hide\"/>
            <!--[if lt IE 9]>
        ";
        }
        // line 50
        echo "        <img src='";
        echo twig_escape_filter($this->env, (isset($context["logoLarge"]) ? $context["logoLarge"] : $this->getContext($context, "logoLarge")), "html", null, true);
        echo "' title=\"";
        echo twig_escape_filter($this->env, (isset($context["linkTitle"]) ? $context["linkTitle"] : $this->getContext($context, "linkTitle")), "html", null, true);
        echo "\" alt=\"Piwik\" />
        ";
        // line 51
        if ((isset($context["hasSVGLogo"]) ? $context["hasSVGLogo"] : $this->getContext($context, "hasSVGLogo"))) {
            echo "<![endif]-->";
        }
        // line 52
        echo "
        ";
        // line 53
        if ((isset($context["isCustomLogo"]) ? $context["isCustomLogo"] : $this->getContext($context, "isCustomLogo"))) {
            // line 54
            echo "            ";
            ob_start();
            // line 55
            echo "            <i><a href=\"http://piwik.org/\" rel=\"noreferrer\"  target=\"_blank\">";
            echo twig_escape_filter($this->env, (isset($context["linkTitle"]) ? $context["linkTitle"] : $this->getContext($context, "linkTitle")), "html", null, true);
            echo "</a></i>
            ";
            $context["poweredByPiwik"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
            // line 57
            echo "        ";
        }
        // line 58
        echo "
        ";
        // line 59
        if (((isset($context["isCustomLogo"]) ? $context["isCustomLogo"] : $this->getContext($context, "isCustomLogo")) == false)) {
            // line 60
            echo "            </a>
            <div class=\"description\">
                <a href=\"http://piwik.org\" title=\"";
            // line 62
            echo twig_escape_filter($this->env, (isset($context["linkTitle"]) ? $context["linkTitle"] : $this->getContext($context, "linkTitle")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, (isset($context["linkTitle"]) ? $context["linkTitle"] : $this->getContext($context, "linkTitle")), "html", null, true);
            echo "</a>
                <div class=\"arrow\"></div>
            </div>
        ";
        }
        // line 66
        echo "    </div>

    <section class=\"loginSection\">

        ";
        // line 71
        echo "        ";
        if (((array_key_exists("isValidHost", $context) && array_key_exists("invalidHostMessage", $context)) && ((isset($context["isValidHost"]) ? $context["isValidHost"] : $this->getContext($context, "isValidHost")) == false))) {
            // line 72
            echo "            ";
            $this->loadTemplate("@CoreHome/_warningInvalidHost.twig", "@Login/login.twig", 72)->display($context);
            // line 73
            echo "        ";
        } else {
            // line 74
            echo "            <div id=\"message_container\">

                ";
            // line 76
            echo twig_include($this->env, $context, "@Login/_formErrors.twig", array("formErrors" => $this->getAttribute((isset($context["form_data"]) ? $context["form_data"] : $this->getContext($context, "form_data")), "errors", array())));
            echo "

                ";
            // line 78
            if ((isset($context["AccessErrorString"]) ? $context["AccessErrorString"] : $this->getContext($context, "AccessErrorString"))) {
                // line 79
                echo "                    <div class=\"message_error\">
                        <strong>";
                // line 80
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Error")), "html", null, true);
                echo "</strong>: ";
                echo (isset($context["AccessErrorString"]) ? $context["AccessErrorString"] : $this->getContext($context, "AccessErrorString"));
                echo "<br/>
                    </div>
                ";
            }
            // line 83
            echo "
                ";
            // line 84
            if ((isset($context["infoMessage"]) ? $context["infoMessage"] : $this->getContext($context, "infoMessage"))) {
                // line 85
                echo "                    <p class=\"message\">";
                echo (isset($context["infoMessage"]) ? $context["infoMessage"] : $this->getContext($context, "infoMessage"));
                echo "</p>
                ";
            }
            // line 87
            echo "            </div>
            <form ";
            // line 88
            echo $this->getAttribute((isset($context["form_data"]) ? $context["form_data"] : $this->getContext($context, "form_data")), "attributes", array());
            echo " ng-non-bindable>
                <h1>";
            // line 89
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LogIn")), "html", null, true);
            echo "</h1>
                <fieldset class=\"inputs\">
                    <input type=\"text\" name=\"form_login\" id=\"login_form_login\" class=\"input\" value=\"\" size=\"20\"
                           tabindex=\"10\"
                           placeholder=\"";
            // line 93
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Username")), "html", null, true);
            echo "\" autofocus=\"autofocus\"/>
                    <input type=\"password\" name=\"form_password\" id=\"login_form_password\" class=\"input\" value=\"\" size=\"20\"
                           tabindex=\"20\"
                           placeholder=\"";
            // line 96
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Password")), "html", null, true);
            echo "\"/>
                    <input type=\"hidden\" name=\"form_nonce\" id=\"login_form_nonce\" value=\"";
            // line 97
            echo twig_escape_filter($this->env, (isset($context["nonce"]) ? $context["nonce"] : $this->getContext($context, "nonce")), "html", null, true);
            echo "\"/>
                </fieldset>

                <fieldset class=\"actions\">
                    <input name=\"form_rememberme\" type=\"checkbox\" id=\"login_form_rememberme\" value=\"1\" tabindex=\"90\"
                           ";
            // line 102
            if ($this->getAttribute($this->getAttribute((isset($context["form_data"]) ? $context["form_data"] : $this->getContext($context, "form_data")), "form_rememberme", array()), "value", array())) {
                echo "checked=\"checked\" ";
            }
            echo "/>
                    <label for=\"login_form_rememberme\">";
            // line 103
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_RememberMe")), "html", null, true);
            echo "</label>
                    <input class=\"submit\" id='login_form_submit' type=\"submit\" value=\"";
            // line 104
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LogIn")), "html", null, true);
            echo "\"
                           tabindex=\"100\"/>
                </fieldset>
            </form>
            <form id=\"reset_form\" style=\"display:none;\" ng-non-bindable>
                <fieldset class=\"inputs\">
                    <input type=\"text\" name=\"form_login\" id=\"reset_form_login\" class=\"input\" value=\"\" size=\"20\"
                           tabindex=\"10\"
                           placeholder=\"";
            // line 112
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LoginOrEmail")), "html", null, true);
            echo "\"/>
                    <input type=\"hidden\" name=\"form_nonce\" id=\"reset_form_nonce\" value=\"";
            // line 113
            echo twig_escape_filter($this->env, (isset($context["nonce"]) ? $context["nonce"] : $this->getContext($context, "nonce")), "html", null, true);
            echo "\"/>

                    <input type=\"password\" name=\"form_password\" id=\"reset_form_password\" class=\"input\" value=\"\" size=\"20\"
                           tabindex=\"20\" autocomplete=\"off\"
                           placeholder=\"";
            // line 117
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Password")), "html", null, true);
            echo "\"/>

                    <input type=\"password\" name=\"form_password_bis\" id=\"reset_form_password_bis\" class=\"input\" value=\"\"
                           size=\"20\" tabindex=\"30\" autocomplete=\"off\"
                           placeholder=\"";
            // line 121
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_PasswordRepeat")), "html", null, true);
            echo "\"/>
                </fieldset>

                <fieldset class=\"actions\">
                    <span class=\"loadingPiwik\" style=\"display:none;\">
                        <img alt=\"Loading\" src=\"plugins/Morpheus/images/loading-blue.gif\"/>
                    </span>
                    <input class=\"submit\" id='reset_form_submit' type=\"submit\"
                           value=\"";
            // line 129
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ChangePassword")), "html", null, true);
            echo "\" tabindex=\"100\"/>
                </fieldset>

                <input type=\"hidden\" name=\"module\" value=\"";
            // line 132
            echo twig_escape_filter($this->env, (isset($context["loginModule"]) ? $context["loginModule"] : $this->getContext($context, "loginModule")), "html", null, true);
            echo "\"/>
                <input type=\"hidden\" name=\"action\" value=\"resetPassword\"/>
            </form>
            <p id=\"nav\">
                ";
            // line 136
            echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.loginNav", "top"));
            echo "
                <a id=\"login_form_nav\" href=\"#\"
                   title=\"";
            // line 138
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LostYourPassword")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LostYourPassword")), "html", null, true);
            echo "</a>
                <a id=\"alternate_reset_nav\" href=\"#\" style=\"display:none;\"
                   title=\"";
            // line 140
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LogIn")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_LogIn")), "html", null, true);
            echo "</a>
                <a id=\"reset_form_nav\" href=\"#\" style=\"display:none;\"
                   title=\"";
            // line 142
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Mobile_NavigationBack")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Cancel")), "html", null, true);
            echo "</a>
                ";
            // line 143
            echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Template.loginNav", "bottom"));
            echo "
            </p>
            ";
            // line 145
            if (array_key_exists("poweredByPiwik", $context)) {
                // line 146
                echo "                <p id=\"piwik\">
                    ";
                // line 147
                echo twig_escape_filter($this->env, (isset($context["poweredByPiwik"]) ? $context["poweredByPiwik"] : $this->getContext($context, "poweredByPiwik")), "html", null, true);
                echo "
                </p>
            ";
            }
            // line 150
            echo "            <div id=\"lost_password_instructions\" style=\"display:none;\">
                <p class=\"message\">";
            // line 151
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Login_ResetPasswordInstructions")), "html", null, true);
            echo "</p>
            </div>
        ";
        }
        // line 154
        echo "    </section>

";
    }

    public function getTemplateName()
    {
        return "@Login/login.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  360 => 154,  354 => 151,  351 => 150,  345 => 147,  342 => 146,  340 => 145,  335 => 143,  329 => 142,  322 => 140,  315 => 138,  310 => 136,  303 => 132,  297 => 129,  286 => 121,  279 => 117,  272 => 113,  268 => 112,  257 => 104,  253 => 103,  247 => 102,  239 => 97,  235 => 96,  229 => 93,  222 => 89,  218 => 88,  215 => 87,  209 => 85,  207 => 84,  204 => 83,  196 => 80,  193 => 79,  191 => 78,  186 => 76,  182 => 74,  179 => 73,  176 => 72,  173 => 71,  167 => 66,  158 => 62,  154 => 60,  152 => 59,  149 => 58,  146 => 57,  140 => 55,  137 => 54,  135 => 53,  132 => 52,  128 => 51,  121 => 50,  112 => 47,  109 => 46,  103 => 44,  101 => 43,  94 => 38,  92 => 37,  87 => 35,  83 => 34,  80 => 33,  77 => 32,  71 => 28,  49 => 8,  46 => 7,  41 => 4,  38 => 3,  34 => 1,  32 => 30,  28 => 26,  11 => 1,);
    }
}
/* {% extends '@Morpheus/layout.twig' %}*/
/* */
/* {% block meta %}*/
/*     <meta name="robots" content="index,follow">*/
/* {% endblock %}*/
/* */
/* {% block head %}*/
/*     {{ parent() }}*/
/* */
/*     <script type="text/javascript" src="libs/bower_components/jquery-placeholder/jquery.placeholder.js"></script>*/
/*     <!--[if lt IE 9]>*/
/*     <script src="libs/bower_components/html5shiv/dist/html5shiv.min.js"></script>*/
/*     <![endif]-->*/
/*     <script type="text/javascript" src="libs/jquery/jquery.smartbanner.js"></script>*/
/*     <link rel="stylesheet" type="text/css" href="libs/jquery/stylesheets/jquery.smartbanner.css" />*/
/* */
/*     <script type="text/javascript">*/
/*         $(function () {*/
/*             $('#form_login').focus();*/
/*             $('input').placeholder();*/
/*             $.smartbanner({title: "Piwik Mobile 2", author: "Piwik team", hideOnInstall: false, layer: true, icon: "plugins/CoreHome/images/googleplay.png"});*/
/*         });*/
/*     </script>*/
/* {% endblock %}*/
/* */
/* {% set title %}{{ 'Login_LogIn'|translate }}{% endset %}*/
/* */
/* {% block pageDescription %}{{ 'General_OpenSourceWebAnalytics'|translate }}{% endblock %}*/
/* */
/* {% set bodyId = 'loginPage' %}*/
/* */
/* {% block body %}*/
/* */
/*     {{ postEvent("Template.beforeTopBar", "login") }}*/
/*     {{ postEvent("Template.beforeContent", "login") }}*/
/* */
/*     {% include "_iframeBuster.twig" %}*/
/* */
/*     <div id="notificationContainer">*/
/*     </div>*/
/* */
/*     <div id="logo">*/
/*         {% if isCustomLogo == false %}*/
/*             <a href="http://piwik.org" title="{{ linkTitle }}">*/
/*         {% endif %}*/
/*         {% if hasSVGLogo %}*/
/*             <img src='{{ logoSVG }}' title="{{ linkTitle }}" alt="Piwik" class="ie-hide"/>*/
/*             <!--[if lt IE 9]>*/
/*         {% endif %}*/
/*         <img src='{{ logoLarge }}' title="{{ linkTitle }}" alt="Piwik" />*/
/*         {% if hasSVGLogo %}<![endif]-->{% endif %}*/
/* */
/*         {% if isCustomLogo %}*/
/*             {% set poweredByPiwik %}*/
/*             <i><a href="http://piwik.org/" rel="noreferrer"  target="_blank">{{ linkTitle }}</a></i>*/
/*             {% endset %}*/
/*         {% endif %}*/
/* */
/*         {% if isCustomLogo == false %}*/
/*             </a>*/
/*             <div class="description">*/
/*                 <a href="http://piwik.org" title="{{ linkTitle }}">{{ linkTitle }}</a>*/
/*                 <div class="arrow"></div>*/
/*             </div>*/
/*         {% endif %}*/
/*     </div>*/
/* */
/*     <section class="loginSection">*/
/* */
/*         {# untrusted host warning #}*/
/*         {% if (isValidHost is defined and invalidHostMessage is defined and isValidHost == false) %}*/
/*             {% include '@CoreHome/_warningInvalidHost.twig' %}*/
/*         {% else %}*/
/*             <div id="message_container">*/
/* */
/*                 {{ include('@Login/_formErrors.twig', {formErrors: form_data.errors } )  }}*/
/* */
/*                 {% if AccessErrorString %}*/
/*                     <div class="message_error">*/
/*                         <strong>{{ 'General_Error'|translate }}</strong>: {{ AccessErrorString|raw }}<br/>*/
/*                     </div>*/
/*                 {% endif %}*/
/* */
/*                 {% if infoMessage %}*/
/*                     <p class="message">{{ infoMessage|raw }}</p>*/
/*                 {% endif %}*/
/*             </div>*/
/*             <form {{ form_data.attributes|raw }} ng-non-bindable>*/
/*                 <h1>{{ 'Login_LogIn'|translate }}</h1>*/
/*                 <fieldset class="inputs">*/
/*                     <input type="text" name="form_login" id="login_form_login" class="input" value="" size="20"*/
/*                            tabindex="10"*/
/*                            placeholder="{{ 'General_Username'|translate }}" autofocus="autofocus"/>*/
/*                     <input type="password" name="form_password" id="login_form_password" class="input" value="" size="20"*/
/*                            tabindex="20"*/
/*                            placeholder="{{ 'General_Password'|translate }}"/>*/
/*                     <input type="hidden" name="form_nonce" id="login_form_nonce" value="{{ nonce }}"/>*/
/*                 </fieldset>*/
/* */
/*                 <fieldset class="actions">*/
/*                     <input name="form_rememberme" type="checkbox" id="login_form_rememberme" value="1" tabindex="90"*/
/*                            {% if form_data.form_rememberme.value %}checked="checked" {% endif %}/>*/
/*                     <label for="login_form_rememberme">{{ 'Login_RememberMe'|translate }}</label>*/
/*                     <input class="submit" id='login_form_submit' type="submit" value="{{ 'Login_LogIn'|translate }}"*/
/*                            tabindex="100"/>*/
/*                 </fieldset>*/
/*             </form>*/
/*             <form id="reset_form" style="display:none;" ng-non-bindable>*/
/*                 <fieldset class="inputs">*/
/*                     <input type="text" name="form_login" id="reset_form_login" class="input" value="" size="20"*/
/*                            tabindex="10"*/
/*                            placeholder="{{ 'Login_LoginOrEmail'|translate }}"/>*/
/*                     <input type="hidden" name="form_nonce" id="reset_form_nonce" value="{{ nonce }}"/>*/
/* */
/*                     <input type="password" name="form_password" id="reset_form_password" class="input" value="" size="20"*/
/*                            tabindex="20" autocomplete="off"*/
/*                            placeholder="{{ 'General_Password'|translate }}"/>*/
/* */
/*                     <input type="password" name="form_password_bis" id="reset_form_password_bis" class="input" value=""*/
/*                            size="20" tabindex="30" autocomplete="off"*/
/*                            placeholder="{{ 'Login_PasswordRepeat'|translate }}"/>*/
/*                 </fieldset>*/
/* */
/*                 <fieldset class="actions">*/
/*                     <span class="loadingPiwik" style="display:none;">*/
/*                         <img alt="Loading" src="plugins/Morpheus/images/loading-blue.gif"/>*/
/*                     </span>*/
/*                     <input class="submit" id='reset_form_submit' type="submit"*/
/*                            value="{{ 'General_ChangePassword'|translate }}" tabindex="100"/>*/
/*                 </fieldset>*/
/* */
/*                 <input type="hidden" name="module" value="{{ loginModule }}"/>*/
/*                 <input type="hidden" name="action" value="resetPassword"/>*/
/*             </form>*/
/*             <p id="nav">*/
/*                 {{ postEvent("Template.loginNav", "top") }}*/
/*                 <a id="login_form_nav" href="#"*/
/*                    title="{{ 'Login_LostYourPassword'|translate }}">{{ 'Login_LostYourPassword'|translate }}</a>*/
/*                 <a id="alternate_reset_nav" href="#" style="display:none;"*/
/*                    title="{{'Login_LogIn'|translate}}">{{ 'Login_LogIn'|translate }}</a>*/
/*                 <a id="reset_form_nav" href="#" style="display:none;"*/
/*                    title="{{ 'Mobile_NavigationBack'|translate }}">{{ 'General_Cancel'|translate }}</a>*/
/*                 {{ postEvent("Template.loginNav", "bottom") }}*/
/*             </p>*/
/*             {% if poweredByPiwik is defined %}*/
/*                 <p id="piwik">*/
/*                     {{ poweredByPiwik }}*/
/*                 </p>*/
/*             {% endif %}*/
/*             <div id="lost_password_instructions" style="display:none;">*/
/*                 <p class="message">{{ 'Login_ResetPasswordInstructions'|translate }}</p>*/
/*             </div>*/
/*         {% endif %}*/
/*     </section>*/
/* */
/* {% endblock %}*/
/* */
