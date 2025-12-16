<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アニメ検索</title>
    <link rel="stylesheet" href="./searchAnime.css">
</head>
<body>

    <h1>アニメ検索</h1>
    <a href="../home.php">
        <button>back</button>
    </a>
    <form autocomplete="off">
        <input list="animeList" type="text" id="animeName" placeholder="アニメのタイトルを入力" autocomplete="off">
        <datalist id="animeList"></datalist>
        <button id="searchButton">検索</button>
    </form>
    <div id="results"></div>

    <script src="searchAnime.js"></script>

</body>
</html>
