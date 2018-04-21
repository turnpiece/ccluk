<form method="post" class="settings-frm">
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Subsite Performance Tests', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'By default Hummingbird restricts performance tests to just super admins. Use this feature to allow subsite admins to run performance tests on their own sites.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle">
				<input type="checkbox" name="subsite-tests" value="1"
					   id="chk1" <?php checked( 1, $subsite_tests ); ?> />
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="chk1"><?php esc_html_e( 'Allow subsite admins to run performance tests', 'wphb' ); ?></label>
		</div>
	</div>
