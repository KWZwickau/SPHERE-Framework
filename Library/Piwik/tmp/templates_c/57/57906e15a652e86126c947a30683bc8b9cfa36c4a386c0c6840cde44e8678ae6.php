<?php

/* @Live/getLastVisitsStart.twig */
class __TwigTemplate_b98507961e96a72b6e9ad2b840ca2e9cb98fe86b03e56a8cd82a7d6968e0e029 extends Twig_Template
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
        // line 2
        $context["maxPagesDisplayedByVisitor"] = 100;
        // line 3
        echo "
<ul id='visitsLive'>
    ";
        // line 5
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["visitors"]) ? $context["visitors"] : $this->getContext($context, "visitors")));
        foreach ($context['_seq'] as $context["_key"] => $context["visitor"]) {
            // line 6
            echo "        <li id=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "idVisit", array()), "html", null, true);
            echo "\" class=\"visit\">
            <div style=\"display:none;\" class=\"idvisit\">";
            // line 7
            echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "idVisit", array()), "html", null, true);
            echo "</div>
            <div title=\"";
            // line 8
            echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["visitor"], "actionDetails", array())), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Actions")), "html", null, true);
            echo "\" class=\"datetime\">
                <span style=\"display:none;\" class=\"serverTimestamp\">";
            // line 9
            echo $this->getAttribute($context["visitor"], "serverTimestamp", array());
            echo "</span>
                ";
            // line 10
            echo call_user_func_array($this->env->getFunction('postEvent')->getCallable(), array("Live.visitorLogWidgetViewBeforeVisitInfo", $context["visitor"]));
            echo "
                ";
            // line 11
            $context["year"] = twig_date_format_filter($this->env, $this->getAttribute($context["visitor"], "serverTimestamp", array()), "Y");
            // line 12
            echo "                ";
            echo twig_escape_filter($this->env, twig_replace_filter($this->getAttribute($context["visitor"], "serverDatePretty", array()), array((isset($context["year"]) ? $context["year"] : $this->getContext($context, "year")) => " ")), "html", null, true);
            echo " - ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "serverTimePretty", array()), "html", null, true);
            echo " ";
            if (($this->getAttribute($context["visitor"], "visitDuration", array()) > 0)) {
                echo "<em>(";
                echo $this->getAttribute($context["visitor"], "visitDurationPretty", array());
                echo ")</em>";
            }
            // line 13
            echo "                ";
            if ( !twig_test_empty((($this->getAttribute($context["visitor"], "visitorId", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($context["visitor"], "visitorId", array()), false)) : (false)))) {
                // line 14
                echo "                  &nbsp;  <a class=\"visits-live-launch-visitor-profile rightLink\" title=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Live_ViewVisitorProfile")), "html", null, true);
                echo " ";
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "userId", array()))) {
                    echo $this->getAttribute($context["visitor"], "userId", array());
                }
                echo "\" data-visitor-id=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitorId", array()), "html", null, true);
                echo "\">
                        ";
                // line 15
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "userId", array()))) {
                    echo "<br/>";
                }
                // line 16
                echo "                        <img src=\"plugins/Live/images/visitorProfileLaunch.png\"/>
                        ";
                // line 17
                echo (($this->getAttribute($context["visitor"], "userId", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($context["visitor"], "userId", array()), "")) : (""));
                echo "
                    </a>
                ";
            }
            // line 20
            echo "                <br />

                ";
            // line 22
            if ($this->getAttribute($context["visitor"], "countryFlag", array(), "any", true, true)) {
                echo "&nbsp;<img src=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "countryFlag", array()), "html", null, true);
                echo "\" title=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "location", array()), "html", null, true);
                echo ", ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Provider_ColumnProvider")), "html", null, true);
                echo " ";
                if ($this->getAttribute($context["visitor"], "providerName", array(), "any", true, true)) {
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "providerName", array()), "html", null, true);
                }
                echo "\"/>";
            }
            // line 23
            echo "                ";
            if ($this->getAttribute($context["visitor"], "browserIcon", array(), "any", true, true)) {
                echo "&nbsp;<img src=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "browserIcon", array()), "html", null, true);
                echo "\" title=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "browser", array()), "html", null, true);
                if ($this->getAttribute($context["visitor"], "plugins", array(), "any", true, true)) {
                    echo ", ";
                    echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Plugins")), "html", null, true);
                    echo ": ";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "plugins", array()), "html", null, true);
                }
                echo "\"/>";
            }
            // line 24
            echo "                ";
            if ($this->getAttribute($context["visitor"], "operatingSystemIcon", array(), "any", true, true)) {
                echo "&nbsp;<img src=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "operatingSystemIcon", array()), "html", null, true);
                echo "\" title=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "operatingSystem", array()), "html", null, true);
                if ($this->getAttribute($context["visitor"], "resolution", array(), "any", true, true)) {
                    echo ", ";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "resolution", array()), "html", null, true);
                }
                echo "\"/>";
            }
            // line 25
            echo "                &nbsp;
                ";
            // line 26
            if ($this->getAttribute($context["visitor"], "visitConverted", array())) {
                // line 27
                echo "                <span title=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_VisitConvertedNGoals", $this->getAttribute($context["visitor"], "goalConversions", array()))), "html", null, true);
                echo "\" class='visitorRank'>
                    <img src=\"";
                // line 28
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitConvertedIcon", array()), "html", null, true);
                echo "\" />
                    <span class='hash'>#</span>
                    ";
                // line 30
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "goalConversions", array()), "html", null, true);
                echo "
                    ";
                // line 31
                if ($this->getAttribute($context["visitor"], "visitEcommerceStatusIcon", array())) {
                    // line 32
                    echo "
                        <img src=\"";
                    // line 33
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitEcommerceStatusIcon", array()), "html", null, true);
                    echo "\" title=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitEcommerceStatus", array()), "html", null, true);
                    echo "\"/>
                    ";
                }
                // line 35
                echo "                </span>
                ";
            }
            // line 37
            echo "                ";
            if ($this->getAttribute($context["visitor"], "visitorTypeIcon", array())) {
                // line 38
                echo "                    <img src=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitorTypeIcon", array()), "html", null, true);
                echo "\" title=\"";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ReturningVisitor")), "html", null, true);
                echo "\"/>
                ";
            }
            // line 40
            echo "
                ";
            // line 41
            if ($this->getAttribute($context["visitor"], "visitIp", array())) {
                echo " <span title=\"";
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "visitorId", array()))) {
                    echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_VisitorID")), "html", null, true);
                    echo ": ";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitorId", array()), "html", null, true);
                }
                echo "\">
                    IP: ";
                // line 42
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "visitIp", array()), "html", null, true);
                echo "</span>
                ";
            }
            // line 44
            echo "            <!--<div class=\"settings\"></div>-->
            <span class=\"referrer\">
                ";
            // line 46
            if (($this->getAttribute($context["visitor"], "referrerType", array(), "any", true, true) && ($this->getAttribute($context["visitor"], "referrerType", array()) != "direct"))) {
                // line 47
                echo "                    ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_FromReferrer")), "html", null, true);
                echo "
                    ";
                // line 48
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "referrerUrl", array()))) {
                    // line 49
                    echo "                        <a href=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerUrl", array()), "html", null, true);
                    echo "\" rel=\"noreferrer\"  target=\"_blank\">
                    ";
                }
                // line 51
                echo "                    ";
                if ($this->getAttribute($context["visitor"], "searchEngineIcon", array(), "any", true, true)) {
                    // line 52
                    echo "                        <img src=\"";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "searchEngineIcon", array()), "html", null, true);
                    echo "\" />
                    ";
                }
                // line 54
                echo "                    ";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerName", array()), "html", null, true);
                echo "
                    ";
                // line 55
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "referrerUrl", array()))) {
                    // line 56
                    echo "                        </a>
                    ";
                }
                // line 58
                echo "                    ";
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "referrerKeyword", array()))) {
                    echo " - \"";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerKeyword", array()), "html", null, true);
                    echo "\"";
                }
                // line 59
                echo "                    ";
                ob_start();
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerKeyword", array()), "html", null, true);
                $context["keyword"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                // line 60
                echo "                    ";
                ob_start();
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerName", array()), "html", null, true);
                $context["searchName"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                // line 61
                echo "                    ";
                ob_start();
                echo "#";
                echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerKeywordPosition", array()), "html", null, true);
                $context["position"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                // line 62
                echo "                    ";
                if ( !twig_test_empty($this->getAttribute($context["visitor"], "referrerKeywordPosition", array()))) {
                    // line 63
                    echo "                        <span title='";
                    echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Live_KeywordRankedOnSearchResultForThisVisitor", (isset($context["keyword"]) ? $context["keyword"] : $this->getContext($context, "keyword")), (isset($context["position"]) ? $context["position"] : $this->getContext($context, "position")), (isset($context["searchName"]) ? $context["searchName"] : $this->getContext($context, "searchName")))), "html", null, true);
                    echo "' class='visitorRank'>
                            <span class='hash'>#</span> ";
                    // line 64
                    echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "referrerKeywordPosition", array()), "html", null, true);
                    echo "
                        </span>
                    ";
                }
                // line 67
                echo "                ";
            } elseif ($this->getAttribute($context["visitor"], "referrerType", array(), "any", true, true)) {
                // line 68
                echo "                    ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Referrers_DirectEntry")), "html", null, true);
                echo "
                ";
            }
            // line 70
            echo "            </span></div>
            <div id=\"";
            // line 71
            echo twig_escape_filter($this->env, $this->getAttribute($context["visitor"], "idVisit", array()), "html", null, true);
            echo "_actions\" class=\"settings\">
                <span class=\"pagesTitle\" title=\"";
            // line 72
            echo twig_escape_filter($this->env, twig_length_filter($this->env, $this->getAttribute($context["visitor"], "actionDetails", array())), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Actions")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Actions")), "html", null, true);
            echo ":</span>&nbsp;
                ";
            // line 73
            $context["col"] = 0;
            // line 74
            echo "                ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["visitor"], "actionDetails", array()));
            $context['loop'] = array(
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            );
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["action"]) {
                // line 75
                echo "                    ";
                if (($this->getAttribute($context["loop"], "index", array()) <= (isset($context["maxPagesDisplayedByVisitor"]) ? $context["maxPagesDisplayedByVisitor"] : $this->getContext($context, "maxPagesDisplayedByVisitor")))) {
                    // line 76
                    echo "                        ";
                    if ((($this->getAttribute($context["action"], "type", array()) == "ecommerceOrder") || ($this->getAttribute($context["action"], "type", array()) == "ecommerceAbandonedCart"))) {
                        // line 77
                        echo "                            ";
                        ob_start();
                        // line 78
                        if (($this->getAttribute($context["action"], "type", array()) == "ecommerceOrder")) {
                            // line 79
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Goals_EcommerceOrder")), "html", null, true);
                        } else {
                            // line 81
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Goals_AbandonedCart")), "html", null, true);
                        }
                        // line 83
                        echo "
 - ";
                        // line 84
                        if (($this->getAttribute($context["action"], "type", array()) == "ecommerceOrder")) {
                            // line 85
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ColumnRevenue")), "html", null, true);
                            echo ":";
                        } else {
                            // line 87
                            ob_start();
                            // line 88
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ColumnRevenue")), "html", null, true);
                            $context["revenueLeft"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                            // line 90
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Goals_LeftInCart", (isset($context["revenueLeft"]) ? $context["revenueLeft"] : $this->getContext($context, "revenueLeft")))), "html", null, true);
                            echo ":";
                        }
                        // line 91
                        echo " ";
                        echo call_user_func_array($this->env->getFilter('money')->getCallable(), array($this->getAttribute($context["action"], "revenue", array()), (isset($context["idSite"]) ? $context["idSite"] : $this->getContext($context, "idSite"))));
                        // line 93
                        echo "
 - ";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "serverTimePretty", array()), "html", null, true);
                        // line 94
                        echo "
";
                        // line 95
                        if ( !twig_test_empty($this->getAttribute($context["action"], "itemDetails", array()))) {
                            // line 96
                            $context['_parent'] = $context;
                            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["action"], "itemDetails", array()));
                            foreach ($context['_seq'] as $context["_key"] => $context["product"]) {
                                // line 97
                                echo "
# ";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["product"], "itemSKU", array()), "html", null, true);
                                if ( !twig_test_empty($this->getAttribute($context["product"], "itemName", array()))) {
                                    echo ": ";
                                    echo twig_escape_filter($this->env, $this->getAttribute($context["product"], "itemName", array()), "html", null, true);
                                }
                                if ( !twig_test_empty($this->getAttribute($context["product"], "itemCategory", array()))) {
                                    echo " (";
                                    echo twig_escape_filter($this->env, $this->getAttribute($context["product"], "itemCategory", array()), "html", null, true);
                                    echo ")";
                                }
                                echo ", ";
                                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Quantity")), "html", null, true);
                                echo ": ";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["product"], "quantity", array()), "html", null, true);
                                echo ", ";
                                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_Price")), "html", null, true);
                                echo ": ";
                                echo call_user_func_array($this->env->getFilter('money')->getCallable(), array($this->getAttribute($context["product"], "price", array()), (isset($context["idSite"]) ? $context["idSite"] : $this->getContext($context, "idSite"))));
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['product'], $context['_parent'], $context['loop']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                        }
                        // line 100
                        echo "                            ";
                        $context["title"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                        // line 101
                        echo "                            <span title=\"";
                        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), "html", null, true);
                        echo "\">
\t\t\t\t\t\t        <img class='iconPadding' src=\"";
                        // line 102
                        echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "icon", array()), "html", null, true);
                        echo "\"/>
                                ";
                        // line 103
                        if (($this->getAttribute($context["action"], "type", array()) == "ecommerceOrder")) {
                            // line 104
                            echo "                                    ";
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ColumnRevenue")), "html", null, true);
                            echo ": ";
                            echo call_user_func_array($this->env->getFilter('money')->getCallable(), array($this->getAttribute($context["action"], "revenue", array()), (isset($context["idSite"]) ? $context["idSite"] : $this->getContext($context, "idSite"))));
                            echo "
                                ";
                        }
                        // line 106
                        echo "                            </span>
                        ";
                    } else {
                        // line 108
                        echo "                            ";
                        $context["col"] = ((isset($context["col"]) ? $context["col"] : $this->getContext($context, "col")) + 1);
                        // line 109
                        echo "                            ";
                        if (((isset($context["col"]) ? $context["col"] : $this->getContext($context, "col")) >= 9)) {
                            // line 110
                            echo "                                ";
                            $context["col"] = 0;
                            // line 111
                            echo "                            ";
                        }
                        // line 112
                        echo "                            <a href=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "url", array()), "html", null, true);
                        echo "\" target=\"_blank\">
                                ";
                        // line 113
                        if (($this->getAttribute($context["action"], "type", array()) == "action")) {
                            // line 115
                            ob_start();
                            // line 116
                            if ( !twig_test_empty($this->getAttribute($context["action"], "pageTitle", array()))) {
                                echo call_user_func_array($this->env->getFilter('rawSafeDecoded')->getCallable(), array($this->getAttribute($context["action"], "pageTitle", array())));
                            }
                            // line 117
                            echo "
";
                            // line 118
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "serverTimePretty", array()), "html", null, true);
                            echo "
";
                            // line 119
                            if ($this->getAttribute($context["action"], "timeSpentPretty", array(), "any", true, true)) {
                                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_TimeOnPage")), "html", null, true);
                                echo ": ";
                                echo $this->getAttribute($context["action"], "timeSpentPretty", array());
                            }
                            $context["title"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                            // line 121
                            echo "                                    <img src=\"plugins/Live/images/file";
                            echo twig_escape_filter($this->env, (isset($context["col"]) ? $context["col"] : $this->getContext($context, "col")), "html", null, true);
                            echo ".png\" title=\"";
                            echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), "html", null, true);
                            echo "\"/>
                                ";
                        } elseif ((($this->getAttribute(                        // line 122
$context["action"], "type", array()) == "outlink") || ($this->getAttribute($context["action"], "type", array()) == "download"))) {
                            // line 123
                            echo "                                    <img class='iconPadding' src=\"";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "icon", array()), "html", null, true);
                            echo "\"
                                         title=\"";
                            // line 124
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "url", array()), "html", null, true);
                            echo " - ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "serverTimePretty", array()), "html", null, true);
                            echo "\"/>
                                ";
                        } elseif (($this->getAttribute(                        // line 125
$context["action"], "type", array()) == "search")) {
                            // line 126
                            echo "                                    <img class='iconPadding' src=\"";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "icon", array()), "html", null, true);
                            echo "\"
                                         title=\"";
                            // line 127
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Actions_SubmenuSitesearch")), "html", null, true);
                            echo ": ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "siteSearchKeyword", array()), "html", null, true);
                            echo " - ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "serverTimePretty", array()), "html", null, true);
                            echo "\"/>
                                ";
                        } elseif ( !twig_test_empty((($this->getAttribute(                        // line 128
$context["action"], "eventCategory", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($context["action"], "eventCategory", array()), false)) : (false)))) {
                            // line 129
                            echo "                                    <img  class=\"iconPadding\" src='";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "icon", array()), "html", null, true);
                            echo "'
                                        title=\"";
                            // line 130
                            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Events_Event")), "html", null, true);
                            echo " ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "eventCategory", array()), "html", null, true);
                            echo " - ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "eventAction", array()), "html", null, true);
                            echo " ";
                            if ($this->getAttribute($context["action"], "eventName", array(), "any", true, true)) {
                                echo "- ";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "eventName", array()), "html", null, true);
                            }
                            echo " ";
                            if ($this->getAttribute($context["action"], "eventValue", array(), "any", true, true)) {
                                echo "- ";
                                echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "eventValue", array()), "html", null, true);
                            }
                            echo "\"/>
                                ";
                        } else {
                            // line 132
                            echo "                                    <img class='iconPadding' src=\"";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "icon", array()), "html", null, true);
                            echo "\"
                                         title=\"";
                            // line 133
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "goalName", array()), "html", null, true);
                            echo " - ";
                            if (($this->getAttribute($context["action"], "revenue", array()) > 0)) {
                                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("General_ColumnRevenue")), "html", null, true);
                                echo ": ";
                                echo call_user_func_array($this->env->getFilter('money')->getCallable(), array($this->getAttribute($context["action"], "revenue", array()), (isset($context["idSite"]) ? $context["idSite"] : $this->getContext($context, "idSite"))));
                                echo " - ";
                            }
                            echo " ";
                            echo twig_escape_filter($this->env, $this->getAttribute($context["action"], "serverTimePretty", array()), "html", null, true);
                            echo "\"/>
                                ";
                        }
                        // line 135
                        echo "                            </a>
                        ";
                    }
                    // line 137
                    echo "                    ";
                }
                // line 138
                echo "                ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['action'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 139
            echo "                ";
            if ((twig_length_filter($this->env, $this->getAttribute($context["visitor"], "actionDetails", array())) > (isset($context["maxPagesDisplayedByVisitor"]) ? $context["maxPagesDisplayedByVisitor"] : $this->getContext($context, "maxPagesDisplayedByVisitor")))) {
                // line 140
                echo "                    <em>(";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('translate')->getCallable(), array("Live_MorePagesNotDisplayed")), "html", null, true);
                echo ")</em>
                ";
            }
            // line 142
            echo "            </div>
        </li>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['visitor'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 145
        echo "</ul>
<script type=\"text/javascript\">
\$('#visitsLive').on('click', '.visits-live-launch-visitor-profile', function (e) {
    e.preventDefault();
    broadcast.propagateNewPopoverParameter('visitorProfile', \$(this).attr('data-visitor-id'));
    return false;
});
</script>";
    }

    public function getTemplateName()
    {
        return "@Live/getLastVisitsStart.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  582 => 145,  574 => 142,  568 => 140,  565 => 139,  551 => 138,  548 => 137,  544 => 135,  530 => 133,  525 => 132,  506 => 130,  501 => 129,  499 => 128,  491 => 127,  486 => 126,  484 => 125,  478 => 124,  473 => 123,  471 => 122,  464 => 121,  457 => 119,  453 => 118,  450 => 117,  446 => 116,  444 => 115,  442 => 113,  437 => 112,  434 => 111,  431 => 110,  428 => 109,  425 => 108,  421 => 106,  413 => 104,  411 => 103,  407 => 102,  402 => 101,  399 => 100,  373 => 97,  369 => 96,  367 => 95,  364 => 94,  360 => 93,  357 => 91,  353 => 90,  350 => 88,  348 => 87,  344 => 85,  342 => 84,  339 => 83,  336 => 81,  333 => 79,  331 => 78,  328 => 77,  325 => 76,  322 => 75,  304 => 74,  302 => 73,  294 => 72,  290 => 71,  287 => 70,  281 => 68,  278 => 67,  272 => 64,  267 => 63,  264 => 62,  258 => 61,  253 => 60,  248 => 59,  241 => 58,  237 => 56,  235 => 55,  230 => 54,  224 => 52,  221 => 51,  215 => 49,  213 => 48,  208 => 47,  206 => 46,  202 => 44,  197 => 42,  187 => 41,  184 => 40,  176 => 38,  173 => 37,  169 => 35,  162 => 33,  159 => 32,  157 => 31,  153 => 30,  148 => 28,  143 => 27,  141 => 26,  138 => 25,  125 => 24,  110 => 23,  96 => 22,  92 => 20,  86 => 17,  83 => 16,  79 => 15,  68 => 14,  65 => 13,  54 => 12,  52 => 11,  48 => 10,  44 => 9,  38 => 8,  34 => 7,  29 => 6,  25 => 5,  21 => 3,  19 => 2,);
    }
}
/* {# some users view thousands of pages which can crash the browser viewing Live! #}*/
/* {% set maxPagesDisplayedByVisitor=100 %}*/
/* */
/* <ul id='visitsLive'>*/
/*     {% for visitor in visitors %}*/
/*         <li id="{{ visitor.idVisit }}" class="visit">*/
/*             <div style="display:none;" class="idvisit">{{ visitor.idVisit }}</div>*/
/*             <div title="{{ visitor.actionDetails|length }} {{ 'General_Actions'|translate }}" class="datetime">*/
/*                 <span style="display:none;" class="serverTimestamp">{{ visitor.serverTimestamp|raw }}</span>*/
/*                 {{ postEvent('Live.visitorLogWidgetViewBeforeVisitInfo', visitor) }}*/
/*                 {% set year = visitor.serverTimestamp|date('Y') %}*/
/*                 {{ visitor.serverDatePretty|replace({(year): ' '}) }} - {{ visitor.serverTimePretty }} {% if visitor.visitDuration > 0 %}<em>({{ visitor.visitDurationPretty|raw }})</em>{% endif %}*/
/*                 {% if visitor.visitorId|default(false) is not empty %}*/
/*                   &nbsp;  <a class="visits-live-launch-visitor-profile rightLink" title="{{ 'Live_ViewVisitorProfile'|translate }} {% if visitor.userId is not empty %}{{ visitor.userId|raw }}{% endif %}" data-visitor-id="{{ visitor.visitorId }}">*/
/*                         {% if visitor.userId is not empty %}<br/>{% endif %}*/
/*                         <img src="plugins/Live/images/visitorProfileLaunch.png"/>*/
/*                         {{ visitor.userId|default('')|raw }}*/
/*                     </a>*/
/*                 {% endif %}*/
/*                 <br />*/
/* */
/*                 {% if visitor.countryFlag is defined %}&nbsp;<img src="{{ visitor.countryFlag }}" title="{{ visitor.location }}, {{ 'Provider_ColumnProvider'|translate }} {% if visitor.providerName is defined %}{{ visitor.providerName }}{% endif %}"/>{% endif %}*/
/*                 {% if visitor.browserIcon is defined %}&nbsp;<img src="{{ visitor.browserIcon }}" title="{{ visitor.browser }}{% if visitor.plugins is defined %}, {{ 'General_Plugins'|translate }}: {{ visitor.plugins }}{% endif %}"/>{% endif %}*/
/*                 {% if visitor.operatingSystemIcon is defined %}&nbsp;<img src="{{ visitor.operatingSystemIcon }}" title="{{ visitor.operatingSystem }}{% if visitor.resolution is defined %}, {{ visitor.resolution }}{% endif %}"/>{% endif %}*/
/*                 &nbsp;*/
/*                 {% if visitor.visitConverted %}*/
/*                 <span title="{{ 'General_VisitConvertedNGoals'|translate(visitor.goalConversions) }}" class='visitorRank'>*/
/*                     <img src="{{ visitor.visitConvertedIcon }}" />*/
/*                     <span class='hash'>#</span>*/
/*                     {{ visitor.goalConversions }}*/
/*                     {% if visitor.visitEcommerceStatusIcon %}*/
/* */
/*                         <img src="{{ visitor.visitEcommerceStatusIcon }}" title="{{ visitor.visitEcommerceStatus }}"/>*/
/*                     {% endif %}*/
/*                 </span>*/
/*                 {% endif %}*/
/*                 {% if visitor.visitorTypeIcon %}*/
/*                     <img src="{{ visitor.visitorTypeIcon }}" title="{{ 'General_ReturningVisitor'|translate }}"/>*/
/*                 {% endif %}*/
/* */
/*                 {% if visitor.visitIp %} <span title="{% if visitor.visitorId is not empty %}{{ 'General_VisitorID'|translate }}: {{ visitor.visitorId }}{% endif %}">*/
/*                     IP: {{ visitor.visitIp }}</span>*/
/*                 {% endif %}*/
/*             <!--<div class="settings"></div>-->*/
/*             <span class="referrer">*/
/*                 {% if visitor.referrerType is defined and visitor.referrerType != 'direct' %}*/
/*                     {{ 'General_FromReferrer'|translate }}*/
/*                     {% if visitor.referrerUrl is not empty %}*/
/*                         <a href="{{ visitor.referrerUrl }}" rel="noreferrer"  target="_blank">*/
/*                     {% endif %}*/
/*                     {% if visitor.searchEngineIcon is defined %}*/
/*                         <img src="{{ visitor.searchEngineIcon }}" />*/
/*                     {% endif %}*/
/*                     {{ visitor.referrerName }}*/
/*                     {% if visitor.referrerUrl is not empty %}*/
/*                         </a>*/
/*                     {% endif %}*/
/*                     {% if visitor.referrerKeyword is not empty %} - "{{ visitor.referrerKeyword }}"{% endif %}*/
/*                     {% set keyword %}{{ visitor.referrerKeyword }}{% endset %}*/
/*                     {% set searchName %}{{ visitor.referrerName }}{% endset %}*/
/*                     {% set position %}#{{ visitor.referrerKeywordPosition}}{% endset %}*/
/*                     {% if visitor.referrerKeywordPosition is not empty %}*/
/*                         <span title='{{ 'Live_KeywordRankedOnSearchResultForThisVisitor'|translate(keyword,position,searchName) }}' class='visitorRank'>*/
/*                             <span class='hash'>#</span> {{ visitor.referrerKeywordPosition }}*/
/*                         </span>*/
/*                     {% endif %}*/
/*                 {% elseif visitor.referrerType is defined %}*/
/*                     {{ 'Referrers_DirectEntry'|translate }}*/
/*                 {% endif %}*/
/*             </span></div>*/
/*             <div id="{{ visitor.idVisit }}_actions" class="settings">*/
/*                 <span class="pagesTitle" title="{{ visitor.actionDetails|length }} {{ 'General_Actions'|translate }}">{{ 'General_Actions'|translate }}:</span>&nbsp;*/
/*                 {% set col = 0 %}*/
/*                 {% for action in visitor.actionDetails %}*/
/*                     {% if loop.index <= maxPagesDisplayedByVisitor %}*/
/*                         {% if action.type == 'ecommerceOrder' or action.type == 'ecommerceAbandonedCart' %}*/
/*                             {% set title %}*/
/*                                 {%- if action.type == 'ecommerceOrder' %}*/
/*                                     {{- 'Goals_EcommerceOrder'|translate -}}*/
/*                                 {% else %}*/
/*                                     {{- 'Goals_AbandonedCart'|translate -}}*/
/*                                 {% endif %}*/
/*                                 {{- "\n - " -}}*/
/*                                 {%- if action.type == 'ecommerceOrder' -%}*/
/*                                     {{- 'General_ColumnRevenue'|translate -}}:*/
/*                                   {%- else -%}*/
/*                                     {%- set revenueLeft -%}*/
/*                                         {{- 'General_ColumnRevenue'|translate -}}*/
/*                                     {%- endset -%}*/
/*                                     {{- 'Goals_LeftInCart'|translate(revenueLeft) -}}:*/
/*                                 {%- endif %} {{ action.revenue|money(idSite)|raw -}}*/
/* */
/*                                 {{- "\n - " -}}{{- action.serverTimePretty -}}*/
/*                                 {{- "\n" -}}*/
/*                                 {% if action.itemDetails is not empty -%}*/
/*                                     {% for product in action.itemDetails -%}*/
/*                                         {{- "\n# " -}}{{ product.itemSKU }}{% if product.itemName is not empty %}: {{ product.itemName }}{% endif %}{% if product.itemCategory is not empty %} ({{ product.itemCategory }}){% endif %}, {{ 'General_Quantity'|translate }}: {{ product.quantity }}, {{ 'General_Price'|translate }}: {{ product.price|money(idSite)|raw }}*/
/*                                     {%- endfor %}*/
/*                                 {%- endif %}*/
/*                             {% endset %}*/
/*                             <span title="{{- title -}}">*/
/* 						        <img class='iconPadding' src="{{ action.icon }}"/>*/
/*                                 {% if action.type == 'ecommerceOrder' %}*/
/*                                     {{ 'General_ColumnRevenue'|translate }}: {{ action.revenue|money(idSite)|raw }}*/
/*                                 {% endif %}*/
/*                             </span>*/
/*                         {% else %}*/
/*                             {% set col = col + 1 %}*/
/*                             {% if col >= 9 %}*/
/*                                 {% set col = 0 %}*/
/*                             {% endif %}*/
/*                             <a href="{{ action.url }}" target="_blank">*/
/*                                 {% if action.type == 'action' %}*/
/* {# white spacing matters as Chrome tooltip display whitespaces #}*/
/* {% set title %}*/
/* {% if action.pageTitle is not empty %}{{ action.pageTitle|rawSafeDecoded }}{% endif %}*/
/* */
/* {{ action.serverTimePretty }}*/
/* {% if action.timeSpentPretty is defined %}{{ 'General_TimeOnPage'|translate }}: {{ action.timeSpentPretty|raw }}{% endif %}*/
/* {%- endset %}*/
/*                                     <img src="plugins/Live/images/file{{ col }}.png" title="{{- title -}}"/>*/
/*                                 {% elseif action.type == 'outlink' or action.type == 'download' %}*/
/*                                     <img class='iconPadding' src="{{ action.icon }}"*/
/*                                          title="{{ action.url }} - {{ action.serverTimePretty }}"/>*/
/*                                 {% elseif action.type == 'search' %}*/
/*                                     <img class='iconPadding' src="{{ action.icon }}"*/
/*                                          title="{{ 'Actions_SubmenuSitesearch'|translate }}: {{ action.siteSearchKeyword }} - {{ action.serverTimePretty }}"/>*/
/*                                 {% elseif action.eventCategory|default(false) is not empty %}*/
/*                                     <img  class="iconPadding" src='{{ action.icon }}'*/
/*                                         title="{{ 'Events_Event'|translate }} {{ action.eventCategory }} - {{ action.eventAction }} {% if action.eventName is defined %}- {{ action.eventName }}{% endif %} {% if action.eventValue is defined %}- {{ action.eventValue }}{% endif %}"/>*/
/*                                 {% else %}*/
/*                                     <img class='iconPadding' src="{{ action.icon }}"*/
/*                                          title="{{ action.goalName }} - {% if action.revenue > 0 %}{{ 'General_ColumnRevenue'|translate }}: {{ action.revenue|money(idSite)|raw }} - {% endif %} {{ action.serverTimePretty }}"/>*/
/*                                 {% endif %}*/
/*                             </a>*/
/*                         {% endif %}*/
/*                     {% endif %}*/
/*                 {% endfor %}*/
/*                 {% if visitor.actionDetails|length > maxPagesDisplayedByVisitor %}*/
/*                     <em>({{ 'Live_MorePagesNotDisplayed'|translate }})</em>*/
/*                 {% endif %}*/
/*             </div>*/
/*         </li>*/
/*     {% endfor %}*/
/* </ul>*/
/* <script type="text/javascript">*/
/* $('#visitsLive').on('click', '.visits-live-launch-visitor-profile', function (e) {*/
/*     e.preventDefault();*/
/*     broadcast.propagateNewPopoverParameter('visitorProfile', $(this).attr('data-visitor-id'));*/
/*     return false;*/
/* });*/
/* </script>*/
