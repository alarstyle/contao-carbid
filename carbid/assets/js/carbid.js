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


    /**
     * Adding custom classes to body tag
     */
    function addCustomClasses() {
        var urlVars = [],
            strClasses = "";
        window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            urlVars[key] = value;
        });
        if ( urlVars['do'] != undefined ) {
            strClasses += " module_" + urlVars['do'];
        }
        if ( urlVars['table'] != undefined ) {
            strClasses += " table_" + urlVars['table'];
        }
        if ( urlVars['act'] != undefined ) {
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
        if(('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) {
            strClasses += " touch";
        }
        else {
            strClasses += " no-touch";
        }
        document.body.className = document.body.className + strClasses;
    }


    /**
     * Replacing filer refrush button, because can't be replaced with css
     */
    function replaceFilterRefreshBtn() {
        var reload1 = document.getElementById('filter'),
            reload2 = document.getElementById('btfilter');
        if (reload1) {
            reload1.setAttribute('src','system/modules/carbid/assets/images/blank.gif');
        }
        if (reload2) {
            reload2.setAttribute('src','system/modules/carbid/assets/images/blank.gif');
        }
    }


    /**
     * Highlight element on mouse over action icon
     */
    function initElementActonsHighlight() {
        var elements, i;
        // metadata
        elements = document.querySelectorAll('#ctrl_meta li');
        for (i = 0; i < elements.length; i++) {
            var img = elements[i].getElementsByClassName('tl_metawizard_img')[0];
            if (img) {
                img.elementContainer = elements[i];
                img.setAttribute('onmouseover', 'Carbid.elementHighlight(this, "deleteAction")');
                img.setAttribute('onmouseout', 'Carbid.elementHighlight(this, "deleteAction", true)');
            }
        }
        // listing
        elements = document.querySelectorAll('.tl_listing_container tr, .tl_listing_container .tl_content, .tl_listing_container .tl_folder, .tl_listing_container .tl_file ');
        for (i = 0; i < elements.length; i++) {
            var anchor = elements[i].getElementsByClassName('delete')[0];
            if (anchor) {
                anchor.elementContainer = elements[i];
                anchor.setAttribute('onmouseover', 'Carbid.elementHighlight(this, "deleteAction")');
                anchor.setAttribute('onmouseout', 'Carbid.elementHighlight(this, "deleteAction", true)');
            }
        }
    }

    window.addEvent('domready', function() {
        addCustomClasses();
        replaceFilterRefreshBtn();
        initElementActonsHighlight();
    });

})(window.Backend);

Carbid = {

    /**
     *
     * @param {object}  el        The DOM element
     * @param {string}  className Class name which will be added or removed
     * @param {boolean} remove    If true - class name will be removed
     */
    elementHighlight: function(el, className, remove) {
        if (!el.elementContainer) {
            el.elementContainer = el.parentNode.parentNode;
        }
        if (!remove) {
            el.elementContainer.className = el.elementContainer.className + ' deleteAction';
        }
        else {
            el.elementContainer.className = el.elementContainer.className.replace(' deleteAction', '');
        }
    },


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