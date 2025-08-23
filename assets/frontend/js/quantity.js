$(function() {
    var stock = document.getElementById('product').dataset.stock;
    var quantity = document.getElementById('product').dataset.quantity;
    $('.product-info .qtyplus').click(function(e) {
        $('#understock').hide();
        $('#sold-out').hide();
        e.preventDefault();
        fieldName = $(this).attr('field');
        var currentVal = parseInt($('input[name=' + fieldName + ']').val());
        if (!isNaN(currentVal) && currentVal < stock) {
            $('input[name=' + fieldName + ']').val(currentVal + 1);
            $('#upper-limit').hide(); 
            if (currentVal >= (stock-quantity)) {
                $('input[name=' + fieldName + ']').val(stock-quantity);
                $('#upper-limit').show(); 
            }
        } else if (!isNaN(currentVal) && currentVal > stock) {
            $('input[name=' + fieldName + ']').val(stock);
            if (currentVal > (stock-quantity)) {
                $('input[name=' + fieldName + ']').val(stock-quantity);
            }
            $('#upper-limit').show(); 
        } 
    });
    $(".product-info .qtyminus").click(function(e) {
        $('#understock').hide();
        $('#sold-out').hide();
        e.preventDefault();
        fieldName = $(this).attr('field');
        var currentVal = parseInt($('input[name=' + fieldName + ']').val());
        if (!isNaN(currentVal) && currentVal > 1) {
            $('input[name=' + fieldName + ']').val(currentVal - 1);
            $('#upper-limit').hide(); 
        } else {
            $('input[name=' + fieldName + ']').val(1);
            $('#upper-limit').hide(); 
        }
    });
    $('.product-info input[name="quantity"]').on('input', function() {
        $('#understock').hide();
        $('#sold-out').hide();
        var currentVal = parseInt($(this).val());
        if (!isNaN(currentVal) && currentVal > stock) {
            $(this).val(stock);
            if (currentVal >= (stock-quantity)) {
                $(this).val(stock-quantity);
            }
            $('#upper-limit').show(); 
        } else if (currentVal == stock) {
            if (currentVal > (stock-quantity)) {
                $(this).val(stock-quantity);
            }
            $('#upper-limit').hide(); 
        } else if (currentVal > 0 && currentVal < stock) {
            $('#upper-limit').hide(); 
            if (currentVal > (stock-quantity)) {
                $(this).val(stock-quantity);
                $('#upper-limit').show(); 
            }
        } else if (currentVal < 1) {
            $('#upper-limit').hide(); 
            $(this).val(1);
        }
    });
    $('.product-info input[name="quantity"]').blur(function() {
        var currentVal = parseInt($(this).val());
        if (isNaN(currentVal)) {
            $('#upper-limit').hide();
            $(this).val(1);
        }
    });
});