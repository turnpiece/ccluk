<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro;

use Hammer\Helper\Log_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Scan\Model\Result_Item;

class Content_Result extends \Hammer\Base\Behavior {
	/**
	 * @return string
	 */
	public function getTitle() {
		$raw = $this->getRaw();

		return pathinfo( $raw['file'], PATHINFO_BASENAME );
	}

	/**
	 * @return mixed
	 */
	public function getSubtitle() {
		$raw = $this->getRaw();

		return $raw['file'];
	}

	/**
	 * Get this slug, will require for checking ignore status while scan
	 * @return string
	 */
	public function getSlug() {
		$raw = $this->getRaw();

		return $raw['file'];
	}

	/**
	 * @return string|void
	 */
	public function getIssueDetail() {
		return $this->getIssueSummary();
	}

	/**
	 * @return string|void
	 */
	public function getIssueSummary() {
		return __( "Suspicious function found", wp_defender()->domain );
	}

	public function renderIssueContent() {
		$raw = $this->getRaw();
		ob_start();
		?>
        <div class="sui-box issue-content">
            <div class="sui-box-body">
                <p>
					<?php printf( __( "We've identified some code in <strong>%s</strong> that could be a potential security weakness. We recommend you take a look to be sure everything is OK and contact your developer if you need help fixing the issue.
Sometimes these checks are false positives, so if you know the code is harmless you can ignore this warning. Alternately you can choose delete this file, but be sure to perform a backup and double-check the file isn't required by a plugin or theme to run correctly.", wp_defender()->domain ), esc_html( pathinfo( $this->getSubtitle(), PATHINFO_BASENAME ) ) ) ?>
                </p>
                <p>
                    <strong><?php _e( "File Location:" ) ?></strong> <?php echo esc_html( $this->getSubtitle() ) ?>
                </p>
                <p>
                    <strong><?php printf( __( "Found %s issues.", wp_defender()->domain ), count( $raw['meta'] ) ) ?></strong>
                </p>
                <div>
					<?php foreach ( $raw['meta'] as $issue ): ?>
                        <p><a class="nav-issue" data-line="<?php echo esc_attr( $issue['line'] ) ?>"
                              data-offset="<?php echo esc_attr( $issue['offset'] ) ?>"
                              data-col="<?php echo esc_attr( $issue['column'] ) ?>"
                              href="#"><?php echo $issue['text'] ?></a></p>
					<?php endforeach; ?>
                </div>
                <div class="source-code">
                    <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
					<?php _e( "Pulling source file...", wp_defender()->domain ) ?>
                    <form method="post" class="float-l pull-src scan-frm">
                        <input type="hidden" name="action" value="pullSrcFile">
						<?php wp_nonce_field( 'pullSrcFile' ) ?>
                        <input type="hidden" name="id" value="<?php echo $this->getOwner()->id ?>"/>
                    </form>
                </div>
            </div>
            <div class="sui-box-footer">
                <div class="sui-actions-left">
                    <form method="post" class="float-l ignore-item scan-frm">
                        <input type="hidden" name="action" value="ignoreItem">
						<?php wp_nonce_field( 'ignoreItem' ) ?>
                        <input type="hidden" name="id" value="<?php echo $this->getOwner()->id ?>"/>
                        <button type="submit" class="sui-button sui-button-ghost">
                            <i class="sui-icon-eye-hide" aria-hidden="true"></i>
							<?php _e( "Ignore", wp_defender()->domain ) ?></button>
                    </form>
                </div>
                <div class="sui-actions-right">
                    <form method="post" class="scan-frm delete-item float-r">
                        <input type="hidden" name="action" value="deleteItem"/>
                        <input type="hidden" name="id" value="<?php echo $this->getOwner()->id ?>"/>
						<?php wp_nonce_field( 'deleteItem' ) ?>
                        <button type="button" class="sui-button sui-button-red delete-mitem">
                            <i class="sui-icon-trash" aria-hidden="true"></i>
							<?php _e( "Delete", wp_defender()->domain ) ?></button>
                        <div class="confirm-box wd-hide">
                            <span><?php _e( "This will permanently remove the selected file/folder. Are you sure you want to continue?", wp_defender()->domain ) ?></span>
                            <div>
                                <button type="submit" class="sui-button sui-button-red">
									<?php _e( "Yes", wp_defender()->domain ) ?>
                                </button>
                                <button type="button" class="sui-button sui-button-ghost">
									<?php _e( "No", wp_defender()->domain ) ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();
	}

	public function getSrcCode() {
		$raw = $this->getRaw();
		//do a dry check first
		$useOldFunc = true;
		foreach ( $raw['meta'] as $meta ) {
			if ( isset( $meta['offset'] ) ) {
				$useOldFunc = false;
				break;
			}
		}

		if ( $useOldFunc ) {
			return $this->_getSrcCode();
		}

		$content = file_get_contents( $raw['file'] );

		//debug
		$plus = 0;
		foreach ( $raw['meta'] as $meta ) {
			$offset = $meta['offset'];
			//move to new index, cause we have to add the length of <mark></mark>
			$offset  = $offset + $plus;
			$content = substr_replace( $content, '<mark>', $offset, 0 );
			$plus    += strlen( '<mark>' );
			$content = substr_replace( $content, '</mark>', $offset + $meta['length'] + strlen( '<mark>' ), 0 );
			$plus    += strlen( '</mark>' );
		}

		$entities = htmlentities( $content, ENT_QUOTES . ENT_HTML5, 'UTF-8' );
		$entities = str_replace( '&lt;mark&gt;', '<mark>', $entities, $count );
		$entities = str_replace( '&lt;/mark&gt;', '</mark>', $entities, $count );

		return '<pre class="line-numbers inner-sourcecode"><code class="language-php">' . $entities . '</code></pre>';
	}

	/**
	 * @return string
	 */
	public function _getSrcCode() {
		return null;
	}

	public function purge() {
		//remove the file first
		$raw  = $this->getRaw();
		$file = $raw['file'];
		if ( strpos( $file, WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' ) === 0 ) {
			//find the plugin
			$revPath = str_replace( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR, '', $file );
			$pools   = explode( '/', $revPath );
			//the path should be first item in pools
			$path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pools[0];
			$res  = $this->deleteFolder( $path );
			if ( is_wp_error( $res ) ) {
				return $res;
			}
			$this->getOwner()->delete();
		} elseif ( strpos( $file, WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' ) === 0 ) {
			//find the theme
			$revPath = str_replace( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR, '', $file );
			$pools   = explode( '/', $revPath );
			//the path should be first item in pools
			$path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pools[0];
		} else {
			if ( $file == ABSPATH . 'wp-config.php' ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "wp-config.php can't be removed. Please remove the suspicious code manually.", wp_defender()->domain ) );
			}
			$res = unlink( $raw['file'] );
			if ( $res ) {
				$this->getOwner()->delete();
			} else {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
			}
		}

		return true;
	}

	private function deleteFolder( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$it    = new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new \RecursiveIteratorIterator( $it,
			\RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				$res = @rmdir( $file->getRealPath() );
			} else {
				$res = @unlink( $file->getRealPath() );
			}
			if ( $res == false ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
			}
		}
		$res = @rmdir( $dir );
		if ( $res == false ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
		}

		return true;
	}

	/**
	 * @return Result_Item;
	 */
	protected function getOwner() {
		return $this->owner;
	}

	/**
	 * @return array
	 */
	protected function getRaw() {
		return $this->getOwner()->raw;
	}
}