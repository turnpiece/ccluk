<div class="sui-wrap">
	<div class="sui-header">
		<h1 class="sui-header-title">Debug</h1>
	</div>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				Scanning
			</h3>
		</div>
		<div class="sui-box-body">
			<p>
				Content: <?php echo count( $content ) ?>
			</p>
			<p>
				Core: <?php echo count( $core ) ?>
			</p>
			<p>
				Progress: <?php echo $progress ?>
			</p>
			<p>
				Time: <?php
				?>
			</p>
			<pre style="max-height: 300px;overflow-y: scroll"><?php echo \WP_Defender\Behavior\Utils::instance()->read_log( 'scan' ) ?></pre>
		</div>
	</div>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">
				Tweaks
			</h3>
			<div class="sui-actions-left">
				<form method="post">
					<?php wp_nonce_field( 'flush_tweaks_cache','_defnonce' ) ?>
					<button type="submit" class="sui-button">Flush cache</button>
				</form>
			</div>
		</div>
		<div class="sui-box-body">
			<?php
			$settings = \WP_Defender\Module\Hardener\Model\Settings::instance();
			$cached   = $settings->getDValues( 'head_requests' );
			?>
			<table class="sui-table">
				<thead>
				<tr>
					<th>URL</th>
					<th>TTL</th>
					<th>Data</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $cached as $url => $item ): ?>
					<tr>
						<td>
							<?php echo $url ?>
						</td>
						<td>
							<?php echo $item['ttl'] . ' - ' . \WP_Defender\Behavior\Utils::instance()->formatDateTime( $item['ttl'] ) ?>
						</td>
						<td>
							<?php echo print_r( $item['data'], true ) ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<pre style="max-height: 300px;overflow-y: scroll"><?php echo \WP_Defender\Behavior\Utils::instance()->read_log( 'tweaks' ) ?></pre>
		</div>
	</div>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">Export</h3>
		</div>
		<div class="sui-box-body">
			<pre style="max-height: 300px;overflow-y: scroll"><?php echo \WP_Defender\Behavior\Utils::instance()->read_log( 'settings' ) ?></pre>
		</div>
	</div>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">Ip Lockout</h3>
		</div>
		<div class="sui-box-body">
			<pre style="max-height: 300px;overflow-y: scroll"><?php echo \WP_Defender\Behavior\Utils::instance()->read_log( 'lockout' ) ?></pre>
		</div>
	</div>
	<div class="sui-box">
		<div class="sui-box-header">
			<h3 class="sui-box-title">Audit</h3>
		</div>
		<div class="sui-box-body">
			<pre style="max-height: 300px;overflow-y: scroll"><?php echo \WP_Defender\Behavior\Utils::instance()->read_log( 'audit' ) ?></pre>
		</div>
	</div>
</div>