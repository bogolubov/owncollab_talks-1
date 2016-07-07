/**
 * Module util.js
 * Its static common helpers methods
 */

(function (window) {

    if (!Array.isArray) {
        Array.isArray = function (arg) {
            return Object.prototype.toString.call(arg) === '[object Array]';
        };
    }

    var util = {};

    /**
     * Deeply extends two objects
     * @param  {Object} destination The destination object, This object will change
     * @param  {Object} source      The custom options to extend destination by
     * @return {Object}             The desination object
     */
    util.extend = function (destination, source) {
        var property;
        for (property in source) {
            if (source[property] && source[property].constructor && source[property].constructor === Object) {
                destination[property] = destination[property] || {};
                util.extend(destination[property], source[property]);
            } else {
                destination[property] = source[property];
            }
        }
        return destination;
    };

    /**
     * Clone object
     * @param   {Object} source
     * @returns {*}
     */
    util.objClone = function (source) {
        if (source === null || typeof source !== 'object') return source;
        var temp = source.constructor();
        for (var key in source)
            temp[key] = util.objClone(source[key]);
        return temp;
    };

    /**
     * Count object length
     * @param   {Object} source
     * @returns {number}
     */
    util.objLen = function (source) {
        var it = 0;
        for (var k in source) it++;
        return it;
    };

    /**
     * Merge an object `src` into the object `objectBase`
     * @param objectBase    main object of merge
     * @param src           the elements of this object will be added/replaced to main object `obj`
     * @returns {*}         object result
     */
    util.objMerge = function (objectBase, src) {
        if (typeof objectBase !== 'object' || typeof src !== 'object')
            return false;

        if (Object.key) {
            Object.keys(src).forEach(function (key) {
                objectBase[key] = src[key];
            });
            return objectBase;
        } else {
            for (var key in src)
                if (src.hasOwnProperty(key)) objectBase[key] = src[key];
            return objectBase;
        }
    };

    /**
     * Merge objects if `objectBase` key not exists
     * @param objectBase
     * @param src
     * @returns {*}
     */
    util.objMergeNotExists = function (objectBase, src) {
        for (var key in src)
            if (objectBase[key] === undefined)
                objectBase[key] = src[key];
        return objectBase;
    };

    /**
     * Merge objects if `objectBase` key is exists
     * @param objectBase
     * @param src
     * @returns {*}
     */
    util.objMergeOnlyExists = function (objectBase, src) {
        for (var key in src)
            if (objectBase[key] !== undefined)
                objectBase[key] = src[key];
        return objectBase;
    };

    /**
     * Merge an array `src` into the array `arrBase`
     * @param arrBase
     * @param src
     * @returns {*}
     */
    util.arrMerge = function (arrBase, src) {
        if (!Array.isArray(arrBase) || !Array.isArray(src))
            return false;

        for (var i = 0; i < src.length; i++)
            arrBase.push(src[i])

        return arrBase;
    };

    /**
     * Computes the difference of arrays
     * Compares arr1 against one or more other arrays and returns the values in arr1
     * that are not present in any of the other arrays.
     * @param arr1
     * @param arr2
     * @returns {*}
     */
    util.arrDiff = function (arr1, arr2) {
        if (util.isArr(arr1) && util.isArr(arr2)) {
            return arr1.slice(0).filter(function (item) {
                return arr2.indexOf(item) === -1;
            })
        }
        return false;
    };

    util.objToArr = function (obj) {
        return [].slice.call(obj);
    };

    util.realObjToArr = function (obj) {
        var arr = [];
        for (var key in obj)
            arr.push(obj[key])
        return arr;
    };

    util.cloneFunction = function (func) {
        var temp = function temporary() {
            return func.apply(this, arguments);
        };
        for (var key in this) {
            if (this.hasOwnProperty(key)) {
                temp[key] = this[key];
            }
        }
        return temp;
    };

    /**
     * Check on typeof is string a param
     * @param param
     * @returns {boolean}
     */
    util.isStr = function (param) {
        return typeof param === 'string';
    };

    /**
     * Check on typeof is array a param
     * @param param
     * @returns {boolean}
     */
    util.isArr = function (param) {
        return Array.isArray(param);
    };

    /**
     * Check on typeof is object a param
     * @param param
     * @returns {boolean}
     */
    util.isObj = function (param) {
        return (param !== null && typeof param == 'object');
    };

    /**
     * Determine param is a number or a numeric string
     * @param param
     * @returns {boolean}
     */
    util.isNum = function (param) {
        return !isNaN(param);
    };

    // Determine whether a variable is empty
    util.isEmpty = function (param) {
        return (param === "" || param === 0 || param === "0" || param === null || param === undefined || param === false || (util.isArr(param) && param.length === 0));
    };

    util.isHtml = function (param) {
        if (util.isNode(param)) return true;
        return util.isNode(util.html2node(param));
    };

    util.isNode = function (param) {
        var types = [1, 9, 11];
        if (typeof param === 'object' && types.indexOf(param.nodeType) !== -1) return true;
        else return false;
    };

    /**
     *
     * Node.ELEMENT_NODE - 1 - ELEMENT
     * Node.TEXT_NODE - 3 - TEXT
     * Node.PROCESSING_INSTRUCTION_NODE - 7 - PROCESSING
     * Node.COMMENT_NODE - 8 - COMMENT
     * Node.DOCUMENT_NODE - 9 - DOCUMENT
     * Node.DOCUMENT_TYPE_NODE - 10 - DOCUMENT_TYPE
     * Node.DOCUMENT_FRAGMENT_NODE - 11 - FRAGMENT
     * Uses: Util.isNodeType(elem, 'element')
     */
    util.isNodeType = function (param, type) {
        type = String((type ? type : 1)).toUpperCase();
        if (typeof param === 'object') {
            switch (type) {
                case '1':
                case 'ELEMENT':
                    return param.nodeType === Node.ELEMENT_NODE;
                    break;
                case '3':
                case 'TEXT':
                    return param.nodeType === Node.TEXT_NODE;
                    break;
                case '7':
                case 'PROCESSING':
                    return param.nodeType === Node.PROCESSING_INSTRUCTION_NODE;
                    break;
                case '8':
                case 'COMMENT':
                    return param.nodeType === Node.COMMENT_NODE;
                    break;
                case '9':
                case 'DOCUMENT':
                    return param.nodeType === Node.DOCUMENT_NODE;
                    break;
                case '10':
                case 'DOCUMENT_TYPE':
                    return param.nodeType === Node.DOCUMENT_TYPE_NODE;
                    break;
                case '11':
                case 'FRAGMENT':
                    return param.nodeType === Node.DOCUMENT_FRAGMENT_NODE;
                    break;
                default:
                    return false;
            }
        }
        else return false;
    };

    /**
     * Determine param to undefined type
     * @param param
     * @returns {boolean}
     */
    util.defined = function (param) {
        return typeof(param) != 'undefined';
    };

    /**
     * Javascript object to JSON data
     * @param data
     */
    util.objToJson = function (data) {
        return JSON.stringify(data);
    };

    /**
     * JSON data to Javascript object
     * @param data
     */
    util.jsonToObj = function (data) {
        return JSON.parse(data);
    };

    /**
     * Cleans the array of empty elements
     * @param src
     * @returns {Array}
     */
    util.cleanArr = function (src) {
        var arr = [];
        for (var i = 0; i < src.length; i++)
            if (src[i]) arr.push(src[i]);
        return arr;
    };

    /**
     * Return type of data as name object "Array", "Object", "String", "Number", "Function"
     * @param data
     * @returns {string}
     */
    util.typeOf = function (data) {
        return Object.prototype.toString.call(data).slice(8, -1);
    };

    /**
     * Convert HTML form to encode URI string
     * @param form
     * @param asObject
     * @returns {*}
     */
    util.formData = function (form, asObject) {
        var obj = {}, str = '';
        for (var i = 0; i < form.length; i++) {
            var f = form[i];
            if (f.type == 'submit' || f.type == 'button') continue;
            if ((f.type == 'radio' || f.type == 'checkbox') && f.checked == false) continue;
            var fName = f.nodeName.toLowerCase();
            if (fName == 'input' || fName == 'select' || fName == 'textarea') {
                obj[f.name] = f.value;
                str += ((str == '') ? '' : '&') + f.name + '=' + encodeURIComponent(f.value);
            }
        }
        return (asObject === true) ? obj : str;
    };

    /**
     * HTML string convert to DOM Elements Object
     * @param data
     * @returns {*}
     */
    util.toNode = function (data) {
        var parser = new DOMParser();
        var node = parser.parseFromString(data, "text/xml");
        console.log(node);
        if (typeof node == 'object' && node.firstChild.nodeType == Node.ELEMENT_NODE)
            return node.firstChild;
        else return false;
    };

    /**
     * Removes duplicate values from an array
     * @param arr
     * @returns {Array}
     */
    util.uniqueArr = function (arr) {
        var tmp = [];
        for (var i = 0; i < arr.length; i++) {
            if (tmp.indexOf(arr[i]) == "-1") tmp.push(arr[i]);
        }
        return tmp;
    };

    /**
     * Reads entire file into a string, synchronously
     * This function uses XmlHttpRequest and cannot retrieve resource from different domain.
     * @param url
     * @returns {*|string|null|string}
     */
    util.fileGetContents = function (url) {
        var req = null;
        try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                try {
                    req = new XMLHttpRequest();
                } catch (e) {
                }
            }
        }
        if (req == null) throw new Error('XMLHttpRequest not supported');
        req.open("GET", url, false);
        req.send(null);
        return req.responseText;
    };

    /**
     * Calculates the position and size of elements.
     *
     * @param elem
     * @returns {{y: number, x: number, width: number, height: number}}
     */
    util.getPosition = function (elem) {
        var top = 0, left = 0;
        if (elem.getBoundingClientRect) {
            var box = elem.getBoundingClientRect();
            var body = document.body;
            var docElem = document.documentElement;
            var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
            var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
            var clientTop = docElem.clientTop || body.clientTop || 0;
            var clientLeft = docElem.clientLeft || body.clientLeft || 0;
            top = box.top + scrollTop - clientTop;
            left = box.left + scrollLeft - clientLeft;
            return {y: Math.round(top), x: Math.round(left), width: elem.offsetWidth, height: elem.offsetHeight};
        } else { //fallback to naive approach
            while (elem) {
                top = top + parseInt(elem.offsetTop, 10);
                left = left + parseInt(elem.offsetLeft, 10);
                elem = elem.offsetParent;
            }
            return {x: left, y: top, width: elem.offsetWidth, height: elem.offsetHeight};
        }
    };

    /**
     * Returns the coordinates of the mouse on any element
     * @param event
     * @param element
     * @returns {{x: number, y: number}}
     */
    util.getMousePosition = function (event, element) {
        var positions = {x: 0, y: 0};
        element = element || document.body;
        if (element instanceof HTMLElement && event instanceof MouseEvent) {
            if (element.getBoundingClientRect) {
                var rect = element.getBoundingClientRect();
                positions.x = event.clientX - rect.left;
                positions.y = event.clientY - rect.top;
            } else {
                positions.x = event.pageX - element.offsetLeft;
                positions.y = event.pageY - element.offsetTop;
            }
        }
        return positions;
    };

    /**
     * Creator of styles, return style-element or style-text.
     *
     * <pre>var style = createStyle('body','font-size:10px');
     *style.add('body','font-size:10px')       // added style
     *style.add( {'background-color':'red'} )  // added style
     *style.getString()                        // style-text
     *style.getObject()                        // style-element</pre>
     *
     * @param selector      name of selector styles
     * @param property      string "display:object" or object {'background-color':'red'}
     * @returns {*}         return object with methods : getString(), getObject(), add()
     */
    util.createStyle = function (selector, property) {
        var o = {
            content: '',
            getString: function () {
                return '<style rel="stylesheet">' + "\n" + o.content + "\n" + '</style>';
            },
            getObject: function () {
                var st = document.createElement('style');
                st.setAttribute('rel', 'stylesheet');
                st.textContent = o.content;
                return st;
            },
            add: function (select, prop) {
                if (typeof prop === 'string') {
                    o.content += select + "{" + ( (prop.substr(-1) == ';') ? prop : prop + ';' ) + "}";
                } else if (typeof prop === 'object') {
                    o.content += select + "{";
                    for (var key in prop)
                        o.content += key + ':' + prop[key] + ';';
                    o.content += "}";
                }
                return this;
            }
        };
        return o.add(selector, property);
    };

    /**
     * Create new NodeElement
     * @param tag       element tag name 'p, div, h3 ... other'
     * @param attrs     object with attributes key=value
     * @param inner     text, html or NodeElement
     * @returns {Element}
     */
    util.createElement = function (tag, attrs, inner) {
        var elem = document.createElement(tag);
        if (typeof attrs === 'object') {
            for (var key in attrs)
                elem.setAttribute(key, attrs[key]);
        }

        if (typeof inner === 'string') {
            elem.innerHTML = inner;
        } else if (typeof inner === 'object') {
            elem.appendChild(elem);
        }
        return elem;
    };


    /**
     * Returns a random integer between min, max, if not specified the default of 0 to 100
     * @param min
     * @param max
     * @returns {number}
     */
    util.rand = function (min, max) {
        min = min || 0;
        max = max || 100;
        return Math.floor(Math.random() * (max - min + 1) + min);
    };

    /**
     * Returns random string color, HEX format
     * @returns {string}
     */
    util.randColor = function () {
        var letters = '0123456789ABCDEF'.split(''),
            color = '#';
        for (var i = 0; i < 6; i++)
            color += letters[Math.floor(Math.random() * 16)];
        return color;
    };

    /**
     * Converts degrees to radians
     * @param deg
     * @returns {number}
     */
    util.degreesToRadians = function (deg) {
        return (deg * Math.PI) / 180;
    };

    /**
     * Converts radians to degrees
     * @param rad
     * @returns {number}
     */
    util.radiansToDegrees = function (rad) {
        return (rad * 180) / Math.PI;
    };

    /**
     * The calculation of the distance between points
     * The point is an object with properties `x` and `y` {x:100,y:100}
     * @param point1
     * @param point2
     * @returns {number}
     */
    util.distanceBetween = function (point1, point2) {
        var dx = point2.x - point1.x;
        var dy = point2.y - point1.y;
        return Math.sqrt(dx * dx + dy * dy);
    };

    /**
     * Encode URI params
     * @param data      Object key=value
     * @returns {*}     query string
     */
    util.encodeData = function (data) {
        if (typeof data === 'string') return data;
        if (typeof data !== 'object') return '';
        var convertData = [];
        Object.keys(data).forEach(function (key) {
            convertData.push(key + '=' + encodeURIComponent(data[key]));
        });
        return convertData.join('&');
    };

    /**
     * Parse URI Request data into object
     * @param url
     * @returns {{}}
     */
    util.parseGet = function (url) {
        url = url || document.location;
        var params = {};
        var parser = document.createElement('a');
        parser.href = url;
        if (parser.search.length > 1) {
            parser.search.substr(1).split('&').forEach(function (part) {
                var item = part.split('=');
                params[item[0]] = decodeURIComponent(item[1]);
            });
        }
        return params;
    };

    /**
     * Parse Url string/location into object
     * @param url
     * @returns {{}}
     */
    util.parseUrl = function (url) {
        url = url || document.location;
        var params = {};
        var parser = document.createElement('a');
        parser.href = url;
        params.protocol = parser.protocol;
        params.host = parser.host;
        params.hostname = parser.hostname;
        params.port = parser.port;
        params.pathname = parser.pathname;
        params.hash = parser.hash;
        params.search = parser.search;
        params.get = util.parseGet(parser.search);
        return params;
    };

    util.each = function (data, callback) {
        if (util.isArr(data)) {
            for (var i = 0; i < data.length; i++) callback.call(null, data[i]);
        } else if (util.isObj(data)) {
            for (var k in data) callback.call(null, k, data[k]);
        } else return false;
    };

    util.ucfirst = function (string) {
        return string && string[0].toUpperCase() + string.slice(1);
    };

    util.node2html = function (element) {
        var container = document.createElement("div");
        container.appendChild(element.cloneNode(true));
        return container.innerHTML;
    };

    util.html2node = function (string) {
        var i, fragment = document.createDocumentFragment(), container = document.createElement("div");
        container.innerHTML = string;
        while (i = container.firstChild) fragment.appendChild(i);
        return fragment.childNodes.length === 1 ? fragment.firstChild : fragment;
    };

    util.base64encode = function (str) {
        var b64chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
        var b64encoded = '';
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        for (var i = 0; i < str.length;) {
            chr1 = str.charCodeAt(i++);
            chr2 = str.charCodeAt(i++);
            chr3 = str.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = isNaN(chr2) ? 64 : (((chr2 & 15) << 2) | (chr3 >> 6));
            enc4 = isNaN(chr3) ? 64 : (chr3 & 63);
            b64encoded += b64chars.charAt(enc1) + b64chars.charAt(enc2) +
                b64chars.charAt(enc3) + b64chars.charAt(enc4);
        }
        return b64encoded;
    };


    util.base64decode = function (str) {
        var b64chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
        var b64decoded = '';
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        str = str.replace(/[^a-z0-9\+\/\=]/gi, '');
        for (var i = 0; i < str.length;) {
            enc1 = b64chars.indexOf(str.charAt(i++));
            enc2 = b64chars.indexOf(str.charAt(i++));
            enc3 = b64chars.indexOf(str.charAt(i++));
            enc4 = b64chars.indexOf(str.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            b64decoded = b64decoded + String.fromCharCode(chr1);
            if (enc3 < 64) {
                b64decoded += String.fromCharCode(chr2);
            }
            if (enc4 < 64) {
                b64decoded += String.fromCharCode(chr3);
            }
        }
        return b64decoded;
    };


    /**
     * Cross-browser function for the character of the event keypress:
     * @param event     event.type must keypress
     * @returns {*}
     */
    util.getChar = function (event) {
        if (event.which == null) {
            if (event.keyCode < 32) return null;
            return String.fromCharCode(event.keyCode)
        }
        if (event.which != 0 && event.charCode != 0) {
            if (event.which < 32) return null;
            return String.fromCharCode(event.which);
        }
        return null;
    };

    util.Date = function () {
    };

    util.Date.time = function (date) {
        "use strict";
        return date instanceof Date ? date.getTime() : (new Date).getTime();

    };
    /**
     * Add days to some date
     * @param day           number of days. 0.04 - 1 hour, 0.5 - 12 hour, 1 - 1 day
     * @param startDate     type Date, start date
     * @returns {*}  type Date
     */
    util.Date.addDays = function (day, startDate) {
        var date = startDate ? new Date(startDate) : new Date();
        date.setTime(date.getTime() + (day * 86400000));
        return date;
    };

    util.Date.daysBetween = function (date1, date2) {
        var ONE_DAY = 86400000,
            date1_ms = date1.getTime(),
            date2_ms = date2.getTime();
        return Math.round((Math.abs(date1_ms - date2_ms)) / ONE_DAY)
    };

    util.Storage = function (name, value) {
        if (!name) {
            return false;
        } else if (value === undefined) {
            return util.Storage.get(name);
        } else if (!value) {
            return util.Storage.remove(name);
        } else {
            return util.Storage.set(name, value);
        }
    };
    util.Storage.set = function (name, value) {
        try {
            value = JSON.stringify(value)
        } catch (error) {
        }
        return window.localStorage.setItem(name, value);
    };
    util.Storage.get = function (name) {
        var value = window.localStorage.getItem(name);
        if (value)
            try {
                value = JSON.parse(value)
            } catch (error) {
            }
        return value;
    };
    util.Storage.remove = function (name) {
        return window.localStorage.removeItem(name);
    };
    util.Storage.key = function (name) {
        return window.localStorage.key(key);
    };
    // when invoked, will empty all keys out of the storage.
    util.Storage.clear = function () {
        return window.localStorage.clear();
    };
    // returns an integer representing the number of data items stored in the Storage object.
    util.Storage.length = function () {
        return window.localStorage.length;
    };


    /**
     * возвращает cookie с именем name, если есть, если нет, то undefined
     * @param name
     * @param value
     */
    util.Cookie = function (name, value) {
        "use strict";
        if (value === undefined) {
            return util.Cookie.get(name);
        }
        else if (value === false || value === null) {
            util.Cookie.delete(name);
        } else {
            util.Cookie.set(name, value);
        }

    };
    util.Cookie.get = function (name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    };
    /**
     *
     * @param name
     * @param value
     * @param {{}} options   {expires: 0, path: '/', domain: 'site.com', secure: false}
     *                          expires - ms, Date, -1, 0
     */
    util.Cookie.set = function (name, value, options) {
        options = options || {};
        var expires = options.expires;
        if (typeof expires == "number" && expires) {
            var d = new Date();
            d.setTime(d.getTime() + expires * 1000);
            expires = options.expires = d;
        }
        if (expires && expires.toUTCString) {
            options.expires = expires.toUTCString();
        }
        value = encodeURIComponent(value);
        var updatedCookie = name + "=" + value;
        for (var propName in options) {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    };

    util.Cookie.delete = util.Cookie.remove = function (name, option) {
        "use strict";
        option = typeof option === 'object' ? option : {};
        option.expires = -1;
        util.Cookie.set(name, "", option);
    };

    util.getURLParameter = function (name) {
        var reg = (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search) || [, null])[1];
        return reg === null ? undefined : decodeURI(reg);
    };

    /**
     * An asynchronous for-each loop
     *
     * @param   {Array}     array       The array to loop through
     *
     * @param   {function}  done        Callback function (when the loop is finished or an error occurs)
     *
     * @param   {function}  iterator
     * The logic for each iteration.  Signature is `function(item, index, next)`.
     * Call `next()` to continue to the next item.  Call `next(Error)` to throw an error and cancel the loop.
     * Or don't call `next` at all to break out of the loop.
     */
    util.asyncForEach = function (array, done, iterator) {
        var i = 0;
        next();

        function next(err) {
            if (err)
                done(err);
            else if (i >= array.length)
                done();
            else if (i < array.length) {
                var item = array[i++];
                setTimeout(function () {
                    iterator(item, i - 1, next);
                }, 0);
            }
        }
    };


    /**
     * Calls the callback in a given interval until it returns true
     * @param {function} callback
     * @param {number} interval in milliseconds
     */
    util.waitFor = function (callback, interval) {
        var internalCallback = function () {
            if (callback() !== true) {
                setTimeout(internalCallback, interval);
            }
        };
        internalCallback();
    };

    /**
     * Remove item from array
     * @param item
     * @param stack
     * @returns {Array}
     */
    util.rmInArray = function (item, stack) {
        var newStack = [];
        for (var i = 0; i < stack.length; i++) {
            if (stack[i] && stack[i] != item)
                newStack.push(stack[i]);
        }
        return newStack;
    };

    /**
     * @param text
     * @returns {string|void|XML}
     */
    util.toTranslit = function (text) {
        return text.replace(/([а-яё])|([\s_-])|([^a-z\d])/gi,
            function (all, ch, space, words, i) {
                if (space || words)
                    return space ? '-' : '';
                var code = ch.charCodeAt(0),
                    index = code == 1025 || code == 1105 ? 0 : code > 1071 ? code - 1071 : code - 1039,
                    t = ['yo', 'a', 'b', 'v', 'g', 'd', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shch', '', 'y', '', 'e', 'yu', 'ya'];
                return t[index];
            });
    };

    window.Util = util;

})(window);
