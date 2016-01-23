<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery');
global $wp_scripts;
?>
<!DOCTYPE html>
<head>
<title><?php _e("Insert Accordion or Tabs", 'virtue-toolkit'); ?></title>
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
		var type = jQuery('#icon-dialog select#tabs').val(); 		 
		 
		var output = '';
		
		// setup the output of our shortcode
		if( type == 'accordion')
				output += '[accordion]<br />[pane title="title1" start=open]<br />Put content here<br />[/pane]<br />[pane title="title2"]<br />Put content here<br />[/pane]<br />[pane title="title3"]<br />Copy and paste to create more<br />[/pane]<br />[/accordion]<br />';
		  else {
				output += '[tabs]<br />[tab title="title1" start=open]<br />Put content here<br />[/tab]<br />[tab title="title2"]<br />Put content here<br />[/tab]<br />[tab title="title3"]<br />Copy and paste to create more<br />[/tab]<br />[/tabs]<br />';
		  }
			
		tinyMCEPopup.execCommand('mceInsertContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
 
</script>
<style type="text/css" media="screen"> .kad-popup {padding: 0 8px; font-size: 0;} #icon-dialog {font-size: 12px;} #icon-dialog label {font-size:14px; display:block; padding:4px;}  #icon-dialog select {display:block; height:28px; width:200px; font-size:12px;} #icon-dialog a#insert {margin-top:10px;}

</style>

</head>
<body class="kad-popup">
	<div id="icon-dialog">
		<form action="/" method="get" accept-charset="utf-8">
			<div>
				<label for="tabs"><?php _e("Accordion or Tabs", 'virtue-toolkit'); ?></label>
				<select name="tabs" id="tabs">
                	<option value="accordion"><?php _e("Accordion", 'virtue-toolkit'); ?></option>
					<option value="tabs"><?php _e("Tabs", 'virtue-toolkit'); ?></option>
                 </select>
			</div>
			<div>	
				<a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px; text-align:center"><?php _e("Insert", 'virtue-toolkit'); ?></a>
			</div>
		</form>
	</div>
</body>
</html>