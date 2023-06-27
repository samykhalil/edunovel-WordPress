<?php
/*
Template Name: Password Reset Template
*/
get_header();
?>

<div id="primary" class="content-area">
   <main id="main" class="site-main">
      <section class="reset-password-section">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <?php
                  if (isset($_GET['login']) && isset($_GET['key'])) {
                     $user_login = sanitize_user(wp_unslash($_GET['login']));
                     $reset_key  = sanitize_text_field(wp_unslash($_GET['key']));

                     $user = check_password_reset_key($reset_key, $user_login);

                     if (!empty($user)) {
                  ?>
                        <h2><?php the_title(); ?></h2>
                        <div id="reset-password-message"></div>

                        <form id="reset-password-form" method="post">
                           <input type="hidden" name="user_login" value="<?php echo esc_attr($user_login); ?>">
                           <input type="hidden" name="reset_key" value="<?php echo esc_attr($reset_key); ?>">
                           <input type="hidden" name="action" value="reset_password">
                           <p>
                              <label for="new_password">كلمة المرور</label><br />
                              <input type="password" name="new_password" id="new_password" class="input" size="20" required />
                           </p>
                           <p>
                              <label for="confirm_password">تاكيد كلمة المرور</label><br />
                              <input type="password" name="confirm_password" id="confirm_password" class="input" size="20" required />
                           </p>
                           <p class="reset-password-submit">
                              <input type="submit" name="submit" class="button button-primary" value="ارسال" />
                           </p>
                        </form>

                        <script>
                           jQuery(document).ready(function($) {
                              $('#reset-password-form').on('submit', function(e) {
                                 e.preventDefault();

                                 var form = $(this);
                                 var formData = form.serialize();

                                 $.ajax({
                                    type: 'POST',
                                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                                    data: formData,
                                    beforeSend: function() {
                                       $('#reset-password-message').html('جاري تحديث كلمة المرور ...')
                                    },
                                    success: function(response) {
                                       if (response.success) {
                                          $('#reset-password-message').html('<p>تم تحديث كلمة المرور بنجاح!</p>');
                                          form.trigger('reset');
                                          window.location.replace(window.location.hostname);

                                       } else {
                                          $('#reset-password-message').html('<p>' + response.data.message + '</p>');
                                       }
                                    },
                                    error: function() {
                                       $('#reset-password-message').html('<p>حدث خطأ ما حاول مرة اخري</p>');
                                    }
                                 });
                              });
                           });
                        </script>
                  <?php
                     } else {
                        echo '<p>رابط اعادة تعيين كلمة المرور غير صالح.</p>';
                     }
                  } else {
                     echo '<p>رابط اعادة تعيين كلمة المرور غير صالح.</p>';
                  }
                  ?>
               </div>
            </div>
         </div>
      </section>
   </main>
</div>

<?php
get_footer();
