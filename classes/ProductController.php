<?php

require_once 'vendor/autoload.php';
require_once 'Observers/ProductObserver.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Aws\S3\S3Client;

class ProductController {
    public function create() {
        require_once 'views/backend/product/create.php';
        exit;
    }

    public function store($data, $input) { 
        $url = $this->imageStore($input);
        $link = new DbController();
        $price = $data['price'];
        $stock = $data['stock'];
        $description = $data['description'];
        $title = $data['title'];
        $productSql = "
            INSERT INTO `products` (
                `title`, 
                `description`, 
                `stock`, 
                `price`,
                `photo`,
                `status`
            ) 
            VALUE (
                '$title', 
                '$description', 
                '$stock', 
                '$price',
                '$url',
                'active'
            )
        ";
        $link->connect()->query($productSql);

        $productSql = "SELECT * FROM `products` WHERE `photo`='$url' LIMIT 1;";
        $product = $link->connect()->query($productSql);
        $product = mysqli_fetch_assoc($product);
        $productObserver = new ProductObserver();
        $productObserver->created($product);

        header('Location: /admin');
    }

    public function imageStore($input) {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($input);
        $width = $image->width();
        $height = $image->height();
        if ($width>=$height) {
            $image->pad($width, $width, 'fff');
        } else {
            $image->pad($height, $height, 'fff');
        }
        $encoded = $image->encode();
        date_default_timezone_set('Asia/Taipei');
        $fileName = date('ymdis').'.jpg';
        $url = null;

        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'endpoint'    => 'http://98.83.133.155:9000',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => 'minioadmin',
                'secret' => 'minioadmin',
            ],
        ]);

        $bucket = 'uploads';
        $key = $fileName; 
        $imageBinary = (string) $image->toJpeg(90);

        try {
            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $imageBinary,
                'ContentType' => 'image/jpeg',
                'ACL'    => 'public-read', 
            ]);

            $url = 'http://98.83.133.155:9000/uploads/'.$fileName;

        } catch (Exception $e) {
            echo "❌ 上傳失敗：" . $e->getMessage();
        }

        return $url;
    }

    public function toInactive($id) {
        $link = new DbController();
        $productSql = "
            UPDATE `products` 
            SET `status`='inactive' 
            WHERE `id`='$id' 
            LIMIT 1
        ";
        $link->connect()->query($productSql);

        $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1;";
        $product = $link->connect()->query($productSql);
        $product = mysqli_fetch_assoc($product);
        $productObserver = new ProductObserver();
        $productObserver->updated($product);

        $cartSql = "
            DELETE FROM `carts` 
            WHERE `product_id`='$id' 
        ";
        $link->connect()->query($cartSql);

        header('Location: /admin');
    }

    public function toActive($id) {
        $link = new DbController();
        $productSql = "
            UPDATE `products` 
            SET `status`='active' 
            WHERE `id`='$id' 
            LIMIT 1
        ";
        $link->connect()->query($productSql);

        $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1;";
        $product = $link->connect()->query($productSql);
        $product = mysqli_fetch_assoc($product);
        $productObserver = new ProductObserver();
        $productObserver->updated($product);

        header('Location: /admin/product?type=unlisted');
    }

    public function edit($id) {
        $link = new DbController();
        $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1";
        $product = $link->connect()->query($productSql); 
        $product = mysqli_fetch_assoc($product);

        require_once 'views/backend/product/edit.php';
        exit;
    }

    public function update($data, $id) {
        $link = new DbController();
        $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1";
        $product = $link->connect()->query($productSql); 
        $product = mysqli_fetch_assoc($product);
        $price = $data['price'];
        $stock = $data['stock'];
        $description = $data['description'];
        $title = $data['title'];
        $productSql = "
            UPDATE `products` 
            SET `price`='$price',
                `stock`='$stock',
                `description`='$description',
                `title`='$title'
            WHERE `id`='$id' 
            LIMIT 1
        ";
        if ($_FILES['photo']['tmp_name']){
            $this->imageDelete($product);
            $url = $this->imageStore($_FILES['photo']['tmp_name']);
            $productSql = "
                UPDATE `products` 
                SET `price`='$price',
                    `stock`='$stock',
                    `description`='$description',
                    `title`='$title',
                    `photo`='$url'
                WHERE `id`='$id' 
                LIMIT 1
            ";
        }
        $link->connect()->query($productSql);

        $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1;";
        $product = $link->connect()->query($productSql);
        $product = mysqli_fetch_assoc($product);
        $productObserver = new ProductObserver();
        $productObserver->updated($product);

        header('Location: /admin');
    }

    public function imageDelete($product) {
        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-1',
            'endpoint'    => 'http://98.83.133.155:9000',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => 'minioadmin',
                'secret' => 'minioadmin',
            ],
        ]);

        $imageName = basename($product['photo']);
        $bucket = 'uploads';

        try {
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $imageName,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => '刪除失敗：' . $e->getMessage(),
            ]);
        }
    }

    public function destroyProducts($data) {
        $link = new DbController();
        foreach ($data['check'] as $id) {
            $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1";
            $product = $link->connect()->query($productSql); 
            $product = mysqli_fetch_assoc($product);
            $productObserver = new ProductObserver();
            $productObserver->deleted($product);
            $this->imageDelete($product);
            $productSql = "
                DELETE FROM `products` 
                WHERE `id`='$id' 
                LIMIT 1
            ";
            $link->connect()->query($productSql);

        }
        header('Location: /admin');
    }

    public function destroy($id) {
        $link = new DbController();
        $productSql = "SELECT * FROM `products` WHERE `id`='$id' LIMIT 1";
        $product = $link->connect()->query($productSql); 
        $product = mysqli_fetch_assoc($product);
        $productObserver = new ProductObserver();
        $productObserver->deleted($product);
        $this->imageDelete($product);
        $productSql = "
            DELETE FROM `products` 
            WHERE `id`='$id' 
            LIMIT 1
        ";
        $link->connect()->query($productSql);

        header('Location: /admin');
    }
}