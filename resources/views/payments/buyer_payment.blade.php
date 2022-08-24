<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? '' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">

    <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>

    <style>
        body {
            background: #FFF7C6;
        }

        .box {
            background: #FFF;
            padding: 18px;
            border: 3px solid #000;
        }
    </style>
  </head>
  <body>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="box">
                    <h1 class="fs-4 fw-bold text-center mb-3">Payment</h1>

                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option>Monthly</option>
                            <option selected>Yearly</option>
                        </select>
                    </div>

                    <div class="form-group mt-3 mb-3">

                        <input type="text" name="promo" class="form-control" placeholder="Do you have a promo code?">
                    </div>



                    <form id="payment-form">
                        <div id="card-container"></div>

                        <div class="d-grid gap-2 mx-auto col-6 mb-4">
                            <button class="btn btn-warning btn-lg" id="card-button" type="button">Pay Now</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>




    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>




 <!-- Configure the Web Payments SDK and Card payment method -->
  <script type="text/javascript">
    async function main() {
      const payments = Square.payments('sandbox-sq0idb-I4XJSQCcN5QRHk7zmVASbA', 'LDW1SQDZFA472');
      const card = await payments.card();
      await card.attach('#card-container');

      async function eventHandler(event) {
        event.preventDefault();

        try {
          const result = await card.tokenize();
          if (result.status === 'OK') {
            //console.log(`Payment token is ${result.token}`);
            startPaying(result.token)
          }
        } catch (e) {
          console.error(e);
        }
      };

      const cardButton = document.getElementById('card-button');
      cardButton.addEventListener('click', eventHandler);
    }

    main();
  </script>

  <script type="text/javascript">

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function startPaying(token) {
        alert(token)

        $.ajax({
           type:'POST',
           url:"{{ route('pay.buyer.post') }}",
           data:{token:token, bid: <?php echo $bid; ?>},
           success:function(data){

              if(data[0].payment.id) {
                window.location.href='https://www.goodyellowco.com/u/payment/success'

              } else {
                window.location.href='https://www.goodyellowco.com/u/payment/error'

              }
           }
        });
    }




</script>





  </body>
</html>
