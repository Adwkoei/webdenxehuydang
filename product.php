<?php
require('system/dbconfig.php');

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: 404.php');
    exit();
}

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.short_name as category_short_name, c.slug as category_slug 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: 404.php');
    exit();
}

// Cập nhật lượt xem
$updateStmt = $conn->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?");
$updateStmt->bind_param("i", $product['id']);
$updateStmt->execute();
$updateStmt->close();

require('system/head.php');

// Lấy danh sách ảnh sản phẩm
$upload_dir = "assets/upload/{$product['product_code']}";
$images = [];
if (is_dir($upload_dir)) {
    $files = array_diff(scandir($upload_dir), ['.', '..']);
    foreach ($files as $file) {
        if ($file !== 'thumbnail.png' && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $images[] = $file;
        }
    }
}
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="text-[#27f2f2]">Trang Chủ</a></li>
            <li><span class="mx-2">/</span></li>
            <?php if ($product['category_slug']): ?>
                <li><a href="category.php?slug=<?= $product['category_slug'] ?>" class="text-[#27f2f2]"><?= htmlspecialchars($product['category_short_name']) ?></a></li>
                <li><span class="mx-2">/</span></li>
            <?php endif; ?>
            <li class="text-gray-400"><?= htmlspecialchars($product['short_name']) ?></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="relative thumbnail-container">
            <?php if (!empty($images)): ?>
                <!-- Ảnh chính -->
                <img id="main-image" src="<?= $upload_dir . '/' . reset($images) ?>" class="w-full h-96 object-cover rounded-lg shadow mb-4 transition-opacity duration-500 ease-in-out bg-[url('assets/img/background.avif')] bg-cover bg-center">
                
                <?php if (count($images) > 1): ?>
                <div class="relative flex overflow-x-auto gap-3 pb-3 scrollbar-hide">
                    <button id="scroll-left" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white p-2 w-12 h-12 rounded-full shadow hover:bg-gray-600 transition flex items-center justify-center z-10">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                    <!-- Thumbnail ảnh -->
                    <div id="thumbnail-container" class="flex gap-3 overflow-x-auto scroll-smooth scrollbar-hide w-full px-10">
                        <?php foreach ($images as $image): ?>
                            <img src="<?= $upload_dir . '/' . $image ?>" class="thumbnail w-24 h-24 rounded-lg cursor-pointer border-cyan-400 border-2 opacity-60 hover:opacity-100 transition-all object-cover bg-[url('assets/img/background.avif')] bg-cover bg-center">
                        <?php endforeach; ?>
                    </div>
                    <button id="scroll-right" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white p-2 w-12 h-12 rounded-full shadow hover:bg-gray-600 transition flex items-center justify-center z-10">
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <img id="main-image" src="assets/img/background.avif" class="w-full h-96 object-cover rounded-lg shadow mb-4 transition-opacity duration-500 ease-in-out bg-[url('assets/img/background.avif')] bg-cover bg-center">
            <?php endif; ?>
        </div>

        <div>
            <h1 class="text-3xl text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-bold mb-4">
                <?= htmlspecialchars($product['name']) ?>
                <?php
                $auth = isAuthenticated();
                if ($auth['status']) {
                    echo '<a class="text-sm ml-2 text-blue-400 hover:text-blue-600 hover:scale-110 transition duration-300" href="admin.php?action=edit-product&id='.$product['id'].'"><i class="fa fa-edit"></i></a>';
                    echo '<a class="text-sm ml-2 text-red-400 hover:text-red-500 hover:scale-110 transition duration-300" href="admin.php?action=delete-product&id='.$product['id'].'" onclick="return confirm(`Bạn có chắc muốn xóa sản phẩm này không?`);"><i class="fa fa-trash"></i></a>';
                }
                ?>           
            </h1>
            
            <div class="mb-4 flex items-center gap-4">
                <span class="bg-[#27f2f2] text-gray-900 px-3 py-1 rounded-full text-sm font-semibold"><?= htmlspecialchars($product['product_code']) ?></span>
                <?php if ($product['category_short_name']): ?>
                    <span class="bg-gray-700 text-white px-3 py-1 rounded-full text-sm"><?= htmlspecialchars($product['category_short_name']) ?></span>
                <?php endif; ?>
            </div>
            
            <p class="mb-4">
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <span class="ml-2 text-gray-400">Lượt xem: <?= number_format($product['view_count']) ?></span>
            </p>
            
            <div class="mb-6">
                <div class="flex items-center grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php if (!empty($product['youtube'])): ?>
                        <a target="_blank" href="<?= htmlspecialchars($product['youtube']) ?>" 
                            class="relative flex items-center justify-center gap-3 w-auto h-16 rounded-lg bg-red-600 text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/youtube.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
                            <img src="assets/img/youtube.png" alt="Youtube" class="w-8 h-8">
                            Xem video<br>Youtube
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($product['tiktok'])): ?>
                        <a target="_blank" href="<?= htmlspecialchars($product['tiktok']) ?>" 
                            class="relative flex items-center justify-center gap-3 w-auto h-16 rounded-lg bg-[#FE2C55] text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/tiktok.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
                            <img src="assets/img/tiktok.png" alt="Tiktok" class="w-8 h-8">
                            Xem video<br>Tiktok
                        </a>
                    <?php endif; ?>

                    <a href="tel:<?=$phone?>" 
                        class="relative flex items-center justify-center gap-3 w-auto h-16 rounded-lg bg-blue-600 text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/call.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
                        <img src="assets/img/call.png" alt="Call" class="w-8 h-8">
                        Liên hệ ngay:<br><?=$phone?>
                    </a>                         
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty(trim($product['content']))): ?>
    <div class="mt-8 pt-4 mb-6 bg-gray-800 shadow-lg py-8 px-8 rounded-2xl text-white">
        <h2 class="text-2xl font-semibold mb-3">CHI TIẾT SẢN PHẨM</h2>
        <div class="leading-relaxed"><?= nl2br(htmlspecialchars($product['content'])) ?></div>
    </div>
    <?php endif; ?>

    <div class="flex items-center gap-4 mb-8" data-aos="fade-up" data-aos-duration="1000">   
        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" class="relative flex items-center justify-center gap-3 w-52 h-16 rounded-lg bg-blue-600 text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/facebook.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
            <img src="assets/img/facebook.png" alt="Facebook" class="w-6 h-6">
            Chia sẻ Facebook
        </a>
        <button onclick="copyLink('<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://")?><?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>')" class="relative flex items-center justify-center gap-3 w-52 h-16 rounded-lg bg-red-500 text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/copy.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
            <img src="assets/img/copy.png" alt="Copy" class="w-6 h-6">
            Copy link
        </button>
    </div>

    <?php
    // Sản phẩm liên quan
    $relatedSql = "SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 8";
    $relatedStmt = $conn->prepare($relatedSql);
    $relatedStmt->bind_param("ii", $product['category_id'], $product['id']);
    $relatedStmt->execute();
    $relatedResult = $relatedStmt->get_result();
    
    if ($relatedResult && $relatedResult->num_rows > 0) {
        echo '<h2 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282]" data-aos="fade-up" data-aos-duration="800">
            <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
                SẢN PHẨM LIÊN QUAN
            </div>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10" data-aos="fade-up" data-aos-duration="1400">';
        
        while ($relatedProduct = $relatedResult->fetch_assoc()) {
            $thumbnail = "assets/upload/{$relatedProduct['product_code']}/thumbnail.png";
            if (!file_exists($thumbnail)) {
                $thumbnail = "assets/img/background.avif";
            }
            
            echo '<a href="product.php?slug=' . htmlspecialchars($relatedProduct['slug']) . '" 
                    class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200">
                    <img src="' . $thumbnail . '" alt="' . htmlspecialchars($relatedProduct['short_name']) . '" class="w-full h-48 object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">
                    <div class="p-4 text-center">
                        <h3 class="text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-semibold mb-1">' . htmlspecialchars($relatedProduct['short_name']) . '</h3>
                        <p class="text-gray-400 text-sm">' . htmlspecialchars($relatedProduct['product_code']) . '</p>
                    </div>
                </a>';
        }
        echo '</div>';
    }
    $relatedStmt->close();
    ?>
</div>

<script>
    document.getElementById('scroll-left')?.addEventListener('click', function() {
        document.getElementById('thumbnail-container').scrollBy({ left: -200, behavior: 'smooth' });
    });

    document.getElementById('scroll-right')?.addEventListener('click', function() {
        document.getElementById('thumbnail-container').scrollBy({ left: 200, behavior: 'smooth' });
    });  

    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('main-image');
    
    thumbnails.forEach(img => {
        img.addEventListener('click', () => {
            mainImage.classList.add('opacity-0');
            setTimeout(() => {
                mainImage.src = img.src;
                mainImage.classList.remove('opacity-0');
            }, 300);
            thumbnails.forEach(t => t.classList.remove('border-cyan-400', 'opacity-100'));
            img.classList.add('border-cyan-400', 'opacity-100');
        });
    });

    function copyLink(link) {
        navigator.clipboard.writeText(link).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: 'Đã sao chép liên kết!',
                timer: 1500,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        }).catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Thất bại!',
                text: 'Không thể sao chép liên kết.',
                timer: 1500,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        });
    }

    // Auto slide thumbnails
    const thumbnailElements = document.querySelectorAll('img.thumbnail');
    let currentIndex = 0;
    let isHovered = false;
    const container = document.querySelector('.thumbnail-container');
    
    if (container) {
        container.addEventListener('pointerenter', () => {
            isHovered = true;
        });
        container.addEventListener('pointerleave', () => {
            isHovered = false;
        });
    }
    
    function clickThumbnails() {
        if (thumbnailElements.length > 1) {
            if (!isHovered) {
                if (currentIndex < thumbnailElements.length) {
                    thumbnailElements[currentIndex].click();
                    currentIndex++;
                } else {
                    currentIndex = 0;
                }
            }
            setTimeout(clickThumbnails, 3000);
        }
    }
    
    if (thumbnailElements.length > 1) {
        clickThumbnails();
    }
</script>

<?php
require('system/foot.php');
?>