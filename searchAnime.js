function searchAnime() {
    // 入力フィールドのDOM要素を取得
    const inputElement = document.getElementById('animeName');
    // ユーザーが入力したアニメ名（検索クエリ）を取得
    const animeTitle = inputElement.value.trim();

    // 入力が空の場合のやつ
    if (!animeTitle) {
        document.getElementById('results').innerHTML = '<p>タイトルを入力してください。</p>';
        return;
    }

    // Anilistのapi使うためのリンク 
    const ANILIST_API_URL = 'https://graphql.anilist.co';

    // クエリ
    const query = `
        query ($search: String) {
            Media(search: $search, type: ANIME) {
                title {
                    native
                    romaji
                }
                coverImage {
                    large
                }
                characters(role: MAIN) {
                    nodes {
                        name {
                            native
                            full
                        }
                        image {
                            large 
                        }
                    }
                }
                genres
                startDate {
                    year
                    month
                    day
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
            const anime = data.data.Media;

            // データが無かった場合
            if (!anime) {
                document.getElementById('results').innerHTML = '<p>該当するアニメが見つかりません。</p>';
                return;
            }

            // HTMLを組み立てる
            const startDate = `${anime.startDate.year || ''}/${anime.startDate.month || ''}/${anime.startDate.day || ''}`;

            let characterHTML = '';
            anime.characters.nodes.forEach(char => {
                characterHTML += `
                    <div style="display:inline-block; margin-right:10px; text-align:center;">
                        <img src="${char.image.large}" width="80"><br>
                        <span>${char.name.native}</span>
                    </div>
                `;
            });

            const html = `
                <h2>${anime.title.native || anime.title.romaji}</h2>
                <img src="${anime.coverImage.large}" width="200"><br><br>

                <p><strong>ジャンル：</strong> ${anime.genres.join(', ')}</p>
                <p><strong>放送開始日：</strong> ${startDate}</p>

                <h3>主要キャラ</h3>
                <div>${characterHTML}</div>
            `;

            // 表示
            document.getElementById('results').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('results').innerHTML = '<p>エラーが発生しました。</p>';
            console.error(err);
        });
}
