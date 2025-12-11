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
    }
    coverImage {
      large
    }
    description(asHtml: false)
    genres
    startDate {
      year
      month
      day
    }
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

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titleEsc; ?></title>
</head>
<body>

<h1><?php echo $titleEsc; ?></h1>

<img src="<?php echo $imgEsc; ?>" width="250" alt="<?php echo $titleEsc; ?>">

<p><strong>ジャンル：</strong> <?php echo implode(', ', $genresEsc) ?: 'なし'; ?></p>

<p><strong>放送開始：</strong> <?php echo htmlspecialchars($startDateStr, ENT_QUOTES, 'UTF-8'); ?></p>

<h2>主要キャラ</h2>
<?php if (!empty($mainChars)): ?>
    <?php foreach ($mainChars as $char): ?>
        <?php
            $cname = $char['name']['native'] ?? $char['name']['full'] ?? '不明';
            $cnameEsc = htmlspecialchars($cname, ENT_QUOTES, 'UTF-8');
            $cimg = $char['image']['large'] ?? 'placeholder-char.png';
        ?>
        <div style="display:inline-block; text-align:center; margin:10px;">
            <img src="<?php echo htmlspecialchars($cimg, ENT_QUOTES, 'UTF-8'); ?>" width="80" alt="<?php echo $cnameEsc; ?>"><br>
            <?php echo $cnameEsc; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>主要キャラ情報はありません。</p>
<?php endif; ?>

<h2>脇役キャラ</h2>
<?php if (!empty($supportingChars)): ?>
    <?php foreach ($supportingChars as $char): ?>
        <?php
            $cname = $char['name']['native'] ?? $char['name']['full'] ?? '不明';
            $cnameEsc = htmlspecialchars($cname, ENT_QUOTES, 'UTF-8');
            $cimg = $char['image']['large'] ?? 'placeholder-char.png';
        ?>
        <div style="display:inline-block; text-align:center; margin:10px;">
            <img src="<?php echo htmlspecialchars($cimg, ENT_QUOTES, 'UTF-8'); ?>" width="80" alt="<?php echo $cnameEsc; ?>"><br>
            <?php echo $cnameEsc; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>脇役キャラ情報はありません。</p>
<?php endif; ?>

</body>
</html>
