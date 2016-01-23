<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery');
global $wp_scripts;
?>
<!DOCTYPE html>
<head>
<title><?php _e("Insert Divider", 'virtue-toolkit'); ?></title>
 <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
<base target="_self" />
<?php wp_print_scripts(); ?>
<script type="text/javascript">
 
var ButtonDialog = {
	local_ed : 'ed',
	init : function(ed) {
		ButtonDialog.local_ed = ed;
		tinyMCEPopup.resizeToInnerSize();
	},
	insert : function insertButton(ed) {
	 
		// Try and remove existing style / blockquote
		tinyMCEPopup.execCommand('mceRemoveNode', false, null);
		 
		// set up variables to contain our input values
		var divider = jQuery('#icon-dialog select#divider').val(); 		 
		 
		var output = '';
		
		// setup the output of our shortcode
		switch(divider) {
		case 'hr':
				output += '[hr]';
		  break;
		  case 'space_20':
		  	output += '[space_20]';
		  break;
		  case 'space_40':
		  	output += '[space_40]';
		  break;
		  case 'space_80':
		  	output += '[space_80]';
		  break;
		  default:
		  output += '[clear]';
	}
			
		tinyMCEPopup.execCommand('mceInsertContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
 
</script>
<style type="text/css" media="screen">.kad-popup {padding: 0 8px; font-size: 0;} #icon-dialog {font-size: 12px;} #icon-dialog label {font-size:14px; display:block; padding:4px;}  #icon-dialog select {display:block; height:28px; width:200px; font-size:12px;} #icon-dialog a#insert {margin-top:10px;}

</style>

</head>
<body class="kad-popup">
	<div id="icon-dialog">
		<form action="/" method="get" accept-charset="utf-8">
			<div>
				<label for="dividers"><?php _e("Choose a Divider", 'virtue-toolkit'); ?></label>
				<select name="divider" id="divider">
                	<option value="hr"><?php _e("Line", 'virtue-toolkit'); ?></option>
                    <option value="space_20"><?php _e("Padding Small", 'virtue-toolkit'); ?></option>
                    <option value="space_40"><?php _e("Padding Medium", 'virtue-toolkit'); ?></option>
                    <option value="space_80"><?php _e("Padding Large", 'virtue-toolkit'); ?></option>
                    <option value="clear"><?php _e("Clear", 'virtue-toolkit'); ?></option>
                 </select>
			</div>
			<div>	
				<a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px; text-align:center"><?php _e("Insert", 'virtue-toolkit'); ?></a>
			</div>
		</form>
	</div>
</body>
</html>