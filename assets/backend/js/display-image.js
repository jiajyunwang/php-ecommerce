$(function(){
    var input = document.querySelector('input[name=photo]')
    input.addEventListener('change', function(e){
        readURL(e.target);   
    })
    function readURL(input){
        if(input.files && input.files[0]){
            var reader = new FileReader();
            reader.onload = function (e) {
                var imgId = input.getAttribute('data-target')
                var img = document.querySelector('#' + imgId)
                img.setAttribute('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
});