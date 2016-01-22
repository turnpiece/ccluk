<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery');
global $wp_scripts;
?>
<!DOCTYPE html>
<head>
<title><?php _e("Insert Columns", 'virtue-toolkit'); ?></title>
 <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
<base target="_self" />
<?php wp_print_scripts(); ?>
<script type="text/javascript">
 var url = '<?php echo plugins_url(); ?>';
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
		var coutput = jQuery('#icon-dialog select#columnoutput').val(); 		 
		 
		var output = '';
		
		// setup the output of our shortcode
		switch (coutput)
			{
		case '1':
			output = '<img src="'+url+'/virtue-toolkit/images/t.gif" class="columnstart mceItem" title="hcolumns" />';
			if(document.getElementById('2column').checked) {
				  	output += '<div class="col-md-6"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-6 mceItem" title="columnhelper col-md-6" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-6"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-6 mceItem" title="columnhelper col-md-6" />';
					output += '<p>add content here</p>';
					output += '</div> ';
				}else if(document.getElementById('2columnright').checked) {
				  	output += '<div class="col-md-4"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-4 mceItem" title="columnhelper col-md-4" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-8"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-8 mceItem" title="columnhelper col-md-8" />';
					output += '<p>add content here</p>';
					output += '</div> ';
				}else if(document.getElementById('2columnleft').checked) {
				  	output += '<div class="col-md-8"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-8 mceItem" title="columnhelper col-md-8" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-4"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-4 mceItem" title="columnhelper col-md-4" />';
					output += '<p>add content here</p>';
					output += '</div> ';
				}else if(document.getElementById('3column').checked) {
				  	output += '<div class="col-md-4"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-4 mceItem" title="columnhelper col-md-4" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-4"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-4 mceItem" title="columnhelper col-md-4" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-4"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-4 mceItem" title="columnhelper col-md-4" />';
					output += '<p>add content here</p>';
					output += '</div> ';
				}else if(document.getElementById('4column').checked) {
				  	output += '<div class="col-md-3"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-3 mceItem" title="columnhelper col-md-3" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-3"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-3 mceItem" title="columnhelper col-md-3" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-3"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-3 mceItem" title="columnhelper col-md-3" />';
					output += '<p>add content here</p>';
					output += '</div> ';
					output += '<div class="col-md-3"><img src="'+url+'/virtue-toolkit/images/t.gif" class="columnhelper col-md-3 mceItem" title="columnhelper col-md-3" />';
					output += '<p>add content here</p>';
					output += '</div> ';
				}
				output += '<img src="'+url+'/virtue-toolkit/images/t.gif" class="columnend mceItem" title="/hcolumns" />';
				
		break;
		default:
				output = '[columns] ';
				if(document.getElementById('2column').checked) {
					output += '[span6] ';
					output += '<p>add content here</p>';
					output += '[/span6]';
					output += '[span6] ';
					output += '<p>add content here</p>';
					output += '[/span6]';
				}else if(document.getElementById('2columnright').checked) {
					output += '[span4] ';
					output += '<p>add content here</p>';
					output += '[/span4]';
					output += '[span8] ';
					output += '<p>add content here</p>';
					output += '[/span8]';
				}else if(document.getElementById('2columnleft').checked) {
					output += '[span8] ';
					output += '<p>add content here</p>';
					output += '[/span8]';
					output += '[span4] ';
					output += '<p>add content here</p>';
					output += '[/span4]';
				}else if(document.getElementById('3column').checked) {
					output += '[span4] ';
					output += '<p>add content here</p>';
					output += '[/span4]';
					output += '[span4] ';
					output += '<p>add content here</p>';
					output += '[/span4]';
					output += '[span4] ';
					output += '<p>add content here</p>';
					output += '[/span4]';
				}else if(document.getElementById('4column').checked) {
					output += '[span3] ';
					output += '<p>add content here</p>';
					output += '[/span3]';
					output += '[span3] ';
					output += '<p>add content here</p>';
					output += '[/span3]';
					output += '[span3] ';
					output += '<p>add content here</p>';
					output += '[/span3]';
					output += '[span3] ';
					output += '<p>add content here</p>';
					output += '[/span3]';
				}

				output += '[/columns]';
		}
			
		tinyMCEPopup.execCommand('mceReplaceContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
 
</script>
<style type="text/css" media="screen"> .kad-popup {padding: 0 8px; font-size: 0;} #icon-dialog {font-size: 12px;} #icon-dialog label {font-size:12px; line-height:24px; width:150px; display:inline-block; text-align:right;} #icon-dialog label.imglabel {width: auto; text-align: left; padding-left: 10px;} #icon-dialog a#insert {margin-top:2px;} #icon-dialog p {font-size:12px;} #icon-dialog .option-row {padding-bottom:6px; border-bottom: solid 1px #d7d7d7; margin-bottom:8px;} #icon-dialog select {width:159px; height:24px;} </style>

</head>
<body class="kad-popup">
	<div id="icon-dialog">
		<form action="/" method="get" id="columnform" accept-charset="utf-8">
        	<div>
            <p>Choose a Column Layout:</p>
            </div>
            <div class="option-row">
				<label for="column-option">Choose Column Output:</label>
				<select name="column-one" id="columnoutput">
					<option value="2">Shortcodes</option>
                	<option value="1">Visual Aid</option>
                    </select>
			</div>
			<div class="option-row">
				<label for="2column">Two Column</label>
				<input type="radio" name="column" value="2column" id="2column">
				<label class="imglabel" for="2column"><img src="<?php echo get_template_directory_uri()?>/assets/img/twocolumn.jpg" /></label>
				</div>
			<div class="option-row">
				<label for="2columnright">Two Column Offset Right</label>
				<input type="radio" name="column" value="2columnright"id="2columnright">
				<label class="imglabel" for="2columnright"><img src="<?php echo get_template_directory_uri()?>/assets/img/twocolumnright.jpg" /></label>
			</div>
			<div class="option-row">
				<label for="2columnleft">Two Column Offset Left</label>
				<input type="radio" name="column" value="2columnleft"id="2columnleft">
				<label class="imglabel" for="2columnleft"><img src="<?php echo get_template_directory_uri()?>/assets/img/twocolumnleft.jpg" /></label>
			</div>
			<div class="option-row">
				<label for="3column">Three Column</label>
				<input type="radio" name="column" value="3column"id="3column">
				<label class="imglabel" for="3column"><img src="<?php echo get_template_directory_uri()?>/assets/img/threecolumn.jpg" /></label>
			</div>
			<div class="option-row">
				<label for="4column">Four Column</label>
				<input type="radio" name="column" value="4column"id="4column">
				<label class="imglabel" for="4column"><img src="<?php echo get_template_directory_uri()?>/assets/img/fourcolumn.jpg" /></label>
			</div>
			<div>	
				<a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px; text-align:center">Insert</a>
			</div>
		</form>
	</div>
</body>
</html>