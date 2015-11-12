<?php

/* @IP2Location/index.twig */
class __TwigTemplate_45a0f0ee9bbd4d6dd2c662dadb630b925016956b140dbd347c23a453244cf0a3 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("admin.twig", "@IP2Location/index.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    public function block_content($context, array $blocks = array())
    {
        // line 4
        $context["piwik"] = $this->loadTemplate("macros.twig", "@IP2Location/index.twig", 4);
        // line 5
        if ((isset($context["isSuperUser"]) ? $context["isSuperUser"] : $this->getContext($context, "isSuperUser"))) {
            // line 6
            echo "
\t<h2>";
            // line 7
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_Settings")), "html", null, true);
            echo "</h2>
\t<p>";
            // line 8
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_PluginDescription")), "html", null, true);
            echo "</p>

\t";
            // line 10
            if ((isset($context["dbNotFound"]) ? $context["dbNotFound"] : $this->getContext($context, "dbNotFound"))) {
                // line 11
                echo "\t\t<div style=\"border:2px solid #cc0000;background:#ffffcc;padding:10px;color:#cc3300\">
\t\t\t<h5>";
                // line 12
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_DatabaseNotFound")), "html", null, true);
                echo "</h5>
\t\t\t<p>
\t\t\t\t";
                // line 14
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_DownloadDatabase")), "html", null, true);
                echo " <a href=\"http://lite.ip2location.com/r=piwik\">http://lite.ip2location.com</a>
\t\t\t\t<br />
\t\t\t\t";
                // line 16
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_Instructions")), "html", null, true);
                echo "
\t\t\t</p>
\t\t</div>
\t";
            }
            // line 20
            echo "
\t";
            // line 21
            if ((isset($context["dbOutDated"]) ? $context["dbOutDated"] : $this->getContext($context, "dbOutDated"))) {
                // line 22
                echo "\t\t<div style=\"border:2px solid #ff6600;background:#ffffcc;padding:10px;color:#ff6600\">
\t\t\t<h5>";
                // line 23
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_DatabaseOutDated")), "html", null, true);
                echo "</h5>
\t\t\t<p>
\t\t\t\t";
                // line 25
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_RedownloadDatabase")), "html", null, true);
                echo " <a href=\"http://lite.ip2location.com/r=piwik\">http://lite.ip2location.com</a>
\t\t\t\t<br />
\t\t\t\t";
                // line 27
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_Instructions")), "html", null, true);
                echo "
\t\t\t</p>
\t\t</div>
\t";
            } else {
                // line 31
                echo "\t\t<div style=\"border:2px solid #009900;background:#fff;padding:10px;color:#006600\">
\t\t\t<h5>";
                // line 32
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_Database")), "html", null, true);
                echo "</h5>
\t\t\t<p>
\t\t\t\t<b>";
                // line 34
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_FileName")), "html", null, true);
                echo ":</b> ";
                echo twig_escape_filter($this->env, (isset($context["fileName"]) ? $context["fileName"] : $this->getContext($context, "fileName")), "html", null, true);
                echo "
\t\t\t\t<br />
\t\t\t\t<b>";
                // line 36
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_Date")), "html", null, true);
                echo ":</b> ";
                echo twig_escape_filter($this->env, (isset($context["date"]) ? $context["date"] : $this->getContext($context, "date")), "html", null, true);
                echo "
\t\t\t</p>
\t\t</div>
\t";
            }
            // line 40
            echo "
\t";
            // line 41
            if (((isset($context["dbNotFound"]) ? $context["dbNotFound"] : $this->getContext($context, "dbNotFound")) == false)) {
                // line 42
                echo "\t\t<p>
\t\t\t<form method=\"post\" action=\"";
                // line 43
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('urlRewriteWithParameters')->getCallable(), array(array("action" => "index", "token_auth" => (isset($context["token_auth"]) ? $context["token_auth"] : $this->getContext($context, "token_auth"))))), "html", null, true);
                echo "\">
\t\t\t\t";
                // line 44
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_EnterIPAddress")), "html", null, true);
                echo "<br />
\t\t\t\t";
                // line 45
                if ((isset($context["showResults"]) ? $context["showResults"] : $this->getContext($context, "showResults"))) {
                    // line 46
                    echo "\t\t\t\t\t<table style=\"border:2px solid #0066cc;padding:10px;margin:10px 0 0 0\">
\t\t\t\t\t<col width=\"100\">
\t\t\t\t\t<col width=\"400\">
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td><b>Country</b></td>
\t\t\t\t\t\t<td>";
                    // line 51
                    echo twig_escape_filter($this->env, (isset($context["country"]) ? $context["country"] : $this->getContext($context, "country")), "html", null, true);
                    echo "</td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td><b>Region</b></td>
\t\t\t\t\t\t<td>";
                    // line 55
                    echo twig_escape_filter($this->env, (isset($context["regionName"]) ? $context["regionName"] : $this->getContext($context, "regionName")), "html", null, true);
                    echo "</td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td><b>City</b></td>
\t\t\t\t\t\t<td>";
                    // line 59
                    echo twig_escape_filter($this->env, (isset($context["cityName"]) ? $context["cityName"] : $this->getContext($context, "cityName")), "html", null, true);
                    echo "</td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td><b>Position</b></td>
\t\t\t\t\t\t<td>";
                    // line 63
                    echo twig_escape_filter($this->env, (isset($context["position"]) ? $context["position"] : $this->getContext($context, "position")), "html", null, true);
                    echo "</td>
\t\t\t\t\t</tr>
\t\t\t\t\t</table>
\t\t\t\t";
                }
                // line 67
                echo "\t\t\t\t<label style=\"font-weight:bold\">";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_IPAddress")), "html", null, true);
                echo ":</label>
\t\t\t\t<input type=\"text\" name=\"ipAddress\" value=\"";
                // line 68
                echo twig_escape_filter($this->env, (isset($context["ipAddress"]) ? $context["ipAddress"] : $this->getContext($context, "ipAddress")), "html", null, true);
                echo "\" maxlength=\"15\" />
\t\t\t\t<input type=\"submit\" value=\"";
                // line 69
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("IP2Location_Lookup")), "html", null, true);
                echo "\" name=\"submit\" class=\"submit\" />
\t\t\t</form>
\t\t</p>
\t";
            }
        }
    }

    public function getTemplateName()
    {
        return "@IP2Location/index.twig";
    }

    // line 3

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  180 => 69,  176 => 68,  171 => 67,  164 => 63,  157 => 59,  150 => 55,  143 => 51,  136 => 46,  134 => 45,  130 => 44,  126 => 43,  123 => 42,  121 => 41,  118 => 40,  109 => 36,  102 => 34,  97 => 32,  94 => 31,  87 => 27,  82 => 25,  77 => 23,  74 => 22,  72 => 21,  69 => 20,  62 => 16,  57 => 14,  52 => 12,  49 => 11,  47 => 10,  42 => 8,  38 => 7,  35 => 6,  33 => 5,  31 => 4,  28 => 3,  11 => 1,);
    }

    protected function doGetParent(array $context)
    {
        return "admin.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }
}
