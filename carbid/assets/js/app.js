
;(function ($, window, document, undefined ) {
    "use strict";

    /** Abort if already present **/
    if (CarbidApp || !(window.history && history.pushState)) return;

    console.log('Page Loaded')

    var $body,
        $main,
        $previewAnchor,
        main;

    // Custom event for content update
    var contentUpdatedEvent = document.createEvent('Event');
    contentUpdatedEvent.initEvent('contentUpdated', true, true);
    /*document.addEventListener('build', function (e) {
        // e.target matches document from above
        alert('ok');
    }, false);*/

    var CarbidApp = {

        /**
         * Initialize application
         */
        init: function() {
            $body = $('body');
            $main = $('#main');
            $previewAnchor = $('.header_preview_container a');
            main = document.getElementById('main');
            CarbidApp.handleLinks();
            CarbidApp.handleForms();
        },

        handleLinks: function() {
            var $anchors = $main.find('#tl_buttons, .tl_right, .tl_content_right, .tl_right_nowrap').find('a');
            $anchors.each(function() {
                if ($(this).attr('onclick') && $(this).attr('onclick') !== ';') return;
                $(this).on("click", function(e) {
                    e.preventDefault();
                    CarbidApp.loadContent(this.href)
                });
            });
        },

        handleForms: function() {
            var $form = $main.find('.tl_form'),
                $submits = $form.find('input[type="submit"]');
            $form.on('submit', function(e) {
                e.preventDefault();
                var formData = $form.serialize(),
                    $btn = $submits.filter("[clicked=true]");
                if (!$btn.length) $btn = $submits.eq(0);
                if ($btn.length) {
                    formData += '&' + $btn.attr('name') + '=' + $btn.attr('value');
                }
                CarbidApp.loadContent(this.action, formData);
            });
            $submits.click(function() {
                $submits.removeAttr("clicked");
                $(this).attr("clicked", "true");
            });
        },

        loadContent: function(url, postData) {
            AjaxRequest.displayBox(Contao.lang.loading + ' …');
            var ajaxObj = {
                url: url,
                    /*beforeSend: function( xhr ) {
                     xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
                     },*/
                crossDomain: true,
                statusCode: {
                    204: function() {
                        alert( "no page content" );
                    },
                    303: function(html, textStatus, jqXHR) {
                        alert('aaaa');
                        console.log(jqXHR.getResponseHeader('Location'));
                    }
                }
            };
            if (postData) {
                ajaxObj.type = 'post';
                ajaxObj.data = postData;
            }

            $.ajax(ajaxObj)
                .done(function(html, textStatus, jqXHR) {
                    //alert( "success" );
                    //$html = ($html instanceof jQuery) ? $html : utility.htmlDoc($html);
                    //var $insideElem         = $(html).find('#main');//,
                        //updatedContainer    = ($insideElem.length) ? $.trim($insideElem.html()) : $html.filter(id).html(),
                        //newContent          = (updatedContainer.length) ? $(updatedContainer) : null;
                    //return newContent;
                    //return $insideElem;
                    //console.log(jqXHR.getResponseHeader('X-Current-Location'));
                    CarbidApp.changeContent(html, jqXHR.getResponseHeader('X-Current-Location'))
                })
                .fail(function() {
                    alert( "An error acquired" );
                })
                .always(function() {
                    //alert( "complete" );
                });
        },

        changeContent: function(html, url) {

            var $html = $(CarbidApp.htmlDoc(html)),
                $htmlBody = $html.find('body');

            if (!$htmlBody.hasClass('template-be_main')) {
                window.location.href = url;
                return;
            }

            var $htmlMain = $(html).find('#main'),
                previewLink = $html.find('.header_preview_container a').attr('href');

            // Remove TinyMce Editors
            if (tinymce && tinymce.editors) {
                tinymce.editors.forEach(function(editor) {
                   editor.remove();
                });
            }

            window.history.pushState({ id: Math.random() }, "LLLL", url);

            /*$htmlMain.find('script').each(function() {
               console.log(this);
            });*/

            //console.log($htmlMain.find('script')[0]);

            //$htmlMain.find('script').remove();

            $body.attr('class', $htmlBody.attr('class'));
            $previewAnchor.attr('href', previewLink);

            //main.innerHTML = $htmlMain.html();
            $main.empty().append($htmlMain.html());

            Backend.collapsePalettes();
            Backend.addInteractiveHelp();
            Backend.convertEnableModules();
            Backend.makeWizardsSortable();
            Stylect.convertSelects();

            // Chosen
            if (Elements.chosen != undefined) {
                $$('select.tl_chosen').chosen();
            }

            // Remove line wraps from textareas
            $$('textarea.monospace').each(function(el) {
                Backend.toggleWrap(el);
            });

            CarbidApp.handleLinks();
            CarbidApp.handleForms();

            document.dispatchEvent(contentUpdatedEvent);

            AjaxRequest.hideBox();
            console.log('Page Updated');
        },


        htmlDoc: function (html) {
            var parent,
                elems       = $(),
                matchTag    = /<(\/?)(html|head|body|title|base|meta)(\s+[^>]*)?>/ig,
                prefix      = "ss" + Math.round(Math.random() * 100000),
                htmlParsed  = html.replace(matchTag, function(tag, slash, name, attrs) {
                    var obj = {};
                    if (!slash) {
                        $.merge(elems, $("<" + name + "/>"));
                        if (attrs) {
                            $.each($("<div" + attrs + "/>")[0].attributes, function(i, attr) {
                                obj[attr.name] = attr.value;
                            });
                        }
                        elems.eq(-1).attr(obj);
                    }
                    return "<" + slash + "div" + (slash ? "" : " id='" + prefix + (elems.length - 1) + "'") + ">";
                });

            // If no placeholder elements were necessary, just return normal
            // jQuery-parsed HTML.
            if (!elems.length) {
                return $(html);
            }
            // Create parent node if it hasn't been created yet.
            if (!parent) {
                parent = $("<div/>");
            }
            // Create the parent node and append the parsed, place-held HTML.
            parent.html(htmlParsed);

            // Replace each placeholder element with its intended element.
            $.each(elems, function(i) {
                var elem = parent.find("#" + prefix + i).before(elems[i]);
                elems.eq(i).html(elem.contents());
                elem.remove();
            });

            return parent.children().unwrap();
        }

    };

    window.CarbidApp = CarbidApp;

    document.addEventListener('DOMContentLoaded', function(){
        CarbidApp.init();
    });

})(jQuery, window, document);

