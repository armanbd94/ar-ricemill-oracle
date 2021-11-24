
<script src="js/app.js" type="text/javascript"></script>
<script src="js/perfect-scrollbar.min.js"></script>
<script src="js/config.js" type="text/javascript"></script>
<script src="js/scripts.bundle.js" type="text/javascript"></script>
<script src="js/custom.js" type="text/javascript"></script>
<script>
    var _token = "{{ csrf_token() }}";
    var $window = $(window);

    // :: Preloader Active Code
    $window.on('load', function () {
        $('#preloader').fadeOut('slow', function () {
            $(this).remove();
        });
    });
    $(document).ready(function(){
        // stock_alert();
        function stock_alert()
        {
            $.get("{{ url('stock-notification') }}", function(data){
                if(data.materials > 0 || data.products > 0)
                {
                    let total = parseFloat(data.materials) + parseFloat(data.products);
                    $('.total-alert-qty-badge').removeClass('d-none');
                    $('.total-alert-qty-badge').text(total);
                    $('#total-alert-qty').text(total);
                    
                    let alert_html = '';
                    if(data.materials > 0){
                        alert_html +=`<div class="pt-3">
                                            <a href="{{ url('material-stock-alert-report') }}" class="text-danger">
                                                <div class="text-center font-weight-bolder" style="height: 50px;
                                                    width: 100%;align-items: center;display: flex;justify-content: center; margin: 0 auto;color:#f64e60;">
                                                    <img src="{{ asset('images/alert.svg') }}" style="width: 30px;"> ${data.materials} materials are going to be out of stock
                                                </div>
                                            </a>
                                        </div>`;
                    }
                    if(data.products > 0){
                        alert_html +=`<div class="pt-3">
                                            <a href="{{ url('product-stock-alert-report') }}" class="text-danger">
                                                <div class="text-center font-weight-bolder" style="height: 50px;
                                                    width: 100%;align-items: center;display: flex;justify-content: center; margin: 0 auto;color:#f64e60;">
                                                    <img src="{{ asset('images/alert.svg') }}" style="width: 30px;"> ${data.products} products are going to be out of stock
                                                </div>
                                            </a>
                                        </div>`;
                    }
                    
                    $('#material-stock-alert').empty().html(alert_html);
                }else{
                    $('.total-alert-qty-badge').addClass('d-none');
                    $('.total-alert-qty-badge').text('');
                    $('#total-alert-qty').text('');
                    $('#material-stock-alert').empty().html(`<div class="p-8 text-center font-weight-bolder">All caught up!<br>No new notifications.</div>`);
                }
            });
        }
        <?php 
        if (session('status')){
        ?>
        notification("{{session('status')}}","{{session('message')}}");
        <?php
        }
        ?>
        <?php 
        if (session('success')){
        ?>
        notification("success","{{session('success')}}");
        <?php
        }
        ?>
        <?php 
        if (session('error')){
        ?>
        notification("error","{{session('error')}}");
        <?php
        }
        ?>
    });
</script>
@stack('scripts') <!-- Load Scripts Dynamically -->
