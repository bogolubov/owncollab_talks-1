(function(window){

	var Inc = function(){
		if(!(this instanceof Inc)) return new Inc();
        
        this.options = {
            sources: [],
            handler: {onload:null,onerror:null},
            error: false,
            current: 0
	    };
	};

	Inc.prototype.onload = null; 

	Inc.prototype.onerror = null; 

	Inc.prototype.init = function(){
		this.options.handler.onload = this.onload;
		this.options.handler.onerror = this.onerror;
		this.load(this.options.sources[0]);
	};

	Inc.prototype.require = function(src){
		this.options.sources.push(src);
	};

	Inc.prototype.load = function(src){
        
        if(this.scriptExists(src)) {
            var index = this.options.sources.indexOf(src);
            this.options.sources.splice(index,1);
            if(this.options.sources[index+1] !== undefined)
                return this.load(this.options.sources[index++]);
            return;
        };

		var self = this,
			script = this.createScriptElement(src);

		script.onload = function(event){
			self.options.current ++;
			if(!self.options.error && self.options.current < self.options.sources.length){
				self.load(self.options.sources[self.options.current]);
			}
			if(!self.options.error && self.options.current === self.options.sources.length && typeof self.options.handler['onload'] === 'function'){
				self.options.handler['onload'].call(script, self.options.sources);
			}
		};

		script.onerror = function(error){
			self.options.error = true;
			if(typeof self.options.handler['onerror'] === 'function')
				self.options.handler['onerror'].call(script, error);
		};

		document.head.appendChild(script);
	};

	Inc.prototype.createScriptElement = function(src){
		var script = document.createElement('script');
        script.setAttribute('data-inc', src);
		script.src = (src.substr(-3).toLowerCase() === '.js') ? src : src + '.js';
		script.type = 'application/javascript';
		return script;
	};

	Inc.prototype.scriptExists = function(src){
        var result = false;
        Array.prototype.slice.call(document.scripts, 0).forEach(function(item){
            if(!result && item.getAttribute('data-inc') === src)
                result = true;
        });
        return result;
	};
    
	window.Inc = Inc;

})(window);