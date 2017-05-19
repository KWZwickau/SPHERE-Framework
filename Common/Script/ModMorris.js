(function($)
{
    'use strict';
    $.fn.ModMorris = function(options)
    {
        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            hideHover: 'always',
            resize: true,
            grid: false,
            goals: [100],
            gridTextSize: 10,
            postUnits: '%',
            parseTime: false,
            lineWidth: 1,
            goalStrokeWidth: 1,
            goalLineColors: ['green'],
            lineColors: ['#559a9b'],
            // ID of the element in which to draw the chart.
            element: this.attr('id'),
            // Chart data records -- each entry in this array corresponds to a point on
            // the chart.
            data: [
                {try: '#1', value: 10},
                {try: '#2', value: 35},
                {try: '#3', value: 85},
                {try: '#4', value: 75},
                {try: '#5', value: 95},
                {try: '#6', value: 100},
            ],
            // The name of the data record attribute that contains x-values.
            xkey: 'try',
            // A list of names of data record attributes that contain y-values.
            ykeys: ['value'],
            // Labels for the ykeys -- will be displayed when you hover over the
            // chart.
            labels: ['Value']

        }, options);

        var Chart = new Morris.Line(settings);
        window.setTimeout(function(){
            Chart.raphael.setSize('100%','100%');
            Chart.redraw();
        },500);

        return this;
    };
}(jQuery));
