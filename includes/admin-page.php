<?php

if ( !class_exists( 'ABD_Admin_Pages' ) ) {
	class ABD_Admin_Pages {
		public static function navigate() {
			self::index();
		}	//	end function navigate()

		protected static function header() {
			?>
			<div class='wrap'>
				<?php screen_icon(); ?>
				<h2>Ad Blocking Detector</h2>
				
				<div id='ABD_content'>
					<div id='ABD_notification'></div>
			<?php
		}

		protected static function footer() {
			?>
				</div><!--	end <div id='ABD_content'>	-->

				<div id='ABD_sidebar'>
					<div class='ABD_sidebar_box ABD_highlight'>
						<h3>Support This Plugin</h3>
						<p>
							Is this plugin useful for you?  If so, please help
							support its ongoing development and improvement 
							with a donation.
						</p>
						<table id='ABD_donations'>							
							<tr>
								<!--Flattr-->
								<td>
									<script id='fbpydtc'>(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//api.flattr.com/button/view/?uid=jtmorris&url='+encodeURIComponent(document.URL);f.title='Flattr';f.height=62;f.width=55;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('fbpydtc');</script>
								</td>
								<!--PayPal-->
								<td>
									<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
										<input type="hidden" name="cmd" value="_s-xclick">
										<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
										<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
										<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
									</form>
								</td>
							</tr>
						</table>

						<br />

						<p>
							Or, if you are short on funds, there are other ways you can help out:
						</p>
						<ul>
							<li>Leave us a positive review on the plugin's <a href=''>WordPress listing</a></li>
							<li>Vote "Works" on the plugin's <a href=''>WordPress listing</a></li>
							<li><a href=''>Share your thoughts on Twitter</a> and other social sites</li>
							<li>Improve this plugin on <a href=''>GitHub</a></li>
						</ul>						
					</div>

					<div class='ABD_sidebar_box'>
						<h3>Get Help / Report a Bug</h3>
						<p>
							If you're encountering a problem, have a question, or would like to suggest an improvement, be sure to let me know!
						</p>						
						<p> 
							You can start a thread on this plugin's <a target='_blank' href=''>WordPress support page</a>, open an "issue"
							in the <a target='_blank' href=''>GitHub</a> repository, or <a target='_blank' href='http://jtmorris.net/contact-me/'>contact the 
							developer directly</a>.
						</p>
					</div>					
				</div>

			</div><!-- end <div class='wrap'>	-->
			<?php
		}

		public static function index() {
			?>				
			<div class='wrap'>
				<?php 
				self::header();


				//	Begin content
				?>
				<div class='ABD_logical_block'>
					<h3>Your Ad Block Detector Shortcodes</h3>
					<a class='ABD_button ABD_new_button ABD_alternate_color'>New Shortcode</a>
					<br /><br />
					<table id='ABD_shortcode_list' class='ABD_shortcode_list'>
						<tr>
							<th>Name</th>
							<th>Shortcode</th>
							<th>Actions</th>
						</tr>
						<tr>
							<td id='ABD_shortcode_list_message' colspan='3'>Initializing...</td>
						</tr>						
					</table>
				</div>					


				<div id='ABD_new_input_form_wrapper' class='ABD_logical_block ABD_input_form_wrapper'>
					<h3 id='ABD_input_form_header'>Add a New Shortcode</h3>
					<form id='ABD_new_input_form' method='post'>
						<table class='ABD_form_layout'>
							<tr>
								<td><label for='ABD_new_input_form_name'>Name / Description:</label></td>
								<td><input type='text' name='ABD_new_input_form_name' id='ABD_new_input_form_name' /></td>
								<td><span id='ABD_new_input_form_name_feedback' class='ABD_input_form_feedback'></span></td>
							</tr>
							<tr>
								<td><label for='ABD_new_input_form_noadblock'>No Ad Blocker <em>(optional)</em></label></td>
								<td><textarea name='ABD_new_input_form_noadblock' id='ABD_new_input_form_noadblock'></textarea></td>
								<td><span id='ABD_new_input_form_noadblock_feedback' class='ABD_input_form_feedback'></span></td>
							</tr>
							<tr>
								<td><label for='ABD_new_input_form_adblock'>Ad Blocker</label></td>
								<td><textarea name='ABD_new_input_form_adblock' id='ABD_new_input_form_adblock'></textarea></td>
								<td><span id='ABD_new_input_form_adblock_feedback' class='ABD_input_form_feedback'></span></td>
							</tr>
						</table>

						<?php wp_nonce_field( 'ABD_new_input_form' ); ?>

						<a id='ABD_new_input_form_submit' class='ABD_button ABD_submit_button'>Save Shortcode</a>
					</form>
				</div>

				<div id='ABD_edit_input_form_wrapper' class='ABD_logical_block ABD_input_form_wrapper'>
					<h3 id='ABD_input_form_header'>Edit a Shortcode</h3>
					<form id='ABD_edit_input_form' method='post'>
						<table class='ABD_form_layout'>
							<tr>
								<td><label for='ABD_edit_input_form_name'>Name / Description:</label></td>
								<td><input type='text' name='ABD_edit_input_form_name' id='ABD_edit_input_form_name' /></td>
								<td><span id='ABD_edit_input_form_name_feedback' class='ABD_input_form_feedback'></span></td>
							</tr>
							<tr>
								<td><label for='ABD_edit_input_form_noadblock'>No Ad Blocker <em>(optional)</em></label></td>
								<td><textarea name='ABD_edit_input_form_noadblock' id='ABD_edit_input_form_noadblock'></textarea></td>
								<td><span id='ABD_edit_input_form_noadblock_feedback' class='ABD_input_form_feedback'></span></td>
							</tr>
							<tr>
								<td><label for='ABD_edit_input_form_adblock'>Ad Blocker</label></td>
								<td><textarea name='ABD_edit_input_form_adblock' id='ABD_edit_input_form_adblock'></textarea></td>
								<td><span id='ABD_edit_input_form_adblock_feedback' class='ABD_input_form_feedback'></span></td>
							</tr>
						</table>

						<input type='hidden' name='ABD_edit_input_form_id' id='ABD_edit_input_form_id' value='-1' />
						
						<?php wp_nonce_field( 'ABD_edit_input_form' ); ?>

						<a id='ABD_edit_input_form_submit' class='ABD_button ABD_submit_button'>Save Shortcode</a>
					</form>
				</div>

				<div class='ABD_footer_box ABD_highlight ABD_tip ABD_logical_block'>
					<h3>Be Warned!</h3>
					<img src='<?php echo ABD_ROOT_URL; ?>images/notification_exclamation.png' style='float: left; width: 100px; margin: 0 25px 25px 0;' />
					<p>
						Using this tool circumvents the wishes of your site's visitors.  They have an ad blocker for a reason.							
						Displaying a different gaudy advertisement or berating your ad block wielding vistors will have negative
						consequences for your site, and will make tools like this a target for future ad blockers.
					</p>
					<p>
						Instead of a plea or alternative ad, consider using the space to encourage participation in other ways.
						Get them to sign up for your newsletter or follow your social media profiles.  Something other than the
						advertisements or requests to view advertisements.  This will be much more effective and well-received.
					</p>
				</div>

				<div class='ABD_footer_box'>
					<h3>How To Use This Plugin</h3>
					<p>
						This plugin operates using <a target='_blank' href='http://codex.wordpress.org/Shortcode'>shortcodes</a>.  All of your available shortcodes are 
						listed in the table near the top of the page.  Each row in the table corresponds to one shortcode.  By default, 
						the plugin includes a few examples to get you started.
					</p>
					<hr />
					<h4>Using Shortcodes</h4>
					<p>
						To use one of your created shortcodes, copy and paste the value from the shortcode column into your post, page,
						or sidebar text widget. <i>**Note: By default, the WordPress sidebar does not display shortcodes.  If you want
						to use this plugin in a sidebar widget, you must 
						<a target='_blank' href='http://www.wpbeginner.com/wp-tutorials/how-to-use-shortcodes-in-your-wordpress-sidebar-widgets/'>enable this functionality</a> manually.</i>
					</p>
					<hr />
					<h4>Creating and Editing Shortcodes</h4>
					<p>
						To add your own new shortcode, you can click the "New Shortcode" button.  To edit an existing shortcode, click the "Edit" button
						in the shortcode's row.
					</p>						  
					 <p>
					 	Creating new shortcodes or editing existing shortcodes will display a form.
					 	The "Name / Description" field is used strictly for descriptive purposes on this page.  The
						"No Ad Blocker" field is what to display if no ad blocking software is present. 
						The "Ad Blocker" field is what you want displayed if ad blocking software is detected.
					</p>
					<p>
						Both ad block fields can contain HTML, CSS, Javascript, or just plain text.  However, be careful if using HTML, CSS, or Javascript
						as you can break your website's layout if you aren't careful.
					</p>
				</div>

				<?php
				//	End content
				self::footer();
				?>
			</div>
			<?php
		}
	}	//	end class ABD_Admin_Pages
}	//	end if( !class_exists