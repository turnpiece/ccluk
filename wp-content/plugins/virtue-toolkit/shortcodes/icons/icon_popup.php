<?php
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery');
global $wp_scripts;
?>
<!DOCTYPE html>
<head>
<title><?php _e("Insert Icon", 'virtue-toolkit'); ?></title>
 <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
<base target="_self" />
<?php wp_print_scripts(); ?>
<script language="javascript" type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/lib/icons/js/icon-select.js"></script>
<link href="<?php echo get_template_directory_uri() ?>/lib/icons/css/icon-select.css" rel="stylesheet"/>
<link href="<?php echo get_template_directory_uri() ?>/assets/css/icons.css" rel="stylesheet"/>
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
		var icon = jQuery('#icon-dialog select#icon-icon').val();
		var size = jQuery('#icon-dialog select#icon-size').val();
		var color = jQuery('#icon-dialog select#icon-color').val();
		var colorhex = jQuery('#icon-dialog input#icon-color-hex').val();
		var float = jQuery('#icon-dialog select#icon-align').val();		 		 
		 
		var output = '';
		
		// setup the output of our shortcode
		output = '[icon ';
			output += 'icon=' + icon + ' ';
			output += 'size=' + size + ' ';
			if(colorhex)
				output += ' color=' + colorhex + ' ';
				else {
				output += 'color=' + color + ' ';
				}
			if(float == 'none')
				output += '';
				else {
			output += 'float=' + float;
				}
			
		// check to see if the TEXT field is blank
			output += ']';
			
		tinyMCEPopup.execCommand('mceInsertContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
 
</script>
<?php $icons = array( 'icon-glass', 'icon-music', 'icon-search', 'icon-envelope-alt', 'icon-heart', 'icon-star', 'icon-star-empty', 'icon-user', 'icon-film', 'icon-th-large', 'icon-th', 'icon-th-list', 'icon-ok', 'icon-remove', 'icon-zoom-in', 'icon-zoom-out', 'icon-power-off', 'icon-off', 'icon-signal', 'icon-gear', 'icon-cog', 'icon-trash', 'icon-home', 'icon-file-alt', 'icon-time', 'icon-road', 'icon-download-alt', 'icon-download', 'icon-upload', 'icon-inbox', 'icon-play-circle', 'icon-rotate-right', 'icon-repeat', 'icon-refresh', 'icon-list-alt', 'icon-lock', 'icon-flag', 'icon-headphones', 'icon-volume-off', 'icon-volume-down', 'icon-volume-up', 'icon-qrcode', 'icon-barcode', 'icon-tag', 'icon-tags', 'icon-book', 'icon-bookmark', 'icon-print', 'icon-camera', 'icon-font', 'icon-bold', 'icon-italic', 'icon-text-height', 'icon-text-width', 'icon-align-left', 'icon-align-center', 'icon-align-right', 'icon-align-justify', 'icon-list', 'icon-indent-left', 'icon-indent-right', 'icon-facetime-video', 'icon-picture', 'icon-pencil', 'icon-map-marker', 'icon-adjust', 'icon-tint', 'icon-edit', 'icon-share', 'icon-check', 'icon-move', 'icon-step-backward', 'icon-fast-backward', 'icon-backward', 'icon-play', 'icon-pause', 'icon-stop', 'icon-forward', 'icon-fast-forward', 'icon-step-forward', 'icon-eject', 'icon-chevron-left', 'icon-chevron-right', 'icon-plus-sign', 'icon-minus-sign', 'icon-remove-sign', 'icon-ok-sign', 'icon-question-sign', 'icon-info-sign', 'icon-screenshot', 'icon-remove-circle', 'icon-ok-circle', 'icon-ban-circle', 'icon-arrow-left', 'icon-arrow-right', 'icon-arrow-up', 'icon-arrow-down', 'icon-mail-forward', 'icon-share-alt', 'icon-resize-full', 'icon-resize-small', 'icon-plus', 'icon-minus', 'icon-asterisk', 'icon-exclamation-sign', 'icon-gift', 'icon-leaf', 'icon-fire', 'icon-eye-open', 'icon-eye-close', 'icon-warning-sign', 'icon-plane', 'icon-calendar', 'icon-random', 'icon-comment', 'icon-magnet', 'icon-chevron-up', 'icon-chevron-down', 'icon-retweet', 'icon-shopping-cart', 'icon-folder-close', 'icon-folder-open', 'icon-resize-vertical', 'icon-resize-horizontal', 'icon-bar-chart', 'icon-twitter-sign', 'icon-facebook-sign', 'icon-camera-retro', 'icon-key', 'icon-gears', 'icon-cogs', 'icon-comments', 'icon-thumbs-up-alt', 'icon-thumbs-down-alt', 'icon-star-half', 'icon-heart-empty', 'icon-signout', 'icon-linkedin-sign', 'icon-pushpin', 'icon-external-link', 'icon-signin', 'icon-trophy', 'icon-github-sign', 'icon-upload-alt', 'icon-lemon', 'icon-phone', 'icon-unchecked', 'icon-check-empty', 'icon-bookmark-empty', 'icon-phone-sign', 'icon-twitter', 'icon-facebook', 'icon-github', 'icon-unlock', 'icon-credit-card', 'icon-rss', 'icon-hdd', 'icon-bullhorn', 'icon-bell', 'icon-certificate', 'icon-hand-right', 'icon-hand-left', 'icon-hand-up', 'icon-hand-down', 'icon-circle-arrow-left', 'icon-circle-arrow-right', 'icon-circle-arrow-up', 'icon-circle-arrow-down', 'icon-globe', 'icon-wrench', 'icon-tasks', 'icon-filter', 'icon-briefcase', 'icon-fullscreen', 'icon-group', 'icon-link', 'icon-cloud', 'icon-beaker', 'icon-cut', 'icon-copy', 'icon-paperclip', 'icon-paper-clip', 'icon-save', 'icon-sign-blank', 'icon-reorder', 'icon-list-ul', 'icon-list-ol', 'icon-strikethrough', 'icon-underline', 'icon-table', 'icon-magic', 'icon-truck', 'icon-pinterest', 'icon-pinterest-sign', 'icon-google-plus-sign', 'icon-google-plus', 'icon-money', 'icon-caret-down', 'icon-caret-up', 'icon-caret-left', 'icon-caret-right', 'icon-columns', 'icon-sort', 'icon-sort-down', 'icon-sort-up', 'icon-envelope', 'icon-linkedin', 'icon-rotate-left', 'icon-undo', 'icon-legal', 'icon-dashboard', 'icon-comment-alt', 'icon-comments-alt', 'icon-bolt', 'icon-sitemap', 'icon-umbrella', 'icon-paste', 'icon-lightbulb', 'icon-exchange', 'icon-cloud-download', 'icon-cloud-upload', 'icon-user-md', 'icon-stethoscope', 'icon-suitcase', 'icon-bell-alt', 'icon-coffee', 'icon-food', 'icon-file-text-alt', 'icon-building', 'icon-hospital', 'icon-ambulance', 'icon-medkit', 'icon-fighter-jet', 'icon-beer', 'icon-h-sign', 'icon-plus-sign-alt', 'icon-double-angle-left', 'icon-double-angle-right', 'icon-double-angle-up', 'icon-double-angle-down', 'icon-angle-left', 'icon-angle-right', 'icon-angle-up', 'icon-angle-down', 'icon-desktop', 'icon-laptop', 'icon-tablet', 'icon-mobile-phone', 'icon-circle-blank', 'icon-quote-left', 'icon-quote-right', 'icon-spinner', 'icon-circle', 'icon-mail-reply', 'icon-reply', 'icon-github-alt', 'icon-folder-close-alt', 'icon-folder-open-alt', 'icon-expand-alt', 'icon-collapse-alt', 'icon-smile', 'icon-frown', 'icon-meh', 'icon-gamepad', 'icon-keyboard', 'icon-flag-alt', 'icon-flag-checkered', 'icon-terminal', 'icon-code', 'icon-reply-all', 'icon-mail-reply-all', 'icon-star-half-full', 'icon-star-half-empty', 'icon-location-arrow', 'icon-crop', 'icon-code-fork', 'icon-unlink', 'icon-question', 'icon-info', 'icon-exclamation', 'icon-superscript', 'icon-subscript', 'icon-eraser', 'icon-puzzle-piece', 'icon-microphone', 'icon-microphone-off', 'icon-shield', 'icon-calendar-empty', 'icon-fire-extinguisher', 'icon-rocket', 'icon-maxcdn', 'icon-chevron-sign-left', 'icon-chevron-sign-right', 'icon-chevron-sign-up', 'icon-chevron-sign-down', 'icon-html5', 'icon-css3', 'icon-anchor', 'icon-unlock-alt', 'icon-bullseye', 'icon-ellipsis-horizontal', 'icon-ellipsis-vertical', 'icon-rss-sign', 'icon-play-sign', 'icon-ticket', 'icon-minus-sign-alt', 'icon-check-minus', 'icon-level-up', 'icon-level-down', 'icon-check-sign', 'icon-edit-sign', 'icon-external-link-sign', 'icon-share-sign', 'icon-compass', 'icon-collapse', 'icon-collapse-top', 'icon-expand', 'icon-euro', 'icon-eur', 'icon-gbp', 'icon-dollar', 'icon-usd', 'icon-rupee', 'icon-inr', 'icon-yen', 'icon-jpy', 'icon-renminbi', 'icon-cny', 'icon-won', 'icon-krw', 'icon-bitcoin', 'icon-btc', 'icon-file', 'icon-file-text', 'icon-sort-by-alphabet', 'icon-sort-by-alphabet-alt', 'icon-sort-by-attributes', 'icon-sort-by-attributes-alt', 'icon-sort-by-order', 'icon-sort-by-order-alt', 'icon-thumbs-up', 'icon-thumbs-down', 'icon-youtube-sign', 'icon-youtube', 'icon-xing', 'icon-xing-sign', 'icon-youtube-play', 'icon-dropbox', 'icon-stackexchange', 'icon-instagram', 'icon-flickr', 'icon-adn', 'icon-bitbucket', 'icon-bitbucket-sign', 'icon-tumblr', 'icon-tumblr-sign', 'icon-long-arrow-down', 'icon-long-arrow-up', 'icon-long-arrow-left', 'icon-long-arrow-right', 'icon-apple', 'icon-windows', 'icon-android', 'icon-linux', 'icon-dribbble', 'icon-skype', 'icon-foursquare', 'icon-trello', 'icon-female', 'icon-male', 'icon-gittip', 'icon-sun', 'icon-moon', 'icon-archive', 'icon-bug', 'icon-vk', 'icon-weibo', 'icon-renren'); 

  ?>
<style type="text/css" media="screen"> .kad-popup {padding: 0 8px; font-size: 0;} #icon-dialog {font-size: 12px;} #icon-dialog label {font-size:14px; display:block; padding:4px;}  #icon-dialog label.hex {font-size:12px; line-height:24px; display:inline-block; padding:6px 4px 6px 12px;} .linebreak {margin-bottom:6px; border-bottom: solid 1px #d7d7d7; padding-bottom:6px}  #icon-dialog input#icon-color-hex {display:inline-block; height:24px;} #icon-dialog a#insert {margin-top:15px;}

</style>

</head>
<body class="kad-popup">
	<div id="icon-dialog">
		<form action="/" method="get" accept-charset="utf-8">
        <div class="linebreak">
			<div>
				<label for="button-url">Choose Icon</label>
				<select name="icon-icon" class="kad_icomoon" id="icon-icon">
<?php  foreach ($icons as $icon) {
    echo '<option value="'.$icon. '">'.$icon. '</option>';
  }?>
                    </select>
			</div>
            </div>
            <div class="linebreak">
			<div>
				<label for="icon-size">Icon Size</label>
				<select name="icon-icon" class="kad_icomoon" id="icon-size">
<option value="5px">5px</option>
<option value="6px">6px</option>
<option value="7px">7px</option>
<option value="8px">8px</option>
<option value="9px">9px</option>
<option value="10px">10px</option>
<option value="11px">11px</option>
<option value="12px">12px</option>
<option value="13px">13px</option>
<option value="14px" selected="selected">14px</option>
<option value="15px">15px</option>
<option value="16px">16px</option>
<option value="17px">17px</option>
<option value="18px">18px</option>
<option value="19px">19px</option>
<option value="20px">20px</option>
<option value="21px">21px</option>
<option value="22px">22px</option>
<option value="23px">23px</option>
<option value="24px">24px</option>
<option value="25px">25px</option>
<option value="26px">26px</option>
<option value="27px">27px</option>
<option value="28px">28px</option>
<option value="29px">29px</option>
<option value="30px">30px</option>
<option value="31px">31px</option>
<option value="32px">32px</option>
<option value="33px">33px</option>
<option value="34px">34px</option>
<option value="35px">35px</option>
<option value="36px">36px</option>
<option value="37px">37px</option>
<option value="38px">38px</option>
<option value="39px">39px</option>
<option value="40px">40px</option>
<option value="41px">41px</option>
<option value="42px">42px</option>
<option value="43px">43px</option>
<option value="44px">44px</option>
<option value="45px">45px</option>
<option value="46px">46px</option>
<option value="47px">47px</option>
<option value="48px">48px</option>
<option value="49px">49px</option>
<option value="50px">50px</option>
<option value="51px">51px</option>
<option value="52px">52px</option>
<option value="53px">53px</option>
<option value="54px">54px</option>
<option value="55px">55px</option>
<option value="56px">56px</option>
<option value="57px">57px</option>
<option value="58px">58px</option>
<option value="59px">59px</option>
<option value="60px">60px</option>
<option value="61px">61px</option>
<option value="62px">62px</option>
<option value="63px">63px</option>
<option value="64px">64px</option>
<option value="65px">65px</option>
<option value="66px">66px</option>
<option value="67px">67px</option>
<option value="68px">68px</option>
<option value="69px">69px</option>
<option value="70px">70px</option>
<option value="71px">71px</option>
<option value="72px">72px</option>
<option value="73px">73px</option>
<option value="74px">74px</option>
<option value="75px">75px</option>
<option value="76px">76px</option>
<option value="77px">77px</option>
<option value="78px">78px</option>
<option value="79px">79px</option>
<option value="80px">80px</option>
                 </select>
			</div>
            </div>
            <div class="linebreak">
			<div>
				<label for="icon-color">Icon Color</label>
				<select name="icon-color" class="kad_icomoon" id="icon-color">
                	<option value="#000">Black</option>
                    <option value="#CDCDCD">Light-Gray</option>
					<option value="#999">Gray</option>
                    <option value="#444">Dark-Gray</option>
                    <option value="#CCC">Silver</option>
                    <option value="#FFF">White</option>
                    <option value="#F2F2F2">Off-White</option>
                    <option value="#FF0000">Red</option>
                    <option value="#0000FF">Blue</option>
                    <option value="#008000">Green</option>
                    <option value="#FFFF00">Yellow</option>
                    <option value="#FFA500">Orange</option>
                    <option value="#FF00FF">Pink</option>
                    <option value="#800080">Purple</option>
                    <option value="#8B4513">Brown</option>
                    <option value="#800000">Maroon</option>
                    
                 </select>
			</div>
			<div>
				<label class="hex"for="icon-color-hex">Or Type Hex Color</label>
				<input type="text" name="icon-color-hex" value="" id="icon-color-hex" />
			</div>
            </div>
            <div class="linebreak">
			<div>
				<label for="icon-align">Icon Float</label>
				<select name="icon-align" class="kad_icomoon" id="icon-align">
                	<option value="none">None</option>
					<option value="left">Left</option>
					<option value="right">Right</option>
                 </select>
			</div>
            </div>
			<div>	
				<a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" style="display: block; line-height: 24px; text-align:center">Insert</a>
			</div>
		</form>
	</div>
</body>
</html>