<?php
defined('ABSPATH') || exit;

/**
 * Enqueue child scripts
 */
if (!function_exists('edumall_child_enqueue_scripts')) {
	function edumall_child_enqueue_scripts()
	{
		wp_enqueue_style('edumall-child-style', get_stylesheet_directory_uri() . '/style.css');
		wp_enqueue_style('custom-style', get_stylesheet_directory_uri() . '/assets/css/custom.css');
		wp_enqueue_script(
			'custom-script',
			get_stylesheet_directory_uri() . '/assets/js/custom_script.js',
			array('jquery')
		);
	}
}
add_action('wp_enqueue_scripts', 'edumall_child_enqueue_scripts', 15);
// saleh

function prefix_after_cart_item_name($cart_item, $cart_item_key)
{
	$parent = wc_pb_get_bundled_cart_item_container($cart_item);
	$product_id = $cart_item['product_id'];
	$product = wc_get_product($product_id);
	$notes = isset($cart_item['assignTo']) ? $cart_item['assignTo'] : '';
	$email = isset($cart_item['assignTo']) ? $cart_item['assignTo'] : '';
	if (!empty($notes)) {
		$user = get_user_by('email', $notes);
		$notes = $user->first_name . ' ' . $user->last_name;
	}
	$ret = '<div><p> قم باختيار طالب لشراء هذا الكورس له او لنفسك</p>';

	// if has childs
	global $current_user;
	// get_currentuserinfo();
	$user_query = new WP_User_Query(array(
		'meta_query' => array(
			array(
				'key' => 'parent_email',
				'value' =>  $current_user->user_email,
				'compare' => 'LIKE'
			),
		)
	));
	// User Loop
	// if (!empty($user_query->get_results())) {
	$ret .=  '<select name="select_student" class="select_student"  data-cart-id="' . $cart_item_key . '">';
	$ret .=  '<option value="' . $current_user->user_email . '">شراء لنفسي </option>';
	foreach ($user_query->get_results() as $user) {
		$selected = !empty($email) && $email == $user->user_email ? 'selected' : '';
		$ret .=  '<option value="' . $user->user_email . '" ' . $selected . '>' . $user->first_name . ' ' . $user->last_name . '</option>';
	}

	$ret .=  '<option value="other">طالب اخر</option>';
	$ret .=  "</select>";
	// }
	if (!$cart_item['bundled_by']) {
		if ($email != $current_user->user_email) {
			$disabled = !empty($email) ? 'disabled' : '';
			$ret .= '<div class="inputContainer"><input type="text" class="prefix-cart-notes" id="cart_notes_' . $cart_item_key . '" data-cart-id="' . $cart_item_key . '" value="' . $email . '" ' . $disabled . ' placeholder="البريد الالكتروني للطالب" /></div>';
		}

		if (!empty($email)) {
			$ret .= '<div class="assignTo"><p>شراء لـ ' . $notes . '</p>';

			if ($email != $current_user->user_email) {
				$ret  .= '<button><i class="fa fa-times"></i></button>';
			}

			$ret .= '<div>';
		}
		$ret .= '</div>';
		echo $ret;
	}
}
add_action('woocommerce_after_cart_item_name', 'prefix_after_cart_item_name', 10, 2);

/**
 * Enqueue our JS file
 */
function prefix_enqueue_scripts()
{
	// wp_enqueue_script( 'script-course-assign', get_template_directory_uri() . '/assets/js/course-assign.js', array(), '1.0.0', true );

	// trailingslashit( plugin_dir_url( __FILE__ ) ) . 'update-cart-item-ajax.js', array( 'jquery-blockui' ), time(), true );
	wp_register_script('script-course-assign',  EDUMALL_THEME_ASSETS_URI . '/js/course-assign.js', array('jquery-blockui'), '2.0.0', true);
	wp_localize_script(
		'script-course-assign',
		'prefix_vars',
		array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'redirecturl' => home_url(),
		)
	);
	wp_enqueue_script('script-course-assign');
}
add_action('wp_enqueue_scripts', 'prefix_enqueue_scripts');




/**
 * Update cart item notes
 */
function check_user_exists()
{
	// Do a nonce check
	if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'woocommerce-cart')) {
		wp_send_json(array('nonce_fail' => 1));
		exit;
	}
	$exists = email_exists(trim($_POST['email']));
	if ($exists) {
		$user = get_user_by('id', $exists);
		$name = $user->first_name . ' ' . $user->last_name;
		wp_send_json(array('success' => 1, 'user_id' => $exists, 'name' => $name));
	} else {
		$error = new WP_Error('001', 'No user information was retrieved.', 'Some information');
		wp_send_json_error($error);
	}
	exit;
}
add_action('wp_ajax_check_user_exists', 'check_user_exists');
add_action('wp_ajax_nopriv_check_user_exists', 'check_user_exists');


/**
 * Update cart item notes
 */
function prefix_update_cart_notes()
{
	// Do a nonce check
	if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'woocommerce-cart')) {
		wp_send_json(array('nonce_fail' => 1));
		exit;
	}
	// Save the assignTo to the cart meta
	$cart = WC()->cart->cart_contents;
	$cart_id = $_POST['cart_id'];
	$assignTo = $_POST['assignTo'];
	$cart_item = $cart[$cart_id];
	$cart_item['assignTo'] = $assignTo;
	WC()->cart->cart_contents[$cart_id] = $cart_item;
	WC()->cart->set_session();
	wp_send_json(array('success' => 1));
	exit;
}
add_action('wp_ajax_prefix_update_cart_notes', 'prefix_update_cart_notes');
add_action('wp_ajax_nopriv_prefix_update_cart_notes', 'prefix_update_cart_notes');
/**
 * Remove cart item notes
 */
function prefix_remove_cart_notes()
{
	// Do a nonce check
	if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'woocommerce-cart')) {
		wp_send_json(array('nonce_fail' => 1));
		exit;
	}
	// Save the notes to the cart meta
	$cart = WC()->cart->cart_contents;
	$cart_id = $_POST['cart_id'];
	$cart_item = $cart[$cart_id];
	unset($cart_item['assignTo']);
	WC()->cart->cart_contents[$cart_id] = $cart_item;
	WC()->cart->set_session();
	wp_send_json(array('success' => 1));
	exit;
}
add_action('wp_ajax_prefix_remove_cart_notes', 'prefix_remove_cart_notes');
add_action('wp_ajax_nopriv_prefix_remove_cart_notes', 'prefix_remove_cart_notes');

function prefix_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
	foreach ($item as $cart_item_key => $cart_item) {
		if (isset($cart_item['assignTo'])) {
			$item->add_meta_data('assignTo', $cart_item['assignTo'], true);
		}
	}
}
add_action('woocommerce_checkout_create_order_line_item', 'prefix_checkout_create_order_line_item', 10, 4);

// add_action('woocommerce_cart_calculate_totals', 'apply_fixed_discount_on_cart_items');

// function apply_fixed_discount_on_cart_items($cart)
// {
//     $packageCount = [];

//     foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
//         $bundle_id = wc_pb_get_bundled_cart_item_container($cart_item);
//         echo $bundle_id . '<br />';
//         die;

//         if ($bundle_id && has_term(317, 'product_cat', $bundle_id)) {
//             $packageCount[$bundle_id] = $bundle_id;
//         }
//     }
//     $packageCount = count(array_unique($packageCount));
//     if ($packageCount == 2) {
//         $discount_amount = 13.33;
//     } elseif ($packageCount == 3) {
//         $discount_amount = 26.66;
//     } elseif ($packageCount == 4) {
//         $discount_amount = 40;
//     }
//     if ($packageCount > 1 && $packageCount < 5) {
//         $discount_reason = 'packages Discount'; // set the reason for the discount here

//         WC()->cart->add_fee(sanitize_text_field($discount_reason), -$discount_amount);
//     }
//     // die;
// }
function woo_add_cart_fee()
{

	global $woocommerce;
	$packageCount = [];

	foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {

		$product     = wc_get_product($cart_item['product_id']);
		if ($product->is_type('bundle') && is_object_in_term($cart_item['product_id'], 'product_cat', 327)) {
			// echo 'YEs<br />';
			$packageCount[$cart_item['product_id']] = $cart_item['product_id'];
		}
		// if ($product->is_type('bundle')) {

		//     $term_obj_list = get_the_terms($cart_item['product_id'], 'product_cat');
		//     print_r($term_obj_list);

		// }
		// $bundle_id = wc_pb_get_bundled_cart_item_container($cart_item, false, true);
		// // echo  has_term(327, 'product_cat', $cart_item['product_id']) . '<br />';
		// if (has_term(327, 'product_cat', $cart_item['product_id'])) {
		// echo  $cart_item['product_id'];
		// echo has_term(317, 'product_cat', $cart_item['product_id']);
		// die;
		// $packageCount[$bundle_id] = $bundle_id;
		// }
	}
	$packageCount = count(array_unique($packageCount));
	if ($packageCount == 2) {
		$discount_amount = 13.33;
	} elseif ($packageCount == 3) {
		$discount_amount = 26.66;
	} elseif ($packageCount == 4) {
		$discount_amount = 40;
	}
	if ($packageCount > 1 && $packageCount < 5) {
		$discount_reason = 'packages Discount'; // set the reason for the discount here

		$woocommerce->cart->add_fee(sanitize_text_field($discount_reason), -$discount_amount);
	}
	// $woocommerce->cart->add_fee(__('Custom', 'woocommerce'), 5);
}
add_action('woocommerce_cart_calculate_fees', 'woo_add_cart_fee');
// add_action('woocommerce_order_status_completed', 'rudr_complete_for_status');
add_action('woocommerce_payment_complete', 'rudr_complete_for_status');

function rudr_complete_for_status($order_id)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$parent_user = wp_get_current_user();


	$assignToUsers = array();
	// 1) Get the Order object
	$order = wc_get_order($order_id);
	// echo $parent_user->user_email;
	// die;
	// 3) Get the order items
	$items = $order->get_items();
	$items_to_remove       = array();
	// 4) group items for each user
	$packageCount = [];
	foreach ($items as $item_id => $item_data) {
		$product_id = $item_data->get_product_id();
		$product = wc_get_product($product_id);
		$product_type = $product->get_type();

		// Output the product type
		// Check if the product is a parent product
		if ($product->is_type('bundle') || $product->is_type('simple')) {
			// if (!isset($item_data['assignTo']) || $item_data['assignTo'] == $parent_user->user_email) {
			//     // Cancel the original order
			//     $order->update_status('cancelled', __('Order cancelled due to email mismatch in item meta.', 'text-domain'));
			//     // break; // Exit the loop after cancelling the order
			// }

			if (isset($item_data['assignTo'])) {
				$assignToUsers[$item_data['assignTo']][] = $item_data;
			}
		}
	}

	if (!array_key_exists($parent_user->user_email, $assignToUsers)) {
		//     echo 'Yesss im in ';
		$moowoodle_moodle_user_id = search_for_moodle_user('email', trim($parent_user->user_email));
		if ($moowoodle_moodle_user_id > 0) {
			// echo 'Yesss im in '
			//     . $moowoodle_moodle_user_id;
			$enrolments = get_enrollment_data($order_id, $moowoodle_moodle_user_id, 1);

			if (empty($enrolments)) {
				return;
			}
			$enrolment_data = $enrolments;
			foreach ($enrolments as $key => $value) {
				unset($enrolments[$key]['linked_course_id']);
				unset($enrolments[$key]['course_name']);
			}
			// print_r($enrolments);
			moowoodle_moodle_core_function_callback('enrol_users', array('enrolments' => $enrolments));
			add_post_meta($order_id, 'moodle_user_enrolled', "false");
			add_post_meta($order_id, 'moodle_user_enrolment_date', time());
			// send confirmation email
			// do_action('moowoodle_after_enrol_moodle_user', $enrolment_data);
		}

		$order->update_status('cancelled');
	}

	// 5) loop items and create order for each user
	foreach ($assignToUsers as $key => $course_items) {
		if ($key == $parent_user->user_email) {
			continue;
		}
		$user = get_user_by('email', $key);
		$new_order_args = array(
			'customer_id' => $user->ID,
		);
		$new_order = wc_create_order($new_order_args);
		foreach ($course_items as $key2 => $item) {
			$product_new     = wc_get_product($item['product_id']);
			if ($product_new->is_type('bundle')) {
				$result = WC_PB()->order->add_bundle_to_order($product_new, $new_order, 1, array(
					'subtotal'     => 0,
					'total'        => 0,
				));
			} else {
				$new_order->add_product($product_new, 1, array(
					'subtotal'     => 0,
					'total'        => 0,
				));
			}
		}

		if (class_exists('WC_Subscriptions_Product') && WC_Subscriptions_Product::is_subscription($product_new)) {
			$sub = wcs_create_subscription(array(
				'order_id' => $new_order->get_id(),
				'status' => 'pending', // Status should be initially set to pending to match how normal checkout process goes
				'billing_period' => WC_Subscriptions_Product::get_period($product_new),
				'billing_interval' => WC_Subscriptions_Product::get_interval($product_new)
			));

			if (is_wp_error($sub)) {
				return false;
			}

			// Modeled after WC_Subscriptions_Cart::calculate_subscription_totals()
			$start_date = gmdate('Y-m-d H:i:s');
			// Add product to subscription
			$sub->add_product($product_new, 1);

			$dates = array(
				'trial_end'    => WC_Subscriptions_Product::get_trial_expiration_date($product_new, $start_date),
				'next_payment' => WC_Subscriptions_Product::get_first_renewal_payment_date($product_new, $start_date),
				'end'          => WC_Subscriptions_Product::get_expiration_date($product_new, $start_date),
			);

			$sub->update_dates($dates);
			$sub->calculate_totals();

			// Update order status with custom note
			$note = !empty($note) ? $note : __('Programmatically added order and subscription.');
			$new_order->update_status('completed', $note, true);
			// Also update subscription status to active from pending (and add note)
			$sub->update_status('active', $note, true);
		} else {
			$new_order->update_status('completed');
		}
		$new_order->save();
	}
}

// split items
function bbloomer_split_product_individual_cart_items($cart_item_data, $product_id)
{
	$unique_cart_item_key = uniqid();
	$cart_item_data['unique_key'] = $unique_cart_item_key;
	return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'bbloomer_split_product_individual_cart_items', 10, 2);
add_filter('woocommerce_cart_item_quantity', 'wc_cart_item_quantity', 10, 3);
function wc_cart_item_quantity($product_quantity, $cart_item_key, $cart_item)
{
	if (is_cart()) {
		$product_quantity = sprintf('%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity']);
	}
	return $product_quantity;
}
// allow user to pay for anther user
add_filter('user_has_cap', 'bbloomer_order_pay_without_login', 9999, 3);

function bbloomer_order_pay_without_login($allcaps, $caps, $args)
{

	//    if ( isset( $caps[0], $_GET['key'] ) ) {
	if ($caps[0] == 'view_order') {
		//  $order_id = isset( $args[2] ) ? $args[2] : null;
		//  $order = wc_get_order( $order_id );
		//  if ( $order ) {
		$allcaps['view_order'] = true;
		//  }
		//   }
	}
	return $allcaps;
}
/**
 * Exclude Post Type from Search
 */
add_action('init', 'excludePostTypeFromSearch', 99);

function excludePostTypeFromSearch()
{
	global $wp_post_types;

	if (post_type_exists('product') && isset($wp_post_types['product'])) {
		$wp_post_types['product']->exclude_from_search = true;
	}
}
add_action('template_redirect', 'wpse_128636_redirect_post');

function wpse_128636_redirect_post()
{
	if (is_singular('product')) :
		wp_redirect(home_url(), 301);
		exit;
	endif;
}


// add Students
add_action('show_user_profile', 'my_user_profile_edit_action');
add_action('edit_user_profile', 'my_user_profile_edit_action');
function my_user_profile_edit_action($user)
{
?>
	<h3>Other</h3>
	<table class="form-table" id="fieldset-billing">
		<tbody>
			<tr>
				<th>
					<label for="parent_email">Parent Email</label>
				</th>
				<td>
					<input name="parent_email" type="text" id="parent_email" value="<?php echo $user->parent_email; ?>">
				</td>
			</tr>
			</body>
	</table>

<?php
}
add_action('personal_options_update', 'my_user_profile_edit_action_save');
add_action('edit_user_profile_update', 'my_user_profile_edit_action_save');

function my_user_profile_edit_action_save($user_id)
{
	update_user_meta($user_id, 'parent_email', sanitize_email($_POST['parent_email']));
}

function ajax_register()
{
	// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
	global $MooWoodle;
	$parent_user = wp_get_current_user();
	// print_r($_POST);
	// First check the nonce, if it fails the function will break
	check_ajax_referer('ajax-login-nonce', 'security');
	if (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
		echo json_encode(array('success' => false, 'message' => 'البريد الالكتروني غير صحيح'));
	} else {
		// Nonce is checked, get the POST data and sign user on
		$info = array();


		// $info['user_nicename'] = $info['nickname'] = $info['display_name']  = $info['user_login'] = sanitize_user($_POST['username']) ;
		// $info['user_pass'] = password_generator();
		$info['user_pass'] = $_POST['password'];
		$info['user_login'] = sanitize_text_field($_POST['user_login']);
		$info['user_email'] = sanitize_email(trim($_POST['email']));
		$info['first_name'] = sanitize_text_field($_POST['first_name']);
		$info['last_name'] = sanitize_text_field($_POST['last_name']);




		// Register the user
		$user_register = wp_insert_user($info);
		if (is_wp_error($user_register)) {
			$error  = $user_register->get_error_codes();
			if (in_array('empty_user_login', $error))
				echo json_encode(array('success' => false, 'message' => __($user_register->get_error_message('empty_user_login'))));
			elseif (in_array('existing_user_login', $error))
				echo json_encode(array('success' => false, 'message' => 'اسم المستخدم موجود بالفعل'));
			elseif (in_array('existing_user_email', $error))
				echo json_encode(array('success' => false, 'message' => 'البريد الالكتروني مسجل بالفعل'));
		} else {
			update_user_meta($user_register, 'parent_email', sanitize_email($_POST['parent_email']));


			// moodle
			$user_id = $user_register;
			$conn_settings = $MooWoodle->options_general_settings;
			$moowoodle_moodle_user_id = search_for_moodle_user('email', trim($_POST['email']));
			if ($moowoodle_moodle_user_id > 0) {
				//User exist in Moodle

				// if ( isset( $conn_settings[ 'update_moodle_user' ] ) && $conn_settings[ 'update_moodle_user' ] == "Enable" ) { //Allow Moodle user to be updated

				// $moowoodle_moodle_user_id = $this->update_moodle_user( $moowoodle_moodle_user_id );
				$user = ($user_id != 0) ? get_userdata($user_id) : false;
				$billing_email = trim($_POST['email']);



				// $username = $billing_email;
				// if( $user ) {
				//     $username = $user->user_login;
				//     } else {
				//     $user = get_user_by( 'email', $billing_email );
				//     if( $user ) {
				//         $username = $user->data->user_login;
				//     }
				// }
				// $username = str_replace( ' ', '', $username );
				// $username = strtolower( $username );
				$username = $user->user_login;
				$username = str_replace(' ', '', $username);
				$username = strtolower($username);
				$pwd = $info['user_pass'];
				add_user_meta($user_id, 'moowoodle_moodle_user_pwd', $pwd);
				$user_data = array();
				$user_data['id'] = $moowoodle_moodle_user_id;
				$user_data['username'] = $username;
				$user_data['firstname'] = 'salehhardcoded';
				$user_data['lastname'] = 'salehhardcoded';
				$user_data['firstname'] = $info['first_name'];
				$user_data['lastname'] = $info['last_name'];
				$user_data['password'] = $pwd;
				// $user_data['profile_field_parent_email'] = $parent_user->user_email ;	
				$user_data['auth'] = 'manual';
				// $a=get_locale();
				// $b=strtolower($a);
				// $user_data['lang'] = substr($b,0,2);
				$user_data['suspended'] = 0;
				$user_data['preferences'][0]['type'] = "auth_forcepasswordchange";
				$user_data['preferences'][0]['value'] = 0;
				$moodle_user = moowoodle_moodle_core_function_callback('update_users', array('users' => array($user_data)));
				// if( ! empty( $moodle_user ) && array_key_exists( 0, $moodle_user ) ) {
				// send email with credentials
				sendEmail($user->ID, $info['user_pass']);
				echo json_encode(array('success' => true, 'message' => 'تم اضافة الطالب بنجاح', 'user_data' => $user_data, 'user' => $moodle_user));
				// }
				// } else { //User does not exist in Moodle
				//     // $moowoodle_moodle_user_id = $this->create_moodle_user();		

				// }
				// update_user_meta( $user_id, 'moowoodle_moodle_user_id', $moowoodle_moodle_user_id );
			} else {
				// create moodle user


				$user = ($user_id != 0) ? get_userdata($user_id) : false;
				// $billing_email = trim($_POST['email']);



				// print_r($user);
				// die;

				// $username = $billing_email;
				// if( $user ) {
				//     $username = $user->user_login;
				//     } else {
				//     $user = get_user_by( 'email', $billing_email );
				//     if( $user ) {
				//         $username = $user->data->user_login;
				//     }
				// // }
				// $username = str_replace( ' ', '', $username );
				// $username = strtolower( $username );

				$username = $user->user_login;
				$username = str_replace(' ', '', $username);
				$username = strtolower($username);

				$pwd = $info['user_pass'];
				add_user_meta($user_id, 'moowoodle_moodle_user_pwd', $pwd);

				$user_data = array();
				$user_data['email'] = $user->user_email;
				$user_data['username'] = $username;
				$user_data['password'] = $pwd;
				$user_data['auth'] = 'manual';
				// $a=get_locale();
				// $b=strtolower($a);
				// $user_data['lang'] = substr($b,0,2);
				$user_data['firstname'] = $info['first_name'];
				$user_data['lastname'] = $info['last_name'];
				// $user_data['profile_field_parent_email'] = $parent_user->user_email ;	
				$user_data['preferences'][0]['type'] = "auth_forcepasswordchange";
				$user_data['preferences'][0]['value'] = 0;
				$moodle_user = moowoodle_moodle_core_function_callback('create_users', array('users' => array($user_data)));
				if (!empty($moodle_user) && array_key_exists(0, $moodle_user)) {
					// send email with credentials
					sendEmail($user->ID, $info['user_pass']);
					echo json_encode(array('success' => true, 'message' => 'تم اضافة الطالب بنجاح', 'user_data' => $user_data));
				}
				// create moodle user
			}
		}
	}


	die();
}

// Enable the user with no privileges to run ajax_register() in AJAX
add_action('wp_ajax_nopriv_ajaxregister', 'ajax_register');
add_action('wp_ajax_ajaxregister', 'ajax_register');
function ajax_edit_user()
{
	// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
	global $MooWoodle;
	$parent_user = wp_get_current_user();
	check_ajax_referer('ajax-login-nonce', 'security');
	$user = get_user_by('email', $_POST['email']);
	$moowoodle_moodle_user_id = search_for_moodle_user('email', trim($_POST['email']));
	if ($moowoodle_moodle_user_id > 0) {
		add_user_meta($user->ID, 'moowoodle_moodle_user_pwd', $_POST['password']);
		$user_data = array();
		$user_data['id'] = $moowoodle_moodle_user_id;
		$user_data['password'] = $_POST['password'];
		$moodle_user = moowoodle_moodle_core_function_callback('update_users', array('users' => array($user_data)));
		echo json_encode(array('success' => true, 'message' => 'تم تعديل كلمة المرور بنجاح'));
	}

	die();
}

add_action('wp_ajax_nopriv_ajaxediteuser', 'ajax_edit_user');
add_action('wp_ajax_ajaxediteuser', 'ajax_edit_user');
add_action('wp_ajax_nopriv_reordertocart', 'reordertocart');
add_action('wp_ajax_reordertocart', 'reordertocart');
function reordertocart()
{
	//  error_reporting(E_ALL);
	// ini_set('display_errors', 1);
	$cart = new WC_Cart();
	$order_id = $_POST['order_id'];
	$order = wc_get_order($order_id);
	$items = $order->get_items();

	// loop through the items and do something with them
	foreach ($items as $item) {
		$product_id = $item->get_product_id();
		$quantity = $item->get_quantity();
		$bundle_id = wc_pb_get_bundled_order_item_container($item, $order, true);
		if ($bundle_id) {
			WC_PB()->cart->add_bundle_to_cart($bundle_id,  $quantity, array(), array('assignTo' => $order->get_billing_email()));
		} else {
			WC()->cart->add_to_cart($product_id, $quantity, 0, array(), array('assignTo' => $order->get_billing_email()));
		}


		WC()->cart->set_session();
	}
	echo json_encode(array('success' => true, 'message' => 'تم اضاقة الي سلة الشراء بنجاح توجه لاتمام الطلب'));

	die();
}
function ajax_user_delete()
{
	if (wp_delete_user($_POST['user_id'])) {
		echo json_encode(array('success' => true, 'message' => 'تم حذف الطالب بنجاح'));
	} else {
		echo json_encode(array('success' => false, 'message' => 'يوجد مشكلة في حذف الطالب'));
	}

	die();
}

// Enable the user with no privileges to run ajax_user_delete() in AJAX
add_action('wp_ajax_nopriv_ajaxuserdelete', 'ajax_user_delete');
add_action('wp_ajax_ajaxuserdelete', 'ajax_user_delete');
function password_generator()
{
	$length = 8;
	$sets = array();
	$sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
	$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	$sets[] = '23456789';
	// $sets[]  = '~!@#$%^&*(){}[],./?';
	$password = '';
	//append a character from each set - gets first 4 characters
	foreach ($sets as $set) {
		$password .= $set[array_rand(str_split($set))];
	}
	//use all characters to fill up to $length
	while (strlen($password) < $length) {
		//get a random set
		$randomSet = $sets[array_rand($sets)];
		//add a random char from the random set
		$password .= $randomSet[array_rand(str_split($randomSet))];
	}
	//shuffle the password string before returning!
	return str_shuffle($password);
}

function search_for_moodle_user($field, $values)
{
	$users = moowoodle_moodle_core_function_callback('get_moodle_users', array('criteria' => array(array('key' => $field, 'value' => $values))));
	if (!empty($users) && !empty($users['users'])) {
		return $users['users'][0]['id'];
	}
	return 0;
}
function update_moodle_user($moowoodle_moodle_user_id = 0)
{
	$user_data = get_moodle_user_data($moowoodle_moodle_user_id);
	return $moowoodle_moodle_user_id;
}
function get_moodle_user_data($moowoodle_moodle_user_id = 0)
{
	$wc_order = $this->wc_order;
	$user_id = $wc_order->get_user_id();
	$user = ($user_id != 0) ? get_userdata($user_id) : false;
	$billing_email = $wc_order->get_billing_email();



	$username = $billing_email;
	if ($user) {
		$username = $user->user_login;
	} else {
		$user = get_user_by('email', $billing_email);
		if ($user) {
			$username = $user->data->user_login;
		}
	}
	$username = str_replace(' ', '', $username);
	$username = strtolower($username);
	$moodle_pwd_meta = get_user_meta($user_id, 'moowoodle_moodle_user_pwd', true);
	$pwd = '';
	if (empty($moodle_pwd_meta)) {
		$pwd = $this->password_generator();
		add_user_meta($user_id, 'moowoodle_moodle_user_pwd', $pwd);
	} else {
		$pwd = $moodle_pwd_meta;
	}
	$user_data = array();
	if ($moowoodle_moodle_user_id) {
		$user_data['id'] = $moowoodle_moodle_user_id;
	} else {
		$user_data['email'] = ($user && $user->user_email != $billing_email) ? $user->user_email : $billing_email;
		$user_data['username'] = $username;
		$user_data['password'] = $pwd;
		$user_data['auth'] = 'manual';
		$a = get_locale();
		$b = strtolower($a);
		$user_data['lang'] = substr($b, 0, 2);
	}
	$user_data['firstname'] = $wc_order->get_billing_first_name();
	$user_data['lastname'] = $wc_order->get_billing_last_name();
	// $user_data['city'] = $wc_order->get_billing_city();
	// $user_data['country'] = $wc_order->get_billing_country();
	//$user_data['preferences'][0]['type'] = "auth_forcepasswordchange";
	//$user_data['preferences'][0]['value'] = 1;

	return apply_filters('moowoodle_moodle_users_data', $user_data, $wc_order);
}
function get_enrollment_data($order_id, $moowoodle_moodle_user_id, $suspend = 0)
{
	$wc_order = wc_get_order($order_id);
	$enrolments = array();
	$items = $wc_order->get_items();
	$role_id = apply_filters('moowoodle_enrolled_user_role_id', 5);
	if (!empty($items)) {
		foreach ($items as  $item) {
			$course_id = get_post_meta($item['product_id'], 'moodle_course_id', true);

			$course_time = get_post_meta($item['product_id'], 'course_time', true);
			$isbundle = wc_pb_is_bundled_order_item($item, $wc_order);
			if ($isbundle) {
				$container_item = wc_pb_get_bundled_order_item_container($item, $wc_order);
				$course_time = get_post_meta($container_item['product_id'], 'course_time', true);
			}

			if (!empty($course_id)) {
				$enrolment = array();
				if (!empty($course_time)) {
					$enrolment['timestart'] = time(); //intval( $course_id );
					$enrolment['timeend'] = time() + intval($course_time);

					add_post_meta($wc_order->get_id(), 'moodle_user_enrolment_date_' . $wc_order->get_id() . '_' . $item['product_id'], $enrolment['timeend'], true);
				}
				$enrolment['courseid'] = intval($course_id);
				$enrolment['userid'] = $moowoodle_moodle_user_id;
				$enrolment['roleid'] =  $role_id;
				$enrolment['suspend'] = $suspend;
				$enrolment['linked_course_id'] =  get_post_meta($item['product_id'], 'linked_course_id', true);
				$enrolment['course_name'] = get_the_title($item['product_id']);
				$enrolments[] = $enrolment;
			}
		}
	}
	return apply_filters('moowoodle_moodle_enrolments_data', $enrolments);
}
function sendEmail($user_id, $pass)
{
	$parent_user = wp_get_current_user();
	// send email
	$user = get_userdata($user_id);

	$subject   = 'تم اضافة حساب طالب لك في ' . get_bloginfo('name');
	$mail_from = get_bloginfo('admin_email');
	$mail_to   = $parent_user->user_email;
	$headers   = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $mail_from,
	);
	$emaildata = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
			<head>
			<!--[if gte mso 9]>
			<xml>
			<o:OfficeDocumentSettings>
			<o:AllowPNG/>
			<o:PixelsPerInch>96</o:PixelsPerInch>
			</o:OfficeDocumentSettings>
			</xml>
			<![endif]-->
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta name="x-apple-disable-message-reformatting">
			<!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
			<title></title>
			
			<style type="text/css">
			@media only screen and (min-width: 570px) {
			.u-row {
			width: 550px !important;
			}
			.u-row .u-col {
			vertical-align: top;
			}
			
			.u-row .u-col-100 {
			width: 550px !important;
			}
			
			}
			
			@media (max-width: 570px) {
			.u-row-container {
			max-width: 100% !important;
			padding-left: 0px !important;
			padding-right: 0px !important;
			}
			.u-row .u-col {
			min-width: 320px !important;
			max-width: 100% !important;
			display: block !important;
			}
			.u-row {
			width: calc(100% - 40px) !important;
			}
			.u-col {
			width: 100% !important;
			}
			.u-col > div {
			margin: 0 auto;
			}
			}
			body {
			margin: 0;
			padding: 0;
			}
			
			table,
			tr,
			td {
			vertical-align: top;
			border-collapse: collapse;
			}
			
			p {
			margin: 0;
			}
			
			.ie-container table,
			.mso-container table {
			table-layout: fixed;
			}
			
			* {
			line-height: inherit;
			}
			
			a[x-apple-data-detectors="true"] {
			color: inherit !important;
			text-decoration: none !important;
			}
			
			table, td { color: #000000; } a { color: #0000ee; text-decoration: underline; } @media (max-width: 480px) { #u_content_image_2 .v-container-padding-padding { padding: 40px !important; } #u_content_image_2 .v-src-width { width: auto !important; } #u_content_image_2 .v-src-max-width { max-width: 60% !important; } #u_content_text_1 .v-container-padding-padding { padding: 30px 30px 30px 20px !important; } #u_content_text_3 .v-container-padding-padding { padding: 30px 30px 80px 20px !important; } }
			</style>
			
			
			
			<!--[if !mso]><!--><link href="https://fonts.googleapis.com/css?family=Crimson+Text:400,700&display=swap" rel="stylesheet" type="text/css"><!--<![endif]-->
			
			</head>
			
			<body class="clean-body u_body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #9e278e;color: #000000">
			<!--[if IE]><div class="ie-container"><![endif]-->
			<!--[if mso]><div class="mso-container"><![endif]-->
			<table style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #9e278e;width:100%" cellpadding="0" cellspacing="0">
			<tbody>
			<tr style="vertical-align: top">
			<td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
			<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color: #9e278e;"><![endif]-->
			
			
			<div class="u-row-container" style="padding: 0px;background-color: transparent">
			<div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 550px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
			<div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
			<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:550px;"><tr style="background-color: transparent;"><![endif]-->
			
			<!--[if (mso)|(IE)]><td align="center" width="550" style="width: 550px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
			<div class="u-col u-col-100" style="max-width: 320px;min-width: 550px;display: table-cell;vertical-align: top;">
			<div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
			<!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
			
			<!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
			</div>
			</div>
			<!--[if (mso)|(IE)]></td><![endif]-->
			<!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
			</div>
			</div>
			</div>
			
			
			
			<div class="u-row-container" style="padding: 0px;background-color: transparent">
			<div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 550px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;">
			<div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
			<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:550px;"><tr style="background-color: #ffffff;"><![endif]-->
			
			<!--[if (mso)|(IE)]><td align="center" width="542" style="width: 542px;padding: 0px;border-top: 4px solid #d9d8d8;border-left: 4px solid #d9d8d8;border-right: 4px solid #d9d8d8;border-bottom: 4px solid #d9d8d8;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
			<div class="u-col u-col-100" style="max-width: 320px;min-width: 550px;display: table-cell;vertical-align: top;">
			<div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
			<!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 4px solid #d9d8d8;border-left: 4px solid #d9d8d8;border-right: 4px solid #d9d8d8;border-bottom: 4px solid #d9d8d8;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
			
			<table id="u_content_image_2" style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
			<tbody>
			<tr>
			<td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:40px 10px 10px;font-family:arial,helvetica,sans-serif;" align="right">
			
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
			<td style="padding-right: 0px;padding-left: 0px;" align="center">
			
			<img align="center" border="0" src="https://edunovel.com/image-1.png" alt="Welcome" title="Welcome" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 28%;max-width: 148.4px;" width="148.4" class="v-src-width v-src-max-width"/>
			
			</td>
			</tr>
			</table>
			
			</td>
			</tr>
			</tbody>
			</table>
			
			<table id="u_content_text_1" style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
			<tbody>
			<tr>
			<td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:10px 30px 30px 40px;font-family:arial,helvetica,sans-serif;" align="right">
			
			<div style="color: #333333; line-height: 140%; text-align: right; word-wrap: break-word;">
			<p style="font-size: 14px; line-height: 140%;"><span style="font-family: "Crimson Text", serif; font-size: 14px; line-height: 19.6px;"><strong><span style="font-size: 22px; line-height: 30.8px;">اهلا بك  ' . $parent_user->user_login . '</span></strong></span></p>
			<p style="font-size: 14px; line-height: 140%;">&nbsp;</p>
			<p style="line-height: 140%; font-size: 14px;"><span style="font-family: Crimson Text, serif;"><span style="font-size: 18px; line-height: 25.2px;">تم تسجيل حساب لابنك بنجاح</span></span></p>
			<p style="line-height: 140%; font-size: 14px;"><span style="font-family: Crimson Text, serif;"><span style="font-size: 18px; line-height: 25.2px;">فيما يلي بيانات تسجيل الدخول</span></span></p>
			<p style="line-height: 140%; font-size: 14px;"><span style="font-family: Crimson Text, serif;"><span style="font-size: 18px; line-height: 25.2px;"> <a rel="noopener" href="https://lms.edunovel.com" target="_blank">https://lms.edunovel.com</a>:رابط الدخول</span></span></p>
			<p style="line-height: 140%; font-size: 14px;"><span style="font-family: Crimson Text, serif;"><span style="font-size: 18px; line-height: 25.2px;">' . $user->user_email . ':البريد الالكتروني</span></span></p>
			<p style="line-height: 140%; font-size: 14px;"><span style="font-family: Crimson Text, serif;"><span style="font-size: 18px; line-height: 25.2px;">كلمة المرور:</span><p>' . $pass . '</p></span></p>
			<p style="line-height: 140%; font-size: 14px;">&nbsp;</p>
			<p style="line-height: 140%; font-size: 14px;">&nbsp;</p>
			</div>
			
			</td>
			</tr>
			</tbody>
			</table>
			
			<table id="u_content_text_3" style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
			<tbody>
			<tr>
			<td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:30px 30px 80px 40px;font-family:arial,helvetica,sans-serif;" align="right">
			
			<div style="color: #333333; line-height: 140%; text-align: right; word-wrap: break-word;">
			<p style="font-size: 14px; line-height: 140%;"><span style="font-size: 18px; line-height: 25.2px; font-family: "Crimson Text", serif;">Feel free to reach out to us with any questions.</span></p>
			<p style="font-size: 14px; line-height: 140%;">&nbsp;</p>
			<p style="font-size: 14px; line-height: 140%;"><span style="font-size: 22px; line-height: 30.8px;"><strong><span style="line-height: 30.8px; font-family: "Crimson Text", serif; font-size: 22px;">Thanks.</span></strong></span><br /><span style="font-size: 18px; line-height: 25.2px; font-family: "Crimson Text", serif;">Edunovel</span></p>
			</div>
			
			</td>
			</tr>
			</tbody>
			</table>
			
			<!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
			</div>
			</div>
			<!--[if (mso)|(IE)]></td><![endif]-->
			<!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
			</div>
			</div>
			</div>
			
			
			
			<div class="u-row-container" style="padding: 0px;background-color: transparent">
			<div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 550px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
			<div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
			<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 0px;background-color: transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:550px;"><tr style="background-color: transparent;"><![endif]-->
			
			<!--[if (mso)|(IE)]><td align="center" width="550" style="width: 550px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;" valign="top"><![endif]-->
			<div class="u-col u-col-100" style="max-width: 320px;min-width: 550px;display: table-cell;vertical-align: top;">
			<div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
			<!--[if (!mso)&(!IE)]><!--><div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
			
			<table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
			<tbody>
			<tr>
			<td class="v-container-padding-padding" style="overflow-wrap:break-word;word-break:break-word;padding:10px 0px 21px;font-family:arial,helvetica,sans-serif;" align="left">
			
			<table height="0px" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 3px solid #000000;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
			<tbody>
			<tr style="vertical-align: top">
			<td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
			<span>&#160;</span>
			</td>
			</tr>
			</tbody>
			</table>
			
			</td>
			</tr>
			</tbody>
			</table>
			
			<!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
			</div>
			</div>
			<!--[if (mso)|(IE)]></td><![endif]-->
			<!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
			</div>
			</div>
			</div>
			
			
			<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
			</td>
			</tr>
			</tbody>
			</table>
			<!--[if mso]></div><![endif]-->
			<!--[if IE]></div><![endif]-->
			</body>
			
			</html>
			';

	wp_mail($mail_to, $subject, $emaildata, $headers);
}
function getlink($email, $fname, $lname)
{
	$url = "https://lms.edunovel.com/getlogin.php";
	$response = wp_remote_post(
		$url,
		array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array('email' => $email, 'fname' => $fname, 'lname' => $lname),
			'cookies' => array()
		)
	);


	$respond = $response['body'];

	return $respond;
}
/**
 * Removes angle brackets (characters < and >) arounds URLs in a given string
 *
 * @param string $string    The string to remove potential angle brackets from
 *
 * @return string    $string where any angle brackets surrounding an URL have been removed.
 * 
 * From: https://wordpress.stackexchange.com/questions/246377/missing-url-in-password-reset-email
 * Addresses: https://github.com/biocuration/isb-website/issues/52
 */
function remove_angle_brackets_around_url($string)
{
	return preg_replace('/<(' . preg_quote(network_site_url(), '/') . '[^>]*)>/', '\1', $string);
}

// Apply the remove_angle_brackets_around_url() function on the "retrieve password" message:
add_filter('retrieve_password_message', 'remove_angle_brackets_around_url', 99, 1);

add_filter('woocommerce_widget_shopping_cart_buttons', 'change_mini_cart_browse_shop_link');

function change_mini_cart_browse_shop_link($buttons)
{
	print_r($buttons);
	$new_link = home_url() . '/#packages';
	$buttons = str_replace('href="' . esc_url(wc_get_page_permalink('shop')) . '"', 'href="' . esc_url($new_link) . '"', $buttons);
	return $buttons;
}
// register new user (parents) in moodle system

function my_custom_registration_action($user_id)
{
	$user = ($user_id != 0) ? get_userdata($user_id) : false;
	$username = $user->user_login;
	$username = str_replace(' ', '', $username);
	$username = strtolower($username);

	$pwd = $_POST['password'];
	add_user_meta($user_id, 'moowoodle_moodle_user_pwd', $pwd);

	$user_data = array();
	$user_data['email'] = $user->user_email;
	$user_data['username'] = $username;
	$user_data['password'] = $pwd;
	$user_data['auth'] = 'manual';
	$user_data['firstname'] = $_POST['firstname'];
	$user_data['lastname'] = $_POST['lastname'];
	$user_data['preferences'][0]['type'] = "auth_forcepasswordchange";
	$user_data['preferences'][0]['value'] = 0;
	$moodle_user = moowoodle_moodle_core_function_callback('create_users', array('users' => array($user_data)));
}
add_action('user_register', 'my_custom_registration_action');
// Modify the forgot password URL
add_filter('lostpassword_url', 'custom_lostpassword_url', 10, 2);

function custom_lostpassword_url($lostpassword_url, $redirect)
{
	// Replace 'custom-forgot-password-page' with the slug of your custom page template
	$custom_lostpassword_url = home_url('/reset-password-page/');

	return $custom_lostpassword_url;
}
add_action('wp_ajax_reset_password', 'custom_reset_password');
add_action('wp_ajax_nopriv_reset_password', 'custom_reset_password');

function custom_reset_password()
{
	// Retrieve the form data
	$user_login = sanitize_user($_POST['user_login']);
	$reset_key  = sanitize_text_field($_POST['reset_key']);
	$new_password = sanitize_text_field($_POST['new_password']);
	$confirm_password = sanitize_text_field($_POST['confirm_password']);

	// Verify if the passwords match
	if ($new_password !== $confirm_password) {
		$response = array(
			'success' => false,
			'data'    => array(
				'message' => 'كلمتي المرور غير متطابقتين',
			),
		);
		wp_send_json($response);
	}

	// Verify the reset key and retrieve the user
	$user = check_password_reset_key($reset_key, $user_login);
	if (empty($user) || is_wp_error($user)) {
		$response = array(
			'success' => false,
			'data'    => array(
				'message' => 'رابط اعادة تعيين كلمة المرور غير صالح.',
			),
		);
		wp_send_json($response);
	}
	$get_user = get_user_by('login', $user_login);
	$moowoodle_moodle_user_id = search_for_moodle_user('email', trim($get_user->user_email));
	if ($moowoodle_moodle_user_id > 0) {
		add_user_meta($get_user->ID, 'moowoodle_moodle_user_pwd', $new_password);
		$user_data = array();
		$user_data['id'] = $moowoodle_moodle_user_id;
		$user_data['password'] = $new_password;
		$moodle_user = moowoodle_moodle_core_function_callback('update_users', array('users' => array($user_data)));
	}
	// Reset the user's password
	reset_password($user, $new_password);

	$response = array(
		'success' => true,
		'data'    => array(
			'message' => 'Password updated successfully!',
		),
	);
	wp_send_json($response);
}
function update_profile_url()
{
	return get_page_url('myaccount');
}
add_filter('edumall_user_profile_url', 'update_profile_url', 40);
function get_page_url($template_name)
{
	$pages = get_posts([
		'post_type' => 'page',
		'post_status' => 'publish',
		'meta_query' => [
			[
				'key' => '_wp_page_template',
				'value' => 'templates/' . $template_name . '.php',
				'compare' => '='
			]
		]
	]);
	if (!empty($pages)) {
		foreach ($pages as $pages__value) {
			return get_permalink($pages__value->ID);
		}
	}
	return get_bloginfo('url');
}
function edumall_user_profile_text()
{
	return 'حسابي';
}
add_filter('edumall_user_profile_text', 'edumall_user_profile_text', 40);
