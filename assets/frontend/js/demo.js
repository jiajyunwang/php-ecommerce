$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /*====================================
    Account Popup
    ======================================*/
    $('#accountForm').on('submit', function(event) {
        event.preventDefault(); 
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#overlay').show();
                    setTimeout(function() {
                        $('#overlay').hide(); 
                        window.location.href = '/account';
                    }, 3000);
                } else if (response.error) {
                    window.location.href = '/account';
                } else {
                    alert('提交失敗');
                    $('#overlay').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('提交失敗');
                $('#overlay').hide();
            }
        });
    });

    /*====================================
    Add Cart
    ======================================*/
    $('#cart').on('click', function(event) {
        $('#understock').hide();
        $('#sold-out').hide();
        $('#upper-limit').hide(); 
        event.preventDefault(); 
        $.ajax({
            url: $('#myForm').attr('action'),
            method: $('#myForm').attr('method'),
            data: $('#myForm').serialize(),
            success: function(response) {
                if (response.success) {
                    $('.count').text(response.count);
                    $('#hidden').show();
                    setTimeout(function() {
                        $('#hidden').hide(); 
                    }, 3000);
                } else if (response.notEnough) {
                    $('#understock').show(); 
                } else if (response.finish) {
                    $('#sold-out').show();
                } else if (response.notLongin){
                    window.location.href = '/user/login';
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("Raw response:", jqXHR.responseText);
                console.log("textStatus:", textStatus);
                alert('提交失敗，請重試。');
            }
        });
    });

    /*====================================
    Check All
    ======================================*/
    $(".checkAll").click(function(){
        if($(this).prop("checked")){
            $("input[type='checkbox']").prop("checked",true);
            $('.btn-prohibit').hide();
            $('.btn-dark').show(); 
        }else{
            $("input[type='checkbox']").prop("checked",false);
            $('.btn-prohibit').show();
            $('.btn-dark').hide(); 
        }
    })
    $(".checkAll2").click(function(){
        if($(this).prop("checked")){
            $("input[type='checkbox']").prop("checked",true);
            $('.btn-prohibit').hide();
            $('.btn-dark').show(); 
        }else{
            $("input[type='checkbox']").prop("checked",false);
            $('.btn-prohibit').show();
            $('.btn-dark').hide(); 
        }
    })
    $("input[type='checkbox']").click(function(){
        var checkLength = $(this).closest("tbody").find("input[type='checkbox']:checked").length;
        var inputLenhth = $(this).closest("tbody").find("input[type='checkbox']").length;

        if(!$(this).prop("checked")){
            $(".checkAll").prop("checked",false);
            $(".checkAll2").prop("checked",false);
            if(checkLength==0){
                $('.btn-prohibit').show();
                $('.btn-dark').hide(); 
            }
        }
        else{
            if(checkLength==inputLenhth){
                $(".checkAll").prop("checked",true);
                $(".checkAll2").prop("checked",true);
            }
            $('.btn-prohibit').hide();
            $('.btn-dark').show(); 
        }
    })

    /*====================================
    Check All
    ======================================*/
    $("#to-delete").click(function(){
        document.form.action='/user/destroy-carts'; 
        document.form.submit();
    });
    $("#to-checkout").click(function(){
        document.form.action='/user/order/create'; 
        document.form.submit();
    });
});