<?php

if ( ! function_exists( 'politics_default_fonts' ) ) :
/**
 * Register Google fonts
 */
    function politics_default_fonts() {
        $fonts_url = '';
        $fonts     = array();
        $subsets   = 'latin,latin-ext';
        /* translators: If there are characters in your language that are not supported by Lora, translate this to 'off'. Do not translate into your own language. */
        if ( 'off' !== _x( 'on', 'Lora font: on or off', 'politics' ) ) {
            $fonts[] = 'Lora:400,700,400italic,700italic';
        }
        /* translators: If there are characters in your language that are not supported by Open San, translate this to 'off'. Do not translate into your own language. */
        if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'politics' ) ) {
            $fonts[] = 'Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800';
        }
        /* translators: To add an additional character subset specific to your language, translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language. */
        $subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'politics' );
        if ( 'cyrillic' == $subset ) {
            $subsets .= ',cyrillic,cyrillic-ext';
        } elseif ( 'greek' == $subset ) {
            $subsets .= ',greek,greek-ext';
        } elseif ( 'devanagari' == $subset ) {
            $subsets .= ',devanagari';
        } elseif ( 'vietnamese' == $subset ) {
            $subsets .= ',vietnamese';
        }
        if ( $fonts ) {
            $fonts_url = add_query_arg( array(
                'family' => urlencode( implode( '|', $fonts ) ),
                'subset' => urlencode( $subsets ),
            ), '//fonts.googleapis.com/css' );
        }
        return $fonts_url;
    }

endif;

if ( ! function_exists( 'politics_add_editor_font_styles' ) ) :
  /**
   * Register Google fonts for the post editor
   */
  function politics_add_editor_font_styles() {
      $font_url = str_replace( ',', '%2C', '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' );
      add_editor_style( $font_url );
  }
  add_action( 'after_setup_theme', 'politics_add_editor_font_styles' );

endif;
