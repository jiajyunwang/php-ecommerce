$(function() {
    var ratings = [];
    $(document).on('click', '.btn-review', function() {
        let orderId = this.dataset.orderId;
        document.querySelectorAll('.rating-box').forEach(ratingBox => {
            const index = ratingBox.dataset.index; 
            const stars = ratingBox.children;
            let ratingValue = 0;
            let activeIndex = -1; 

            openReview(orderId);

            for (let i = 0; i < stars.length; i++) {
                stars[i].addEventListener("mouseover", function () {
                    for (let j = 0; j < stars.length; j++) {
                        stars[j].classList.remove("full");
                    }
                    for (let j = 0; j <= i; j++) {
                        stars[j].classList.add("full");
                    }
                });

                stars[i].addEventListener("click", function (e) {
                    ratingValue = i + 1;
                    activeIndex = i;
                    ratings[index] = ratingValue;
                    const fieldName = $(this).attr('field');
                    document.getElementById('rate-'+fieldName+'-'+index).value = ratingValue;
                    var ratingCount = 0;
                    var ratingLength =  document.querySelectorAll('.rating-' + fieldName).length;
                    if(ratings.length==ratingLength){
                        for (let i = 0; i < ratingLength; i++) {
                            if(0<ratings[i] && ratings[i]<=5){
                                ratingCount++;
                            }
                        }
                        if(ratingCount==ratingLength){
                            $('.btn-prohibit-' + fieldName).hide();
                            $('.btn-accent-' + fieldName).show(); 
                        }
                    }
                });

                stars[i].addEventListener("mouseout", function () {
                    for (let j = 0; j < stars.length; j++) {
                        stars[j].classList.remove("full");
                    }
                    for (let j = 0; j <= activeIndex; j++) {
                        stars[j].classList.add("full");
                    }
                });
            }
            $('.btn-dark').click(function(e) {
                const fieldName = $(this).attr('field');
                $(`.order-`+fieldName).hide();
                $(`.order-`+fieldName).find('textarea').val('');
                $('.btn-prohibit-' + fieldName).show();
                $('.btn-accent-' + fieldName).hide(); 
                for (let j = 0; j < stars.length; j++) {
                    stars[j].classList.remove("full");
                }
                ratings = [];
            });
        });

        function openReview(orderId) { 
            $(`.order-${orderId}`).show();
        } 
    });
});