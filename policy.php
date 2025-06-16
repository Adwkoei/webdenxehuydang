<?php
require('system/dbconfig.php');

// Lấy chính sách bảo hành từ database
$stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'warranty_policy'");
$stmt->execute();
$result = $stmt->get_result();
$policyData = $result->fetch_assoc();
$stmt->close();

$warrantyPolicy = $policyData ? $policyData['setting_value'] : 'Chính sách bảo hành đang được cập nhật.';

require('system/head.php');
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            CHÍNH SÁCH BẢO HÀNH
        </div>
    </h1>

    <div class="bg-gray-800 rounded-2xl p-8 shadow-lg">
        <div class="prose prose-invert max-w-none">
            <?= nl2br(htmlspecialchars($warrantyPolicy)) ?>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-700">
            <h3 class="text-xl font-semibold mb-4 text-[#27f2f2]">Liên Hệ Hỗ Trợ</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-phone text-[#27f2f2] text-xl"></i>
                    <div>
                        <p class="text-sm text-gray-400">Hotline</p>
                        <a href="tel:<?= htmlspecialchars($phone) ?>" class="text-white hover:text-[#27f2f2] font-semibold"><?= htmlspecialchars($phone) ?></a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-envelope text-[#27f2f2] text-xl"></i>
                    <div>
                        <p class="text-sm text-gray-400">Email</p>
                        <a href="mailto:<?= htmlspecialchars($email) ?>" class="text-white hover:text-[#27f2f2] font-semibold"><?= htmlspecialchars($email) ?></a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-map-marker-alt text-[#27f2f2] text-xl"></i>
                    <div>
                        <p class="text-sm text-gray-400">Địa chỉ</p>
                        <p class="text-white"><?= htmlspecialchars($address) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require('system/foot.php');
?>