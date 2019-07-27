<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'BP_Better_Messages_Files' ) ):

    class BP_Better_Messages_Files
    {

        public static function instance()
        {
            static $instance = null;

            if ( null === $instance ) {
                $instance = new BP_Better_Messages_Files();
            }

            return $instance;
        }


        public function __construct()
        {
            add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
            add_action( 'wp_ajax_bp_better_messages_attach_file', array( $this, 'handle_upload' ) );
            add_action( 'wp_ajax_bp_better_messages_deattach_file', array( $this, 'handle_delete' ) );

            add_action( 'bp_messages_after_reply_form', array( $this, 'upload_form' ), 10, 1 );
            add_action( 'bp_better_messages_before_message_send', array( $this, 'empty_message_fix' ), 20, 2 );

            /**
             * Modify message before save
             */
            add_action( 'messages_message_before_save', array( $this, 'add_files_to_message' ) );
            add_action( 'messages_message_after_save', array( $this, 'add_files_to_message_meta' ) );

            add_filter( 'bp_better_messages_pre_format_message', array( $this, 'nice_files' ), 90, 3 );

            add_action( 'init', array($this, 'register_cleaner') );
            add_action( 'bp_better_messages_clear_attachments', array($this, 'remove_old_attachments') );
        }

	    public function register_cleaner()
	    {
		    if ( ! wp_next_scheduled( 'bp_better_messages_clear_attachments' ) ) {
			    wp_schedule_event( time(), 'fifteen_minutes', 'bp_better_messages_clear_attachments' );
		    }
	    }

	    public function load_scripts(){
            wp_enqueue_script( 'uppy-js',  BP_Better_Messages()->url . 'assets/js/uppy.min.js' );
            wp_enqueue_style( 'uppy-css', BP_Better_Messages()->url . 'assets/css/uppy.min.css', __FILE__ );
        }

	    public function remove_old_attachments(){

		    $delete_after_days = (int) BP_Better_Messages()->settings['attachmentsRetention'];
		    $delete_after = $delete_after_days * 24 * 60 * 60;
		    $delete_after_time = time() - $delete_after;

		    $old_attachments = get_posts(array(
			    'post_type' => 'attachment',
			    'posts_per_page' => 50,
			    'meta_query' => array(
				    array(
					    'key' => 'bp-better-messages-attachment',
					    'value' => '1',
					    'compare' => '='
				    ),
				    array(
					    'key' => 'bp-better-messages-upload-time',
					    'value' => $delete_after_time,
					    'compare' => '<'
				    )
			    ),
			    'fields' => 'ids'
		    ));

		    foreach($old_attachments as $attachment){
		        $this->remove_attachment($attachment);
            }
        }

	    public function remove_attachment($attachment_id){
	        global $wpdb;

	        $message_id = get_post_meta($attachment_id, 'bp-better-messages-message-id', true);
	        if( ! $message_id ) return false;

            // Get Message
	        $table = $wpdb->prefix . 'bp_messages_messages'; 
	        $message_attachments = bp_messages_get_meta($message_id, 'attachments', true);

            wp_delete_attachment($attachment_id, true);

	        /**
	         * Deleting attachment from message
	         */
	        if( isset( $message_attachments[$attachment_id] ) ) {
		        $message = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}bp_messages_messages` WHERE `id` = '{$message_id}'" );
		        $content = str_replace($message_attachments[$attachment_id], '', $message->content);
		        if( empty( trim( $content ) ) ){
			        bp_messages_delete_meta($message_id);
			        $wpdb->delete($table, array('id' => $message_id));
		        } else {
			        unset($message_attachments[$attachment_id]);
			        bp_messages_update_meta($message_id, 'attachments', $message_attachments);
			        $wpdb->update($table, array('content' => $content), array('id' => $message_id));
		        }
	        }

	        return true;

        }

        public function empty_message_fix( &$args, &$errors )
        {
            if ( ! empty( $args[ 'content' ] ) ) return $args;
            if (  !empty( $this->get_unused_attachments( $args[ 'thread_id' ], get_current_user_id() ) ) ){
                $args[ 'content' ] = ' ';
                unset($errors['empty']);
            }

            return $args;
        }

        public function nice_files( $message, $message_id, $context )
        {
            if ( $context !== 'stack' ) return $message;

            global $processedUrls;

            $attachments = bp_messages_get_meta( $message_id, 'attachments', true );
            if ( !empty( $attachments ) ) {

                $images = array();
                $videos = array();
                $audios = array();
                $files = array();

                foreach ( $attachments as $attachment_id => $url ) {
                    $_attachment = get_post( $attachment_id );
                    if ( ! $_attachment ) {
	                    $message = str_replace( array( $url . "\n", "\n" . $url, $url ), '', $message );
                        continue;
                    } else if ( strpos( $_attachment->post_mime_type, 'image/' ) === 0 ) {
                        $images[$attachment_id] = array(
                                'url' => $url,
                                'thumb' => wp_get_attachment_image_url( $attachment_id, array(200, 200) )
                        );
                        $message = str_replace( array( $url . "\n", "\n" . $url, $url ), '', $message );
                    } else if (strpos( $_attachment->post_mime_type, 'video/') === 0 ) {
                        $videos[$attachment_id] = $url;
                        $message = str_replace( array( $url . "\n", "\n" . $url, $url ), '', $message );
                    }else if (strpos( $_attachment->post_mime_type, 'audio/') === 0 ) {
                        $audios[$attachment_id] = $url;
                        $message = str_replace( array( $url . "\n", "\n" . $url, $url ), '', $message );
                    } else {
                        $files[$attachment_id] = $url;
                        $message = str_replace( array( $url . "\n", "\n" . $url, $url ), '', $message );
                    }
                }


                if ( !empty( $videos ) ) {
                    $message .= '<div class="videos">';
                    foreach ( $videos as $video ) {
                        $ext = pathinfo( $video, PATHINFO_EXTENSION );
                        $video = do_shortcode('[video '.$ext.'="'.$video.'"][/video]');
                        $video = str_replace('style="', 'style="width: 100% !important;height: 100% !important;', $video);
                        $processedUrls[ $message_id ][] = '<div class="video"><div class="video-container">' . $video . '</div></div>';
                        $message .= '%%link_' . count( $processedUrls[ $message_id ] ) . '%%';
                    }
                    $message .= '</div>';
                }

                if ( !empty( $images ) ) {
                    $message .= '<div class="images images-'. count($images) .'">';
                    foreach ( $images as $image ) {
                        $processedUrls[ $message_id ][] = '<a href="' . $image['url'] . '" target="_blank" class="image" style="background-image: url('.$image['thumb'].');"></a>';
                        $message .= '%%link_' . count( $processedUrls[ $message_id ] ) . '%%';
                    }
                    $message .= '</div>';
                }

                if ( !empty( $audios ) ) {
                    $message .= '<div class="audios">';
                    foreach ( $audios as $audio ) {
                        $ext = pathinfo( $audio, PATHINFO_EXTENSION );
                        $processedUrls[ $message_id ][] = do_shortcode('[audio '.$ext.'="'.$audio.'"]');
                        $message .= '%%link_' . count( $processedUrls[ $message_id ] ) . '%%';
                    }
                    $message .= '</div>';
                }

                if ( !empty( $files ) ) {
                    $message .= '<div class="files">';
                    foreach ( $files as $attachment_id => $file ) {
                        $path = get_attached_file( $attachment_id );
                        $size = size_format(filesize($path));
                        $ext = pathinfo( $file, PATHINFO_EXTENSION );
                        $name = get_post_meta($attachment_id, 'bp-better-messages-original-name', true);
                        if( empty($name) ) $name = wp_basename( $file );
                        $icon = 'file-o';
                        if( in_array($ext, $this->get_archive_extensions())) $icon = 'file-archive';
                        if( in_array($ext, $this->get_text_extensions())) $icon = 'file-text';
                        if( $ext == 'pdf' ) $icon = 'file-pdf';
                        if( strpos($ext, 'doc') === 0 ) $icon = 'file-word';
                        if( strpos($ext, 'xls') === 0 ) $icon = 'file-excel';

                        $processedUrls[ $message_id ][] = '<a href="' . $file . '" target="_blank" class="file file-' . $ext . '"><i class="fas fa-'.$icon.'" aria-hidden="true"></i>' . $name . '<span class="size">('.$size.')</span></a>';
                        $message .= '%%link_' . count( $processedUrls[ $message_id ] ) . '%%';
                    }
                    $message .= '</div>';
                }
            }

            return $message;
        }

        public function get_archive_extensions(){
            return array(
                "7z",
                "a",
                "apk",
                "ar",
                "cab",
                "cpio",
                "deb",
                "dmg",
                "egg",
                "epub",
                "iso",
                "jar",
                "mar",
                "pea",
                "rar",
                "s7z",
                "shar",
                "tar",
                "tbz2",
                "tgz",
                "tlz",
                "war",
                "whl",
                "xpi",
                "zip",
                "zipx"
            );
        }

        public function get_text_extensions(){
            return array(
                "txt", "rtf"
            );
        }

        public function add_files_to_message( $message )
        {
            $attachments = $this->get_unused_attachments( $message->thread_id, get_current_user_id() );

            foreach ( $attachments as $attachment ) {
                $message->message .= "\n" . wp_get_attachment_url( $attachment->ID );
            }

        }

        public function add_files_to_message_meta( $message )
        {
            $attachments = $this->get_unused_attachments( $message->thread_id, get_current_user_id() );

            $attachment_meta = array();

            foreach ( $attachments as $attachment ) {
                $attachment_meta[ $attachment->ID ] = wp_get_attachment_url( $attachment->ID );
                add_post_meta( $attachment->ID, 'bp-better-messages-message-id', $message->id, true );
            }

            if( count( $attachment_meta ) > 0 ) bp_messages_add_meta( $message->id, 'attachments', $attachment_meta, true );
        }

        public function upload_form( $thread_id )
        {
            $user_id = get_current_user_id();

            if ( ! $this->user_can_upload( $user_id, $thread_id ) ) return false;

	        $extensions = apply_filters( 'bp_better_messages_attachment_allowed_extensions', BP_Better_Messages()->settings['attachmentsFormats'], $thread_id, $user_id );

	        $maxSize = apply_filters( 'bp_better_messages_attachment_max_size', BP_Better_Messages()->settings['attachmentsMaxSize'], $thread_id, $user_id );

            $attachments = $this->get_unused_attachments( $thread_id, $user_id );

            $files = array();

            if ( !empty( $attachments ) ) {
                foreach ( $attachments as $attachment ) {
                    $url = wp_get_attachment_thumb_url( $attachment->ID );
                    $path = get_attached_file( $attachment->ID );

                    $files[] = array(
                        'id'   => $attachment->ID,
                        'name' => get_post_meta( $attachment->ID, 'bp-better-messages-original-name', true ),
                        'size' => filesize( $path ),
                        'type' => $attachment->post_mime_type,
                        'file' => $url,
                        'url'  => $url
                    );
                }
            }
            $endpoint = add_query_arg(
                array(
                    'action' => 'bp_better_messages_attach_file',
                    'thread_id' => $thread_id,
                    'nonce' => wp_create_nonce( 'file-upload-' . $thread_id )
                ),
                admin_url( 'admin-ajax.php' )
            );
            ?>
            <span class="clearfix"></span>

            <script type="text/javascript">
                (function($){
                insertButton();
                function insertButton() {
                    var files = <?php echo json_encode($files); ?>;
                    var count = files.length;
                    var initiated = $('.reply form .message').prepend('<span id="bpbm-upload-btn" class="upload-btn"><i class="fas fa-paperclip" aria-hidden="true"></i><span class="count count-' + count + '">' + count + '</span></span>');
                    if (initiated.length == 0) {
                        setTimeout(insertButton, 1000);
                    } else {
                        var uppy = Uppy.Core({
                            autoProceed: true,
                            restrictions: {
                                maxFileSize: <?php esc_attr_e($maxSize * 1000 * 1000); ?>,
                                maxNumberOfFiles: false,
                                minNumberOfFiles: false,
                                allowedFileTypes: false
                            },
                            locale: {
                                strings: {
                                    youCanOnlyUploadX: {
                                        0: '<?php esc_attr_e('You can only upload %{smart_count} file', 'bp-better-messages'); ?>',
                                        1: '<?php esc_attr_e('You can only upload %{smart_count} files', 'bp-better-messages'); ?>'
                                    },
                                    youHaveToAtLeastSelectX: {
                                        0: '<?php esc_attr_e('You have to select at least %{smart_count} file', 'bp-better-messages'); ?>',
                                        1: '<?php esc_attr_e('You have to select at least %{smart_count} files', 'bp-better-messages'); ?>'
                                    },
                                    exceedsSize: '<?php esc_attr_e('This file exceeds maximum allowed size of', 'bp-better-messages'); ?>',
                                    youCanOnlyUploadFileTypes: '<?php esc_attr_e('You can only upload', 'bp-better-messages'); ?>:',
                                    uppyServerError: '<?php esc_attr_e('Connection with Uppy Server failed', 'bp-better-messages'); ?>'
                                }
                            },
                            onBeforeUpload: function(files) {
                                $.each(files, function(index){
                                    if (this.source.substring(0, 3) === "wp_") {
                                        files[index]['progress']['uploadComplete'] = true;
                                        files[index]['progress']['uploadStarted'] = true;

                                        uppy.setFileMeta(index, {
                                            id: this.source.replace('wp_', '')
                                        })
                                    }
                                });
                                return Promise.resolve()
                            }
                        });

                        uppy.use(Uppy.Dashboard, {
                            trigger: '#bpbm-upload-btn',
                            hideUploadButton: true,
                            disablePageScrollWhenModalOpen: false,
                            locale: {
                                strings: {
                                    uploading: '<?php esc_attr_e('Uploading', 'bp-better-messages'); ?>',
                                    uploadComplete: '<?php esc_attr_e('Upload complete', 'bp-better-messages'); ?>',
                                    uploadFailed: '<?php esc_attr_e('Upload failed', 'bp-better-messages'); ?>',
                                    pleasePressRetry: '<?php esc_attr_e('Please press Retry to upload again', 'bp-better-messages'); ?>',
                                    paused: '<?php esc_attr_e('Paused', 'bp-better-messages'); ?>',
                                    error: '<?php esc_attr_e('Error', 'bp-better-messages'); ?>',
                                    retry: '<?php esc_attr_e('Retry', 'bp-better-messages'); ?>',
                                    pressToRetry: '<?php esc_attr_e('Press to retry', 'bp-better-messages'); ?>',
                                    retryUpload: '<?php esc_attr_e('Retry upload', 'bp-better-messages'); ?>',
                                    resumeUpload: '<?php esc_attr_e('Resume upload', 'bp-better-messages'); ?>',
                                    cancelUpload: '<?php esc_attr_e('Cancel upload', 'bp-better-messages'); ?>',
                                    pauseUpload: '<?php esc_attr_e('Pause upload', 'bp-better-messages'); ?>',
                                    uploadXFiles: {
                                        0: '<?php esc_attr_e('Upload %{smart_count} file', 'bp-better-messages'); ?>',
                                        1: '<?php esc_attr_e('Upload %{smart_count} files', 'bp-better-messages'); ?>'
                                    },
                                    uploadXNewFiles: {
                                        0: '<?php esc_attr_e('Upload +%{smart_count} file', 'bp-better-messages'); ?>',
                                        1: '<?php esc_attr_e('Upload +%{smart_count} files', 'bp-better-messages'); ?>'
                                    },
                                    selectToUpload: '<?php esc_attr_e('Select files to upload', 'bp-better-messages'); ?>',
                                    closeModal: '<?php esc_attr_e('Close Modal', 'bp-better-messages'); ?>',
                                    upload: '<?php esc_attr_e('Upload', 'bp-better-messages'); ?>',
                                    importFrom: '<?php esc_attr_e('Import files from', 'bp-better-messages'); ?>',
                                    dashboardWindowTitle: '<?php esc_attr_e('Uppy Dashboard Window (Press escape to close)', 'bp-better-messages'); ?>',
                                    dashboardTitle: '<?php esc_attr_e('Uppy Dashboard', 'bp-better-messages'); ?>',
                                    copyLinkToClipboardSuccess: '<?php esc_attr_e('Link copied to clipboard.', 'bp-better-messages'); ?>',
                                    copyLinkToClipboardFallback: '<?php esc_attr_e('Copy the URL below', 'bp-better-messages'); ?>',
                                    done: '<?php esc_attr_e('Done', 'bp-better-messages'); ?>',
                                    dropPasteImport: '<?php esc_attr_e('Drop files here, paste, import from one of the locations above or', 'bp-better-messages'); ?>',
                                    dropPaste: '<?php esc_attr_e('Drop files here, paste or', 'bp-better-messages'); ?>',
                                    browse: '<?php esc_attr_e('browse', 'bp-better-messages'); ?>',
                                    fileProgress: '<?php esc_attr_e('File progress: upload speed and ETA', 'bp-better-messages'); ?>',
                                    numberOfSelectedFiles: '<?php esc_attr_e('Number of selected files', 'bp-better-messages'); ?>',
                                    uploadAllNewFiles: '<?php esc_attr_e('Upload all new files', 'bp-better-messages'); ?>',
                                    emptyFolderAdded: '<?php esc_attr_e('No files were added from empty folder', 'bp-better-messages'); ?>',
                                    folderAdded: {
                                        0: '<?php esc_attr_e('Added %{smart_count} file from %{folder}', 'bp-better-messages'); ?>',
                                        1: '<?php esc_attr_e('Added %{smart_count} files from %{folder}', 'bp-better-messages'); ?>'
                                    }
                                }
                            }
                        });

                        $(document).on("bp-better-messages-message-sent", function(){
                            uppy.reset();
                            count = 0;
                            var button = $('#bpbm-upload-btn .count');
                            button.text(count);
                            button.attr('class', 'count count-' + count);
                        });

                        uppy.use(Uppy.XHRUpload, {
                            endpoint: '<?php echo $endpoint; ?>',
                            getResponseError: function getResponseError(xhr) {
                                var result = jQuery.parseJSON(xhr);
                                BBPMShowError(result.error);
                            }
                        });

                        uppy.run();

                        uppy.on('upload-success', function(fileId, resp, uploadURL) {
                            var id = resp.result;
                            uppy.setFileMeta(fileId, {
                                id: id
                            });

                            count++;

                            var button = $('#bpbm-upload-btn .count');
                            button.text(count);
                            button.attr('class', 'count count-' + count);
                        });

                        uppy.on('file-removed', function(file) {
                            var id = file.meta.id;
                            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                                action: 'bp_better_messages_deattach_file',
                                file_id: id,
                                thread_id: "<?php echo $thread_id; ?>",
                                nonce: "<?php echo wp_create_nonce( 'file-delete-' . $thread_id ); ?>"
                            });

                            count--;
                            var button = $('#bpbm-upload-btn .count');
                            button.text(count);
                            button.attr('class', 'count count-' + count);
                        });



                        $.each(files, function () {
                           var file = this;
                           if(file.type.substring(0, 6) === "image/"){
                               var xhr = new XMLHttpRequest();
                               xhr.open('GET', file.url, true);
                               xhr.responseType = 'blob';
                               xhr.onload = function(e) {
                                   var blob = new File([this.response], file.name);
                                   uppy.addFile({
                                       name: file.name, // file name
                                       data: blob,
                                       type: file.type, // file type
                                       source: 'wp_' +  file.id,
                                       size: file.size,
                                       isRemote: false
                                   });
                               };
                               xhr.send();
                           } else {
                               uppy.addFile({
                                   name: file.name, // file name
                                   data: '',
                                   type: file.type, // file type
                                   source: 'wp_' +  file.id,
                                   size: file.size,
                                   isRemote: false
                               });
                           }

                        });

                        function onEnter(event) {
                            event.preventDefault();
                            event.stopImmediatePropagation();

                            var dataTransfer = event.dataTransfer || (event.originalEvent && event.originalEvent.dataTransfer);
                            if (isDragSourceExternalFile(dataTransfer)){
                                uppy.getPlugin('Dashboard').openModal();
                            }
                        }

                        function onLeave(event) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            //var className = event.relatedTarget.className;
                            if ( event.relatedTarget === null || event.relatedTarget.className.substring(0, 5) !== "uppy-") {
                                uppy.getPlugin('Dashboard').closeModal();
                            }
                        }

                        function isDragSourceExternalFile(dataTransfer){
                            // Source detection for Safari v5.1.7 on Windows.
                            if (typeof Clipboard != 'undefined') {
                                if (dataTransfer.constructor == Clipboard) {
                                    if (dataTransfer.files.length > 0)
                                        return true;
                                    else
                                        return false;
                                }
                            }

                            // Source detection for Firefox on Windows.
                            if (typeof DOMStringList != 'undefined'){
                                var DragDataType = dataTransfer.types;
                                if (DragDataType.constructor == DOMStringList){
                                    if (DragDataType.contains('Files'))
                                        return true;
                                    else
                                        return false;
                                }
                            }

                            // Source detection for Chrome on Windows.
                            if (typeof Array != 'undefined'){
                                var DragDataType = dataTransfer.types;
                                if (DragDataType.constructor == Array){
                                    if (DragDataType.indexOf('Files') != -1)
                                        return true;
                                    else
                                        return false;
                                }
                            }
                        }

                        $(window).on( 'dragover.bp-messages', onEnter );
                        //$(window).on( 'dragenter.bp-messages', onEnter );
                        $('.uppy-Dashboard *').on( 'dragleave', onLeave );
                    }
                }
                })(jQuery)
            </script>
            <?php
            return true;
        }

	    public function random_string($length) {
		    $key = '';
		    $keys = array_merge(range(0, 9), range('a', 'z'));

		    for ($i = 0; $i < $length; $i++) {
			    $key .= $keys[array_rand($keys)];
		    }

		    return $key;
	    }

        public function get_unused_attachments( $thread_id, $user_id )
        {
            return get_posts( array(
                'post_type'   => 'attachment',
                'post_status' => 'any',
                'author'      => $user_id,
                'numberposts' => -1,
                'meta_query'  => array(
                    array(
                        'key'   => 'bp-better-messages-attachment',
                        'value' => true
                    ),
                    array(
                        'key'   => 'bp-better-messages-thread-id',
                        'value' => $thread_id
                    ),
                    array(
                        'key'     => 'bp-better-messages-message-id',
                        'compare' => 'NOT EXISTS'
                    )
                )
            ) );
        }

        public function handle_delete()
        {
            $user_id = get_current_user_id();
            $attachment_id = intval( $_POST[ 'file_id' ] );
            $thread_id = intval( $_POST[ 'thread_id' ] );
            $attachment = get_post( $attachment_id );


            // Security verify 1
            if ( !BP_Messages_Thread::check_access( $thread_id, $user_id ) ||
                !wp_verify_nonce( $_POST[ 'nonce' ], 'file-delete-' . $thread_id ) ||
                ( (int)$attachment->post_author !== $user_id ) || !$attachment
            ) {
                wp_send_json( false );
                exit;
            }

            // Security verify 2
            if ( (int) get_post_meta( $attachment->ID, 'bp-better-messages-thread-id', true ) !== $thread_id ) {
                wp_send_json( false );
                exit;
            }

            // Looks like we can delete it now!
            $result = wp_delete_attachment( $attachment->ID, true );
            if ( $result ) {
                wp_send_json( true );
            } else {
                wp_send_json( false );
            }

            exit;
        }

        public function upload_dir($dir){
            $dirName = apply_filters('bp_better_messages_upload_dir_name', 'bp-better-messages');

            return array(
	             'path'   => $dir['basedir'] . '/' . $dirName,
	             'url'    => $dir['baseurl'] . '/' . $dirName,
	             'subdir' => '/' . $dirName
            ) + $dir;
        }

        public function upload_mimes($mimes, $user){
	        $allowedExtensions = BP_Better_Messages()->settings['attachmentsFormats'];
	        $allowed = array();
	        foreach(wp_get_mime_types() as $extensions => $mime_type){
		        $key = array();

		        foreach(explode('|', $extensions) as $ext){
			        if( in_array($ext, $allowedExtensions) ) $key[] = $ext;
		        }

		        if( ! empty($key) ){
			        $key = implode('|', $key);
			        $allowed[$key] = $mime_type;
		        }
	        }
            return $allowed;
        }

        public function handle_upload()
        {

	        add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
	        add_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 10, 2 );
            $result = array(
                'result' => false,
                'error'  => ''
            );

            $thread_id = intval( $_GET[ 'thread_id' ] );
            $user_id   = get_current_user_id();

            if ( !empty( $_FILES[ 'files' ] ) || wp_verify_nonce( $_GET[ 'nonce' ], 'file-upload-' . $thread_id ) ) {

	            // The nonce was valid and the user has the capabilities, it is safe to continue.

	            $can_upload = $this->user_can_upload( get_current_user_id(), $thread_id );

                if ( ! $can_upload ) {
                    $result[ 'error' ] = __( 'You can`t upload files.', 'bp-better-messages' );
                    status_header( 403 );
                    wp_send_json( $result );
                    exit;
                }

	            $extensions = apply_filters( 'bp_better_messages_attachment_allowed_extensions', BP_Better_Messages()->settings['attachmentsFormats'], $thread_id, $user_id );
	            $ext = strtolower(pathinfo($_FILES['files']['name'][0], PATHINFO_EXTENSION));
                $name = wp_basename($_FILES['files']['name'][0]);

	            if(BP_Better_Messages()->settings['attachmentsRandomName'] === '1'){
                    $_FILES['files']['name'][0] = $this->random_string(20) . '.' . $ext;
	            }

	            if( ! in_array( $ext, $extensions ) ){
		            $result[ 'error' ] = __( 'This file type are not allowed to be uploaded: ' . $ext, 'bp-better-messages' );
		            status_header( 403 );
		            wp_send_json( $result );
		            exit;
	            }


	            $maxSizeMb = apply_filters( 'bp_better_messages_attachment_max_size', BP_Better_Messages()->settings['attachmentsMaxSize'], $thread_id, $user_id );
	            $maxSize = $maxSizeMb * 1024 * 1024;

	            if($_FILES['files']['size'][0] > $maxSize){
		            $result[ 'error' ] = sprintf(__( '%s is too large! Please upload file up to %d MB.', 'bp-better-messages' ), $_FILES['files']['name'][0], $maxSizeMb);
		            status_header( 403 );
		            wp_send_json( $result );
		            exit;
                }



                // These files need to be included as dependencies when on the front end.
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                foreach ( $_FILES[ 'files' ] as $key => $val ) {
                    $_FILES[ 'files' ][ $key ] = $val[ 0 ];
                }

                // Remember, 'my_image_upload' is the name of our file input in our form above.
                $attachment_id = media_handle_upload( 'files', $_POST[ 'post_id' ] );

                if ( is_wp_error( $attachment_id ) ) {
                    // There was an error uploading the image.
                    status_header( 400 );
                    $result[ 'error' ] = $attachment_id->get_error_message();
                } else {
                    // The image was uploaded successfully!
                    add_post_meta( $attachment_id, 'bp-better-messages-attachment', true, true );
                    add_post_meta( $attachment_id, 'bp-better-messages-thread-id', $thread_id, true );
	                add_post_meta( $attachment_id, 'bp-better-messages-upload-time', time(), true );
	                add_post_meta( $attachment_id, 'bp-better-messages-original-name', $name, true );
                    status_header( 200 );
                    $result[ 'result' ] = $attachment_id;
                }
            } else {
                status_header( 406 );
                $result[ 'error' ] = __( 'Your request is empty.', 'bp-better-messages' );
            }

            wp_send_json( $result );

	        remove_filter( 'upload_dir', array( $this, 'upload_dir' ) );
	        remove_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 10, 2 );
            exit;
        }

        public function user_can_upload( $user_id, $thread_id )
        {
            return apply_filters( 'bp_better_messages_user_can_upload_files', BP_Messages_Thread::check_access( $thread_id, $user_id ), $user_id, $thread_id );
        }

    }

endif;


function BP_Better_Messages_Files()
{
    return BP_Better_Messages_Files::instance();
}
