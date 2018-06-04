(function ($) {
    'use strict';
    $.fn.ModCookie = function () {
        window.cookieconsent_options = {
            message: 'Diese Website nutzt Cookies, um bestmögliche Funktionalität bieten zu können.',
            dismiss: 'Ok, verstanden',
            learnMore: 'Mehr Infos',
            link: '/Document/DataProtectionOrdinance',
            theme: 'dark-top'
        };

        if(!(document.cookie.indexOf("cookieconsent_dismissed")>-1)){
            window.update_cookieconsent_options(window.cookieconsent_options);
        }

        return this;
    };

}(jQuery));