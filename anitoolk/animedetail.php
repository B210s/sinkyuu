<?php
// AniList API
const ANILIST_API_URL = "https://graphql.anilist.co";

// 受け取ったID
$animeId = $_GET['id'] ?? null;

if (!$animeId) {
    echo "アニメIDが指定されていません。";
    exit;
}

$query = <<<'GRAPHQL'
query ($id: Int) {
  Media(id: $id, type: ANIME) {
    title {
      native
      romaji
      english
    }
    coverImage {
      large
    }
    bannerImage
    description(asHtml: false)
    genres
    startDate {
      year
      month
      day
    }
    endDate {
      year
      month
      day
    }
    averageScore
    popularity
    episodes
    characters {
      edges {
        role
        node {
          name {
            native
            full
          }
          image {
            large
          }
        }
      }
    }
  }
}
GRAPHQL;

$postData = json_encode([
    'query' => $query,
    'variables' => ['id' => intval($animeId)]
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => ANILIST_API_URL,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$response = curl_exec($ch);

// cURLエラーチェック
if (curl_errno($ch)) {
    echo 'cURLエラー: ' . htmlspecialchars(curl_error($ch), ENT_QUOTES, 'UTF-8');
    curl_close($ch);
    exit;
}
curl_close($ch);

// レスポンスチェック
if (!$response) {
    echo 'レスポンスが空です。API呼び出しに失敗しました。';
    exit;
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON解析エラー';
    exit;
}

if (!empty($data['errors'])) {
    echo 'APIエラー: ' . htmlspecialchars($data['errors'][0]['message'] ?? '不明なエラー', ENT_QUOTES, 'UTF-8');
    exit;
}

$anime = $data['data']['Media'] ?? null;
if (!$anime) {
    echo "アニメデータが取得できませんでした。";
    exit;
}

// ──────────────────────────────
// データ整形
// ──────────────────────────────

$title = $anime['title']['native'] ?? $anime['title']['romaji'] ?? 'タイトル不明';
$titleEsc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

$img = $anime['coverImage']['large'] ?? 'placeholder.png';
$imgEsc = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');

$genres = is_array($anime['genres'] ?? null) ? $anime['genres'] : [];
$genresEsc = array_map(fn($g) => htmlspecialchars($g, ENT_QUOTES, 'UTF-8'), $genres);

$sd = $anime['startDate'] ?? [];
$startDateStr = ($sd['year'] ?? '') ? sprintf('%s/%s/%s', $sd['year'], $sd['month'] ?? '??', $sd['day'] ?? '??') : '未定';

$edges = $anime['characters']['edges'] ?? [];

// ──────────────
// キャラ分類
// ──────────────
$mainChars = [];
$supportingChars = [];

foreach ($edges as $edge) {
    $role = $edge['role'] ?? '';
    $node = $edge['node'] ?? null;
    if (!$node) continue;

    if ($role === 'MAIN') {
        $mainChars[] = $node;
    } elseif ($role === 'SUPPORTING') {
        $supportingChars[] = $node;
    }
}

$description = $anime['description'] ?? '';
$descriptionEsc = nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));

// バナー画像
$bannerImage = $anime['bannerImage'] ?? 'placeholder-banner.png';
$bannerImageEsc = htmlspecialchars($bannerImage, ENT_QUOTES, 'UTF-8');
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titleEsc; ?></title>
    <link rel="stylesheet" href="./animedetail.css">
</head>
<body>

<!-- バナー画像 -->
<div class="anime-banner" style="background-image: url('<?php echo $bannerImageEsc; ?>');">
    <div class="anime-main-image-container">
        <img src="<?php echo $imgEsc; ?>" alt="<?php echo $titleEsc; ?>" class="anime-main-image">
    </div>
</div>

<!-- タイトル -->
<h1><?php echo $titleEsc; ?></h1>

<div class="Anime_broadcast_detaile">   

<div class="anime-info">
<!-- ジャンル -->
<p><strong>ジャンル：</strong> <?php echo implode(', ', $genresEsc) ?: 'なし'; ?></p>

<!-- 放送開始 -->
<p><strong>放送開始：</strong> <?php echo htmlspecialchars($startDateStr, ENT_QUOTES, 'UTF-8'); ?></p>
</div>


<!-- 主要キャラ -->
<h2>主要キャラ</h2>
<?php if (!empty($mainChars)): ?>
    <div class="character-list">
        <?php foreach ($mainChars as $char): ?>
            <?php
                $cname = $char['name']['native'] ?? $char['name']['full'] ?? '不明';
                $cnameEsc = htmlspecialchars($cname, ENT_QUOTES, 'UTF-8');
                $cimg = $char['image']['large'] ?? 'placeholder-char.png';
            ?>
            <div class="character-card">
                <img src="<?php echo htmlspecialchars($cimg, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $cnameEsc; ?>"><br>
                <?php echo $cnameEsc; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>主要キャラ情報はありません。</p>
<?php endif; ?>

<!-- 脇役キャラ -->
<h2>脇役キャラ</h2>
<?php if (!empty($supportingChars)): ?>
    <div class="character-list">
        <?php foreach ($supportingChars as $char): ?>
            <?php
                $cname = $char['name']['native'] ?? $char['name']['full'] ?? '不明';
                $cnameEsc = htmlspecialchars($cname, ENT_QUOTES, 'UTF-8');
                $cimg = $char['image']['large'] ?? 'placeholder-char.png';
            ?>
            <div class="character-card">
                <img src="<?php echo htmlspecialchars($cimg, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $cnameEsc; ?>"><br>
                <?php echo $cnameEsc; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>脇役キャラ情報はありません。</p>
<?php endif; ?>

</div>

</body>
</html>
