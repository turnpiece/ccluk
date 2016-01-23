<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery');
global $wp_scripts;
?>
<!DOCTYPE html>
<head>
<title><?php _e("Insert YouTube Video", 'virtue-toolkit'); ?></title>
 <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
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
		var video_url = jQuery('#youtube-dialog input#video').val();
		var max_width = jQuery('#youtube-dialog input#maxwidth').val(); 
		var width = jQuery('#youtube-dialog input#width').val(); 
		var height = jQuery('#youtube-dialog input#height').val(); 		 
		 
		var output = '';
			output = '[kad_youtube ';
			output += 'url="' + video_url + '" ';
			if(width) {output += ' width=' + width + ' ';}
			if(height) {output += ' height=' + height + ' ';}
			if(max_width) {output += ' maxwidth=' + max_width + ' '; }
			output += ']';
			
		tinyMCEPopup.execCommand('mceInsertContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
 
</script>
<style type="text/css" media="screen"> #youtube-dialog label {font-size:14px; font-weight: bold; display:block; padding:4px; margin-top: 5px;}  #youtube-dialog input[type=text] {display:block; width: 100%;
padding: 8px;
box-sizing: border-box;
}  #youtube-dialog a#insert {margin-top:10px;} #youtube-dialog span {
 font-size: 10px;
}
body {padding: 0 8px; font-size: 0;}

</style>

</head>
<body>
	<div id="youtube-dialog">
		<form action="/" method="get" accept-charset="utf-8">
			<div>
				<label for="video"><?php _e("YouTube Link", 'virtue-toolkit'); ?></label>
				<input type="text" name="video" name="video" id="video" />
			</div>
			<div>
				<label for="width"><?php _e("Width", 'virtue-toolkit'); ?></label>
				<input type="text" name="width" value="" id="width" />
				<span style="display:inline-block; padding-left:5px;">(*<?php _e("note just use number", 'virtue-toolkit'); ?>)</span>
			</div>
			<div>
				<label for="height"><?php _e("Height", 'virtue-toolkit'); ?></label>
				<input type="text" name="height" value="" id="height" />
				<span style="display:inline-block; padding-left:5px;">(*<?php _e("note just use number", 'virtue-toolkit'); ?>)</span>
			</div>
        	<div>
				<label for="maxwidth"><?php _e("(Optional) Max Width", 'virtue-toolkit'); ?></label>
				<input type="text" name="maxwidth" value="" id="maxwidth" />
				<span style="display:inline-block; padding-left:5px;">(*<?php _e("note just use number", 'virtue-toolkit'); ?>)</span>
			</div>
			<div>	
				<a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px; text-align:center"><?php _e("Insert", "kadencetoolkit"); ?></a>
			</div>
		</form>
	</div>
</body>
</html>