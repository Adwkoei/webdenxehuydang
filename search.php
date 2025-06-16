<?php
require('system/dbconfig.php');
require('system/head.php');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            KẾT QUẢ TÌM KIẾM
        </div>
    </h1>

    <?php if (!empty($query)): ?>
        <p class="text-center text-gray-400 mb-8">Kết quả tìm kiếm cho: "<span class="text-[#27f2f2] font-semibold"><?= htmlspecialchars($query) ?></span>"</p>
    <?php endif; ?>

    <?php
    if (!empty($query)) {
        // Tìm kiếm mờ với nhiều từ khóa
        $searchTerms = explode(' ', $query);
        $searchConditions = [];
        $searchParams = [];
        $paramTypes = '';
        
        foreach ($searchTerms as $term) {
            if (!empty(trim($term))) {
                $searchConditions[] = "(p.name LIKE ? OR p.short_name LIKE ? OR p.product_code LIKE ? OR c.name LIKE ? OR c.short_name LIKE ?)";
                $searchTerm = "%{$term}%";
                $searchParams = array_merge($searchParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                $paramTypes .= 'sssss';
            }
        }
        
        if (!empty($searchConditions)) {
            $sql = "SELECT p.*, c.short_name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE " . implode(' OR ', $searchConditions) . " 
                    ORDER BY p.created_at DESC LIMIT 20";
            
            $stmt = $conn->prepare($sql);
            if (!empty($searchParams)) {
                $stmt->bind_param($paramTypes, ...$searchParams);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                echo '<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">';
                while ($product = $result->fetch_assoc()) {
                    $thumbnail = "assets/upload/{$product['product_code']}/thumbnail.png";
                    if (!file_exists($thumbnail)) {
                        $thumbnail = "assets/img/background.avif";
                    }
                    
                    echo '<a href="product.php?slug=' . htmlspecialchars($product['slug']) . '" 
                            class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200">
                            <img src="' . $thumbnail . '" alt="' . htmlspecialchars($product['short_name']) . '" class="w-full h-48 object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">
                            <div class="p-4 text-center">
                                <h3 class="text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-semibold mb-1">' . htmlspecialchars($product['short_name']) . '</h3>
                                <p class="text-gray-400 text-sm">' . htmlspecialchars($product['product_code']) . '</p>
                                ' . ($product['category_name'] ? '<p class="text-gray-500 text-xs mt-1">' . htmlspecialchars($product['category_name']) . '</p>' : '') . '
                            </div>
                        </a>';
                }
                echo '</div>';
            } else {
                echo '<div class="text-center py-20">
                        <i class="fas fa-search text-6xl text-gray-600 mb-4"></i>
                        <p class="text-red-500 text-lg">Không tìm thấy sản phẩm nào phù hợp.</p>
                        <p class="text-gray-400 mt-2">Hãy thử với từ khóa khác hoặc <a href="' . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '" class="text-[#27f2f2] hover:underline">quay về trang chủ</a></p>
                      </div>';
            }
            $stmt->close();
        }
    } else {
        echo '<div class="text-center py-20">
                <i class="fas fa-search text-6xl text-gray-600 mb-4"></i>
                <p class="text-red-500 text-lg">Vui lòng nhập từ khóa để tìm kiếm.</p>
              </div>';
    }
    ?>
</div>

<?php
require('system/foot.php');
?>