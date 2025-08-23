document.addEventListener('DOMContentLoaded', function () {
    let page = 1;
    let sortBy = 'created_at';
    let sortOrder = 'desc';
    const loading = document.getElementById('loading');
    const orderType = document.getElementById('order-container').dataset.type;

    let observer = new IntersectionObserver(loadMoreOrders); 

    function loadMoreOrders(entries) {
        if (entries[0].isIntersecting) {
            loading.textContent = '';
            loading.classList.add("loader");
            page++;
            fetchOrders(page, orderType);
        }
    }

    observer.observe(loading);

    async function fetchOrders(page, orderType) {
        try {
            const response = await fetch(
                `/user/orders/fetch?page=${page}&type=${orderType}`
            );
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            loading.classList.remove("loader");
            
            if (html.trim().length === 0) {
                observer.disconnect();
                loading.textContent = '無更多訂單';
                return;
            }
            
            const container = document.getElementById('order-container');
            container.insertAdjacentHTML('beforeend', html);
            
            observer.disconnect(); 
            observer.observe(loading);

        } catch (error) {
            console.error('Error fetching orders:', error);
        }
    }
});