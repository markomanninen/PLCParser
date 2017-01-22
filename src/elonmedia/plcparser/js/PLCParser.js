"use strict";

function PLCParser() {

    var parser = parser || {};

    return parser;

}

// for node environment require call
if( typeof module !== 'undefined' ) {
    if ( typeof module.exports === 'undefined' ) {
        module.exports = {};
    }
    exports.PLCParser = PLCParser;
}
