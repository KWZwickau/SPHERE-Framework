(function($)
{
    'use strict';
    $.fn.ModCountDown = function(
        seconds, options
    )
    {

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            precision: 200,
// [format] Directives
//Directive, Blank-padded, Description
//%Y %-Y Years left *
//%m %-m Months left *
//%w %-w Weeks left
//%d %-d Days left (taking away weeks)
//%D %-D Total amount of days left
//%H %-H Hours left
//%M %-M Minutes left
//%S %-S Seconds left
            format: '%-S'
        }, options);

        var MomentJs = moment();
        MomentJs.add(seconds, 's');
        // script goes here
        this.countdown(MomentJs.toDate(), function(Event)
        {
            $(Event.target).html(Event.strftime(settings.format));
        });
        return this;
    };

}(jQuery));
