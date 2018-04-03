# albumHtmlMakerByPHP

## 概要
* GUIでページ、セクション、画像を指定してHTMLページを作成
* GUIのためにWebを使用(PHP)


## ファイル構造等n
### 前提構造
* 作成したいディレクトリ
    - set.php : 制作物
    - photos/ : 表示させたい画像が入ったディレクトリ

### 作成後構造
* 作成したディレクトリ
    - set.php
    - photos/
    - thumbs/ : サムネイル用画像が入ったディレクトリ
    - pages/ : 一覧ページが入ったディレクトリ
    - views/ : tehta画像閲覧用ページが入ったディレクトリ
    - index.html : 一番上のページにリダイレクトするファイル
    - set.json : ページ構造を記憶するファイル


## 作成方法
* "add page", "add section", "add image" ボタンで記入欄を追加し、名前を記入
* "image name" 欄に関しては PHP用の正規表現が使用可能
* "deploy" ボタンで作成/更新


## 処理内容
...