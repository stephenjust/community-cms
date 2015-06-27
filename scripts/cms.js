/* global requirejs */

requirejs.config({
    "baseurl": "scripts",
    "paths": {
        "jquery": "//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min",
        "jquery-ui": "//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min",
        "galleria": "galleria/galleria-1.4.2.min",
        "galleria-theme": "galleria/themes/classic/galleria.classic.min"
    },
    "shim": {
        "jquery-cycle": ["jquery"],
        "jquery-dropdownmenu": ["jquery"],
        "tiny_mce/jquery.tinymce": ["jquery"],
        "galleria-theme": ["jquery", "galleria"],
        "galleria": ["jquery"]
    }
});

requirejs(["main"]);