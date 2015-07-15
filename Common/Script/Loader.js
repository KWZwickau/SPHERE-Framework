var Client = (function()
{
    'use strict';
    var useDelay = 5;
    var useConfig = {};
    var setModule = function( Module, Depending )
    {
        useConfig[Module] = {
            Depending: Depending,
            Source: window.location.pathname.slice(
                0, window.location.pathname.search( 'Client' )
            ) + 'Sphere/Client/Script/' + Module + '.js',
            /**
             * @return {boolean}
             */
            Test: function()
            {
                return 'undefined' !== typeof jQuery.fn[Module];
            },
            isUsed: false,
            isLoaded: false,
            Retry: 0,
            isReady: function( Callback )
            {
                var dependingModule, dependingSize = this.Depending.length - 1;
                for (dependingSize; 0 <= dependingSize; dependingSize--) {
                    dependingModule = this.Depending[dependingSize];
                    if (useConfig[dependingModule].Test()) {
                        if (!useConfig[dependingModule].isReady()) {
                            loadModule( dependingModule );
                            return false;
                        }
                    } else {
                        loadModule( dependingModule );
                        return false;
                    }
                }
                if (this.Test()) {
                    this.isLoaded = true;
                    return true;
                }
                if ('undefined' !== typeof Callback) {
                    loadModule( Module, Callback );
                }
                return false;
            }
        };
    };
    var setSource = function( Module, Source, Test )
    {
        defineSource( Module, [], Source, Test );
    };
    var defineSource = function( Module, Depending, Source, Test )
    {
        useConfig[Module] = {
            Depending: Depending,
            Source: Source,
            Test: Test,
            isUsed: false,
            isLoaded: false,
            Retry: 0,
            isReady: function( Callback )
            {
                var dependingModule;
                var dependingSize = this.Depending.length - 1;
                for (dependingSize; 0 <= dependingSize; dependingSize--) {
                    dependingModule = this.Depending[dependingSize];
                    if (useConfig[dependingModule].Test()) {
                        if (!useConfig[dependingModule].isReady()) {
                            loadModule( dependingModule );
                            return false;
                        }
                    } else {
                        loadModule( dependingModule );
                        return false;
                    }
                }
                if (this.Test()) {
                    this.isLoaded = true;
                    return true;
                } else {
                    if ('undefined' !== typeof Callback) {
                        loadModule( Module, Callback );
                    }
                    return false;
                }
            }
        };
    };
    var loadScript = function( Source )
    {
        var htmlElement = document.createElement( "script" );
        htmlElement.src = Source;
        document.body.appendChild( htmlElement );
    };
    var loadModule = function( Module )
    {
        if (!useConfig[Module].isUsed) {
            loadScript( useConfig[Module].Source );
            useConfig[Module].isUsed = true;
        }
    };
    var waitModule = function( Module, Callback )
    {
        if (useConfig[Module].isReady( Callback )) {
            return Callback();
        } else {
            if (100000 < useConfig[Module].Retry) {
                return false;
            } else {
                useConfig[Module].Retry++;
            }
            window.setTimeout( function()
            {
                waitModule( Module, Callback );
            }, useDelay );
        }
        return null;
    };
    var setUse = function setUse( Module, Callback )
    {
        if ('function' !== typeof Callback) {
            //noinspection AssignmentToFunctionParameterJS
            Callback = function Callback()
            {
            };
        }
        return waitModule( Module, Callback );
    };
    return {
        Module: setModule,
        Source: setSource,
        Use: setUse
    };
})();
