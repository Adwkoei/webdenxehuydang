<?php
require('system/dbconfig.php');
require('system/head.php');
?>
<main class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            DANH MỤC SẢN PHẨM
        </div>
    </h1>
    
    <!-- Hiển thị danh mục -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10" data-aos="fade-up" data-aos-duration="1000">
        <?php
        $sql = "SELECT * FROM categories ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($category = $result->fetch_assoc()) {
                // Đếm số sản phẩm trong danh mục
                $countSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $category['id']);
                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $productCount = $countResult->fetch_assoc()['count'];
                $countStmt->close();

                echo '
                    <div onclick="viewCategory(' . $category['id'] . ', \'' . htmlspecialchars($category['slug']) . '\')" 
                        class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-6 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200 cursor-pointer">
                        <div class="w-full h-32 bg-gradient-to-br from-[#27f2f2] to-blue-500 rounded-lg mb-4 flex items-center justify-center">
                            <i class="fas fa-lightbulb text-white text-4xl"></i>
                        </div>
                        <h2 class="text-xl text-[#27f2f2] text-center drop-shadow-[0_0_5px_#27f2f282] font-semibold mb-2">' . htmlspecialchars($category['short_name']) . '</h2>
                        <p class="text-gray-400 text-sm text-center">' . $productCount . ' sản phẩm</p>
                    </div>          
                ';
            }
        } else {
            echo '<p class="text-red-500 col-span-full text-center">Chưa có danh mục nào.</p>';
        }
        ?>
    </div>

    <!-- Sản phẩm mới nhất -->
    <h2 class="text-2xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-8 drop-shadow-[0_0_10px_#27f2f282]" 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-2 border-[#27f2f2] pb-2">
            SẢN PHẨM MỚI NHẤT
        </div>
    </h2>
    
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">
        <?php
        $sql = "SELECT p.*, c.short_name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC LIMIT 8";
        $result = $conn->query($sql);

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
                            ' . ($product['category_name'] ? '<p class="text-gray-500 text-xs mt-1">' . htmlspecialchars($product['category_name']) . '</p>' : '') . '
                        </div>
                    </div>          
                ';
            }
        } else {
            echo '<p class="text-red-500 col-span-full text-center">Chưa có sản phẩm nào.</p>';
        }
        ?>
    </div> 
</main>

<script>
function viewCategory(categoryId, slug) {
    window.location.href = `category.php?slug=${slug}`;
}

function viewProduct(slug) {
    window.location.href = `product.php?slug=${slug}`;
}
</script>

<?php
require('system/foot.php');
?>