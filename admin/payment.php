
<!-- begin header -->
<script src="https://js.stripe.com/v3/"></script>
<!-- end header -->
<div class="row">
    <div class="col-sm-12">
        <div id="card-element"></div>
         <!-- Used to display Element errors. -->
         <div id="card-errors" role="alert"></div>
        <p id="card-errors" style="margin-bottom: 10px; line-height: inherit; color: #eb1c26; font-weight: bold;"></p>
        <input type="button" id=payNowButton value="Pay now">
    </div>
</div>
<!-- begin footer -->
<script type="text/javascript">
//get this from custom fields
var stripe = Stripe('pk_test_51INpZ6LpiOi48zknh0lXElbb6kJGlYOfrhrnf4TkpVAXFmkWynQJzIo38kVyjFP7oi1x6lbe3oioCmSjxVCQaHTV00hXbGEhX0');
var elements = stripe.elements();

//let elements = stripe.elements();

var style = {
    base: {}
};

// Create an instance of the card Element
var card = elements.create('card', { hidePostalCode: true, style: style });

// Add an instance of the card Element into the `card-element` <div>
if ($('#card-element').length) {
    card.mount('#card-element');
}

// Create a token or display an error the form is submitted.
var submitButton = document.getElementById('payNowButton');
if (submitButton) {
    submitButton.addEventListener('click',
        function(event) {
            event.preventDefault();
            $("#payNowButton").attr("disabled", "disabled");
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Inform the user if there was an error
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;

                   // $("#payNowButton").removeAttr("disabled");
                } else {
                    console.log(result.token.id)
                    // Send the result.token.id to a php file and use the token to create the subscription
                   // SubscriptionManager.PayNowSubmit(result.token.id, e);
                }
            });

        });
}
</script>
<!-- end footer -->
