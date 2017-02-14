/**
 * @type NamespaceApplication App
 */
if (App.namespace) { App.namespace('Cache', function (App) {

    /**
     * @namespace App.Cache
     */
    var _ = {};

    /**
     * Private session data
     * @type {{}}
     * @private
     */
    var _data = {};

    /**
     * @namespace App.Cache.put
     *
     * @param key
     * @param value
     */
    _.put = function (key, value) {
        _data[key] = value;
    };

    /**
     * @namespace App.Cache.get
     *
     * @param key
     * @returns {*}
     */
    _.get = function (key) {
        return _data[key]
    };

    /**
     * @namespace App.Cache.getAll
     * @returns {{}}
     */
    _.getAll = function () {
        return _data
    };

    /**
     * @namespace App.Cache.exist
     *
     * @param key
     * @returns {boolean}
     */
    _.exist = function (key) {
        return _data[key] !== undefined
    };

    return _;

})}