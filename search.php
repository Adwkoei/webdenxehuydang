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

    <?php
    if (!empty($query)) {
        $stmt = $conn->prepare("SELECT p.id as 'pid', s.* FROM subfolder as s JOIN `post`as p ON p.name = s.post WHERE s.name LIKE ? OR s.post LIKE ? OR s.category LIKE ? ORDER BY RAND() LIMIT 12");
        $searchTerm = "%{$query}%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result && $result->num_rows > 0) {
            echo '<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">';
            while ($product = $result->fetch_assoc()) {
                $folderAnd = 'assets/upload/'.$product['pid'].'/'.$product['id'].'/';
                if (is_dir($folderAnd)) {
                    $filesAnd = scandir($folderAnd);
                } else {
                    $filesAnd = [];
                }
                $filesAnd = array_filter($filesAnd, function($fileAnd) use ($folderAnd) {
                    return is_file($folderAnd . $fileAnd);
                });
                $firstFileAnd = reset($filesAnd);
                if($firstFileAnd) {
                    $imgFileAnd = '<img src="' . @$folderAnd.@$firstFileAnd . '" alt="' . htmlspecialchars($product['name']) . '" class="w-full h-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">';
                } else {
                    $imgFileAnd = '<span style="height: 180px;" class="w-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center"></span>';
                }
                echo '<a href="product.php?idp=' . htmlspecialchars($product['id']) . '" 
                        class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200">
                        '.$imgFileAnd.'
                        <h2 class="text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-semibold mt-4 mb-4">' . htmlspecialchars($product['name']) . ' - '.htmlspecialchars($product['post']).' '.htmlspecialchars($product['category']).'</h2>
                    </a>';
            }
            echo '</div>';
        } else {
            echo '<p class="text-red-500 text-center">Không có sản phẩm nào được tìm thấy.</p>';
        }
    } else {
        echo '<p class="text-red-500 text-center">Vui lòng nhập từ khóa để tìm kiếm.</p>';
    }
    ?>
</div>

<?php
require('system/foot.php');
?>
