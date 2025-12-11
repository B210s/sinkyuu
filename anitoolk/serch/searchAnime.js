// AnilistのAPIを使ってアニメ名を検索し、候補を表示する関数
function searchAnime() {
    const inputElement = document.getElementById('animeName');
    const animeTitle = inputElement.value.trim();

    if (!animeTitle) {
        document.getElementById('results').innerHTML = '<p>タイトルを入力してください。</p>';
        return;
    }

    const ANILIST_API_URL = 'https://graphql.anilist.co';

    // クエリ
    const query = `
        query ($search: String) {
            Page(page: 1, perPage: 10) {
                media(search: $search, type: ANIME) {
                    title {
                        native
                    }
                    coverImage {
                        large
                    }
                }
            }
        }
    `;

    const options = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            query: query,
            variables: {
                search: animeTitle
            }
        })
    };

    fetch(ANILIST_API_URL, options)
        .then(res => res.json())
        .then(data => {
            const mediaList = data.data.Page.media;

            if (!mediaList || mediaList.length === 0) {
                document.getElementById('results').innerHTML = '<p>該当するアニメが見つかりません。</p>';
                return;
            }

            // 検索結果HTMLを作成
            let resultHTML = '';
            mediaList.forEach(anime => {
                resultHTML += `
                    <div class="anime_item">
                        <h3>${anime.title.native}</h3>
                        <img src="${anime.coverImage.large}" width="150"><br>
                    </div>
                `;
            });

            // 検索結果を表示
            document.getElementById('results').innerHTML = resultHTML;
        })
        .catch(err => {
            document.getElementById('results').innerHTML = '<p>エラーが発生しました。</p>';
            console.error(err);
        });
}

// 送信ボタンをクリックしたときの処理
document.getElementById('searchButton').addEventListener('click', function(event) {
    event.preventDefault(); // フォームの送信を防ぐ
    searchAnime(); // 検索処理を実行
});

// 入力イベントで検索候補を表示する
document.getElementById('animeName').addEventListener('input', debounce(function(event) {
    const keyword = event.target.value;
    console.log(`[デバウンス後] 取得したキーワード: ${keyword}`);
    suggestAnime(keyword);
}, 500));

// デバウンス関数（500msの遅延を適用）
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}

// キーワード入力時にアニメの候補を表示する
function suggestAnime(keyword) {
    if (!keyword || keyword.trim() === "") {
        return;
    }

    const ANILIST_API_URL = 'https://graphql.anilist.co';
    const query = `
        query ($search: String) {
            Page(page: 1, perPage: 5) {
                media(search: $search, type: ANIME) {
                    title {
                        native
                    }
                }
            }
        }
    `;
    
    const options = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            query: query,
            variables: {
                search: keyword
            }
        })
    };

    fetch(ANILIST_API_URL, options)
        .then(res => res.json())
        .then(data => {
            const mediaList = data.data.Page.media;
            const dataList = document.getElementById('animeList');
            
            // datalistをクリア
            dataList.innerHTML = '';

            // 結果があればdatalistに追加
            mediaList.forEach(anime => {
                const option = document.createElement('option');
                option.value = anime.title.native;
                dataList.appendChild(option);
            });
        })
        .catch(err => console.error('検索候補取得失敗:', err));
}
