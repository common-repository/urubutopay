const apiBaseUrl = document
  .querySelector('link[rel="https://api.w.org/"]')
  .getAttribute("href");

jQuery(document).ready(function () {
  const paymentModal = jQuery(".upg-single-product-buy-button");
  paymentModal.on("click", (e) => {
    const productId = paymentModal.attr("data-attr");
    togglePaymentModal(String(productId));
  });
});

function togglePaymentModal(id) {
  const modal = jQuery(".upg-modal");

  modal.each(function () {
    const pId = jQuery(this).attr("data-product-id");

    if (Number(pId) === Number(id)) {
      jQuery(this).attr("style", "display: block");
      const nameInput = jQuery(".upg-modal input[name='payer_names']");
      const phoneInput = jQuery(".upg-modal input[name='phone_number']");
      nameInput.val("");
      phoneInput.val("");

      const activePaymentModeClass = "upg-active-payment-mode-btn";
      const paymentModeBtn = jQuery(".upg-form-payment-mode-btn");
      paymentModeBtn.removeClass(activePaymentModeClass);
    } else {
      jQuery(this).attr("style", "display: none");
    }
  });
}

function closePaymentModal() {
  const modal = jQuery(".upg-modal");
  modal.attr("style", "display: none");
}

async function initPayment(productId) {
  const endpoint = `${apiBaseUrl}urubutopay/create`;

  const activePaymentModeClass = "upg-active-payment-mode-btn";

  const nameInput = jQuery(".upg-modal input[name='payer_names']");

  const phoneInput = jQuery(".upg-modal input[name='phone_number']");

  const channelName = jQuery(`.upg-modal .${activePaymentModeClass}`);

  const payNowBtn = jQuery(".upg-modal .upg-form-send-btn");

  const payNowTxt = jQuery(".upg-modal .upg-form-send-btn span");

  const loader = jQuery(".upg-modal .upg-form-send-btn img");

  // remove validation error & other error
  removeError();
  // validation
  const validation = validateInput();
  if (validation) {
    return undefined;
  }

  //hide
  payNowTxt.attr("style", "display: none;");
  loader.attr("style", "display: block;");
  payNowBtn.attr("disabled", true);
  payNowBtn.attr("style", "cursor: not-allowed;opacity: 0.7;");

  const payload = {
    payer_names: nameInput.val(),
    phone_number: phoneInput.val(),
    channel_name: channelName.attr("name"),
    product_id: productId,
    rdurl:
      channelName.attr("name") === PaymentChannel.CARD
        ? window.location.href
        : "",
  };

  jQuery
    .ajax(endpoint, {
      contentType: "application/json",
      data: JSON.stringify(payload),
      method: "POST",
    })
    .done((response, status) => {
      payNowTxt.attr("style", "display: block;");
      loader.attr("style", "display: none;");
      payNowBtn.attr("style", "cursor: pointer; opacity: 1;");
      payNowBtn.attr("disabled", false);

      if (
        response &&
        status === "success" &&
        response.data &&
        response.data.transaction_id
      ) {
        if (
          payload.channel_name === PaymentChannel.CARD &&
          response.data.card_processing_url
        ) {
          window.open(response.data.card_processing_url, "__blank");
        }
        checkTransaction(
          response.data.transaction_id,
          response.data.post_id,
          payload.channel_name
        );
      }
    })
    .fail((error) => {
      payNowTxt.attr("style", "display: block;");
      loader.attr("style", "display: none;");
      payNowBtn.attr("style", "cursor: pointer; opacity: 1;");
      payNowBtn.attr("disabled", false);

      if (error?.responseJSON && error.responseJSON.message) {
        displayErrorAlert(
          jQuery(".upg-modal-body"),
          error.responseJSON.message
        );
      } else {
        displayErrorAlert(
          jQuery(".upg-modal-body"),
          error.statusText || "Internal server error"
        );
      }
    });
}

const paymentModeBtn = jQuery(".upg-form-payment-mode-btn").on(
  "click",
  handleActivePaymentModeButton
);

function handleActivePaymentModeButton(e) {
  e.preventDefault();
  paymentModeBtn.removeClass("upg-active-payment-mode-btn");
  jQuery(this).addClass("upg-active-payment-mode-btn");
  jQuery(this).attr("");
}

// check transaction after transaction initiated
function checkTransaction(transactionId, postId, channelName) {
  const endpoint = `${apiBaseUrl}urubutopay/transaction/check`;

  const element = jQuery(".upg-check-transaction-modal");
  element.css("display", "flex");

  const closeTrxModal = jQuery(".upg-check-transaction-modal-close-btn");

  const shortCode =
    channelName === PaymentChannel.MOMO
      ? "*182*7*1#"
      : PaymentChannel.AIRTEL_MONEY === channelName
      ? "*500*8#"
      : null;

  const pendingContent = `Thank you for initiating payment, check your pending transaction
  ${shortCode ? `on ${shortCode}` : ""} and follow the instruction`;

  const failedContent = "Payment failed";
  const successContent = "Payment succeed";

  const failedClassName = "upg-check-transaction-content--failed";
  const primaryClassName = "upg-check-transaction-content--primary";
  const successClassName = "upg-check-transaction-content--success";

  const imageLoadClass = jQuery(".upg-check-transaction-loader-image img");

  const content = jQuery(".upg-check-transaction-content");
  content.text(pendingContent);
  imageLoadClass.attr("src", Assets["loading-icon-blue"]);

  const payload = { transaction_id: transactionId, post_id: postId };

  jQuery
    .ajax(endpoint, {
      contentType: "application/json",
      data: JSON.stringify(payload),
      method: "POST",
    })
    .done((response) => {
      if (response?.data) {
        switch (response.data.transaction_status) {
          case TransactionStatus.PENDING:
          case TransactionStatus.INITIATED:
            content.removeClass(successClassName);
            content.removeClass(failedClassName);
            content.addClass(primaryClassName);
            imageLoadClass.attr("src", Assets["loading-icon-blue"]);
            content.text(pendingContent);
            closeTrxModal.css("display", "none");

            setTimeout(() => {
              checkTransaction(transactionId, postId);
            }, 5000);
            break;

          case TransactionStatus.VALID:
          case TransactionStatus.PENDING_SETTLEMENT:
            content.removeClass(primaryClassName);
            content.removeClass(failedClassName);
            content.addClass(successClassName);
            imageLoadClass.attr("src", Assets["success-icon"]);
            content.text(successContent);
            closeTrxModal.css("display", "flex");
            break;

          case TransactionStatus.FAILED:
            content.removeClass(primaryClassName);
            content.removeClass(successClassName);
            content.addClass(failedClassName);
            imageLoadClass.attr("src", Assets["failed-icon"]);
            content.text(failedContent);
            closeTrxModal.css("display", "flex");
            break;
        }
      }
    })
    .fail((error) => {
      if (error?.responseJSON) {
        if (
          typeof error.responseJSON.retry === "boolean" &&
          error.responseJSON.retry === true
        ) {
          content.removeClass(successClassName);
          content.removeClass(failedClassName);
          content.addClass(primaryClassName);
          imageLoadClass.attr("src", Assets["loading-icon-blue"]);
          content.text(pendingContent);

          setTimeout(() => {
            checkTransaction(transactionId, postId);
          }, 5000);
        } else {
          content.text("Something went wrong");
          content.removeClass(primaryClassName);
          content.removeClass(successClassName);
          content.addClass(failedClassName);
          imageLoadClass.attr("src", Assets["failed-icon"]);
          closeTrxModal.css("display", "flex");
        }
      }
    });
}

function handleCloseTrxModal() {
  const checkTransactionModal = jQuery(".upg-check-transaction-modal");
  checkTransactionModal.css("display", "none");

  closePaymentModal();
}

function validateInput() {
  const formGroup = jQuery(".upg-modal .upg-form-group");

  const nameInput = jQuery(".upg-modal input[name='payer_names']").val();
  const nameInpuGroup = formGroup.filter(function () {
    return jQuery(this).attr("data-attr") === "payer_names";
  })[0];

  const phoneInput = jQuery(".upg-modal input[name='phone_number']").val();
  const phoneInputGroup = formGroup.filter(function () {
    return jQuery(this).attr("data-attr") === "phone_number";
  })[0];

  const activePaymentModeClass = "upg-active-payment-mode-btn";
  const channelName = jQuery(`.upg-modal .${activePaymentModeClass}`).attr(
    "name"
  );

  const channelNameGroup = formGroup.filter(function () {
    return jQuery(this).attr("data-attr") === "channel_name";
  })[0];

  let isValidationError = false;

  if (nameInput.length <= 0) {
    displayValidationError(nameInpuGroup, "Names is required");
    isValidationError = true;
  }

  if (phoneInput.length <= 0) {
    displayValidationError(phoneInputGroup, "Phone number is required");
    isValidationError = true;
  }

  if (!channelName) {
    displayValidationError(channelNameGroup, "Payment mode is required");
    isValidationError = true;
  }

  if (channelName && channelName.length <= 0) {
    displayValidationError(channelNameGroup, "Payment mode is required");
    isValidationError = true;
  }

  return isValidationError;
}

function displayValidationError(selector, message) {
  jQuery(selector).append(
    `<span class="upg-input-error" style="display: block;">${message}</span>`
  );
}

function removeError() {
  jQuery(".upg-input-error").remove();
  jQuery(".upg-modal-alert-error").remove();
}

function displayErrorAlert(selector, message) {
  jQuery(selector).prepend(
    `<div class='upg-alert-danger upg-modal-alert-error'>${message}</div>`
  );
}

// convert price
const priceConverter = jQuery(".upg-price-converter");
if (priceConverter) {
  priceConverter.each(function () {
    const priceValue = jQuery(this).text();
    jQuery(this).text(Number(priceValue).toLocaleString());
  });
}
