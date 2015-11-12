/*! jQuery Browser - v0.1.0 - 3/23/2012
 * https://github.com/jquery/jquery-browser
 * Copyright (c) 2012 John Resig; Licensed MIT */
(function (a)
{
    var b, c = navigator.userAgent || "";
    a.uaMatch = function (a)
    {
        a = a.toLowerCase();
        var b = /(chrome)[ \/]([\w.]+)/.exec(a) || /(webkit)[ \/]([\w.]+)/.exec(
                a) || /(opera)(?:.*version)?[ \/]([\w.]+)/.exec(a) || /(msie) ([\w.]+)/.exec(a) || a.indexOf(
                "compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+))?/.exec(a) || [];
        return {browser: b[1] || "", version: b[2] || "0"}
    }, b = a.uaMatch(
        c), a.browser = {}, b.browser && (a.browser[b.browser] = !0, a.browser.version = b.version), a.browser.webkit && (a.browser.safari = !0)
})(jQuery)
