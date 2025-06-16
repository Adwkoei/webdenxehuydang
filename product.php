<?php
require('system/dbconfig.php');

$idp = @$_GET['idp'];

$stmt = $conn->prepare("SELECT p.id as 'pid',c.id as 'cid', s.* FROM subfolder as s JOIN `post`as p ON p.name = s.post JOIN `category`as c ON (c.name = s.category AND c.post = s.post) WHERE s.id = ?");
$stmt->bind_param("i", $idp);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) { 
    header('Location: 404.php');
    exit();
} else {
    require('system/head.php');
}
$pid=$product['pid'];
$cid=$product['cid'];
$productPost = htmlspecialchars($product['post']);
$productCate = htmlspecialchars($product['category']);
$productName = htmlspecialchars($product['name']);
$upload_dir = "assets/upload/{$pid}/{$idp}";
$images = [];
if (is_dir($upload_dir)) {
    $images = array_diff(scandir($upload_dir), ['.', '..']);
}
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="text-[#27f2f2]">Trang Chủ</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="view.php?cate=<?=$productPost?>&sub=<?=$productCate?>" class="text-[#27f2f2]"><?= $productPost ?> <?= $productCate ?></a></li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="relative thumbnail-container">
            <?php if (!empty($images)): ?>
                <!-- Ảnh chính -->
                <img id="main-image" src="<?= $upload_dir . '/' . reset($images) ?>" class="w-full h-96 object-cover rounded-lg shadow mb-4 transition-opacity duration-500 ease-in-out bg-[url('assets/img/background.avif')] bg-cover bg-center">
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
            <?php else: ?>
                <img id="main-image" src="assets/img/background.avif" class="w-full h-96 object-cover rounded-lg shadow mb-4 transition-opacity duration-500 ease-in-out bg-[url('assets/img/background.avif')] bg-cover bg-center">
            <?php endif; ?>
        </div>

        <div>
            <h1 class="text-3xl text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-bold mb-4"><?= $productName .' dành cho '. $productCate ?>
            <?php
            $auth = isAuthenticated();
            if ($auth['status']) {
                echo '<a class="text-sm ml-2 text-blue-400 hover:text-blue-600 hover:scale-110 transition duration-300" href="admin.php?action=edit-post&id='.$pid.'&sub='.$product['id'].'"><i class="fa fa-edit"></i></a>';
                echo '<a class="text-sm ml-2 text-red-400 hover:text-red-500 hover:scale-110 transition duration-300" href="admin.php?action=delete-post&id='.$pid.'&sub='.$product['id'].'" onclick="return confirm(`Bạn có chắc muốn xóa bài viết này không?`);"><i class="fa fa-trash"></i></a>';
            }
            ?>           
            </h1>
            <p class="mb-4">
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
                <i class="fa-solid fa-star text-yellow-300"></i>
            </p>            
            <p class="mb-4"><i class="fa fa-bicycle mr-2"></i> Loại xe: <span class="font-semibold text-[#27f2f2]"><?= $productPost ?></span></p>
            <p class="mb-4"><i class="fa fa-screwdriver mr-2"></i> Đời xe: <span class="font-semibold text-[#27f2f2]"><?= $productCate ?></span></p>
            <ul id="policy-list" class="bg-gray-800 shadow-lg py-4 px-4 rounded-2xl mb-4 text-white text-sm">
                <?php 
                $policies = explode("\n", $product['policy']);
                $totalPolicies = count(array_filter($policies, fn($p) => !empty(trim($p))));
                $visibleCount = 2;
                $currentCount = 0;

                foreach ($policies as $policy):
                    if (!empty(trim($policy))):
                        $currentCount++;
                ?>
                    <li class="mb-2 <?= $currentCount > $visibleCount ? 'hidden extra-policy' : '' ?>">
                        <i class="fa-regular fa-circle-check mr-2"></i> <?= htmlspecialchars($policy) ?>
                    </li>
                <?php 
                    endif;
                endforeach; 
                ?>
                <li>
                    <?php if ($totalPolicies > $visibleCount): ?>
                        <button id="toggle-policy" class="text-[#27f2f2] text-sm"><i class="fa fa-circle-arrow-down mr-2"></i> Xem thêm</button>
                    <?php endif; ?>                        
                </li>
            </ul>           
            <p class="mb-4">
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
            </p>
        </div>
    </div>

    <div class="mt-4 pt-4 mb-6 bg-gray-800 shadow-lg py-8 px-8 rounded-2xl text-white">
        <h2 class="text-2xl font-semibold mb-3">CHI TIẾT</h2>
        <p class="leading-relaxed"><?= nl2br($product['content']) ?></p>
    </div>

    <div class="flex items-center gap-4 mb-8" data-aos="fade-up" data-aos-duration="1000">   
        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='.$url.'" class="relative flex items-center justify-center gap-3 w-52 h-16 rounded-lg bg-blue-600 text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/facebook.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
            <img src="assets/img/facebook.png" alt="Facebook" class="w-6 h-6">
            Chia sẻ Facebook
        </a>
        <button onclick="copyLink('<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://")?><?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>')" class="relative flex items-center justify-center gap-3 w-52 h-16 rounded-lg bg-red-500 text-white font-semibold hover:scale-105 transform transition-transform duration-500 overflow-hidden no-underline before:content-[''] before:absolute before:-top-4 before:-right-4 before:w-20 before:h-20 before:bg-[url('assets/img/copy.png')] before:bg-contain before:bg-no-repeat before:opacity-20 before:rotate-[30deg]">
            <img src="assets/img/copy.png" alt="Copy" class="w-6 h-6">
            Copy link
        </button>
    </div>

    <?php
    $stmtAnd = $conn->prepare("SELECT p.id as 'pid', s.* FROM subfolder as s JOIN `post`as p ON p.name = s.post WHERE s.post = ? AND s.id != ? ORDER BY RAND() LIMIT 8");
    $stmtAnd->bind_param("si", $productPost, $idp);
    $stmtAnd->execute();
    $resultAnd = $stmtAnd->get_result();
    $stmtAnd->close();
    if ($resultAnd && $resultAnd->num_rows > 0) {
        echo '<h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
            <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
                SẢN PHẨM LIÊN QUAN
            </div>
        </h1>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10" data-aos="fade-up" data-aos-duration="1400">';
        while ($productAnd = $resultAnd->fetch_assoc()) {
            $folderAnd = 'assets/upload/'.$productAnd['pid'].'/'.$productAnd['id'].'/';
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
                $imgFileAnd = '<img src="' . @$folderAnd.@$firstFileAnd . '" alt="' . htmlspecialchars($productAnd['name']) . '" class="w-full h-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">';
            } else {
                $imgFileAnd = '<span style="height: 180px;" class="w-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center"></span>';
            }
            echo '<a href="product.php?idp=' . htmlspecialchars($productAnd['id']) . '" 
                    class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200">
                    '.$imgFileAnd.'
                    <h2 class="text-[#27f2f2] text-center drop-shadow-[0_0_5px_#27f2f282] font-semibold mt-4 mb-4">' . htmlspecialchars($productAnd['name']) . ' - '.htmlspecialchars($productAnd['category']).'</h2>
                </a>';
        }
        echo '</div>';
    }

    // $stmtOrther = $conn->prepare("SELECT p.id as 'pid', s.* FROM subfolder as s JOIN `post`as p ON p.name = s.post WHERE s.id != ? ORDER BY RAND() LIMIT 8");
    // $stmtOrther->bind_param("i", $idp);
    // $stmtOrther->execute();
    // $resultOrther = $stmtOrther->get_result();
    // $stmtOrther->close();
    // if ($resultOrther && $resultOrther->num_rows > 0) { 
    //     echo '<h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
    //     data-aos="fade-up" data-aos-duration="800">
    //         <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
    //             SẢN PHẨM KHÁC
    //         </div>
    //     </h1>
    //     <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">';
    //     while ($productOrther = $resultOrther->fetch_assoc()) {
    //         $folderOrther = 'assets/upload/'.$productOrther['pid'].'/'.$productOrther['id'].'/';
    //         if (is_dir($folderOrther)) {
    //             $filesOrther = scandir($folderOrther);
    //         } else {
    //             $filesOrther = [];
    //         }
    //         $filesOrther = array_filter($filesOrther, function($fileOrther) use ($folderOrther) {
    //             return is_file($folderOrther . $fileOrther);
    //         });
    //         $firstFileOrther = reset($filesOrther); 
    //         if($firstFileOrther) {
    //             $imgFileOrther = '<img src="' . @$folderOrther.@$firstFileOrther . '" alt="' . htmlspecialchars($productAnd['name']) . '" class="w-full h-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">';
    //         } else {
    //             $imgFileOrther = '<span style="height: 180px;" class="w-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center"></span>';
    //         }
    //         echo '
    //             <a href="product.php?idp=' . htmlspecialchars($productOrther['id']) . '" 
    //                 class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200">
    //                 '.$imgFileOrther.'
    //                 <h2 class="text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] font-semibold mt-4 mb-4">' . htmlspecialchars($productOrther['name']) . ' - '.htmlspecialchars($productOrther['post']).' '.htmlspecialchars($productOrther['category']).'</h2>
    //             </a>';
    //     }
    //     echo '</div>';
    // }        
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

    document.getElementById('toggle-policy')?.addEventListener('click', function() {
        const extraItems = document.querySelectorAll('.extra-policy');
        const isHidden = extraItems[0]?.classList.contains('hidden');
        extraItems.forEach(item => item.classList.toggle('hidden'));
        this.innerHTML = isHidden ? '<i class="fa fa-circle-arrow-up mr-2"></i> Thu gọn' : '<i class="fa fa-circle-arrow-down mr-2"></i> Xem thêm';
    });

    const thumbnailElements = document.querySelectorAll('img.thumbnail');
    let currentIndex = 0;
    let isHovered = false;
    const container = document.querySelector('.thumbnail-container');
    container.addEventListener('pointerenter', () => {
        isHovered = true;
    });
    container.addEventListener('pointerleave', () => {
        isHovered = false;
    });
    function clickThumbnails() {
        if (thumbnailElements.length > 0) {
            if (!isHovered) {
                if (currentIndex < thumbnailElements.length) {
                    thumbnailElements[currentIndex].click();
                    currentIndex++;
                } else {
                    currentIndex = 0;
                }
            }
            setTimeout(clickThumbnails, 2000);
        }
    }
    clickThumbnails();
</script>
<?php
require('system/foot.php');
?>
