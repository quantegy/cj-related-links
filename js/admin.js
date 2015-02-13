var cjRelLink = {
    updateLabel:function(linkId, labelText) {
        return jQuery.post(ajaxurl, {
            action:'related_links_update_label',
            link_id:linkId,
            label:labelText
        }, null, 'json');
    },
    updateLink: function(linkId, url) {
        return jQuery.post(ajaxurl, {
            action:'related_links_update_link',
            link_id:linkId,
            'url':url
        }, null, 'json');
    },
    getAll:function(postId) {
        return jQuery.get(ajaxurl, {
            action:'related_links_get_all',
            post_id:postId
        }, null, 'html');
    },
    remove: function(linkId) {
        return jQuery.post(ajaxurl, {
            action:'related_links_remove_link',
            link_id:linkId
        }, null, 'json');
    }
};

jQuery(function ($) {
    $(document).on('click', '#addLinkButton', function (e) {
        var label = $('#featureLinkLabel').val();
        var href = $('#featureLinkUrl').val();

        $('#errorFeatLabel').remove();
        $('#errorFeatHref').remove();

        if (label != '' && href != '') {
            $('#related_links').block({'message': 'Saving link...'});

            /**
             * Only submit the link if it contains http://...
             */
            var urlre = new RegExp("(ftp|http|https)://.*$", 'i');
            var isUrl = urlre.test(href);
            //console.log(isUrl);

            //if(href.indexOf('http://') != 0) {
            if (isUrl === false) {
                $('#related_links').unblock();

                $('<div />').attr({'id': 'errorFeatHref'}).css({
                    'background-color': '#edb3dd',
                    'border': '1px solid #990000',
                    'color': '#990000',
                    'padding': '5px'
                }).html('Not a valid URL.').appendTo($('#featureLinkUrl').parent());
            } else {
                $.when(addLinkToFeature($('#post_ID').val(), label, href)).done(function (a) {
                    $.when(cjRelLink.getAll($('#post_ID').val())).done(function (b) {
                        $('#relatedLinks').html(b);
                        $('#related_links').unblock();
                        $('#featureLinkLabel').val('');
                        $('#featureLinkUrl').val('');
                        initRelatedLinks($);
                    });
                });
            }
        } else {
            $('<div />').attr({'id': 'errorFeatLabel'}).css({
                'background-color': '#edb3dd',
                'border': '1px solid #990000',
                'color': '#990000',
                'padding': '5px'
            }).html('Label is required').appendTo($('#featureLinkLabel').parent());

            $('<div />').attr({'id': 'errorFeatHref'}).css({
                'background-color': '#edb3dd',
                'border': '1px solid #990000',
                'color': '#990000',
                'padding': '5px'
            }).html('URL is required').appendTo($('#featureLinkUrl').parent());
        }
    });

    $(document).on('click', '.editFeatureLink', function (e) {
        e.preventDefault();

        var link_id = $(e.currentTarget).data('id');

        $.when(getRelatedLink($('#post_ID').val(), link_id)).done(function (a) {
            $('#featLinksList').parent().block({
                message: $('#editLink'),
                css: {width: '90%', padding: '10px', 'text-align': 'left'}
            });

            $('#editLinkLabel').val(a.result.link_name);
            $('#editLinkHref').val(a.result.link_url);
            $('#editLinkId').val(a.result.link_id);
        });
    });

    $(document).on('click', '#editCancelBtn', function (e) {
        e.preventDefault();

        $('#editLink').find('input[type="text"], input[type="hidden"]').val('');

        $('#featLinksList').parent().unblock();
    });

    $(document).on('click', '#editLinkBtn', function (e) {
        e.preventDefault();

        var label = $('#editLinkLabel').val();
        var href = $('#editLinkHref').val();
        var id = $('#editLinkId').val();

        if (label != '' && href != '') {
            $.when(updateRelatedLink($('#post_ID').val(), id, label, href)).done(function (a) {
                $.when(getPostLinks($('#post_ID').val())).done(function (b) {
                    $('#featLinksList').html(b);

                    $('#featLinksList').parent().unblock();

                    initRelatedLinks($);
                });
            });
        }
    });

    $('#featureLinkUrl').on('keydown', function (e) {
        if (e.keyCode == 13) {
            $('#addLinkButton').trigger('click');
        }
    });

    $(document).on('click', '.removeFeatureLink', function (e) {
        e.preventDefault();

        var linkId = $(this).data('id');
        
        $.when(cjRelLink.remove(linkId)).done(function(a) {
            $.when(cjRelLink.getAll($('#post_ID').val())).done(function(b) {
                $('#relatedLinks').html(b);
                
                initRelatedLinks($);
            })
        });

        return false;
    });

    initRelatedLinks($);
});

function getRelatedLink(post_id, link_id) {
    return jQuery.post(ajaxurl, {
        'action': 'related_links_get_one',
        'post_id': post_id,
        'link_id': link_id
    }, null, 'json');
}

function deleteLink(post_id, link_id) {
    return jQuery.post(ajaxurl, {
        'action': 'related_links_delete',
        'post_id': post_id,
        'link_id': link_id
    }, null, 'json');
}

function initRelatedLinks($) {
    if ($('#relatedLinks').length > 0) {
        
        $('.flLabel').editable(function(value, settings) {
            var linkId = $(this).data('id');
            
            $.when(cjRelLink.updateLabel(linkId, value)).done(function(a) {
                $.when(cjRelLink.getAll($('#post_ID').val())).done(function(b) {
                    $('#relatedLinks').html(b);
                    
                    initRelatedLinks($);
                });
            });
            
            return value;
        }, {
            type:'text',
            onblur:'submit',
            select:true
        });
        
        $('.flLink').editable(function(value, settings) {
            var linkId = $(this).data('id');
            
            $.when(cjRelLink.updateLink(linkId, value)).done(function(a) {
                $.when(cjRelLink.getAll($('#post_ID').val())).done(function(b) {
                    $('#relatedLinks').html(b);
                    
                    initRelatedLinks($);
                });
            });
            
            return value;
        }, {
            type:'text',
            onblur:'submit',
            select:true
        });
        
        $('#relatedLinks').sortable({
            stop: function (e, ui) {
                $('.rlItem').each(function (i, v) {
                    var post_id = $('#post_ID').val();
                    var link_id = $(v).data('id');

                    $.when(updateLinkOrder(post_id, link_id, i)).done(function (a) {});
                });
            }
        });
        $('#relatedLinks').disableSelection();
    }
}

function updateRelatedLink(post_id, link_id, label, href) {
    return jQuery.post(ajaxurl, {
        'action': 'related_links_update',
        'post_id': post_id,
        'link_id': link_id,
        'label': label,
        'href': href
    }, null, 'json');
}

function getPostLinks(post_id) {
    return jQuery.post(ajaxurl, {'action': 'related_links_list', 'post_id': post_id}, null, 'html');
}

function updateLinkOrder(post_id, link_id, order) {
    return jQuery.post(ajaxurl, {
        'action': 'related_link_reorder',
        'post_id': post_id,
        'link_id': link_id,
        'order': order
    }, null, 'json');
}

/*function updateLinksOrder(post_id, items) {
 return jQuery.post(ajaxurl, {'action':'related_links_reorder', post:post_id, data:items}, null, 'json');
 }*/

function addLinkToFeature(post_id, label, href) {
    return jQuery.post(ajaxurl, {'action': 'related_links_add', 'post_id': post_id, 'label': label, 'href': href}, null, 'json');
}