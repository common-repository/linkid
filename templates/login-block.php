<button class="linkid-action-button linkid-action-button-small linkid-start-button" type="button" id="loginLink">
	<img class="linkid-block-header-logo" src="<?php echo LINK_WP_LINKID_PLUGIN_URL . "img/linkid-logo-zw.png" ?>"/>
	<br/>
	<b>
		<?php
		if ( $isMerge ) {
			_e( "Merge your account with linkID", 'link-linkid' );
		} else if ( Link_WP_LinkID_Login::can_register() ) {
			_e( "Log in or register with linkID", 'link-linkid' );
		} else {
			_e( "Log in with linkID", 'link-linkid' );
		}
		?>
	</b>
</button>

<div id="linkid-login-block" style="display: none;">
	<div class="linkid-block linkid-block-tall">
		<div class="linkid-block-header">
			<img class="linkid-block-header-logo"
			     src="<?php echo LINK_WP_LINKID_PLUGIN_URL . "img/linkid-logo-zw.png" ?>"/>
		</div>
		<div class="linkid-block-content">
			<div class="body-header body-header-tall">
				<?php
				if ( $isMerge ) {
					_e( "Merge your account with linkID", 'link-linkid' );
				} else if ( Link_WP_LinkID_Login::can_register() ) {
					_e( "Log in or register with linkID", 'link-linkid' );
				} else {
					_e( "Log in with linkID", 'link-linkid' );
				}
				?></div>
			<div id="linkid-action">
			</div>
			<div class="linkid-block-text">
				<?php
				_e( "Scan the QR Code with your linkID app, enter your pin and... You're in!", 'link-linkid' )
				?>
			</div>
			<div class="store-logos">
				<a href="https://play.google.com/store/apps/details?id=net.link.qr"
				   target="_blank" title="Download the linkID app on your android phone">
					<img class="store-img" src="<?php echo LINK_WP_LINKID_PLUGIN_URL . "img/play-store.png" ?>"/>
				</a>
				<a href="https://itunes.apple.com/us/app/linkid-for-mobile/id522371545?mt=8"
				   target="_blank" title="Download the linkID app on your iOS phone">
					<img class="store-img" src="<?php echo LINK_WP_LINKID_PLUGIN_URL . "img/app-store.png" ?>"/>
				</a>
			</div>
			<a class="learn-more-button" target="_blank" href="http://www.linkid.be"
			   title="Learn more on linkID.be">
				<?php
				_e( "Learn more on linkID.be", 'link-linkid' )
				?>
			</a>
		</div>
	</div>
</div>
