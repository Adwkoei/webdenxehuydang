<?php
require('config.php');
require('dbinfo.php');
$conn = @mysqli_connect($sqlServer, $sqlUser, $sqlPass, $sqlDB);
if(!$conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sqlServer = $_POST['sqlServer'] ?? '';
        $sqlUser = $_POST['sqlUser'] ?? '';
        $sqlPass = $_POST['sqlPass'] ?? '';
        $sqlDB = $_POST['sqlDB'] ?? '';
    
        if (empty($sqlServer) || empty($sqlUser) || empty($sqlDB)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Vui lòng điền đầy đủ thông tin!'
            ]);
            exit();
        } else {
            $configContent = "<?php\n";
            $configContent .= "\$sqlServer = \"$sqlServer\";\n";
            $configContent .= "\$sqlUser = \"$sqlUser\";\n";
            $configContent .= "\$sqlPass = \"$sqlPass\";\n";
            $configContent .= "\$sqlDB = \"$sqlDB\";\n";
            $configContent .= "?>";
    
            if (file_put_contents('dbinfo.php', $configContent)) {
                $conn = @mysqli_connect($sqlServer, $sqlUser, $sqlPass, $sqlDB);
    
                if ($conn) {
                    $sqlFile = 'data.sql';
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);

                        $queries = explode(';', $sqlContent);
                        $queries = array_filter($queries, 'trim');
                        foreach ($queries as $query) {
                            if (!mysqli_query($conn, $query)) {
                                echo json_encode([
                                    'status' => 'success',
                                    'message' => 'Cấu hình cơ sở dữ liệu đã được lưu thành công!'
                                ]);
                                exit();
                            }
                        }

                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Cấu hình cơ sở dữ liệu đã được lưu thành công và dữ liệu đã được nạp vào cơ sở dữ liệu!'
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Không tìm thấy file SQL (data.sql)!'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Lỗi khi kết nối đến cơ sở dữ liệu! Vui lòng kiểm tra lại thông tin.'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Lỗi khi lưu cấu hình!'
                ]);
            }
        }
        exit();
    }
  echo '
  <!DOCTYPE html>
  <html lang="vi">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Thiết lập Cơ sở Dữ liệu</title>
      <style>
          * {
              margin: 0;
              padding: 0;
              box-sizing: border-box;
          }
          
          body {
              font-family: "Arial", sans-serif;
              background-color: #f4f7fc;
              display: flex;
              justify-content: center;
              align-items: center;
              height: 100vh;
              margin: 0;
          }

          .form-container {
              background-color: #fff;
              border-radius: 8px;
              box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
              padding: 30px;
              width: 100%;
              max-width: 400px;
              text-align: center;
          }

          h2 {
              margin-bottom: 20px;
              font-size: 24px;
              font-weight: 600;
              color: #333;
          }

          p {
              margin-top: 20px;
              font-size: 12px;
              font-weight: 600;
              color: #333;
          }

          label {
              font-size: 14px;
              font-weight: 500;
              color: #333;
              text-align: left;
              display: block;
              margin-bottom: 8px;
          }

          input {
              width: 100%;
              padding: 12px;
              margin-bottom: 20px;
              border-radius: 6px;
              border: 1px solid #ccc;
              font-size: 16px;
              color: #333;
              transition: border 0.3s ease;
          }

          input:focus {
              border-color: #4CAF50;
              outline: none;
          }

          button {
              width: 100%;
              padding: 12px;
              background-color: #4CAF50;
              color: white;
              font-size: 16px;
              border: none;
              border-radius: 6px;
              cursor: pointer;
              transition: background-color 0.3s ease;
          }

          button:hover {
              background-color: #45a049;
          }

          .message {
              font-size: 16px;
              margin: 10px 0;
          }

          .error {
              color: red;
          }

          .success {
              color: green;
          }
      </style>
  </head>
  <body>
    <div class="form-container">
        <h2>THIẾT LẬP CƠ SỞ DỮ LIỆU</h2>
        <div id="message" class="message"></div>  
        <form id="dbconfig-form">
            <label for="sqlServer">Máy chủ SQL</label>
            <input type="text" name="sqlServer" id="sqlServer" placeholder="Nhập máy chủ SQL" autocomplete="off">

            <label for="sqlUser">Tên người dùng</label>
            <input type="text" name="sqlUser" id="sqlUser" placeholder="Nhập tên người dùng" autocomplete="off">

            <label for="sqlPass">Mật khẩu</label>
            <input type="password" name="sqlPass" id="sqlPass" placeholder="Nhập mật khẩu" autocomplete="off">

            <label for="sqlDB">Cơ sở dữ liệu</label>
            <input type="text" name="sqlDB" id="sqlDB" placeholder="Nhập tên cơ sở dữ liệu" autocomplete="off">

            <button type="submit">Lưu cấu hình</button>          
        </form>
        <p>Source được phát triển bởi: <a target="_blank" href="https://taphoammo.net/gian-hang/dich-vu-viet-tool-thiet-ke-website-banner-logo-theo-yeu-cau-gia-hat-re_939353" class="text-warning text-decoration-none fw-bold">MINHTAM6868</a></p>
    </div>
  </body>
  <script>
  document.getElementById("dbconfig-form").addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch("system/dbconfig.php", {
          method: "POST",
          body: formData
      })
      .then(response => response.json())
      .then(result => {
          const messageElement = document.getElementById("message");
          if (result.status === "success") {
              messageElement.innerHTML = `<div class="success">${result.message}</div>`;
              setTimeout(() => {
                location.reload()
              }, 2000);              
          } else {
              messageElement.innerHTML = `<div class="error">${result.message}</div>`;
          }
      })
      .catch(error => {
          const messageElement = document.getElementById("message");
          messageElement.innerHTML = `<div class="error">Có lỗi xảy ra. Vui lòng thử lại.</div>`;
      });
  });
  </script>  
  </html>  
  ';
  exit();
}
function isAuthenticated() {
  global $adminUser, $adminPass;
  $username = null;
  $password = null;
  $token = $_COOKIE['auth_token'] ?? '';

  $expectedToken = hash('sha256', $adminUser . $adminPass);

  if ($token === $expectedToken) {
      return ['status' => true, 'username' => $adminUser, 'password' => $adminPass];
  } else {
      return ['status' => false, 'username' => null, 'password' => null];
  }
}
if ($maintenance === "0") {
  die("");
}
?>
