<?php
/*
Snapshots Plugin Destinations Dropbox
Author: Paul Menard (Incsub)
*/

if ( ( ! class_exists( 'SnapshotDestinationFTP' ) )
     && ( stristr( WPMUDEV_SNAPSHOT_DESTINATIONS_EXCLUDE, 'SnapshotDestinationFTP' ) === false )
) {

	class SnapshotDestinationFTP extends Snapshot_Model_Destination {

		// The slug and name are used to identify the Destination Class
		public $name_slug;
		public $name_display;

		// These vars are used when connecting and sending file to the destination. There is an
		// inteface function which populates these from the destination data.
		public $destination_info;
		public $error_array;
		public $sftp_connection;
		public $ftp_connection;
		public $form_errors;
		public $protocols;

		public function on_creation() {
			//private destination slug. Lowercase alpha (a-z) and dashes (-) only please! Must be unique for all destinations
			$this->name_slug = 'ftp';

			// The display name for listing on admin panels
			$this->name_display = __( 'FTP/sFTP', SNAPSHOT_I18N_DOMAIN );

			// On the details form we want to provide a 'test connection' button which will submit the form via AJAX to this script
			// where the form fields will be validated and the connection tot he remote server tested.
			add_action( 'wp_ajax_snapshot_destination_ftp', array( &$this, 'destination_ajax_proc' ) );
			$this->load_scripts();

			$this->protocols = array(
				'ftp' => __( 'FTP', SNAPSHOT_I18N_DOMAIN ),
				'sftp' => __( 'SFTP', SNAPSHOT_I18N_DOMAIN ),
                'ftps-tcl-ssl' => __( 'FTP with TSL/SSL', SNAPSHOT_I18N_DOMAIN )
			);
		}

		public function destination_ajax_proc() {
			$this->init();
			check_ajax_referer( 'snapshot-ajax-nonce', 'security' );

			//echo "_REQUEST<pre>"; print_r($_REQUEST); echo "</pre>";
			if ( ! isset( $_POST['snapshot_action'] ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Missing 'snapshot_action' value.";
				echo wp_json_encode( $this->error_array );
				die();
			}

			if ( ! isset( $_POST['destination_info'] ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: Missing 'destination_info' values.";
				echo wp_json_encode( $this->error_array );
				die();
			}
			$destination_info = $_POST['destination_info'];
			if ( ! $this->validate_form_data( $destination_info ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = implode( ', ', $this->form_errors );
				echo wp_json_encode( $this->error_array );
				die();
			}

			if ( ( "sftp" === $destination_info['protocol'] && version_compare( phpversion(), "5.3.8", "<" ) ) ) {
				$this->error_array['errorStatus']  = true;
				$this->error_array['errorArray'][] = "Error: the SFTP destination requires PHP v5.3.8+.";
				echo wp_json_encode( $this->error_array );
				die();
			}

			if ( sanitize_text_field( $_POST['snapshot_action'] ) === "connection-test" ) {
				$this->load_class_destination( $destination_info );
				//echo "destination_info<pre>"; print_r($this->destination_info); echo "</pre>";

				if ( ! $this->login() ) {
					echo wp_json_encode( $this->error_array );
					die();
				}
				if ( ! $this->set_remote_directory() ) {
					echo wp_json_encode( $this->error_array );
					die();
				}
				$this->error_array['responseArray'][] = "Success!";

			}
			echo wp_json_encode( $this->error_array );
			die();
		}

		public function load_scripts() {
			if ( ! isset( $_REQUEST['destination-noonce-field']  ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ) {
				return;
			}

			if ( ( ! isset( $_GET['page'] ) ) || ( ! in_array( sanitize_text_field( $_GET['page'] ), array( "snapshots_destinations_panel", "snapshot_pro_destinations" ), true ) ) ) {
				return;
			}

			if ( ( ! isset( $_GET['type'] ) ) || ( sanitize_text_field( $_GET['type'] ) !== $this->name_slug ) ) {
				return;
			}

			if( sanitize_text_field( $_GET['page'] ) === "snapshots_destinations_panel" ){
				wp_enqueue_script( 'snapshot-destination-ftp-js', plugins_url( '/js/snapshot_destination_ftp.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_style( 'snapshot-destination-ftp-css', plugins_url( '/css/snapshot_destination_ftp.css', __FILE__ ) );
			} else {
				wp_enqueue_script( 'new_snapshot-destination-ftp-js', plugins_url( '/js/snapshot_destination_ftp.js', __FILE__ ), array( 'jquery' ) );
			}

		}

		public function validate_form_data( $d_info ) {

			// Will contain the filtered fields from the form (d_info).
			$destination_info = array();
			$this->form_errors = array();

			$text_fields = array( 'type', 'name', 'address', 'username', 'password', 'protocol', 'passive', 'directory' );
			$number_fields = array( 'port', 'timeout' );

			$required_fields = array(
				'name' => __( 'Name is required', SNAPSHOT_I18N_DOMAIN ),
				'address' => __( "Address is required", SNAPSHOT_I18N_DOMAIN ),
				'username' => __( "Username is required", SNAPSHOT_I18N_DOMAIN ),
				'password' => __( "Password is required", SNAPSHOT_I18N_DOMAIN ),
				'protocol' => __( "Connection type is required", SNAPSHOT_I18N_DOMAIN ),
			);

			$destination_info = $this->validate_text_fields( $text_fields, $d_info, $destination_info, $required_fields );
			$destination_info = $this->validate_number_fields( $number_fields, $d_info, $destination_info, $required_fields );

			return $destination_info;
		}

		public function display_listing_table( $destinations, $edit_url, $delete_url ) {
			?>
			<table class="widefat">
				<thead>
				<tr class="form-field">
					<th class="snapshot-col-delete"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-server"><?php esc_html_e( 'Host', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-login"><?php esc_html_e( 'Login', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-directory"><?php esc_html_e( 'Directory', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<th class="snapshot-col-used"><?php esc_html_e( 'Used', SNAPSHOT_I18N_DOMAIN ); ?></th>
				</tr>
				<thead>
				<tbody>
				<?php
				if ( ( isset( $destinations ) ) && ( count( $destinations ) ) ) {

					//echo "destinations<pre>"; print_r($destinations); echo "</pre>";
					foreach ( $destinations as $idx => $item ) {

						//echo "idx=[". $idx ."] destination<pre>"; print_r($item); echo "</pre>";

						if ( ! isset( $row_class ) ) {
							$row_class = "";
						}
						$row_class = ( '' === $row_class ? 'alternate' : '' );

						?>
						<tr class="<?php echo esc_attr( $row_class ); ?>
						<?php
						if ( isset( $item['type'] ) ) {
							echo ' snapshot-row-filter-type-' . esc_attr( $item['type'] );
						}
                        ?>
                        ">
							<td class="snapshot-col-delete" style="width:5px;"><input type="checkbox"
							                                                          name="delete-bulk-destination[<?php echo esc_attr( $idx ); ?>]"
							                                                          id="delete-bulk-destination-<?php echo esc_attr( $idx ); ?>">
							</td>

							<td class="snapshot-col-name"><a
									href="<?php echo esc_url( $edit_url ); ?>item=<?php echo esc_attr( $idx ); ?>"><?php echo esc_html( stripslashes( $item['name'] ) ); ?></a>

								<div class="row-actions" style="margin:0; padding:0;">
									<span class="edit">
										<a href="<?php echo esc_url( $edit_url ); ?>item=<?php echo esc_attr( $idx ); ?>">
											<?php
											esc_html_e( 'edit', SNAPSHOT_I18N_DOMAIN );
                                            ?>
                                        </a>
                                    </span> |
                                    <span class="delete">
                                    	<a
											href="
                                            <?php
											echo esc_url( $delete_url );
                                            ?>
                                            item=<?php echo esc_attr( $idx ); ?>&amp;destination-noonce-field=
                                            <?php
											echo esc_attr( wp_create_nonce( 'snapshot-destination' ) );
                                            ?>
                                            "><?php esc_html_e( 'delete', SNAPSHOT_I18N_DOMAIN ); ?>
                                        </a>
                                    </span>
								</div>
							</td>
							<td class="snapshot-col-server">
                            <?php
								if ( isset( $item['address'] ) ) {
									echo esc_html( $item['address'] );
								}
								?>
                                </td>
							<td class="snapshot-col-username">
                            <?php
								if ( isset( $item['username'] ) ) {
									echo esc_html( $item['username'] );
								}
								?>
                                </td>
							<td class="snapshot-col-username">
                            <?php
								if ( isset( $item['directory'] ) ) {
									echo esc_html( $item['directory'] );
								}
								?>
                                </td>

							<td class="snapshot-col-used"><?php Snapshot_Model_Destination::show_destination_item_count( $idx ); ?></td>
						</tr>
					<?php
					}
				} else {
					?>
					<tr class="form-field">
					<td colspan="4"><?php esc_html_e( 'No FTP Destinations', SNAPSHOT_I18N_DOMAIN ); ?></td></tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<?php
			if ( ( isset( $destinations ) ) && ( count( $destinations ) ) ) {
				?>
				<div class="tablenav">
					<div class="alignleft actions">
						<input class="button-secondary" type="submit"
						       value="<?php esc_html_e( 'Delete Destination', SNAPSHOT_I18N_DOMAIN ); ?>"/>
					</div>
				</div>
			<?php
			}
		}

		public function display_details_form( $item = 0 ) {
			if ( ! $item ) {
				$item = array();
			}

			//echo "item<pre>"; print_r($item); echo "</pre>";
			?>
			<p><?php esc_html_e( 'Define an FTP destination connection. You can define multiple destinations which use FTP. Each destination can connect to different servers with different remote paths.', SNAPSHOT_I18N_DOMAIN ); ?></p>

			<div id="poststuff" class="metabox-holder">
			<div style="display: none" id="snapshot-destination-test-result"></div>
			<div class="postbox" id="snapshot-destination-item">

				<h3 class="hndle">
					<span><?php echo esc_html( $this->name_display ); ?> <?php esc_html_e( 'Destination', SNAPSHOT_I18N_DOMAIN ); ?></span>
				</h3>

				<div class="inside">

					<input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type"
					       value="<?php echo esc_attr( $this->name_slug ); ?>"/>
					<table class="form-table">
						<?php
						if ( ( isset( $this->form_errors ) ) && ( count( $this->form_errors ) ) ) {
							?>
							<tr class="form-field">
								<th scope="row"><?php esc_html_e( "Form Errors", SNAPSHOT_I18N_DOMAIN ); ?></th>
								<td>
									<ul>
										<?php
										foreach ( $this->form_errors as $error ) {
											?>
											<li><?php echo esc_html( $error ); ?></li>
											<?php
										}
										?>
									</ul>
								</td>
							</tr>
						<?php
						}
						?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-name"><?php esc_html_e( 'Destination Name', SNAPSHOT_I18N_DOMAIN ); ?></label>
								*
							</th>
							<td><input type="text" name="snapshot-destination[name]" id="snapshot-destination-name"
							           value="
                                       <?php
                                       if ( isset( $item['name'] ) ) {
								           echo esc_attr( stripslashes( $item['name'] ) );
							           }
                                       ?>
                                       "/>
							</td>
						</tr>

						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-address"><?php esc_html_e( 'Server Address', SNAPSHOT_I18N_DOMAIN ); ?></label>
								*
							</th>
							<td><input type="text" name="snapshot-destination[address]"
							           id="snapshot-destination-address"
							           value="
                                       <?php
                                       if ( isset( $item['address'] ) ) {
								           echo esc_attr( $item['address'] );
							           }
                                       ?>
                                       "/>

								<p class="description"><?php esc_html_e( 'This should remote server address as in somehost.co or ftp.somehost.com or maybe the IP address 123.456.789.255. Do not use ftp://somehost.com', SNAPSHOT_I18N_DOMAIN ); ?></p>
							</td>
						</tr>

						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-username"><?php esc_html_e( 'Username', SNAPSHOT_I18N_DOMAIN ); ?></label>
								*
							</th>
							<td><input type="text" name="snapshot-destination[username]"
							           id="snapshot-destination-username"
							           value="
                                       <?php
                                       if ( isset( $item['name'] ) ) {
								           echo esc_attr( $item['username'] );
							           }
                                       ?>
                                       "/></td>
						</tr>

						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-password"><?php esc_html_e( 'Password', SNAPSHOT_I18N_DOMAIN ); ?></label>
								*
							</th>
							<td><input type="password" name="snapshot-destination[password]"
							           id="snapshot-destination-password"
							           value="
                                       <?php
                                       if ( isset( $item['name'] ) ) {
								           echo esc_attr( $item['password'] );
							           }
                                       ?>
                                       "/></td>
						</tr>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-directory"><?php esc_html_e( 'Remote Path', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="text" name="snapshot-destination[directory]"
							           id="snapshot-destination-directory"
							           value="
                                       <?php
                                       if ( isset( $item['directory'] ) ) {
								           echo esc_attr( $item['directory'] );
							           }
                                       ?>
                                       "/>

								<p class="description"><?php esc_html_e( 'The remote path will be used to store the snapshot archives. The remote path must already existing on the server. If the remote path is blank then the FTP home directory will be used as the destination for snapshot files. This is a global setting and will be used by all snapshot configurations using this destination. You can also defined a directory used by a specific snapshot.', SNAPSHOT_I18N_DOMAIN ); ?></p>
							</td>
						</tr>

						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-protocol"><?php esc_html_e( 'Connection Protocol', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td>
								<select name="snapshot-destination[protocol]" id="snapshot-destination-protocol">
									<?php
									foreach ( $this->protocols as $protocol_key => $protocol_label ) {
										?>
										<option
										value="<?php echo esc_attr( $protocol_key ); ?>"
										<?php
                                        if ( ( isset( $item['protocol'] ) ) && ( $item['protocol'] === $protocol_key ) ) {
											echo ' selected="selected" ';
										}
                                        ?>
                                         ><?php echo esc_html( $protocol_label ); ?></option>
                                        <?php
									}
									?>
								</select>

								<p class="description"><?php echo wp_kses_post( __( 'FTP: uses standard PHP library functions.  (default)<br />SFTP: Implementation use the <a href="http://phpseclib.sourceforge.net" target="_blank">PHP Secure Communications Library</a>. This option may not work depending on how your PHP binaries are compiled.<br />FTPS with TSL/SSL. This option attempts a secure connection. Will only work if PHP and OpenSSL are properly configured on your host and the destination host. This option will not work under Windows using the default PHP binaries. Check the PHP docs for ftp_ssl_connection', SNAPSHOT_I18N_DOMAIN ) ); ?></p>
							</td>
						</tr>

						<?php //if (!isset($item['ssl'])) { $item['ssl'] = "yes"; } ?>
						<?php
                        /* ?>
					<tr class="form-field">
						<th scope="row"><label for="snapshot-destination-ssl"><?php _e('Use sFTP Connection', SNAPSHOT_I18N_DOMAIN); ?></label></th>
						<td>
							<select name="snapshot-destination[ssl]" id="snapshot-destination-ssl">
								<option value="yes" <?php if ($item['ssl'] == "yes") { echo ' selected="selected" '; } ?> >Yes</option>
								<option value="no" <?php if ($item['ssl'] == "no") { echo ' selected="selected" '; } ?> >No</option>
							</select>

							<p class="description"><?php _e('Default: Yes. If set to yes, will attempt to connect to the remote server using a secure connection using the <a href="http://phpseclib.sourceforge.net" target="_blank">PHP Secure Communications Library</a>. This option may not work depending on how your PHP binaries are compiled. This option will not work under Windows. Suggestion is to try SSL. If the test connection fails then try setting SSL to no.', SNAPSHOT_I18N_DOMAIN); ?></p>
						</td>
					</tr>
<?php */
?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-port"><?php esc_html_e( 'Server Port (optional)', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="text" name="snapshot-destination[port]" id="snapshot-destination-port"
							           value="
                                       <?php
                                       if ( isset( $item['port'] ) ) {
								           echo esc_attr( $item['port'] );
							           }
                                       ?>
                                       "/>

								<p class="description"><?php esc_html_e( 'In most normal cases the port should be left blank. Only in rare cases where the system administrator set the default FTP/sFTP port to some other value should the port be set here. If left blank the port will be assumed as 21 for FTP or 22 for sFTP.', SNAPSHOT_I18N_DOMAIN ); ?></p>
							</td>
						</tr>

						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-timeout"><?php esc_html_e( 'Server Timeout', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td><input type="text" name="snapshot-destination[timeout]"
							           id="snapshot-destination-timeout"
							           value="
                                       <?php
                                       if ( isset( $item['timeout'] ) ) {
								           echo esc_attr( $item['timeout'] );
							           }
                                       ?>
                                       "/>

								<p class="description"><?php esc_html_e( 'The default timeout for PHP FTP connections is 90 seconds. Sometimes this timeout needs to be longer for slower connections to busy servers.', SNAPSHOT_I18N_DOMAIN ); ?></p>
							</td>
						</tr>

						<?php
                        if ( ! isset( $item['passive'] ) ) {
							$item['passive'] = "no";
						}
                        ?>
						<tr class="form-field">
							<th scope="row"><label
									for="snapshot-destination-passive"><?php esc_html_e( 'Passive Mode', SNAPSHOT_I18N_DOMAIN ); ?></label>
							</th>
							<td>
								<select name="snapshot-destination[passive]" id="snapshot-destination-passive">
									<option value="yes"
                                    <?php
                                    if ( "yes" === $item['passive'] ) {
										echo ' selected="selected" ';
									}
                                    ?>
                                     >Yes
									</option>
									<option value="no"
                                    <?php
                                    if ( "no" === $item['passive'] ) {
										echo ' selected="selected" ';
									}
                                    ?>
                                     >No
									</option>
								</select>

								<p class="description"><?php esc_html_e( 'Default: No. This options turns on or off passive mode. In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind firewall.', SNAPSHOT_I18N_DOMAIN ); ?></p>
							</td>
						</tr>

						<tr class="form-field" id="snapshot-destination-test-connection-container">
							<th scope="row">&nbsp;</th>
							<td>
								<button id="snapshot-destination-test-connection" class="button-seconary" name="">
								<?php
									esc_html_e( 'Test Connection', SNAPSHOT_I18N_DOMAIN );
								?>
                                </button>
								<div id="snapshot-ajax-destination-test-result" style="display:none"></div>

							</td>
						</tr>
					</table>
				</div>
			</div>
		<?php
		}

		public function init() {

			if ( isset( $this->destination_info ) ) {
				unset( $this->destination_info );
			}
			$this->destination_info = array();

			if ( isset( $this->error_array ) ) {
				unset( $this->error_array );
			}
			$this->error_array = array();

			$this->error_array['errorStatus']    = false;
			$this->error_array['sendFileStatus'] = false;
			$this->error_array['errorArray']     = array();
			$this->error_array['responseArray']  = array();

			// Kill our instance of the sftp connection
			if ( isset( $this->sftp_connection ) ) {
				unset( $this->sftp_connection );
			}

			if ( isset( $this->ftp_connection ) ) {
				unset( $this->ftp_connection );
			}
		}

		public function load_class_destination( $d_info ) {

			if ( isset( $d_info['type'] ) ) {
				$this->destination_info['type'] = esc_attr( $d_info['type'] );
			}

			if ( isset( $d_info['name'] ) ) {
				$this->destination_info['name'] = esc_attr( $d_info['name'] );
			}

			if ( isset( $d_info['address'] ) ) {
				$this->destination_info['address'] = esc_attr( $d_info['address'] );
			}

			if ( ( isset( $d_info['username'] ) ) && ( strlen( $d_info['username'] ) ) ) {
				$this->destination_info['username'] = esc_attr( $d_info['username'] );
			}

			if ( ( isset( $d_info['password'] ) ) && ( strlen( $d_info['password'] ) ) ) {
				$this->destination_info['password'] = html_entity_decode( $d_info['password'] );
			}


			if ( ( isset( $d_info['protocol'] ) ) && ( strlen( $d_info['protocol'] ) ) ) {
				$this->destination_info['protocol'] = esc_attr( $d_info['protocol'] );
			} else {
				// If we don't have the 'protocol' setting then this is a legacy destination. So check the 'ssl' value.
				if ( ( isset( $d_info['ssl'] ) ) && ( strlen( $d_info['ssl'] ) ) ) {
					$this->destination_info['ssl'] = esc_attr( $d_info['ssl'] );
				} else {
					$this->destination_info['ssl'] = "no";
				}

				if ( "no" === $this->destination_info['ssl'] ) {
					$this->destination_info['protocol'] = "ftp";
				} else {
					$this->destination_info['protocol'] = "sftp";
				}

				// We no longer need the 'ssl' setting.
				unset( $this->destination_info['ssl'] );
			}

			if ( ( isset( $d_info['passive'] ) ) && ( strlen( $d_info['passive'] ) ) ) {
				$this->destination_info['passive'] = esc_attr( $d_info['passive'] );
			} else {
				$this->destination_info['passive'] = "no";
			}

			if ( ( isset( $d_info['port'] ) ) && ( strlen( $d_info['port'] ) ) ) {

				$this->destination_info['port'] = intval( $d_info['port'] );

				if ( 0 === $this->destination_info['port'] ) {

					if ( "sftp" === $this->destination_info['protocol'] ) {
						$this->destination_info['port'] = 22;
					} else {
						$this->destination_info['port'] = 21;
					}
				}

			} else {

				if ( "sftp" === $this->destination_info['protocol'] ) {
					$this->destination_info['port'] = 22;
				} else {
					$this->destination_info['port'] = 21;
				}
			}

			if ( ( isset( $d_info['timeout'] ) ) && ( strlen( $d_info['timeout'] ) ) ) {

				$this->destination_info['timeout'] = intval( $d_info['timeout'] );

				if ( 0 === $this->destination_info['timeout'] ) {

					if ( "sftp" === $this->destination_info['protocol'] ) {
						$this->destination_info['timeout'] = 90;
					} else {
						$this->destination_info['timeout'] = 90;
					}
				}

			} else {

				if ( "sftp" === $this->destination_info['protocol'] ) {
					$this->destination_info['timeout'] = 90;
				} else {
					$this->destination_info['timeout'] = 90;
				}

			}

			if ( ( isset( $d_info['directory'] ) ) && ( strlen( $d_info['directory'] ) ) ) {
				$this->destination_info['directory'] = esc_attr( $d_info['directory'] );
			} else {
				$this->destination_info['directory'] = "";
			}
		}

		public function login() {
			if ( "sftp" === $this->destination_info['protocol'] ) {

				$this->error_array['responseArray'][] = "Using sFTP connection";
				$this->error_array['responseArray'][] = "Connecting to host: " . $this->destination_info['address']
				                                        . " Port: " . $this->destination_info['port']
				                                        . " Timeout: " . $this->destination_info['timeout'];

				// phpcs:ignore
				set_include_path( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'phpseclib1.0.10' . PATH_SEPARATOR . get_include_path() );
				require_once  'Net/SFTP.php' ;

				$this->sftp_connection = new Net_SFTP( $this->destination_info['address'], $this->destination_info['port'], $this->destination_info['timeout'] );

				if ( ! $this->sftp_connection->login( $this->destination_info['username'], $this->destination_info['password'] ) ) {
					$this->error_array['errorStatus']     = true;
					$this->error_array['responseArray'][] = "Login attempt failed. Check username and password.";
					//echo "sftp errors<pre>"; print_r($$this->sftp_connection); echo "</pre>";

					$this->error_array['errorArray'] = array_merge( $this->error_array['errorArray'], $this->sftp_connection->getSFTPErrors() );

					return false;

				} else {
					$this->error_array['responseArray'][] = "Login success.";
					$this->error_array['responseArray'][] = "Home/Root: " . $this->get_remote_directory();

					return true;
				}
			} else {
				if ( "ftp" === $this->destination_info['protocol'] ) {
					$this->error_array['responseArray'][] = "Using FTP connection";

					$this->error_array['responseArray'][] = "Connecting to host: " . $this->destination_info['address']
					                                        . " Port: " . $this->destination_info['port']
					                                        . " Timeout: " . $this->destination_info['timeout'];

					$this->ftp_connection = ftp_connect( $this->destination_info['address'], intval( $this->destination_info['port'] ) );
				} else if ( "ftps-tcl-ssl" === $this->destination_info['protocol'] ) {

					$this->error_array['responseArray'][] = "Using FTP with TSL/SSL connection";

					$this->error_array['responseArray'][] = "Connecting to host: " . $this->destination_info['address']
					                                        . " Port: " . $this->destination_info['port']
					                                        . " Timeout: " . $this->destination_info['timeout'];

					$this->ftp_connection = ftp_ssl_connect( $this->destination_info['address'], intval( $this->destination_info['port'] ) );
				}

				if ( ! is_resource( $this->ftp_connection ) ) {
					$this->error_array['errorStatus']     = true;
					$this->error_array['responseArray'][] = "ftp_connect failed.";

					$last_error = error_get_last();
					if ( isset( $last_error['message'] ) ) {
						$this->error_array['errorArray'][] = $last_error['message'];
					}

					return false;
				}

				$login_result = ftp_login( $this->ftp_connection, $this->destination_info['username'], $this->destination_info['password'] );
				if ( ! $login_result ) {
					$this->error_array['errorStatus']     = true;
					$this->error_array['responseArray'][] = "ftp_login failed.";

					$last_error = error_get_last();
					if ( isset( $last_error['message'] ) ) {
						$this->error_array['errorArray'][] = $last_error['message'];
					}

					return false;

				} else {

					// turn passive mode on/off
					if ( "no" === $this->destination_info['passive'] ) {
						$this->error_array['responseArray'][] = "Passive mode off.";
						ftp_pasv( $this->ftp_connection, false );
					} else {
						$this->error_array['responseArray'][] = "Passive mode on.";
						ftp_pasv( $this->ftp_connection, true );
					}

					ftp_set_option( $this->ftp_connection, FTP_TIMEOUT_SEC, $this->destination_info['timeout'] );
					$this->error_array['responseArray'][] = "Timeout set to " . $this->destination_info['timeout'];

					$this->error_array['responseArray'][] = "Login success.";
					$this->error_array['responseArray'][] = "Home/Root: " . $this->get_remote_directory();

					return true;
				}
			}
		}

		public function logout() {
			if ( "sftp" === $this->destination_info['protocol'] ) {
				unset( $this->sftp_connection );
			} else {
				ftp_close( $this->ftp_connection );
			}

		}

		public function get_remote_directory() {

			if ( "sftp" === $this->destination_info['protocol'] ) {
				return $this->sftp_connection->pwd();
			} else {
				return ftp_pwd( $this->ftp_connection );
			}
		}

		public function set_remote_directory() {
			if ( "sftp" === $this->destination_info['protocol'] ) {

				if ( strlen( $this->destination_info['directory'] ) ) {
					if ( ! $this->mkdir( $this->destination_info['directory'] ) ) {
						$this->error_array['errorStatus']     = true;
						$this->error_array['responseArray'][] = "sFTP: Failed to MKDIR: " . $this->destination_info['directory'];
						$this->error_array['errorArray']      = array_merge( $this->error_array['errorArray'], $this->sftp_connection->getSFTPErrors() );

						return $this->error_array;
					} else {
						$this->error_array['responseArray'][] = "Current Directory: " . $this->get_remote_directory();

						return true;
					}
				} else {
					$this->error_array['responseArray'][] = "Current Directory: " . $this->get_remote_directory();

					return true;
				}
			} else {
				if ( strlen( $this->destination_info['directory'] ) ) {
					if ( ! $this->mkdir( $this->destination_info['directory'] ) ) {
						$this->error_array['responseArray'][] = "Couldn't MKDIR:" . $this->destination_info['directory'];

						$last_error = error_get_last();
						if ( isset( $last_error['message'] ) ) {
							$this->error_array['errorArray'][] = $last_error['message'];
						}

						return false;
					} else {
						$this->error_array['responseArray'][] = "Current Directory: " . $this->get_remote_directory();

						return true;
					}
				} else {
					$this->error_array['responseArray'][] = "Current Directory: " . $this->get_remote_directory();

					return true;
				}
			}
		}

		public function send_file( $filename ) {
			if ( "sftp" === $this->destination_info['protocol'] ) {
				$put_ret = $this->sftp_connection->put( basename( $filename ), $filename, NET_SFTP_LOCAL_FILE );
				if ( true !== $put_ret ) {
					$this->error_array['errorStatus']     = true;
					$this->error_array['responseArray'][] = "PUT file failed: " . basename( $filename );
					$this->error_array['errorArray']      = array_merge( $error_array['errorArray'], $this->sftp_connection->getSFTPErrors() );

					return $this->error_array;

				} else {
					$this->error_array['responseArray'][] = "PUT file success: " . basename( $filename );
					$this->error_array['sendFileStatus']  = true;

					return $this->error_array;
				}
			} else {
				if ( ! ftp_put( $this->ftp_connection, basename( $filename ), $filename, FTP_BINARY ) ) {
					$this->error_array['errorStatus']     = true;
					$this->error_array['responseArray'][] = "ftp_put failed: " . basename( $filename );

					$last_error = error_get_last();
					if ( isset( $last_error['message'] ) ) {
						$this->error_array['errorArray'][] = $last_error['message'];
					}

					return $this->error_array;

				} else {
					$this->error_array['responseArray'][] = "ftp_put success:" . basename( $filename );
					$this->error_array['sendFileStatus']  = true;

					return $this->error_array;
				}
			}
		}

		/**
		 * Obtains remote items list
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param string $root Filename prefix to match.
		 *
		 * @return array A list of remote items
		 */
		public function list_remote_items ($root) {
			$items = array();

			if ($this->set_remote_directory()) {
				if ('sftp' === $this->destination_info['protocol']) {
					$raw = $this->sftp_connection->rawlist();
					$results = array();
					foreach ($raw as $filename => $item) {
						if (!preg_match('/^' . preg_quote($root, '/') . '.*$/', $filename)) continue;
						$results[$item['mtime']] = $filename;
					}
					ksort($results);
					$items = array_filter(array_values($results));
				} else {
					// Plain ol' FTP - sort by timestamp, reversed
					$items = ftp_nlist($this->ftp_connection, "-rt ./{$root}*");
				}
			}

			return $items;
		}

		/**
		 * Parses response items into shared format
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param array $items Raw remote items
		 *
		 * @return array
		 */
		public function get_prepared_items ($items) {
			$prepared = array();

			// Faking the timestamp pivot point
			$initial_ts = time() - (count($items) * DAY_IN_SECONDS) - DAY_IN_SECONDS;
			foreach ($items as $idx => $item) {
				// Fake the timestamp
				$ts = $initial_ts + ($idx * DAY_IN_SECONDS);
				$prepared[$ts] = array(
					'created' => date('r', $ts),
					'title' => $item,
					'id' => $item,
				);
			}
			return $prepared;
		}

		/**
		 * Removes remote file
		 *
		 * Assumes remote connection has been established already.
		 *
		 * @since 3.1.6-beta.1
		 *
		 * @param string $file_id Destination-dependent file ID.
		 *
		 * @return bool
		 */
		public function remove_file ($file_id) {
			return 'sftp' === $this->destination_info['protocol']
				? $this->sftp_connection->delete($file_id)
				: ftp_delete($this->ftp_connection, $file_id)
			;
		}

		public function sendfile_to_remote( $destination_info, $filename ) {

			$this->init();

			$this->load_class_destination( $destination_info );

			if ( ! $this->login() ) {
				return $this->error_array;
			}

			if ( ! $this->set_remote_directory() ) {
				return $this->error_array;
			}

			if ( ! $this->send_file( $filename ) ) {
				return $this->error_array;
			}

			return $this->error_array;
		}

		public function mkdir( $directory ) {
			if ( ! strlen( $directory ) ) {
				return;
			}

			$this->error_array['responseArray'][] = "Changing Directory: " . $directory;
			if ( "sftp" === $this->destination_info['protocol'] ) {

				if ( ! $this->sftp_connection ) {
					return false;
				}

				$directory_parts = explode( '/', $directory );
				if ( ( $directory_parts ) && ( count( $directory_parts ) ) ) {
					$current_path = '';
					if ( "/" === $directory[0] ) {
						$current_path = '/';
					} else {
						$current_path = $this->sftp_connection->pwd();
					}

					foreach ( $directory_parts as $directory_part ) {
						$current_path .= $directory_part;

						if ( ! $this->sftp_connection->stat( $current_path ) ) {

							if ( ! $this->sftp_connection->mkdir( $current_path ) ) {
								return false;
							}
						}
						$this->sftp_connection->chdir( $current_path );

						if ( "/" !== $current_path ) {
							$current_path .= "/";
						}
					}
				}

				return true;

			} else {
				if ( ! $this->ftp_connection ) {
					return;
				}

				$directory_parts = explode( '/', $directory );
				if ( ( $directory_parts ) && ( count( $directory_parts ) ) ) {
					$current_path = '';
					if ( "/" === $directory[0] ) {
						$current_path = '/';
					} else {
						$current_path = ftp_pwd( $this->ftp_connection );
					}

					foreach ( $directory_parts as $directory_part ) {
						$current_path .= $directory_part;

						if ( ! $this->ftp_directory_exists( $current_path ) ) {

							if ( ! ftp_mkdir( $this->ftp_connection, $current_path ) ) {
								$last_error = error_get_last();

								return false;

							} else {
								ftp_chdir( $this->ftp_connection, $current_path );
							}
						}

						if ( "/" !== $current_path ) {
							$current_path .= "/";
						}
					}
				}

				return true;

			}
		}

		public function ftp_directory_exists( $dir ) {
			// Get the current working directory
			$origin = ftp_pwd( $this->ftp_connection );

			// Attempt to change directory
			if ( ftp_chdir( $this->ftp_connection, $dir ) ) {
				ftp_chdir( $this->ftp_connection, $dir );

				return true;
			}

			// Directory does not exist
			return false;
		}
	}

	do_action( 'snapshot_register_destination', 'SnapshotDestinationFTP' );
}
?>