<?php
require_once 'config/bootstrap.php';
require_once BACK . 'inc/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="title">
            <p>編輯商品</p>
        </div>
    </div>
    <div class="card-body">
        <form class="form" method="post" enctype="multipart/form-data" action="/admin/product/update/<?= $product['id'] ?>">
            <div class="form-group">
                <label>商品標題<span>*</span></label>
                <input class="form-control" type="text" name="title" required="required" value="<?= $product['title'] ?>">
            </div>

            <div class="form-group">
                <label>價格<span>*</span></label>
                <input class="form-control" type="number" name="price" min="1" required="required" value="<?= $product['price'] ?>">
            </div>

            <div class="form-group">
                <label>庫存<span>*</span></label>
                <input class="form-control" type="number" name="stock" min="0" required="required" value="<?= $product['stock'] ?>">
            </div>

            <div class="form-group">
                <label>圖檔<span>*</span></label>
                <input class="form-control" name="photo" type="file" data-target="preview_product_image" accept="image/*">
                <img style="max-width: 400px" id="preview_product_image" src="<?= $product['photo'] ?>">
            </div>

            <div class="form-group">
                <label>商品詳情<span>*</span></label>
                <textarea id="editor" name="description"><?= $product['description'] ?></textarea>
            </div>
            
            <div class="form-group">
                <button class="create-button" type="submit">提交</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once BACK . 'inc/footer.php';
?>