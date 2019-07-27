<div class="bp-messages-wrap">
    <div class="chat-header">
        <a href="<?php echo BP_Better_Messages()->functions->get_link(); ?>" class="new-message ajax" title="<?php _e( 'New Thread', 'bp-better-messages' ); ?>"><i class="fas fa-times" aria-hidden="true"></i></a>

        <a href="#" class="mobileClose"><i class="fas fa-window-close"></i></a>
    </div>
    <div class="bulk-message">
        <div class="reports">
            <h3 style="margin: 0 0 10px"><?php _e('Reports', 'bp-better-messages'); ?></h3>
            <?php
            global $wpdb;
            $reports = get_posts(array(
                'post_type' => 'bpbm-bulk-report',
                'post_status' => 'any',
                'posts_per_page' => -1
            ));

            if( count($reports) > 0 ){ ?>
                <table class="reports-list" style="width: 100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php _e('Subject', 'bp-better-messages' ); ?></th>
                        <th><?php _e('Sender', 'bp-better-messages' ); ?></th>
                        <th><?php _e('Sent', 'bp-better-messages' ); ?></th>
                        <th><?php _e('Read', 'bp-better-messages' ); ?></th>
                        <th><?php _e('Date', 'bp-better-messages' ); ?></th>
                        <th style="text-align: center"><?php _e('Disable Reply', 'bp-better-messages' ); ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <?php foreach($reports as $report){
                        $thread_ids = get_post_meta($report->ID, 'thread_ids');
                        $read_count = $wpdb->get_var(
                            $wpdb->prepare(
                               "SELECT COUNT(*) 
                                   FROM `{$wpdb->base_prefix}bp_messages_recipients` 
                                   WHERE `user_id` != %d 
                                   AND `thread_id` IN (%s) 
                                   AND `unread_count` = 0",
                               $report->post_author,
                               implode(',', $thread_ids))
                        );

                        $user = get_userdata($report->post_author);
                        ?>
                        <tr data-id="<?php esc_attr_e($report->ID); ?>" data-count="<?php esc_attr_e(count($thread_ids)); ?>">
                            <td><?php esc_attr_e($report->ID); ?></td>
                            <td><?php esc_attr_e($report->subject); ?></td>
                            <td><?php esc_attr_e($user->user_login); ?></td>
                            <td><?php esc_attr_e(count($thread_ids)); ?></td>
                            <td><?php esc_attr_e($read_count); ?></td>
                            <td><?php esc_attr_e($report->post_date); ?></td>
                            <td style="text-align: center"><input class="disableReply" type="checkbox" <?php checked($report->disableReply, '1'); ?>></td>
                            <td><span class="delete"><?php _e('Delete', 'bp-better-messages' ); ?></span></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p><?php _e('No reports yet.', 'bp-better-messages'); ?></p>
            <?php } ?>
        </div>
        <?php
        $users = new WP_User_Query(array(
            'count_total' => true
        ));
        ?>
        <form>
            <div>
                <label><?php _e( "Send To:", 'bp-better-messages' ); ?></label>
                <div class="box">
                    <ul class="send-to">
                        <li>
                            <input type="radio" name="sent-to" id="sent-to-all" value="all" checked />
                            <label for="sent-to-all"><?php _e('All Users', 'bp-better-messages' ); ?></label>
                        </li>
                        <li>
                            <input type="radio" name="sent-to" id="sent-to-role" value="role" />
                            <label for="sent-to-role"><?php _e('Users with Role', 'bp-better-messages' ); ?></label>
                            <div class="roles" style="display: none">
                                <ul class="rolesSelector">
                                    <?php foreach(wp_roles()->roles as $slug => $role ){ ?>
                                        <li><input type="checkbox" name="roles[]" id="role-<?php esc_attr_e($slug); ?>" value="<?php esc_attr_e($slug); ?>" /><label for="role-<?php esc_attr_e($slug); ?>"><?php esc_attr_e($role['name']); ?></label></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </li>
                        <?php
                        if( function_exists('groups_get_groups') ){
                        $groups = groups_get_groups(array(
                            'per_page' => -1
                        ));
                        ?>
                        <li>
                            <input type="radio" name="sent-to" id="sent-to-group" value="group" />
                            <label for="sent-to-group"><?php _e('Group', 'bp-better-messages' ); ?></label>
                            <div class="groups" style="display: none">
                                <select name="group">
                                    <?php foreach($groups['groups'] as $group){ ?>
                                    <option value="<?php esc_attr_e($group->id); ?>"><?php esc_attr_e($group->name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>

                    <p style="margin: 5px 0 0;" id="users-selected"><?php _e('Users selected', 'bp-better-messages' ); ?>: <b><?php esc_attr_e($users->get_total()); ?></b></p>
                </div>
            </div>
            <div>
                <label><?php _e( "Options", 'bp-better-messages' ); ?></label>
                <div class="box">
                    <ul class="options">
                        <li>
                            <input type="checkbox" name="disableReply" id="disableReply" value="1" checked />
                            <label for="disableReply"><?php _e('Disallow reply to this message', 'bp-better-messages' ); ?></label>
                        </li>
                        <li>
                            <input type="checkbox" name="hideThread" id="hideThread" value="1" checked />
                            <label for="hideThread"><?php _e('Hide this thread from your thread list', 'bp-better-messages' ); ?></label>
                            <p style="margin: 0 0 0 22px;font-size: 11px;"><?php _e('If you are messaging to many users better to hide new threads from your thread list.', 'bp-better-messages' ); ?></p>
                        </li>
                        <li>
                            <input type="checkbox" name="singleThread" id="singleThread" value="1" />
                            <label for="singleThread"><?php _e('Add all recipients to one thread', 'bp-better-messages' ); ?></label>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if(BP_Better_Messages()->settings['disableSubject'] !== '1') { ?>
                <div>
                    <label for="subject-input"><?php _e( 'Subject', 'bp-better-messages' ); ?></label>
                    <input style="width:100%;box-sizing:border-box" type="text" tabindex="3" name="subject" class="subject-input" id="subject-input" autocomplete="off">
                    <span class="clearfix"></span>
                </div>
            <?php } ?>

            <div>
                <label for="message-input"><?php _e( 'Message', 'bp-better-messages' ); ?></label>

                <textarea name="message" placeholder="<?php esc_attr_e( "Write your message", 'bp-better-messages' ); ?>" id="message-input" autocomplete="off"></textarea>
                <span class="clearfix"></span>
            </div>

            <div class="progress">
                <div class="progress-value" style="width: 0"></div>
                <span class="progress-text">0%</span>
            </div>

            <button type="submit"><?php _e( 'Start Messaging', 'bp-better-messages' ); ?></button>
        </form>

    </div>

    <style type="text/css">
        .bp-messages-wrap div.bulk-message .reports-list tbody tr td span.delete{
            color: red;
            text-decoration: underline;
            cursor: pointer;
        }

        .bp-messages-wrap div.bulk-message .progress{
            height: 25px;
            width: 100%;
            background: #fcfcfc;
            border: 1px solid #f2f2f2;
        }

        .bp-messages-wrap div.bulk-message .progress-value{
            background: #dddddd;
            height: 25px;
        }

        .bp-messages-wrap div.bulk-message .progress-text{
            position: absolute;
            left: 0;
            right: 0;
            margin: auto;
            top: 0;
            bottom: 0;
            text-align: center;
        }

        .bp-messages-wrap div.bulk-message {
            padding: 20px 20px;
        }
        .bp-messages-wrap .bulk-message form > div {
            margin-bottom: 20px;
            position: relative;
        }

        .bp-messages-wrap .bulk-message .box{
            background: #fcfcfc;
            padding: 10px;
            border: 1px solid #f2f2f2;
        }

        .bp-messages-wrap .bulk-message form ul.send-to,
        .bp-messages-wrap .bulk-message form ul.options{
            margin-bottom: 0;
            margin-left: 0;
        }

        .bp-messages-wrap .bulk-message form ul.send-to li,
        .bp-messages-wrap .bulk-message form ul.options li {
            list-style: none;
            margin: 0;
        }

        .bp-messages-wrap .bulk-message form div.roles{
            margin-left: 22px;
        }

        .bp-messages-wrap .bulk-message form ul.send-to li input,
        .bp-messages-wrap .bulk-message form ul.options li input {
            margin: 0 5px 0 0;
            vertical-align: middle;
        }

        .bp-messages-wrap .bulk-message form > div label {
            margin-bottom: 5px;
            font-size: 14px;
        }
        .bp-messages-wrap .bulk-message form > div > label{
            font-weight: bold;
        }
        .bp-messages-wrap .bulk-message form > div textarea {
            display: block;
            width: 100%;
            box-sizing: border-box;
            background-color: #fff;
            border: 1px solid #ccc;
            -moz-border-radius: 3px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -moz-transition: border-color 0.15s ease-in-out, -moz-box-shadow 0.15s ease-in-out;
            -o-transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            -webkit-transition: border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            font-size: 14px;
            padding-left: 12px;
            padding-top: 10px;
            padding-bottom: 6px;
            line-height: 12px;
            min-height: 80px;
        }
    </style>

    <script type="text/javascript">
        (function($){
            var ajax = '<?php echo admin_url('admin-ajax.php'); ?>';
            var total = 0;
            var perRequest = 5;
            var parts = 0;
            var current = 0;
            var sending = false;
            var report_id = 0;

            $('.bp-messages-wrap').on('change', '.bulk-message .reports-list input.disableReply', function(event){
                event.preventDefault();
                var disableReply = $(this).is(':checked');
                var tr = $(this).parent().parent();
                var report_id = tr.data('id');

                $.post(ajax, {
                    'action'    : 'bp_better_messages_change_report',
                    'report_id' : report_id,
                    'property'  : 'disableReply',
                    'nonce'     : '<?php echo wp_create_nonce( 'change_report' ); ?>',
                    'value'     : (disableReply) ? '1' : '0'
                }, function(response){
                    if( ! response ){
                        BBPMShowError('<?php esc_attr_e('Security error', 'bp-better-messages'); ?>');
                    } else {
                        if(disableReply){
                            BBPMNotice('<?php esc_attr_e("Users can`t reply to this message anymore.", 'bp-better-messages'); ?>');
                        } else {
                            BBPMNotice('<?php esc_attr_e("Users can reply to this message now.", 'bp-better-messages'); ?>');
                        }
                    }
                });

            });

            $('.bp-messages-wrap').on('click touchstart', '.bulk-message .reports-list span.delete', function(event){
                event.preventDefault();
                event.stopPropagation();
                var tr = $(this).parent().parent();
                var report_id = tr.data('id');
                var count     = tr.data('count');
                var message   = '<?php esc_attr_e("Are you sure you want to delete %d thread(s) and report?", 'bp-better-messages'); ?>'.replace('%d', count);

                if ( confirm(message) ) {
                    $.post(ajax, {
                        'action'    : 'bp_better_messages_delete_report',
                        'nonce'     : '<?php echo wp_create_nonce( 'delete_report' ); ?>',
                        'report_id' : report_id
                    }, function(response){
                        updateReports();
                    });
                }
            });

            $('.bp-messages-wrap').on('change', '.bulk-message .send-to input', function(event){
                //event.preventDefault();
                var form = $('.bulk-message > form');
                $.post(ajax, {
                    action: 'bp_better_messages_select_users',
                    nonce: '<?php echo wp_create_nonce( 'select_users' ); ?>',
                    selector: form.serialize()
                }, function(response){
                    $('#users-selected b').text(response.total);
                });
            });

            $('.bp-messages-wrap').on('submit', '.bulk-message > form', function(event){
                event.preventDefault();
                event.stopImmediatePropagation();
                var form = $('.bulk-message > form');

                $('.bulk-message .progress .progress-value').animate({width: '0%'});
                $('.bulk-message .progress .progress-text').text( '0%' );

                $.post(ajax, {
                    action: 'bp_better_messages_select_users',
                    selector: form.serialize(),
                    nonce: '<?php echo wp_create_nonce( 'select_users' ); ?>',
                    report_id: report_id
                }, function(response){
                    $('#users-selected b').text(response.total);
                    total = parseInt(response);
                    parts = Math.ceil(response.total / perRequest);
                    if($('#singleThread').is(':checked')){
                        parts = 1;
                    }
                    current = 1;

                    if( typeof response.errors == 'object'){
                        $.each(response.errors, function () {
                            BBPMShowError(this);
                        })
                    } else {
                        report_id = response.report_id;
                        sendMessages();
                    }

                });
            });

            $('.bp-messages-wrap').on('change', 'ul.send-to > li > input', function(){
                var sentTo = $('.bp-messages-wrap ul.send-to > li > input:checked').val();

                if(sentTo === 'role'){
                    $('.bulk-message .roles').show();
                } else {
                    $('.bulk-message .roles').hide();
                }
                if(sentTo === 'group'){
                    $('.bulk-message .groups').show();
                } else {
                    $('.bulk-message .groups').hide();
                }
            });

            function updateReports(){
                $.get('', function(html){
                    var reportsList = $(html).find('.bp-messages-wrap:not(.bp-better-messages-mini) .bulk-message .reports').html();
                    $('.bp-messages-wrap:not(.bp-better-messages-mini) .bulk-message .reports').html(reportsList);
                });
            }

            function sendMessages(){
                var form = $('.bulk-message > form');
                $.post(ajax, {
                    action  : 'bp_better_messages_send_messages',
                    selector: form.serialize(),
                    current : current,
                    nonce: '<?php echo wp_create_nonce( 'send_messages' ); ?>',
                    perPage : perRequest,
                    report_id: report_id
                }, function(response){
                    if(response === 'ok'){
                        var percent = Math.ceil((current * 100) / parts);
                        $('.bulk-message .progress .progress-value').animate({width: percent + '%'});
                        $('.bulk-message .progress .progress-text').text( percent + '%' );

                        if(current < parts){
                            current++;
                            sendMessages();
                        } else {
                            updateReports();
                        }
                    }
                });
            }

        })(jQuery)
    </script>

    <div class="preloader"></div>
</div>