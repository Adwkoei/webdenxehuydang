<div class="pt-0 md:pt-20"></div>
<footer class="bg-gray-900 text-white pt-10 border-t-4 rounded-2xl border-[#27f2f2]">
    <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-8">

        <!-- Cột 1: Logo + Giới thiệu -->
        <div class="md:col-span-2">
            <div class="flex items-center justify-center md:justify-start space-x-3">
                <img src="assets/img/logo.png" alt="Logo Huy Đặng" class="logo-effect" style="height: 6rem;width: 6rem;">
                <h3 class="text-[#27f2f2] text-3xl font-bold uppercase hidden md:block">Huy Đặng Auto Light</h3>
            </div>
            <p class="text-gray-400 mt-4 leading-relaxed">
                <?= htmlspecialchars($description) ?>
            </p>
            <p class="mt-4"><i class="fa-solid fa-phone text-[#27f2f2] mr-2"></i> <?= htmlspecialchars($phone) ?></p>
            <p class="mt-2"><i class="fa-solid fa-envelope text-[#27f2f2] mr-2"></i> <?= htmlspecialchars($email) ?></p>
        </div>

        <!-- Cột 2: Liên kết nhanh -->
        <div>
            <h3 class="text-lg font-semibold mb-3 text-[#27f2f2] uppercase">Liên kết</h3>
            <ul class="space-y-3 text-gray-400">
                <li><a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="hover:text-[#27f2f2] transition">Trang chủ</a></li>
                <li><a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST'].'/policy.php'?>" class="hover:text-[#27f2f2] transition">Chính sách bảo hành</a></li>
                <li><a href="tel:<?= htmlspecialchars($phone) ?>" class="hover:text-[#27f2f2] transition">Liên hệ</a></li>
            </ul>
        </div>

        <!-- Cột 3: Mạng xã hội -->
        <div>
            <h3 class="text-lg font-semibold mb-3 text-[#27f2f2] uppercase">Kết nối với chúng tôi</h3>
            <div class="flex space-x-4">
                <a href="<?=$facebook?>" class="text-gray-400 hover:text-[#27f2f2] text-2xl transition"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="<?=$youtube?>" class="text-gray-400 hover:text-[#27f2f2] text-2xl transition"><i class="fa-brands fa-youtube"></i></a>
                <a href="<?=$tiktok?>" class="text-gray-400 hover:text-[#27f2f2] text-2xl transition"><i class="fa-brands fa-tiktok"></i></a>
            </div>
        </div>
    </div>

    <!-- Dòng bản quyền -->
    <div class="border-t border-gray-700 mt-10 pt-3 pb-3 text-center text-gray-400" style="background-image: url('assets/img/header-bg.jpg'); background-size: cover; background-position: center;">
        &copy; 2025 <span class="text-[#27f2f2] font-semibold">Đèn Xe Huy Đặng</span>. All rights reserved.
    </div>
</footer>

</body>
<script>
  AOS.init();

    window.addEventListener('scroll', function() {
        const scroll = document.getElementById('scroll');
        if (window.scrollY > 0) {
            scroll.classList.remove('opacity-0', 'scale-75');
            scroll.classList.add('opacity-100', 'scale-100');
        } else {
            scroll.classList.remove('opacity-100', 'scale-100');
            scroll.classList.add('opacity-0', 'scale-75');
        }
    });

    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    const mobileSearch = document.getElementById('mobile-search');
    const mobileSearchToggle = document.getElementById('mobile-search-toggle');
    const mobileSearchClose = document.getElementById('mobile-search-close');

    mobileMenuToggle.addEventListener('click', () => {
    mobileMenu.classList.remove('hidden');
    setTimeout(() => mobileMenu.classList.remove('-translate-x-full'), 10);
    });

    mobileMenuClose.addEventListener('click', () => {
    mobileMenu.classList.add('-translate-x-full');
    setTimeout(() => mobileMenu.classList.add('hidden'), 500);
    });

    mobileSearchToggle.addEventListener('click', () => {
    mobileSearch.classList.remove('hidden');
    setTimeout(() => mobileSearch.classList.remove('translate-x-full'), 10);
    });

    mobileSearchClose.addEventListener('click', () => {
    mobileSearch.classList.add('translate-x-full');
    setTimeout(() => mobileSearch.classList.add('hidden'), 500);
    });

</script>
</html>