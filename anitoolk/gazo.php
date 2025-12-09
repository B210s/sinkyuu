<?php
// AniListのGraphQL APIエンドポイント
const ANILIST_API_URL = 'https://graphql.anilist.co';

// APIからランキングデータを取得する関数
function fetchAniListRanking($sortType) {
    $GetRankingQuery = "
        query {
            Page(page: 1, perPage: 5) {
                media(type: ANIME, sort: $sortType) {
                    title {
                        native
                    }
                    coverImage {
                        large
                    }
                }
            }
        }
    ";

    $postData = json_encode(['query' => $GetRankingQuery]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => ANILIST_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURLエラー: ' . curl_error($ch);
        curl_close($ch);
        return [];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['data']['Page']['media'] ?? [];
}

// 2つのランキングを取得
$TrendingMediaList = fetchAniListRanking('TRENDING_DESC');
$PopularMediaList = fetchAniListRanking('POPULARITY_DESC');
$UpcomingMediaList = fetchAniListRanking('START_DATE_DESC');
$Enddate = fetchAniListRanking('END_DATE');
$Trend = fetchAniListRanking('TRENDING');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>アニメランキング</title>
    <style>
        .ranking-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }
        .anime-item {
            padding: 10px;
            width: 200px;
            text-align: center;
        }
        .anime-item img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 10px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <h1>✨ トレンドアニメランキング TOP 5 ✨</h1>
    <div class="ranking-container">
        <?php if (!empty($TrendingMediaList)): ?>
            <?php foreach ($TrendingMediaList as $index => $anime): ?>
                <div class="anime-item">
                    <h2><?php echo ($index + 1) . '位'; ?></h2>
                    <img src="<?php echo htmlspecialchars($anime['coverImage']['large']); ?>" alt="<?php echo htmlspecialchars($anime['title']['native']); ?>">
                    <p><strong><?php echo htmlspecialchars($anime['title']['native']); ?></strong></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>ランキングデータを取得できませんでした。</p>
        <?php endif; ?>
    </div>

    <h1>🔥 人気アニメランキング TOP 5 🔥</h1>
    <div class="ranking-container">
        <?php if (!empty($PopularMediaList)): ?>
            <?php foreach ($PopularMediaList as $index => $anime): ?>
                <div class="anime-item">
                    <h2><?php echo ($index + 1) . '位'; ?></h2>
                    <img src="<?php echo htmlspecialchars($anime['coverImage']['large']); ?>" alt="<?php echo htmlspecialchars($anime['title']['native']); ?>">
                    <p><strong><?php echo htmlspecialchars($anime['title']['native']); ?></strong></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>ランキングデータを取得できませんでした。</p>
        <?php endif; ?>
    </div>

    <h1>🚀 これから放送される新作アニメ期待度ランキング TOP 5 🚀</h1>
    <div class="ranking-container">
        <?php if (!empty($UpcomingMediaList)): ?>
            <?php foreach ($UpcomingMediaList as $index => $anime): ?>
                <div class="anime-item">
                    <h2><?php echo ($index + 1) . '位'; ?></h2>
                    <img src="<?php echo htmlspecialchars($anime['coverImage']['large']); ?>" alt="<?php echo htmlspecialchars($anime['title']['native']); ?>">
                    <p><strong><?php echo htmlspecialchars($anime['title']['native']); ?></strong></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>ランキングデータを取得できませんでした。</p>
        <?php endif; ?>
    </div>

    <h1>🚀 これから放送される新作アニメ期待度ランキング TOP 5 🚀</h1>
    <div class="ranking-container">
        <?php if (!empty($Enddate)): ?>
            <?php foreach ($Enddate as $index => $anime): ?>
                <div class="anime-item">
                    <h2><?php echo ($index + 1) . '位'; ?></h2>
                    <img src="<?php echo htmlspecialchars($anime['coverImage']['large']); ?>" alt="<?php echo htmlspecialchars($anime['title']['native']); ?>">
                    <p><strong><?php echo htmlspecialchars($anime['title']['native']); ?></strong></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>ランキングデータを取得できませんでした。</p>
        <?php endif; ?>
    </div>

    <h1>⭐ これから放送される新作アニメ期待度ランキング TOP 5 ⭐</h1>
    <div class="ranking-container">
        <?php if (!empty($Trend)): ?>
            <?php foreach ($Trend as $index => $anime): ?>
                <div class="anime-item">
                    <h2><?php echo ($index + 1) . '位'; ?></h2>
                    <img src="<?php echo htmlspecialchars($anime['coverImage']['large']); ?>" alt="<?php echo htmlspecialchars($anime['title']['native']); ?>">
                    <p><strong><?php echo htmlspecialchars($anime['title']['native']); ?></strong></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>ランキングデータを取得できませんでした。</p>
        <?php endif; ?>
    </div>
</body>
</html>