<div class="wrap">

	<?php 
		$post = WOF::get_instance( $_GET["post"] );
	?>

	<h2><?php echo __( 'Edit Order Form', 'wof-sm' );?></h2>

	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post->id() ), menu_page_url( 'wof', false ) ) ); ?>">

		<input type="hidden" id="form_id" name="form_id" value="<?php echo (int)$post->id(); ?>"/>
		<input type="hidden" name="command" value="save"/>
		<?php wp_nonce_field( 'wof_save', 'nonce' ); ?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">

					<div id="titlediv">
						<div id="titlewrap">
								<input type="text" name="post_title" size="30" value="<?php echo $post->title();?>" id="title" spellcheck="true" autocomplete="off">
						</div>
					</div>

					<div class="postarea">

						<h4>HTML</h4>
						<textarea class="codemirror" name="wof_content" id="wof_content" style="width: 50%; height: auto; margin-top: 25px;"><?php echo $post->content();?></textarea>
						<br>

						<h4>CSS</h4>
						<textarea class="codemirror_css" name="wof_content_css" id="wof_content_css" style="width: 50%; height: auto; margin-top: 25px;"><?php echo $post->content_css();?></textarea>

						<br>

						<h4>IFRAME</h4>

						<textarea name="" style="width: 50%" id="" cols="30" rows="10"><iframe src="<?php echo plugins_url() ."/module-woo-orderforms/iframe.php?wof_id=".$post->id();?>" frameborder="0"></iframe></textarea>
					</div>
					<!-- /.postarea -->


				</div>


				<div id="postbox-container-1" class="postbox-container">
								
					<div id="submitdiv" class="postbox">
						<h3><?php echo esc_html( __( 'General', 'wof-sm' ) ); ?></h3>
						<div class="inside">

							<p style="padding: 15px;">
								<?php echo  __( 'Orders via this form: ', 'wof-sm' );?>
								<?php
									global $wpdb;
									// Replace these with your meta_key and meta_value
									$meta_key = 'wof_form';
									$meta_value = $post->id();

									$sql = "SELECT count(DISTINCT pm.post_id)
									FROM $wpdb->postmeta pm
									JOIN $wpdb->posts p ON (p.ID = pm.post_id)
									WHERE pm.meta_key = '$meta_key'
									AND pm.meta_value = '$meta_value'
									AND p.post_type = 'shop_order'
									";
									$count = $wpdb->get_var($sql);
									echo "<b>$count</b>";
								?>
							</p>

							<div class="submitbox" id="submitpost">

								<div id="major-publishing-actions">

									<div id="delete-action">
										<a class="submitdelete deletion" onclick="return confirm('<?php echo __( 'Are you sure?', 'wof-sm' );?>')" href="">Delete</a>
									</div>

									<div id="publishing-action">
											<input name="original_publish" type="hidden" id="original_publish" value="Save">
											<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Save">
									</div>
									<div class="clear"></div>

								</div>

							</div>
						</div>
					</div>
					<!-- END OF SUBMITDIV -->
								
					<div id="redirect" class="postbox">
						<h3><?php echo esc_html( __( 'Redirect (thank you page)', 'wof-sm' ) ); ?></h3>
						<div class="inside">
							<select name="wof_redirect" id="wof_redirect">
								<option value="woo_thankyou" <?php if( $post->wof_redirect() == "woo_thankyou" ) { echo " selected"; } ?>>WooCommerce Thank You Page</option>
								<option value="custom" <?php if( $post->wof_redirect() == "custom" ) { echo " selected"; } ?>>Custom link</option>
							</select>
							<input type="text" name="custom_redirect" id="custom_redirect" value="<?php echo $post->custom_redirect();?>" placeholder="Link for redirect" style="display: none; width: 100%; margin-top: 5px;">
						</div>
					</div>
					<!-- END OF REDIRECT -->	

					<?php
					/// Multilanguage
					if(function_exists('icl_object_id')) {
					?>
					<div id="language" class="postbox">
						<h3><?php echo esc_html( __( 'Language (WPML)', 'wof-sm' ) ); ?></h3>
						<div class="inside">
							<select name="language" id="language" style="width: 100%;">
								<?php
								    $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
								 
								    if ( !empty( $languages ) ) {
								        foreach( $languages as $l ) {
								        ?>

								          <option value='<?php echo $l['language_code'];?>'
								           <?php if ( $l["language_code"] == $post->language() ) {
								          	echo "selected=selected";}?>><?php echo $l['native_name'];?></option>

								        <?php
								        }
								    }
								?>
							</select>
						</div>
					</div>
					<!-- END OF LANGUAGE -->	
					<?php
					}
					?>		

					<div id="producst" class="postbox">
						<h3><?php echo esc_html( __( 'Products', 'wof-sm' ) ); ?></h3>
						<div class="inside">
							<select name="wof_products[]" id="wof_products" multiple style="width: 100%;">

								<?php
									$args = array(
										'post_type' => 'product',
										);
									$loop = new WP_Query( $args );
									if ( $loop->have_posts() ) {
										while ( $loop->have_posts() ) : $loop->the_post();
										$ID = get_the_ID();
									?>
										<option value="<?php the_ID();?>" <?php if( in_array($ID,  $post->wof_products() ) ) { echo " selected"; } ?>><?php the_title();?></option>
									<?php
										endwhile;
									} else {
										///echo __( 'No products found' );
									}
									wp_reset_postdata();
								?>

							</select>
						</div>
					</div>
					<!-- END OF PRODUCTS -->

								
								
					<div id="help" class="postbox">
						<h3><?php echo esc_html( __( 'Help', 'wof-sm' ) ); ?></h3>
						<div class="inside">

							<?php echo __( 'Name attributes for inputs:', 'wof-sm' );?> 
							<hr>

							<pre class="shortcode">name = First name</pre>
							<pre class="shortcode">surname = Surname</pre>
							<pre class="shortcode">address = Address</pre>
							<pre class="shortcode">city = City</pre>
							<pre class="shortcode">zip = Zip code</pre>
							<pre class="shortcode">email = Email</pre>
							<pre class="shortcode">phone = Phone</pre> <br>

							<?php echo __( 'Products:', 'wof-sm' );?> 
							<hr>

							<pre class="shortcode">[product-select] <br>Will echo select with products</pre>
							<pre class="shortcode">[product-single] <br>Will echo hidden input <br>with single product ID</pre>
							<pre class="shortcode">[payment-options] <br>Will echo UL > LI of <br>payment methods</pre>

						</div>
					</div>
					<!-- END OF HELP -->


				</div>

			</div>
		</div>

	</form>
</div>