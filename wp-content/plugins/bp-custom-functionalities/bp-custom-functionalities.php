<?php 
defined( 'ABSPATH' ) || exit;
add_action( 'admin_menu', 'bp_cfunc_plugin_menu' );

function bp_cfunc_plugin_menu() {
	add_options_page( 
		'BP Custom Functionalities',
		'BP Custom Functionalities',
		'manage_options',
		'bp-cfunc.php',
		'bp_cfunc_plugin_func'
	);
}

function bp_cfunc_plugin_func(){
	$lock = get_option('ps_lock_bp', true);
	$exclevels = get_option('ps_exclude_levels', true);
	global $wp_roles;
    $roles = $wp_roles->get_names();
    $excroles = get_option('ps_exclude_roles', true);
    $restrict_member = get_option('ps_restrict_member', true);
    $lock_bb = get_option('ps_lock_bb', true);
	?>
  	<div class="wrap">
        <h2><?php _e('BP Custom Functionalities Settings','bp-custom-functionalities');?></h2>
        <hr/>
        <form method="post">
        	<?php wp_nonce_field( basename(__FILE__), 'bp_cfunc_settings' );?>
        	<table class="form-table">
	            <tr>
	            	<th scope="row"><?php _e('Lock BuddyPress For Guest Users','bp-custom-functionalities');?></th>
	                <td><input type="checkbox" name="ps_lock_bp" value="1" <?php if($lock): ?> checked <?php endif; ?>/></td>
	                
	            </tr>
	            <?php if ( class_exists( 'bbPress' ) ) :?>
	            <tr>
	            	<th scope="row"><?php _e('Lock bbPress For Guest Users','bp-custom-functionalities');?></th>
	                <td><input type="checkbox" name="ps_lock_bb" value="1" <?php if($lock_bb): ?> checked <?php endif; ?>/></td>
	                
	            </tr>
	        	<?php endif;?>
	            <?php 
	            global $membership_levels;
	            if($membership_levels):
	            ?>
	            <tr>
	            	<th scope="row"><?php _e('Restrict BuddyPress To Certain Membership levels','bp-custom-functionalities');?></th>
	            	<td>
	                	<select style="width: 25%; font: inherit;" size="<?php echo count($membership_levels)+1;?>" name="ps_exclude_levels[]" multiple> 
		                	<option>--SELECT LEVELS--</option>
		                	<?php foreach($membership_levels as $keys => $level)  { ?>
		                		<option value="<?php echo $level->id;?>" <?php if(is_array($exclevels) && !empty($exclevels) && in_array($level->id, $exclevels)):?> selected <?php endif;?>> 
		                			<?php echo $level->name;?>
		                		</option>
		                	<?php }?>
	                	</select>
	            	</td>
	            </tr>
	        <?php endif;?>
	            <tr>
	            	<th scope="row"><?php _e('Restrict Members From Viewing Other Members Profile','bp-custom-functionalities');?></th>
	            	<td>
	                	<input type="checkbox" name="ps_restrict_member" value="1" <?php if($restrict_member): ?> checked <?php endif; ?>/>
	            	</td>
	            </tr>

	            <tr>
	            	<th scope="row"><?php _e('Exclude Roles From Members Directory','bp-custom-functionalities');?></th>
	                <td>
	                	<select style="width: 25%; font: inherit;" size="<?php echo count($roles)+1;?>" name="ps_exclude_roles[]" multiple> 
		                	<option>--SELECT ROLES--</option>
		                	<?php foreach($roles as $key => $role)  { ?>
		                		<option value="<?php echo $key;?>" <?php if( !empty($excroles) && is_array($excroles) && in_array($key, $excroles)):?> selected <?php endif;?>> 
		                			<?php echo $role;?>
		                		</option>
		                	<?php }?>
	                	</select>
	                </td>
	            </tr>
	            <tr><td><input type="submit" value="Save Settings" class="button-primary" name="save_ps_settings"/></td></tr>
        	</table>
        </form>
        <p> <?php _e('For any custom development or support you can contact at <a href="https://prashantdev.wordpress.com/contact">https://prashantdev.wordpress.com/contact</a>.','bp-custom-functionalities'); ?>
    </div>
	<?php
}

add_action('admin_init','bp_cfunc_save_settings');
function bp_cfunc_save_settings(){
	if(current_user_can('manage_options')){
		if(isset($_POST['save_ps_settings'])){
			if ( empty($_POST['bp_cfunc_settings']) || !wp_verify_nonce( $_POST['bp_cfunc_settings'], basename(__FILE__) ) ) return;

			$lockbp = isset($_POST['ps_lock_bp']) ? '1' : '0';
			update_option('ps_lock_bp',$lockbp,true);

			$lockbb = isset($_POST['ps_lock_bb']) ? '1' : '0';
			update_option('ps_lock_bb',$lockbb,true);

			$retrict_levels = isset($_POST['ps_exclude_levels']) ? array_map( 'sanitize_text_field', $_POST['ps_exclude_levels'] ) : '';

			update_option('ps_exclude_levels',$retrict_levels ,true );

			$exclude_members = isset($_POST['ps_exclude_roles']) ? array_map( 'sanitize_text_field', $_POST['ps_exclude_roles']) : '';

			update_option('ps_exclude_roles',$exclude_members,true);

			$restrict_members = isset($_POST['ps_restrict_member']) ? '1' : '0';
			update_option('ps_restrict_member',$restrict_members,true);
		}
	}
}
?>