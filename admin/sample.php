<!DOCTYPE html>
<html lang="en">
<!-- begin header -->

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>charge</title>

    <script src="https://js.stripe.com/v3/"></script>
</head>
<!-- end header -->

<body>
    <form action="charge.php" method="post" id="payment-form">
        <div class="form-row">
            <label for="card-element">
                Credit or debit card
            </label>
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display Element errors. -->
            <div id="card-errors" role="alert"></div>
        </div>

        <button>Submit Payment</button>
    </form>


</body>
<!-- begin footer -->
<script type="text/javascript" src="scripts/client.js"></script>
<!-- end footer -->

</html>