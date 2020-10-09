<?php

namespace WP_Defender\Component;

use Faker\Factory;
use WP_Defender\Module\Hardener\Component\Change_Admin_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Component\Scanning;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Setting\Component\Backup_Settings;
use function WP_CLI\Utils\format_items;

class Cli {

	/**
	 *
	 * This is a helper for scan module
	 * #Options
	 * <command>
	 * : Value can be run - Perform a scan, or (un)ignore|delete|resolve to do the relevant task
	 *
	 * [--type=<type>]
	 * : Default is all, or core|plugins|content
	 *
	 * @param $args
	 * @param $options
	 *
	 * @throws \WP_CLI\ExitException
	 */
	public function scan( $args, $options ) {
		if ( empty( $args ) ) {
			\WP_CLI::error( 'Invalid command' );
		}
		list( $command ) = $args;
		switch ( $command ) {
			case 'run':
				$this->scan_all();
				break;
			default:
				$commands = [
					'ignore',
					'unignore',
					'resolve',
					'delete'
				];
				if ( in_array( $command, $commands ) ) {
					\WP_CLI::confirm( 'This can cause your site get fatal error and can\'t restore back unless you have a backup, are you sure to continue?', $options );
					$this->scan_task( $command, $options );
				} else {
					\WP_CLI::error( sprintf( 'Unknown command %s', $command ) );
				}

		}
	}

	private function scan_task( $task, $options ) {
		$type   = isset( $options['type'] ) ? $options['type'] : null;
		$active = Scan_Api::getActiveScan();
		if ( is_object( $active ) ) {
			return \WP_CLI::error( "A scan is running, you need to wait till it complete to continue" );
		}
		$model = Scan_Api::getLastScan();
		switch ( $task ) {
			case 'ignore':
				$items = $model->getItems( 0, Result_Item::STATUS_ISSUE, $type );
				foreach ( $items as $item ) {
					$item->ignore();
				}
				\WP_CLI::log( sprintf( 'Ignored %s items', count( $items ) ) );
				break;
			case 'unignore':
				$items = $model->getItems( 0, Result_Item::STATUS_IGNORED, $type );
				foreach ( $items as $item ) {
					$item->unignore();
				}
				\WP_CLI::log( sprintf( 'Unignored %s items', count( $items ) ) );
				break;
			case 'resolve':
				$items    = $model->getItems( 0, Result_Item::STATUS_ISSUE, $type );
				$resolved = [];
				foreach ( $items as $item ) {
					if ( $item->type == 'core' ) {
						\WP_CLI::log( sprintf( 'Reverting %s to original', $item->raw['file'] ) );
						$ret = $item->resolve();
						if ( ! is_wp_error( $ret ) ) {
							$resolved[] = $item;
						} else {
							return \WP_CLI::error( $ret->get_error_message() );
						}
					} elseif ( $item->type == 'content' ) {
						//if this is content, we will try to delete them
						$whitelist  = [
							//wordfence waf
							ABSPATH . '/wordfence-waf.php',
							//any files inside plugins, if delete can cause fatal error
							WP_CONTENT_DIR . '/plugins/',
							//any files inside themes
							WP_CONTENT_DIR . '/themes/'
						];
						$path       = $item->raw['file'];
						$can_delete = true;
						$current    = '';
						foreach ( $whitelist as $value ) {
							$current = $value;
							if ( strpos( $value, $path ) > 0 ) {
								//ignore this
								$can_delete = false;
								break;
							}
						}
						if ( $can_delete == false ) {
							\WP_CLI::log( sprintf( "Ignore file %s as it is in %s", $path, $current ) );
						} else {
							if ( @unlink( $path ) ) {
								\WP_CLI::log( sprintf( 'Delete file %s', $path ) );
								$item->markAsResolved();
								$resolved[] = $item;
							} else {
								return \WP_CLI::error( sprintf( "Can't remove file %s", $path ) );
							}
						}
					}
				}
				\WP_CLI::log( sprintf( 'Resolved %s items', count( $resolved ) ) );
				break;
			case 'delete':
				break;
		}
	}

	/**
	 * Perform a scan
	 */
	private function scan_all() {
		echo 'Check if there is a scan ongoing...' . PHP_EOL;
		$model = Scan_Api::getActiveScan();
		$start = microtime( true );
		if ( ! is_object( $model ) ) {
			echo 'No active scan, create one now...' . PHP_EOL;
			Scan_Api::createScan();
		} else {
			echo 'Found active scan, process...' . PHP_EOL;
		}
		//echo sprintf( 'Total core files: %d' . PHP_EOL, count( Scan_Api::getCoreFiles() ) );
		//echo sprintf( 'Total content files: %d' . PHP_EOL, count( Scan_Api::getContentFiles() ) );
		echo '=============================================' . PHP_EOL;
		$is_done = false;
		while ( $is_done == false ) {
			$memory = ( memory_get_peak_usage( true ) / 1024 / 1024 );
			echo 'Memory: ' . $memory . ' MB' . PHP_EOL;
			if ( $memory > 256 ) {
				break;
			}
			$scanning = new Scanning();
			$scanning->releaseLock();
			$is_done  = $scanning->run();
			$progress = $scanning->getScanProgress();
			//$is_done  = Scan_Api::processActiveScan();
			//$progress = Scan_Api::getScanProgress();
			echo 'Scanning at ' . $progress . PHP_EOL;
			gc_collect_cycles();
		}
		if ( $is_done ) {
			$model  = Scan_Api::getLastScan();
			$finish = microtime( true ) - $start;
			//\WP_CLI::log( sprintf( 'Found %s issues. Please go to %s for more info.' . PHP_EOL, count( $model->getItems() ), network_admin_url( 'admin.php?page=wdf-scan&view=issues' ) ) );
			$results = $model->getItemsAsJson();
			if ( count( $results ) ) {
				format_items( 'table', $results, [ 'type', 'short_desc', 'full_path' ] );
			} else {
				\WP_CLI::log( 'All good!' );
			}
			\WP_CLI::log( 'Scan take ' . round( $finish, 2 ) . 's to process.' );
			\WP_CLI::success( 'Scan done.' );
		} else {
			\WP_CLI::log( 'Run the command wp defender scan run again to continue process the scan.' );
		}
	}

	public function tweaks( $task, $options ) {
		$task  = array_shift( $task );
		$model = Settings::instance();
		switch ( $task ) {
			case 'resolve':
				$tweaks = $model->getDefinedRules( true );
				foreach ( $tweaks as $tweak ) {
					if ( $tweak->check() == false ) {
						$slug = $tweak::$slug;
						if ( in_array( $slug, [
							'protect-information',
							'prevent-php-executed'
						] ) ) {
							continue;
						}
						\WP_CLI::log( sprintf( 'Resolving %s', $tweak->getTitle() ) );
						$service = $tweak->getService();
						switch ( $slug ) {
							case 'replace-admin-username':
								fwrite( STDOUT, 'Please enter new admin username:' );
								$username = strtolower( trim( fgets( STDIN ) ) );
								$service->setUsername( $username );
								break;
							case 'db-prefix':
								fwrite( STDOUT, 'Please enter new db prefix:' );
								$prefix              = strtolower( trim( fgets( STDIN ) ) );
								$service->new_prefix = $prefix;
								break;
							default:
								break;
						}
						$ret = $service->process();
						if ( is_wp_error( $ret ) ) {
							\WP_CLI::error( $ret->get_error_message() );

							return;
						}
						\WP_CLI::success( 'Done' );
					}
				}
				break;
			case 'ignore':
				$tweaks = $model->getIssues();
				foreach ( $tweaks as $tweak ) {
					$tweak->ignore();
					\WP_CLI::success( sprintf( 'Ignored %s', $tweak->getTitle() ) );
				}
				break;
			case 'revert':
				$tweaks = $model->getFixed();
				foreach ( $tweaks as $tweak ) {
					$tweak->revert();
					\WP_CLI::success( sprintf( 'Reverted %s', $tweak->getTitle() ) );
				}
				break;
			case 'unignore':
				$tweaks = $model->getIgnore();
				foreach ( $tweaks as $tweak ) {
					$tweak->restore();
					\WP_CLI::success( sprintf( 'Restore %s', $tweak->getTitle() ) );
				}
				break;
			default:
				\WP_CLI::error( 'Invalid command, only resolve|ignore allow' );
				break;
		}
	}

	/**
	 * This will generate randomly settings, use to check upgrade scenario
	 */
	public function seeding() {
		//reset all the data before generate
		$this->reset();
		$faker = Factory::create();
		//start with tweaks
		$tweaks = Settings::instance();

	}

	/**
	 * Reset all settings
	 */
	public function reset() {
		Backup_Settings::resetSettings();
	}
}