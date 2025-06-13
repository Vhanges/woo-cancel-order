jQuery(document).ready(function ($) {

    /* ============================================================
     * Code for Initiating the form request for cancellation
     * ------------------------------------------------------------
     * - Listens for initiate cancel button clicks
     * - Sends AJAX request to server
     * - Handles success/error responses
     * - Scrolls to order data section after action
     * ============================================================
     */

    $(document).on("click", ".initiate_cancel_order", function (e) {
        e.preventDefault();
    });
});

