<?php
if (!defined('ABSPATH')) {
     exit;
}

function urubutopay_show_all_products()
{
     $args = array('post_type' => URUBUTOPAY_POST_TYPE['PRODUCT']);

     $query = new WP_Query($args);
?>
     <div class="upg-products relative">
          <?php if ($query->have_posts()) : ?>
               <ul class="upg-list-products">
                    <?php
                    while ($query->have_posts()) {
                         $query->the_post();
                    ?>
                         <li class="relative upg-product-item">
                              <a href="<?php the_permalink(); ?>">
                                   <div class="upg-product-image"><?php esc_html(the_post_thumbnail(array(250, 100))) ?></div>
                                   <div class="upg-product-small-detail">
                                        <div class="upg-list-product-title"><?php esc_html(the_title()); ?></div>
                                        <?php if (get_post_meta(get_the_ID(), URUBUTOPAY_META_BOX['PRICE'])) : ?>
                                             <div class="upg-list-product-price">
                                                  <span class="uppercase">RWF</span>
                                                  <span class="upg-price-converter"><?php echo esc_html(get_post_meta(get_the_ID(), URUBUTOPAY_META_BOX['PRICE'], true)); ?></span>
                                             </div>
                                        <?php endif; ?>

                                   </div>
                              </a>
                         </li>
                    <?php
                    }
                    ?>
               </ul>
          <?php endif ?>
     </div>
<?php
}
