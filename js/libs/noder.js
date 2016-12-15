(function(window){

    /**
     * // Эквивалентно Noder.refresh () + Noder.get ()
     * Noder ()
     * // Эквивалентно Noder.get (key)
     * Noder (key)
     * // Эквивалентно Noder.add (key, node)
     * Noder (key, node)
     *
     * // Объетк глобальных параметров скрипта
     * Noder.config
     *
     * // Повтор поиска нодов в DOM и сохранение/ссылка в хранилище
     * Noder.refresh ()
     *
     * // Добавления нода в хранилище
     * Noder.add ('group', elem)
     *
     * // Выбрать массив всех нодов с хранилища
     * Noder.get ()
     *
     * // Выбрать массив нодов с хранилища по ключу
     * Noder.get ('group')
     *
     * // Выбрать массив нодов по ключу, если указан второй аргумент - фукция,
     * результат выборки попадет в первый параметр фукции
     * Noder.get ('group', function (elemList) {})
     *
     * // Выбрать единственный елемент нода, если указан второй аргумент - фукция,
     * результат выборки попадет в первый параметр фукции
     * Noder.getOne ('group', function (elem) {})
     *
     * // Если существует "element" в хранилище или елемнт/ы в группе по ключу существуют
     * // Выполяет функция 1, в какчестве параметра передается массив совпадений
     * // В противном случае функция 2
     * Noder.exist (element, function (elems) {}, function () {})
     * Noder.exist ('group', function (elems) {}, function () {})
     *
     * // Пройдет по списку каждого нода.
     * Noder.each (function (elem) {})
     * Noder.each ('group', function (elem) {})
     * Noder.each ('group', function (elem) {}, thisInst)
     *
     * // Удалит все ноды по ключу
     * Noder.remove ('group')
     *
     * // Удалит один нод по ключу
     * Noder.remove ('group', elem)
     *
     * // Добавить событие
     * Noder.on ('click', 'group', function (event) {})
     *
     * // Добавить событие click
     * Noder.click ('group', function (event, elem, value) {});
     *
     * // Выборка елементов по селектору
     * Noder.query (selector, function (elems) {})
     * Noder.queryOne (selector, function (elem) {})
     *
     * // Создать новый елемент
     * Noder.create (tag, attrs, inner)
     *
     * // Шаблонизаыия строки viewString и объекта params
     * // все подстроки обернутые как переменные {{var_name}},
     * // будут заменены значчением свойства
     * // объекта params по ключу/имени, если существует
     * Noder.template (viewString, params)
     *
     * // Обходит последовательность - массивы, объекты, массиво-подобные объекты
     * Noder.eachElement (data, callback)
     *
     * // Работа с формами
     * Noder.Form
     * Noder.Form.data (form)
     * Noder.Form.getData (key)
     * Noder.Form.elements (form)
     * Noder.Form.getElements (key)
     *
     **/

    function Noder(key, node) {
        if (arguments.length === 0) {
            Noder.refresh();
            return Noder.get ()
        }
        else if (arguments.length === 1)
            Noder.get(key);
        else if (arguments.length === 2)
            Noder.add(key, node);
    }
    Noder._nodes = [];
    Noder.config = {
        searchSelector: '.noder',
        keyName: 'data-key',
        valueName: 'data-value'
    };
    Noder.refresh = function () {
        var key,
            selector = Noder.config.searchSelector || '*['+Noder.config.keyName+']',
            elems = document.querySelectorAll(selector);

        for(var i = 0; i < elems.length; i ++ ){
            if (elems[i].getAttribute(Noder.config.keyName) && Noder._nodes.indexOf(elems[i]) === -1) {
                key = elems[i].getAttribute(Noder.config.keyName);
                Noder.add(key, elems[i]);
            }
        }
        return Noder._nodes;
    };
    Noder.add = function (key, elem, update) {
        if (typeof elem === 'string') {
            elem = Noder.queryOne(elem);
        }

        if (elem.nodeType !== Node.ELEMENT_NODE ) {
            console.error('Error. Second argument ['+elem.toString()+'] is not node element!');
            return;
        }

        if (Noder._nodes.indexOf(elem) === -1) {
            // todo: renamed name of property 'key' => 'noderKey'
            elem.key = elem.noderKey = key;
            Noder._nodes.push(elem);
            return Noder;
        } else {
            if (update) {
                Noder.remove(key, elem);
                return Noder.add(key, elem);
            }
            else
                console.error('Element ['+elem.toString()+'] was not added! It exits in storage.');
        }

    };

    Noder.update = function (key, elem) {
        return Noder.add(key, elem, true);
    };

    Noder.remove = function (key, elem) {
        for(var i = 0; i < Noder._nodes.length; i ++) {
            if(Noder._nodes[i].noderKey === key) {
                if (elem === undefined) {
                    delete Noder._nodes[i];
                }
                else
                if (elem.nodeType === Node.ELEMENT_NODE && Noder._nodes[i] === elem) {
                    delete Noder._nodes[i];
                }
            }
        }
        return Noder;
    };
    Noder.get = function (key, callback) {
        var i, result = [], list = Noder._nodes;
        if (key) {
            for(i = 0; i < list.length; i ++) {
                if(list[i].noderKey === key)
                    result.push(list[i]);
            }
        } else if (arguments.length === 0) {
            result = list;
        }

        if (typeof callback === 'function')
            callback.call({}, list);

        return result;
    };

    Noder.getOne = function (key, callback) {
        var elem = Noder.get(key);
        if (elem.length > 0) elem = elem[0];
        else elem = false;
        if (typeof callback === 'function')
            callback.call({}, elem);
        return elem;
    };
    Noder.exist = function (key, ifCallback, elseCallback) {
        var elems = [];
        if (typeof key === 'string') {
            elems = Noder.get(key);
        } else if (key && key.nodeType === Node.ELEMENT_NODE) {
            elems = [key];
        }
        if (arguments.length > 1) {
            if (elems.length > 0 && typeof ifCallback === 'function')
                ifCallback.call({}, elems);
            else if (elems.length == 0 && typeof elseCallback === 'function')
                elseCallback.call({});
        }
        return elems.length > 0;
    };

    Noder.each = function (key, callback, thisInst) {
        if (arguments.length === 1 && typeof key === 'function') {
            Noder._nodes.map(key, thisInst || {})

        } else if (typeof key === 'string' && typeof callback === 'function') {
            Noder.get(key).map(callback, thisInst || {});
        }
        return Noder
    };
    Noder.on = function (event, key, callback, useCapture) {
        var elem = Noder.get(key);
        if(elem) {
            for(var i = 0; i < elem.length; i ++) {
                elem[i].addEventListener(event, callback, useCapture);
            }
        }
        return Noder
    };
    Noder.click = function (key, callback, useCapture) {
        var valueName = Noder.config.valueName;
        var specialCallback = function (event) {
            callback.call(event, event, event.target, event.target.getAttribute(valueName))
        };
        Noder.on('click', key,  specialCallback, useCapture);
        return Noder
    };
    Noder.query = function (selector, callback) {
        var elems = Array.prototype.slice.call(document.querySelectorAll(selector));
        if (typeof callback === 'function')
            callback.call({}, elems);
        return elems
    };
    Noder.queryOne = function (selector, callback) {
        var elem = document.querySelector(selector);
        if (typeof callback === 'function')
            callback.call({}, elem);
        return elem
    };
    Noder.create = function (tag, attrs, inner) {
        var key, elem = document.createElement(tag);
        if (typeof elem  !== 'object') return null;
        if (typeof attrs === 'object')
            for (key in attrs)
                elem.setAttribute(key, attrs[key]);
        if (typeof inner === 'string')      elem.innerHTML = inner;
        else if (typeof inner === 'object') elem.appendChild(inner);
        return elem;
    };
    Noder.template = Noder.assign = function (viewString, params) {
        if (typeof params === 'object')
            for (var k in params)
                viewString = viewString.replace(new RegExp('{{' + k + '}}', 'gi'), params[k]);
        return viewString;
    };

    Noder.eachElement = function (data, callback) {
        if(data && data.length > 0) {
            for(var i = 0; i < data.length; i ++) callback.call(data, data[i], i);
        }else if(Object.prototype.toString.call(data) === '[object Object]'){
            for(var k in data) callback.call(data, data[k], k);
        }
    };

    Noder.style = function (key, styleObject) {
        var k, elems = [];

        if (typeof key === 'string')                        elems = Noder.get(key);
        else if (key && key.nodeType === Node.ELEMENT_NODE) elems = [key];

        elems.map (function (elem) {
            for (k in styleObject)
                elem.style[k] = styleObject[k]
        });
    };

    //////////////////////////////////////////  Noder.Form   ////////////////////////////////////////////

    Noder.Form = {};

    Noder.Form.getData = {};

    Noder.Form.data = {};

    Noder.Form.getElements = function (key) {
        return Noder.Form.elements(Noder.getOne(key));
    };

    Noder.Form.elements = function (form) {
        var i, elem, node, resultObject = {asString: ''};

        for (i = 0; i < form.length; i++) {
            elem = form[i];
            node = elem.nodeName.toLowerCase();

            if (!resultObject[elem.name])
                resultObject[elem.name] = elem;

            if (['input','select','textarea'].indexOf(node) !== -1)
                resultObject[elem.name].asString += ((resultObject[elem.name].asString == '') ? '' : '&') + elem.name + '=' + encodeURIComponent(elem.value);
        }
        return resultObject
    };




    window.Noder = Noder;
    window.Noder.version = '0.0.1'

})(window);