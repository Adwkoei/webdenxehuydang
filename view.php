<?php

require('system/dbconfig.php');

$cate = @$_GET['cate'];
$sub = @$_GET['sub'];

$stmt = $conn->prepare("SELECT p.id as 'pid',c.id as 'cid', s.* FROM subfolder as s JOIN `post`as p ON p.name = s.post JOIN `category`as c ON (c.name = s.category AND c.post = s.post) WHERE s.post = ? AND s.category = ?");
$stmt->bind_param("is", $cate, $sub);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

require('system/head.php');
$thumps=[];
foreach($products as $k=>$product){
    $pid=$product['pid'];
    $id=$product['id'];
    $folder = 'assets/upload/'.$pid.'/'.$id.'/';
    if (is_dir($folder)) {
        $files = scandir($folder);
    } else {
        $files = [];
    }
    $files = array_filter($files, function($file) use ($folder) {
        return is_file($folder . $file);
    });
    $firstFile = reset($files);
    if($firstFile){
        $thumps[$k]=$folder.$firstFile;
    }else{
        $thumps[$k]=null;
    }
}
?>

<main class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            <?=@$sub?>
        </div>
    </h1>
    <?php
    if (($products)!=null) {
        echo '<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">';
        foreach($products as $k=>$product){
            if(@$thumps[$k]) {
                $imgFileHtml = '<img src="' . $thumps[$k] . '" alt="' . htmlspecialchars($product['name']) . '" class="w-full h-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">';
            } else {
                $imgFileHtml = '<span style="height: 180px;" class="w-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center"></span>';
            }
            echo '<a href="product.php?idp=' . htmlspecialchars($product['id']) . '" 
                    class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200">
                    '.$imgFileHtml.'
                    <h2 class="text-[#27f2f2] text-center drop-shadow-[0_0_5px_#27f2f282] font-semibold mt-4 mb-4">' . htmlspecialchars($product['name']).' '.@$sub.'</h2>
                </a>';
        }
        echo '</div>';
    } else {
        echo '<p class="text-red-500 text-center">Không có sản phẩm nào được tìm thấy.</p>';
    }
    ?>
</main>

<?php
require('system/foot.php');
?>
