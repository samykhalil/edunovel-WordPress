<?php

/**
 * Template Name: my account
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Edumall
 * @since    1.0.0
 * @version  2.10.1
 */
global $wpdb;
defined('ABSPATH') || exit;
get_header();
?>
<div class="container myaccount">
   <?php
   if (is_user_logged_in()) { ?>

      <?php
      $current_user = wp_get_current_user();
      if (($current_user instanceof WP_User)) {
      ?>
         <div class="my-account-profile">
            <div class="my-avatar">
               <?php echo edumall_get_avatar($current_user->ID, 100); ?>
            </div>
            <div class="my-info">
               <div class="welcome-text"><?php esc_html_e('Hello!', 'edumall'); ?></div>
               <h6 class="my-name fn"><?php echo esc_html($current_user->display_name); ?></h6>
            </div>
         </div>
      <?php } ?>

      <div class="status">

      </div>
      <?php $user_query = new WP_User_Query(array(
         'meta_query' => array(
            array(
               'key' => 'parent_email',
               'value' =>  $current_user->user_email,
               'compare' => 'LIKE'
            ),

         )
      ));
      // User Loop
      if (!empty($user_query->get_results())) {
      ?>
         <h3>تفاصيل الطلاب المشتركين</h3>
         <?php
         foreach ($user_query->get_results() as $user) {

         ?>
            <div id="myModal" class="modal">

               <!-- Modal content -->
               <div class="modal-content">
                  <span class="close">&times;</span>
                  <p>هل انت متأكد من حذف الطالب ؟</p>
                  <div class="name">الاسم: <?php echo $user->display_name ?></div>
                  <div class="name">البريد الالكتروني: <?php echo $user->user_email ?></div>
                  <div class="footer"><button class="deleteuser" data-user-id="<?php echo $user->ID ?>">حذف</button>
                     <button class="closebutton">اغلاق</button>
                  </div>
               </div>

            </div>
            <div class="heading">
               <div class="name">
                  <?php echo edumall_get_avatar($user->ID, 50); ?>
                  <div>
                     <div>اسم الطالب : <?php echo $user->display_name ?></div>
                     <div>البريد الالكتروني: <?php echo $user->user_email ?>
                     </div>
                  </div>
               </div>
               <div class="actions">
                  <?php $link = json_decode(getlink($user->user_email, $user->first_name, $user->last_name)); ?>
                  <?php if ($link->link) : ?> <button class="login" data-login-url="<?php echo $link->link; ?>">تسجيل دخول</button><?php endif; ?>

                  <button class="showorders">عرض التفاصيل</button>
                  <button class="edituserpassword"> تغير كلمة المرور</button>
                  <button class="deleteuser">حذف الطالب</button>
               </div>
            </div>
            <div class="userContent">
               <div class="ordersContent">
                  <?php

                  $query = "
    SELECT o.ID, o.post_date
    FROM {$wpdb->prefix}posts AS o
    INNER JOIN {$wpdb->prefix}postmeta AS om ON o.ID = om.post_id
    INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON o.ID = oi.order_id
    INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON oi.order_item_id = oim.order_item_id
    WHERE o.post_type = 'shop_order'
    AND o.post_status = 'wc-completed'
    AND oi.order_item_type = 'line_item'
    AND (
        (om.meta_key = '_customer_user' AND om.meta_value = %d)
        OR
        (oi.order_item_id IN (
            SELECT DISTINCT oi2.order_item_id
            FROM {$wpdb->prefix}woocommerce_order_items AS oi2
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2 ON oi2.order_item_id = oim2.order_item_id
            WHERE oi2.order_id = o.ID
            AND oi2.order_item_type = 'bundle'
        ))
    )
    GROUP BY o.ID
    HAVING COUNT(DISTINCT oim.meta_value) > 1
    LIMIT 7
";
                  $query = $wpdb->prepare($query, $user->ID);

                  // Execute the query
                  $orders = $wpdb->get_results($query);

                  if (count($orders)) {
                  ?>
                     <div class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
                        <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
                           اسم الكورس
                        </div>
                        <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
                           تاريخ الطلب
                        </div>
                        <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
                           موعد التجديد
                        </div>
                        <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status">
                           الحالة
                        </div>
                        <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
                           التكلفة
                        </div>
                        <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
                           خيارات
                        </div>
                     </div>
                     <?php

                     foreach ($orders as $customer_order) {
                        $order      = wc_get_order($customer_order); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited


                        $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                     ?>

                        <div class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                           <?php
                           $array = wc_get_account_orders_columns();

                           $res = array_slice($array, 0, 2, true) +
                              array("renew" => "الوقت المتبقى ") +
                              array_slice($array, 2, count($array) - 1, true);

                           foreach ($res  as $column_id => $column_name) :  ?>
                              <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">
                                 <?php if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) : ?>
                                    <?php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); ?>

                                 <?php elseif ('order-number' === $column_id) : ?>
                                    <?php
                                    $items = $order->get_items();
                                    $items_to_remove       = array();
                                    $course_time = '';
                                    // 4) group items for each user
                                    foreach ($items as $item_id => $item_data) {
                                       if (!$item_data['bundled_by']) {
                                          $course = Edumall_Tutor::instance()->get_course_by_wc_product($item_data['product_id']);
                                          echo  '<img src="' . get_the_post_thumbnail_url($course->ID) . '" height="80" width="80" />';
                                          $course_time .= get_post_meta($item_data['product_id'], 'course_time', true);
                                          echo  $item_data->get_name();
                                       }
                                    }
                                    ?>

                                 <?php elseif ('renew' === $column_id) :
                                    $ex =  explode(":", $course_time);
                                    $order_date_created = $order->get_date_created();

                                    $order_end_date = new DateTime($order_date_created);
                                    // Increment the date by one month
                                    $order_end_date_timestamp  = $order_end_date->modify('+' . $ex[0] . ' seconds');



                                    // Get the updated date
                                    $updated_date = $order_end_date->format('Y-m-d');

                                    // Output the updated date



                                 ?>
                                    <div class="countdown-item" data-start-date="<?php echo $order->get_date_created()->getTimestamp(); ?>" data-end-date="<?php echo $order_end_date_timestamp->getTimestamp(); ?>">
                                       <div>
                                          <span><?php echo $updated_date; ?></span>
                                       </div>
                                       <div class="countdown"></div>
                                    </div>

                                 <?php elseif ('order-date' === $column_id) : ?>
                                    <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo date('Y-m-d ', strtotime($order->get_date_created())); ?></time>

                                 <?php elseif ('order-status' === $column_id) : ?>
                                    <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>

                                 <?php elseif ('order-total' === $column_id) : ?>
                                    <?php
                                    /* translators: 1: formatted order total 2: total order items */
                                    echo wp_kses_post(sprintf(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'edumall'), $order->get_formatted_order_total(), $item_count));
                                    ?>

                                 <?php elseif ('order-actions' === $column_id) : ?>

                                    <a href="#" class="woocommerce-button button reBuy" data-order_id="<?php echo $order->get_id(); ?>">إعادة الشراء</a>
                                 <?php endif; ?>
                              </div>
                           <?php endforeach; ?>

                        </div>
                     <?php
                     }
                  } else {
                     ?>
                     <h6>لا يوجد طلبات</h6>
                  <?php } ?>
               </div>
               <div class="changepassword">
                  <?php wp_nonce_field('ajax-login-nonce', 'security'); ?>
                  <input type="hidden" name="email" value="<?php echo $user->user_email ?>" />
                  <div class="tutor-form-row">
                     <div class="tutor-form-col-6">
                        <div class="tutor-form-group">
                           <label>
                              كلمة المرور
                           </label>

                           <input type="password" name="password" value="" placeholder="كلمة المرور" required autocomplete="new-password">
                        </div>
                     </div>
                  </div>
                  <div class="tutor-form-group">
                     <button type="submit" name="tutor_register_student_btn" value="register" class="tutor-btn editpasswordsubmit">تعديل كلمة المرور</button>
                  </div>
               </div>

            </div>
      <?php }
      } else {
         echo 'No users found.';
      } ?>
      <?php

      $query = "
    SELECT o.ID, o.post_date
    FROM {$wpdb->prefix}posts AS o
    INNER JOIN {$wpdb->prefix}postmeta AS om ON o.ID = om.post_id
    INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON o.ID = oi.order_id
    INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON oi.order_item_id = oim.order_item_id
    WHERE o.post_type = 'shop_order'
    AND o.post_status = 'wc-completed'
    AND oi.order_item_type = 'line_item'
    AND (
        (om.meta_key = '_customer_user' AND om.meta_value = %d)
        OR
        (oi.order_item_id IN (
            SELECT DISTINCT oi2.order_item_id
            FROM {$wpdb->prefix}woocommerce_order_items AS oi2
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2 ON oi2.order_item_id = oim2.order_item_id
            WHERE oi2.order_id = o.ID
            AND oi2.order_item_type = 'bundle'
        ))
    )
    GROUP BY o.ID
    HAVING COUNT(DISTINCT oim.meta_value) > 1
    LIMIT 7
";
      $query = $wpdb->prepare($query, get_current_user_id());

      // Execute the query
      $parentOrders = $wpdb->get_results($query);



      if (count($parentOrders) > 0) {
      ?>
         <h2>طلباتي </h2>
         <div class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
            <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
               اسم الكورس
            </div>
            <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
               تاريخ الطلب
            </div>
            <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
               موعد التجديد
            </div>
            <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status">
               الحالة
            </div>
            <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
               التكلفة
            </div>
            <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
               خيارات
            </div>
         </div>
         <?php
         foreach ($parentOrders as $customer_order) {
            $order      = wc_get_order($customer_order); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited


            $item_count = $order->get_item_count() - $order->get_item_count_refunded();
         ?>

            <div class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
               <?php
               $array = wc_get_account_orders_columns();

               $res = array_slice($array, 0, 2, true) +
                  array("renew" => "الوقت المتبقى ") +
                  array_slice($array, 2, count($array) - 1, true);

               foreach ($res  as $column_id => $column_name) :  ?>
                  <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">
                     <?php if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) : ?>
                        <?php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); ?>

                     <?php elseif ('order-number' === $column_id) : ?>
                        <?php
                        $items = $order->get_items();
                        $items_to_remove       = array();
                        $course_time = '';
                        // 4) group items for each user
                        foreach ($items as $item_id => $item_data) {
                           if (!$item_data['bundled_by']) {
                              $course = Edumall_Tutor::instance()->get_course_by_wc_product($item_data['product_id']);
                              echo  '<img src="' . get_the_post_thumbnail_url($course->ID) . '" height="80" width="80" />';
                              $course_time .= get_post_meta($item_data['product_id'], 'course_time', true);
                              print_r($item_data->get_name());
                           }
                        }
                        ?>

                     <?php elseif ('renew' === $column_id) :
                        $ex =  explode(":", $course_time);
                        $order_date_created = $order->get_date_created();
                        $order_end_date = new DateTime($order_date_created);
                        // Increment the date by one month
                        $order_end_date_timestamp  = $order_end_date->modify('+' . $ex[0] . ' seconds');



                        // Get the updated date
                        $updated_date = $order_end_date->format('Y-m-d');

                        // Output the updated date



                     ?>
                        <div class="countdown-item" data-start-date="<?php echo $order->get_date_created()->getTimestamp(); ?>" data-end-date="<?php echo $order_end_date_timestamp->getTimestamp(); ?>">
                           <div>
                              <span><?php echo $updated_date; ?></span>
                           </div>
                           <div class="countdown"></div>
                        </div>

                     <?php elseif ('order-date' === $column_id) : ?>
                        <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo date('Y-m-d ', strtotime($order->get_date_created())); ?></time>


                     <?php elseif ('order-status' === $column_id) : ?>
                        <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>

                     <?php elseif ('order-total' === $column_id) : ?>
                        <?php
                        /* translators: 1: formatted order total 2: total order items */
                        echo wp_kses_post(sprintf(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'edumall'), $order->get_formatted_order_total(), $item_count));
                        ?>

                     <?php elseif ('order-actions' === $column_id) : ?>
                        <a href="#" class="woocommerce-button button reBuy" data-order_id="<?php echo $order->get_id(); ?>">إعادة الشراء</a>
                     <?php endif; ?>
                  </div>
               <?php endforeach; ?>

            </div>
      <?php
         }
      }
   } else {
      ?>

      <p>يجب عليك تسجيل الدخول اولا لاستعراض الصفحة</p>
   <?php
   } ?>
</div>

<?php get_footer(); ?>