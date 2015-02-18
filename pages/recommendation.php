<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>

	<h2><?php _e( 'Pollen Tools - Settings', 'POLLEN_PLUGIN' ); ?></h2>

	<!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
	<?php settings_errors(); ?>

	<!-- Create the form that will be used to render our options -->
	<form method="post" action="options.php">
		<?php
		// This prints out all hidden setting fields
		settings_fields( $this->settings_option_id );
		do_settings_sections( $this->settings_recommendation_page_slug );
		submit_button();
		?>
	</form>

</div>