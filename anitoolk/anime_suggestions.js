// Anilist APIを使ってアニメを検索する関数
function anime_suggestions(keyword) {
    if (!keyword || keyword.trim() === "") {
        const dataList = document.getElementById('list');
        dataList.innerHTML = ''; // datalistの内容をクリア
        const answerList = document.querySelector('.anime_answer_list');
        answerList.innerHTML = ''; // 検索結果もクリア
        console.log("キーワードが空なので、検索をスキップしdatalistをクリアしました。");
        return;
    }

    const ANILIST_API_URL = 'https://graphql.anilist.co';
    const query = `
    query ($search: String) {
        Page(page: 1, perPage: 10) {
            media(search: $search, type: ANIME) {
                title {
                    native
                }
            }
        }
    }`;

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
            console.log('APIからのレスポンス:', data); // レスポンスを確認
            const mediaList = data.data.Page.media;
            const anime_titles = mediaList.map(media => media.title.native);

            // 結果が空でないかチェック
            if (anime_titles.length === 0) {
                console.log('該当するアニメが見つかりません');
                document.querySelector('.anime_answer_list').innerHTML = '<p>該当するアニメが見つかりません。</p>';
                return;
            }

            const dataList = document.getElementById('list');
            const answerList = document.querySelector('.anime_answer_list');

            // datalistをクリア
            dataList.innerHTML = '';
            // 検索結果divをクリア
            answerList.innerHTML = '';

            // datalistに<option>を追加
            anime_titles.forEach(title => {
                const option = document.createElement('option');
                option.value = title;
                dataList.appendChild(option);
            });

            // 検索結果divにアニメ名を表示
            anime_titles.forEach(title => {
                const div = document.createElement('div');
                div.className = 'anime_item';
                div.textContent = title; // アニメ名をdivにセット
                answerList.appendChild(div); // anime_answer_listに追加
            });
        })
        .catch(error => {
            console.error('APIリクエスト失敗:', error);
            document.querySelector('.anime_answer_list').innerHTML = '<p>エラーが発生しました。</p>';
        });
}

// 送信ボタンをクリックしたときに検索を実行
const searchButton = document.getElementById('searchButton');
searchButton.addEventListener('click', function() {
    const keyword = document.getElementById('animeKeyword').value;
    anime_suggestions(keyword);
});

// 500msの遅延を加えて入力イベントにdebounceを適用
const inputElement = document.getElementById('animeKeyword');
inputElement.addEventListener("input", debounce(function (event) {
    const keyword = event.target.value;
    console.log(`[デバウンス後] 取得したキーワード: ${keyword}`);
    anime_suggestions(keyword);
}, 500));

// デバウンス関数
function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}
