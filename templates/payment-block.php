<div class="linkid-block linkid-block-payment">
	<div class="linkid-block-header">
		<img class="linkid-block-header-logo" src="<?php echo LINK_WP_LINKID_PLUGIN_URL . "img/linkid-logo-zw.png" ?>"/>
	</div>
	<div class="linkid-block-content">
		<div class="body-header body-header-tall">
			<?php
			_e( "Pay using linkID", 'link-linkid' )
			?>
		</div>
		<div class="payment-info">
			<?php printf( __( "About to pay for order #%s", "link-linkid" ), $order->id ) ?>
		</div>
		<div class="linkid-block-text">
			<?php
			_e( "Scan the QR Code with your linkID app, enter your pin, select payment method and... Done!", 'link-linkid' )
			?>
		</div>
		<?php if ( $show_error_text ) { ?>
			<p class="linkid-action-error-message">
				<?php echo $error_text ?>
			</p>
		<?php } ?>
		<div id="linkid-action">
			<?php if ( ! $payment_initialized ) { ?>
				<p class="linkid-action-error-message">
					<?php
					_e( "Could not start payment. Please try again later.", 'link-linkid' );
					?>
				</p>
			<?php } else { ?>
				<div class="qr-wrapper">
					<img class="qr-image" id="paymentQRImage"
					     src="data:image/png;base64,<?php echo $linkIDAuthnSession->qrCodeImageEncoded ?> "/>
				</div>
			<?php } ?>
		</div>
		<div class="abort-payment">
			<a href="<?php echo $cancel_order_url ?>">
				<?php
				_e( "Cancel payment and go back to cart.", 'link-linkid' );
				?>
			</a>
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
		<a class="learn-more-button" target="_blank" href="http://www.linkid.be" title="Learn more on linkID.be">Learn
			more on linkID.be</a>
	</div>
</div>