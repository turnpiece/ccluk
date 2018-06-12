<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Result
 *
 * Abstract class for results
 *
 * @since 1.1
 */

abstract class Forminator_Result {

  /*
	 * Entry id
	 *
	 * @var integer
	 */
	protected $entry_id = 0;
	protected $post_type = 'quizzes';

  public function __construct() {

    add_filter( 'init', array( $this, 'add_rewrite_rules' ) );
    add_filter( 'admin_init', array( $this, 'flush_rewrites' ) );
    add_filter( 'wp_head', array( $this, 'load_results_page' ), 99 );

	}

	public function get_post_type() {

		return $this->post_type;

	}

	public function set_post_type( $string ) {

		$this->post_type = $string;

		return $string;

	}

	public function get_description() {

		return get_the_excerpt();

	}

  public function load_results_page( $template ) {

    $this->entry_id = get_query_var( 'entries', 0 );

		$description = $this->get_description();

      if (null !== $description):
				$featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

      ?>
      <meta property="og:url" content="<?php echo esc_html( $this->build_permalink() ); ?>"/>
      <meta property="og:title" content="<?php single_post_title( '' ); ?>" />
      <meta property="og:description" content="<?php echo esc_html( $description ); ?>" />
      <meta property="og:type" content="article" />
      <?php
			if ( false !== $featured_img_url) {
				?>
				<meta property="og:image" content="<?php echo esc_html( $featured_img_url ); ?>" />
				<?php
			}
    endif;

  	return $template;
  }

  public function build_permalink() {

    return get_the_permalink() . "entries/" . $this->entry_id . "/";

  }

  public function add_rewrite_rules() {

    add_rewrite_tag( '%entries%', '([^&]+)' );
    add_rewrite_rule( '(.?.+?)/entries(/(.*))?/?$', 'index.php?pagename=$matches[1]&entries=$matches[3]', 'top' );

  }

  public function flush_rewrites() {
  	flush_rewrite_rules();
  }

}