<?php
require_once 'config/bootstrap.php';
require_once BACK . 'inc/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="title">
            <p>新增商品</p>
        </div>
    </div>
    <div class="card-body">
        <form class="form" method="post" enctype="multipart/form-data" action="/admin/product/store">
            <div class="form-group">
                <label>商品標題<span>*</span></label>
                <input class="form-control" type="text" name="title" required="required">
            </div>

            <div class="form-group">
                <label>價格<span>*</span></label>
                <input class="form-control" type="number" min="1" name="price" required="required">
            </div>

            <div class="form-group">
                <label>庫存<span>*</span></label>
                <input class="form-control" type="number" min="0" name="stock" required="required">
            </div>

            <div class="form-group">
                <label>圖檔<span>*</span></label>
                <input class="form-control" name="photo" type="file" data-target="preview_product_image" accept="image/*" required="required">
                <img style="max-width: 400px" id="preview_product_image" src="{{asset('frontend/images/2024-07-09 214802.jpg')}}">
            </div>

            <div class="form-group">
                <label>商品詳情<span>*</span></label>
                <textarea id="editor" name="description"></textarea>
            </div>

            <div class="form-group">
                <button class="create-button" type="submit">刊登</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once BACK . 'inc/footer.php';
?>