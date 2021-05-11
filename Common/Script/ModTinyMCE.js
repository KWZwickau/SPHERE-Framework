(function($)
{
    'use strict';
    /**
     * @param options
     * @returns {$.fn.ModTinyMCE}
     * @constructor
     */
    $.fn.ModTinyMCE = function(options)
    {
        // This is the easiest way to have default options.
        var settings = $.extend({
            entity_encoding : "html",
//             selector: 'textarea#default',  // change this value according to your HTML
            height: 500,
//             width: 600,
//             min_width: 400,
            min_height: 113,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
//             skin: 'oxide',
//             skin: 'oxide-dark',
            paste_word_valid_elements: 'b,u,strong,i,em,br,div,p,li,ul,span',
            forced_root_block: false,
            branding: false
        }, options);

        this.tinymce(settings);
        return this;

    };

}(jQuery));
