<?php

/* @CorePluginsAdmin/plugins.twig */
class __TwigTemplate_030076a374654fb07b6fc5bb0f9440d272f32b38842a9c76bc31c4b2f4f42269 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("admin.twig", "@CorePluginsAdmin/plugins.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    public function block_content($context, array $blocks = array())
    {
        // line 8
        echo "<div class=\"pluginsManagement\">

    ";
        // line 10
        if (twig_length_filter($this->env, (isset($context["pluginsHavingUpdate"]) ? $context["pluginsHavingUpdate"] : $this->getContext($context, "pluginsHavingUpdate")))) {
            // line 11
            echo "        <h2>";
            echo twig_escape_filter($this->env, twig_length_filter($this->env, (isset($context["pluginsHavingUpdate"]) ? $context["pluginsHavingUpdate"] : $this->getContext($context, "pluginsHavingUpdate"))), "html", null, true);
            echo " Update(s) available</h2>

        <p>";
            // line 13
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CorePluginsAdmin_InfoPluginUpdateIsRecommended")), "html", null, true);
            echo "</p>

        ";
            // line 15
            echo $context["plugins"]->gettablePluginUpdates((isset($context["pluginsHavingUpdate"]) ? $context["pluginsHavingUpdate"] : $this->getContext($context, "pluginsHavingUpdate")), (isset($context["updateNonce"]) ? $context["updateNonce"] : $this->getContext($context, "updateNonce")), (isset($context["activateNonce"]) ? $context["activateNonce"] : $this->getContext($context, "activateNonce")), 0);
            echo "
    ";
        }
        // line 17
        echo "
    <h2 piwik-enriched-headline>";
        // line 18
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), "html", null, true);
        echo "</h2>

    <p>";
        // line 20
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CorePluginsAdmin_PluginsExtendPiwik")), "html", null, true);
        echo "
        ";
        // line 21
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CorePluginsAdmin_OncePluginIsInstalledYouMayActivateHere")), "html", null, true);
        echo "

    ";
        // line 23
        if ( !(isset($context["isPluginsAdminEnabled"]) ? $context["isPluginsAdminEnabled"] : $this->getContext($context, "isPluginsAdminEnabled"))) {
            // line 24
            echo "        <br/>";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CorePluginsAdmin_DoMoreContactPiwikAdmins")), "html", null, true);
            echo "
    ";
        }
        // line 26
        echo "

    ";
        // line 28
        if ((isset($context["isMarketplaceEnabled"]) ? $context["isMarketplaceEnabled"] : $this->getContext($context, "isMarketplaceEnabled"))) {
            // line 29
            echo "        <br />
        ";
            // line 30
            echo call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CorePluginsAdmin_ChangeLookByManageThemes", (("<a href=\"" . call_user_func_array($this->env->getFunction('linkTo')->getCallable(), array(array("action" => "themes")))) . "\">"), "</a>"));
            echo "
    ";
        }
        // line 32
        echo "    </p>

    ";
        // line 34
        echo $context["plugins"]->getpluginsFilter(false, (isset($context["isMarketplaceEnabled"]) ? $context["isMarketplaceEnabled"] : $this->getContext($context, "isMarketplaceEnabled")));
        echo "

    ";
        // line 36
        echo $context["plugins"]->gettablePlugins((isset($context["pluginsInfo"]) ? $context["pluginsInfo"] : $this->getContext($context, "pluginsInfo")), (isset($context["pluginNamesHavingSettings"]) ? $context["pluginNamesHavingSettings"] : $this->getContext($context, "pluginNamesHavingSettings")), (isset($context["activateNonce"]) ? $context["activateNonce"] : $this->getContext($context, "activateNonce")), (isset($context["deactivateNonce"]) ? $context["deactivateNonce"] : $this->getContext($context, "deactivateNonce")), (isset($context["uninstallNonce"]) ? $context["uninstallNonce"] : $this->getContext($context, "uninstallNonce")), false, (isset($context["marketplacePluginNames"]) ? $context["marketplacePluginNames"] : $this->getContext($context, "marketplacePluginNames")), (isset($context["isPluginsAdminEnabled"]) ? $context["isPluginsAdminEnabled"] : $this->getContext($context, "isPluginsAdminEnabled")));
        echo "

</div>
";
    }

    public function getTemplateName()
    {
        return "@CorePluginsAdmin/plugins.twig";
    }

    // line 7

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  108 => 36,  103 => 34,  99 => 32,  94 => 30,  91 => 29,  89 => 28,  85 => 26,  79 => 24,  77 => 23,  72 => 21,  68 => 20,  63 => 18,  60 => 17,  55 => 15,  50 => 13,  44 => 11,  42 => 10,  38 => 8,  35 => 7,  31 => 1,  27 => 5,  25 => 3,  11 => 1,);
    }

    protected function doGetParent(array $context)
    {
        return "admin.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 3
        $context["plugins"] = $this->loadTemplate("@CorePluginsAdmin/macros.twig", "@CorePluginsAdmin/plugins.twig", 3);
        // line 5
        ob_start();
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("CorePluginsAdmin_PluginsManagement")), "html", null, true);
        $context["title"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }
}
