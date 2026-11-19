$(function() {
   $('#checkout-form').on('submit', function(e) {
       e.preventDefault();

       $('#error-message').hide();
       $('#submit-button').prop('disabled', true).text('Processing...');
       
       $.ajax({
        url: 'api/create_payment.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',

        success: function(response) {
            if(response.success) {
                $('#checkout-form').hide();  
                $('#payment-container').show();
                $('#payment-iframe').attr('src', response.payment_url);
            }
            else {
                $('#error-message').text(response.message).show();
            }
        },
        error: function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Payment failed. Please try again.';
            $('#error-message').text(msg).show();
        },
        complete: function() {
            $('#submit-button').prop('disabled', false).text('Submit');
        }
       })
   }); 
});