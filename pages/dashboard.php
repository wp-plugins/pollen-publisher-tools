<div id="icon-options-general" class="icon32"></div>

<h2><?php _e( 'Pollen Publisher Tools for Wordpress', 'POLLEN_PLUGIN' ); ?></h2>

<div class="fb-like" data-href="<?php echo pollenplugin_url ?>" data-layout="standard" data-action="like"
     data-show-faces="true" data-share="true"></div>
<div id="fb-root"></div>
<script>(function (d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s);
		js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=711233425582186&version=v2.0";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

<h3>Application Information</h3>

<p>Edit your application settings on <a href="<?php echo pollenplugin_url ?>">Pollen Dashboard</a></p>

<!-- Create the form that will be used to render our options -->
<form method="post" action="options.php">

	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row">
				<label for="facebook-app-id">Website URL</label>
			</th>
			<td>
				<input type="text" value="<?php echo get_site_url() ?>"
				       maxlength="32" size="40" autocomplete="off" pattern="[0-9]+" disabled>

				<p class="description">You can change your Wordpress URL under <a
						href="<?php echo get_site_url() . '/wp-admin/options-general.php' ?>">Settings > General</a></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="facebook-app-secret">Website Name</label>
			</th>
			<td>
				<input type="text" value="<?php echo get_bloginfo() ?>" size="40" autocomplete="off" pattern="[0-9a-f]+"
				       disabled>

				<p class="description">You can change your Wordpress title under <a
						href="<?php echo get_site_url() . '/wp-admin/options-general.php' ?>">Settings > General</a></p>
		</tr>
		<tr>
			<th scope="row">
				<label for="facebook-app-id">Website ID</label>
			</th>
			<td>
				<div id="pollen_publisher_auth_button">
					<a id="pollen_authorize_button" class="button button-primary">Authorize this site</a>

					<p class="description">Authorize this site to enable Pollen publisher tools and features.</p>
				</div>

				<div id="pollen_publisher_hash" style="display: none;">

					<input name="vm_pollenplugin_option_name[publisher_hash]" type="text"
					       value="<?php echo isset( $this->options['publisher_hash'] ) ? $this->options['publisher_hash'] : '' ?>"
					       autocomplete="off" size="40" disabled>
					<a href="#" id="pollen_unlink_button">unlink account</a>

					<input name="vm_pollenplugin_option_name[publisher_hash]" type="text"
					       value="<?php echo isset( $this->options['publisher_hash'] ) ? $this->options['publisher_hash'] : '' ?>"
					       autocomplete="off" size="40" hidden="hidden">
					<input name="vm_pollenplugin_option_name[publisher_id]" type="text"
					       value="<?php echo isset( $this->options['publisher_id'] ) ? $this->options['publisher_id'] : '' ?>"
					       autocomplete="off" pattern="[0-9]+" hidden="hidden">


					<p class="description">
						You have connected your <i><?php echo $this->name ?></i> accounts.
					</p>

					<p class="description">
						To get started, you can
						<a href="<?php echo get_site_url() ?>/wp-admin/widgets.php">create Widgets</a> and
						<a href="<?php echo get_site_url() ?>/wp-admin/admin.php?page=vm_pollenplugin_menu_slug_recommendation">enable
							Recommendation Widget</a> on posts.
					</p>
				</div>

				<div id="pollen_publisher_save" style="display: none;">
					<p class="description">
						Please save to continue.
					</p>
				</div>

				<script>
					(function () {
						var url = '<?php echo pollenplugin_url ?>';

						var showSection = function (section) {

							var all_sections = ['pollen_publisher_auth_button', 'pollen_publisher_hash', 'pollen_publisher_save'];
							all_sections.forEach(function (value, index) {
								jQuery('#' + value).hide();
							});

							switch (section) {
								case 'pollen_publisher_hash' :
									jQuery('#' + section).show();
									break;
								case 'pollen_publisher_auth_button':
								default:
									jQuery('#pollen_publisher_auth_button').show();
									break;
							}
						};

						var fnCheckAuth = function () {
							jQuery.ajax({
								type: "GET",
								url: "url",
								dataType: 'jsonp',
								timeout: 3000,
								success: function (data) {
									//successful authentication here
									console.log(data);
								},
								error: function (jqXHR, textStatus, errorThrown) {
									console.log("error: " + textStatus);
									// popup login screen

								}
							});
						};

						var getPublisher = function () {
							jQuery.getJSON(url + '/api/worpdress/publisher/add?callback=?&<?php echo http_build_query(['url' => get_site_url()]) ?>', function (xhrResponse) {
								console.log(xhrResponse);
								console.log(xhrResponse);
								jQuery('[name="vm_pollenplugin_option_name[publisher_hash]"]').val(btoa(xhrResponse.publisher_site_url));
								jQuery('[name="vm_pollenplugin_option_name[publisher_id]"]').val(xhrResponse.id);
								showSection('pollen_publisher_hash');
								jQuery('[name="submit"]').click();
							}).fail(function () {
								console.log("error");
							});
						};

						var unlinkAccount = function () {

						};

						jQuery('#pollen_authorize_button').click(function () {
							POLLENPLUGIN.login(getPublisher);
						});
						jQuery('#pollen_unlink_button').click(function () {
							if (!confirm('Are you sure you want to unlink the account?')) {
								return;
							}
							jQuery('[name="vm_pollenplugin_option_name[publisher_hash]"]').val('');
							jQuery('[name="vm_pollenplugin_option_name[publisher_id]"]').val('');
							jQuery('[name="submit"]').click();
						});

						var isAuthorized = <?php echo isset($this->options['publisher_id']) && $this->options['publisher_id'] ? $this->options['publisher_id'] : 'null' ?>;
						if (isAuthorized) {
							showSection('pollen_publisher_hash');
						} else {
							showSection('pollen_publisher_auth_button');
						}

					})();
				</script>

			</td>
		</tr>
		</tbody>
	</table>

	<?php
	settings_fields( $this->settings_option_id );
	submit_button();
	?>
</form>
