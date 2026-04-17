<div class="wrap">


	<h2><?php echo __( 'Create Order Form', 'wof-sm' );?></h2>

	<form method="post" action="<?php echo esc_url( menu_page_url( 'wof', false ) ); ?>">

		<input type="hidden" name="command" value="create"/>
		<?php wp_nonce_field( 'wof_create', 'nonce' ); ?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">

					<div id="titlediv">
						<div id="titlewrap">
								<input type="text" name="post_title" size="30" value="" placeholder="Order Form Title" id="title" spellcheck="true" autocomplete="off">
						</div>
					</div>

					<div class="postarea">

						<h4>HTML</h4>
						<textarea class="codemirror" name="wof_content" id="wof_content" style="width: 50%; height: auto; margin-top: 25px;"><?php WOF::get_default_content();?></textarea>
						<br>

						<h4>CSS</h4>
						<textarea class="codemirror_css" name="wof_content_css" id="wof_content_css" style="width: 50%; height: auto; margin-top: 25px;"><?php WOF::get_default_content_css();?></textarea>

					</div>
					<!-- /.postarea -->
				</div>


				<div id="postbox-container-1" class="postbox-container">
								
					<div id="submitdiv" class="postbox">
						<h3><?php echo esc_html( __( 'General', 'wof-sm' ) ); ?></h3>
						<div class="inside">

							<div class="submitbox" id="submitpost">

								<div id="major-publishing-actions">

									<div id="publishing-action">
											<input name="original_publish" type="hidden" id="original_publish" value="Publish">
											<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Publish">
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
								<option value="woo_thankyou">WooCommerce Thank You Page</option>
								<option value="custom">Custom link</option>
							</select>
							<input type="text" name="custom_redirect" id="custom_redirect" placeholder="Link for redirect" style="display: none; width: 100%; margin-top: 5px;">
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
								           <?php if ( $l["active"] == 1 ) {
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
									?>
										<option value="<?php the_ID();?>"><?php the_title();?></option>
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