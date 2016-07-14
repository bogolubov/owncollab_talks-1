(function () {


    var version = '0.1.0';


    /**
     * Constructor
     * @param properties
     * @returns {app|NamespaceApplication}
     */
    var app = function (properties) {
        if (!(this instanceof NamespaceApplication))
            return new NamespaceApplication(properties);


        this.version = version;
        this.domLoaded = app.domLoaded;
        this.request = app.request;
        this.script = app.script;
        this.style = app.style;
        this.file = app.file;
        this.extend = app.extend;
        this.store = app.store;
        this.route = app.route;
        this.routePath = app.routePath;
        this.assign = app.assign;
        this.inject = app.inject;
        this.query = app.query;
        this.queryAll = app.queryAll;
        this.each = app.each;
        this.setProperties(properties);

    };

    /** Execute callback function if or when DOM is loaded
     * @param callback
     */
    app.domLoaded = function (callback) {
        if (document.querySelector('body')) {
            callback.call({});
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                callback.call({})
            }, false);
        }
    };


    /**
     * Base url request
     * @param method
     * @param url
     * @param callback
     * @param callbackError
     * @returns {XMLHttpRequest}
     */
    app.request = function (method, url, callback, callbackError) {
        var xhr = new XMLHttpRequest();
        method = method || 'POST';
        url = url || '/';

        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        if (typeof callback === 'function') xhr.onloadend = callback;
        if (typeof callbackError === 'function') xhr.onerror = callbackError;
        xhr.send();
        return xhr;
    };


    /**
     * Loads the script element
     * @param src
     * @param onload
     * @param onerror
     * @returns {*}
     */
    app.script = function (src, onload, onerror) {

        if (!src) return null;

        var script = document.createElement('script'),
            id = "src-" + Math.random().toString(32).slice(2);

        script.src = (src.substr(-3) === '.js') ? src : src + '.js';
        script.type = 'application/javascript';
        script.id = id;
        script.onload = onload;
        script.onerror = onerror;

        document.head.appendChild(script);

        return script;
    };


    /**
     *
     * Loads the CSS link element
     *
     * @param url
     * @param onload
     * @param onerror
     * @returns {Element}
     */
    app.style = function (url, onload, onerror) {
        var link = document.createElement('link'),
            id = "src-" + Math.random().toString(32).slice(2);

        link.href = (url.substr(-4) === '.css') ? url : url + '.css';
        link.rel = 'stylesheet';
        link.id = id;
        link.onload = onload;
        link.onerror = onerror;
        document.head.appendChild(link);
        return link;
    };


    /**
     * Loads the file
     * @param url
     * @param onload
     * @param onerror
     */
    app.file = function (url, onload, onerror) {
        app.request('GET', url, function (event) {
            if (event.target.status === 200)
                onload.call(this, event.target.responseText, event);
            else
                onerror.call(this, event);
        }, onerror)
    };

    /**
     * Merge objects
     * @param obj objectBase
     * @param src
     * @param callback
     * @returns {*}
     */
    app.extend = function (obj, src, callback) {
        for (var key in src) {
            var free, entry = src[key];
            if (typeof callback === 'function')
                free = callback(key, obj[key], src[key]);
            if (src.hasOwnProperty(key) && !!free) obj[key] = entry;
        }
        return obj;
    };


    /**
     * Storage in memory
     * if `object` is a Object - set new objects
     * if `object` is a String - return object by name
     * if `object` is a not set - return all objects
     *
     * @param object
     * @param keyWithValue
     * @returns {*}
     */
    app.store = function (object, keyWithValue) {

        if(typeof object === 'string' && keyWithValue !== undefined) {
            var _object = {};
            _object[object] = keyWithValue;
            return this.store(_object);
        }

        if (typeof object === 'object') {
            for (var key in object)
                this._stackStorage[key] = object[key];
            return this._stackStorage;
        }
        else if (typeof object === 'string')
            return this._stackStorage[object] ? this._stackStorage[object] : null;

        else if (object === undefined)
            return this._stackStorage;

    };

    /**
     * Storage for static calls
     * @type {{}}
     * @private
     */
    app._stackStorage = {};


    /**
     * Simple router
     *
     * @param uri
     * @param callback
     */
    app.route = function (uri, callback, hash, query) {
        uri = uri || '';
        var reg = new RegExp('^' + uri + '$', 'i'),
            path = app.routePath.call(this, hash, query);

        if (reg.test(path)) {
            callback.call(this);
            return true;
        }
        return false;
    };

    /**
     *
     * @returns {string}
     */
    app.routePath = function (hash, query) {
        var path = window.location.pathname;
        if (hash) path += window.location.hash;
        if (query) path += window.location.search;
        if (this.url && path.indexOf(this.url) === 0) {
            path = path.substr(this.url.length);
            if (path.slice(0, 1) !== '/') path = '/' + path;
        }
        return path;
    };


    /**
     * Simple template builder
     * @param stringData    source string data with marks "{{key1}}"
     * @param params        object {key1 : 'value'}
     * @returns {*}
     */
    app.assign = function (stringData, params) {
        if (typeof params === 'object')
            for (var k in params)
                stringData = stringData.replace(new RegExp('{{' + k + '}}', 'gi'), params[k]);

        return stringData;
    };


    /**
     * Simple inject data to HTMLElement [by selector]
     * @param selector
     * @param data
     * @returns {*}
     */
    app.inject = function (selector, data) {
        if (typeof selector === 'string')
            selector = this.query(selector);

        if (typeof selector === 'object' && selector.nodeType === Node.ELEMENT_NODE) {
            selector.textContent = '';
            if (typeof data === 'object')
                selector.appendChild(data);
            else
                selector.innerHTML = data;
            return selector;
        }
        return null;
    };


    /**
     * Query DOM Element by selector
     *
     * @param selector
     * @param parent|callback
     * @returns {Element}
     */
    app.query = function (selector, parent) {
        var elems = this.queryAll(selector, parent);
        if (elems && elems.length > 0)
            return elems[0];
        return null;
    };


    /**
     * Query DOM Elements by selector
     *
     * @param selector
     * @param parent    callback
     * @returns {*}
     */
    app.queryAll = function (selector, parent) {
        var callback, _elemsList, elems, from = document;

        if (typeof parent === 'function')
            callback = parent;
        else if (typeof parent === 'string')
            from = document.querySelector(parent);
        else if (typeof parent === 'object' && parent.nodeType === Node.ELEMENT_NODE)
            from = parent;


        if (from) {
            elems = [].slice.call(from.querySelectorAll(selector));
        }


        if (elems.length > 0 && typeof callback == 'function')
            callback.call(this, elems);

        // debug
        if (this.debug && !elems)
            console.error("Error queryAll DOM Elements by selector ", selector);

        return elems;
    };


    /**
     *
     * @param list
     * @param callback
     * @param tmp
     */
    app.each = function (list, callback, tmp) {
        var i = 0;
        if (list instanceof Array)
            for (i = 0; i < list.length; i++) callback.call({}, list[i], i, tmp);
        else if(typeof list === 'object')
            for (i in list) callback.call({}, list[i], i, tmp);
    };


    /**
     *
     * @type {{url: string, debug: boolean, constructsType: string, _lastKey: null, _stackRequires: {}, _stackStorage: {}, _stackConstructs: Array}}
     */
    app.prototype._properties = {

        /**
         * Base url
         */
        url: '/',

        /**
         * Debug mod
         */
        debug: true,

        /**
         * Startup type of constructor for modules
         * Type: false - off constructor
         *      'runtime' - perform during the assignment of namespace
         *      'gather' - save in the stack,
         *          for call and execute all constructor methods, use .constructsStart()
         */
        constructsType: 'runtime',

        _lastKey: null,
        _stackRequires: {},
        _stackStorage: {},
        _stackConstructs: []
    };


    /**
     * Create namespace for module-script
     * @param namespace  "Controller.Name" or "Action.Name"
     * @param callback
     * @param args
     * @returns {{}}
     */
    app.prototype.namespace = function (namespace, callback, args) {
        var
            name,
            path = namespace.split('.'),
            tmp = this || {},
            len = path.length;

        for (var i = 0; i < len; i++) {
            name = path[i].trim();
            if (typeof tmp[name] !== 'object') {
                tmp[name] = (i + 1 >= len) ? (callback ? callback.call(tmp, this, {}) : {}) : {};
                tmp = tmp[name];
            } else
                tmp = tmp[name];
        }

        if (typeof tmp === "object" && tmp.construct) {
            args = Array.isArray(args) ? args : [];
            if (this.constructsType == 'runtime') {
                tmp.construct.apply(tmp, args);
            }
            else if (this.constructsType == 'gather')
                this._stackConstructs.push(tmp);
        }

        return tmp;
    };


    /**
     * Run all modules constructs
     * @param args
     * @returns {app|NamespaceApplication}
     */
    app.prototype.constructsStart = function (args) {
        app.each(this._stackConstructs, function (item, index) {
            item.construct.apply(item, args);
        }, args);
        this._stackConstructs = [];
        return this;
    };


    /**
     * Designate a list of scripts for loading
     * @param key           list key (identifier)
     * @param path          array with scripts url
     * @param oncomplete    executing when all scripts are loaded
     * @param onerror
     * @returns {app|NamespaceApplication}
     */
    app.prototype.require = function (key, path, oncomplete, onerror) {
        this._lastKey = key;

        this._stackRequires[key] = {
            src: Array.isArray(path) ? path : [path],
            oncomplete: oncomplete,
            onerror: onerror
        };
        return this;
    };


    /**
     * Start loading the list of scripts by key (identifier)
     *
     * @param key
     * @returns {app|NamespaceApplication}
     */
    app.prototype.requireStart = function (key) {
        var source;
        key = key || this._lastKey;
        if (this._stackRequires[key]) {
            this._recursive_load_script(0, key);
        } else {
            console.error("Require source not found! Key: " + key + " not exist!");
        }
        return this;
    };


    /**
     *
     * @param i
     * @param key
     * @private
     */
    app.prototype._recursive_load_script = function (i, key) {
        var self = this,
            source = this._stackRequires[key];

        if (source.src[i]) {
            if (!Array.isArray(source.node)) source.node = [];

            source.node.push(app.script(source.src[i], function () {
                self._recursive_load_script(++i, key);
            }, source.onerror));

        } else if (i === source.src.length)
            source.oncomplete.call(self, source.node);
        else
            self._recursive_load_script(++i, key);
    };


    /**
     * Apply properties object to instance properties
     * @param properties
     * @returns {app|NamespaceApplication}
     */
    app.prototype.setProperties = function (properties) {

        if (typeof properties !== 'object') properties = {};

        var key, props = app.extend(this._properties, properties, function (key, obj, src) {
            return key.slice(0, 1) !== '_';
        });

        for (key in props)
            if (this[key] === undefined)
                this[key] = props[key];

        return this;
    };


    /**
     * Run all modules constructs
     * @param args
     * @returns {app|NamespaceApplication}
     */
    app.prototype.constructsStart = function (args) {
        this.each(this._stackConstructs, function (item, index) {
            if (typeof item.construct === 'function')
                item.construct.apply(item, args);
        }, args);
        this._stackConstructs = [];
        return this;
    };


    /**
     * @type {app}
     */
    window.NamespaceApplication = app;

})();