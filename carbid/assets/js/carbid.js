/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014 Alexander Stulnikov
 *
 * @package    Carbid
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/MIT
 */

(function(Backend, Stylect, $) {

    var html = document.getElementsByTagName("html")[0],
        body = document.getElementsByTagName("body")[0];

    html.classList.add('js');


    /**
     * Adding custom classes to body tag
     */
    /*function addCustomClasses() {
        var strClasses = "";
        if ( window.self !== window.top ) {
            strClasses += " popup";
        }
        document.body.className = document.body.className + strClasses;
    }*/


    /**
     * Highlight element on mouse over action icon
     */
    function initElementActionsHighlight() {
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

    function initLogin() {
        $('.tl_login_table').each(function() {
           var $this = $(this),
               $nameLabel = $this.find('label[for="username"]'),
               $passwordLabel = $this.find('label[for="password"]'),
               $nameInput = $this.find('#username'),
               $passwordInput = $this.find('#password');
            $nameInput.prop('placeholder', $nameLabel.html());
            $passwordInput.prop('placeholder', $passwordLabel.html());
        });
    }

    function initHeader() {
        var $header = $('#header'),
            $tmenu = $header.find('#tmenu'),
            $alerts = $header.find('.tl_permalert'),
            $menuToggler = $('<div id="menuToggler"><i></i></div>'),
            $tmenuToggler = $('<div class="toggler"></div>'),
            $tmenuInner = $('<div class="inner"></div>'),
            $alertContainer = $('<div id="alerts"></div>'),
            $alertToggler = $('<div class="toggler"></div>'),
            $alertInner = $('<div class="inner"></div>');
        $header.prepend($menuToggler);
        $tmenu.wrapInner($tmenuInner);
        $tmenu.prepend($tmenuToggler);
        $tmenu.after($alertContainer);
        $alertContainer.append($alertToggler).append($alertInner);
        $alertInner.append($alerts);
        if (!$alerts.length) {
            $alertContainer.addClass('hidden');
        }
        function openMenu() {
            closeAlerts();
            if ($tmenu.hasClass('opened')) {
                return;
            }
            $tmenu.addClass('opened');
            $(document).on('click', closeMenu);
        }
        function closeMenu() {
            $(document).off('click', closeMenu);
            $tmenu.removeClass('opened');
        }
        function openAlerts() {
            closeMenu();
            if ($alertContainer.hasClass('opened')) {
                return;
            }
            $alertContainer.addClass('opened');
            $(document).on('click', closeAlerts);
        }
        function closeAlerts() {
            $(document).off('click', closeAlerts);
            $alertContainer.removeClass('opened');
        }
        $alertContainer.add($tmenu).on('click', function(e) {
            e.stopPropagation();
        });
        $tmenuToggler.click(function(e) {
            e.stopPropagation();
            if ($tmenu.hasClass('opened')) {
                closeMenu();
            }
            else {
                openMenu();
            }
        });
        $alertToggler.click(function(e) {
            e.stopPropagation();
            if ($alertContainer.hasClass('opened')) {
                closeAlerts();
            }
            else {
                openAlerts();
            }
        });
    }

    function initScroll() {
        var $left = $('#left');
        $('#tl_navigation').slimScroll({
            height: '100%',
            opacity: 1,
            color: '#000',
            size: '4px',
            distance: '0'
        });
    }

    window.addEvent('domready', function() {
        //addCustomClasses();
        initLogin();
        initHeader();
        initScroll();
        initElementActionsHighlight();
    });

    /*Stylect.convertSelects = function() {
        $('select').not('.tl_chosen').uniform({selectAutoWidth: false});
    };*/

})(window.Backend, window.Stylect, window.jQuery);


var Carbid = {

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
    },

    /**
     * Open a selector page in a modal window
     *
     * @param {object} options An optional options object
     */
    openModalSelector: function(options) {
        var opt = options || {},
            max = (window.getSize().y-180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal({
            'width': opt.width,
            'btn_ok': Contao.lang.close,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
            'onHide': function() { document.body.setStyle('overflow', 'auto'); }
        });
        M.addButton(Contao.lang.close, 'btn', function() {
            this.hide();
        });
        M.addButton(Contao.lang.apply, 'btn primary', function() {
            var val = [],
                frm = null,
                frms = window.frames;
            for (i=0; i<frms.length; i++) {
                if (frms[i].name == 'simple-modal-iframe') {
                    frm = frms[i];
                    break;
                }
            }
            if (frm === null) {
                alert('Could not find the SimpleModal frame');
                return;
            }
            if (frm.document.location.href.indexOf('contao/main.php') != -1) {
                alert(Contao.lang.picker);
                return; // see #5704
            }
            var inp = frm.document.getElementById('tl_listing').getElementsByTagName('input');
            for (var i=0; i<inp.length; i++) {
                if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
                if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
            }
            if (opt.tag) {
                $(opt.tag).value = val.join(',');
                if (opt.url.match(/page\.php/)) {
                    $(opt.tag).value = '{{link_url::' + $(opt.tag).value + '}}';
                }
                opt.self.set('href', opt.self.get('href').replace(/&value=[^&]*/, '&value='+val.join(',')));
            } else {
                $('ctrl_'+opt.id).value = val.join("\t");
                var act = 'reloadPicker';
                new Request.Contao({
                    field: $('ctrl_'+opt.id),
                    evalScripts: false,
                    onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
                    onSuccess: function(txt, json) {
                        $('ctrl_'+opt.id).getParent('div').set('html', json.content);
                        json.javascript && Browser.exec(json.javascript);
                        AjaxRequest.hideBox();
                        window.fireEvent('ajax_change');
                    }
                }).post({'action':act, 'name':opt.id, 'value':$('ctrl_'+opt.id).value, 'REQUEST_TOKEN':Contao.request_token});
            }
            this.hide();
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
            'model': 'modal'
        });
    }

};

