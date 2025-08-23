$(function() {
    $(document).on('click', '#again', function() {
        let orderId = $(this).data('orderId');
        repurchase(orderId);
    });

    async function repurchase(orderId) { 
        try {
            const response = await fetch(`/user/order/repurchase/${orderId}`);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();

            if (data.productExists) {
                window.location.href = `/user/cart`;
            } else {
                $('.hidden').show();
                setTimeout(function() {
                    $('.hidden').hide(); 
                }, 3000);
            }
        } catch (error) {
            console.error('Error occurred:', error);
            alert('發生錯誤，請稍後再試');
        }
    }
});