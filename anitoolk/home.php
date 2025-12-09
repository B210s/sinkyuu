<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホーム</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <header>
            <h1>anitooooolk</h1>
            <nav>
                <ul>
                    <li><a href="#">ランキング</a></li>
                    <li><a href="#">スレッド作成</a></li>
                </ul>
            </nav>
    </header>

    <main>
        <div class="wapper">
            <div class="introduction">
                <h2>ようこそ！anitooooolkへ的な概要を描く紹介コーナー</h2>
            </div>
            <div class="serch">
                <h2>気になるアニメを探そう!</h2>
                <form>
                    <label for="site-search">アニメを検索：</label>
                    <input type="search" placeholder="🔍" id="site-search" name="name" />
                    <button type="submit">Search</button>
                </form>
            </div>
            
        </div>

            <div class="ranking">
                <?php include 'gazo.php'; ?>
            </div>

        
    </main>    


</body>
</html>