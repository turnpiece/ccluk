/**
 * BuddyPress User Blog
 *
 *
 * This file should load in the footer
 *
 * @author      BuddyBoss
 * @since       BuddyPress User Blog (1.0.0)
 * @package     BuddyPress User Blog
 *
 * ====================================================================
 *
 * 1. Main SAP Functionality
 */


/**
 * 1. Main SAP Functionality
 * ====================================================================
 */
;if ( typeof jq == "undefined" ) {
    var jq = jQuery;
}

//initializing MediumEditor object
var title = new MediumEditor( '.sap-editable-title', {
    placeholder: {
        text: sap_loading_string.editor_title
    }
} );

var editor = new MediumEditor( '.sap-editable-area', {
    placeholder: {
        text: sap_loading_string.content_placeholder
    },
    toolbar: {
        buttons: ['bold', 'italic', 'underline', 'anchor', 'h2', 'h3','orderedlist','unorderedlist' ,'quote']
    },
    autoLink: true,
    targetBlank: true
} );

jq( function () {
    jq( '.sap-editable-area' ).mediumInsert( {
        editor: editor,
         addons: { // (object) Addons configuration
                images: { // (object) Image addon configuration
                    label: '<span class="fa fa-camera"></span>', // (string) A label for an image addon
                    deleteScript: ajaxurl+"?action=buddyboss_sap_delete_photo&sap-editor-nonce="+jq( "input[name='sap_editor_nonce']" ).val(), // (string) A relative path to a delete script
                    deleteMethod: 'POST',
                    fileDeleteOptions: {}, // (object) extra parameters send on the delete ajax request, see http://api.jquery.com/jquery.ajax/
                    preview: true, // (boolean) Show an image before it is uploaded (only in browsers that support this feature)
                    captionPlaceholder: 'Type caption for image (optional)', // (string) Caption placeholder
                    autoGrid: 3, // (integer) Min number of images that automatically form a grid
                    fileUploadOptions: { // (object) File upload configuration. See https://github.com/blueimp/jQuery-File-Upload/wiki/Options
                        url: ajaxurl+"?action=buddyboss_sap_post_photo", // (string) A relative path to an upload script
                        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i, // (regexp) Regexp of accepted file types
                        formData: [
                        {name:'sap-editor-nonce',value: jq( "input[name='sap_editor_nonce']" ).val()}
                        ],
                        limitMultiFileUploadSize : false,
                        always: function(e, data) {

                            window.sap_last_uploaded_image = data;


                        },
                        fail: function(e,data) {

                           window.sap_last_uploaded_image = data;
                        }
                    },

                    uploadCompleted : function ($el, data) {

                       if (data.result.files[0].url == "" || data.result.files[0].url == null) {
                         alert("Error while uploading image on the server.");
                         jQuery($el).remove();
                       }

                        $el.find(".fa-spin").remove();

                        var pending_status = jq('#sap-draft-status').val();
                        if (pending_status == "") { pending_status = "draft" }
                        var edit_status = jq('#sap-edit-status').val();
                        if ( 'draft' != pending_status ) {
                            return;
                        }

                        setTimeout(function(){
                            sap_post_add_screen( 'draft', 'true' );
                        },500);

                    }
                }
         }
    } );
} );

jq(document).on("click",".medium-editor-action",function() {
    setTimeout(function(){
                                        var pending_status = jq('#sap-draft-status').val();
                                        if (pending_status == "") { pending_status = "draft" }
                                        var edit_status = jq('#sap-edit-status').val();
                                        if ( 'draft' != pending_status) {
                                            return;
                                        }

                                        sap_post_add_screen( 'draft', 'true' );

    },1000);
});

//Publish Post
jq( '.sap-story-publish' ).on( 'click', function ( e ) {
    e.preventDefault();

    if ( 'empty' == sap_check_empty_title() ) {
        alert(sap_loading_string.empty_title);
        return;
    }

    jq( '#sap-draft-status' ).val('publish');
    sap_post_add_screen( 'publish', 'false' );

} );

//Submit Review
jq( '.sap-story-review' ).on( 'click', function ( e ) {
    e.preventDefault();

    if ( 'empty' == sap_check_empty_title() ) {
        alert(sap_loading_string.empty_title);
        return;
    }

    sap_post_add_screen( 'pending', 'false' );

} );

//Save Draft
jq( '.sap-story-draft' ).on( 'click', function ( e ) {
    e.preventDefault();

    jq('#sap-draft-status').val(''); //empty post status
    sap_post_add_screen( 'draft', 'false' );
    jq( '.sap-story-delete' ).show();
    jq( '.sap-pending-preview' ).hide();
    jq( '.sap-story-review' ).show();
    jQuery(".sap-story-publish").text('Publish');

} );

//update
jq( '.sap-story-update' ).on( 'click', function ( e ) {
    e.preventDefault();

    jq('#sap-edit-status').val('');
    sap_post_add_screen( 'publish', 'false' );

} );

//Delete post
jq( '.sap-story-delete' ).on( 'click', function ( e ) {
    e.preventDefault();

    var r = confirm( "Once deleted it cannot be recovered. Are you sure?" );
    if ( r == true ) {

        var data = {
            action: 'sap_delete_post',
            sap_nonce: jq( '.sap-editor-nonce' ).val(),
            sap_editor_nonce: jq( '.sap-publish-popup' ).find( 'input[name="sap_editor_nonce"]' ).val(),
            draft_pid: jq( '#sap-draft-pid' ).val()
        };

        jq.ajax( {
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function ( response ) {
                sap_empty_editor();
                window.location.reload();
            }
        } );

    } else {
        return;
    }

} );


// draft idle handle for add new post
function sap_draft_screen_auto_save() {

    leavetype = function () {
        if(jq(this).find('.medium-insert-images').length > 0 || jq(this).find('medium-insert-embeds').length > 0 || jq(this).find('p').html() !== '<br>') {
            clearTimeout( window.sap_autosave_secout );
            window.sap_autosave_secout = setTimeout( function () {

                var pending_status = jq('#sap-draft-status').val();
                if (pending_status == "") { pending_status = "draft" }
                var edit_status = jq('#sap-edit-status').val();
                if ( 'draft' != pending_status ) {
                    return;
                }

                sap_post_add_screen( 'draft', 'true' );

            }, 1000 );
        }
    };

    entertype = function () {

        clearTimeout( window.sap_autosave_secout ); //clear the timeout if there.

    };
    jQuery( document ).on( "keyup", ".sap-editable-area", leavetype );
    jQuery( document ).on( "keydown", ".sap-editable-area", entertype );

    window.sap_draft_leavetype = leavetype;
    window.sap_draft_entertype = entertype;

}

function sap_post_add_screen( visibility, draft ) {
    var thisbutton = jq( '.sap-story-publish' );
    var draftbutton = jq( '.sap-story-draft' );
    var reviewbutton = jq( '.sap-story-review' );

    //disable button
    thisbutton.addClass( 'sap-disabled' );
    draftbutton.addClass( 'sap-disabled' );
    draftbutton.text(sap_loading_string.saving_string);
    reviewbutton.addClass( 'sap-disabled' );

    var post_title_obj = title.serialize();
    var post_title_wrap = jq( '.sap-editable-title' ).attr( 'id' );
    var post_content_obj = editor.serialize();
    var post_content_wrap = jq( '.sap-editable-area' ).attr( 'id' );
    var post_visibility = jq( '#sap-draft-status' ).val();
    var edit_status = jq('#sap-edit-status').val();
    var is_index_page = jq('#sap_is_index_page').val();
    
    if( is_index_page == 'yes' ){
        visibility = 'draft';
    }

    var post_cat_arr = new Array();
    jq('.category_hierarchy_ul .single_cat .blogpost_cat').each(function(){
        var curr_obj = jq(this);
        if( curr_obj.is(':checked') ){
            post_cat_arr.push( curr_obj.val() );
        }
    });

    var data = {
        action: 'sap_save_post',
        sap_nonce: jq( '.sap-editor-nonce' ).val(),
        sap_editor_nonce: jq( '.sap-publish-popup' ).find( 'input[name="sap_editor_nonce"]' ).val(),
        post_title: post_title_obj[post_title_wrap],
        post_content: post_content_obj[post_content_wrap],
        post_cat: post_cat_arr,
        post_tags: jq( '#input-tags-select' ).val(),
        post_img: jq( '#featured-img-placeholder-id' ).val(),
        post_status: visibility,
        draft_pid: jq( '#sap-draft-pid' ).val()
    };

    //remove oembed html from post content and just leave the urls
    //let wordpress handle oembeds while displaying post content
    data.post_content = BBOSS_SAP.editor.sanitize_content( data.post_content );

    //data = jq.param( data );

    jq.ajax( {
        type: "POST",
        url: ajaxurl,
        data: data,
        success: function ( response ) {
            var response = jQuery.parseJSON( response );

            if ( response[0] == "Failed" ) {
                alert( sap_loading_string.failed_string );
            }
            else if ( response[0] == 'Empty' ) {
                alert( sap_loading_string.empty_content );
            }
            else {
                if ( visibility == 'pending' ) {
                    jq( '.sap-post-status' ).text( sap_loading_string.inreview_string );
                    alert(sap_loading_string.review_string);
                }
                if ( visibility == 'publish' ) {
                    window.location.href = response[1];
                }
                if ( visibility == 'draft' ) {
                    if( is_index_page == 'yes' ){
                        window.location.href = response[1];
                    }else{
                        jq( '.sap-post-status' ).text( sap_loading_string.draft_string );
                    }
                }
                jq( '#sap-draft-pid' ).val( response[0] );
                jq( '.sap-story-preview' ).attr( 'href', response[1] );
                jq( '.sap-story-preview' ).removeClass( 'sap-disabled' );

                if ( draft == 'false' ) {
                    jq( '.sap-story-publish' ).removeClass( 'sap-story-publish-btn' );
                }
            }

            thisbutton.removeClass( 'sap-disabled' );
            draftbutton.removeClass( 'sap-disabled' );
            draftbutton.text(sap_loading_string.saved_string);
            reviewbutton.removeClass( 'sap-disabled' );
            if ( 'pending' == visibility && response[0] != 'Empty' ) {
                draftbutton.text( 'Revert to draft' );
                jq( '#sap-draft-status' ).val( 'pending' );
                jq( '.sap-pending-preview' ).show();
                jq( '.sap-story-review' ).hide();
                jq( '.sap-story-delete' ).show();
            }

        }
    } );
}

function sap_check_empty_title() {
    var check_post_title_obj = title.serialize();
    var check_post_title_wrap = jq( '.sap-editable-title' ).attr( 'id' );
    var check_title = check_post_title_obj[check_post_title_wrap]['value'];
    var edit_status = jq('#sap-edit-status').val();

    if (  !jq('body').hasClass('userblog-ie') && 'true' != edit_status && jq(check_title).text().length == 0 ) {
        return 'empty';
    }

    if ( check_title.length == 0 ) {
        return 'empty';
    }
}

function sap_empty_editor() {
    jq('.sap-editable-title').html('');
    jq('.sap-editable-area').html('');
    jq('#sap-cat').val('1');
    jq('#sap-cat').change();
    jq('.selectize-input').find('div').remove();
}

//for tweeter preview
jq( document ).keypress( function ( e ) {
    if ( e.which == 13 ) {
        jq.getScript( '//platform.twitter.com/widgets.js', function () {
            var k = 0;
            var tweet = document.getElementById( 'twitter-widget-' + k );
            var tweetParent, tweetID;
            while ( tweet ) {
                tweetParent = tweet.parentNode;
                tweetID = tweet.dataset.tweetId;
                jq( tweet ).remove();
                twttr.widgets.createTweet(
                    tweetID,
                    tweetParent
                    );
                k++;
                tweet = document.getElementById( 'twitter-widget-' + k );
            }
        } );

    }
} );

BBOSS_SAP = { };
BBOSS_SAP.editor = { };
( function ( context, window, jq ) {
    var config = { },
        uploader = false,
        _l = { },
        filesAdded = 0
    pics_uploaded = [ ],
        state = {
            uploader_max_files: 1,
            uploader_temp_img: 'http://placehold.it/150&text=image'
        };

    context.init = function () {

        if ( !context.get_elements() ) {
            return false;
        }

        setTimeout( function () {
            context.start_uploader();
        }, 10 );

        _l.$close_button.on( 'click', function ( e ) {
            e.preventDefault();
            context.clear_upload();
        } );

        jq( '.toggle-sap-widgets' ).on( 'click', function ( e ) {
            e.preventDefault();
            jq( this ).toggleClass( 'active' );
            jq( this ).find( 'i' ).toggleClass( 'fa-pencil-square-o fa-times' );
            // jq( '.sap-widget-container' ).slideToggle(250);
            jq( '.sap-widget-container' ).toggleClass('toggled');
        } );

        //for adding new tags
        jq( '#input-tags-select' ).selectize( {
            plugins: [ 'remove_button' ],
            delimiter: ',',
            persist: false,
            create: function ( input ) {
                return {
                    value: input,
                    text: input
                };
            }
        } );
    };

    context.get_elements = function () {
        _l.$form = jq( '.sap-container-wrapper' );//CHANGE THIS

        if ( _l.$form.length === 0 ) {
            return false;
        }

        _l.$open_uploder_button = jq( '#featured-img-placeholder' );
        _l.$image = _l.$form.find( '.featured-img-preview' );
        _l.$close_button = _l.$form.find( '.sap-preview-close' );
        _l.$hidden_field = _l.$form.find( '#featured-img-placeholder-id' );

        return true;
    };

    context.start_uploader = function () {

        //var uploader_state = 'closed';
        var ieMobile = navigator.userAgent.indexOf( 'IEMobile' ) !== -1;

        uploader = new plupload.Uploader( {
            runtimes: 'html5,flash,silverlight,html4',
            browse_button: _l.$open_uploder_button[0],
            dragdrop: false,
            container: document.getElementById('sap-widget-container'),
            max_file_size: '10mb',
            multi_selection: true,
            url: ajaxurl,
            multipart: true,
            multipart_params: {
                action: 'buddyboss_sap_post_photo',
                'cookie': encodeURIComponent( document.cookie ),
                'sap-editor-nonce': jq( "input[name='sap_editor_nonce']" ).val()//CHANGE THIS
            },
            filters: [
                { title: 'Upload file', extensions: 'jpg,jpeg,gif,png,bmp', prevent_duplicates: true }//change this
            ],
            init: {
                Init: function () {

                },
                FilesAdded: function ( up, files ) {
                    if ( up.files.length > state.uploader_max_files || files.length > state.uploader_max_files ) {
                        uploader.splice( filesAdded, uploader.files.length );

                        alert( 'Exceeded maximum number of files' );
                        return false;
                    }

                    _l.$open_uploder_button.hide();
                    _l.$image.attr( 'src', sap_loading_string.config.loading_image ).show();
                    _l.$close_button.hide();
                    up.start();
                },
                UploadProgress: function ( up, file ) {
                    /*if ( file && file.hasOwnProperty( 'percent' ) ) {
                     $progressBar = jq( 'div[data-fileid="' + file.id + '"]' ).find( 'progress' );
                     progressPercent = file.percent;
                     $progressBar.val( progressPercent );
                     }*/
                },
                FileUploaded: function ( up, file, info ) {
                    var responseJSON = jq.parseJSON( info.response );
                    console.log( responseJSON );
                    _l.$hidden_field.val( responseJSON.attachment_id );
                    _l.$image.attr( 'src', responseJSON.url ).show();
                    _l.$close_button.data( 'fileid', file.id ).show();
                },
                Error: function ( up, args ) {
                    alert( 'Error uploading photo' );
                }
            }
        } );

        uploader.init();
        uploader.refresh();
    }, // start_uploader();

    context.clear_upload = function () {
        _l.$hidden_field.val( '' );
        _l.$image.attr( 'src', sap_loading_string.config.loading_image ).hide();
        _l.$close_button.hide();
        _l.$open_uploder_button.show();

        jq.each( uploader.files, function ( i, ufile ) {
            uploader.removeFile( ufile );
        } );

        //Fix: unable to change featured image on mobile
        context.start_uploader();
    },
    context.sanitize_content = function ( content ) {
        var newcontent = "<div class='bboss_e_sanitize_wrapper'>" + content.value + "</div>";
        $content = jq( newcontent );
        $content.wrap( "<div class='bboss_e_sanitize_wrapper'></div>" );
        $content.find( '.me_oembed_orig_url' ).each( function ( i, e ) {
            $org_url_div = jq( e );
            var url = $org_url_div.data( 'url' );

            // $wp_embed->autoembed( $content ) doesn't appears to be working for the Facebook embed
            // So do not sanitize Facebook oembed content
            // WordPress Trac Ticket: https://core.trac.wordpress.org/ticket/34737
            if ( url.indexOf('www.facebook.com') == -1 ) {
                url = url.replace(/(\r\n|\n|\r)/gm,"");//remove line breaks
                $embed_wrapper = $org_url_div.next( '.medium-insert-embeds-wpoembeds' );
                $embed_wrapper.html( "\n" + url + "\n" );
                $org_url_div.remove();
            } else {
                $embed_wrapper = $org_url_div.next( '.medium-insert-embeds-wpoembeds' );
                $embed_wrapper.attr( 'class', 'medium-insert-embeds-wpoembeds medium-insert-embeds' );
            }
        } );

        content.value = $content.html();
        return content;
    };

} )( BBOSS_SAP.editor, window, window.jQuery );

jQuery( document ).ready( function ( jq ) {
    BBOSS_SAP.editor.init();

    //for auto draft
    window.sap_autosave_secout = null;

    var is_index_page = jq('#sap_is_index_page').val();
    // if current page is not blog index page, then enable autosave
    if ( is_index_page === undefined || is_index_page == 'no' ) {
        sap_draft_screen_auto_save();
    }

} );


// Load more posts
jq( document ).on( 'ready', function () {
    jq( '#sort-posts-form #sort' ).on( 'change', function () {
        jq( this ).parents( 'form' ).submit();
    } );

    jq( document ).on( 'click', '.sap-load-more-posts', function ( e ) {
        e.preventDefault();

        var self = jq( this ),
            sort = self.attr( 'data-sort' ),
            max = self.attr( 'data-max' ),
            paged = self.attr( 'data-paged' );

        jq.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sap_posts_pagination',
                sort: sort,
                paged: paged,
            },
            beforeSend: function () {
                self.addClass( 'loading' );
            },
            success: function ( html ) {

                // Remove loading class
                self.removeClass( 'loading' );

                // Append Post Markup
                self.parent().before( html );

                // Remove Button
                if ( html === 'null' ) {
                    self.remove();
                    return;
                }

                // Remove Button
                if ( max === paged ) {
                    self.remove();
                }

                paged++;
                self.attr( 'data-paged', paged );
            }
        } );
    } );

} );