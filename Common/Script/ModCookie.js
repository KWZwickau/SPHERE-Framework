(function ($) {
    'use strict';
    $.fn.ModCookie = function () {
        window.CookieHinweis_options = {
            message: 'Diese Website nutzt Cookies, um bestmögliche Funktionalität bieten zu können.',
            agree: 'Ok, verstanden',
            learnMore: 'Mehr Infos',
            link: '/Document/DataProtectionOrdinance', /* Link zu den eigenen Datenschutzbestimmungen */
            theme: 'hell-unten-rechts' /* weitere Theme-Optionen sind dunkel-unten oder dunkel-oben */
        };
        if (!(document.cookie.indexOf("CookieHinweis_options") > -1)) {
            window.update_CookieHinweis_options(window.CookieHinweis_options);
        }

        return this;
    };

}(jQuery));