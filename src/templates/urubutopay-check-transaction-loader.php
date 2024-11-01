<?php
if (!defined('ABSPATH')) {
     exit;
}

function urubutopay_check_transaction_loader()
{
?>
     <div class="upg-check-transaction-modal" style="display: none;">
          <button class="upg-check-transaction-modal-close-btn" style="display: none;" onclick="handleCloseTrxModal();">
               <img alt="close" src="<?php echo esc_url(plugins_url('../../public/images/payment/close.png', __FILE__)); ?>" />
          </button>
          <div class="upg-check-transaction-loader-image">
               <img src="<?php echo esc_url(plugins_url('../../public/images/spinner/blue.svg', __FILE__)); ?>" alt="" class="upg-check-transaction-pending-img" />
          </div>
          <div class="upg-check-transaction-content upg-check-transaction-content--primary"></div>
     </div>
<?php
}
