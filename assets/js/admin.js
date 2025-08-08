jQuery(document).ready(function ($) {

    /* ============================================================
     * Code for approving and rejecting cancel request
     * ------------------------------------------------------------
     * - Listens for cancel button clicks
     * - Sends AJAX request to server
     * - Handles success/error responses
     * - Scrolls to order data section after action
     * ============================================================
     */

    $(document).on("click", ".cancel-admin-action", function (e) {
        e.preventDefault();

        const $btn = $(this);
        const orderId = $btn.data("order_id");
        const actionType = $btn.data("action");

        const confirmation = confirm("Are you sure you want to proceed with this action?");
        if (!confirmation) {
            return; // Exit early if the user cancels
        }

        console.log(actionType);
        $btn.prop("disabled", true);

        $.ajax({
            url: cancel_ajax_object.ajax_url,
            method: "POST",
            data: {
                action: actionType,
                order_id: orderId,
                security: cancel_ajax_object.nonce
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data?.message || "Failed to process request.");
                    location.reload();

                    scrollToOrderData();

                    $btn.prop("disabled", false);

                } else {
                    alert(response.data?.message || "Failed to process request.");
                    location.reload();

                    scrollToOrderData();


                    $btn.prop("disabled", false);

                }
            },
            error: function (xhr) {
                alert("AJAX error occurred.");
                console.error(xhr.responseText);
            }
        });
    });


    function scrollToOrderData() {
    if (document.readyState === "complete") {
        let orderDataElement = $("#order_data");
        if (orderDataElement.length) {
            orderDataElement[0].scrollIntoView({ behavior: "smooth", block: "start" });
            orderDataElement.focus();
        }
    } else {
        $(window).on("load", function () {
            let orderDataElement = $("#order_data");
            if (orderDataElement.length) {
                orderDataElement[0].scrollIntoView({ behavior: "smooth", block: "start" });
                orderDataElement.focus();
            }
        });
    }

    /* ============================================================
     * Code for approving and rejecting cancel request
     * ------------------------------------------------------------
     * - Listens for cancel button clicks
     * - Sends AJAX request to server
     * - Handles success/error responses
     * - Scrolls to order data section after action
     * ============================================================
     */

$(document).on("click", ".initiate_cancel_order", function (e) {
    e.preventDefault();
    console.log("Button clicked! Event firing successfully.");
});


}
});

