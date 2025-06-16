<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
  <meta name="robots" content="index, follow">
  <link rel="shortcut icon" href="assets/img/favicon.ico">
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="assets/img/logo.png">
  <meta property="og:locale" content="vi_VN">
  <meta property="og:site_name" content="<?= htmlspecialchars($title) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
  <meta name="twitter:image" content="assets/img/logo.png">  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>  
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'K2D', sans-serif !important;
      user-select: none;
      -webkit-user-drag: none;      
    }

    a, img {
      -webkit-user-drag: none;
    }  

    @keyframes shining-effect {
      0% {
        filter: brightness(1);
        transform: scale(1);
      }
      33% {
        filter: brightness(2);
        transform: scale(1.09);
      }
      66% {
        filter: brightness(2);
        transform: scale(1.05);
      }
      100% {
        filter: brightness(1);
        transform: scale(1);
      }
    }

    .logo-effect {
      position: relative;
      display: inline-block;
      animation: shining-effect 2s infinite ease-in-out;
    }

    @keyframes ring-rotate {
      0% { transform: rotate(0deg); }
      10% { transform: rotate(15deg); }
      20% { transform: rotate(-10deg); }
      30% { transform: rotate(10deg); }
      40% { transform: rotate(-5deg); }
      50% { transform: rotate(5deg); }
      60% { transform: rotate(0deg); }
      100% { transform: rotate(0deg); }
    }

    .ring-animation {
      animation: ring-rotate 1s infinite ease-in-out;
    }    

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    } 

    @keyframes border {
        0% {
            box-shadow: 0 0 5px 2px rgba(255, 223, 94, 0.8);
        }
        50% {
            box-shadow: 0 0 15px 6px rgba(255, 223, 94, 1);
        }
        100% {
            box-shadow: 0 0 5px 2px rgba(255, 223, 94, 0.8);
        }
    }

    .animate-border {
        animation: border 1s infinite;
    }
</style>  
</head>

<body class="bg-gray-900 text-white min-h-screen transition-colors duration-500">
  <header class="fixed top-0 left-0 z-50 w-full h-16 md:h-32 bg-gray-900 border-b-4 rounded-2xl rounded-t-none border-[#27f2f2] flex md:grid md:grid-cols-3 items-center justify-center md:justify-start px-6" style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center;">
    <button id="mobile-menu-toggle" class="text-white text-1xl md:hidden absolute left-4 top-1/2 transform -translate-y-1/2 focus:outline-none">
      <i class="fa fa-bars"></i>
    </button>

    <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="flex items-center space-x-3 justify-start">
      <img src="assets/img/logo.png" alt="Logo" class="logo-effect w-20 h-auto md:w-24">
    </a>

    <button id="mobile-search-toggle" class="text-white text-1xl md:hidden absolute right-4 top-1/2 transform -translate-y-1/2 focus:outline-none">
      <i class="fa fa-search"></i>
    </button>

    <div class="hidden md:flex flex-col justify-center items-center">
      <div class="relative w-full max-w-lg hover:scale-105 hover:shadow-lg transform transition-transform duration-500">
        <form action="search.php" method="GET">
          <input type="text" name="query" placeholder="Tìm kiếm sản phẩm..." autocomplete="off"
                class="w-full py-3 pl-5 pr-16 bg-gray-800 text-white placeholder-gray-400 border-none focus:outline-none rounded-2xl">
          <button type="submit" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-white">
            <i class="fa fa-search"></i>
          </button>
        </form>
      </div>

      <nav class="flex justify-center text-center flex-wrap space-x-6 mt-2 w-full max-w-lg">
        <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="text-gray-400 hover:text-[#27f2f2] transition-colors">Trang chủ</a>
        <?php
        $auth = isAuthenticated();
        if ($auth['status']) {
          echo '<a href="admin.php?action=post" class="text-gray-400 hover:text-[#27f2f2] transition-colors">Quản lý website</a>';
        } else {
          echo '<a href="login.php" class="text-gray-400 hover:text-[#27f2f2] transition-colors">Đăng nhập</a>';
        }
        ?>
        <a href="policy.php" class="text-gray-400 hover:text-[#27f2f2] transition-colors">Chính sách bảo hành</a>
        <a href="tel:<?= htmlspecialchars($phone) ?>" class="text-gray-400 hover:text-[#27f2f2] transition-colors">Liên hệ</a>
      </nav>
    </div>

    <a href="tel:<?= htmlspecialchars($phone) ?>" class="hidden md:flex items-center justify-end space-x-3">
      <img src="assets/img/call.png" alt="Hotline Icon" class="w-10 h-10 ring-animation">
      <div class="text-white">
        <p class="text-sm uppercase">Hotline tư vấn</p>
        <p class="text-lg font-bold text-[#27f2f2]"><?= htmlspecialchars($phone) ?></p>
      </div>
    </a>

    <div id="mobile-search" class="fixed inset-0 bg-gray-900 hidden z-50 flex flex-col p-6 transition-transform duration-500 transform translate-x-full" style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center;">
      <button id="mobile-search-close" class="text-white text-1xl self-end mb-4 focus:outline-none transition-transform duration-300 hover:rotate-180">
        <i class="fa fa-close"></i>
      </button>
      <form action="search.php" method="GET" class="relative w-full">
        <input type="text" name="query" placeholder="Tìm kiếm sản phẩm..." autocomplete="off"
              class="w-full py-3 pl-5 pr-16 bg-gray-800 text-white placeholder-gray-400 border-none focus:outline-none rounded-2xl">
        <button type="submit" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-white">
          <i class="fa fa-search"></i>
        </button>
      </form>
    </div>

    <div id="mobile-menu" class="fixed inset-0 bg-gray-900 hidden z-50 flex flex-col items-center p-6 space-y-6 transition-transform duration-500 transform -translate-x-full" style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center;">
      <button id="mobile-menu-close" class="text-white text-1xl self-end mb-4 focus:outline-none transition-transform duration-300 hover:rotate-180">
        <i class="fa fa-close"></i>
      </button>
      <nav class="flex flex-col items-center space-y-4 text-white">
        <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>" class="hover:text-[#27f2f2] transition-colors">Trang chủ</a>
        <?php
        $auth = isAuthenticated();
        if ($auth['status']) {
          echo '<a href="admin.php?action=post" class="hover:text-[#27f2f2] transition-colors">Quản lý website</a>';
        } else {
          echo '<a href="login.php" class="hover:text-[#27f2f2] transition-colors">Đăng nhập</a>';
        }
        ?>
        <a href="policy.php" class="hover:text-[#27f2f2] transition-colors">Chính sách bảo hành</a>
        <a href="tel:<?= htmlspecialchars($phone) ?>" class="hover:text-[#27f2f2] transition-colors">Liên hệ</a>
        <?php
        if ($auth['status']) {
          echo '<a href="logout.php" class="hover:text-[#27f2f2] transition-colors">Đăng xuất</a>';
        }
        ?>
      </nav>
    </div>
  </header>
  <div class="pt-16 md:pt-32"></div>