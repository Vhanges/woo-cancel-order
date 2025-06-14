jQuery(document).ready(function ($) {
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
                            <button class="initial-cancel abort-cancel-request order-cancel-modal-close" submit-cancel-request type="submit">Keep My Order</button>
                            <button class="cancel-submitted abort-cancel-request order-cancel-modal-close" type="submit" style="display:none;">Return to my Orders</button>
                        </span>
                    </form>
                </div>
            </div>
        `);
    }

    // Open modal on button click and set order ID
    $(document).on("click", ".initiate_cancel_order", function (e) {
        e.preventDefault();
        var orderId = $(this).data("order-id");
        $("#order-cancel-form").attr("data-order-id", orderId);
        $("#cancel-form-modal").show();
    });

    // Close modal on close button click
    $(document).on("click", ".order-cancel-modal-close", function () {
        $("#cancel-form-modal").hide();
    });

    // Close modal when clicking outside modal content
    $(window).on("click", function (e) {
        if ($(e.target).is("#cancel-form-modal")) {
            $("#cancel-form-modal").hide();
        }
    });

    $(document).on("click", ".submit-cancel-request", function(){
        $(".initial-cancel").hide(); 
        $(".cancel-submitted").show(); 
    });


});