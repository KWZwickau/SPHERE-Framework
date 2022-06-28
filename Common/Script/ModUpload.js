(function($)
{
    'use strict';
    $.fn.ModUpload = function(options)
    {

        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            'showUpload': false,
            'showPreview': false,
//             'maxFileSize': '2097152',
            'previewFileType': 'any',
            'browseLabel': 'Durchsuchen',
            'maxFilesNum': 1
        }, options);

        this.fileinput(settings);
        return this;

    };

}(jQuery));
