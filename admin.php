<?php
require('system/dbconfig.php');

$auth = isAuthenticated();
if (!$auth['status']) {
    header('Location: login.php');
    exit();
}

$action = $_GET['action'] ?? 'dashboard';

// Xử lý các action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Có lỗi xảy ra'];
    
    switch ($action) {
        case 'add-category':
            $name = trim($_POST['name'] ?? '');
            $short_name = trim($_POST['short_name'] ?? '');
            
            if (empty($name) || empty($short_name)) {
                $response['message'] = 'Vui lòng điền đầy đủ thông tin';
            } else {
                $slug = createSlug($short_name);
                $stmt = $conn->prepare("INSERT INTO categories (name, short_name, slug) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $short_name, $slug);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Thêm danh mục thành công'];
                }
                $stmt->close();
            }
            break;
            
        case 'add-product':
            $name = trim($_POST['name'] ?? '');
            $short_name = trim($_POST['short_name'] ?? '');
            $category_id = $_POST['category_id'] ?? null;
            $youtube = trim($_POST['youtube'] ?? '');
            $tiktok = trim($_POST['tiktok'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($name) || empty($short_name)) {
                $response['message'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
            } else {
                // Tạo mã sản phẩm tự động
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
                $stmt->execute();
                $result = $stmt->get_result();
                $count = $result->fetch_assoc()['count'];
                $product_code = 'SP' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
                $stmt->close();
                
                $slug = createSlug($short_name);
                
                // Kiểm tra slug trùng lặp
                $checkStmt = $conn->prepare("SELECT id FROM products WHERE slug = ?");
                $checkStmt->bind_param("s", $slug);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows > 0) {
                    $slug .= '-' . time();
                }
                $checkStmt->close();
                
                $stmt = $conn->prepare("INSERT INTO products (product_code, name, short_name, slug, category_id, youtube, tiktok, content) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssisss", $product_code, $name, $short_name, $slug, $category_id, $youtube, $tiktok, $content);
                
                if ($stmt->execute()) {
                    $product_id = $conn->insert_id;
                    
                    // Tạo thư mục upload
                    $upload_dir = "assets/upload/$product_code";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $response = ['status' => 'success', 'message' => 'Thêm sản phẩm thành công', 'product_code' => $product_code];
                }
                $stmt->close();
            }
            break;
            
        case 'update-policy':
            $policy = trim($_POST['policy'] ?? '');
            if (empty($policy)) {
                $response['message'] = 'Nội dung chính sách không được để trống';
            } else {
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'warranty_policy'");
                $stmt->bind_param("s", $policy);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Cập nhật chính sách thành công'];
                }
                $stmt->close();
            }
            break;
    }
    
    echo json_encode($response);
    exit();
}

// Xử lý xóa
if ($action === 'delete-category' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin.php?action=categories');
    exit();
}

if ($action === 'delete-product' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin sản phẩm để xóa thư mục
    $stmt = $conn->prepare("SELECT product_code FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        // Xóa thư mục upload
        $upload_dir = "assets/upload/{$product['product_code']}";
        if (is_dir($upload_dir)) {
            deleteDirectory($upload_dir);
        }
        
        // Xóa sản phẩm khỏi database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header('Location: admin.php?action=products');
    exit();
}

// Hàm tạo slug
function createSlug($string) {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');
    
    // Chuyển đổi ký tự có dấu thành không dấu
    $vietnamese = array(
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ',
        'đ'
    );
    
    $english = array(
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd'
    );
    
    $string = str_replace($vietnamese, $english, $string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}

// Hàm xóa thư mục
function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

require('system/head.php');
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282]">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            QUẢN TRỊ WEBSITE
        </div>
    </h1>

    <!-- Menu điều hướng -->
    <div class="bg-gray-800 rounded-2xl p-6 mb-8">
        <nav class="flex flex-wrap justify-center gap-4">
            <a href="admin.php?action=dashboard" class="px-4 py-2 rounded-lg <?= $action === 'dashboard' ? 'bg-[#27f2f2] text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600' ?> transition">Dashboard</a>
            <a href="admin.php?action=categories" class="px-4 py-2 rounded-lg <?= $action === 'categories' ? 'bg-[#27f2f2] text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600' ?> transition">Danh mục</a>
            <a href="admin.php?action=products" class="px-4 py-2 rounded-lg <?= $action === 'products' ? 'bg-[#27f2f2] text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600' ?> transition">Sản phẩm</a>
            <a href="admin.php?action=policy" class="px-4 py-2 rounded-lg <?= $action === 'policy' ? 'bg-[#27f2f2] text-gray-900' : 'bg-gray-700 text-white hover:bg-gray-600' ?> transition">Chính sách</a>
            <a href="logout.php" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">Đăng xuất</a>
        </nav>
    </div>

    <?php if ($action === 'dashboard'): ?>
        <!-- Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            // Thống kê
            $categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
            $productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
            $totalViews = $conn->query("SELECT SUM(view_count) as total FROM products")->fetch_assoc()['total'] ?? 0;
            ?>
            
            <div class="bg-gray-800 rounded-2xl p-6 text-center">
                <i class="fas fa-folder text-4xl text-[#27f2f2] mb-4"></i>
                <h3 class="text-2xl font-bold text-white mb-2"><?= $categoryCount ?></h3>
                <p class="text-gray-400">Danh mục</p>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-6 text-center">
                <i class="fas fa-box text-4xl text-[#27f2f2] mb-4"></i>
                <h3 class="text-2xl font-bold text-white mb-2"><?= $productCount ?></h3>
                <p class="text-gray-400">Sản phẩm</p>
            </div>
            
            <div class="bg-gray-800 rounded-2xl p-6 text-center">
                <i class="fas fa-eye text-4xl text-[#27f2f2] mb-4"></i>
                <h3 class="text-2xl font-bold text-white mb-2"><?= number_format($totalViews) ?></h3>
                <p class="text-gray-400">Lượt xem</p>
            </div>
        </div>

        <!-- Sản phẩm mới nhất -->
        <div class="mt-8 bg-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-bold text-[#27f2f2] mb-4">Sản phẩm mới nhất</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-2">Mã SP</th>
                            <th class="text-left py-2">Tên sản phẩm</th>
                            <th class="text-left py-2">Danh mục</th>
                            <th class="text-left py-2">Lượt xem</th>
                            <th class="text-left py-2">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, c.short_name as category_name FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                ORDER BY p.created_at DESC LIMIT 5";
                        $result = $conn->query($sql);
                        while ($product = $result->fetch_assoc()):
                        ?>
                        <tr class="border-b border-gray-700">
                            <td class="py-2"><?= htmlspecialchars($product['product_code']) ?></td>
                            <td class="py-2"><?= htmlspecialchars($product['short_name']) ?></td>
                            <td class="py-2"><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?></td>
                            <td class="py-2"><?= number_format($product['view_count']) ?></td>
                            <td class="py-2"><?= date('d/m/Y', strtotime($product['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($action === 'categories'): ?>
        <!-- Quản lý danh mục -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form thêm danh mục -->
            <div class="bg-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-bold text-[#27f2f2] mb-4">Thêm danh mục mới</h2>
                <form id="add-category-form" class="space-y-4">
                    <div>
                        <label class="block text-white mb-2">Tên đầy đủ *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-[#27f2f2] focus:outline-none" placeholder="VD: Đèn pha bi cầu cao cấp">
                    </div>
                    <div>
                        <label class="block text-white mb-2">Tên rút gọn *</label>
                        <input type="text" name="short_name" required class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-[#27f2f2] focus:outline-none" placeholder="VD: Bi cầu">
                    </div>
                    <button type="submit" class="w-full bg-[#27f2f2] text-gray-900 py-2 rounded-lg font-semibold hover:bg-cyan-400 transition">Thêm danh mục</button>
                </form>
            </div>

            <!-- Danh sách danh mục -->
            <div class="bg-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-bold text-[#27f2f2] mb-4">Danh sách danh mục</h2>
                <div class="space-y-3">
                    <?php
                    $sql = "SELECT c.*, COUNT(p.id) as product_count FROM categories c 
                            LEFT JOIN products p ON c.id = p.category_id 
                            GROUP BY c.id ORDER BY c.created_at DESC";
                    $result = $conn->query($sql);
                    while ($category = $result->fetch_assoc()):
                    ?>
                    <div class="flex items-center justify-between bg-gray-700 p-4 rounded-lg">
                        <div>
                            <h3 class="text-white font-semibold"><?= htmlspecialchars($category['short_name']) ?></h3>
                            <p class="text-gray-400 text-sm"><?= htmlspecialchars($category['name']) ?></p>
                            <p class="text-gray-500 text-xs"><?= $category['product_count'] ?> sản phẩm</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="category.php?slug=<?= $category['slug'] ?>" class="text-blue-400 hover:text-blue-300" title="Xem danh mục">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="admin.php?action=delete-category&id=<?= $category['id'] ?>" 
                               onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')" 
                               class="text-red-400 hover:text-red-300" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    <?php elseif ($action === 'products'): ?>
        <!-- Quản lý sản phẩm -->
        <div class="space-y-8">
            <!-- Form thêm sản phẩm -->
            <div class="bg-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-bold text-[#27f2f2] mb-4">Thêm sản phẩm mới</h2>
                <form id="add-product-form" class="space-y-6">
                    <!-- Thông tin cơ bản -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-white mb-2 font-semibold">
                                <i class="fas fa-tag text-[#27f2f2] mr-2"></i>Tên đầy đủ sản phẩm *
                            </label>
                            <textarea name="name" required rows="3" class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-[#27f2f2] focus:outline-none resize-none transition-colors" placeholder="VD: Đèn pha bi cầu LED cao cấp cho xe Wave 2020-2024 với công nghệ chiếu sáng hiện đại"></textarea>
                        </div>
                        <div>
                            <label class="block text-white mb-2 font-semibold">
                                <i class="fas fa-edit text-[#27f2f2] mr-2"></i>Tên rút gọn *
                            </label>
                            <input type="text" name="short_name" required class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-[#27f2f2] focus:outline-none transition-colors" placeholder="VD: Bi cầu Wave">
                        </div>
                    </div>

                    <!-- Phân loại -->
                    <div>
                        <label class="block text-white mb-2 font-semibold">
                            <i class="fas fa-folder text-[#27f2f2] mr-2"></i>Danh mục sản phẩm
                        </label>
                        <select name="category_id" class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-[#27f2f2] focus:outline-none transition-colors">
                            <option value="">Chọn danh mục (tùy chọn)</option>
                            <?php
                            $categories = $conn->query("SELECT * FROM categories ORDER BY short_name");
                            while ($cat = $categories->fetch_assoc()):
                            ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['short_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Liên kết video -->
                    <div class="bg-gray-700 rounded-lg p-4">
                        <h3 class="text-white font-semibold mb-4">
                            <i class="fas fa-video text-[#27f2f2] mr-2"></i>Liên kết video (tùy chọn)
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-300 mb-2">
                                    <i class="fab fa-youtube text-red-500 mr-2"></i>Link YouTube
                                </label>
                                <input type="url" name="youtube" class="w-full px-4 py-3 rounded-lg bg-gray-600 text-white border border-gray-500 focus:border-[#27f2f2] focus:outline-none transition-colors" placeholder="https://youtube.com/...">
                            </div>
                            <div>
                                <label class="block text-gray-300 mb-2">
                                    <i class="fab fa-tiktok text-pink-500 mr-2"></i>Link TikTok
                                </label>
                                <input type="url" name="tiktok" class="w-full px-4 py-3 rounded-lg bg-gray-600 text-white border border-gray-500 focus:border-[#27f2f2] focus:outline-none transition-colors" placeholder="https://tiktok.com/...">
                            </div>
                        </div>
                    </div>

                    <!-- Mô tả chi tiết (không bắt buộc) -->
                    <div class="bg-gray-700 rounded-lg p-4">
                        <h3 class="text-white font-semibold mb-2">
                            <i class="fas fa-align-left text-[#27f2f2] mr-2"></i>Mô tả chi tiết (tùy chọn)
                        </h3>
                        <p class="text-gray-400 text-sm mb-3">Bạn có thể bỏ trống phần này và thêm mô tả sau</p>
                        <textarea name="content" rows="4" class="w-full px-4 py-3 rounded-lg bg-gray-600 text-white border border-gray-500 focus:border-[#27f2f2] focus:outline-none resize-none transition-colors" placeholder="Mô tả chi tiết về sản phẩm, tính năng, ưu điểm..."></textarea>
                    </div>

                    <!-- Nút submit -->
                    <div class="flex justify-center">
                        <button type="submit" class="bg-gradient-to-r from-[#27f2f2] to-cyan-400 text-gray-900 px-8 py-4 rounded-lg font-bold text-lg hover:from-cyan-400 hover:to-[#27f2f2] transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-plus mr-2"></i>Thêm sản phẩm mới
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danh sách sản phẩm -->
            <div class="bg-gray-800 rounded-2xl p-6">
                <h2 class="text-xl font-bold text-[#27f2f2] mb-4">Danh sách sản phẩm</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-white">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left py-2">Mã SP</th>
                                <th class="text-left py-2">Tên rút gọn</th>
                                <th class="text-left py-2">Danh mục</th>
                                <th class="text-left py-2">Lượt xem</th>
                                <th class="text-left py-2">Ngày tạo</th>
                                <th class="text-left py-2">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, c.short_name as category_name FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    ORDER BY p.created_at DESC";
                            $result = $conn->query($sql);
                            while ($product = $result->fetch_assoc()):
                            ?>
                            <tr class="border-b border-gray-700">
                                <td class="py-2 font-mono"><?= htmlspecialchars($product['product_code']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($product['short_name']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?></td>
                                <td class="py-2"><?= number_format($product['view_count']) ?></td>
                                <td class="py-2"><?= date('d/m/Y', strtotime($product['created_at'])) ?></td>
                                <td class="py-2">
                                    <div class="flex space-x-2">
                                        <a href="product.php?slug=<?= $product['slug'] ?>" class="text-blue-400 hover:text-blue-300" title="Xem sản phẩm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="admin.php?action=delete-product&id=<?= $product['id'] ?>" 
                                           onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" 
                                           class="text-red-400 hover:text-red-300" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($action === 'policy'): ?>
        <!-- Quản lý chính sách -->
        <div class="bg-gray-800 rounded-2xl p-6">
            <h2 class="text-xl font-bold text-[#27f2f2] mb-4">Chỉnh sửa chính sách bảo hành</h2>
            <?php
            $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'warranty_policy'");
            $stmt->execute();
            $result = $stmt->get_result();
            $policyData = $result->fetch_assoc();
            $stmt->close();
            $currentPolicy = $policyData ? $policyData['setting_value'] : '';
            ?>
            <form id="update-policy-form">
                <div class="mb-4">
                    <label class="block text-white mb-2">Nội dung chính sách bảo hành</label>
                    <textarea name="policy" rows="20" required class="w-full px-4 py-2 rounded-lg bg-gray-700 text-white border border-gray-600 focus:border-[#27f2f2] focus:outline-none resize-none"><?= htmlspecialchars($currentPolicy) ?></textarea>
                    <p class="text-gray-400 text-sm mt-2">Sử dụng Enter để xuống dòng. Nội dung sẽ được hiển thị trên trang chính sách bảo hành.</p>
                </div>
                <button type="submit" class="bg-[#27f2f2] text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-cyan-400 transition">Cập nhật chính sách</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
// Form handlers
document.getElementById('add-category-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php?action=add-category', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: result.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi xử lý yêu cầu'
        });
    }
});

document.getElementById('add-product-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php?action=add-product', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: result.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi xử lý yêu cầu'
        });
    }
});

document.getElementById('update-policy-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php?action=update-policy', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: result.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi xử lý yêu cầu'
        });
    }
});

// Auto-generate short name from full name
document.querySelector('input[name="name"]')?.addEventListener('input', function() {
    const shortNameField = document.querySelector('input[name="short_name"]');
    if (shortNameField && !shortNameField.value) {
        let shortName = this.value;
        // Simple logic to create short name
        shortName = shortName.replace(/đèn pha |đèn |cho xe |cao cấp|chính hãng|hệ thống/gi, '');
        shortName = shortName.split(' ').slice(0, 2).join(' ');
        shortNameField.value = shortName;
    }
});

document.querySelector('textarea[name="name"]')?.addEventListener('input', function() {
    const shortNameField = document.querySelector('input[name="short_name"]');
    if (shortNameField && !shortNameField.value) {
        let shortName = this.value;
        // Simple logic to create short name
        shortName = shortName.replace(/đèn pha |đèn |cho xe |cao cấp|chính hãng|hệ thống/gi, '');
        shortName = shortName.split(' ').slice(0, 2).join(' ');
        shortNameField.value = shortName;
    }
});
</script>

<?php
require('system/foot.php');
?>