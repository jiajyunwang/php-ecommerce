document.addEventListener('DOMContentLoaded', function () {
    let page = 1;
    let sortBy = 'created_at';
    let sortOrder = 'desc';
    const reviewContainer = document.getElementById('review-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const newestBtn = document.getElementById('newest-btn');
    const productId = newestBtn.dataset.productId;

    let observer = new IntersectionObserver(loadMoreReviews);

    document.querySelectorAll('.sort-button').forEach((button) => {
        button.addEventListener('click', function () {
            var element = document.querySelector('.nav-tabs').children;
            for (let i = 0; i < element.length; i++) {
                element[i].classList.remove("active");
            }
            button.classList.add("active");
            sortBy = this.dataset.sort; 
            sortOrder = this.dataset.order;
            page = 0;
            reviewContainer.innerHTML = ''; 
            observer.disconnect(); 
            observer.observe(loadingIndicator);
        });
    });

    function loadMoreReviews(entries) {
        if (entries[0].isIntersecting) {
            loadingIndicator.textContent = '';
            loadingIndicator.classList.add("loader");
            page++;
            fetchReviews(page, sortBy, sortOrder, productId);
        }
    }

    observer.observe(loadingIndicator);

    async function fetchReviews(page, sortBy, sortOrder, productId) {
        try {
            const response = await fetch(
                `/reviews/fetch?page=${page}&sort_by=${sortBy}&sort_order=${sortOrder}&product_id=${productId}`
            );
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            loadingIndicator.classList.remove("loader");
            if (data.length === 0) {
                observer.disconnect(); 
                loadingIndicator.textContent = '無更多評價';
                return;
            }
            
            data.forEach((review) => {
                const reviewElement = createReviewElement(review);
                reviewContainer.appendChild(reviewElement);
            });

            observer.disconnect(); 
            observer.observe(loadingIndicator);
        } catch (error) {
            console.error('Error fetching reviews:', error);
        }
    }
    function createReviewElement(review) {
        const div = document.createElement('div');
        const date = new Date(review.created_at);
        const formattedDate = date.toISOString().split('T')[0];
        div.innerHTML = `
            <div class="review-inner m-b-m">
                <p class="m-0">${review.nickname}</p>
                <div class="ratings">
                    <div class="empty-stars"></div>
                    <div class="full-stars" style="width:${review.percentage}%"></div>
                </div>
                <p class="m-b-l">${review.review || ''}</p>
                <p class="date">${formattedDate}</p>
            </div>
        `;
        return div;
    }
});