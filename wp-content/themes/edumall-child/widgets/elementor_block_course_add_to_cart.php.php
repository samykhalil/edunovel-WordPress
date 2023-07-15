<?php
class TutorCourse extends \Elementor\Widget_Base
{
   public function get_name()
   {
      return 'tutor-course';
   }

   public function get_title()
   {
      return __('Edunovel Tutor Course', 'elementor');
   }

   public function get_icon()
   {
      return 'edunovel-icon tutor-course';
   }

   public function get_categories()
   {
      return ['edunovel-widgets'];
   }

   public function __construct($data = [], $args = null)
   {
      parent::__construct($data, $args);

      //wp_register_script( 'script-handle', plugins_url( '../js/tutor-course.js', __FILE__ ), array( 'jquery', 'elementor-frontend' ), rand(), true );
   }

   public function get_script_depends()
   {
      return ['script-handle'];
   }

   protected function register_controls_course()
   {

      $options = array();

      $posts = get_posts(array(
         'post_type'  => 'courses',
         'numberposts' => -1
      ));


      foreach ($posts as $key => $post) {
         $options[$post->ID] = get_the_title($post->ID);
      }


      $this->add_control(
         'course_id',
         [
            'label' => esc_html__('Course Select', 'plugin-name'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $options,
            'multiple' => false
         ]
      );
   }


   protected function _register_controls()
   {

      // start content section

      $this->start_controls_section(
         'section_content',
         [
            'label' => __('Content', 'edunovel'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
         ]
      );

      // import our controls
      $this->register_controls_course();

      // end content section
      $this->end_controls_section();
   }


   protected function render()
   {

      $settings = $this->get_settings_for_display();
      $course_id = $settings['course_id'];
      if (!empty($course_id)) :
         $course = get_post($course_id);
         $title = $course->post_title;
         // $term_list = wp_get_post_terms($course_id, 'course-category', array('fields' => 'all'));
         // $category_name = $term_list[0]->name;
         // $category_slug = $term_list[0]->slug;
         $course_permalink = get_permalink($course_id);
         // $category_permalink = get_term_link($category_slug, 'course-category');
         $course_image = get_the_post_thumbnail($course_id, 'full');
         $product_id = (int) get_post_meta($course_id, '_tutor_course_product_id', true);

         $price_html = '<div class="tutor-price course-free"><p class="price">$0</p></div>';

         $product    = wc_get_product($product_id);

         if ($product) {
            $price_html = '<div class="tutor-price"><p class="price">' . $product->get_price_html() . '</p></div>';
         }
         // $course = Edumall_Tutor::instance()->get_course_by_wc_product($item_data['product_id']);
         // echo  '<img src="' . get_the_post_thumbnail_url($course->ID) . '" height="80" width="80" />';


         // $sale_percentage = sale_badge_percentage($product_id);


?>

         <div class="tutor-course grid-item courses type-courses status-publish has-post-thumbnail hentry course-paid course-purchasable animate">
            <div class="course-loop-wrapper edumall-box edumall-tooltip">
               <div class="tutor-course-header">
                  <div class="course-thumbnail edumall-image">
                     <a href="<?= $course_permalink ?>"> <?= $course_image ?> </a>
                  </div>
                  <div class="course-loop-badges">
                     <?php // if (!empty($sale_percentage)) : 
                     ?>
                     <div class="tutor-course-badge onsale"> <?php //echo $sale_percentage 
                                                               ?> </div>
                     <?php //endif; 
                     ?>
                  </div>
               </div>
               <div class="course-loop-info">
                  <div class="course-loop-badge-level beginner">
                     <span class="badge-text"> </span>
                  </div>
                  <div class="course-loop-category">
                     <a href="<?php //echo $category_permalink 
                              ?>"> <?php //echo $category_name 
                                    ?> </a>
                  </div>
                  <h2 class="course-loop-title course-loop-title-collapse-2-rows"><a href="<?= $course_permalink ?>"> <?= $title ?> </a></h2>
                  <div class="course-loop-excerpt course-loop-excerpt-collapse-2-rows">
                  </div>
                  <div class="course-loop-price">
                     <?= $price_html ?>
                  </div>

                  <div class="course-loop-enrolled-button cart-notification">
                     <a href="/courses/?add-to-cart=<?= $product_id ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="<?= $product_id ?>" data-product_sku="" aria-label="Read more" rel="nofollow"><span class="btn-icon tutor-icon-cart-line-filled"></span><span class="cart-text"> اشترك الآن !</span></a>
                  </div>
               </div>

            </div>

         </div>


      <?php

      endif;
   }

   protected function _content_template()
   { ?>

      <p> if content does not appear, please update and refresh page to see course details</p>

<?php
   }
}

?>