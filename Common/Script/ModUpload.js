(function($)
{
    'use strict';
    $.fn.ModUpload = function(options)
    {

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            'showUpload': false,
            'previewFileType': 'any',
            'browseLabel': 'Durchsuchen',
            'maxFilesNum': 1
        }, options);

        this.fileinput(settings);
        return this;

    };

}(jQuery));
