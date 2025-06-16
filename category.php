<?php
require('system/dbconfig.php');

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: 404.php');
    exit();
}

// Lấy thông tin danh mục
$stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    header('Location: 404.php');
    exit();
}

require('system/head.php');
?>

<main class="max-w-7xl mx-auto px-4 py-10">
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="text-[#27f2f2]">Trang Chủ</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-400"><?= htmlspecialchars($category['short_name']) ?></li>
        </ol>
    </nav>

    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            <?= htmlspecialchars($category['short_name']) ?>
        </div>
    </h1>
    
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">
        <?php
        $sql = "SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                $thumbnail = "assets/upload/{$product['product_code']}/thumbnail.png";
                if (!file_exists($thumbnail)) {
                    $thumbnail = "assets/img/background.avif";
                }
                
                echo '
                    <div onclick="viewProduct(\'' . htmlspecialchars($product['slug']) . '\')" 
                        class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200 cursor-pointer">
                        <img src="' . $thumbnail . '" alt="' . htmlspecialchars($product['short_name']) . '"
                            class="w-full h-48 object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">
                        <div class="p-4 text-center">
                            <h3 class="text-lg text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-semibold mb-1">' . htmlspecialchars($product['short_name']) . '</h3>
                            <p class="text-gray-400 text-sm">' . htmlspecialchars($product['product_code']) . '</p>
                        </div>
                    </div>          
                ';
            }
        } else {
            echo '<p class="text-red-500 col-span-full text-center">Chưa có sản phẩm nào trong danh mục này.</p>';
        }
        $stmt->close();
        ?>
    </div>
</main>

<script>
function viewProduct(slug) {
    window.location.href = `product.php?slug=${slug}`;
}
</script>

<?php
require('system/foot.php');
?>