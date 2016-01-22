<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery');
global $wp_scripts;
?>
<!DOCTYPE html>
<head>
<title><?php _e("Insert Styled Quote", 'virtue-toolkit'); ?></title>
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
		var quote = jQuery('#icon-dialog select#quote').val(); 
		var align = jQuery('#icon-dialog select#align').val(); 		 
		 
		var output = '';
		if(quote == 'pull')
			output += '[pullquote align=' + align + ']<p>Sample Text</p>[/pullquote]<br />';
			else {
			output += '[blockquote align=' + align + ']<p>Sample Text </p>[/blockquote]<br />';
			}
			
		tinyMCEPopup.execCommand('mceInsertContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
 
</script>
<style type="text/css" media="screen"> .kad-popup {padding: 0 8px; font-size: 0;} #icon-dialog {font-size: 12px;} #icon-dialog label {font-size:14px; display:inline-block; padding:4px;} #icon-dialog select {display:block; height:28px; width:200px; font-size:12px;} .linebreak {margin-bottom:6px; border-bottom: solid 1px #d7d7d7; padding-bottom:6px} #icon-dialog a#insert {margin-top:10px;}

</style>

</head>
<body class="kad-popup">
	<div id="icon-dialog">
		<form action="/" method="get" accept-charset="utf-8">
        <div class="linebreak">
        	<div>
				<label for="text"><?php _e("Style", 'virtue-toolkit'); ?></label>
				<select name="quote" id="quote">
                	<option value="pull"><?php _e("Pull-Quote", 'virtue-toolkit'); ?></option>
					<option value="block"><?php _e("Block-Quote", 'virtue-toolkit'); ?></option>
                 </select>
			</div>
            </div>
            <div class="linebreak">
			<div>
				<label for="text"><?php _e("Align", 'virtue-toolkit'); ?></label>
				<select name="quote" id="align">
                	<option value="center"><?php _e("Center", 'virtue-toolkit'); ?></option>
					<option value="left"><?php _e("Left", 'virtue-toolkit'); ?></option>
                    <option value="right"><?php _e("Right", 'virtue-toolkit'); ?></option>
                 </select>
			</div>
            </div>
			<div>	
				<a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px; text-align:center"><?php _e("Insert", 'virtue-toolkit'); ?></a>
			</div>
		</form>
	</div>
</body>
</html>