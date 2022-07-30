<?php

/**
 * Shipper Package Installer
 * Version: 1.2.12
 * Build: 2022-04-04
 *
 * Copyright 2009-2022 Incsub (http://incsub.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



// Source: lib/installer/src/preamble.php

/**
 * Preamble file
 *
 * Sets general PHP setup and installer password/salt.
 * phpcs:ignoreFile as it's out side of WP scope.
 *
 * @package shipper-installer
 */

define( 'SHINST_INSTALLER_PASSWORD', '{{SHIPPER_INSTALLER_PASSWORD}}' );

define( 'SHINST_SALT', '{{SHIPPER_INSTALLER_SALT}}' );

if ( function_exists( 'ini_set' ) ) {
	@ini_set( 'display_errors', 'on' );
	@ini_set( 'log_errors', 'on' );
	@ini_set( 'error_log', dirname( __FILE__ ) . '/shipper-working/installer.log' );
}

if ( function_exists( 'set_time_limit' ) ) {
	set_time_limit( 0 );
}

if ( function_exists( 'session_start' ) && @session_start() ) {
	define( 'SHINST_USE_SESSIONS', true );
}



// Source: lib/installer/build/dependencies.php

// Source: lib/installer/src/lib/class-controller.php

/**
 * Installer controller abstraction
 *
 * @package shipper-installer
 */

abstract class Shinst_Controller {

	/**
	 * Dispatches controller handling
	 *
	 * @return bool True if actions were dispatched, false otherwise.
	 */
	abstract public function run();

	/**
	 * Handles invalid request sent to controller
	 *
	 * @param string $rq Request string (raw).
	 * @param int    $status Status code.
	 */
	public function handle_invalid_request( $rq, $status = 500 ) {
		http_response_code( $status );
		throw new Shinst_Exception(
			sprintf(
				'We don\'t know how to handle your request: [%s]',
				preg_replace( '/[^-_a-z0-9]/i', '', $rq )
			)
		);
	}
}



// Source: lib/installer/src/lib/class-view.php

/**
 * Installer view class abstraction
 *
 * @package shipper-installer
 */

abstract class Shinst_View {

	abstract public function print_markup();

	/**
	 * Holds child components
	 *
	 * @var array
	 */
	private $_components = array();

	/**
	 * Holds context-dependent text contents
	 *
	 * @var string
	 */
	private $_title;

	/**
	 * Adds a component to child components queue
	 *
	 * @param object Shinst_View instance.
	 *
	 * @return object Shinst_View instance (self).
	 */
	public function add_component( Shinst_View $c ) {
		$this->_components[] = $c;
		return $this;
	}

	/**
	 * Gets a list of registered child components
	 *
	 * @return array
	 */
	public function get_components() {
		return (array) $this->_components;
	}

	/**
	 * Sets text contents
	 *
	 * @param string $str Contents to set.
	 *
	 * @return object Shinst_View instance (self)
	 */
	public function set_title( $str ) {
		$this->_title = $str;
		return $this;
	}

	/**
	 * Gets text contents
	 *
	 * @return string
	 */
	public function get_title() {
		return (string) $this->_title;
	}

	/**
	 * Prints all child component styles
	 *
	 * To be overridden in instances if needed, to include own styles
	 */
	public function print_scripts() {
		$scripts = array();
		foreach ( $this->get_components() as $c ) {
			ob_start();
			$c->print_scripts();
			$scripts[] = ob_get_clean();
		}
		echo join( "\n", array_unique( $scripts ) );
	}

	/**
	 * Prints all child component scripts
	 *
	 * To be overridden in instances if needed, to include own scripts
	 */
	public function print_styles() {
		$styles = '';
		foreach ( $this->get_components() as $c ) {
			ob_start();
			$c->print_styles();
			$styles .= ob_get_clean();
		}
		$styles = explode( '}', $styles );
		echo join( '}', array_unique( $styles ) );
	}
}



// Source: lib/installer/src/lib/controller/ajax/class-cleanup.php

/**
 * Installer cleanup AJAX controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax_Cleanup extends Shinst_Controller_Ajax {

	public function run() {
		$root_path = Shinst_Model_Fs_Path::get_working_dir( false );
		if ( is_dir( $root_path ) ) {
			Shinst_Model_Fs_Path::rmdir_r( $root_path, false );
		}

		return $this->cleanup_self();
	}

	/**
	 * Clears the working directory and responds with markup and status
	 */
	public function cleanup_work_dir() {
		Shinst_Model_Fs_Path::rmdir_r(
			Shinst_Model_Fs_Path::get_working_dir(),
			''
		);
		@rmdir( Shinst_Model_Fs_Path::get_working_dir( false ) );
		$progress = new Shinst_View_Cmp_Progress(
			'Deleting install.php',
			50
		);
		$main     = new Shinst_View_Cmp_Main();
		$main
			->add_component( new Shinst_View_Cmp_Title( 'Running Cleanup' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'We’re running the cleanup on your new website, and you’ll be redirected to the admin login screen after that. This will only take a couple of seconds.' ) )
			->add_component( $progress );

		ob_start();
		$main->print_markup();
		echo '<style>';
		$main->print_styles();
		echo '</style>';
		$out = ob_get_clean();

		return $this->send_success(
			array(
				'is_done' => false,
				'markup'  => $out,
			)
		);
	}

	/**
	 * Clears log file, archive and installer and responds with all done status
	 */
	public function cleanup_self() {
		try {
			$archive = Shinst_Model_Fs_Archive::get_archive();
			unlink( $archive );
		} catch ( Exception $e ) {
			Shinst_Model_Log::write( $e->getMessage() );
		}
		@rmdir( Shinst_Model_Fs_Path::get_working_dir( false ) );
		unlink( __FILE__ );
		unlink( shinst_trailingslash( Shinst_Model_Fs_Path::get_working_dir( false ) ) . 'installer.log' );

		return $this->send_all_done();
	}

	/**
	 * Responds with all done markup and status
	 */
	public function send_all_done() {
		$main = new Shinst_View_Cmp_Main();
		$main
			->add_component( new Shinst_View_Cmp_Title( 'Redirecting…' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'Cleanup on your new website is finished! We are redirecting you to the admin login screen.' ) );

		ob_start();
		$main->print_markup();
		echo '<style>';
		$main->print_styles();
		echo '</style>';
		$out = ob_get_clean();

		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$session->clear();
		$session->save();

		return $this->send_success(
			array(
				'is_done' => true,
				'markup'  => $out,
			)
		);
	}
}



// Source: lib/installer/src/lib/controller/ajax/class-connection.php

/**
 * Installer connection controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax_Connection extends Shinst_Controller_Ajax {

	public function run() {
		$error = 'Couldn\'t connect to the database. Please make sure the database credentials you\'re using are correct and try again.';

		$args  = array(
			'name'     => ! empty( $_POST['name'] ) ? $_POST['name'] : '',
			'user'     => ! empty( $_POST['username'] ) ? $_POST['username'] : '',
			'password' => ! empty( $_POST['password'] ) ? $_POST['password'] : '',
			'prefix'   => ! empty( $_POST['prefix'] ) ? $_POST['prefix'] : '',
		);
		$fetch = ! empty( $_POST['fetch'] ) ? $_POST['fetch'] : false;
		$fetch = filter_var( $fetch, FILTER_VALIDATE_BOOLEAN );

		if ( $fetch ) {
			$config           = shinst_read_wpconfig();
			$args['name']     = $config['db_name'];
			$args['user']     = $config['db_user'];
			$args['password'] = $config['db_password'];
			$args['host']     = $config['db_host'];
			$args['port']     = $config['port'];
			$args['prefix']   = ! empty( $args['prefix'] ) ? $args['prefix'] : $config['table_prefix'];
		} else {
			if ( ! empty( $_POST['host'] ) ) {
				$args['host'] = $_POST['host'];
			}
			if ( ! empty( $_POST['port'] ) ) {
				$args['port'] = $_POST['port'];
			}
		}

		if ( empty( $args['prefix'] ) ) {
			$args['prefix'] = Shinst_Model_Manifest::get()->get_value( 'table_prefix' );
		}

		$db = Shinst_Model_Db::create( $args );

		try {
			$db->get_handle();
		} catch ( Shinst_Exception $e ) {
			Shinst_Model_Log::write( $e->getMessage() );

			return $this->send_error( $error );
		}

		$config = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$config->set_value( 'dbhost', $db->get_host() );
		$config->set_value( 'dbport', $db->get_port() );
		$config->set_value( 'dbname', $db->get_name() );
		$config->set_value( 'dbuser', $db->get_user() );
		$config->set_value( 'dbpassword', $db->get_password() );

		$config->set_value( 'table_prefix', $args['prefix'] );
		$config->save();

		if ( Shinst_Model_Env::is_flywheel() ) {
			$table_prefix = shinst_read_wpconfig()['table_prefix'];

			if ( $args['prefix'] !== $table_prefix ) {
				return $this->send_success(
					array(
						'prefix' => $table_prefix,
					)
				);
			}

			return $this->send_success();
		}

		$table_prefix = $this->get_unique_table_prefix( $args );
		if ( $table_prefix !== $args['prefix'] ) {
			return $this->send_success(
				array(
					'prefix' => $table_prefix,
				)
			);
		}

		$this->send_success();
	}

	public function get_unique_table_prefix( $args ) {
		$dbh    = Shinst_Model_Db_Table::create( $args );
		$prefix = $dbh->get_prefix();
		$tables = $dbh->query( "SHOW TABLES LIKE '{$prefix}%'" );
		if ( empty( $tables ) ) {
			return $dbh->get_prefix();
		}

		$base_table = preg_replace(
			'/' . preg_quote( $prefix, '/' ) . '/',
			'',
			$this->get_longest_table_name()
		);

		$prefix = $this->get_randchar() . $this->get_randchar() . $prefix;
		while ( strlen( "{$prefix}{$base_table}" ) < Shinst_Model_Db_Table::MAX_SQL_TABLE_NAME_LENGTH ) {
			$dbh->set_prefix( $prefix );
			$tables = $dbh->query( "SHOW TABLES LIKE '{$dbh->get_prefix()}'" );
			if ( empty( $tables ) ) {
				return $dbh->get_prefix();
			}

			$prefix = $this->get_randchar() . $prefix;
		}

		return $dbh->get_prefix();
	}

	/**
	 * Gets a random character
	 *
	 * @return string
	 */
	public function get_randchar() {
		return shinst_randchar();
	}

	/**
	 * Gets the longest string out of known table names
	 *
	 * @return string
	 */
	public function get_longest_table_name() {
		return Shinst_Model_Package::get_dumped_sql_file_name();
	}
}



// Source: lib/installer/src/lib/controller/ajax/class-deploy.php

/**
 * Installer deployment controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax_Deploy extends Shinst_Controller_Ajax {

	public function run() {
		$endpoint = ! empty( $_POST['endpoint'] ) ? $_POST['endpoint'] : '';
		$endpoint = preg_replace( '/[^a-z]/', '', $endpoint );
		$method   = 'deploy_' . $endpoint;
		if ( ! is_callable( array( $this, $method ) ) ) {
			return $this->send_error( 'Invalid endpoint' );
		}

		return $this->$method();
	}

	/**
	 * Sends status update back to client
	 *
	 * @param int $percentage Current progress, in percents.
	 */
	public function send_update( $percentage ) {
		return $this->send_success(
			array(
				'is_done'    => $percentage >= 100,
				'percentage' => $percentage,
			)
		);
	}

	public function deploy_unpack1() {
		set_time_limit( - 1 );
		Shinst_Model_Fs_Archive::extract_all();
		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_DEPLOY );
		$session->clear();
		$session->save();

		$list = new Shinst_Model_Fs_List(
			Shinst_Model_Package::get_component_dir( Shinst_Model_Package::COMPONENT_FS )
		);
		$list->reset();

		return $this->send_update( 100 );
	}

	public function deploy_unpack() {
		set_time_limit( - 1 );
		$session   = Shinst_Model_Session::get( Shinst_Model_Session::SESS_DEPLOY );
		$flag_path = Shinst_Model_Fs_Path::get_working_dir() . 'extracting';
		if ( ! file_exists( $flag_path ) ) {
			$session->clear();
			$session->save();
			file_put_contents( $flag_path, 1 );
		}
		$offset = $session->get_value( 'unpack_offset' );
		if ( $offset == false ) {
			// unpack the meta first
			$offset = 0;
		}
		$ret = Shinst_Model_Fs_Archive::extract_by_offset( $offset );
		if ( $offset === true ) {
			// this done
			$list = new Shinst_Model_Fs_List(
				Shinst_Model_Package::get_component_dir( Shinst_Model_Package::COMPONENT_FS )
			);
			$list->reset();
			$this->send_update( 100 );
		}
		list( $offset, $percent ) = $ret;
		$session->set_value( 'unpack_offset', $offset );
		$session->save();
		$this->send_update( $percent );
	}

	/**
	 * Analyzes the unpacked files content
	 */
	public function deploy_analyze() {
		$session     = Shinst_Model_Session::get( Shinst_Model_Session::SESS_DEPLOY );
		$total_files = $session->get_value( 'total_files', 0 );
		$list        = new Shinst_Model_Fs_List(
			Shinst_Model_Package::get_component_dir( Shinst_Model_Package::COMPONENT_FS )
		);

		$files   = $list->get_files();
		$is_done = $list->is_done();

		if ( $is_done ) {

			$list->reset();
		}

		$total_files += count( $files );
		$session->set_value( 'total_files', $total_files );
		$session->save();

		if ( $is_done ) {
			$percentage = 100;
			Shinst_Model_Log::write( "Analysis done, [{$total_files}] files total" );
		} else {
			$total      = $list->get_total_steps();
			$total      = $total < 1 ? 1 : $total;
			$current    = $list->get_current_step();
			$percentage = (int) ( ( $current / $total ) * 100 );
			$percentage = $percentage >= 100 ? 99 : $percentage;
			Shinst_Model_Log::write(
				"Analyzing paths: [{$current}] of [{$total}]: {$percentage}%"
			);
		}

		return $this->send_update( $percentage );
	}

	/**
	 * Actually deploys unpacked files
	 */
	public function deploy_files() {
		$session        = Shinst_Model_Session::get( Shinst_Model_Session::SESS_DEPLOY );
		$total_files    = $session->get_value( 'total_files', 0 );
		$deployed_files = $session->get_value( 'deployed_files', 0 );
		$config_files   = $session->get_value( 'config_files', array() );

		$list = new Shinst_Model_Fs_List(
			Shinst_Model_Package::get_component_dir( Shinst_Model_Package::COMPONENT_FS )
		);

		if ( $list->is_done() ) {
			$list->reset();

			return $this->send_update( 100 );
		}

		$files = $list->get_files();

		foreach ( $files as $file ) {
			$source = ! empty( $file['path'] )
				? $file['path']
				: false;
			if ( empty( $source ) ) {
				continue;
			}
			$destination = Shinst_Model_Fs_Path::get_rerooted(
				$source,
				Shinst_Model_Fs_Path::get_root(),
				Shinst_Model_Package::get_component_dir(
					Shinst_Model_Package::COMPONENT_FS
				)
			);
			if ( $source === $destination ) {
				Shinst_Model_Log::write( "File [{$source}]: destination path same" );
				continue;
			}

			// If this is a config file, stow it away now.
			if ( Shinst_Model_Fs_Path::is_config_file( $source ) ) {
				Shinst_Model_Log::write( "Stowing config file [{$source}]" );
				$config_files[ $source ] = $destination;
				continue;
			}

			/**
			 * We'll remove the plugins and theme dir first and deploy files later.
			 * We're doing this cause, we want to make both source and destination site identical.
			 * So source and destination site will have same plugins
			 *
			 * @since 1.1.4
			 */
			if ( ! $session->get_value( 'plugins_deleted', false ) && false !== strpos( $destination, '/wp-content/plugins' ) ) {
				$plugins_dir = strstr( $destination, 'plugins', true ) . 'plugins';
				Shinst_Model_Fs_Path::rmdir_r( $plugins_dir, false );
				$session->set_value( 'plugins_deleted', true );
				$session->save();
			}

			if ( ! $session->get_value( 'themes_deleted', false ) && false !== strpos( $destination, '/wp-content/themes' ) ) {
				$themes_dir = strstr( $destination, 'themes', true ) . 'themes';
				Shinst_Model_Fs_Path::rmdir_r( $themes_dir, false );
				$session->set_value( 'themes_deleted', true );
				$session->save();
			}

			$dir = pathinfo( $destination, PATHINFO_DIRNAME );
			if ( ! is_dir( $dir ) ) {
				// create it
				Shinst_Model_Fs_Path::mkdir_p( $dir );
			}

			if ( ! copy( $source, $destination ) ) {
				Shinst_Model_Log::write(
					sprintf(
						'WARNING: unable to copy staged file %1$s to %2$s',
						$source,
						$destination
					)
				);
			}
		}
		$deployed_files += count( $files );

		Shinst_Model_Log::write(
			"Deployed {$deployed_files} files of {$total_files} total"
		);
		$session->set_value( 'deployed_files', $deployed_files );
		$session->set_value( 'config_files', $config_files );
		$session->save();

		$percentage = $total_files > 0
			? ( $deployed_files / $total_files ) * 100
			: 50;
		if ( 100 === $percentage ) {
			$percentage = $list->is_done() ? 100 : 99;
		}

		return $this->send_update( $percentage );
	}
}



// Source: lib/installer/src/lib/controller/ajax/class-password.php

/**
 * Installer password handling controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax_Password extends Shinst_Controller_Ajax {

	public function run() {
		$password = ! empty( $_POST['password'] )
			? $_POST['password']
			: false;
		if ( empty( $password ) ) {
			throw new Shinst_Exception_Auth(
				'Please, submit a non-empty password'
			);
		}

		$ctrl = new Shinst_Controller_Front();
		if ( $ctrl->get_password() !== $password ) {
			throw new Shinst_Exception_Auth( 'Invalid password' );
		}

		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$session->set_value( 'installer-password', $password );

		if ( ! $session->save() ) {
			throw new Shinst_Exception( 'Error storing session' );
		}

		return $this->send_success();
	}
}



// Source: lib/installer/src/lib/controller/ajax/class-requirements.php

/**
 * Installer requirements controller
 *
 * Performs the requirements checks and responds with status object.
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax_Requirements extends Shinst_Controller_Ajax {

	public function run() {
		$ctrl = new Shinst_Controller_Front();
		if ( ! $ctrl->is_user_allowed() ) {
			throw new Shinst_Exception_Auth(
				'You are not wanted here, please leave'
			);
		}

		$main = new Shinst_View_Cmp_Main();
		$main
			->add_component( new Shinst_View_Cmp_Title( 'Requirements Check' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'We’ve uncovered a few potential issues that may affect this migration. Take a look through them and action what you like. While you can ignore the warnings, you must fix the errors (if any) to continue your migration.' ) )
			->add_component( $this->get_archive_component() )
			->add_component( $this->get_zip_component() )
			->add_component( $this->get_php_component() )
			->add_component( $this->get_basedir_component() )
			->add_component( $this->get_exec_time_component() )

			->add_component( new Shinst_View_Cmp_Button_Recheck() )
			->add_component( new Shinst_View_Cmp_Button_Continue() );

		ob_start();
		$main->print_markup();
		echo '<style>';
		$main->print_styles();
		echo '</style>';
		$out = ob_get_clean();

		return $this->send_success( $out );
	}

	public function get_archive_component() {
		try {
			$archive = Shinst_Model_Fs_Archive::get_archive( true );
		} catch ( Shinst_Exception $e ) {
			$archive = false;
		}
		return ! empty( $archive )
			? new Shinst_View_Cmp_RequirementsItem_Ok(
				sprintf( 'Found archive: %s', basename( $archive ) )
			)
			: new Shinst_View_Cmp_RequirementsItem_Archive();
	}

	public function get_zip_component() {
		return class_exists( 'ZipArchive' )
			? new Shinst_View_Cmp_RequirementsItem_Ok( 'Zip support OK' )
			: new Shinst_View_Cmp_RequirementsItem_Zip();
	}

	public function get_php_component() {
		return version_compare( '5.5', phpversion(), 'lt' )
			? new Shinst_View_Cmp_RequirementsItem_Ok( 'PHP version OK' )
			: new Shinst_View_Cmp_RequirementsItem_Phpversion();
	}

	public function get_basedir_component() {
		$has_basedir = (bool) @ini_get( 'open_basedir' );
		if ( $has_basedir ) {
			$has_basedir = is_writable( Shinst_Model_Log::get_file_path() ) &&
				is_writable( Shinst_Model_Fs_Path::get_working_dir() );
		}
		return ! $has_basedir
			? new Shinst_View_Cmp_RequirementsItem_Ok( 'Open Basedir OK' )
			: new Shinst_View_Cmp_RequirementsItem_Basedir();
	}

	public function get_exec_time_component() {
		$time = (int) @ini_get( 'max_exec_time' );
		return $time === 0 || $time >= 120
			? new Shinst_View_Cmp_RequirementsItem_Ok( 'Max Exec time is OK' )
			: new Shinst_View_Cmp_RequirementsItem_Exectime();
	}
}



// Source: lib/installer/src/lib/controller/ajax/class-update.php

/**
 * Installer data update controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax_Update extends Shinst_Controller_Ajax {

	public function run() {
		$endpoint = preg_replace( '/[^a-z]/', '', $_POST['endpoint'] );
		$method   = 'update_' . $endpoint;
		if ( ! is_callable( array( $this, $method ) ) ) {
			$this->send_error( 'Invalid endpoint' );
		}

		$this->$method();
	}

	public function send_update( $percentage ) {
		return $this->send_success(
			array(
				'is_done'    => $percentage >= 100,
				'percentage' => $percentage,
			)
		);
	}

	/**
	 * Update tables
	 *
	 * @throws \Shinst_Exception_Fs
	 * @throws \Shinst_Exception_Recoverable_Db
	 */
	public function update_tables() {
		$dumped_sql_file = Shinst_Model_Package::get_dumped_sql_file();

		if ( ! file_exists( $dumped_sql_file ) ) {
			Shinst_Model_Log::write( 'Dumped SQL file is not found. Trying once again.' );

			if ( ! Shinst_Model_Fs_Archive::extract_dumped_sqls() ) {
				throw new Shinst_Exception_Fs(
					'Unable to find the tables to import'
				);
			}
		}

		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_DEPLOY );

		$config = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$dbh    = Shinst_Model_Db::create(
			array(
				'name'     => $config->get_value( 'dbname' ),
				'user'     => $config->get_value( 'dbuser' ),
				'password' => $config->get_value( 'dbpassword' ),
				'host'     => $config->get_value( 'dbhost' ),
			)
		);

		$query_string       = "";
		$replacer           = new Shinst_Model_Replacer();
		$serialize_decoder  = new Shinst_Model_Serialize_Decoder();
		$reader             = $this->read_file( $dumped_sql_file );
		$total_line         = $session->get_value( 'total_line', false );

		if ( false === $total_line ) {
			$reader->seek( PHP_INT_MAX );
			$total_line = $reader->key();
			$session->set_value( 'total_line', $total_line );
			$session->save();
			$reader->rewind();
		}

		$limit    = 1000;
		$max_line = $session->get_value( 'max_line', $limit );
		$pointer  = $session->get_value( 'pointer', 0 );

		$reader->seek( $pointer );

		while ( ! $reader->eof() ) {
			$line = $reader->current();
			$reader->next();

			$left_trimmed_line = ltrim( $line );
			$temp_query_string = trim( $query_string );

			if ( 1 === preg_match( "/^#|^\-\-/", $left_trimmed_line ) && empty( $temp_query_string ) ) {
				continue; // skip one-line comments.
			}

			if ( preg_match( "/^\/\*\!/m", $left_trimmed_line ) ) {
				continue;
			}

			$decoded_serialized = $serialize_decoder->transform( $left_trimmed_line );
			$decoded_string     = $replacer->replace( $decoded_serialized );
			$query_string      .= $decoded_string . "\n"; // append the line to the current query.
			$trimmed_line      = rtrim( $decoded_string );

			if ( 1 !== preg_match( "/;$/", $trimmed_line ) ) {
				continue; // skip incomplete statement.
			}

			$query_string = trim( $query_string );
			$query_string = shinst_maybe_randomize_constraint_sql( $query_string );

			$dbh->query( "SET SQL_MODE='ALLOW_INVALID_DATES'" );
			$dbh->query( 'SET foreign_key_checks = 0' );
			$dbh->query( $query_string );
			$dbh->query( 'SET foreign_key_checks = 1' );

			$line_number  = $reader->key();
			$query_string = "";

			if ( $line_number >= $max_line ) {
				$total_line  = $session->get_value( 'total_line' );
				$session->set_value( 'pointer', $line_number );
				$session->set_value( 'max_line', $max_line + $limit );
				$session->save();

				return $this->send_update( floor( ( $line_number / $total_line ) * 100 ) );
			}
		}

		return $this->send_update( 100 );
	}

	/**
	 * Read file
	 *
	 * @param $path
	 *
	 * @return SplFileObject
	 */
	public function read_file( $path ) {
		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			Shinst_Model_Log::write( 'Unable to read file:' . $path );
		}

		return new SplFileObject( $path );
	}

	/**
	 * Saves the update data in config session
	 */
	public function update_save() {
		$cfg = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );

		$url = ! empty( $_POST['url'] )
			? $_POST['url'] // Validate
			: Shinst_Model_Url::get_root_url();
		$cfg->set_value( 'site_url', $url );

		$rawpath = ! empty( $_POST['path'] )
			? $_POST['path'] // Validate
			: Shinst_Model_Url::get_root_url();
		$path    = parse_url( $rawpath, PHP_URL_PATH );
		$cfg->set_value( 'site_path', $path );

		$title = ! empty( $_POST['title'] )
			? $_POST['title'] // Validate
			: 'Destination Website';
		$cfg->set_value( 'site_title', $title );
		$cfg->save();

		$main = new Shinst_View_Cmp_Main();
		$main
			->add_component( new Shinst_View_Cmp_Title( 'Update Data' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'Please keep this window open while we update data on your new site. This can take anywhere from a few seconds to a few minutes depending upon the size of your database.' ) )
			->add_component( new Shinst_View_Cmp_Progress() );

		ob_start();
		$main->print_markup();
		echo '<style>';
		$main->print_styles();
		echo '</style>';
		$out = ob_get_clean();

		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_REPLACE );
		$session->clear();
		$session->save();

		$this->send_success( $out );
	}

	public function update_files() {
		if ( shinst_maybe_return_early( 'As some important tables are not found, skipping file update' ) ) {
			return $this->send_update( 100 );
		}

		$dsess        = Shinst_Model_Session::get( Shinst_Model_Session::SESS_DEPLOY );
		$config_files = $dsess->get_value( 'config_files', array() );
		if ( empty( $config_files ) ) {
			Shinst_Model_Log::write( 'No config files found stowed away' );

			return $this->send_update( 100 );
		}

		$session        = Shinst_Model_Session::get( Shinst_Model_Session::SESS_REPLACE );
		$deployed_files = $session->get_value( 'deployed_files', array() );

		$replacer = new Shinst_Model_Replacer();

		foreach ( $config_files as $source => $destination ) {
			if ( in_array( $source, $deployed_files, true ) ) {
				continue;
			}
			if ( ! is_readable( $source ) ) {
				Shinst_Model_Log::write( "Unable to read config source [{$source}]; skip" );
				$deployed_files[] = $source;
				continue;
			}
			$interim = shinst_trailingslash( Shinst_Model_Fs_Path::get_temp_dir() ) .
					   md5( $source . SHINST_SALT );
			$content = file_get_contents( $source );
			file_put_contents( $interim, $replacer->replace( $content ) );

			Shinst_Model_Log::write( "Deploying config file: [{$interim}]" );

			// ... move the file to destination...
			// ... remove interim file...
			copy( $interim, $destination );
			@unlink( $interim );
			$deployed_files[] = $source;
		}

		$session->set_value( 'deployed_files', $deployed_files );
		$session->save();

		return $this->send_update( 100 );
	}

	public function update_title() {
		if ( shinst_maybe_return_early( 'As some important tables are not found, skipping title update' ) ) {
			return $this->send_update( 100 );
		}

		$config = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$title  = $config->get_value( 'site_title' );
		if ( empty( $title ) ) {
			Shinst_Model_Log::write( 'Skipping site title update' );

			return $this->send_update( 100 );
		}

		Shinst_Model_Log::write( "Updating site title: [{$title}]" );
		$options = Shinst_Model_Db_Table::create(
			array(
				'name'       => $config->get_value( 'dbname' ),
				'user'       => $config->get_value( 'dbuser' ),
				'password'   => $config->get_value( 'dbpassword' ),
				'host'       => $config->get_value( 'dbhost' ),
				'prefix'     => $config->get_value( 'table_prefix' ),
				'table_name' => 'options',
			)
		);

		$options->query(
			"UPDATE {$options->get_table()} SET " .
			'option_value=%s WHERE ' .
			"option_name='blogname'",
			array( $title )
		);

		return $this->send_update( 100 );
	}

	public function update_finalize() {
		if ( shinst_maybe_return_early( 'As some important tables are not found, skipping final table update.' ) ) {
			return $this->send_update( 100 );
		}

		$config     = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$current = $config->get_value( 'site_url' );

		Shinst_Model_Log::write( "Updating site url: [{$current}]" );
		$options = Shinst_Model_Db_Table::create(
			array(
				'name'       => $config->get_value( 'dbname' ),
				'user'       => $config->get_value( 'dbuser' ),
				'password'   => $config->get_value( 'dbpassword' ),
				'host'       => $config->get_value( 'dbhost' ),
				'prefix'     => $config->get_value( 'table_prefix' ),
				'table_name' => 'options',
			)
		);

		$network_type = Shinst_Model_Manifest::get()->get_value( 'network_type' );

		Shinst_Model_Log::write( "Updating new site url: [{$current}]" );
		$options->query(
			"UPDATE {$options->get_table()} SET " .
			'option_value=%s WHERE ' .
			"option_name='siteurl'",
			array( $current )
		);
		$options->query(
			"UPDATE {$options->get_table()} SET " .
			'option_value=%s WHERE ' .
			"option_name='home'",
			array( $current )
		);

		Shinst_Model_Log::write( "Finally updating active theme and plugins of: [{$current}]" );
		$site_info     = Shinst_Model_Manifest::get()->get_value( 'site_info' );
		$plugins       = ! empty( $site_info['plugins'] ) ? $site_info['plugins'] : array();
		$theme         = ! empty( $site_info['template'] ) ? $site_info['template'] : '';
		$child_theme   = ! empty( $site_info['stylesheet'] ) ? $site_info['stylesheet'] : '';
		$is_multi_site = 'whole_network' === $network_type;

		$this->set_plugins( $plugins, $options, $is_multi_site );
		$this->set_theme( $theme, $child_theme, $options );
		$this->maybe_move_sitemeta_to_options_table( $network_type );

		return $this->send_update( 100 );
	}

	/**
	 * Set active plugins on destination site
	 *
	 * @param array                 $plugins
	 * @param Shinst_Model_Db_Table $model
	 * @param bool                  $is_multi_site
	 */
	public function set_plugins( $plugins, $model, $is_multi_site = false ) {
		$plugins_to_keep = array();
		Shinst_Model_Log::write( 'Found plugins from manifest.json: ' . shinst_convert_array_to_string( $plugins ) );

		$plugins_dir   = Shinst_Model_Fs_Path::get_plugins_dir();
		$plugins_files = Shinst_Model_Fs_Path::glob_all( $plugins_dir );

		$available_plugins = array_map(
			function( $plugins_file ) {
				return basename( $plugins_file );
			},
			$plugins_files
		);

		Shinst_Model_Log::write( 'Available plugins in wp-content/plugins:' . shinst_convert_array_to_string( $available_plugins ) );

		foreach ( $plugins as $plugin ) {
			$plugin_name = dirname( $plugin );

			if ( in_array( $plugin_name, $available_plugins, true ) ) {
				$plugins_to_keep[] = $plugin;
			}
		}

		Shinst_Model_Log::write( 'Setting these plugins as active: ' . shinst_convert_array_to_string( $plugins_to_keep ) );

		$model->query(
			"UPDATE {$model->get_table()} SET " .
			'option_value=%s WHERE ' .
			"option_name='active_plugins'",
			array( serialize( $plugins_to_keep ) )
		);

		if ( $is_multi_site ) {
			$cfg  = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
			$meta = Shinst_Model_Db_Table::create(
				array(
					'name'       => $cfg->get_value( 'dbname' ),
					'user'       => $cfg->get_value( 'dbuser' ),
					'password'   => $cfg->get_value( 'dbpassword' ),
					'host'       => $cfg->get_value( 'dbhost' ),
					'prefix'     => $cfg->get_value( 'table_prefix' ),
					'table_name' => 'sitemeta',
				)
			);

			$flattened_plugins = array();
			array_map(
				function( $plugin ) use ( &$flattened_plugins ) {
					$flattened_plugins = array_merge( $flattened_plugins, array( $plugin => time() ) );
				},
				$plugins_to_keep
			);

			$meta->query(
				"UPDATE {$meta->get_table()} SET " .
				'meta_value=%s WHERE ' .
				"meta_key='active_sitewide_plugins'",
				array( serialize( $flattened_plugins ) )
			);

			Shinst_Model_Log::write( 'Setting these plugins as active for the network: ' . shinst_convert_array_to_string( $plugins_to_keep ) );
		}
	}

	/**
	 * Set active theme in destination site
	 *
	 * @param string                $theme
	 * @param Shinst_Model_Db_Table $model
	 */
	public function set_theme( $theme, $child_theme, $model ) {
		Shinst_Model_Log::write( "Found themes from manifest.json: {$child_theme}" );

		$themes_dir   = Shinst_Model_Fs_Path::get_themes_dir();
		$themes_files = Shinst_Model_Fs_Path::glob_all( $themes_dir );

		$available_themes = array_map(
			function( $themes_file ) {
				return basename( $themes_file );
			},
			$themes_files
		);

		Shinst_Model_Log::write( 'Available themes in wp-content/themes:' . shinst_convert_array_to_string( $available_themes ) );

		if ( in_array( $child_theme, $available_themes, true ) ) {
			Shinst_Model_Log::write( 'Setting this theme as active: ' . $child_theme );

			$model->query(
				"UPDATE {$model->get_table()} SET " .
				'option_value=%s WHERE ' .
				"option_name='stylesheet'",
				array( $child_theme )
			);

			$model->query(
				"UPDATE {$model->get_table()} SET " .
				'option_value=%s WHERE ' .
				"option_name='template'",
				array( $theme )
			);
		} else {
			$theme = is_array( $available_themes ) && ! empty( $available_themes ) ? array_rand( array_flip( $available_themes ) ) : '';
			Shinst_Model_Log::write( sprintf( 'The active theme from manifest.json is not found in wp-content/themes. So trying to activate a random theme: %s', $theme ) );

			$model->query(
				"UPDATE {$model->get_table()} SET " .
				'option_value=%s WHERE ' .
				"option_name='stylesheet'",
				array( $theme )
			);

			$model->query(
				"UPDATE {$model->get_table()} SET " .
				'option_value=%s WHERE ' .
				"option_name='template'",
				array( $theme )
			);
		}
	}

	/**
	 * Maybe move sitemeta to options table
	 *
	 * @since 1.2.8
	 *
	 * @param string $network_type network type.
	 *
	 * @return void
	 */
	public function maybe_move_sitemeta_to_options_table( $network_type ) {
		if ( 'subsite' !== $network_type ) {
			return;
		}

		$cfg = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$db  = Shinst_Model_Db::create(
			array(
				'name'       => $cfg->get_value( 'dbname' ),
				'user'       => $cfg->get_value( 'dbuser' ),
				'password'   => $cfg->get_value( 'dbpassword' ),
				'host'       => $cfg->get_value( 'dbhost' )
			)
		);

		$prefix         = $cfg->get_value( 'table_prefix' );
		$sitemeta_table = $prefix . 'sitemeta';
		$options_table  = $prefix . 'options';
		$has_sitemeta   = $db->query( "show tables like '{$sitemeta_table}'" );

		if ( empty( $has_sitemeta ) ) {
			return;
		}

		$site_meta = $db->query( "select * from {$sitemeta_table};" );

		if ( empty( $site_meta ) || ! is_array( $site_meta ) ) {
			return;
		}

		foreach ( $site_meta as $meta ) {
			if ( in_array( $meta['meta_key'], $this->meta_to_ignore(), true ) ) {
				continue;
			}

			$db->query(
				"INSERT IGNORE INTO {$options_table} ( option_name, option_value )" .
				"VALUES( %s, %s );",
				array(
					$meta['meta_key'],
					$meta_value = is_string( $meta['meta_value'] )
						? $meta['meta_value']
						: serialize( $meta['meta_value'] )
				)
			);
		}

		$db->query( "DROP TABLE {$sitemeta_table};" );
	}

	/**
	 * Meta to ignore
	 *
	 * @since 1.2.8
	 *
	 * @return array
	 */
	public function meta_to_ignore() {
		return array(
			'site_name',
			'admin_email',
			'admin_user_id',
			'site_admins',
			'siteurl'
		);
	}
}



// Source: lib/installer/src/lib/controller/class-ajax.php

/**
 * Installer AJAX request controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Ajax extends Shinst_Controller {

	public function run() {
		if ( empty( $_GET['action'] ) ) {
			return false;
		}
		// Showing errors will break JSON and header setting.
		ini_set( 'display_errors', 'off' );

		$action  = preg_replace( '/[^-_a-zA-Z0-9]/', '', $_GET['action'] );
		$cls     = 'Shinst_Controller_Ajax_' . ucfirst( strtolower( $action ) );
		$allowed = array(
			Shinst_Controller_Ajax_Cleanup::class,
			Shinst_Controller_Ajax_Connection::class,
			Shinst_Controller_Ajax_Deploy::class,
			Shinst_Controller_Ajax_Password::class,
			Shinst_Controller_Ajax_Requirements::class,
			Shinst_Controller_Ajax_Update::class,
		);
		if ( ! class_exists( $cls ) || ! in_array( $cls, $allowed ) ) {
			throw new Shinst_Exception(
				sprintf( 'Unable to handle your action: [%s]', $action )
			);
		}
		$ctrl = new $cls();

		return $ctrl->run();
	}

	public function send_success( $data = array() ) {
		http_response_code( 200 );
		$data = is_string( $data ) ? $data : json_encode( $data );

		return $this->drop_dead( $data );
	}

	public function send_error( $data, $status = 500 ) {
		http_response_code( $status );
		$data = is_string( $data ) ? $data : json_encode( $data );

		return $this->drop_dead( $data );
	}

	public function drop_dead( $data = null ) {
		die( $data );
	}
}



// Source: lib/installer/src/lib/controller/class-front.php

/**
 * Installer front controller implementation
 *
 * @package shipper-installer
 */

class Shinst_Controller_Front extends Shinst_Controller {

	/**
	 * Stub password
	 *
	 * Used in tests.
	 *
	 * @var string
	 */
	private static $_password;

	public function run() {
		$pages = new Shinst_Controller_Page();
		try {
			$pages->run();
		} catch ( Exception $e ) {
			Shinst_Model_Log::write( $e->getMessage() );
			$view = new Shinst_View_Page_Error( $e );
			$view->print_markup();
			return false;
		}

		$ajax = new Shinst_Controller_Ajax();
		try {
			$ajax->run();
		} catch ( Exception $e ) {
			Shinst_Model_Log::write( "AJAX: {$e->getMessage()}" );
			$ajax->send_error( $e->getMessage() );
			return false;
		}

		return true;
	}

	/**
	 * Whether or not we are password protected
	 *
	 * @return bool
	 */
	public function has_password() {
		return ! empty( $this->get_password() );
	}

	/**
	 * Gets installer password
	 *
	 * @return string
	 */
	public function get_password() {
		if ( empty( self::$_password ) ) {
			return defined( 'SHINST_INSTALLER_PASSWORD' )
				? SHINST_INSTALLER_PASSWORD
				: '';
		}
		return self::$_password;
	}

	/**
	 * Sets password protection
	 *
	 * Used in tests.
	 *
	 * @param string $password Password to use.
	 */
	public function set_password( $password ) {
		self::$_password = (string) $password;
	}

	/**
	 * Check session password against installer password
	 *
	 * @return bool
	 */
	public function is_user_allowed() {
		if ( ! $this->has_password() ) {
			return true;
		}
		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		return $session->get_value( 'installer-password' ) === $this->get_password();
	}
}



// Source: lib/installer/src/lib/controller/class-page.php

/**
 * Installer pages handling controller
 *
 * @package shipper-installer
 */

class Shinst_Controller_Page extends Shinst_Controller {

	public function run() {
		$page = ! empty( $_GET['page'] )
			? $_GET['page']
			: ( empty( $_GET ) ? $this->get_default_page() : false );
		if ( empty( $page ) ) {
			return false;
		}

		if ( ! in_array( $page, $this->get_known_pages() ) ) {
			return $this->handle_invalid_request( $page );
		}

		$cls = 'Shinst_View_Page_' . ucfirst(
			strtolower(
				preg_replace( '/[^a-z]/i', '', $page )
			)
		);
		if ( ! class_exists( $cls ) ) {
			return $this->handle_invalid_request( $page );
		}

		return $this->serve_page( new $cls() );
	}

	/**
	 * Shows the page content, if applicable
	 *
	 * @throws Shinst_Exception on invalid protected pages access.
	 *
	 * @param object Shinst_View_Page instance, page to show.
	 *
	 * @return bool
	 */
	public function serve_page( Shinst_View_Page $page ) {
		if ( $page->is_protected() ) {
			$ctrl = new Shinst_Controller_Front();
			if ( ! $ctrl->is_user_allowed() ) {
				throw new Shinst_Exception( 'You are not allowed here, please leave.' );
				return false;
			}
		}
		$page->print_markup();

		return true;
	}

	/**
	 * Decides on where to start
	 *
	 * @return string Default page slug
	 */
	public function get_default_page() {
		$ctrl = new Shinst_Controller_Front();
		return $ctrl->is_user_allowed()
			? 'requirements'
			: 'password';
	}

	/**
	 * Gets a list of known page slugs.
	 *
	 * @return array
	 */
	public function get_known_pages() {
		return array(
			'password',
			'requirements',
			'connection',
			'deploy',
			'update',
			'finish',
			'error',
			'logs',
		);
	}

	/**
	 * Gets an absolute URL to a controller page
	 *
	 * @param string $page Page to get the URL to.
	 *
	 * @return string
	 */
	public function get_page_url( $page ) {
		return Shinst_Model_Url::get_self_url() . '?' . http_build_query(
			array(
				'page' => $page,
			)
		);
	}
}



// Source: lib/installer/src/lib/exceptions.php


/**
 * General, catch-all exception
 */
class Shinst_Exception extends Exception {}

/**
 * Filesystem specific exception
 */
class Shinst_Exception_Fs extends Shinst_Exception {}

/**
 * Database specific exception
 */
class Shinst_Exception_Db extends Shinst_Exception {}

/**
 * General data exception
 */
class Shinst_Exception_Data extends Shinst_Exception {}

/**
 * Authentication exception
 */
class Shinst_Exception_Auth extends Shinst_Exception {}

// ----- Recoverable exceptions -----

/**
 * Special-case recoverable exception
 *
 * Importantly, does not inherit from Shinst_Exception so it
 * can be caught independently.
 */
class Shinst_Exception_Recoverable extends Exception {}

/**
 * Database specific recoverable exception
 */
class Shinst_Exception_Recoverable_Db extends Shinst_Exception_Recoverable {}



// Source: lib/installer/src/lib/functions.php

/**
 * Random installer functions
 *
 * @package shipper-installer
 */

/**
 * Gets string with trailing slash removed
 *
 * @param string $what String to process.
 *
 * @return string
 */
function shinst_untrailingslash( $what ) {
	return rtrim( $what, '/' );
}

/**
 * Gets string with trailing slash added
 *
 * @param string $what String to process.
 *
 * @return string
 */
function shinst_trailingslash( $what ) {
	return shinst_untrailingslash( $what ) . '/';
}

/**
 * Gets nested list results
 *
 * Simplified `wp_list_pluck`
 *
 * @param array  $list List to process.
 * @param string $what Key/property to extract.
 *
 * @return array List of results
 */
function shinst_list_pluck( $list, $what ) {
	$results = array();
	foreach ( $list as $item ) {
		$value = null;
		if ( is_object( $item ) && isset( $item->$what ) ) {
			$value = $item->$what;
		}
		if ( is_array( $item ) && isset( $item[ $what ] ) ) {
			$value = $item[ $what ];
		}
		$results[] = $value;
	}

	return $results;
}

/**
 * Whether or not data is serialized
 *
 * Taken from wp-core (`is_serialized`).
 *
 * @param string $data Data to check.
 * @param bool   $strict Strictness flag.
 *
 * @return bool
 */
function shinst_is_serialized( $data, $strict = true ) {
	// if it isn't a string, it isn't serialized.
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
	if ( 'N;' === $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, - 1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	} else {
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		// Either ; or } must exist.
		if ( false === $semicolon && false === $brace ) {
			return false;
		}
		// But neither must be in the first X characters.
		if ( false !== $semicolon && $semicolon < 3 ) {
			return false;
		}
		if ( false !== $brace && $brace < 4 ) {
			return false;
		}
	}
	$token = $data[0];
	switch ( $token ) {
		case 's':
			if ( $strict ) {
				if ( '"' !== substr( $data, - 2, 1 ) ) {
					return false;
				}
			} elseif ( false === strpos( $data, '"' ) ) {
				return false;
			}
			break;
		// or else fall through.
		case 'a':
		case 'O':
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b':
		case 'i':
		case 'd':
			$end = $strict ? '$' : '';

			return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	}

	return false;
}

/**
 * Returns random character
 *
 * @return string Random character a-z0-9
 */
function shinst_randchar() {
	$chars = array_merge(
		range( 'a', 'z' ),
		range( 0, 0 )
	);

	return (string) $chars[ rand( 0, count( $chars ) - 1 ) ];
}

/**
 * @return bool
 */
function shinst_is_multisite() {
	$wpconfig_path = Shinst_Model_Fs_Path::is_wpconfig_located();
	if ( $wpconfig_path == false ) {
		return false;
	}
	// get if it is multisite
	$pattern  = "/^\s*?define\(\s*'MULTISITE'\s*,\s*true\s*\);/";
	$wpconfig = file_get_contents( $wpconfig_path );

	return preg_match_all( $pattern, $wpconfig );
}

function shinst_read_wpconfig() {
	$wpconfig_path = Shinst_Model_Fs_Path::is_wpconfig_located();
	if ( $wpconfig_path == false ) {
		return false;
	}
	$configs = file_get_contents( $wpconfig_path );
	if ( ! $configs ) {
		return false;
	}
	$params = array(
		'db_name'      => "/define.+?'DB_NAME'.+?'(.*?)'.+/",
		'db_user'      => "/define.+?'DB_USER'.+?'(.*?)'.+/",
		'db_password'  => "/define.+?'DB_PASSWORD'.+?'(.*?)'.+/",
		'db_host'      => "/define.+?'DB_HOST'.+?'(.*?)'.+/",
		'table_prefix' => "/\\\$table_prefix\s*=\s*[\'|\"](\w*\-?\w*)[\'|\"]\s*;/"
	);
	$return = array();

	foreach ( $params as $key => $value ) {
		$found = preg_match_all( $value, $configs, $result );

		if ( $found ) {
			if ( $key == 'db_host' ) {
				if ( stristr( $result[1][0], ':' ) ) {
					list( $host, $port ) = explode( ':', $result[1][0] );
					$return['db_host']   = $host;
					$return['port']      = $port;
				} else {
					$default_port      = (int) ini_get( 'mysqli.default_port' );
					$return['db_host'] = $result[1][0];
					$return['port']    = $default_port ? $default_port : 3306;
				}
			} else {
				$return[ $key ] = $result[1][0];
			}
		} else {
			$return[ $key ] = false;
		}
	}

	return $return;
}

/**
 * Borrow from WP core
 *
 * @param $original
 *
 * @return mixed
 */
function shinst_maybe_unserialize( $original ) {
	if ( shinst_is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
		// @RIPS\Annotation\Ignore
		return @unserialize( $original );
	}
	return $original;
}

/**
 * Check whether to return early or not
 *
 * @since 1.1.4
 *
 * @param $message_log
 *
 * @return bool
 */
function shinst_maybe_return_early( $message_log = '' ) {
	$is_missing = ! ! Shinst_Model_Manifest::get()->get_value( 'is_important_tables_missing' );

	if ( $is_missing && ! empty( $message_log ) ) {
		Shinst_Model_Log::write( $message_log );
	}

	return $is_missing;
}

/**
 * Check whether the string is a float like value
 *
 * @since 1.2.0
 *
 * @param $string
 *
 * @return bool
 */
function shinst_is_float_string( $string ) {
	return is_numeric( $string )
		   && false !== strpos( $string, '.' )
		   && 2 === count( explode( '.', $string ) );
}

/**
 * Check whether the sql string is foreign key constraint or not
 *
 * @since 1.2.2
 *
 * @param $string
 *
 * @return false|int
 */
function shinst_is_constraint( $string ) {
	return preg_match( "/constraint\s([`_a-z]+)\sFOREIGN\sKEY/mi", $string );
}

/**
 * Generate random string
 *
 * @since 1.2.2
 *
 * @param int $len
 *
 * @return string
 * @throws Exception
 */
function shinst_random_string( $len = 5 ) {
	$random = function_exists( 'random_bytes' )
		? 'random_bytes'
		: 'openssl_random_pseudo_bytes';

	return $len <= 1 ? 'r' : 'r' . substr( bin2hex( $random( $len ) ), 0, $len - 1 );
}

/**
 * If it's a foreign key constraint sql, add some random string and make it unique
 *
 * @since 1.2.2
 *
 * @param $string
 *
 * @return string|string[]|null
 * @throws Exception
 */
function shinst_maybe_randomize_constraint_sql( $string ) {
	if ( ! shinst_is_constraint( $string ) ) {
		return $string;
	}

	return preg_replace(
		"/constraint\s([`_a-z])/mi",
		'constraint $1' . shinst_random_string( 5 ) . '$3',
		$string
	);
}

/**
 * Convert a list of plugins/themes to a string.
 *
 * @since 1.2.5
 *
 * @param array $items An array of items.
 *
 * @return string|void
 */
function shinst_convert_array_to_string( $items ) {
	if ( ! is_array( $items ) || empty( $items ) ) {
		return;
	}

	$items = array_map(
		function( $item ) {
			$info = pathinfo( $item );
			return '.' !== $info['dirname'] ? $info['dirname'] : $info['filename'];
		},
		$items
	);

	return implode( ', ', $items );
}


// Source: lib/installer/src/lib/model/class-db.php

/**
 * Installer database abstraction
 *
 * @package shipper-installer
 */

class Shinst_Model_Db {

	/**
	 * MySQL host address
	 *
	 * @var string
	 */
	private $_host = 'localhost';

	/**
	 * MySQL port
	 *
	 * @var int
	 */
	private $_port = 3306;

	/**
	 * MySQL user name
	 *
	 * @var string
	 */
	private $_user;

	/**
	 * MySQL user password
	 *
	 * @var string
	 */
	private $_password;

	/**
	 * MySQL database name
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * MySQL connection reference
	 *
	 * @var resource
	 */
	private $_handle;

	/**
	 * Database model creation factory
	 *
	 * @param array $args Default params hash, optional.
	 *
	 * @return object Shinst_Model_Db instance
	 */
	public static function create( $args = array() ) {
		$me   = new self();
		$args = array_merge(
			self::get_empty_args_list(),
			$args
		);

		foreach ( $args as $key => $value ) {
			$setter = "set_{$key}";
			if ( is_callable( array( $me, $setter ) ) ) {
				$me->$setter( $value );
			}
		}

		return $me;
	}

	/**
	 * Gets an empty argument list
	 *
	 * Used as defaults for create factory
	 *
	 * @return array
	 */
	public static function get_empty_args_list() {
		return array(
			'name'     => '',
			'user'     => '',
			'password' => '',
			'port'     => '',
		);
	}

	/**
	 * Gets the MySQL connection reference
	 *
	 * Creates one, if one's not already created
	 *
	 * @return resource
	 * @throws Shinst_Exception_Db on failure.
	 */
	public function get_handle() {
		if ( $this->_handle ) {
			return $this->_handle;
		}

		$host = $this->get_host();
		if ( empty( $host ) ) {
			throw new Shinst_Exception_Db( 'Missing host' );
		}

		$port = $this->get_port();
		if ( empty( $port ) ) {
			throw new Shinst_Exception_Db( 'Missing port' );
		}

		$user = $this->get_user();
		if ( empty( $user ) ) {
			throw new Shinst_Exception_Db( 'Missing user' );
		}

		$password = $this->get_password();

		$name = $this->get_name();
		if ( empty( $name ) ) {
			throw new Shinst_Exception_Db( 'Missing db name' );
		}

		$this->_handle = @mysqli_connect(
			$host,
			$user,
			$password,
			$name,
			$port
		);

		if ( ! $this->_handle ) {
			$error = mysqli_connect_errno();
			throw new Shinst_Exception_Db(
				sprintf( 'Error connecting to DB: %d', $error )
			);
		}

		return $this->_handle;
	}

	/**
	 * Sets MySQL port
	 *
	 * @param int|false $port Port to use - if false, use whatever is the default.
	 */
	public function set_port( $port ) {
		$port = (int) $port;

		if ( empty( $port ) ) {
			$port = (int) ini_get( 'mysqli.default_port' );
		}

		if ( ! empty( $port ) ) {
			$this->_port = $port;
		}
	}

	/**
	 * Gets the MySQL port
	 *
	 * @return int
	 */
	public function get_port() {
		return (int) $this->_port;
	}

	/**
	 * Sets MySQL host address
	 *
	 * @param string $host Host address.
	 */
	public function set_host( $host ) {
		$host = ! empty( $host ) && is_string( $host )
			? $host
			: false;
		if ( ! empty( $host ) ) {
			$this->_host = $host;
		}
	}

	/**
	 * Gets MySQL host address
	 *
	 * @return string
	 */
	public function get_host() {
		return $this->_host;
	}

	/**
	 * Sets MySQL user
	 *
	 * @param string $user MySQL user.
	 */
	public function set_user( $user ) {
		$user        = is_string( $user )
			? $user
			: '';
		$this->_user = $user;
	}

	/**
	 * Gets MySQL user
	 *
	 * @return string
	 */
	public function get_user() {
		return $this->_user;
	}

	/**
	 * Sets MySQL password
	 *
	 * @param string $pass MySQL password.
	 */
	public function set_password( $pass ) {
		$pass            = is_string( $pass )
			? $pass
			: '';
		$this->_password = $pass;
	}

	/**
	 * Gets MySQL password
	 *
	 * @return string
	 */
	public function get_password() {
		return $this->_password;
	}

	/**
	 * Sets MySQL database name
	 *
	 * @param string $name MySQL db name.
	 */
	public function set_name( $name ) {
		$name        = is_string( $name )
			? $name
			: '';
		$this->_name = $name;
	}

	/**
	 * Gets MySQL database name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->_name;
	}

	/**
	 * Prepares and executes a query with supplied parameters
	 *
	 * @param string $raw_sql SQL with optional placeholders.
	 * @param array  $params Optional list of parameters.
	 *
	 * @return mixed (bool)true on success, or
	 *               result array for SELECT, DESCRIBE, SHOW, EXPLAIN queries.
	 * @throws Shinst_Exception_Recoverable_Db Recoverable exception on query failure.
	 */
	public function query( $raw_sql, $params = array() ) {
		$handle = $this->get_handle();
		$query  = $this->prepare_sql( $raw_sql, $params );
		$result = mysqli_query( $handle, $query );

		if ( false === $result ) {
			throw new Shinst_Exception_Recoverable_Db(
				sprintf(
					'Failure executing query [%s]: %s',
					$query,
					mysqli_error( $handle )
				)
			);
		}

		if ( true === $result ) {
			return true;
		}
		if ( ! function_exists( 'mysqli_fetch_all' ) ) {
			$actual = array();
			while ( $row = $result->fetch_assoc() ) {
				$actual[] = $row;
			}
		} else {
			$actual = mysqli_fetch_all( $result, MYSQLI_ASSOC );
		}

		mysqli_free_result( $result );

		return $actual;
	}

	/**
	 * Gets a particular row hash from prepared query results
	 *
	 * @param string $raw_sql SQL with optional placeholders.
	 * @param array  $params Optional list of parameters.
	 * @param int    $row Optional row index to fetch.
	 *
	 * @return array
	 * @throws Shinst_Exception_Recoverable_Db Recoverable exception on result non-array.
	 * @throws Shinst_Exception_Recoverable_Db Recoverable exception on index not in result.
	 */
	public function get_row( $raw_sql, $params = array(), $row = 0 ) {
		$result = $this->query( $raw_sql, $params );

		if ( ! is_array( $result ) ) {
			throw new Shinst_Exception_Recoverable_Db(
				sprintf( 'Unable to get row [%s] from non-array: %s', $row, json_encode( $result ) )
			);
		}

		if ( $row === 0 && empty( $result ) ) {
			return array();
		}

		if ( ! isset( $result[ $row ] ) ) {
			throw new Shinst_Exception_Recoverable_Db(
				sprintf( 'Unable to get row [%s], out of bound: %s', $row, json_encode( $result ) )
			);
		}

		return $result[ $row ];
	}

	/**
	 * Gets a particular column values from prepared query results
	 *
	 * @param string $raw_sql SQL with optional placeholders.
	 * @param array  $params Optional list of parameters.
	 * @param int    $col Optional column index to fetch.
	 *
	 * @return array
	 * @throws Shinst_Exception_Recoverable_Db Recoverable exception on result non-array.
	 * @throws Shinst_Exception_Recoverable_Db Recoverable exception on index not in result.
	 */
	public function get_col( $raw_sql, $params = array(), $col = 0 ) {
		$result = $this->query( $raw_sql, $params );

		if ( ! is_array( $result ) ) {
			throw new Shinst_Exception_Recoverable_Db(
				sprintf( 'Unable to get col [%s] from non-array: %s', $col, json_encode( $result ) )
			);
		}

		if ( $col === 0 && empty( $result ) ) {
			return array();
		}

		$first = reset( $result );
		if ( $col < 0 || $col > count( $first ) ) {
			throw new Shinst_Exception_Recoverable_Db(
				sprintf( 'Unable to get col [%s], out of bound: %s', $col, json_encode( $result ) )
			);
		}

		$column = array();
		foreach ( $result as $item ) {
			foreach ( array_values( $item ) as $idx => $value ) {
				if ( $idx !== $col ) {
					continue;
				}
				$column[] = $value;
			}
		}

		return $column;
	}

	/**
	 * Gets a particular value from prepared query results
	 *
	 * @param string $raw_sql SQL with optional placeholders.
	 * @param array  $params Optional list of parameters.
	 * @param int    $row Optional row index to fetch.
	 * @param int    $col Optional column index to fetch.
	 *
	 * @return scalar
	 * @throws Shinst_Exception_Recoverable_Db Recoverable exception on index not in result.
	 */
	public function get_val( $raw_sql, $params = array(), $row = 0, $col = 0 ) {
		$result = $this->get_col( $raw_sql, $params, $col );

		if ( $row === 0 && empty( $result ) ) {
			return false;
		}

		if ( ! isset( $result[ $row ] ) ) {
			throw new Shinst_Exception_Recoverable_Db(
				sprintf( 'Unable to get value [%s], out of bound: %s', $row, json_encode( $result ) )
			);
		}

		return $result[ $row ];
	}

	/**
	 * Prepares SQL based on parameters
	 *
	 * Escapes the parameters and builds query from those.
	 *
	 * @param string $raw_sql SQL with optional placeholders.
	 * @param array  $params Optional list of parameters.
	 *
	 * @return string
	 */
	public function prepare_sql( $raw_sql, $params = array() ) {
		if ( ! is_array( $params ) ) {
			return $raw_sql;
		}

		foreach ( $params as $key => $param ) {
			$params[ $key ] = $this->escape_param( $param );
		}

		return count( $params )
			? vsprintf( $raw_sql, $params )
			: $raw_sql;
	}

	/**
	 * Escapes a parameter for query substitution
	 *
	 * @param mixed $what Parameter to escape
	 *
	 * @return mixed int, float or string
	 */
	public function escape_param( $what ) {
		if ( is_numeric( $what ) ) {
			return false === strpos( "{$what}", '.' )
				? (int) $what
				: (float) $what;
		}

		$handle = $this->get_handle();
		if ( ! is_string( $what ) ) {
			$what = serialize( $what );
		}

		return sprintf( "'%s'", mysqli_real_escape_string( $handle, $what ) );
	}
}



// Source: lib/installer/src/lib/model/class-env.php

/**
 * Environment checking class
 *
 * @package shipper-installer
 */

/**
 * Class Shinst_Model_Env
 *
 * @since 1.2.6
 */
class Shinst_Model_Env {

	/**
	 * Checks whether we're on WPMU DEV Hosting
	 *
	 * @return bool
	 */
	public static function is_wpmu_hosting() {
		return isset( $_SERVER['WPMUDEV_HOSTED'] ) && ! empty( $_SERVER['WPMUDEV_HOSTED'] );
	}

	/**
	 * Checks whether we're on WPMU DEV Hosting
	 *
	 * @return bool
	 */
	public static function is_wpmu_staging() {
		if ( ! self::is_wpmu_hosting() ) {
			return false;
		}

		return isset( $_SERVER['WPMUDEV_HOSTING_ENV'] ) && 'production' !== $_SERVER['WPMUDEV_HOSTING_ENV'];
	}

	/**
	 * Checks whether we're dealing with Flywheel hosting
	 *
	 * @return bool
	 */
	public static function is_flywheel() {
		$wp_config_path = Shinst_Model_Fs_Path::is_wpconfig_located();

		return $wp_config_path && false !== strpos( file_get_contents( $wp_config_path ), 'FLYWHEEL_PLUGIN_DIR' );
	}
}



// Source: lib/installer/src/lib/model/class-log.php

/**
 * Log file handling
 *
 * @package shipper-installer
 */

class Shinst_Model_Log {

	/**
	 * Returns path to log file location
	 *
	 * @return string
	 */
	public static function get_file_path() {
		return shinst_trailingslash( Shinst_Model_Fs_Path::get_working_dir() ) .
			'installer.log';
	}

	/**
	 * Formats message into a log file line
	 *
	 * @param mixed $msg Message to format.
	 *
	 * @return string
	 */
	public static function get_log_line( $msg ) {
		$msg = is_string( $msg )
			? $msg
			: json_encode( $msg );
		return sprintf(
			"[%s] - %s\n",
			date( 'Y-m-d H:i:s' ),
			$msg
		);
	}

	/**
	 * Writes the message to log file
	 *
	 * @param mixed $msg Message to write as log line.
	 *
	 * @return bool
	 */
	public static function write( $msg ) {
		return (bool) file_put_contents(
			self::get_file_path(),
			self::get_log_line( $msg ),
			FILE_APPEND
		);
	}
}



// Source: lib/installer/src/lib/model/class-manifest.php

/**
 * Installer manifest parser
 *
 * @package shipper-installer
 */

class Shinst_Model_Manifest {

	const MANIFEST_BASENAME = 'migration_manifest';

	/**
	 * Parsed manifest data hash
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Singleton instance
	 *
	 * @var object Shinst_Model_Manifest instance
	 */
	private static $_instance;

	/**
	 * Singleton instance getter
	 *
	 * @return object Shinst_Model_Manifest instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Gets full path to the manifest file
	 *
	 * @return string
	 */
	public static function get_file_path() {
		$dir = Shinst_Model_Package::get_component_dir(
			Shinst_Model_Package::COMPONENT_META
		);
		return $dir . self::MANIFEST_BASENAME . '.json';
	}

	/**
	 * Constructor
	 *
	 * Sets up the model for usage - loads the data from the file.
	 */
	private function __construct() {
		$this->load();
	}

	/**
	 * Loads data from the manifest file
	 *
	 * @throws Shinst_Exception_Fs on manifest file read error.
	 * @throws Shinst_Exception_Data on manifest data parse error.
	 *
	 * @param bool $reload Whether to reload the data, defaults to false (no).
	 *
	 * @return bool False if already loaded and no reload requested, true otherwise
	 */
	public function load( $reload = false ) {
		$file = self::get_file_path();

		if ( ! empty( $this->_data ) && empty( $reload ) ) {
			// Already loaded, no reload requested. Done.
			return false;
		}

		if ( ! file_exists( $file ) ) {
			throw new Shinst_Exception_Fs(
				sprintf( 'Unable to find manifest at: %s', $file )
			);
		}
		if ( ! is_readable( $file ) ) {
			throw new Shinst_Exception_Fs(
				sprintf( 'Manifest file unreadable: %s', $file )
			);
		}

		$raw = file_get_contents( $file );
		if ( empty( $raw ) ) {
			throw new Shinst_Exception_Data(
				sprintf( 'Manifest file empty: %s', $file )
			);
		}

		$data  = @json_decode( $raw, true );
		$error = json_last_error();
		if ( empty( $data ) && ! empty( $error ) ) {
			throw new Shinst_Exception_Data(
				sprintf( 'Invalid manifest data: %s', json_last_error_msg() )
			);
		}

		$this->_data = $data;
		return true;
	}

	/**
	 * Gets specific property
	 *
	 * @param string $prop Property name to fetch.
	 * @param mixed  $fallback Optional fallback value, defaults to (bool)false.
	 *
	 * @return mixed Property value, or fallback.
	 */
	public function get_value( $prop, $fallback = false ) {
		if ( ! isset( $this->_data[ $prop ] ) ) {
			return $fallback;
		}

		return $this->_data[ $prop ];
	}
}



// Source: lib/installer/src/lib/model/class-package.php

/**
 * General installer package info
 *
 * @package shipper-installer
 */

class Shinst_Model_Package {

	const COMPONENT_FS      = 'files';
	const COMPONENT_DB      = 'sqls';
	const COMPONENT_META    = 'meta';
	const COMPONENT_DB_FILE = 'dump.sql';

	/**
	 * Generic component directory path getter
	 *
	 * @throws Shinst_Exception_Fs on invalid component.
	 *
	 * @param string $component Package component to resolve directory for.
	 *
	 * @return string
	 */
	public static function get_component_dir( $component ) {
		if ( ! in_array(
			$component,
			array(
				self::COMPONENT_META,
				self::COMPONENT_DB,
				self::COMPONENT_FS,
			),
			true
		) ) {
			throw new Shinst_Exception_Fs(
				sprintf( 'Unknown directory: %s', $component )
			);
		}
		$dir = shinst_trailingslash( Shinst_Model_Fs_Path::get_working_dir() ) .
			shinst_trailingslash( $component );

		if ( ! is_dir( $dir ) ) {
			Shinst_Model_Fs_Path::mkdir_p( $dir );
		}

		return shinst_trailingslash( $dir );
	}

	public static function get_dumped_sql_file() {
		return self::get_component_dir( self::COMPONENT_DB ) . self::COMPONENT_DB_FILE;
	}

	public static function get_dumped_sql_file_name() {
		return self::COMPONENT_DB_FILE;
	}
}



// Source: lib/installer/src/lib/model/class-replacer.php

/**
 * Installer replacer
 *
 * @package shipper-installer
 */

class Shinst_Model_Replacer {

	/**
	 * Macros cache
	 *
	 * @var array
	 */
	private $_macros = array();

	/**
	 * Replaces all shipper macros in a string
	 *
	 * @param string $what String to operate on.
	 *
	 * @return string
	 */
	public function replace( $what ) {
		if ( ! preg_match( '/' . preg_quote( '{{SHIPPER_', '/' ) . '/', $what ) ) {
			// Nothing to expand.
			return $what;
		}

		foreach ( $this->get_macros() as $macro => $value ) {
			// before replace, we need to check if this is serialize
			$what = preg_replace(
				'/' . preg_quote( $macro, '/' ) . '/',
				$value,
				$what
			);
		}

		/**
		 * if this is subsite extract, we need to clean up the mulitiste config in wp-config
		 */
		foreach ( $this->_get_subsite_extract_macros() as $macro => $value ) {
			$what = preg_replace(
				'/' . $macro . '/',
				$value,
				$what
			);
		}

		foreach ( $this->get_other_macros() as $macro => $value ) {
			$what = preg_replace(
				'/' . $macro . '/',
				$value,
				$what
			);
		}

		return ( new Shinst_Model_JSON_Serializer( $what ) )->run();
	}

	/**
	 * Gets macro replacements
	 *
	 * Cached: actually populates macros cache once.
	 *
	 * @return array
	 */
	public function get_macros() {
		if ( empty( $this->_macros ) ) {
			$this->_macros = $this->_get_macros();
		}

		return $this->_macros;
	}

	public function _get_subsite_extract_macros() {
		$network_type = Shinst_Model_Manifest::get()->get_value( 'network_type' );
		$macros       = array();
		$cfg          = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$current      = $cfg->get_value( 'site_url' );
		if ( $network_type == 'subsite' ) {
			/**
			 * remove the network config inside wp-config.php
			 */
			$macros = array_merge(
				$macros,
				array(
					"define\(\s*'SUNRISE'\s*,\s*(.*)\s*\);"                    => null,
					"define\(\s*'MULTISITE'\s*,\s*(true|false)\s*\);"          => null,
					"define\(\s*'WP_ALLOW_MULTISITE'\s*,\s*(true|false)\s*\);" => null,
					"define\(\s*'SUBDOMAIN_INSTALL'\s*,\s*(true|false)\s*\);"  => null,
					"define\s*\(\s*'DOMAIN_CURRENT_SITE'\s*,\s*'.*?'\s*\);"    => null,
					"define\s*\(\s*'PATH_CURRENT_SITE'\s*,\s*'.*?'\s*\);"      => null,
					"define\(\s*'SITE_ID_CURRENT_SITE'\s*,\s*\d+\s*\);"        => null,
					"define\(\s*'BLOG_ID_CURRENT_SITE'\s*,\s*\d+\s*\);"        => null,
				)
			);
		}

		return $macros;
	}

	/**
	 * Get other macros to replace
	 *
	 * @since 1.2.8
	 *
	 * @return array;
	 */
	public function get_other_macros() {
		return array(
			"define\(\s*'WP_CACHE_KEY_SALT'\s*,\s*(.*)\s*\);" => null,
		);
	}

	private function _get_macros() {
		$cfg      = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$manifest = Shinst_Model_Manifest::get();
		$codecs   = $manifest->get_value( 'codecs' );

		$current = $cfg->get_value( 'site_url' );
		$domain  = parse_url( $current, PHP_URL_HOST );
		$path    = parse_url( $current, PHP_URL_PATH );

		// Fix slash position in current, so that we don't end up
		// with double-slashed replacements.
		$original = ! empty( $codecs['{{SHIPPER_URL_WITH_SCHEME}}'] )
			? $codecs['{{SHIPPER_URL_WITH_SCHEME}}']
			: false;
		if ( shinst_untrailingslash( $original ) === $original ) {
			$current = shinst_untrailingslash( $current );
		}

		$abspath      = Shinst_Model_Fs_Path::get_root();
		$content_path = Shinst_Model_Fs_Path::get_rerooted(
			$codecs['{{SHIPPER_CONTENT_DIR}}'],
			$abspath,
			$codecs['{{SHIPPER_ABSPATH}}']
		);
		$macros       = array(
			'{{SHIPPER_DB_NAME}}'             => $cfg->get_value( 'dbname' ),
			'{{SHIPPER_DB_HOST}}'             => $cfg->get_value( 'dbhost' ),
			'{{SHIPPER_DB_USER}}'             => $cfg->get_value( 'dbuser' ),
			'{{SHIPPER_DB_PASSWORD}}'         => $cfg->get_value( 'dbpassword' ),
			'{{SHIPPER_TABLE_PREFIX}}'        => $cfg->get_value( 'table_prefix' ),
			'{{SHIPPER_DOMAIN_CURRENT_SITE}}' => $domain,
			'{{SHIPPER_PATH_CURRENT_SITE}}'   => $codecs['{{SHIPPER_PATH_CURRENT_SITE}}'],
			'{{SHIPPER_WPURL_WITH_SCHEME}}'   => $current,
			'{{SHIPPER_URL_WITH_SCHEME}}'     => $current,
			'{{SHIPPER_URL}}'                 => $current,
			'{{SHIPPER_HOME_URL}}'            => $domain,
			'{{SHIPPER_ADMIN_URL}}'           => shinst_trailingslash( $current ) . 'wp-admin',
			'{{SHIPPER_DOMAIN}}'              => $domain,
			'{{SHIPPER_MS_DOMAIN}}'           => $domain,
			'{{SHIPPER_MS_PATH}}'             => $path,
			'{{SHIPPER_CONTENT_DIR}}'         => shinst_trailingslash( $content_path ),
			'{{SHIPPER_ABSPATH}}'             => shinst_trailingslash( $abspath ),
			'{{SHIPPER_REWRITE_BASE}}'        => "RewriteBase {$path}",
		);

		return $macros;
	}

	/**
	 * @param $string
	 * @param string $value
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: '[^\'"]*?';

		// @codingStandardsIgnoreStart
		return '(?:^|\b)define\s?\(\s*' .
		       '(?:\'|")' .
		       preg_quote( $string, '/' ) .
		       '(?:\'|")' .
		       '\s*,\s*' .
		       '(?:\'|")' .
		       '((.*?))' .
		       '(?:\'|")' .
		       '\s*' .
		       '\)\s*;\s*(?:\b|$)';
		// @codingStandardsIgnoreEnd
	}
}



// Source: lib/installer/src/lib/model/class-session.php

/**
 * Installer session model
 *
 * Handles interim data storage.
 *
 * @package shipper-installer
 */

/**
 * Session model singleton factory
 */
class Shinst_Model_Session {

	const SESS_FSLIST  = 'fslist';
	const SESS_CONFIG  = 'config';
	const SESS_DEPLOY  = 'deploy';
	const SESS_REPLACE = 'replace';

	protected static $_instances = array();

	/**
	 * Singleton factory method
	 *
	 * @param string $id Session identifier.
	 *
	 * @return object Shinst_Model_Session instance
	 */
	public static function get( $id ) {
		if ( ! isset( self::$_instances[ $id ] ) ) {
			self::$_instances[ $id ] = new self( $id );
		}
		return self::$_instances[ $id ];
	}

	/**
	 * Session identifier
	 *
	 * @var string
	 */
	private $_id;

	/**
	 * Internal data storage
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Constructor
	 *
	 * @param string $id Session identifier.
	 */
	protected function __construct( $id ) {
		$this->_id = $id;
		$this->load();
	}

	public function can_use_actual_sessions() {
		return defined( 'SHINST_USE_SESSIONS' ) && SHINST_USE_SESSIONS;
	}

	/**
	 * Loads pickled session data
	 *
	 * @return bool
	 */
	public function load() {
		if ( ! $this->can_use_actual_sessions() ) {
			return $this->load_from_file();
		}

		if ( ! isset( $_SESSION[ $this->_id ] ) ) {
			$_SESSION[ $this->_id ] = array();
		}
		$this->_data = $_SESSION[ $this->_id ];
		return true;
	}

	/**
	 * Loads pickled session data from file
	 *
	 * @throws Shinst_Exception_Fs exception on file failure.
	 * @throws Shinst_Exception_Data on data parse error.
	 *
	 * @return bool
	 */
	public function load_from_file() {
		$file = $this->get_file_path();

		if ( ! file_exists( $file ) ) {
			return false; // No session data just yet.
		}

		if ( ! is_readable( $file ) ) {
			throw new Shinst_Exception_Fs(
				sprintf( 'Session [%s] unreadable: %s', $this->_id, $file )
			);
		}

		$raw = file_get_contents( $file );

		$data  = @json_decode( $raw, true );
		$error = json_last_error();
		if ( empty( $data ) && ! empty( $error ) ) {
			throw new Shinst_Exception_Data(
				sprintf(
					'Invalid session [%1$s] data: %2$s in %3$s',
					$this->_id,
					json_last_error_msg(),
					$file
				)
			);
		}

		$this->_data = $data;
		return true;
	}

	/**
	 * Persists session data to a storage file
	 *
	 * @throws Shinst_Exception_Fs on file not writable.
	 *
	 * @return bool
	 */
	public function save() {
		if ( $this->can_use_actual_sessions() ) {
			$_SESSION[ $this->_id ] = $this->_data;
			return true;
		}

		$file = $this->get_file_path();

		if ( file_exists( $file ) && ! is_writable( $file ) ) {
			throw new Shinst_Exception_Fs(
				sprintf( 'Unable to preserve session [%s] data in: %s', $this->_id, $file )
			);
		}

		$data = json_encode( $this->_data );
		return (bool) file_put_contents( $file, $data );
	}

	/**
	 * Returns path to pickled storage file
	 *
	 * @return string
	 */
	public function get_file_path() {
		return shinst_trailingslash( Shinst_Model_Fs_Path::get_temp_dir() ) .
			md5( $this->_id . SHINST_SALT ) . '.json';
	}

	/**
	 * Sets specific property
	 *
	 * @param string $key Property name to set.
	 * @param mixed  $value Property value to set.
	 */
	public function set_value( $key, $value ) {
		$this->_data[ $key ] = $value;
	}

	/**
	 * Gets specific property
	 *
	 * @param string $prop Property name to fetch.
	 * @param mixed  $fallback Optional fallback value, defaults to (bool)false.
	 *
	 * @return mixed Property value, or fallback.
	 */
	public function get_value( $key, $fallback = false ) {
		if ( ! isset( $this->_data[ $key ] ) ) {
			return $fallback;
		}
		return $this->_data[ $key ];
	}

	/**
	 * Clears out internal data storage
	 */
	public function clear() {
		$this->_data = array();
	}

}



// Source: lib/installer/src/lib/model/class-style.php

/**
 * Installer script styles hub
 *
 * @package shipper-installer
 */

class Shinst_Model_Style {

	const GLOBAL_BG          = '#FFFFFF';
	const GLOBAL_FG          = '#333333';
	const GLOBAL_FONT        = 'Roboto';
	const GLOBAL_LINE_HEIGHT = 30;

	const PARAGRAPH_FG = '#666666';
	const GHOST_FG     = '#888888';

	const BUTTON_BG = '#17A8E3';

	const GLOBAL_FONT_SIZE = 15;
	const LABEL_FONT_SIZE  = 12;

	const SIDEBAR_BG    = '#FAFAFA';
	const SIDEBAR_WIDTH = 268;

	const MAIN_WIDTH = 420;

	const COLOR_LABEL  = '#AAAAAA';
	const COLOR_BORDER = '#DDDDDD';

	const INPUT_HEIGHT = 40;
	const INPUT_INNER  = 22;

	const COLOR_ERROR   = '#FF6D6D';
	const COLOR_WARNING = '#FECF2F';
	const COLOR_SUCCESS = 'green';

	const DISABLED_BG = '#E6E6E6';
}



// Source: lib/installer/src/lib/model/class-url.php

/**
 * Installer URL resolution and handling model
 *
 * @package shipper-installer
 */

class Shinst_Model_Url {

	/**
	 * Whether or not we're to use HTTPS
	 *
	 * @return bool
	 */
	public static function is_ssl() {
		return isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'];
	}

	/**
	 * Gets the domain
	 *
	 * @throws Shinst_Exception if domain can't be inferred.
	 *
	 * @return string
	 */
	public static function get_host() {
		if ( empty( $_SERVER['HTTP_HOST'] ) ) {
			throw new Shinst_Exception( 'Unable to determine server host' );
		}

		// @RIPS\Annotation\Ignore
		$host = urlencode( $_SERVER['HTTP_HOST'] );

		if ( false !== strpos( $_SERVER['HTTP_HOST'], ':' ) ) {
			$host = urldecode( $host );
		}

		return $host;
	}

	/**
	 * Gets the full path to the script
	 *
	 * @throws Shinst_Exception if path can't be inferred.
	 *
	 * @return string
	 */
	public static function get_self_path() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			throw new Shinst_Exception( 'Unable to determine root path' );
		}

		return trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	}

	/**
	 * Gets absolute URL to the script itself
	 *
	 * @return string
	 */
	public static function get_self_url() {
		return self::get_url( basename( __FILE__ ) );
	}

	/**
	 * Gets root path to the directory
	 *
	 * Basically, the script directory.
	 *
	 * @return string
	 */
	public static function get_root_path() {
		$path = self::get_self_path();
		$root = dirname( $path );

		return shinst_trailingslash(
			'.' === $root ? '' : $root
		);
	}

	/**
	 * Gets protocol to be used in URL construction
	 *
	 * @return string
	 */
	public static function get_protocol() {
		return self::is_ssl() ? 'https://' : 'http://';
	}

	/**
	 * Gets the root URL
	 *
	 * This is the absolute URL to the installer directory.
	 *
	 * @return string
	 */
	public static function get_root_url() {
		return self::get_protocol() .
			   shinst_trailingslash( self::get_host() ) .
			   ltrim( self::get_root_path(), '/' );
	}

	/**
	 * Gets absolute url from a relative path
	 *
	 * @param string $path Relative path to resolve to absolute URL.
	 *
	 * @return string
	 */
	public static function get_url( $path ) {
		return self::get_root_url() . ltrim( $path, '/' );
	}

	/**
	 * Resolves an absolute FS path to relative URL path
	 *
	 * @param string $fs_path Absolute path to a file in script root.
	 *
	 * @return string
	 */
	public static function get_fs_url_path( $fs_path ) {
		return Shinst_Model_Fs_Path::get_rerooted(
			$fs_path,
			self::get_root_path()
		);
	}

	/**
	 * Resolves an absolute FS path to absolute URL
	 *
	 * @param string $fs_path Absolute path to a file in script root.
	 *
	 * @return string
	 */
	public static function get_fs_url( $fs_path ) {
		return self::get_protocol() .
			   shinst_trailingslash( self::get_host() ) .
			   ltrim( self::get_fs_url_path( $fs_path ), '/' );
	}
}



// Source: lib/installer/src/lib/model/db/class-table.php

/**
 * Installer database table abstraction
 *
 * @package shipper-installer
 */

class Shinst_Model_Db_Table extends Shinst_Model_Db {

	const MAX_SQL_TABLE_NAME_LENGTH = 64;

	/**
	 * MySQL table prefix
	 *
	 * @var string
	 */
	private $_tbl_prefix;

	/**
	 * MySQL table name (sans prefix)
	 *
	 * @var string
	 */
	private $_tbl_name;

	/**
	 * Database table model creation factory
	 *
	 * @param array $args Default params hash, optional.
	 *
	 * @return object Shinst_Model_Db_Table instance
	 */
	public static function create( $args = array() ) {
		$me   = new self();
		$args = array_merge(
			self::get_empty_args_list(),
			$args
		);

		foreach ( $args as $key => $value ) {
			$setter = "set_{$key}";
			if ( is_callable( array( $me, $setter ) ) ) {
				$me->$setter( $value );
			}
		}

		return $me;
	}

	/**
	 * Gets an empty argument list
	 *
	 * Used as defaults for create factory.
	 * Extends the parent list with prefix.
	 *
	 * @return array
	 */
	public static function get_empty_args_list() {
		$list               = parent::get_empty_args_list();
		$list['prefix']     = '';
		$list['table_name'] = '';

		return $list;
	}

	/**
	 * Sets table prefix
	 *
	 * @param string $prefix Table prefix.
	 */
	public function set_prefix( $prefix ) {
		$prefix            = is_string( $prefix )
			? $prefix
			: '';
		$this->_tbl_prefix = $prefix;
	}

	/**
	 * Gets table prefix
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->_tbl_prefix;
	}

	/**
	 * Sets table raw name
	 *
	 * @param string $name Raw table name.
	 */
	public function set_table_name( $name ) {
		$name            = is_string( $name )
			? $name
			: '';
		$this->_tbl_name = $name;
	}

	/**
	 * Gets table raw name
	 *
	 * @return string Table raw (sans-prefix) name.
	 */
	public function get_table_name() {
		return $this->_tbl_name;
	}

	/**
	 * Gets table full name (prefix+raw name)
	 *
	 * @return string Table full name.
	 */
	public function get_table() {
		return $this->get_prefix() . $this->get_table_name();
	}
}



// Source: lib/installer/src/lib/model/fs/class-archive.php

/**
 * Installer path resolution class
 *
 * @package shipper-installer
 */

class Shinst_Model_Fs_Archive {

	/**
	 * Gets full path to shipper package archive
	 *
	 * @param bool $reset_cache Reset cache on archive fetching (used in tests).
	 *
	 * @return string
	 */
	public static function get_archive( $reset_cache = false ) {
		static $zip;
		if ( ! empty( $reset_cache ) ) {
			$zip = '';
		}
		if ( empty( $zip ) ) {
			$path  = shinst_trailingslash( Shinst_Model_Fs_Path::get_root() );
			$files = glob( "{$path}*.shipper.zip" );
			if ( empty( $files ) ) {
				throw new Shinst_Exception_Fs(
					"Unable to find any archives in [{$path}]"
				);
			}
			// we always get the latest
			$by_date = array();
			$by_size = array();
			foreach ( $files as $file ) {
				// $by_size[ $file ] = filesize( $file );
				$by_date[ $file ] = filemtime( $file );
			}
			arsort( $by_date );
			$keys = array_keys( $by_date );
			$zip  = reset( $keys );
		}

		return $zip;
	}

	/**
	 * Extracts the Shipper package archive to the working directory
	 *
	 * @param bool $reset_cache Reset cache on archive fetching (used in tests).
	 *
	 * @return string
	 */
	public static function extract_all( $reset_cache = false ) {
		$archive = self::get_archive( $reset_cache );
		$zip     = new ZipArchive();
		if ( true !== $zip->open( $archive ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to open archive [{$archive}]"
			);
		}

		$destination = Shinst_Model_Fs_Path::get_working_dir();
		if ( true !== $zip->extractTo( $destination ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to extract archive [{$archive}] to [{$destination}]"
			);
		}

		return true;
	}

	/**
	 * Extract by entries
	 *
	 * @param $lists
	 *
	 * @return bool
	 * @throws Shinst_Exception_Fs
	 */
	public static function extract( $lists ) {
		$archive = self::get_archive( true );
		$zip     = new ZipArchive();
		if ( true !== $zip->open( $archive ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to open archive [{$archive}]"
			);
		}
		$destination = Shinst_Model_Fs_Path::get_working_dir();

		if ( true !== $zip->extractTo( $destination, $lists ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to extract archive [{$archive}] to [{$destination}]"
			);
		}

		return true;
	}

	public static function extract_by_offset( $offset ) {
		$archive = self::get_archive( true );
		$zip     = new ZipArchive();
		if ( true !== $zip->open( $archive ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to open archive [{$archive}]"
			);
		}
		if ( $offset >= $zip->numFiles ) {
			return true;
		}
		$destination = Shinst_Model_Fs_Path::get_working_dir();
		$limit       = 500;
		for ( $i = $offset; $i < $zip->numFiles; $i ++ ) {
			$path = $zip->getNameIndex( $i );
			$zip->extractTo( $destination, array( $path ) );
			$limit --;
			if ( $limit <= 0 ) {
				break;
			}
		}

		return array( $i, floor( ( $i / $zip->numFiles ) * 100 ) );
	}

	public static function extract_manifest( $reset_cache = false ) {
		$archive = self::get_archive( $reset_cache );
		$zip     = new ZipArchive();
		if ( true !== $zip->open( $archive ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to open archive [{$archive}]"
			);
		}
		$manifest = shinst_trailingslash( Shinst_Model_Package::COMPONENT_META ) .
					Shinst_Model_Manifest::MANIFEST_BASENAME . '.json';

		$destination = Shinst_Model_Fs_Path::get_working_dir();
		if ( true !== $zip->extractTo( $destination, array( $manifest ) ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to extract [{$manifest}] from archive [{$archive}]"
			);
		}

		return true;
	}

	public static function extract_dumped_sqls( $reset_cache = false ) {
		$archive = self::get_archive( $reset_cache );
		$zip     = new ZipArchive();

		if ( true !== $zip->open( $archive ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to open archive [{$archive}]"
			);
		}

		$sql_file    = shinst_trailingslash( Shinst_Model_Package::COMPONENT_DB ) . Shinst_Model_Package::COMPONENT_DB_FILE;
		$destination = Shinst_Model_Fs_Path::get_working_dir();

		if ( true !== $zip->extractTo( $destination, array( $sql_file ) ) ) {
			throw new Shinst_Exception_Fs(
				"Unable to extract [{$sql_file}] from archive [{$archive}]"
			);
		}

		return true;
	}
}



// Source: lib/installer/src/lib/model/fs/class-list.php

/**
 * Breadth-first filesystem lister
 *
 * @package shipper-installer
 */

/**
 * Breadth-first filesystem lister model
 */
class Shinst_Model_Fs_List {

	const KEY_TOTAL = 'total';
	const KEY_STEP  = 'step';
	const KEY_PATHS = 'paths';
	const KEY_DONE  = 'is_done';

	/**
	 * Root directory to start the crawl from
	 *
	 * Defaults to ABSPATH
	 *
	 * @var string
	 */
	private $_root;

	/**
	 * Files in current run storage
	 *
	 * @var array
	 */
	private $_files = array();

	/**
	 * Constructor
	 *
	 * @param string $path Absolute root path.
	 */
	public function __construct( $root ) {
		$this->_root = $root;
	}

	/**
	 * Whether we're done here
	 *
	 * @return bool
	 */
	public function is_done() {
		return (bool) Shinst_Model_Session::get( Shinst_Model_Session::SESS_FSLIST )
			->get_value( self::KEY_DONE, false );
	}

	/**
	 * Gets current step
	 *
	 * @return int
	 */
	public function get_current_step() {
		return (int) Shinst_Model_Session::get( Shinst_Model_Session::SESS_FSLIST )
			->get_value( self::KEY_STEP, 1 );
	}

	/**
	 * Gets total steps
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return (int) Shinst_Model_Session::get( Shinst_Model_Session::SESS_FSLIST )
			->get_value( self::KEY_TOTAL, 1 );
	}

	/**
	 * Gets files gathered this far
	 *
	 * Or, loads the next batch
	 *
	 * @return array
	 */
	public function get_files() {
		if ( ! empty( $this->_files ) ) {
			return $this->_files;
		}

		return $this->process_files();
	}

	/**
	 * Gets paths limitation
	 *
	 * @return int
	 */
	public function get_paths_limit() {
		return 250;
	}

	/**
	 * Gets chunk size limitation, in bytes
	 *
	 * @return int Size limitation, in bytes (zero means no limit)
	 */
	public function get_bytes_limit() {
		return 25 * 1024 * 1024;
	}

	/**
	 * Processes a list of files
	 *
	 * @return array
	 */
	public function process_files() {
		if ( $this->is_done() ) {
			return $this->_files; }

		$processed   = 0;
		$limit       = $this->get_paths_limit();
		$limit_files = $limit * 6;
		$limit_bytes = $this->get_bytes_limit();

		$session = Shinst_Model_Session::get( Shinst_Model_Session::SESS_FSLIST );

		$paths = $session->get_value( self::KEY_PATHS, array( $this->_root ) );

		while ( ! empty( $paths ) ) {
			$path = array_shift( $paths );
			$processed++;

			$contents = Shinst_Model_Fs_Path::glob_all( $path );
			foreach ( $contents as $item ) {
				if ( is_file( $item ) && ! is_link( $item ) ) {
					if ( ! is_readable( $item ) ) {
						// We will have issues with this!
						// Log or not, don't process this file.
						continue;
					}

					$size = filesize( $item );

					$this->_files[] = array(
						'path' => $item,
						'size' => $size,
					);
				} elseif ( is_dir( $item ) && ! is_link( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$paths[] = $item;
					}
				}
			}
			$session->set_value( self::KEY_PATHS, $paths );

			if ( count( $this->_files ) >= $limit_files ) {
				break;
			}

			if ( $processed >= $limit ) {
				break;
			}

			if ( ! empty( $limit_bytes ) ) {
				$total_bytes = array_sum( shinst_list_pluck( $this->_files, 'size' ) );
				if ( $total_bytes > $limit_bytes ) {
					break;
				}
			}
		}

		$paths = $session->get_value( self::KEY_PATHS );
		if ( empty( $paths ) ) {
			// So we are done. Say so.
			$session->set_value( self::KEY_DONE, true );
		}
		$step = $session->get_value( self::KEY_STEP, 0 );
		$step++;
		$session->set_value( self::KEY_STEP, $step );

		$total = $this->get_total_steps();
		if ( $total <= $step ) {
			$total++;
			$session->set_value( self::KEY_TOTAL, $total );
		}

		$session->save();

		return $this->_files;
	}

	/**
	 * Resets the lister state
	 */
	public function reset() {
		Shinst_Model_Session::get( Shinst_Model_Session::SESS_FSLIST )->clear();
		Shinst_Model_Session::get( Shinst_Model_Session::SESS_FSLIST )->save();
		$this->_files = array();
	}

	/**
	 * Reset local files cache
	 *
	 * Used in tests.
	 */
	public function reset_files_soft_cache() {
		$this->_files = array();
	}
}



// Source: lib/installer/src/lib/model/fs/class-path.php

/**
 * Installer path resolution class
 *
 * @package shipper-installer
 */

class Shinst_Model_Fs_Path {

	/**
	 * Gets the root directory for the installer
	 *
	 * @return string Root directory full path
	 */
	public static function get_root() {
		return dirname( __FILE__ );
	}

	/**
	 * Gets absolute path to installer working directory
	 *
	 * Will create the directory if it doesn't exist, as a side-effect.
	 *
	 * @param bool $create_path Optional path creation flag, defaults to true.
	 *
	 * @return string
	 */
	public static function get_working_dir( $create_path = true ) {
		$shipper_dir = shinst_trailingslash( self::get_root() ) . 'shipper-working';

		if ( ! empty( $create_path ) ) {
			if ( ! is_dir( $shipper_dir ) ) {
				self::mkdir_p( $shipper_dir );
			}

			if ( ! is_file( shinst_trailingslash( $shipper_dir ) . '.htaccess' ) ) {
				self::attempt_htaccess_protect( $shipper_dir );
			}
		}

		return shinst_trailingslash( $shipper_dir );
	}

	/**
	 * Get wp-content directory
	 *
	 * @return string
	 */
	public static function get_content_dir() {
		$wp_content_dir = shinst_trailingslash( self::get_root() ) . 'wp-content';

		return shinst_trailingslash( $wp_content_dir );
	}

	/**
	 * Get plugins directory
	 *
	 * @return string
	 */
	public static function get_plugins_dir() {
		$plugins_dir = shinst_trailingslash( self::get_content_dir() ) . 'plugins';

		return shinst_trailingslash( $plugins_dir );
	}

	/**
	 * Get themes directory
	 *
	 * @return string
	 */
	public static function get_themes_dir() {
		$themes_dir = shinst_trailingslash( self::get_content_dir() ) . 'themes';

		return shinst_trailingslash( $themes_dir );
	}

	/**
	 * Gets absolute path to installer temporary directory
	 *
	 * Will create the directory if it doesn't exist, as a side-effect.
	 *
	 * @return string
	 */
	public static function get_temp_dir() {
		$working = self::get_working_dir();
		$temp    = shinst_trailingslash( $working ) . 'tmp';

		if ( ! is_dir( $temp ) ) {
			self::mkdir_p( $temp );
		}

		return shinst_trailingslash( $temp );
	}

	/**
	 * Attempt to protect a web-visible directory
	 *
	 * Places a .htaccess file into the directory, which will add
	 * at least some protection from information disclosure.
	 *
	 * @param string $directory Path to the directory to protect.
	 *
	 * @return bool
	 */
	public static function attempt_htaccess_protect( $directory ) {
		if ( empty( $directory ) ) {
			return false;
		}

		$directory = shinst_trailingslash( $directory );
		if ( ! is_writable( $directory ) ) {
			return false;
		}

		$lines = array(
			'Order deny,allow',
			'Deny from all',
			'Options -Indexes',
		);

		return ! ! file_put_contents(
			"{$directory}.htaccess",
			join( "\n", $lines )
		);
	}

	/**
	 * Ensure we have decent compatibility with broken hosts
	 *
	 * @param string $path Path to glob.
	 *
	 * @return array
	 */
	public static function glob_all( $path ) {
		return defined( 'GLOB_BRACE' )
			? glob( shinst_trailingslash( $path ) . '{,.}[!.,!..]*', GLOB_BRACE )
			: glob( shinst_trailingslash( $path ) . '[!.,!..]*' );
	}

	/**
	 * Replaces FS root directory in supplied path to new root
	 *
	 * @param string $abspath Absolute path to reroot.
	 * @param string $newroot New root to use.
	 * @param string $oldroot Optional old root directory, defaults to root.
	 *
	 * @return string
	 */
	public static function get_rerooted( $abspath, $newroot, $oldroot = false ) {
		$oldroot = ! empty( $oldroot )
			? $oldroot
			: self::get_root();
		$root_rx = '/^' . preg_quote( shinst_trailingslash( $oldroot ), '/' ) . '/';

		return preg_replace( $root_rx, shinst_trailingslash( $newroot ), $abspath );
	}

	/**
	 * Whether or not file qualifies as a config file
	 *
	 * Config files get special treatment.
	 *
	 * @param string $path Absolute path to a file.
	 *
	 * @return bool
	 */
	public static function is_config_file( $path ) {
		$configs = array(
			'wp-config.php',
			'wp-tests-config.php',
			'.htaccess',
			'php.ini',
			'.user.ini',
			'wordfence-waf.php',
		);

		return in_array( basename( $path ), $configs, true );
	}

	/**
	 * Recursively clean directory path
	 *
	 * @param string $path Path to clean up.
	 * @param string $previous Previous path.
	 *
	 * @return bool
	 */
	public static function rmdir_r( $path, $previous ) {
		$next    = ( ! empty( $previous ) ? shinst_trailingslash( $previous ) : '' ) . basename( $path );
		$cleanup = self::glob_all( $path );
		$status  = true;
		foreach ( $cleanup as $file ) {
			if ( is_dir( $file ) ) {
				if ( ! self::rmdir_r( $file, $next ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
				if ( ! rmdir( $file ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
			} else {
				if ( ! is_writable( $file ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}

				// Alright, drop it like it's hot.
				if ( ! unlink( $file ) ) {
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
			}
		}

		return $status;
	}

	/**
	 * Check if the current directory alreayd have an old wp-config.php
	 *
	 * @return string | bool
	 */
	public static function is_wpconfig_located() {
		$wpconfig_path = self::get_root() . '/wp-config.php';
		if ( file_exists( $wpconfig_path ) ) {
			return $wpconfig_path;
		}

		return false;
	}

	/**
	 * Taken from `wp_mkdir_p`
	 *
	 * @param string $target Directory path to create.
	 *
	 * @return bool
	 */
	public static function mkdir_p( $target ) {
		// From php.net/mkdir user contributed notes.
		$target = str_replace( '//', '/', $target );

		/*
		 * Safe mode fails with a trailing slash under certain PHP versions.
		 * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
		 */
		$target = rtrim( $target, '/' );
		if ( empty( $target ) ) {
			$target = '/';
		}

		if ( file_exists( $target ) ) {
			return @is_dir( $target );
		}

		// We need to find the permissions of the parent folder that exists and inherit that.
		$target_parent = dirname( $target );
		while ( '.' != $target_parent && ! is_dir( $target_parent ) && dirname( $target_parent ) !== $target_parent ) {
			$target_parent = dirname( $target_parent );
		}

		// Get the permission bits.
		if ( $stat = @stat( $target_parent ) ) {
			$dir_perms = $stat['mode'] & 0007777;
		} else {
			$dir_perms = 0777;
		}

		if ( @mkdir( $target, $dir_perms, true ) ) {

			/*
			 * If a umask is set that modifies $dir_perms, we'll have to re-set
			 * the $dir_perms correctly with chmod()
			 */
			if ( $dir_perms != ( $dir_perms & ~umask() ) ) {
				$folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
				for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i ++ ) {
					@chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
				}
			}

			return true;
		}

		return false;
	}
}



// Source: lib/installer/src/lib/model/json/class-serializer.php

/**
 * JSON to Serializer
 *
 * @package shipper-installer
 */

/**
 * Class Shinst_Model_JSON_Serializer
 */
class Shinst_Model_JSON_Serializer {

	/**
	 * @var string
	 */
	private $string;

	const SHIPPER_JSON_START = '{{SHIPPER_JSON_START}}';
	const SHIPPER_JSON_END   = '{{SHIPPER_JSON_END}}';

	public function __construct( $string ) {
		$this->string = $string;
	}

	private function get_json_start_pos() {
		return strpos( $this->string, self::SHIPPER_JSON_START );
	}

	private function is_json() {
		return false !== strpos( $this->string, self::SHIPPER_JSON_START );
	}

	private function get_json_end_pos() {
		return strpos( $this->string, self::SHIPPER_JSON_END );
	}

	private function get_json() {
		if ( ! $this->is_json() ) {
			return false;
		}

		$pos                = $this->get_json_start_pos() + strlen( self::SHIPPER_JSON_START );
		$without_first_part = substr( $this->string, $pos );
		$json               = strstr( $without_first_part, self::SHIPPER_JSON_END, true );

		return stripslashes( $json );
	}

	private function get_json_last_part( $without_const = true ) {
		$pos = $without_const
			? $this->get_json_end_pos() + strlen( self::SHIPPER_JSON_END )
			: $this->get_json_end_pos();

		return substr( $this->string, $pos );
	}

	private function get_json_first_part( $without_const = true ) {
		$pos = $without_const
			? $this->get_json_start_pos()
			: $this->get_json_start_pos() + strlen( self::SHIPPER_JSON_START );

		return substr( $this->string, 0, $pos );
	}

	private function get_serialize() {
		return addslashes( serialize( json_decode( $this->get_json(), true ) ) );
	}

	public function run() {
		if ( ! $this->get_json() ) {
			return $this->string;
		}

		return $this->get_json_first_part() . $this->get_serialize() . $this->get_json_last_part();
	}
}



// Source: lib/installer/src/lib/model/serialize/class-decoder.php


/**
 * Class Shipper_Helper_Replacer_Serialize
 */
class Shinst_Model_Serialize_Decoder {

	/**
	 * String holder
	 *
	 * @var string
	 */
	private $string;

	const SHIPPER_SERIALIZE_START = '{{SHIPPER_SERIALIZE_START}}';
	const SHIPPER_SERIALIZE_END   = '{{SHIPPER_SERIALIZE_END}}';

	/**
	 * Check whether it's serialized or not
	 *
	 * @return bool
	 */
	private function is_serialized() {
		return false !== strpos( $this->string, self::SHIPPER_SERIALIZE_START );
	}

	/**
	 * Get serialized starting position
	 *
	 * @return false|int
	 */
	private function get_serialized_start_pos() {
		return strpos( $this->string, self::SHIPPER_SERIALIZE_START );
	}

	/**
	 * Get serialized ending position
	 *
	 * @return false|int
	 */
	private function get_serialized_end_pos() {
		return strpos( $this->string, self::SHIPPER_SERIALIZE_END );
	}

	/**
	 * Get Serialize string
	 *
	 * @return false|string
	 */
	private function get_serialized() {
		if ( ! $this->is_serialized() ) {
			return false;
		}

		$pos                = $this->get_serialized_start_pos() + strlen( self::SHIPPER_SERIALIZE_START );
		$without_first_part = substr( $this->string, $pos );
		$serialized         = strstr( $without_first_part, self::SHIPPER_SERIALIZE_END, true );

		return stripslashes( $serialized );
	}

	/**
	 * Get Serialize last part
	 *
	 * @param bool $without_const Whether to return the string without the CONST or NOT.
	 *
	 * @return false|string
	 */
	private function get_serialized_last_part( $without_const = true ) {
		$pos = $without_const
			? $this->get_serialized_end_pos() + strlen( self::SHIPPER_SERIALIZE_END )
			: $this->get_serialized_end_pos();

		return substr( $this->string, $pos );
	}

	/**
	 * Get first part of the Serialize string
	 *
	 * @param bool $without_const Whether to return the string without the CONST or NOT.
	 *
	 * @return false|string
	 */
	private function get_serialized_first_part( $without_const = true ) {
		$pos = $without_const
			? $this->get_serialized_start_pos()
			: $this->get_serialized_start_pos() + strlen( self::SHIPPER_SERIALIZE_START );

		return substr( $this->string, 0, $pos );
	}

	/**
	 * Get serialized string
	 *
	 * @return string
	 */
	private function get_decoded_serialized() {
		$model               = new Shinst_Model_Replacer();
		$serialized_replacer = new Shinst_Model_Serialize_Replacer();

		$decoded_string = $this->get_serialized();

		foreach ( $model->get_macros() as $find => $replace ) {
			$decoded_string = $serialized_replacer->replace( $find, $replace, $decoded_string );
		}

		foreach ( $model->_get_subsite_extract_macros() as $find => $replace ) {
			$decoded_string = $serialized_replacer->replace( $find, $replace, $decoded_string );
		}

		return addslashes( $decoded_string );
	}

	/**
	 * Transform the Serialize string
	 *
	 * @param string $string the string to be transformed.
	 *
	 * @return string
	 */
	public function transform( $string ) {
		$this->string = $string;

		if ( ! $this->get_serialized() ) {
			return $this->string;
		}

		return $this->get_serialized_first_part() . $this->get_decoded_serialized() . $this->get_serialized_last_part();
	}
}



// Source: lib/installer/src/lib/model/serialize/class-replacer.php

/**
 * Shipper helpers: serialized values replacer
 *
 * Handles low level serialized values replacement transformations.
 *
 * @package shipper
 */

/**
 * String replacer class
 */
class Shinst_Model_Serialize_Replacer {
	/**
	 * Whether to use regex replace or not
	 *
	 * @var bool
	 */
	private $regex_replace;

	/**
	 * Shipper_Helper_Replacer_Serialized constructor.
	 *
	 * @param false $regex_replace
	 */
	public function __construct( $regex_replace = false ) {
		$this->regex_replace = $regex_replace;
	}

	/**
	 * @param string $search string to replace.
	 * @param string $replace replacement string.
	 * @param string $string subject string.
	 * @param int    $count number of replacement.
	 *
	 * @return array|mixed|string|string[]|null
	 */
	private function str_replace( $search, $replace, $string, &$count = 0 ) {
		if ( $this->regex_replace ) {
			return preg_replace( "/{$search}/m", $replace, $string, -1, $count );
		}

		if ( function_exists( 'mb_split' ) ) {
			return self::mb_str_replace( $search, $replace, $string, $count );
		} else {
			return str_replace( $search, $replace, $string, $count );
		}
	}

	/**
	 * @param string $search string to replace.
	 * @param string $replace replacement string.
	 * @param string $string subject string.
	 * @param int    $count number of replacement.
	 *
	 * @return array|mixed|string
	 */
	private static function mb_str_replace( $search, $replace, $subject, &$count = 0 ) {
		if ( ! is_array( $subject ) ) {
			// Normalize $search and $replace so they are both arrays of the same length
			$searches     = is_array( $search ) ? array_values( $search ) : array( $search );
			$replacements = is_array( $replace )
				? array_values( $replace )
				: array( $replace );
			$replacements = array_pad( $replacements, count( $searches ), '' );

			foreach ( $searches as $key => $search ) {
				$parts = mb_split( preg_quote( $search ), $subject );

				if ( ! is_array( $parts ) ) {
					continue;
				}

				$count   += count( $parts ) - 1;
				$subject = implode( $replacements[ $key ], $parts );
			}
		} else {
			// Call mb_str_replace for each subject in array, recursively
			foreach ($subject as $key => $value) {
				$subject[$key] = self::mb_str_replace( $search, $replace, $value, $count );
			}
		}

		return $subject;
	}

	/**
	 * Replace the serialized string
	 *
	 * @param string $from
	 * @param string $to
	 * @param string $data
	 * @param false $serialised
	 *
	 * @return __PHP_Incomplete_Class|array|mixed|string|string[]|null
	 */
	public function replace( $from = '', $to = '', $data = '', $serialised = false ) {
		if ( is_string( $data ) && shinst_is_serialized( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
			$data = $this->replace( $from, $to, $unserialized, true );
		} elseif ( is_array( $data ) ) {
			$tmp_array = array();

			foreach ( $data as $key => $value ) {
				$tmp_array[$key] = $this->replace( $from, $to, $value, false );
			}

			$data = $tmp_array;
			unset( $tmp_array );
		} elseif ( is_object( $data ) && ! $data instanceof __PHP_Incomplete_Class ) {
			$tmp_object = $data;
			$props     = get_object_vars( $data );

			foreach ( $props as $key => $value ) {
				if ( is_int( $key ) ) {
					continue;
				}

				$tmp_object->$key = $this->replace( $from, $to, $value, false );
			}

			$data = $tmp_object;
			unset( $tmp_object );
		} elseif ( is_string( $data ) ) {
			$data = $this->str_replace( $from, $to, $data );
		}

		if ( $serialised ) {
			return serialize( $data );
		}

		return $data;
	}
}



// Source: lib/installer/src/lib/view/class-page.php

/**
 * Installer page view abstraction
 *
 * @package shipper-installer
 */

class Shinst_View_Page extends Shinst_View {

	/**
	 * Whether or not this page is protected
	 *
	 * @return bool
	 */
	public function is_protected() {
		$ctrl = new Shinst_Controller_Front();
		return $ctrl->has_password();
	}

	/**
	 * Main page area component
	 *
	 * @var object Shinst_View_Cmp_Main instance
	 */
	private $_main;

	/**
	 * Constructor
	 *
	 * @param string $title Page title.
	 */
	public function __construct( $title ) {
		$this->set_title( $title );
		$this->_main = new Shinst_View_Cmp_Main();
		$this
			->add_to_body( new Shinst_View_Cmp_Title( $title ) );
		$this
			->add_component( new Shinst_View_Cmp_Sidebar( $title ) )
			->add_component( new Shinst_View_Cmp_Topnav() )
			->add_component( $this->_main );
	}

	/**
	 * Adds a component to main area queue
	 *
	 * @param object Shinst_View component instance.
	 *
	 * @return object Shinst_View instance (self)
	 */
	public function add_to_body( Shinst_View $cmp ) {
		$this->_main->add_component( $cmp );
		return $this;
	}

	/**
	 * Gets main area component object
	 *
	 * @return object Shinst_View_Cmp_Main instance
	 */
	public function get_body() {
		return $this->_main;
	}

	public function print_styles() {
		?>
body {
	font-family: <?php echo Shinst_Model_Style::GLOBAL_FONT; ?>;
	font-size: <?php echo Shinst_Model_Style::GLOBAL_FONT_SIZE; ?>px;
	color: <?php echo Shinst_Model_Style::GLOBAL_FG; ?>;
	line-height: <?php echo Shinst_Model_Style::GLOBAL_LINE_HEIGHT; ?>px;
	background: linear-gradient(
		90deg,
		<?php echo Shinst_Model_Style::SIDEBAR_BG; ?> 0,
		<?php echo Shinst_Model_Style::SIDEBAR_BG; ?> <?php echo Shinst_Model_Style::SIDEBAR_WIDTH; ?>px,
		<?php echo Shinst_Model_Style::GLOBAL_BG; ?> <?php echo Shinst_Model_Style::SIDEBAR_WIDTH; ?>px,
		<?php echo Shinst_Model_Style::GLOBAL_BG; ?> 100%
	);
}
h1 {
	text-align: center;
	font-size: <?php echo Shinst_Model_Style::INPUT_INNER; ?>px;
}
p {
	color: <?php echo Shinst_Model_Style::PARAGRAPH_FG; ?>;
}
main {
	margin-bottom: 100px;
}
		<?php
		parent::print_styles();
	}

	public function print_scripts() {
		?>
<script>
;( function( $ ) {

	function init() {
		$( document ).on(
			'click',
			'.button.back',
			function( e ) {
				shinst.redirect( 'requirements' );
				return shist.stop_prop( e );
			}
		);
	}

	$( init );

} )( jQuery );
</script>
		<?php
		parent::print_scripts();
	}

	public function print_markup() {
		?>
<html>
	<head>
		<title><?php echo $this->get_title(); ?>: Shipper Package Installer</title>
		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" />
		<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
		<script>
			shinst = {
				stop_prop: function( e ) {
					if ( e && e.stopPropagation ) e.stopPropagation();
					if ( e && e.preventDefault ) e.preventDefault();

					return false;
				},
				ajax: function( action, obj ) {
					var path = window.location.pathname;
					return $.post(
						path + '?action=' + action,
						obj
					);
				},
				redirect: function( page ) {
					return window.location.search = '?page=' + page;
				}
			};
		</script>
		<style>
			<?php echo $this->print_styles(); ?>
		</style>
		<?php echo $this->print_scripts(); ?>
	</head>
	<body>
		<?php foreach ( $this->get_components() as $c ) { ?>
			<?php echo $c->print_markup(); ?>
		<?php } ?>
	</body>
</html>
		<?php
	}
}



// Source: lib/installer/src/lib/view/class-svg.php

/**
 * Installer SVG image abstraction
 *
 * @package shipper-installer
 */

abstract class Shinst_View_Svg extends Shinst_View {
}



// Source: lib/installer/src/lib/view/component/button/class-admin.php

/**
 * Visit admin button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Admin extends Shinst_View_Cmp_Button {

	public function __construct() {
		parent::__construct( 'Admin Login' );
	}

	public function get_href() {
		$admin_url = Shinst_Model_Url::get_url( 'wp-admin/' );
		/**
		 * attempt to check if we can access the admin page
		 */
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $admin_url );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 3 ); // connect timeout
		curl_setopt( $ch, CURLOPT_TIMEOUT, 3 ); // curl timeout
		$ret     = curl_exec( $ch );
		$retcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

		if ( $retcode == 200 ) {
			// thing is fine
			return $admin_url;
		}
		// need to lookup a bit if defender mask login here
		$config  = Shinst_Model_Session::get( Shinst_Model_Session::SESS_CONFIG );
		$options = Shinst_Model_Db::create(
			array(
				'name'     => $config->get_value( 'dbname' ),
				'user'     => $config->get_value( 'dbuser' ),
				'password' => $config->get_value( 'dbpassword' ),
				'host'     => $config->get_value( 'dbhost' ),
				'prefix'   => $config->get_value( 'table_prefix' ),
			)
		);

		// get if it is multisite
		$table_name   = $config->get_value( 'table_prefix' ) . 'options';
		$field_value  = 'option_value';
		$field_name   = 'option_name';
		$is_multisite = shinst_is_multisite();
		if ( $is_multisite ) {
			$table_name  = $config->get_value( 'table_prefix' ) . 'sitemeta';
			$field_name  = 'meta_key';
			$field_value = 'meta_value';
		}
		$sql = $options->prepare_sql( "SELECT $field_value FROM $table_name WHERE $field_name = %s", array( 'wd_masking_login_settings' ) );

		$data = $options->get_val( $sql );
		$data = json_decode( $data, true );
		if ( $data == false ) {
			return $admin_url;
		}
		// data is array,we have value here
		if ( $data['enabled'] == true && ! empty( $data['mask_url'] ) ) {
			$admin_url = Shinst_Model_Url::get_url( $data['mask_url'] );
		}

		return $admin_url;
	}
}



// Source: lib/installer/src/lib/view/component/button/class-back.php

/**
 * Back button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Back extends Shinst_View_Cmp_Button {

	public function __construct() {
		parent::__construct( 'Back' );
	}

	public function get_icon() {
		return new Shinst_View_Svg_Arrow();
	}

	public function print_styles() {
		parent::print_styles();
		?>
a.button.back {
	background: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
	margin-left: 30px;
}
a.button.back .text {
	display: none;
}
a.button.back .icon svg {
	transform: scaleX(-1);
	fill: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
	height: 16px;
}
		<?php
	}
}



// Source: lib/installer/src/lib/view/component/button/class-continue.php

/**
 * Continue anyway button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Continue extends Shinst_View_Cmp_Button {

	public function __construct() {
		parent::__construct( 'Continue Anyway' );
	}

	public function get_icon() {
		return new Shinst_View_Svg_Arrow();
	}

	public function get_icon_order() {
		return 'after';
	}

	public function get_href() {
		$ctrl = new Shinst_Controller_Page();
		return $ctrl->get_page_url( 'connection' );
	}
}



// Source: lib/installer/src/lib/view/component/button/class-documentation.php

/**
 * Installer documentation button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Documentation extends Shinst_View_Cmp_Button {

	public function __construct() {
		return parent::__construct( 'Documentation', true );
	}

	public function get_class() {
		return parent::get_class() . ' documentation';
	}

	public function get_href() {
		return 'https://wpmudev.com/docs/wpmu-dev-plugins/shipper/#package';
	}

	public function get_icon() {
		return new Shinst_View_Svg_Documentation();
	}

	public function print_styles() {
		?>
a.button.documentation {
	background: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
	border: 1px solid <?php echo Shinst_Model_Style::COLOR_BORDER; ?>;
	color: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
a.button.documentation .icon svg {
	fill: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/button/class-logs.php

/**
 * View logs button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Logs extends Shinst_View_Cmp_Button {

	public function __construct() {
		parent::__construct( 'View Installer Logs', true );
	}

	public function get_icon() {
		return new Shinst_View_Svg_Eye();
	}

	public function get_href() {
		$ctrl = new Shinst_Controller_Page();

		return $ctrl->get_page_url( 'logs' );
	}

	public function print_styles() {
		?>
		.button.viewinstallerlogs {
		background-color: #FAFAFA;
		color: #888888;
		border: 2px solid #DDDDDD;
		}
		.button.viewinstallerlogs .icon svg {
		fill: #888888;
		}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/button/class-next.php

/**
 * Next button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Next extends Shinst_View_Cmp_Button {

	public function __construct() {
		parent::__construct( 'Next' );
	}

	public function get_icon() {
		return new Shinst_View_Svg_Arrow();
	}

	public function get_icon_order() {
		return 'after';
	}
}



// Source: lib/installer/src/lib/view/component/button/class-recheck.php

/**
 * Recommendations recheck button
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button_Recheck extends Shinst_View_Cmp_Button {

	public function __construct() {
		parent::__construct( 'Re-Check' );
	}

	public function get_icon() {
		return new Shinst_View_Svg_Repeat();
	}
}



// Source: lib/installer/src/lib/view/component/class-button.php

/**
 * Installer button component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Button extends Shinst_View {
	public $open_new_tab = false;

	public function __construct( $title, $open_new_tab = false ) {
		$this->set_title( $title );
		$this->open_new_tab = $open_new_tab;
	}

	public function get_icon() {
		return '';
	}

	public function get_icon_order() {
		return 'before';
	}

	public function get_href() {
		return '#' . preg_replace( '/[^a-z0-9]/', '', strtolower( $this->get_title() ) );
	}

	public function get_class() {
		return preg_replace( '/[^a-z0-9]/', '', strtolower( $this->get_title() ) );
	}

	public function print_markup() {
		$cls        = $this->get_class();
		$icon       = $this->get_icon();
		$icon_order = false;
		if ( ! empty( $icon ) ) {
			$icon_order = $this->get_icon_order();
			$cls       .= ' with-icon icon-' . $icon_order;
		}
		?>
		<a <?php echo $this->open_new_tab == true ? 'target="_blank"' : null; ?> href="<?php echo $this->get_href(); ?>"
																				class="button <?php echo $cls; ?>">
			<?php if ( 'before' === $icon_order ) { ?>
				<span class="icon"><?php $icon->print_markup(); ?></span>
			<?php } ?>
			<span class="text"><?php echo $this->get_title(); ?></span>
			<?php if ( 'after' === $icon_order ) { ?>
				<span class="icon"><?php $icon->print_markup(); ?></span>
			<?php } ?>
		</a>
		<?php
	}

	public function print_styles() {
		?>
		a.button {
		display: inline-block;
		height: <?php echo Shinst_Model_Style::GLOBAL_LINE_HEIGHT; ?>;
		border-radius: 4px;
		color: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
		background: <?php echo Shinst_Model_Style::BUTTON_BG; ?>;
		text-decoration: none;
		text-transform: uppercase;
		font-size: <?php echo Shinst_Model_Style::LABEL_FONT_SIZE; ?>px;
		}
		a.button .icon svg {
		height: 12px;
		fill: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
		}
		a.button[disabled] {
		background: <?php echo Shinst_Model_Style::DISABLED_BG; ?>;
		color: <?php echo Shinst_Model_Style::COLOR_LABEL; ?>;
		}
		a.button[disabled] svg {
		fill: <?php echo Shinst_Model_Style::COLOR_LABEL; ?>;
		}
		a.button span {
		padding: 9px 16px;
		}
		a.button.with-icon .text, a.button.with-icon .icon {
		vertical-align: middle;
		display: inline-block;
		margin: 0;
		padding-top: 0;
		}
		a.button.icon-before span.icon {
		padding-right: 1px;
		}
		a.button.icon-before span.text {
		padding-left: 1px;
		}
		a.button.icon-after span.icon {
		padding-left: 1px;
		}
		a.button.icon-after span.text {
		padding-right: 1px;
		}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/class-code.php

/**
 * Installer code block view
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Code extends Shinst_View_Cmp_Paragraph {

	public function print_markup() {
		?>
	<pre><code><?php echo $this->get_title(); ?></code></pre>
		<?php
	}
}



// Source: lib/installer/src/lib/view/component/class-input.php

/**
 * Installer input view abstraction
 *
 * @package shipper-installer
 */

abstract class Shinst_View_Cmp_Input extends Shinst_View {

	/**
	 * Input field HTML type attribute
	 *
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Input field HTML name attribute
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Input field label
	 *
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Gets additional HTML classes
	 *
	 * @return string
	 */
	public function get_classes() {
		return '';
	}

	public function print_markup() {
		$cls = $this->get_classes();
		?>
<label class="field-<?php echo $this->get_type(); ?> <?php echo $cls; ?>">
	<span class="title"><?php echo $this->get_label(); ?></span>
	<input
		data-lpignore="true"
		type="<?php echo $this->get_type(); ?>"
		name="<?php echo $this->get_name(); ?>"
		placeholder="<?php echo $this->get_placeholder(); ?>"
	/>
	<span class="error">Error</span>
</label>
		<?php
	}

	public function print_styles() {
		$input_outer        = Shinst_Model_Style::INPUT_HEIGHT;
		$input_inner        = Shinst_Model_Style::INPUT_INNER;
		$input_vertical_pad = ( $input_outer - ( $input_inner + 2 ) ) / 2;
		?>
label span {
	display: block;
	color: <?php echo Shinst_Model_Style::COLOR_LABEL; ?>;
	font-size: <?php echo Shinst_Model_Style::LABEL_FONT_SIZE; ?>px;
	font-weight: bold;
	height: <?php echo $input_inner; ?>px;
	line-height: <?php echo $input_inner; ?>px;
}
label span.title {
	margin-bottom: 5px;
}
label input {
	color: #333333;
	border: 1px solid <?php echo Shinst_Model_Style::COLOR_BORDER; ?>;
	height: <?php echo $input_outer; ?>px;
	padding: <?php echo $input_vertical_pad; ?>px 12px;
	line-height: <?php echo $input_inner; ?>px;
	width: 100%;
	border-radius: 4px;
	font-size: 15px;
	background: #FAFAFA;
}
label input:focus {
	background: #FFFFFF;
}
label input::-webkit-input-placeholder { /* Chrome/Opera/Safari */
  color: #AAAAAA;
}
label input::-moz-placeholder { /* Firefox 19+ */
  color: #AAAAAA;
}
label input:-ms-input-placeholder { /* IE 10+ */
  color: #AAAAAA;
}
label input:-moz-placeholder { /* Firefox 18- */
  color: #AAAAAA;
}
label span.error {
	color: <?php echo Shinst_Model_Style::COLOR_ERROR; ?>;
	display: none;
}
label.has-error input {
	border-bottom: 2px solid <?php echo Shinst_Model_Style::COLOR_ERROR; ?>;
}
label.has-error span.error {
	display: block;
	font-weight: normal;
	text-align: right;
}
		<?php
		parent::print_styles();
	}

	public function get_placeholder() {
		return ''; }
}



// Source: lib/installer/src/lib/view/component/class-logs.php

/**
 * Logs text code view
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Logs extends Shinst_View {

	public function __construct( $title = '' ) {
		if ( ! empty( $title ) ) {
			$this->set_title( $title );
		}
	}

	public function print_markup() {
		$ctrl  = new Shinst_Controller_Front();
		$log   = Shinst_Model_Log::get_file_path();
		$title = $this->get_title();
		?>
		<?php if ( $ctrl->is_user_allowed() ) { ?>
			<?php if ( file_exists( $log ) ) { ?>
				<?php if ( ! empty( $title ) ) { ?>
				<h3><?php echo $title; ?></h3>
			<?php } ?>
			<pre class="logs"><?php echo file_get_contents( $log ); ?></pre>
		<?php } else { ?>
			<p><b>No Log file found</b></p>
		<?php } ?>
	<?php } else { ?>
		You are not authorized to view the log
	<?php } ?>
		<?php
	}

	public function print_styles() {
		?>
	pre.logs {
		padding: 1em;
		overflow-x: scroll;
	}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/class-main.php

/**
 * Page main body component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Main extends Shinst_View {

	public function print_markup() {
		?>
<main>
	<section>
		<?php foreach ( $this->get_components() as $cmp ) { ?>
			<?php $cmp->print_markup(); ?>
	<?php } ?>
	</section>
</main>
		<?php
	}

	public function print_styles() {
		?>
main {
	float: left;
	width: calc( 100% - <?php echo Shinst_Model_Style::SIDEBAR_WIDTH; ?>px );
}
main section {
	width: <?php echo Shinst_Model_Style::MAIN_WIDTH; ?>px;
	margin: 0 auto;
}
main section>p {
	text-align: center;
	margin-bottom: 30px;
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/class-notification.php

/**
 * Installer notification component
 *
 * @package shipper-installer
 */

abstract class Shinst_View_Cmp_Notification extends Shinst_View {

	abstract public function get_type();

	public function __construct( $title ) {
		$this->set_title( $title );
	}

	public function print_markup() {
		$type  = $this->get_type();
		$alert = new Shinst_View_Svg_Alert();
		?>
<div class="notification <?php echo $type; ?>">
	<div class="icon">
		<span><?php echo $alert->print_markup(); ?></span>
	</div>
	<div class="content">
		<?php echo $this->get_title(); ?>
	</div>
</div>
		<?php
	}

	public function print_styles() {
		?>
	.notification {
		padding: 18px 12px;
		border-radius: 4px;
		display: flex;
	}
	.notification.error {
		box-shadow: inset 2px 0 0 0 <?php echo Shinst_Model_Style::COLOR_ERROR; ?>, inset 0 0 0 1px <?php echo Shinst_Model_Style::DISABLED_BG; ?>;
	}
	.notification.warning {
		box-shadow: inset 2px 0 0 0 <?php echo Shinst_Model_Style::COLOR_WARNING; ?>, inset 0 0 0 1px <?php echo Shinst_Model_Style::DISABLED_BG; ?>;
	}
	.notification.info {
		box-shadow: inset 2px 0 0 0 #AAAAAA, inset 0 0 0 1px #E6E6E6;
	}
	.notification .icon {
		height: 16px;
		width: 16px;
		margin-top: 4px;
	}
	.notification.error .icon {
		fill: <?php echo Shinst_Model_Style::COLOR_ERROR; ?>;
	}
	.notification.warning .icon {
		fill: <?php echo Shinst_Model_Style::COLOR_WARNING; ?>;
	}
	.notification .content {
		color: <?php echo Shinst_Model_Style::GLOBAL_FG; ?>;
		line-height: <?php echo Shinst_Model_Style::INPUT_INNER; ?>px;
		margin-left: 10px;
		font-size: 13px;
	}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/class-paragraph.php

/**
 * Installer text paragraph view
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Paragraph extends Shinst_View {

	public function __construct( $title ) {
		$this->set_title( $title );
	}

	public function print_markup() {
		?>
	<p><?php echo $this->get_title(); ?></p>
		<?php
	}
}



// Source: lib/installer/src/lib/view/component/class-progress.php

/**
 * Installer progress meter component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Progress extends Shinst_View {

	private $_percentage = 0;

	public function __construct( $title = '', $percentage = 0 ) {
		$this->set_title( $title );
		$this->set_percentage( $percentage );
	}

	public function get_percentage() {
		return (int) $this->_percentage;
	}

	public function set_percentage( $percentage ) {
		$this->_percentage = (int) $percentage;
	}

	public function print_markup() {
		$loader     = new Shinst_View_Svg_Loader();
		$title      = $this->get_title();
		$percentage = $this->get_percentage();

		if ( empty( $title ) ) {
			$title = 'Connecting...';
		}
		?>
<div class="progress">
	<div class="meter">
		<span class="icon loader"><?php $loader->print_markup(); ?></span>
		<b class="status-percentage"><?php echo (int) $percentage; ?>%</b>
		<div class="progress-bar">
			<div class="status-bar" style="width: <?php echo (int) $percentage; ?>%"></div>
		</div>
	</div>
	<div class="status-message">
		<span><?php echo $title; ?></span>
	</div>
</div>
		<?php
	}

	public function print_styles() {
		?>
.progress {
	margin-top: 30px;
	color: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
.progress .meter {
	border: 1px solid <?php echo Shinst_Model_Style::DISABLED_BG; ?>;
	border-radius: 5px;
	display: flex;
	align-items: center;
	padding: 10px 30px;
	padding-left: 20px;
	font-size: 12px;
}
.progress .meter .loader {
	margin-right: 10px;
	animation: spin 1.3s linear infinite;
}
.progress .meter .loader svg {
	fill: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
.progress .meter .loader, .progress .meter .status-percentage {
	display: block;
	width: 20px;
}
.progress .meter .progress-bar {
	background: <?php echo Shinst_Model_Style::DISABLED_BG; ?>;
	border-radius: 5px;
	height: 10px;
	width: 100%;
	margin-left: 15px;
	position: relative;
}
.progress .meter .progress-bar .status-bar {
	position: absolute;
	top: 0;
	left: 0;
	height: 10px;
	border-radius: 5px;
	background: <?php echo Shinst_Model_Style::BUTTON_BG; ?>;
	width: 0;
}
.progress .status-message {
	text-align: center;
	font-size: 13px;
	margin-top: 10px;
}
@-webkit-keyframes spin{
	0% { -webkit-transform:rotate(0);transform:rotate(0); }
	100% {-webkit-transform:rotate(360deg); transform:rotate(360deg); }
}
@keyframes spin{
	0% { transform:rotate(0); }
	100% { transform:rotate(360deg); }
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/class-requirementsitem.php

/**
 * Installer requirements item abstraction
 *
 * @package shipper-installer
 */

abstract class Shinst_View_Cmp_RequirementsItem extends Shinst_View {

	const SEVERITY_ERROR   = 'error';
	const SEVERITY_WARNING = 'warning';

	public function __construct( $title ) {
		$this->set_title( $title );
	}

	/**
	 * Gets item check status
	 *
	 * @return bool|string True on success, severity level on fail
	 */
	public function get_status() {
		return self::SEVERITY_ERROR;
	}

	/**
	 * @return object Shinst_View_Svg instance
	 */
	public function get_success_icon() {
		return new Shinst_View_Svg_Check();
	}

	/**
	 * @return object Shinst_View_Svg instance
	 */
	public function get_warning_icon() {
		return new Shinst_View_Svg_Alert();
	}

	/**
	 * @return object Shinst_View_Svg instance
	 */
	public function get_error_icon() {
		return new Shinst_View_Svg_Alert();
	}

	/**
	 * @return object Shinst_View_Svg instance
	 */
	public function get_icon() {
		if ( true === $this->get_status() ) {
			return $this->get_success_icon();
		}
		return self::SEVERITY_WARNING === $this->get_status()
			? $this->get_warning_icon()
			: $this->get_error_icon();
	}

	public function print_markup() {
		$success_state_cls = 'success';
		$status            = $this->get_status();
		$content           = false;
		if ( true !== $status ) {
			$success_state_cls = "failure {$status}";
			$content           = $this->get_components();
			if ( ! empty( $content ) ) {
				$success_state_cls .= ' has-content';
			}
		}
		$chevron = new Shinst_View_Svg_Chevron();
		?>
<div class="requirements-item <?php echo $success_state_cls; ?> closed">
	<div class="requirements-item-title">
		<div class="status icon">
			<?php $this->get_icon()->print_markup(); ?>
		</div>
		<div class="title">
			<?php echo $this->get_title(); ?>
		</div>
		<?php if ( true !== $status ) { ?>
		<div class="toggle">
			<span><?php echo $chevron->print_markup(); ?></span>
		</div>
	<?php } ?>
	</div>
		<?php if ( ! empty( $content ) ) { ?>
	<div class="requirements-item-content">
		<div class="content-wrap">
			<?php foreach ( $content as $item ) { ?>
				<?php $item->print_markup(); ?>
			<?php } ?>
		</div>
		<div class="requirements-item-footer">
			<?php ( new Shinst_View_Cmp_Button_Recheck() )->print_markup(); ?>
		</div>
	</div>
<?php } ?>
</div>
		<?php
	}

	public function print_styles() {
		?>
.requirements-item {
	box-sizing: border-box;
}
.requirements-item-title {
	display: flex;
	padding: 19px 22px;
	border-radius: 4px;
	border: 1px solid <?php echo Shinst_Model_Style::DISABLED_BG; ?>;
	max-height: 60px;
	margin-bottom: 10px;
}
.requirements-item-title .status.icon,
.requirements-item-title .toggle,
{
	width: 35px;
	height: 16px
}
.requirements-item-title .toggle svg {
	width: 12px;
	margin-top: 4px;
	fill: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
.requirements-item-title .status.icon svg {
	height: 16px;
	width: 16px;
}
.requirements-item.success {
	display: none;
}
.requirements-item.success .status.icon svg {
	fill: <?php echo Shinst_Model_Style::COLOR_SUCCESS; ?>;
}
.requirements-item.warning .status.icon svg {
	fill: <?php echo Shinst_Model_Style::COLOR_WARNING; ?>;
}
.requirements-item.error .status.icon svg {
	fill: <?php echo Shinst_Model_Style::COLOR_ERROR; ?>;
}
.requirements-item.has-content .requirements-item-title {
	cursor: pointer;
}
.requirements-item-title .toggle {
	margin-left: auto;
	display: none;
}
.requirements-item.has-content .toggle {
	display: block;
}
.requirements-item-title .title {
	height: <?php echo Shinst_Model_Style::INPUT_INNER; ?>px;
	line-height: <?php echo Shinst_Model_Style::INPUT_INNER; ?>px;
	margin-left: 10px;
	font-size: 13px;
	font-weight: bold;
}

.requirements-item.closed .requirements-item-content {
	display: none;
}
.requirements-item.open .requirements-item-content {
	display: block;
	border: 1px solid <?php echo Shinst_Model_Style::COLOR_BORDER; ?>;
	border-top: none;
	margin-bottom: 10px;
}
.requirements-item.open .requirements-item-title {
	border-bottom: none;
	margin-bottom: 0;
}
.requirements-item.open .requirements-item-title .toggle {
	transform: scaleY(-1);
}

.requirements-item .content-wrap {
	border-top: 1px solid <?php echo Shinst_Model_Style::COLOR_BORDER; ?>;
	margin: 30px;
	margin-top: -1px;
}
.requirements-item-content {
	font-size: 13px;
}
.requirements-item-content h3 {
	font-weight: bold;
	margin-top: 30px;
	margin-bottom: 5px;
	font-size: 13px;
}
.requirements-item-content p {
	margin-top: 0;
	margin-bottom: 5px;
	line-height: <?php echo Shinst_Model_Style::INPUT_INNER; ?>px;
	color: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
.requirements-item-content a {
	text-decoration: none;
	font-weight: bold;
	color: <?php echo Shinst_Model_Style::BUTTON_BG; ?>;
}
.requirements-item-footer {
	padding: 30px;
	border-top: 1px solid <?php echo Shinst_Model_Style::COLOR_BORDER; ?>;
}
.requirements-item-footer a.button.recheck {
	background: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
	color: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
	border: 2px solid <?php echo Shinst_Model_Style::COLOR_BORDER; ?>;
}
.requirements-item-footer a.button.recheck .icon svg {
	fill: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/class-sidebar.php

/**
 * Installer sidebar component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Sidebar extends Shinst_View {

	/**
	 * Constructor
	 *
	 * @param string $active Currently active item title.
	 */
	public function __construct( $active ) {
		$items = array(
			'Installer Password',
			'Requirements Check',
			'Database Connection',
			'Deploy Website',
			'Update Data',
			'Finish & Cleanup',
		);
		foreach ( $items as $title ) {
			$item = new Shinst_View_Cmp_SidebarItem( $title, $active, $items );
			$this->add_component( $item );
		}
	}

	public function print_markup() {
		$title = new Shinst_View_Cmp_Title( 'Migration Wizard' );
		?>
<aside class="sidebar">
	<nav>
		<?php $title->print_markup(); ?>
		<ul>
		<?php foreach ( $this->get_components() as $c ) { ?>
			<?php echo $c->print_markup(); ?>
		<?php } ?>
		</ul>
	</nav>
		<?php
		try {
			$this->get_button()->print_markup();
		} catch ( Exception $e ) {
			echo ''; }
		?>
</aside>
		<?php
	}

	public function print_styles() {
		?>
aside.sidebar {
	float: left;
	width: <?php echo Shinst_Model_Style::SIDEBAR_WIDTH; ?>px;
	height: 100%;
}
aside h1 {
	margin-top: 30px;
	margin-bottom: 60px;
	margin-left: 22px;
	font-size: 28px;
	text-align: left;
}
aside.sidebar ul {
	list-style-type: none;
	padding: 0;
	margin: 0;
	margin-left: 22px;
}
aside.sidebar ul li {
	margin: 0;
	padding: 0;
	display: flex;
	margin-bottom: 15px;
	font-weight: bold;
}
aside.sidebar ul li span {
	height: 20px;
	display: block;
	line-height: 20px;
}
aside.sidebar li span.status {
	width: 20px;
	background: <?php echo Shinst_Model_Style::GLOBAL_FG; ?>;
	border-radius: 10px;
	margin-right: 12px;
	position: relative;
}

aside.sidebar li span.status.previous {
	background: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
}
aside.sidebar li span.status.previous svg {
	fill: <?php echo Shinst_Model_Style::GLOBAL_FG; ?>;
}
aside.sidebar li span.status.active::before {
	content: " ";
	background: <?php echo Shinst_Model_Style::GLOBAL_BG; ?>;
	display: block;
	position: absolute;
	height: 6px;
	width: 6px;
	border-radius: 6px;
	top: 7px;
	left: 7px;
}
aside.sidebar li span.status.next {
	background-color: #F2F2F2;
}

.button.viewinstallerlogs {
	position: fixed;
	bottom: 10px;
	left: 40px;
}
		<?php
		$this->get_button()->print_styles();
		parent::print_styles();
	}

	public function get_button() {
		if ( empty( $this->_button ) ) {
			$this->_button = new Shinst_View_Cmp_Button_Logs();
		}
		return $this->_button;
	}
}



// Source: lib/installer/src/lib/view/component/class-sidebaritem.php

/**
 * Sidebar item view
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_SidebarItem extends Shinst_View {

	/**
	 * Constructor
	 *
	 * @param string $title Sidebar item title.
	 * @param string $active Currently active sidebar item title.
	 * @param array  $all_items All sidebar item titles.
	 */
	public function __construct( $title, $active, $all_items ) {
		$this->set_title( $title );

		$this->_current = $active;
		$this->_self    = $title;
		$this->_items   = $all_items;
	}

	/**
	 * Whether or not this item is currently active one.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->_current === $this->_self;
	}

	public function print_markup() {
		?>
	<li class="<?php echo $this->is_active() ? 'active' : ''; ?>">
		<?php echo $this->get_status_indicator(); ?>
		<span><?php echo $this->get_title(); ?></span>
	</li>
		<?php
	}

	/**
	 * Gets item status indicator
	 *
	 * This shows whether the item is alredy done, is active or is to be done.
	 *
	 * @uses Shinst_View_Cmp_SidebarItem::get_inactive_indicator
	 *
	 * @return string
	 */
	public function get_status_indicator() {
		return $this->is_active()
			? '<span class="status active"></span>'
			: $this->get_inactive_indicator();
	}

	/**
	 * Gets inactive status indicator
	 *
	 * Visually determines if the item is already done, or is to be done.
	 *
	 * @return string
	 */
	public function get_inactive_indicator() {
		$active  = array_search( $this->_current, $this->_items );
		$current = array_search( $this->_self, $this->_items );
		if ( false === $active || false === $current ) {
			return '';
		}

		if ( $active > $current ) {
			$icon = new Shinst_View_Svg_Check();
			ob_start();
			$icon->print_markup();
			$markup = ob_get_clean();
			return '<span class="status previous">' .
				$markup .
				'</span>';
		}

		return '<span class="status next"></span>';
	}
}



// Source: lib/installer/src/lib/view/component/class-subtitle.php

/**
 * Installer subtitle view
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Subtitle extends Shinst_View_Cmp_Title {

	public function print_markup() {
		?>
	<h3><?php echo $this->get_title(); ?></h3>
		<?php
	}
}



// Source: lib/installer/src/lib/view/component/class-title.php

/**
 * Installer title view
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Title extends Shinst_View {

	public function __construct( $title ) {
		$this->set_title( $title );
	}

	public function print_markup() {
		?>
	<h1><?php echo $this->get_title(); ?></h1>
		<?php
	}
}



// Source: lib/installer/src/lib/view/component/class-topnav.php

/**
 * Installer main page top navigation
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Topnav extends Shinst_View {

	public function __construct() {
		$this->add_component( new Shinst_View_Cmp_Button_Back() );
		$this->add_component( new Shinst_View_Cmp_Button_Documentation() );
	}

	public function print_markup() {
		?>
<nav class="top">
		<?php foreach ( $this->get_components() as $c ) { ?>
			<?php echo $c->print_markup(); ?>
<?php } ?>
</nav>
		<?php
	}

	public function print_styles() {
		?>
nav.top {
	margin-top: 35px;
	margin-right: 30px;
}
nav.top a.documentation {
	float: right;
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/component/input/class-installerpassword.php

/**
 * Installer password component implementation
 *
 * @package shipper-installer
 */
class Shinst_View_Cmp_Input_InstallerPassword extends Shinst_View_Cmp_Input_Password {

	public function get_label() {
		return 'Installer Password';
	}

	public function get_placeholder() {
		return 'Enter your installer password';
	}

	public function print_markup() {
		parent::print_markup();
		$eye   = new Shinst_View_Svg_Eye();
		$blind = new Shinst_View_Svg_Blindeye();
		?>
<span class="installer-password icon eye active">
		<?php $eye->print_markup(); ?>
</span>
<span class="installer-password icon blindeye">
		<?php $blind->print_markup(); ?>
</span>
		<?php
	}

	public function print_scripts() {
		?>
<script>
;(function( $ ) {

	function show_error( msg ) {
		var $el = $( 'label.field-password' );
		$el
			.find( '.error' ).text( msg ).end()
			.addClass( 'has-error' )
		;
	}

	function clear_error() {
		$( 'label.field-password' ).removeClass( 'has-error' );
	}

	function auth_request( password ) {
		return shinst.ajax( 'password', { password: password } );
	}

	function handle_auth( e ) {
		clear_error();
		var pwd = $( '[name="password"]' ).val();
		var $btn = $( '.button.next' );
		if ( ! pwd ) {
			show_error( 'Please enter password' );
			return shinst.stop_prop( e );
		}

		$btn
			.off( 'click', handle_auth )
			.attr( 'disabled', true )
			.find( '.text' ).text( 'Authorizing...' );
		auth_request( pwd )
			.done( function () {
				shinst.redirect( 'requirements' );
			} )
			.fail( function ( rq ) {
				show_error( rq.responseText );
			} )
			.always( function () {
				$btn
					.on( 'click', handle_auth )
					.attr( 'disabled', false )
					.find( '.text' ).text( 'Next' );
			} );
		return shinst.stop_prop( e );
	}

	function handle_password_field_type_change( e ) {
		var $pwd = $( '[name="password"]' ),
			type = $pwd.attr( 'type' ),
			newtype = 'password' === type ? 'text' : 'password',
			active = 'password' === newtype ? 'eye' : 'blindeye';
		$( '.installer-password.icon' )
			.removeClass( 'active' )
				.filter( '.' + active ).addClass( 'active' );
		$pwd.attr( 'type', newtype );
		return shinst.stop_prop( e );
	}

	function init() {
		$( '.installer-password.icon' ).insertAfter( $( 'label.field-password input' ) );
		$( '.button.next' ).on( 'click', handle_auth );
		$( '.installer-password.icon' ).on( 'click', handle_password_field_type_change );
	}
	$( init );
})( jQuery );
</script>
		<?php
	}

	public function print_styles() {
		parent::print_styles();
		?>
.installer-password.icon {
	position: absolute;
	top: 2px;
	right: 14px;
	display: none;
}
.installer-password.icon.active {
	display: block;
}
.installer-password.icon svg {
	fill: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
	max-height: 16px;
	min-height: 16px;
	max-width: 16px;
	min-width: 16px;
}
label.field-password {
	position: relative;
}
label.field-password input {
	background-image: none !important;
	background-attachment:none !important;
}
		<?php
	}
}



// Source: lib/installer/src/lib/view/component/input/class-password.php

/**
 * Installer password input component
 *
 * @package shipper-installer
 */

abstract class Shinst_View_Cmp_Input_Password extends Shinst_View_Cmp_Input {

	public function get_type() {
		return 'password'; }

	public function get_name() {
		return 'password'; }
}



// Source: lib/installer/src/lib/view/component/input/class-text.php

/**
 * Installer password input component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Input_Text extends Shinst_View_Cmp_Input {

	public function __construct( $label ) {
		$this->set_title( $label );
	}

	public function get_label() {
		return $this->get_title();
	}

	public function get_safe_title() {
		return strtolower( preg_replace( '/[^a-z0-9]/i', '', $this->get_title() ) );
	}

	public function get_type() {
		return 'text'; }

	public function get_name() {
		return $this->get_safe_title(); }
	public function get_classes() {
		return $this->get_safe_title(); }

	public function get_placeholder() {
		return 'Enter your ' . strtolower( $this->get_title() );
	}
}



// Source: lib/installer/src/lib/view/component/notification/class-error.php

/**
 * Installer error notification component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Notification_Error extends Shinst_View_Cmp_Notification {

	public function get_type() {
		return 'error';
	}
}



// Source: lib/installer/src/lib/view/component/notification/class-info.php

/**
 * Installer error notification component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Notification_Info extends Shinst_View_Cmp_Notification {

	public function get_type() {
		return 'info';
	}
}



// Source: lib/installer/src/lib/view/component/notification/class-warning.php

/**
 * Installer warning notification component
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_Notification_Warning extends Shinst_View_Cmp_Notification {

	public function get_type() {
		return 'warning';
	}
}



// Source: lib/installer/src/lib/view/component/requirements/class-archive.php

/**
 * Installer requirements failed item: archive not found
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_RequirementsItem_Archive extends Shinst_View_Cmp_RequirementsItem {

	public function __construct() {
		parent::__construct( 'Archive is not found' );
		$this
			->add_component( new Shinst_View_Cmp_Subtitle( 'Overview' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'We were not able to find a site package archive in the same directory as the installer script.' ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'Status' ) )
			->add_component( new Shinst_View_Cmp_Notification_Error( 'Package archive not found.' ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'How To Fix' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'Please make sure you uploaded the site package you wish to restore to the same directory as this installer script.' ) );
	}

}



// Source: lib/installer/src/lib/view/component/requirements/class-basedir.php

/**
 * Installer requirements failed item: Open Basedir presence
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_RequirementsItem_Basedir extends Shinst_View_Cmp_RequirementsItem {

	public function __construct() {
		parent::__construct( 'Open_basedir restriction in effect' );
		$this
			->add_component( new Shinst_View_Cmp_Subtitle( 'Overview' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'PHP has a security measure called <b>open_basedir</b> which limits which files can be accessed by a PHP script. Usually, it’s set to your root directory or a couple of specific directories. Shipper needs to be able to write in the working directory, storage directory, temp directory, and log directory to work properly. Having open_basedir active is likely to cause migration failures.' ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'Status' ) )
			->add_component( new Shinst_View_Cmp_Notification_Warning( sprintf( 'Open_basedir restriction is in effect on %s.', Shinst_Model_Url::get_host(), @ini_get( 'max_execution_time' ) ) ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'How To Fix' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'We recommend disabling the <b>open_basedir</b> restriction during migrations to ensure things go smoothly.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '1. Go to your cPanel > Select PHP Version, and click on Switch to PHP Options link to see the default values of PHP options. Update the value of open_basedir to an empty value, and click on Apply and then Save.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '2. Open your php.ini file, comment the open_basedir rule, and restart your server. To comment a line of code in the php.ini file, you need to suffixit with a semicolon. So, to comment the open_basedir rule, add a semicolon in the beginning as in: “; open_basedir true”. Commenting is preferred to deleting the rule, in case you want to reactivate it after the migration.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '3. You can also edit the Apache configuration file to disable the PHP open_basedir restriction. Open the httpd.conf file. Add the following line of code at the end, and restart your web server.' ) )
			->add_component( new Shinst_View_Cmp_Code( 'php_admin_value open_basedir none' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '4. If none of the above works, you can ask your hosting support to turn off the open_basedir restriction for you.' ) );
	}

	public function get_status() {
		return is_writable( Shinst_Model_Fs_Path::get_working_dir() )
			? self::SEVERITY_WARNING
			: self::SEVERITY_ERROR;
	}
}



// Source: lib/installer/src/lib/view/component/requirements/class-exectime.php

/**
 * Installer requirements failed item: max_exec_time
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_RequirementsItem_Exectime extends Shinst_View_Cmp_RequirementsItem {

	public function __construct() {
		parent::__construct( 'Max Execution Time is low' );
		$this
			->add_component( new Shinst_View_Cmp_Subtitle( 'Overview' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'Max execution time defines how long a PHP script can run before it returns an error. Shipper will often require longer than the default setting, so we recommend increasing your Max Execution time to <b>120s or above</b> to ensure migrations have the best chance of succeeding.' ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'Status' ) )
			->add_component( new Shinst_View_Cmp_Notification_Warning( sprintf( 'Max execution time on %s is %s seconds', Shinst_Model_Url::get_host(), @ini_get( 'max_execution_time' ) ) ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'How To Fix' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'You can set the <b>max_execution_time</b> of your site to any value above 120s by using any of the following methods:' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '1. Go to your cPanel > Select PHP Version, and click on the Switch to PHP Options link to see the default values of your PHP options. Update the value of max_execution_time to 120s, and click on Apply and then Save.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '2. Connect to your site via FTP, and add the following line to your .htaccess file. Make sure you backup your .htaccess file before you edit it.' ) )
			->add_component( new Shinst_View_Cmp_Code( 'php_value max_execution_time 120' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '3. If you have access to the php.ini file, you can increase the execution time limit by adding the following line of code or updating it (if it exists already) in your php.ini file.' ) )
			->add_component( new Shinst_View_Cmp_Code( 'max_execution_time = 120;' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '4. If none of the above works, you can ask your hosting support to increase the max execution time for you.' ) );
	}

	public function get_status() {
		return self::SEVERITY_WARNING;
	}
}



// Source: lib/installer/src/lib/view/component/requirements/class-ok.php

/**
 * Installer requirements item success
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_RequirementsItem_Ok extends Shinst_View_Cmp_RequirementsItem {

	public function get_status() {
		return true;
	}
}



// Source: lib/installer/src/lib/view/component/requirements/class-phpversion.php

/**
 * Installer requirements failed item: PHP version
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_RequirementsItem_Phpversion extends Shinst_View_Cmp_RequirementsItem {

	public function __construct() {
		parent::__construct( 'PHP v5.5 or newer is required' );
		$this
			->add_component( new Shinst_View_Cmp_Subtitle( 'Overview' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'PHP is the scripting language that powers WordPress under the hood. New versions are released over time that brings both speed and security improvements. It’s important to use the latest and greatest tools, so older PHP versions eventually cease to be supported.' ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'Status' ) )
			->add_component( new Shinst_View_Cmp_Notification_Error( sprintf( '%s is using PHP %s, and Shipper needs PHP 5.5 or above to work.', Shinst_Model_Url::get_host(), phpversion() ) ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'How To Fix' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'You need to upgrade your PHP version to the latest stable release. You can either contact your hosting provider and ask them to update your PHP version, or do it yourself following an official WordPress tutorial <a href="https://wordpress.org/support/update-php/" target="_blank">on updating your PHP version</a>.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '<b>Note:</b> Make sure you run a full backup of your website before updating your PHP version. <a href="https://wpmudev.com/project/snapshot/" target="_blank">Snapshot Pro</a> can help you with this!' ) );
	}
}



// Source: lib/installer/src/lib/view/component/requirements/class-zip.php

/**
 * Installer requirements failed item: ZIP archive support
 *
 * @package shipper-installer
 */

class Shinst_View_Cmp_RequirementsItem_Zip extends Shinst_View_Cmp_RequirementsItem {

	public function __construct() {
		parent::__construct( 'Zip support is not found' );
		$this
			->add_component( new Shinst_View_Cmp_Subtitle( 'Overview' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'Shipper uses PHP\'s built-in ZipArchive class to zip your files on your source website and unzip them on your destination. You need to have this module available on both sites for the migration to run.' ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'Status' ) )
			->add_component( new Shinst_View_Cmp_Notification_Error( sprintf( 'PHP ZipArchive class not found on %s.', Shinst_Model_Url::get_host() ) ) )

			->add_component( new Shinst_View_Cmp_Subtitle( 'How To Fix' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( 'You need to make sure the ZipArchive PHP extension is installed and available to use. You can use any of the following methods to install the extension:' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '1. Most hosts have the ZipArchive extension installed and available by default, but it may not be active. Open your cPanel, and under the Software section, click on select the PHP version option. You\'ll see your current PHP version, extensions available, and active PHP extensions. Check the zip option, and click on save to activate it. Note that if the zip option is not available in this list, please contact your hosting support, and ask them to install zip extension for you.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '2. If you have your own VPS, you must install the zip extension, and restart your server. You can ask your sysadmin to install the zip extension.' ) )
			->add_component( new Shinst_View_Cmp_Paragraph( '3. If none of the above works, you can ask your hosting support or your system admin to install the zip extension on your server.' ) );
	}

}



// Source: lib/installer/src/lib/view/page/class-connection.php

/**
 * Installer database connection page view
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Connection extends Shinst_View_Page {

	public function __construct() {
		$title = 'Database Connection';
		$ctrl  = new Shinst_Controller_Page();
		parent::__construct( $title );
		$next = new Shinst_View_Cmp_Button_Next();
		$next->set_title( 'Test Connection & Deploy' );

		$is_flywheel = Shinst_Model_Env::is_flywheel();
		$content     = $is_flywheel
			? 'We’ve detected that your site is hosted with Flywheel. As Flywheel doesn’t allow replacing core files and the database prefix, please do not change your prefix to a new one. Make sure that your prefix below matches the default prefix inside the Flywheel database.'
			: 'Let’s connect to your database. We recommend creating a new database. However, if you are using an existing database, please use a different prefix to avoid any data loss.';

		$this
			->add_to_body( new Shinst_View_Cmp_Paragraph( $content ) )
			->add_to_body( new Shinst_View_Cmp_Notification_Error( 'NA' ) )
			->add_to_body(
				new Shinst_View_Cmp_Notification_Warning(
					sprintf(
						'We detected existing tables in your database using the prefix you supplied. <a href="%s">Click here to proceed using this prefix</a> - <b>note:</b> this will destroy the data you already have in your existing tables. Alternatively, you can re-test the connection with the conflict-free table prefix we automatically created for you below.',
						$ctrl->get_page_url( 'deploy' )
					)
				)
			);

		// need to check if we allow to copy from wp-config
		if ( Shinst_Model_Fs_Path::is_wpconfig_located() ) {
			$this->add_to_body(
				new Shinst_View_Cmp_Notification_Info(
					sprintf(
						'<div><strong>Fetch database credentials from the config file</strong><label class="sui-toggle">
	<input type="checkbox" id="use-wpconfig">
	<span class="sui-toggle-slider"></span>
</label></div><p>
Trying to override an existing WordPress installation? Enable this option to fetch the database credentials from the existing wp-config.php file automatically. 
</p>'
					)
				)
			);
		}
		$this
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'Database Host' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'Port' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'Database Name' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'Database Username' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'Database Password' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'Table Prefix' ) )
			->add_to_body( $next );

	}

	public function print_styles() {
		?>
		.notification.error, .notification.warning, .notification.info {
		margin: 30px auto;
		display: none;
		}
		.notification.error .icon, .notification.warning .icon, .notification.info .icon {
		max-width: 16px;
		min-width: 16px;
		}
		.notification.info p{
		color:#888888;
		font-size: 13px;
		line-height: 22px;
		}
		label {
		display: block;
		width: 100%;
		float: left;
		margin-bottom: 20px;
		}
		label.port {
		width: 80px;
		margin-left: 10px;
		}
		label.databasehost {
		width: calc( 100% - 90px );
		}
		a.button.testconnectiondeploy {
		margin-top: 10px;
		float: right;
		}

		.sui-toggle {
		position: relative;
		display: inline-block;
		width: 34px;
		height: 16px;
		margin-right: 10px;
		float:right
		}
		.sui-toggle input{
		visibility:hidden;
		}
		.sui-toggle.sui-toggle-label {
		top: 3px;
		}
		.sui-toggle + label {
		font-weight: 500;
		}
		.sui-toggle-label {
		vertical-align: text-bottom;
		line-height: 22px;
		font-weight: 500;
		}
		.sui-toggle-content,  .sui-toggle-content.sui-border-frame {
		margin-left: 48px;
		}
		.sui-toggle input[type=checkbox]:checked[disabled] + .sui-toggle-slider {
		background-color: #DDDDDD;
		}
		.sui-toggle input[type=checkbox]:checked[disabled] + .sui-toggle-slider:hover {
		box-shadow: none;
		}
		.sui-toggle input[type=checkbox][disabled] + .sui-toggle-slider {
		opacity: 0.5;
		cursor: not-allowed;
		}
		.sui-toggle-slider {
		position: absolute;
		cursor: pointer;
		width: 34px;
		height: 16px;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #AAAAAA;
		border-radius: 8px;
		transition: 0.4s;
		border: none;
		}
		.sui-toggle-slider:hover {
		box-shadow: 0 0 0 5px #F2F2F2;
		}
		@media (-ms-high-contrast: active) {
		.sui-toggle-slider {
		-ms-high-contrast-adjust: none;
		}
		}
		.sui-toggle-slider:before {
		position: absolute;
		content: "";
		height: 14px;
		width: 14px;
		top: 1px;
		left: 1px;
		background-color: #FFFFFF;
		border-radius: 50%;
		transition: 0.2s;
		}
		input[type=checkbox]:checked + .sui-toggle-slider {
		background-color: #17A8E3;
		}
		input[type=checkbox]:checked + .sui-toggle-slider:before {
		transform: translateX(18px);
		}
		input[type=checkbox]:checked + .sui-toggle-slider:hover {
		box-shadow: 0 0 0 5px #E1F6FF;
		}
		<?php
		parent::print_styles();
	}

	public function print_scripts() {
		$table_prefix = $this->get_table_prefix();
		?>
		<script>
			;(function ($) {

				function show_error(msg) {
					$('.notification.error')
						.find('.content').text(msg).end()
						.css('display', 'flex');
				}

				function show_prefix_warning(data) {
					var newpfx = (data || {}).prefix;
					$('.field-text.tableprefix input').val(newpfx);
					$('.notification.warning')
						.css('display', 'flex');
				}

				function show_wpconfig_fetch() {
					$('.notification.info')
						.css('display', 'flex');
				}

				function handle_test_connection(e) {
					$('.notification.error').hide();
					$('.button.testconnectiondeploy')
						.find('.text').text('Connecting...').end()
						.find('.icon svg').hide().end()
						.attr('disabled', true);
					var obj = {
						host: $('.field-text.databasehost input').val(),
						port: $('.field-text.port input').val(),
						name: $('.field-text.databasename input').val(),
						username: $('.field-text.databaseusername input').val(),
						password: $('.field-text.databasepassword input').val(),
						prefix: $('.field-text.tableprefix input').val(),
					}
					if($('#use-wpconfig').size() > 0){
						obj.fetch = $('#use-wpconfig').prop('checked')
					}
					shinst.ajax('connection',obj)
						.done(function (resp) {
							if (!!resp.match(/prefix/)) {
								// Table prefix warning.
								return show_prefix_warning(JSON.parse(resp));
							}
							shinst.redirect('deploy');
						})
						.fail(function (rq) {
							show_error(rq.responseText);
						})
						.always(function () {
							$('.button.testconnectiondeploy')
								.find('.text').text('Test Connection & Deploy').end()
								.find('.icon svg').show().end()
								.attr('disabled', false);
						})
					;
					return shinst.stop_prop(e);
				}

				function handle_fetch_wpconfig(e) {
					if ($(this).prop('checked') == true) {
						$('.field-text:not(.tableprefix)').hide();
					} else {
						$('.field-text:not(.tableprefix)').show();
					}
				}

				function init() {
					$(document).on(
						'click',
						'.button.testconnectiondeploy',
						handle_test_connection
					);
					var $port = $('.field-text.port input');
					if (!$port.val()) {
						$port.val('3306');
					}
					var $prefix = $('.field-text.tableprefix input');
					if (!$prefix.val()) {
						$prefix.val('<?php echo $table_prefix; ?>');
					}
					$(document).on(
						'change',
						'#use-wpconfig',
						handle_fetch_wpconfig
					);
					if ($('#use-wpconfig').size() > 0) {
						show_wpconfig_fetch();
					}
				}

				$(init);

			})(jQuery);
		</script>
		<?php
		parent::print_scripts();
	}

	public function get_table_prefix() {
		$prefix = '';

		if ( Shinst_Model_Env::is_flywheel() ) {
			return shinst_read_wpconfig()['table_prefix'];
		}

		try {
			Shinst_Model_Fs_Archive::extract_manifest();
			$prefix = Shinst_Model_Manifest::get()->get_value( 'table_prefix' );
		} catch ( Exception $e ) {
			$prefix = '';
		}

		return $prefix;
	}
}



// Source: lib/installer/src/lib/view/page/class-deploy.php

/**
 * Installer deployment page
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Deploy extends Shinst_View_Page {

	public function __construct() {
		parent::__construct( 'Deploy Website' );
		$this->add_to_body(
			new Shinst_View_Cmp_Paragraph( 'Please keep this window open while we deploy your website. This can take anywhere from a few seconds to a few minutes depending upon the size of your archive and database.' )
		);
		$this->add_to_body( new Shinst_View_Cmp_Progress() );

		// Update page title because it's different than sidebar.
		$body = $this->get_body();
		foreach ( $body->get_components() as $cmp ) {
			if ( ! ( $cmp instanceof Shinst_View_Cmp_Title ) ) {
				continue;
			}
			$cmp->set_title( 'Deploying Website' );
			break;
		}
	}

	public function print_scripts() {
		?>
<script>
;( function( $ ) {

	var _steps = [
		{ step: 'Unpacking Archive', endpoint: 'unpack', dfr: new $.Deferred },
		{ step: 'Analyzing Restore', endpoint: 'analyze', dfr: new $.Deferred },
		{ step: 'Restoring Files', endpoint: 'files', dfr: new $.Deferred },
	];
	var _step = false;

	function get_steps() {
		return _steps;
	}

	function get_next_step() {
		var steps = get_steps(),
			next = false;
		$.each( get_steps(), function( idx, step ) {
			if ( 'pending' === step.dfr.state() ) {
				next = step;
				return false;
			}
		} );
		return next;
	}

	function get_step_index( step ) {
		var index = 0;
		$.each( get_steps(), function( idx, s ) {
			if ( step.step === s.step ) {
				index = idx;
				return false;
			}
		} );
		return index;
	}

	function get_overall_progress( step, step_percentage ) {
		var step_index = get_step_index( step ),
			total_steps = get_steps().length,
			per_step = 1 / total_steps,
			total_clean = ( step_index * per_step ) * 100,
			total_step = ( step_percentage * per_step );
		return total_clean + total_step;
	}

	function update_progress( percentage, msg ) {
		percentage = parseInt( percentage, 10 ) + '%';
		var $bar = $( '.progress .status-bar' ),
			$percentage = $( '.progress .status-percentage' ),
			$message = $( '.progress .status-message' );

		$bar.css( 'width', percentage );
		$percentage.text( percentage );
		if ( ( msg || '' ).length ) {
			$message.text( msg );
		}
	}

	function process_step() {
		var step = get_next_step();
		if ( false === step ) {
			update_progress( 100, 'All done!' );
			_step = false;
			shinst.redirect( 'update' );
			return false;
		}
		if ( _step !== step.step ) {
			update_progress( get_overall_progress( step, 1 ), step.step + '...' );
			_step = step.step;
		}
		shinst.ajax( 'deploy', { endpoint: step.endpoint } )
			.done( function( data ) {
				data = JSON.parse( data );
				var is_done = ( data || {} ).is_done,
					percentage = ( data || {} ).percentage || 0;
				if ( is_done ) {
					step.dfr.resolve();
				}
				if ( percentage ) {
					update_progress( get_overall_progress( step, percentage ) );
				}
				setTimeout( process_step, 1000 );
			} )
			.fail( function( rq ) {
				shinst.redirect( 'error' );
			} )
		;
	}

	function init() {
		process_step();
	}

	$( init );

} )( jQuery );
</script>
		<?php
		parent::print_scripts();
	}
}



// Source: lib/installer/src/lib/view/page/class-error.php

/**
 * Installer error page
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Error extends Shinst_View_Page {

	public function is_protected() {
		return false;
	}

	private $_exception;

	public function __construct( $exception = false ) {
		if ( $exception && is_object( $exception ) && $exception instanceof Exception ) {
			$this->_exception = $exception;
		}
	}

	public function print_markup() {
		$e = $this->_exception;

		$ctrl         = new Shinst_Controller_Front();
		$log          = new Shinst_View_Cmp_Logs( 'Log File' );
		$msg          = ! empty( $e )
			? $e->getMessage()
			: ( $ctrl->is_user_allowed()
				? 'Please check the log below'
				: 'You are not authorized to view the log'
			);
		$notification = new Shinst_View_Cmp_Notification_Error( $msg );
		?>
<html>
	<head>
		<title>Error: Shipper Installer</title>
		<style>
			body {
				font-family: sans-serif;
			}
			header, main {
				width: 60%;
				min-width: 800px;
			}
			header {
				margin: 4em auto;
			}
			main {
				margin: 0 auto;
			}
			main pre {
				border-left: 2px solid <?php echo Shinst_Model_Style::COLOR_ERROR; ?>;
				padding: 1em;
				overflow-x: scroll;
			}
			div.error-msg {
				font-size: 1.5em;
			}
			<?php $notification->print_styles(); ?>
			<?php $log->print_styles(); ?>
		</style>
	</head>
	<body>
		<header>
		<h1>We encountered an error!</h1>
		<?php if ( ! empty( $e ) ) { ?>
			<p>
				<code><?php echo $e->getFile(); ?></code> at line
				<?php echo $e->getLine(); ?> said:
			</p>
		<?php } ?>
			<div class="error-msg"><?php echo $notification->print_markup(); ?></div>
		</header>
		<main>
		<?php if ( ! empty( $e ) ) { ?>
			<pre><code><?php echo $e->getTraceAsString(); ?></code></pre>
		<?php } ?>
		<?php $log->print_markup(); ?>
		</main>
	</body>
</html>
		<?php
	}
}



// Source: lib/installer/src/lib/view/page/class-finish.php

/**
 * Installer finalization page
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Finish extends Shinst_View_Page {

	public function is_protected() {
		return false;
	}

	public function __construct() {
		parent::__construct( 'Finish & Cleanup' );
		$this->add_to_body(
			new Shinst_View_Cmp_Paragraph( 'Your website is ready! Please login to your new website and make sure everything looks perfect. Once confirmed, please return here and run the cleanup script to clear the migration files from your server. ' )
		);

		$this->add_to_body( new Shinst_View_Cmp_Button_Admin() );

		$next = new Shinst_View_Cmp_Button_Next();
		$next->set_title( 'Run Cleanup' );
		$this->add_to_body( $next );
	}

	public function print_scripts() {
		?>
<script>
;( function( $ ) {

	var admin_login;

	function run_cleanup( e ) {
		shinst.ajax( 'cleanup' )
			.done( function( data ) {
				data = JSON.parse( data );
				var done = ( data || {} ).is_done,
					markup = ( data || {} ).markup;
				if ( markup ) {
					$( 'main' ).replaceWith( markup );
				}
				if ( ! done ) {
					return setTimeout( run_cleanup, 1000 );
				}

				return setTimeout( function() {
					window.location.replace( admin_login );
				}, 3000 );
			} )
			.fail( function( rq ) {
				shinst.redirect( 'error' );
			} );
		return shinst.stop_prop( e );
	}

	function init() {
		admin_login = $( '.button.adminlogin' ).attr( 'href' );
		$( '.button.adminlogin' ).attr( 'target', '_blank' );
		$( document ).on(
			'click',
			'.button.runcleanup',
			run_cleanup
		);
	}

	$( init );

} )( jQuery );
</script>
		<?php
		parent::print_scripts();
	}

	public function print_styles() {
		?>
nav.top .button.back {
	display: none;
}
main .button {
	margin-top: 20px;
}
.button.runcleanup {
	float: right;
}
.button.adminlogin {
	background: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/page/class-logs.php

/**
 * Installer logs page view
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Logs extends Shinst_View_Page {

	public function __construct() {
	}

	public function is_protected() {
		return false;
	}

	public function print_markup() {
		$log = new Shinst_View_Cmp_Logs();
		?>
<html>
	<head>
		<title>Shipper Installer: View Logs</title>
		<style>
			body {
				font-family: sans-serif;
			}
			header, main {
				width: 60%;
				min-width: 800px;
			}
			header {
				margin: 4em auto;
			}
			main {
				margin: 0 auto;
			}
			<?php $log->print_styles(); ?>
		</style>
	</head>
	<body>
		<header>
			<h1>Installer Logs</h1>
		</header>
		<main>
			<?php $log->print_markup(); ?>
		</main>
	</body>
</html>
		<?php
	}
}



// Source: lib/installer/src/lib/view/page/class-password.php

/**
 * Installer password page view
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Password extends Shinst_View_Page {

	public function is_protected() {
		return false; // Password page is not protected.
	}

	public function __construct() {
		$title = 'Installer Password';
		parent::__construct( $title );
		$this
			->add_to_body(
				new Shinst_View_Cmp_Paragraph(
					'You’ve password protected your installer file. Please enter your chosen password to continue the migration process.'
				)
			)
			->add_to_body( new Shinst_View_Cmp_Input_InstallerPassword() )
			->add_to_body( new Shinst_View_Cmp_Button_Next() );
	}

	public function print_styles() {
		?>
nav.top .button.back {
	display: none;
}
main a.button.next {
	margin-top: 30px;
	float: right;
}
		<?php
		parent::print_styles();
	}

	public function print_scripts() {
		?>
<script>
;( function( $ ) {

	function send_next( e ) {
		if ( 13 === e.which ) {
			$( '.button.next' ).click();
			return shinst.stop_prop( e );
		}
	}

	function init() {
		$( 'input' ).on( 'keypress', send_next );
	}

	$( init );
} )( jQuery );
</script>
		<?php
		parent::print_scripts();
	}
}



// Source: lib/installer/src/lib/view/page/class-requirements.php

/**
 * Requirements page view
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Requirements extends Shinst_View_Page {

	public function __construct() {
		parent::__construct( 'Requirements Check' );
		$this->add_to_body(
			new Shinst_View_Cmp_Paragraph( 'We are looking for any issues that might prevent a successful migration. This will only take a couple of seconds.' )
		);
		$this->add_to_body( new Shinst_View_Cmp_Progress() );
	}

	public function print_scripts() {
		?>
<script>
;( function( $ ) {

	function update_progress( percentage, msg ) {
		percentage = parseInt( percentage, 10 ) + '%';
		var $bar = $( '.progress .status-bar' ),
			$percentage = $( '.progress .status-percentage' ),
			$message = $( '.progress .status-message' );

		$bar.css( 'width', percentage );
		$percentage.text( percentage );
		if ( msg.length ) {
			$message.text( msg );
		}
	}

	function attempt_progress() {
		var $items = $( '.requirements-item' ),
			$failures = $items.filter( '.failure' ),
			$errors = $items.filter( '.error' );
		if ( $failures.length ) {
			$( '.button.continueanyway' ).attr( 'disabled', !!$errors.length );
			return false;
		}

		shinst.redirect( 'connection' );
	}

	function check_requirements() {
		shinst.ajax( 'requirements' )
			.done( function( data ) {
				if ( $( data ).find( '.failure' ).length ) {
					$( 'main' ).replaceWith( data );
					$( 'main' ).addClass( 'has-results' );
					attempt_progress();
				} else {
					shinst.redirect( 'connection' );
				}
			} )
			.fail( function() {
				shinst.redirect( 'error' );
			} )
		;
	}

	function start_requirements_check() {
		$( 'main' ).removeClass( 'has-results' );
		update_progress( 0, 'Connecting' );
		setTimeout( function() {
			update_progress( 50, 'Checking Archive File...' );
			check_requirements();
		}, 500 );
	}

	function init () {
		start_requirements_check();
		$( document ).on( 'click', '.button.recheck', function( e ) {
			window.location.reload();
			return shinst.stop_prop( e );
		} );
		$( document ).on(
			'click',
			'.requirements-item.has-content.open .requirements-item-title',
			function( e ) {
				$( this ).closest( '.requirements-item' )
					.removeClass( 'open' )
					.addClass( 'closed' );
				return shinst.stop_prop( e );
			}
		);
		$( document ).on(
			'click',
			'.requirements-item.has-content.closed .requirements-item-title',
			function( e ) {
				$( this ).closest( '.requirements-item' )
					.removeClass( 'closed' )
					.addClass( 'open' );
				return shinst.stop_prop( e );
			}
		);
	}

	$( init );

})( jQuery );
</script>
		<?php
		parent::print_scripts();
	}

	public function print_styles() {
		?>
main.has-results section {
	width: 600px;
}
.button.continueanyway {
	float: right;
	margin-top: 30px;
}
.button.recheck {
	background: <?php echo Shinst_Model_Style::GHOST_FG; ?>;
	margin-top: 30px;
}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/page/class-update.php

/**
 * Installer update page
 *
 * @package shipper-installer
 */

class Shinst_View_Page_Update extends Shinst_View_Page {

	public function __construct() {
		$title = 'Update Data';
		parent::__construct( $title );
		$next = new Shinst_View_Cmp_Button_Next();
		$next->set_title( 'Update' );
		$this
			->add_to_body(
				new Shinst_View_Cmp_Paragraph( 'Following are the details of your new website. Make sure everything is correct and click on the Update button to finish the migration.' )
			)
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'New Site URL' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'New Site Path' ) )
			->add_to_body( new Shinst_View_Cmp_Input_Text( 'New Site Title' ) )
			->add_to_body( $next );
	}

	public function print_scripts() {
		?>
<script>
;( function( $ ) {

	var _steps = [
		{ step: 'Updating Database', endpoint: 'tables', dfr: new $.Deferred },
		{ step: 'Updating config files', endpoint: 'files', dfr: new $.Deferred },
		{ step: 'Updating site title', endpoint: 'title', dfr: new $.Deferred },
		{ step: 'Finalizing...', endpoint: 'finalize', dfr: new $.Deferred },
	];
	var _step = false;

	function get_steps() {
		return _steps;
	}

	function get_next_step() {
		var steps = get_steps(),
			next = false;
		$.each( get_steps(), function( idx, step ) {
			if ( 'pending' === step.dfr.state() ) {
				next = step;
				return false;
			}
		} );
		return next;
	}

	function get_step_index( step ) {
		var index = 0;
		$.each( get_steps(), function( idx, s ) {
			if ( step.step === s.step ) {
				index = idx;
				return false;
			}
		} );
		return index;
	}

	function get_overall_progress( step, step_percentage ) {
		var step_index = get_step_index( step ),
			total_steps = get_steps().length,
			per_step = 1 / total_steps,
			total_clean = ( step_index * per_step ) * 100,
			total_step = ( step_percentage * per_step );

		return total_clean + total_step;
	}

	function update_progress( percentage, msg ) {
		percentage = parseInt( percentage, 10 ) + '%';
		var $bar = $( '.progress .status-bar' ),
			$percentage = $( '.progress .status-percentage' ),
			$message = $( '.progress .status-message' );

		$bar.css( 'width', percentage );
		$percentage.text( percentage );
		if ( ( msg || '' ).length ) {
			$message.text( msg );
		}
	}

	function process_step() {
		var step = get_next_step();
		if ( false === step ) {
			update_progress( 100, 'All done!' );
			_step = false;
			shinst.redirect( 'finish' );
			return false;
		}
		if ( _step !== step.step ) {
			update_progress( get_overall_progress( step, 1 ), step.step + '...' );
			_step = step.step;
		}
		shinst.ajax( 'update', { endpoint: step.endpoint } )
			.done( function( data ) {
				data = JSON.parse( data );
				var is_done = ( data || {} ).is_done,
					percentage = ( data || {} ).percentage || 0;
				if ( is_done ) {
					step.dfr.resolve();
				}
				if ( percentage ) {
					update_progress( get_overall_progress( step, percentage ) );
				}
				setTimeout( process_step, 1000 );
			} )
			.fail( function( rq ) {
				shinst.redirect( 'error' );
			} )
		;
	}

	function submit_new_data( e ) {
		shinst.ajax( 'update', {
			endpoint: 'save',
			url: $( '.field-text.newsiteurl input' ).val(),
			path: $( '.field-text.newsitepath input' ).val(),
			title: $( '.field-text.newsitetitle input' ).val(),
		} )
			.done( function( data ) {
				$( 'main' ).replaceWith( data );
				process_step();
			} )
			.fail( function( rq ) {
				shinst.redirect( 'error' );
			} )
		;

		return shinst.stop_prop( e );
	}

	function set_default_value( where, val ) {
		val = val || '<?php echo Shinst_Model_Url::get_root_url(); ?>';
		var $field = $( '.field-text' + where + ' input' );
		if ( ! $field.val() ) $field.val( val );
	}

	function init() {
		set_default_value( '.newsiteurl' );
		set_default_value( '.newsitepath' );
		set_default_value( '.newsitetitle', 'Destination Website' );
		$( '.button.update' ).on( 'click', submit_new_data );
	}

	$( init );

} )( jQuery );
</script>
		<?php
		parent::print_scripts();
	}

	public function print_styles() {
		?>
	nav.top .button.back {
		display: none;
	}
	label {
		display: block;
		margin-bottom: 20px;
	}
	a.button.update {
		margin-top: 10px;
		float: right;
	}
		<?php
		parent::print_styles();
	}
}



// Source: lib/installer/src/lib/view/svg/class-alert.php

/**
 * Warning-alert SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Alert extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M512 0c70.667 0 137 13.333 199 40 62.667 26.667 117.166 63.166 163.5 109.5S957.333 250.333 984 313c26.667 62 40 128.333 40 199s-13.333 137-40 199c-26.667 62.667-63.166 117.166-109.5 163.5S773.667 957.333 711 984c-62 26.667-128.333 40-199 40s-137-13.333-199-40c-62.667-26.667-117.166-63.166-163.5-109.5S66.667 773.667 40 711C13.333 649 0 582.667 0 512s13.333-137 40-199c26.667-62.667 63.166-117.166 109.5-163.5S250.333 66.667 313 40C375 13.333 441.333 0 512 0zm0 256c-35.346 0-64 28.654-64 64v192c0 35.346 28.654 64 64 64s64-28.654 64-64V320c0-35.346-28.654-64-64-64zm0 512c35.346 0 64-28.654 64-64s-28.654-64-64-64c-35.346 0-64 28.654-64 64s28.654 64 64 64z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-arrow.php

/**
 * Arrow SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Arrow extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M963.754 491.008L574.805 102.4c-5.491-5.46-13.061-8.836-21.419-8.836s-15.927 3.375-21.42 8.837l-62.463 62.121c-5.46 5.491-8.836 13.061-8.836 21.419s3.375 15.927 8.837 21.42L700.073 437.76H78.164h-.001c-14.644 0-26.527 11.823-26.623 26.444v95.583c0 14.704 11.92 26.624 26.624 26.624h621.909l-230.4 230.4c-5.46 5.491-8.836 13.061-8.836 21.419s3.375 15.927 8.837 21.42l62.292 61.951c5.491 5.46 13.061 8.836 21.419 8.836s15.927-3.375 21.42-8.837l388.948-388.607c5.38-5.401 8.706-12.851 8.706-21.077s-3.326-15.677-8.707-21.078z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-blindeye.php

/**
 * Blind Eye SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Blindeye extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M201.216 121.003c-2.596-2.605-6.187-4.217-10.155-4.217s-7.559 1.612-10.154 4.216l-61.44 60.928c-2.605 2.596-4.217 6.187-4.217 10.155s1.612 7.559 4.216 10.154l701.27 700.758c2.596 2.605 6.187 4.217 10.155 4.217s7.559-1.612 10.154-4.216l60.928-60.928c2.605-2.596 4.217-6.187 4.217-10.155s-1.612-7.559-4.216-10.154zM665.6 503.467c-4.372-78.297-66.77-140.695-144.666-145.049zm-307.2 19.114c5.285 76.768 66.251 137.734 142.541 142.992zM512 288.085l.818-.002c123.288 0 223.232 99.944 223.232 223.232 0 19.895-2.603 39.181-7.486 57.539l150.881 148.97c60.159-57.039 108.919-125.422 142.957-201.809S887.467 159.914 512 159.914c-.695-.003-1.517-.005-2.339-.005-58.177 0-114.196 9.241-166.669 26.335l113.712 108.838c16.592-4.431 35.643-6.983 55.287-6.997zm0 448l-.768.001C387.944 736.086 288 636.142 288 512.854c0-19.337 2.459-38.1 7.081-55.993L143.701 307.2C83.962 364.008 35.501 432.036 1.599 507.99S136.533 863.915 512 863.915c.727.004 1.587.006 2.447.006 57.572 0 113.029-9.053 165.026-25.812l-113.884-109.02c-16.056 4.238-34.509 6.722-53.525 6.826z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-check.php

/**
 * Checkmark SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Check extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M874.008 149.931C781.357 57.295 653.369 0 512 0 229.23 0 0 229.23 0 512s229.23 512 512 512c141.37 0 269.358-57.295 362.009-149.932C966.68 781.412 1024 653.4 1024 512s-57.32-269.412-149.991-362.068zM724.675 424.875L473.283 676.267c-10.683 10.67-25.435 17.268-41.728 17.268s-31.045-6.599-41.729-17.269L263.021 549.461c-6.868-6.884-11.116-16.386-11.116-26.88s4.247-19.996 11.116-26.881l29.695-29.695c6.893-6.919 16.429-11.201 26.965-11.201s20.073 4.282 26.964 11.2l85.335 85.335 209.067-210.091c6.884-6.868 16.386-11.116 26.88-11.116s19.996 4.247 26.881 11.116l29.695 29.695c6.868 6.884 11.116 16.386 11.116 26.88s-4.247 19.996-11.116 26.881z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-chevron.php

/**
 * Chevron SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Chevron extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M921.583 371.441a51.346 51.346 0 0 1 12.847 18.431c2.739 6.961 4.255 14.629 4.255 22.65 0 .738-.013 1.472-.038 2.204.004.035.005.201.005.368a59.02 59.02 0 0 1-4.757 23.293 59.984 59.984 0 0 1-12.321 18.397L555.333 823.878a73.911 73.911 0 0 1-19.153 12.272c-7.479 3.363-15.665 5.215-24.282 5.215s-16.803-1.851-24.179-5.178a74.422 74.422 0 0 1-19.334-12.377L102.384 456.775a60.152 60.152 0 0 1-12.31-18.382 58.719 58.719 0 0 1-4.757-23.802l-.001-.246a61.773 61.773 0 0 1 4.76-23.857c2.613-7.223 6.867-13.729 12.297-19.037l42.506-43.359a76.148 76.148 0 0 1 18.81-12.263c7.349-3.353 15.388-5.195 23.857-5.195s16.508 1.841 23.738 5.146a75.906 75.906 0 0 1 19.004 12.379L511.983 609.18l280.576-279.723c10.689-10.554 25.384-17.073 41.602-17.073.314 0 .628.002.941.007l.198-.001a62.775 62.775 0 0 1 24.065 4.762c6.98 2.849 13.25 7.256 18.322 12.78l43.895 41.509z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-documentation.php

/**
 * Documentation SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Documentation extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M487.166 877.106c-66.404 0-348.164-62.974-348.164-189.082V546.712L476.15 683.261c3.235 1.348 6.993 2.133 10.938 2.133s7.704-.783 11.127-2.201L831.448 548.25l3.724 1.843v138.239c0 126.104-281.604 189.082-348.01 189.082l.004-.308zM1023.687 896l-65.319-78.49L892.894 896V532.431v-16.742L709.503 423.53 1024 492.65l-.313 403.351zm-74.63-456.96l-358.248-69.58c-4.658-.072-7.647 2.885-7.647 6.532a6.568 6.568 0 0 0 2.665 5.281l193.028 126.732-291.689 118.119L48.152 448.241C8.811 432.3-10.159 387.486 5.781 348.144a76.86 76.86 0 0 1 42.386-42.377L487.166 128 949.02 315.022c34.251 13.869 50.773 52.879 36.904 87.129a66.907 66.907 0 0 1-36.867 36.889z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-eye.php

/**
 * Eye SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Eye extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M512 736.085c123.759 0 224.085-100.326 224.085-224.085S635.759 287.915 512 287.915c-123.759 0-224.085 100.326-224.085 224.085S388.241 736.085 512 736.085zM0 512s136.533-352.085 512-352.085S1024 512 1024 512 887.467 864.085 512 864.085 0 512 0 512zm512 95.915c52.972 0 95.915-42.942 95.915-95.915S564.973 416.085 512 416.085c-52.972 0-95.915 42.942-95.915 95.915s42.942 95.915 95.915 95.915z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-loader.php

/**
 * Loader SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Loader extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M512 0c-34.404 0-62.293 27.89-62.293 62.293v151.723c0 34.404 27.89 62.293 62.293 62.293s62.293-27.89 62.293-62.293V62.293C574.293 27.889 546.403 0 512 0zm0 747.691c-34.404 0-62.293 27.89-62.293 62.293v151.723c0 34.404 27.89 62.293 62.293 62.293s62.293-27.89 62.293-62.293V809.984c0-34.404-27.89-62.293-62.293-62.293zm210.603-384h.11c17.165 0 32.7-6.981 43.92-18.259L873.985 238.08c11.99-11.377 19.45-27.428 19.45-45.221 0-34.404-27.89-62.293-62.293-62.293-17.793 0-33.843 7.46-45.195 19.422L678.572 257.365c-11.263 11.271-18.229 26.838-18.229 44.032 0 34.392 27.871 62.275 62.26 62.293zM301.397 660.309h-.11c-17.165 0-32.7 6.981-43.92 18.259L150.015 785.92c-11.99 11.377-19.45 27.428-19.45 45.221 0 34.404 27.89 62.293 62.293 62.293 17.793 0 33.843-7.46 45.195-19.422l107.375-107.377c11.263-11.271 18.229-26.838 18.229-44.032 0-34.392-27.871-62.275-62.26-62.293zm660.31-210.602H809.984c-34.404 0-62.293 27.89-62.293 62.293s27.89 62.293 62.293 62.293h151.723c34.404 0 62.293-27.89 62.293-62.293s-27.89-62.293-62.293-62.293zM276.309 512c0-34.404-27.89-62.293-62.293-62.293H62.293C27.889 449.707 0 477.597 0 512s27.89 62.293 62.293 62.293h151.723c34.404 0 62.293-27.89 62.293-62.293zm490.326 166.571c-11.377-11.99-27.428-19.45-45.221-19.45-34.404 0-62.293 27.89-62.293 62.293 0 17.793 7.46 33.843 19.422 45.195L785.92 873.984c11.377 11.99 27.428 19.45 45.221 19.45 34.404 0 62.293-27.89 62.293-62.293 0-17.793-7.46-33.843-19.422-45.195zM238.08 150.016c-11.14-10.571-26.233-17.073-42.843-17.073-34.404 0-62.293 27.89-62.293 62.293 0 16.611 6.502 31.703 17.099 42.871l107.323 107.322c11.377 11.99 27.428 19.45 45.221 19.45 34.404 0 62.293-27.89 62.293-62.293 0-17.793-7.46-33.843-19.422-45.195z"/></svg>
		<?php
	}
}



// Source: lib/installer/src/lib/view/svg/class-repeat.php

/**
 * Repeat SVG icon view
 *
 * @package shipper-installer
 */

class Shinst_View_Svg_Repeat extends Shinst_View_Svg {

	public function print_markup() {
		?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path d="M311.743 737.378c.929.838 1.863 1.67 2.802 2.497 69.009 60.732 161.584 87.586 253.821 71.323 103.046-18.17 187.795-87.13 227.628-181.864 17.126-40.729 64.026-59.863 104.755-42.737s59.863 64.026 42.737 104.755c-60.693 144.344-190.177 249.705-347.337 277.416-140.63 24.797-282.185-16.266-387.309-108.781a465.842 465.842 0 0 1-16.422-15.167l-47.146 42.45a64.004 64.004 0 0 1-42.81 16.439c-35.346.008-64.006-28.639-64.014-63.986L38.4 624.021a63.868 63.868 0 0 1 .352-6.718c3.703-35.152 35.2-60.646 70.352-56.944l214.515 22.595a63.996 63.996 0 0 1 40.857 20.824c23.651 26.267 21.53 66.734-4.737 90.386l-47.997 43.216zm405.156-449.962a312.68 312.68 0 0 0-3.885-3.478c-69.009-60.732-161.584-87.586-253.821-71.323-103.046 18.17-187.795 87.13-227.628 181.864-17.126 40.729-64.026 59.863-104.755 42.737S66.947 373.19 84.073 332.461C144.766 188.117 274.25 82.756 431.41 55.045c140.63-24.797 282.185 16.266 387.309 108.781a465.935 465.935 0 0 1 17.467 16.183l48.274-43.466a64.004 64.004 0 0 1 42.81-16.439c35.346-.008 64.006 28.639 64.014 63.986l.048 215.702c0 2.244-.117 4.487-.352 6.718-3.703 35.152-35.2 60.646-70.352 56.944l-214.515-22.595a63.996 63.996 0 0 1-40.857-20.824c-23.651-26.267-21.53-66.734 4.737-90.386l46.906-42.235z"/></svg>
		<?php
	}
}




// Source: lib/installer/src/index.php

/**
 * WPMU DEV Shipper installer standalone script.
 *
 * This is where the process gets initialized.
 *
 * @package shipper-installer
 */

$ctrl = new Shinst_Controller_Front();
$ctrl->run();
