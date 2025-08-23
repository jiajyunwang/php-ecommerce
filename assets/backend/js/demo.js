$(function(){
    /*====================================
    Product Operation 
    ======================================*/
    $(document).on('click', '#delete', function() {
        formAction = "/admin/product/destroy-products";
        formSubmit(formAction);
    });
    $(document).on('click', '.btn-edit', function() {
        let productId = $(this).data('productId');
        formAction = `/admin/product/edit/${productId}`;
        formSubmit(formAction);
    });
    $(document).on('click', '.to-inactive', function() {
        let productId = $(this).data('productId');
        formAction = `/admin/product/to-inactive/${productId}`;
        formSubmit(formAction);
    });
    $(document).on('click', '#single-delete', function() {
        let productId = $(this).data('productId');
        formAction = `/admin/product/destroy/${productId}`;
        formSubmit(formAction);
    });
    $(document).on('click', '.to-active', function() {
        let productId = $(this).data('productId');
        formAction = `/admin/product/to-active/${productId}`;
        formSubmit(formAction); 
    });

    function formSubmit(formAction) {
        document.form.action = formAction; 
        document.form.submit();
    }

    /*====================================
    Check All
    ======================================*/
    $(".checkAll").click(function(){
        if($(this).prop("checked")){
            $("input[type='checkbox']").prop("checked",true);
        }else{
            $("input[type='checkbox']").prop("checked",false);
        }
    })
    $("input[type='checkbox']").click(function(){
        var checkLength = $(this).closest("tbody").find("input[type='checkbox']:checked").length;
        var inputLenhth = $(this).closest("tbody").find("input[type='checkbox']").length;

        if(!$(this).prop("checked")){
            $(".checkAll").prop("checked",false);
            $(".checkAll").prop("checked",false);
        }
        else{
            if(checkLength==inputLenhth){
                $(".checkAll").prop("checked",true);
                $(".checkAll").prop("checked",true);
            }
        }
    })

    /*====================================
    CKEditor 5
    ======================================*/
    ClassicEditor
        .create(document.querySelector( 
            '#editor'))
        .then(editor=>{
            console.log(editor);
        })
        .catch(error=>{
            console.error(error);
        });

    /*====================================
    Dropdown Slide Toggle
    ======================================*/
    $(".flip").click(function() {
        if ($(".panel").is(":visible")) {
            $(this).text("訂單明細🔻");
        }else {
            $(this).text("訂單明細🔺");
        }
        var panel = $(this).closest("td").find(".panel");
        panel.slideToggle("slow");
    });

    setTimeout(function() {
        $('.popup-content').fadeOut('slow');
    }, 3000); 
});