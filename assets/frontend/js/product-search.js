$(function(){
    let sortBy = null;
    let sortOrder = null;
    let search = null;
    let page = 0;
    $('.search-bar').find('button').click(function(){
        search = $('.search-bar').find('input').val();
        window.location.href = `/product/search?search=${search}`;
    })

    $('.sort-by').find('span').eq(0).click(function(){
        search = $('#search').data('search');
        window.location.href = `/product/search?search=${search}`;
    });

    $('.sort-by').find('span').eq(2).click(function(){
        search = $('#search').data('search');
        sortBy = 'price';
        if ($(this).text() === '價格🔺' && $(this).attr('class') === 'cursor active'){
            sortOrder = 'desc';
        } else {
            sortOrder = 'asc';
        }
        window.location.href = `/product/search?search=${search}&sortBy=${sortBy}&sortOrder=${sortOrder}`;
    });
});