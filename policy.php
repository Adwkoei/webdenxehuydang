<?php
require('system/dbconfig.php');
require('system/head.php');
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            CHÍNH SÁCH BẢO HÀNH
        </div>
    </h1>

    
    <section class="mb-10">
        <h2 class="text-2xl font-semibold mb-4">1. Thời Gian Bảo Hành</h2>
        <p class="leading-relaxed">
            - Tất cả sản phẩm được bảo hành trong vòng <strong>12 tháng</strong> kể từ ngày mua hàng.<br>
            - Thời gian bảo hành có thể khác nhau tùy thuộc vào từng sản phẩm cụ thể.
        </p>
    </section>

    <section class="mb-10">
        <h2 class="text-2xl font-semibold mb-4">2. Điều Kiện Bảo Hành</h2>
        <ul class="list-disc pl-5 leading-relaxed">
            <li>Sản phẩm bị lỗi do nhà sản xuất.</li>
            <li>Sản phẩm còn trong thời hạn bảo hành và có hóa đơn mua hàng hợp lệ.</li>
            <li>Tem bảo hành phải còn nguyên vẹn, không bị rách hoặc chỉnh sửa.</li>
        </ul>
    </section>

    <section class="mb-10">
        <h2 class="text-2xl font-semibold mb-4">3. Những Trường Hợp Không Được Bảo Hành</h2>
        <ul class="list-disc pl-5 leading-relaxed">
            <li>Sản phẩm bị hư hỏng do sử dụng sai cách, va đập, rơi vỡ hoặc tác động từ bên ngoài.</li>
            <li>Sản phẩm bị thay đổi, sửa chữa không được ủy quyền từ chúng tôi.</li>
            <li>Sản phẩm bị hư hỏng do thiên tai như lũ lụt, hỏa hoạn, sét đánh...</li>
        </ul>
    </section>

    <section class="mb-10">
        <h2 class="text-2xl font-semibold mb-4">4. Quy Trình Bảo Hành</h2>
        <ol class="list-decimal pl-5 leading-relaxed">
            <li>Liên hệ bộ phận hỗ trợ khách hàng qua hotline hoặc email để đăng ký bảo hành.</li>
            <li>Gửi sản phẩm về trung tâm bảo hành cùng với hóa đơn mua hàng.</li>
            <li>Chúng tôi sẽ kiểm tra và thông báo kết quả trong vòng 7 ngày làm việc.</li>
        </ol>
    </section>

    <section class="mb-10">
        <h2 class="text-2xl font-semibold mb-4">5. Thời Gian Xử Lý Bảo Hành</h2>
        <p class="leading-relaxed">
            - Thời gian xử lý bảo hành thông thường là <strong>7-14 ngày làm việc</strong> kể từ khi nhận sản phẩm.<br>
            - Trong một số trường hợp đặc biệt, thời gian có thể kéo dài và sẽ được thông báo cụ thể.
        </p>
    </section>

    <section class="mb-10">
        <h2 class="text-2xl font-semibold mb-4">6. Liên Hệ</h2>
        <p class="leading-relaxed">
            - Hotline: <a href="tel:<?= htmlspecialchars($phone) ?>" class="text-[#27f2f2] hover:underline"><?= htmlspecialchars($phone) ?></a><br>
            - Email: <a href="mailto:support@tencongty.vn" class="text-[#27f2f2] hover:underline"><?= htmlspecialchars($email) ?></a><br>
            - Địa chỉ: <?= htmlspecialchars($address) ?>.
        </p>
    </section>
</div>

<?php
require('system/foot.php');
?>
