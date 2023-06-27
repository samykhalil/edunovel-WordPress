<?php
/**
 * Template Name: Add Students
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Edumall
 * @since    1.0.0
 * @version  2.10.1
 */

defined( 'ABSPATH' ) || exit;
get_header();
$current_user = wp_get_current_user();


?>
<div id="page-content" class="page-content">
	<div class="container">
		<?php 
		if (is_user_logged_in()) {

		
			
		?>
		<div class="status">

		</div>
		<div class="row">

			<?php Edumall_Sidebar::instance()->render( 'left' ); ?>

			<div id="page-main-content" class="page-main-content">

				<form method="post" enctype="multipart/form-data" id="student-registration">

		    <?php wp_nonce_field('ajax-login-nonce', 'security'); ?>  

		<input type="hidden" value="<?php echo $current_user->user_email ?>" name="parent_email"/>

		

		<div class="tutor-form-row">
			<div class="tutor-form-col-6">
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'First Name', 'tutor' ); ?>
					</label>

					<input type="text" name="first_name" value="" placeholder="<?php _e( 'First Name', 'tutor' ); ?>" required autocomplete="given-name">
				</div>
			</div>

			<div class="tutor-form-col-6">
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Last Name', 'tutor' ); ?>
					</label>

					<input type="text" name="last_name" value="" placeholder="<?php _e( 'Last Name', 'tutor' ); ?>" required autocomplete="family-name">
				</div>
			</div>

		</div>

		<div class="tutor-form-row">
			<div class="tutor-form-col-6">
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'User Name', 'tutor' ); ?>
					</label>

					<input type="text" name="user_login" class="tutor_user_name" value="" placeholder="<?php esc_html_e( 'User Name', 'tutor' ); ?>" required autocomplete="username">
				</div>
			</div>

			<div class="tutor-form-col-6">
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'E-Mail', 'tutor' ); ?>
					</label>

					<input type="text" name="email" value="" placeholder="<?php esc_html_e( 'E-Mail', 'tutor' ); ?>" required autocomplete="email">
				</div>
			</div>

		</div>

		<div class="tutor-form-row">
			<div class="tutor-form-col-6">
				<div class="tutor-form-group">
					<label>
						<?php esc_html_e( 'Password', 'tutor' ); ?>
					</label>

					<input type="password" name="password" value="" placeholder="<?php esc_html_e( 'Password', 'tutor' ); ?>" required autocomplete="new-password">
				</div>
			</div>
		</div>

  


	

		<div>
			<button type="submit" name="tutor_register_student_btn" value="register" class="tutor-btn tutor-btn-primary"><?php esc_html_e( 'Register', 'tutor' ); ?></button>
		</div>

	</form>

			</div>

			<?php Edumall_Sidebar::instance()->render( 'right' ); ?>

		</div>
		<?php 
		}else{
			?>

			<p>يجب عليك تسجيل الدخول اولا لاستعراض الصفحة</p>
			<?php
		}?>
	</div>
</div>
<?php get_footer(); ?>