<?php
// 引入配置文件
$config_file = __DIR__ . '/login_config.php';
if (!file_exists($config_file)) {
    // 默认配置
    file_put_contents($config_file, "<?php\nreturn ['password'=>'123456','enabled'=>true];\n");
}
$config = include $config_file;

// 管理界面
if (isset($_GET['admin'])) {
    session_start();
    
    // 检查是否已登录
    if (!isset($_SESSION['passed'])) {
        if (isset($_POST['page_pwd']) && $_POST['page_pwd'] === $config['password']) {
            $_SESSION['passed'] = true;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>管理员登录</title></head><body style="background:#f5f5f5;"><div style="max-width:400px;margin:100px auto;padding:40px;background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);"><form method="post"><h2 style="margin-bottom:20px;">请输入管理密码</h2><input type="password" name="page_pwd" style="width:100%;padding:12px 10px;font-size:16px;border:1px solid #ddd;border-radius:6px;margin-bottom:18px;" autofocus><button type="submit" style="width:100%;padding:12px 0;background:#3a3f4b;color:#fff;border:none;border-radius:6px;font-size:16px;cursor:pointer;">登录</button></form></div></body></html>';
            exit;
        }
    }

    // 已登录后的管理界面
    if (isset($_POST['new_password'])) {
        $config['password'] = $_POST['new_password'];
        $config['enabled'] = isset($_POST['enabled']) ? true : false;
        file_put_contents($config_file, "<?php\nreturn ['password'=>'" . addslashes($config['password']) . "','enabled'=>" . ($config['enabled'] ? 'true' : 'false') . "];\n");
        echo "<script>alert('保存成功');location.href='index.php?admin=1';</script>";
        exit;
    }
    
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>管理密码保护</title></head><body style="background:#f5f5f5;"><div style="max-width:400px;margin:100px auto;padding:40px;background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);"><form method="post"><h2 style="margin-bottom:20px;">管理密码保护</h2><label>新密码：<input type="text" name="new_password" value="' . htmlspecialchars($config['password']) . '" style="width:100%;padding:12px 10px;font-size:16px;border:1px solid #ddd;border-radius:6px;margin-bottom:18px;"></label><br><label><input type="checkbox" name="enabled" ' . ($config['enabled'] ? 'checked' : '') . '> 启用密码访问</label><br><br><button type="submit" style="width:100%;padding:12px 0;background:#3a3f4b;color:#fff;border:none;border-radius:6px;font-size:16px;cursor:pointer;">保存</button></form></div></body></html>';
    exit;
}

// 密码保护开始
session_start();
if ($config['enabled']) {
    if (!isset($_SESSION['passed'])) {
        if (isset($_POST['page_pwd']) && $_POST['page_pwd'] === $config['password']) {
            $_SESSION['passed'] = true;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"/><title>请输入访问密码</title></head><body style="background:#f5f5f5;"><div style="max-width:400px;margin:100px auto;padding:40px;background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);"><form method="post"><h2 style="margin-bottom:20px;">请输入访问密码</h2><input type="password" name="page_pwd" style="width:100%;padding:12px 10px;font-size:16px;border:1px solid #ddd;border-radius:6px;margin-bottom:18px;" autofocus><button type="submit" style="width:100%;padding:12px 0;background:#3a3f4b;color:#fff;border:none;border-radius:6px;font-size:16px;cursor:pointer;">进入</button></form></div></body></html>';
            exit;
        }
    }
}
