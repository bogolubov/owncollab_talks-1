/**
 * Extension linker.js
 * Выполнитель действий для кнопок.
 * Елементы собераются в стек по индификатору
 * Нидификатор - назначается по атрибутам по проиоритету data-id, id, href#hash
 * linker.search();     Соберает все елементы с классом .linker
 * linker.get( id )     Выбирает элемент по индификатору
 * linker.click( id )   Назначает событие клика для элемента по индификатору
 */
(function(){

    /**
     * @namespace window.Linker
     */
    var linker = {
        stack: [],
        stackError: []
    };

    /**
     * Search link elements
     * Priority for search elements: data-id, id, href
     * @namespace window.Linker.search
     * @param where
     * @returns {*}
     */
    linker.search = function(where) {
        where = typeof where === 'object' && where.nodeType === Node.ELEMENT_NODE ? where : document;
        var elems = where.querySelectorAll('.linker');

        for(var i = 0; i < elems.length; i ++ ){
            var id = elems[i].getAttribute('data-id')
                ? elems[i].getAttribute('data-id')
                : ( elems[i].id
                    ? elems[i].id
                    : ( elems[i].href && elems[i].hash.length > 1
                        ? elems[i].hash.slice(1)
                        : false
                    )
                );
            if(id) {
                elems[i]._linkerId = id;
                linker.stack.push(elems[i]);
            }
            else linker.stackError.push(elems[i]);
        }

        if(linker.stackError.length > 0)
            console.error('Linker catch elements without ID: ', linker.stackError);

        return linker.stack;
    };


    /**
     *
     * @namespace window.Linker.refresh
     * @returns {*}
     */
    linker.refresh = function() {
        return linker.search();
    };

    /**
     *
     * @namespace window.Linker.get
     * @param id
     * @param array
     * @returns {*}
     */
    linker.get = function(id, array){
        if(!id && !array) return linker.stack;
        var linkers = [];
        for(var i = 0; i < linker.stack.length; i ++)
            if(linker.stack[i]._linkerId === id)
                linkers.push(linker.stack[i]);
        if(linkers.length === 0) return null;
        if(!!array) return linkers;
        return linkers[0];
    };

    /**
     *
     * @namespace window.Linker.click
     * @param id
     * @param callback
     * @param useCapture
     * @returns {*}
     */
    linker.click = function(id, callback, useCapture) {
        return linker.on('click', id,  callback, useCapture);
    };


    /**
     *
     * @namespace window.Linker.on
     * @param event
     * @param id
     * @param callback
     * @param useCapture
     * @returns {boolean}
     */
    linker.on = function(event, id, callback, useCapture) {
        if(typeof callback !== 'function') {
            console.error('typeof callback not a function');
            return false;
        }
        var elem = linker.get(id, true);
        if(elem) {
            for(var i = 0; i < elem.length; i ++){
                elem[i].addEventListener(event, callback, useCapture);
            }
        }
    };


    window.Linker = linker;

})();