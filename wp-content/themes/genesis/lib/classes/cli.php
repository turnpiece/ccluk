<?php
/**
 * 
 */


class Genesis_CLI_Command extends WP_CLI_Command {

	/**
     * Upgrade the Genesis settings, usually after an upgrade.
     * 
     * ## EXAMPLES
     * 
     *     wp genesis upgrade-db
     *
     */
	public function upgrade_db( $args, $assoc_args ) {

		//* Disable post-upgrade redirect
		remove_action( 'genesis_upgrade', 'genesis_upgrade_redirect' );

		// Call the upgrade function
		genesis_upgrade();

		WP_CLI::success( __( 'Genesis database upgraded.', 'genesis' ) );

	}

}

WP_CLI::add_command( 'genesis', 'Genesis_CLI_Command' );