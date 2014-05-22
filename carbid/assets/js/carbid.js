/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014 Alexander Stulnikov
 *
 * @package    Carbid
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

(function(Backend) {
    window.addEvent('domready', function() {

        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                vars[key] = value;
            });
            return vars;
        }

        var reload1 = document.getElementById('filter'),
            reload2 = document.getElementById('btfilter'),
            urlVars = getUrlVars(),
            strClasses = "";

        if (urlVars['do'] != undefined) {
            strClasses += " module_" + urlVars['do'];
        }
        if (urlVars['table'] != undefined) {
            strClasses += " table_" + urlVars['table'];
        }
        if (urlVars['act'] != undefined) {
            strClasses += " act_" + urlVars['act'];
        }
        if ( window.self !== window.top ) {
            strClasses += " popup";
        }
        if ( document.getElementsByClassName('tl_login_form').length ) {
            strClasses += " login_page";
        }
        if ( window.location.pathname.match(/install\.php/) ) {
            strClasses += " install_page";
        }

        document.body.className = document.body.className + strClasses;

        // Replacing images
        if (reload1) {
            reload1.setAttribute('src','system/modules/carbid/assets/images/blank.gif');
        }
        if (reload2) {
            reload2.setAttribute('src','system/modules/carbid/assets/images/blank.gif');
        }

    });
})(window.Backend);

Carbid = {
    /**
     * Key/value wizard
     *
     * @param {object} el      The DOM element
     * @param {string} command The command name
     * @param {string} id      The ID of the target element
     */
    keyValueWizard: function(el, command, id) {
        var table = $(id),
            tbody = table.getElement('tbody'),
            parent = $(el).getParent('tr'),
            rows = tbody.getChildren(),
            tabindex = tbody.get('data-tabindex'),
            input, childs, i, j;

        Backend.getScrollOffset();

        switch (command) {
            case 'copy':
                var tr = new Element('tr');
                childs = parent.getChildren();
                for (i=0; i<childs.length; i++) {
                    var next = childs[i].clone(true).inject(tr, 'bottom');
                    if (input = childs[i].getFirst('input')) {
                        next.getFirst().value = input.value;
                        if (next.getFirst().type == 'hidden') {
                            next.getFirst().value = '';
                        }
                    }
                }
                tr.inject(parent, 'after');
                break;
            case 'up':
                if (tr = parent.getPrevious('tr')) {
                    parent.inject(tr, 'before');
                } else {
                    parent.inject(tbody, 'bottom');
                }
                break;
            case 'down':
                if (tr = parent.getNext('tr')) {
                    parent.inject(tr, 'after');
                } else {
                    parent.inject(tbody, 'top');
                }
                break;
            case 'delete':
                if (rows.length > 1) {
                    parent.destroy();
                }
                break;
        }

        rows = tbody.getChildren();

        for (i=0; i<rows.length; i++) {
            childs = rows[i].getChildren();
            for (j=0; j<childs.length; j++) {
                if (input = childs[j].getFirst('input')) {
                    input.set('tabindex', tabindex++);
                    input.name = input.name.replace(/\[[0-9]+\]/g, '[' + i + ']')
                }
            }
        }

        new Sortables(tbody, {
            contstrain: true,
            opacity: 0.6,
            handle: '.drag-handle'
        });
    }
}