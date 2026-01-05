@extends('payment-views.layouts.master')

@section('content')
    <center>
        <h1>Please do not refresh this page...</h1>
        <div id="error-message" style="display: none; color: red;"></div>
    </center>

    @if(empty(config('razor_config.api_key')))
        <div style="color: red; text-align: center;">
            <h2>Payment Gateway Configuration Error</h2>
            <p>Please contact support.</p>
        </div>
    @else
        <form action="{!!route('razor-pay.payment',['payment_id'=>$data->id])!!}" id="form" method="POST">
            @csrf
            <script src="https://checkout.razorpay.com/v1/checkout.js"
                    data-key="{{ config('razor_config.api_key') }}"
                    data-amount="{{round($data->payment_amount, 2)*100}}"
                    data-buttontext="Pay {{ round($data->payment_amount, 2) . ' ' . $data->currency_code }}"
                    data-name="{{ $business_name }}"
                    data-description="Payment for order"
                    data-image="{{ $business_logo }}"
                    data-prefill.name="{{$payer->name ?? ''}}"
                    data-prefill.email="{{$payer->email ?? ''}}"
                    data-theme.color="#ff7529">
            </script>
            <button class="btn btn-block" id="pay-button" type="submit" style="display:none"></button>
        </form>
    @endif

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            @if(!empty(config('razor_config.api_key')))
                document.getElementById("pay-button").click();
            @else
                document.getElementById("error-message").innerHTML = 
                    'Payment gateway not configured properly. Please try again later.';
                document.getElementById("error-message").style.display = 'block';
            @endif
        });
    </script>
@endsection