<form method="post" class="settings-frm">
	<div class="box-content settings-form">
		<div class="row">
			<div class="col-third">
				<strong><?php esc_html_e( 'Subsite Performance Tests', 'wphb' ); ?></strong>
				<span class="sub">
					<?php esc_html_e( 'By default Hummingbird restricts performance tests to just super admins. Use this feature to allow subsite admins to run performance tests on their own sites.', 'wphb' ); ?>
				</span>
			</div><!-- end col-third -->
			<div class="col-two-third">
				<span class="toggle">
					<input type="hidden" name="subsite-tests" value="0"/>
					<input type="checkbox" class="toggle-checkbox" name="subsite-tests" value="1"
							id="chk1" <?php checked( 1, $subsite_tests ); ?> />
					<label class="toggle-label small" for="chk1"></label>
				</span>
				<label><?php esc_html_e( 'Allow subsite admins to run performance tests', 'wphb' ) ?></label>
				<div class="clear mline"></div>
			</div><!-- end col-two-third -->
		</div><!-- end row -->
	</div>
