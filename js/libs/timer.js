/**
 * Script Timer (Timer.js) simulates class ActionScript 3.0 Timer
 * @url
 */
(function(){

    "use strict";

    var
        timerProto = {
            ms: 0,
            mark: 0,
            delay: 0,
            onstart: null,
            onprogress: null,
            oncomplete: null,
            _eventTarget: null,
            timerHandler: 0,
            iterator: 0,
            _events: {_onstart:null,_onprogress:null,_oncomplete:null}
        },

        /**
         * Timer constructor
         * @param ms        Interval Timer in milliseconds. Call function onprogress every time
         * @param delay     Number of iterations, if assigned 0, iteration occur infinitely
         * @returns {timer|*}
         */
        timer = function(ms, delay){
            if(!ms) return false;
            if(!(this instanceof Timer)) return new Timer(ms, delay);
            var instance = this;
            this.ms = parseInt(ms);
            this.delay = delay ? parseInt(delay) : 0;
            this._events._onstart = new Event(Timer.START);
            this._events._onprogress = new Event(Timer.PROGRESS);
            this._events._oncomplete = new Event(Timer.COMPLETE);
            this._eventTarget = document.createDocumentFragment();
            this.addEventListener = function(event, callback, useCapture){
                useCapture = useCapture || false;
                this._eventTarget.addEventListener.call(this._eventTarget, event, callback, useCapture)
            };

            /**
             * Start timer
             */
            this.start = function(){

                if(typeof this.onstart === 'function')
                    this._eventTarget.addEventListener(Timer.START, this.onstart, false);
                if(typeof this.onprogress === 'function')
                    this._eventTarget.addEventListener(Timer.PROGRESS, this.onprogress, false);
                if(typeof this.oncomplete === 'function')
                    this._eventTarget.addEventListener(Timer.COMPLETE, this.oncomplete, false);
                this._eventTarget.dispatchEvent(this._events._onstart);

                // performs interval
                this.timerHandler = setInterval(function(){
                    instance._events._onprogress.iterator = instance._events._onprogress.progress = ++ instance.iterator;
                    instance._eventTarget.dispatchEvent(instance._events._onprogress);
                    if(instance.delay !== 0 && instance.iterator >= instance.delay){
                        clearInterval(instance.timerHandler);
                        instance._eventTarget.dispatchEvent(instance._events._oncomplete);
                    }
                }, this.ms);
            };

            /**
             * Abort timer
             */
            this.abort = function(){
                clearInterval(this.timerHandler)
            };

            this.reset = function(){
                clearInterval(this.timerHandler);
                this._events._onprogress.iterator = this._events._onprogress.progress = instance.iterator = 0;
            };

        };


    timer.START = 'start';
    timer.PROGRESS = 'progress';
    timer.COMPLETE = 'complete';
    
    window.Timer = timer;
    window.Timer.prototype = timerProto;
    window.Timer.prototype.constructor = timer;

})();