<?php
// セッション開始
session_start();

const ANILIST_API_URL = 'https://graphql.anilist.co';

function fetchAniListRanking($sortType) {
    $GetRankingQuery = "
        query {
            Page(page: 1, perPage: 10) {
                media(type: ANIME, sort: $sortType) {
                    id
                    title {
                        native
                        romaji
                        english
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

$TrendingMediaList = fetchAniListRanking('TRENDING_DESC');

// セッションから前回順位を取得
$previousRankList = $_SESSION['previousRanks'] ?? [];

// 初回アクセス時、前回順位が設定されていない場合は空の配列で初期化
if (empty($previousRankList)) {
    // 初回は全てのアニメにnullをセット
    $previousRankList = array_fill(0, count($TrendingMediaList), null);
    $_SESSION['previousRanks'] = $previousRankList; // 初回セッション保存
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>アニメランキング</title>
    <style>
        .ranking-container {
            display: block;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .ranking-container a {
            display: block;
            text-decoration: none;
            color: black; 
        }

        .tlendranling {
            color: #fff;
            border-left: 6px solid #fff;
            padding-left: 15px;
            margin-left: 0;
        }

        .anime-item {
            padding: 10px;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #fff;
            margin-top: 10px;
            border-radius: 10px;
        }

        .rankimg {
            width: 150px;
            height: auto;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .ranking_number {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .ranking_anime_title {
            font-size: 30px;
            margin: 0;
            color: black;
            flex: 1;
            word-break: break-word;
        }

        .anime-item:hover{
            background-color: #e0e0e0ff;
        }

        .rank_icon {
            width: 24px;
            height: 24px;
        }

    </style>
</head>
<body>

<h1 class="tlendranling">トレンドアニメランキング TOP 5</h1>

<div class="ranking-container">

<?php if (!empty($TrendingMediaList)): ?>

    <?php foreach ($TrendingMediaList as $index => $anime): ?>

        <?php
        // 現在順位
        $currentRank = $index + 1;

        // セッションに保存された前回順位
        $previousRank = $previousRankList[$index];

        // アイコン決定 (前回順位がnullの場合でも画像を表示する)
        if ($previousRank === null) {
            // 初回はアイコンを表示しない
            $icon = "Asis.png";  // とりあえずAsis.pngを表示
        } else {
            // 前回順位と比較してアイコンを決定
            if ($previousRank > $currentRank) {
                $icon = "up.png"; // 前回順位が上位だった場合「上昇」のアイコン
            } elseif ($previousRank < $currentRank) {
                $icon = "down.png"; // 前回順位が下位だった場合「下降」のアイコン
            } else {
                $icon = "Asis.png"; // 前回順位と現在順位が同じ場合
            }
        }

        // 現在の順位をセッションに保存
        $previousRankList[$index] = $currentRank;
        $_SESSION['previousRanks'] = $previousRankList; // セッション保存

        // デバッグ出力 (これを有効にして動作を確認)
        // echo "Current Rank: $currentRank, Previous Rank: $previousRank<br>";
        // echo "Icon: $icon<br>";
        ?>

        <a href="animedetail.php?id=<?= $anime['id'] ?>">
            <div class="anime-item">

                <h2 class="ranking_number">
                    <img src="imgs/<?= $icon ?>" class="rank_icon"> <!-- アイコンの表示 -->
                    <?= $currentRank ?>位
                </h2>

                <img class="rankimg"
                    src="<?= htmlspecialchars($anime['coverImage']['large']) ?>"
                    alt="<?= htmlspecialchars($anime['title']['native']) ?>">

                <p class="ranking_anime_title">
                    <strong><?= htmlspecialchars($anime['title']['native']) ?></strong>
                </p>

            </div>
        </a>

    <?php endforeach; ?>

<?php else: ?>

    <p>ランキングデータを取得できませんでした。</p>

<?php endif; ?>

</div>

</body>
</html>
