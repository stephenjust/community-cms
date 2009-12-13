/**
* $Id: ed_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
*
* @author Moxiecode
* @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
* _Private variable
*/



(function() {
    tinymce.create('tinymce.plugins.CleanUpStyles', {
        _InvalidStyles: '',
        /**
        * Initializes the plugin, this will be executed after the plugin has been created.
        * This call is done before the ed instance has finished it's initialization so use the onInit event
        * of the ed instance to intercept that event.
        *
        * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
        * @param {string} url Absolute URL to where the plugin is located.
        */
        init: function(ed, url) {
            var t = this;
            t._InvalidStyles = ed.getParam('invalid_styles', '');
            if (t._InvalidStyles.length > 0)
                t._InvalidStyles = ',' + t._InvalidStyles + ',';

            ed.onExecCommand.add(function(ed, cmd, ui, val) {
                if (cmd == 'mceCleanup')
                    t._TinyMCECleanupStyles(ed);
            });

            ed.onSetContent.add(function(ed, o) { t._CleanupStyles(ed, o); });
            ed.onGetContent.add(function(ed, o) { t._CleanupStyles(ed, o); });
        },

        _CleanupStyles: function(ed, o) {
            var t = this;
            var styleAttr, newstyle;
            var elms = ed.dom.getRoot().getElementsByTagName('*');

            for (i = 0; i < elms.length; i++) {
                // get style if there is such an Attribute
                styleAttr = ed.dom.getAttrib(elms[i], 'style', '');

                if (styleAttr.length > 0) {
                    var StylesArray = styleAttr.split(';');

                    newstyle = '';
                    for (j = 0; j < StylesArray.length; j++) {
                        var WorkingStyle = StylesArray[j].replace(/^ */, '').replace(/ *$/, ''); //trim whitespace
                        if (WorkingStyle.length > 0) {
                            WorkingStyle = WorkingStyle.replace(/^([-a-zA-Z]+)\s*:\s*(.*)$/, function(a, b, c) { return t._VerifyStyles(a, b, c); });
                            if (WorkingStyle.length > 0)
                                newstyle += WorkingStyle;
                        }
                    }

                    if (newstyle != styleAttr) {
                        // if there are valid styles, set style attribute
                        if (newstyle.length > 0)
                            ed.dom.setAttrib(elms[i], 'style', newstyle);
                        // no valid styles, remove style attribute
                        else
                            ed.dom.setAttrib(elms[i], 'style', '');
                    }
                }
            }
        },

        _VerifyStyles: function(StyleAttribute, StyleName, StyleValue) {
            if (this._InvalidStyles.indexOf(',' + StyleName.toLowerCase() + ',') >= 0)
                return '';
            else
                return StyleName.toLowerCase() + ': ' + StyleValue.toLowerCase() + ';';
        },

        /**
        * Returns information about the plugin as a name/value array.
        * The current keys are longname, author, authorurl, infourl and version.
        *
        * @return {Object} Name/value array containing information about the plugin.
        */
        getInfo: function() {
            return {
                longname: 'Clean up styles plug in',
                author: 'Chad Killingsworth',
                authorurl: 'http://www.missouristate.edu/web/',
                version: "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('cleanupstyles', tinymce.plugins.CleanUpStyles);
})();