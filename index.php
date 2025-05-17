<?php
include 'login.php';
// 生成随机IP
$randIP = mt_rand(1,255).".".mt_rand(1,255).".".mt_rand(1,255).".".mt_rand(1,255);

// 设置请求上下文
$opts = array(
    'http'=>array(
        'header'=>"X-Forwarded-For: $randIP\r\n" .
                 "Client-IP: $randIP\r\n" .
                 "Referer: https://xiaoil.cn\r\n"
    )
);
$context = stream_context_create($opts);

// 构建URL和缓存文件路径
$url='https://xiaoil.cn/api/wp.php?keyword='.urlencode($_GET['keyword']).'&pan='.$_GET['pan'].'&offset='.$_GET['p'];
$cacheDir = __DIR__ . '/data/';
$cacheFile = $cacheDir . md5($url) . '.json';

// 检查缓存目录是否存在，不存在则创建
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// 清理过期缓存文件
$cacheFiles = glob($cacheDir . '*.json');
foreach ($cacheFiles as $file) {
    if (time() - filemtime($file) >= 86400) {
        unlink($file);
    }
}

// 检查缓存是否存在且未过期（24小时）
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
    // 从缓存读取数据
    $jsonStr = file_get_contents($cacheFile);
} else {
    // 从远程获取数据
    $jsonStr = file_get_contents($url, false, $context);
    
    // 缓存数据
    if ($jsonStr !== false) {
        file_put_contents($cacheFile, $jsonStr);
    }
}

// 解析为数组
$data = json_decode($jsonStr, true);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width"/>
    <title>搜索资源<?php echo !empty($_GET['keyword']) ? '-'.$_GET['keyword'] : ''?></title>
    <style>
        body { background: #f5f5f5; font-family: "微软雅黑", Arial, sans-serif; }
        .container { 
            display: flex; 
            flex-direction: column;
            gap: 24px; 
            justify-content: flex-start; 
            margin: 30px auto; 
            max-width: 900px; 
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: row;
            padding: 18px;
            box-sizing: border-box;
            align-items: flex-start;
            transition: box-shadow 0.2s;
        }
        .card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.15);}
        .card-img {
            width: 180px;
            min-width: 120px;
            max-width: 200px;
            margin-right: 24px;
            border-radius: 8px;
            object-fit: cover;
        }
        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .card-content .title {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .card-content .desc {
            font-size: 15px;
            color: #333;
            margin-bottom: 10px;
            white-space: pre-line;
        }
        .card-content .meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }
        .card-content .time {
            font-size: 13px;
            color: #aaa;
            margin-top: 8px;
        }
        .card-content a {
            color: #337ab7;
            text-decoration: underline;
            word-break: break-all;
        }
        .tab-btn {
    background: none;
    border: none;
    font-size: 16px;
    color: #666;
    padding: 8px 22px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.tab-btn.active {
    background: #6c2cff;
    color: #fff;
}
.tab-btn:not(.active):hover {
    background: #ece6ff;
    color: #6c2cff;
}
    </style>
</head>
<body>
<div style="background:#f3f3f3;padding:32px 0 18px 0;">
    <div style="max-width:900px;margin:0 auto;">
        <div id="pan-tabs" style="display:flex;align-items:center;margin-bottom:18px;">
            <button class="tab-btn active" data-pan="">全部</button>
            <button class="tab-btn" data-pan="aliyundrive">阿里</button>
            <button class="tab-btn" data-pan="quark">夸克</button>
            <button class="tab-btn" data-pan="xunlei">迅雷</button>
            <button class="tab-btn" data-pan="baidu">百度</button>
        </div>
        <form id="search-form" style="display:flex;align-items:center;">
            <input id="search-input" name="keyword" type="text" value="<?php echo $_GET['keyword']?>" placeholder="请输入搜索关键字…" style="flex:1;padding:16px 18px;font-size:18px;border:1px solid #ddd;border-radius:8px 0 0 8px;outline:none;">
            <button type="submit" style="background:#3a3f4b;border:none;width:60px;height:52px;border-radius:0 8px 8px 0;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <svg width="22" height="22" fill="#fff" viewBox="0 0 24 24"><path d="M21.71 20.29l-3.4-3.39A8.93 8.93 0 0019 11a9 9 0 10-9 9 8.93 8.93 0 005.9-1.69l3.39 3.4a1 1 0 001.42-1.42zM4 11a7 7 0 117 7 7 7 0 01-7-7z"></path></svg>
            </button>
        </form>
    </div>
</div>
<div class="container">
<?php
foreach ($data['pageProps']['data']['data'] as $item) {
    // 过滤掉“👥 群组：@yunpangroup”和“📢 频道：@yunpanshare”
    $content = str_replace(["👥 群组：@yunpangroup", "📢 频道：@yunpanshare"], "", $item['content']);
    // 去除图片链接两侧的反引号和空格
    $img = trim($item['image'], " `");
    // 提取各字段
    // 名称
    preg_match('/名称：(.+?)\n/', $content, $nameMatch);
    $name = isset($nameMatch[1]) ? $nameMatch[1] : '';
    // 链接
    preg_match('/链接：<a.*?href="([^"]+)".*?>([^<]+)<\/a>/', $content, $linkMatch);
    $link = isset($linkMatch[1]) ? $linkMatch[1] : '';
    // 大小
    preg_match('/大小：([^\n]+)\n/', $content, $sizeMatch);
    $size = isset($sizeMatch[1]) ? $sizeMatch[1] : '';
    // 标签
    preg_match('/标签：([^\n]+)\n/', $content, $tagMatch);
    $tags = isset($tagMatch[1]) ? $tagMatch[1] : '';
    // 资源发布时间
    $time = substr($item['time'], 0, 10);

    echo '<div class="card">';
    echo '<img class="card-img" src="'.(empty($img) || $img == 'null' ? 'favicon.png' : htmlspecialchars($img)).'" alt="封面">';
    echo '<div class="card-content">';
    echo '<div class="title">名称：'.($name ? $name : $content).'</div>';
    if ($link) {
        echo '<div class="meta">链接：<a href="'.htmlspecialchars($link).'" target="_blank">'.htmlspecialchars($link).'</a></div>';
    }
    if($size) {
        echo '<div class="meta">📁 大小：'.$size.'</div>';
    }
    echo '<div class="meta">🏷 标签：'.$tags.'</div>';
    echo '<div class="meta">🗂 网盘: '.htmlspecialchars($item['pan']).'</div>';
    echo '<div class="time">资源发布时间：'.$time.'</div>';
    echo '</div>';
    echo '</div>';
}
?>
<?php
// 获取当前页偏移量
$p = isset($_GET['p']) ? intval($_GET['p']) : 0;
$keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '';
$pan = isset($_GET['pan']) ? htmlspecialchars($_GET['pan']) : '';
// 总数
$total = isset($data['pageProps']['data']['total']) ? intval($data['pageProps']['data']['total']) : 0;
$pageSize = 10;
?>
<div style="display:flex;gap:0;justify-content:center;margin:30px 0;">
    <?php if($p > 0): ?>
        <a href="?keyword=<?php echo urlencode($keyword); ?>&pan=<?php echo urlencode($pan); ?>&p=<?php echo max(0, $p-$pageSize); ?>" style="background:#3a3f4b;color:#fff;padding:14px 36px;border-radius:8px 0 0 8px;text-decoration:none;display:inline-block;font-size:18px;">上一页</a>
    <?php endif; ?>
    <?php if($p + $pageSize < $total): ?>
        <a href="?keyword=<?php echo urlencode($keyword); ?>&pan=<?php echo urlencode($pan); ?>&p=<?php echo $p+$pageSize; ?>" style="background:#3a3f4b;color:#fff;padding:14px 36px;border-radius:<?php echo $p>0?'0 8px 8px 0':'8px'; ?>;text-decoration:none;display:inline-block;font-size:18px;">下一页</a>
    <?php endif; ?>
</div>
</div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const input = document.getElementById('search-input');
    const form = document.getElementById('search-form');
    let currentPan = "<?php echo isset($_GET['pan']) ? $_GET['pan'] : ''; ?>";

    // 初始化tab高亮
    tabs.forEach(tab => {
        if(tab.getAttribute('data-pan') === currentPan) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
        tab.onclick = function() {
            tabs.forEach(t=>t.classList.remove('active'));
            tab.classList.add('active');
            currentPan = tab.getAttribute('data-pan');
            // 如果input有值，自动提交表单
            if(input.value.trim() !== '') {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });

    form.onsubmit = function(e) {
        e.preventDefault();
        const keyword = encodeURIComponent(input.value.trim());
        let panParam = currentPan ? "&pan=" + currentPan : "";
        window.location.href = "?keyword=" + keyword + panParam;
    };
});
</script>
</html>
