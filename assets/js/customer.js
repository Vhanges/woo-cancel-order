jQuery(document).ready(function ($) {

    window.order_id = 0;

    // Inject the modal HTML into the page
    if (!$('#cancel-form-modal').length) {
        $('body').append(`
            <div class="order-cancel-modal" id="cancel-form-modal" style="display:none;">
                <div class="order-cancel-modal-content">

                    <h1 class="initial-cancel">Order Cancellation</h1>

                    <h1 class="cancel-submitted" style="display:none;">Order Cancellation Submitted!</h1>

                    <form id="order-cancel-form" data-order-id="">
                        
                        <span class="initial-cancel form-cancel-group"> 
                            <label for="cancel-reason">
                                <h2>We’d love to know your reason for canceling—your feedback helps us improve!</h2>
                            </label>
                            <textarea id="cancel-reason" name="cancel_reason" max-length="200" placeholder="Let us know your reason for canceling (up to 200 characters)" required></textarea>
                        </span>

                        <span class="cancel-submitted form-cancel-group" style="display:none;> 

                            <label for="cancel-reason">
                                <h4>We appreciate your feedback. We’ll now review your request and verify whether cancellation is possible. You’ll receive an update shortly.</h4>
                            </label>

                        </span>
    
                        <span class="modal-buttons">
                            <button class="initial-cancel submit-cancel-request" type="submit">Submit</button>
                            <button class="initial-cancel abort-cancel-request order-cancel-modal-close" type="button" submit-cancel-request >Keep My Order</button>
                            
                            <button class="cancel-failed abort-cancel-request order-cancel-modal-close" type="button" style="display:none;">Return to my Orders</button>
                            
                            <button class="cancel-submitted abort-cancel-request order-cancel-modal-close" type="button" style="display:none;">Return to my Orders</button>
                        </span>
                    </form>
                </div>
            </div>
        `);
    }

    // Open modal on button click and set order ID
    $(document).on("click", ".initiate_cancel_order", function (e) {
        e.preventDefault();
        let order_id = $(this).attr("href");
        order_id = order_id.replace(/#/g, "");
        $("#order-cancel-form").attr("data-order_id", order_id);
        $("#cancel-form-modal").show();
    });

    // Close modal on close button click
    $(document).on("click", ".order-cancel-modal-close", function (e) {
        e.preventDefault();
        $(this).prop("disabled", false);
        $("#cancel-form-modal").hide();
    });

    // Close modal when clicking outside modal content
    $(window).on("click", function (e) {
        if ($(e.target).is("#cancel-form-modal")) {
            $("#cancel-form-modal").hide();
        }
    });

        // Handle form submission
    $(document).on("submit", "#order-cancel-form", function (e) {
        e.preventDefault();

        $('.submit-cancel-request').prop("disabled", false);

        
        const order_id = $(this).data("order_id");
        const reason = $("#cancel-reason").val().trim();

        if (!reason) {
            alert("Please provide a reason for cancellation.");
            return;
        }

        
        $.ajax({
            url: cancel_ajax_object.ajax_url,
            method: "POST",
            data: {
                action: "nopriv_woo_cancel_request",
                order_id: order_id,
                reason: reason,
                security: cancel_ajax_object.nonce
            },
            beforeSend: function () {
                $(".initial-cancel").hide();
            },
            success: function (response) {
                if (response.success) {
                $(".cancel-submitted").show();
                location.reload();
                alert("Cancel Request Submitted");
            } else {
                alert(
                (response.data && response.data.message ? response.data.message : "Cancellation failed.")
                );
                location.reload();
            }
            },
            error: function (xhr, status, error) {
            alert("An error occurred: " + error + ". Please try again.");
            $("#cancel-form-modal").hide();
            }
        });
    });



});