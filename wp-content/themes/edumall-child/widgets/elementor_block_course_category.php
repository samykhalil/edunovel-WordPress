<?php

class TutorCourseCategories extends \Elementor\Widget_Base
{
   public function get_name()
   {
      return 'tutor-categories';
   }

   public function get_title()
   {
      return __('Tutor Course Categories', 'elementor');
   }

   public function get_icon()
   {
      return 'edunovel-icon tutor-categories';
   }

   public function get_categories()
   {
      return ['edunovel-widgets'];
   }

   public function __construct($data = [], $args = null)
   {
      parent::__construct($data, $args);

      //wp_register_script( 'script-handle', plugins_url( '../js/tutor-categories.js', __FILE__ ), array( 'jquery', 'elementor-frontend' ), rand(), true );
   }

   public function get_script_depends()
   {
      return ['script-handle'];
   }

   protected function register_controls_categories()
   {

      // get tutoe course categories
      $tutor_categories = get_terms([
         'taxonomy' => 'course-category',
         'hide_empty' => false,
      ]);

      foreach ($tutor_categories as $category) :
         $data_options[$category->term_id] = esc_html__($category->name, 'edunovel');
      endforeach;


      $posts = get_posts(array(
         'post_type'  => 'page',
         'numberposts' => -1
      ));

      foreach ($posts as $key => $post) {
         $pages_options[$post->ID] = get_the_title($post->ID);
      }

      $repeater = new \Elementor\Repeater();

      $repeater->add_control(
         'course_image',
         [
            'label' => esc_html__('Choose Image', 'edunovel'),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
               'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
         ]
      );

      $repeater->add_control(
         'list_title',
         [
            'label' => esc_html__('Category Title', 'edunovel'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__('List Title', 'edunovel'),
            'label_block' => true,
         ]
      );

      //        $repeater->add_control(
      //            'course_categories',
      //            [
      //                'label' => esc_html__( 'Select Category', 'edunovel' ),
      //                'type' => \Elementor\Controls_Manager::SELECT2,
      //                'multiple' => false,
      //                'options' => $data_options,
      //                'default' => [],
      //            ]
      //        );

      $repeater->add_control(
         'category_url',
         [
            'label' => esc_html__('Select Page', 'edunovel'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'multiple' => false,
            'options' => $pages_options,
            'default' => [],
         ]
      );

      $this->add_control(
         'list',
         [
            'label' => esc_html__('Categories List', 'edunovel'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
               [
                  'list_title' => esc_html__('Title #1', 'edunovel'),
                  'course_categories' => '',
               ],
            ],
            'title_field' => '{{{ list_title }}}',
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
      $this->register_controls_categories();

      // end content section
      $this->end_controls_section();
   }


   protected function render()
   {

      $settings = $this->get_settings_for_display();
      if ($settings['list']) {

         echo '<section class="cards">';

         foreach ($settings['list'] as $item) {


            $term_data = get_term_by('id', $item['course_categories'], 'course-category');
            $term_name = $term_data->name;
            $image_id           = get_term_meta($term_data->term_id, 'thumbnail_id', true);
            $post_thumbnail_img = wp_get_attachment_image_src($image_id, 'large');
            $item_image_url = $item['course_image']['url'];
            if (!empty($item_image_url)) :
               $image_url = $item_image_url;
            else :
               $image_url = $post_thumbnail_img[0];
            endif;

            if (!empty($item['list_title'])) :
               $title = $item['list_title'];
            else :
               $title = $term_name;
            endif;

            $term_parent = $term_data->parent;
            $term_data_parent = get_term_by('id', $term_parent, 'course-category')->name;

            $category_url_id = $item['category_url'];
            $category_url = get_permalink($category_url_id);

?>

            <article class="card card--1">
               <div class="card__img" style="background-image: url('<?= $image_url ?>');"></div>
               <a href="<?= $category_url ?>" class="card_link">
                  <div class="card__img--hover" style="background-image: url('<?= $image_url ?>');"></div>
               </a>
               <div class="card__info">
                  <span class="card__category"> <?= $term_data_parent ?> </span>
                  <h3 class="card__title"> <?= $title ?> </h3>
               </div>
            </article>


      <?php

         }

         echo '</section>';
      }
   }

   protected function _content_template()
   {

      ?>

      <section class="cards">
         <# if ( settings.list.length ) { #>

            <# _.each( settings.list, function( item ) { #>

               <# if ( item.course_image.url ) { var image={ id: item.course_image.id, url: item.course_image.url, }; var image_url=elementor.imagesManager.getImageUrl( image ); if ( ! image_url ) { return; } } #>
                  <article class="card card--1">
                     <div class="card__img" style="background-image: url({{ image_url }});"></div>
                     <a href="" class="card_link">
                        <div class="card__img--hover" style="background-image: url({{ image_url }});"></div>
                     </a>
                     <div class="card__info">
                        <span class="card__category"> </span>
                        <h3 class="card__title"> {{{ item.list_title }}} </h3>
                     </div>
                  </article>

                  <# }); #>

                     <# } #>
      </section>



<?php
   }
}

?>