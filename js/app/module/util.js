/**
 * Module util.js
 * Its static common helpers methods
 */

(function($, OC, app){

    // elimination of dependence this action object
    if(typeof app.module.util !== 'object') {
        app.module.util = {};
    }

    if (!Array.isArray) {
        Array.isArray = function(arg) {
            return Object.prototype.toString.call(arg) === '[object Array]';
        };
    }

    // alias of app.u and app.module.util
    var o = app.u = app.module.util;

    // Clone object
    o.objClone = function(obj){
        if (obj === null || typeof obj !== 'object') return obj;
        var temp = obj.constructor();
        for (var key in obj)
            temp[key] = o.objClone(obj[key]);
        return temp;
    };

    // Count object length
    o.objLen = function(obj){
        var it = 0;
        for(var k in obj) it ++;
        return it;
    };

    // Merge two objects into one - 'obj'
    o.objMerge = function(obj, src){
        if(Object.key){
            Object.keys(src).forEach(function(key) { obj[key] = src[key]; });
            return obj;
        }else{
            for (var key in src)
                if (src.hasOwnProperty(key)) obj[key] = src[key];
            return obj;
        }
    };

    // Check on typeof is string a param
    o.isStr = function(param) {
        return (typeof param === 'string');
    };

    // Check on typeof is array a param
    o.isArr = function(param) {
        return Array.isArray(param);
    };

    // Check on typeof is object a param
    o.isObj = function(param) {
        return (param !== null && typeof param == 'object');
    };

    // Finds whether a variable is a number or a numeric string
    o.isNum = function(param) {
        return !isNaN(param);
    };

    // Determine param to undefined type
    o.defined = function(param) {
        return typeof(param) != 'undefined';
    };

    // Determine whether a variable is empty
    o.isEmpty = function(param) {
        return (param===""||param===0||param==="0"||param===null||param===undefined||param===false||(o.isArr(param)&&param.length===0));
    };

    // Javascript object to JSON data
    o.objToJson = function(data) {
        return JSON.stringify(data);
    };

    // JSON data to Javascript object
    o.jsonToObj = function(data) {
        return JSON.parse(data);
    };

    o.cleanArr = function (src) {
        var arr = [];
        for (var i = 0; i < src.length; i++)
            if (src[i]) arr.push(src[i]);
        return arr;
    };
    // Return type of data as name object "Array", "Object", "String", "Number", "Function"
    o.typeOf = function(data) {
        return Object.prototype.toString.call(data).slice(8, -1);
    };

    // Convert HTML form to encode URI string
    o.formData = function (form, asObject){
        var obj = {}, str = '';
        for(var i=0;i<form.length;i++){
            var f = form[i];
            if(f.type == 'submit' || f.type == 'button') continue;
            if((f.type == 'radio' || f.type == 'checkbox') && f.checked == false) continue;
            var fName = f.nodeName.toLowerCase();
            if(fName == 'input' || fName == 'select' || fName == 'textarea'){
                obj[f.name] = f.value;
                str += ((str=='')?'':'&') + f.name +'='+encodeURIComponent(f.value);
            }
        }
        return (asObject === true)?obj:str;
    };

    // HTML string convert to DOM Elements Object
    o.toNode = function(data) {
        var parser = new DOMParser();
        var node = parser.parseFromString(data, "text/xml");
        console.log(node);
        if(typeof node == 'object' && node.firstChild.nodeType == Node.ELEMENT_NODE)
            return node.firstChild;
        else return false;
    };

    // Removes duplicate values from an array
    o.uniqueArr = function (arr) {
        var tmp = [];
        for (var i = 0; i < arr.length; i++) {
            if (tmp.indexOf(arr[i]) == "-1") tmp.push(arr[i]);
        }
        return tmp;
    };

    // Reads entire file into a string
    // This function uses XmlHttpRequest and cannot retrieve resource from different domain.
    o.fileGetContents = function(url) {
        var req = null;
        try { req = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
            try { req = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {
                try { req = new XMLHttpRequest(); } catch(e) {}
            }
        }
        if (req == null) throw new Error('XMLHttpRequest not supported');
        req.open("GET", url, false);
        req.send(null);
        return req.responseText;
    };

    //
    o.getPosition = function(elem) {
        var top=0, left=0;
        if (elem.getBoundingClientRect) {
            var box = elem.getBoundingClientRect();
            var body = document.body;
            var docElem = document.documentElement;
            var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
            var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
            var clientTop = docElem.clientTop || body.clientTop || 0;
            var clientLeft = docElem.clientLeft || body.clientLeft || 0;
            top  = box.top +  scrollTop - clientTop;
            left = box.left + scrollLeft - clientLeft;
            return { y: Math.round(top), x: Math.round(left), width:elem.offsetWidth, height:elem.offsetHeight };
        } else { //fallback to naive approach
            while(elem) {
                top = top + parseInt(elem.offsetTop,10);
                left = left + parseInt(elem.offsetLeft,10);
                elem = elem.offsetParent;
            }
            return { y: top, x: left, width:elem.offsetWidth, height: elem.offsetHeight};
        }
    };

    //
    o.arrDiff = function (arr1, arr2) {
        return arr1.slice(0).filter(function(item) {
            return arr2.indexOf(item) === -1;
        })
    };


})(jQuery, OC, app);
