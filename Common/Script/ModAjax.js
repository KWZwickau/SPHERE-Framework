(function ($) {
    'use strict';
    $.fn.ModAjax = function (options) {

        if( typeof $.fn.ModAjax.NotifyHandler == "undefined" ) {
            $.fn.ModAjax.NotifyHandler = [];
        }

        var settings = $.extend(true,{
            Receiver: [],
            Notify: {
                Hash: 'default',
                onLoad: {
                    Title: 'Loading',
                    Message: 'Please wait..',
                },
                onSuccess: {
                    Title: 'Done',
                    Message: '',
                }
            }
        }, options);

        var parseAjaxError = function (request, status, error) {
            // Parse Error
            var ErrorMessage = '';
            if (request.status === 0) {
                ErrorMessage = ('Not connected.\nPlease verify your network connection.');
            } else if (request.status == 400) {
                ErrorMessage = ('Bad Request. [400]');
            } else if (request.status == 404) {
                ErrorMessage = ('The requested page not found. [404]');
            } else if (request.status == 500) {
                ErrorMessage = ('Internal Server Error [500].');
            } else if (status === 'parsererror') {
                ErrorMessage = ('Requested JSON parse failed.');
            } else if (status === 'timeout') {
                ErrorMessage = ('Time out error.');
            } else if (status === 'abort') {
                ErrorMessage = ('Ajax request aborted.');
            } else {
                ErrorMessage = ('Uncaught Error.\n' + request.responseText);
            }
            return ErrorMessage;
        }

        var domLoadTemplate = '<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"><span class="loading-indicator-animate"></span>&nbsp;</span>' +
            '<span data-notify="title">{1}</span><br/>' +
            '<div class="progress" data-notify="progressbar" style="height: 3px; margin: 0;">' +
            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '</div>' +
            '<span data-notify="message">{2}</span>' +
            '<a href="{3}" target="{4}" data-notify="url"></a>' +
            '</div>';

        var domErrorTemplate = '<div data-notify="container" class="col-xs-11 col-sm-8 alert alert-{0}" role="alert">' +
            '<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
            '<span data-notify="icon"></span>' +
            '<span data-notify="title">{1}</span><br/>' +
            '<div class="progress" data-notify="progressbar" style="height: 3px; margin: 0;">' +
            '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
            '</div>' +
            '<span data-notify="message">{2}</span>' +
            '<a href="{3}" target="{4}" data-notify="url"></a>' +
            '</div>';

        var getRandomInt = function getRandomInt(min, max) {
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        var onLoadEvent = function ( jqXHR, Config) {
            if (!$.fn.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                $.fn.ModAjax.NotifyHandler[settings.Notify.Hash] = $.notify({
                    title: settings.Notify.onLoad.Title,
                    message: settings.Notify.onLoad.Message + '<br/>' + Config.url
                }, {
                    z_index: 32768,
                    showProgressbar: true,
                    newest_on_top: true,
                    placement: {from: 'top', align: 'right'},
                    type: 'info',
                    delay: 0,
                    template: domLoadTemplate
                });
            } else {
                $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].update({message: settings.Notify.onLoad.Message});
            }
            $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].update({progress: getRandomInt(15, 25)});
        };

        var onErrorEvent = function (request, status, error) {
            var ErrorMessage = parseAjaxError(request, status, error);
            if (!$.fn.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                $.fn.ModAjax.NotifyHandler[settings.Notify.Hash] = $.notify({
                    title: settings.Notify.onLoad.Title,
                    message: ErrorMessage
                }, {
                    z_index: 32768,
                    newest_on_top: true,
                    placement: {from: "top", align: "right"},
                    type: 'danger',
                    delay: 0,
                    animate: {enter: 'animated fadeInRight', exit: 'animated fadeOutRight'},
                    template: domErrorTemplate
                });
            } else {
                $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].update({showProgressbar: false, title: 'Error', message: ErrorMessage});
            }
        };

        var onSuccessEvent = function (Response) {
            console.log('onSuccessEvent');
            if ($.fn.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].update({progress: getRandomInt(75, 85)});
            }

            for (var Index in settings.Receiver) {
                var callReceiver;
                if (settings.Receiver.hasOwnProperty(Index)) {
                    callReceiver = new Function('Response', settings.Receiver[Index]);
                    callReceiver(Response);
                }
            }
        };

        var callAjax = function (Method, Url, Data, Callback) {
            console.log('callAjax');
            executeScript = function (Script) {
                Script();
            };
            if (Callback == false) {
                Callback = function () {
                    console.log('Stop-Ajax-Event');
                    if ($.fn.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                        $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].update({
                            progress: getRandomInt(90, 95),
                            type: 'success',
                            title: settings.Notify.onSuccess.Title,
                            message: settings.Notify.onSuccess.Message
                        });
                        $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].close();
                        $.fn.ModAjax.NotifyHandler[settings.Notify.Hash] = null;
                    }
                }
            }
            jQuery.ajax({
                method: Method,
                url: Url,
                data: JSON.parse(Data),
                dataType: "json",
                cache: false,
                beforeSend: onLoadEvent,
                error: onErrorEvent,
                success: onSuccessEvent
            }).always(Callback)
        };

        var callContent = function (Content, Callback) {
            console.log('callContent');
            executeScript = function (Script) {
                Script();
            };
            onSuccessEvent(Content);
            if (Callback == false) {
                Callback = function () {
                    console.log('Stop-Content-Event');
                    if ($.fn.ModAjax.NotifyHandler[settings.Notify.Hash]) {
                        $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].update({
                            progress: getRandomInt(90, 95),
                            type: 'success',
                            title: settings.Notify.onSuccess.Title,
                            message: settings.Notify.onSuccess.Message
                        });
                        $.fn.ModAjax.NotifyHandler[settings.Notify.Hash].close();
                        $.fn.ModAjax.NotifyHandler[settings.Notify.Hash] = null;
                    }
                }
            }
            Callback();
        }

        return {
            'loadAjax': callAjax,
            'loadContent': callContent,
        };
    };
}(jQuery));

