(function($){
  $(document).ready(function(){
    'use strict';

    $('.insert_shortcode').on('click', function() {

      // Close the thickbox modal window when a shortcode button is clicked
      self.parent.tb_remove();

      // Prepare shortcode variable
      var shortcode = '';

      // Assign example text variable
      var exampleText = 'Example Text';
      exampleText = rescueTranslate.exampleText;

      // Get the id of the clicked element and load the correct shortcode
      switch(this.id){

        // Columns
        case 'rescue_half':
          shortcode = '[rescue_column size="one-half" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_third':
          shortcode = '[rescue_column size="one-third" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_fourth':
          shortcode = '[rescue_column size="one-fourth" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_fifth':
          shortcode = '[rescue_column size="one-fifth" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_sixth':
          shortcode = '[rescue_column size="one-sixth" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_twothird':
          shortcode = '[rescue_column size="two-third" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_threefourth':
          shortcode = '[rescue_column size="three-fourth" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_twofifth':
          shortcode = '[rescue_column size="two-fifth" position="first"]' + exampleText + '[/rescue_column]';
          break;

        case 'rescue_threefifth':
          shortcode = '[rescue_column size="three-fifth" position="first"]' + exampleText + '[/rescue_column]';
          break;

        // Elements
        case 'rescue_button':
          shortcode = '[rescue_button colorhex="#2ecc71" url="https://rescuethemes.com" title="Visit Site" target="blank" class="left" border_radius="3px"]' + exampleText + '[/rescue_button]';
          break;

        case 'rescue_icon':
          shortcode = '[icon type="cloud" size="3x" pull="left" color="#cccccc"]';
          break;

        case 'rescue_map':
          shortcode = '[rescue_googlemap title="Rescue Themes Offices" location="5046 S Greenwood Ave, Chicago, IL 60615" zoom="14" height=250 key=]';
          break;

        case 'rescue_tabbed':
          shortcode = '[rescue_tabgroup]<br />[rescue_tab title="First Tab"]<br />First tab content<br />[/rescue_tab]<br />[rescue_tab title="Second Tab"]<br />Second Tab Content.<br />[/rescue_tab]<br />[/rescue_tabgroup]';
          break;

        case 'rescue_toggle':
          shortcode = '[rescue_toggle title="This Is Your Toggle Title"]' + exampleText + '[/rescue_toggle]';
          break;

        case 'rescue_progress':
          shortcode = '[rescue_progressbar title="Example" percentage="75" color="#f1c40f"]';
          break;

        case 'rescue_spacing':
          shortcode = '[rescue_spacing size="40px"]';
          break;

        case 'rescue_clear':
          shortcode = '[rescue_clear_floats]';
          break;

        // Boxes
        case 'rescue_box_blue':
          shortcode = '[rescue_box color="blue" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_box_gray':
          shortcode = '[rescue_box color="gray" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_box_green':
          shortcode = '[rescue_box color="green" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_box_red':
          shortcode = '[rescue_box color="red" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_box_yellow':
          shortcode = '[rescue_box color="yellow" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        // Highlights
        case 'rescue_highlight_blue':
          shortcode = '[rescue_box color="blue" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_highlight_gray':
          shortcode = '[rescue_box color="gray" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_highlight_green':
          shortcode = '[rescue_box color="green" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_highlight_red':
          shortcode = '[rescue_box color="red" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        case 'rescue_highlight_yellow':
          shortcode = '[rescue_box color="yellow" text_align="left" width="100%" float="none"]' + exampleText + '[/rescue_box]';
          break;

        // Animations
        case 'rescue_animate-slideInDown':
          shortcode = '[rescue_animate type="slideInDown" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-slideInLeft':
          shortcode = '[rescue_animate type="slideInLeft" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-slideInRight':
          shortcode = '[rescue_animate type="slideInRight" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-fadeIn':
          shortcode = '[rescue_animate type="fadeIn" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-fadeInLeft':
          shortcode = '[rescue_animate type="fadeInLeft" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-fadeInRight':
          shortcode = '[rescue_animate type="fadeInRight" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-fadeInUp':
          shortcode = '[rescue_animate type="fadeInUp" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-fadeInDown':
          shortcode = '[rescue_animate type="fadeInDown" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-bounceIn':
          shortcode = '[rescue_animate type="bounceIn" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-bounceInLeft':
          shortcode = '[rescue_animate type="bounceInLeft" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-bounceInRight':
          shortcode = '[rescue_animate type="bounceInRight" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-bounceInUp':
          shortcode = '[rescue_animate type="bounceInUp" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;

        case 'rescue_animate-bounceInDown':
          shortcode = '[rescue_animate type="bounceInDown" duration="2s" delay="0s" iteration="1"]' + exampleText + '[/rescue_animate]';
          break;
      }

      // Check if visual editor is active or not
      var tinyMCEstatus = typeof(tinyMCE) !== "undefined" && tinyMCE.activeEditor !== null ? true : false;

      // Append shortcode to content for both Visual and Text mode
      var wpEditor = $('textarea.wp-editor-area');
      if (tinyMCEstatus) {
        tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
        return false;
      } else {
        wpEditor.append(shortcode);
      }

    });

  });

})(this.jQuery);
