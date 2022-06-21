# クラスの読み込み
## オートローダ：[ClassLoader.php] [bootstrap.php]
  オートロードとは定義されていないクラスを使おうとした時に、
  指定されたオートロード関数を使ってクラスを読み込む仕組み。
  require_onceを使ってクラスファイルを読み込まなくて良くなる。

</br>

## __construct()：コンストラクタ
クラスインスタンスの生成時に渡す引数
例： new UserController($name, $action);

</br>

## クラスへのアクセス権の設定
- protected宣言されたクラスのメンバは、そのクラス自身と親子関係にあるクラスのみアクセスが可能
-   public 宣言されたプロパティ、メソッド、定数（今後はクラスのメンバと呼びます）はどこからでもアクセスが可能
-   private宣言されたクラスのメンバは、 そのクラス自身のみがアクセス可能

</br>

## フロントコントローラ：[index.php]
全てのリクエストを1つのphpファイルで受け取る仕組み。
[http://example.com/index.php/list]みたいなURLでアクセスで受け取り、
フレームワーク側で[index.php]の部分を抜き出してURLとして使用する。

</br>

</br>

# リクエストとルーティング
## htaccessでURLを書き換える:[.htaccess]
htaccessファイルはApacheの設定を変更するファイル
Apacheの機能を使用して[http://example.com/list]という形式できたURLをindex.php
でアクセスさせる

</br>

## カプセル化
オブジェクトのデータを外部からアクセス出来ないようにし直接の参照からオブジェクトを保護
する仕組み。
データを変更する際は、クラス内にデータ変更用の関数を用意して間接的に変更を行う

</br>

## ベースURLとPATH_INFO:
***ベースURL***
ホスト名の後からフロントコントローラーまでのパスを特定する値。

***PATH＿INFO***
フロントコントローラー以降の値。
内部的で使うURLで、この値を使ってRouterクラスがURLとコントローラの対応付けを行う。

[対応名称]
       |   ホスト名   |ベースURL| フロント  |PATH＿INFO]
[http:// example.com /foo/bar /index.php /list      ]

</br>

## SCRIPT_NAMEとREQUEST_URI
■ $_SERVER['SCRIPT_NAME']
フロントコントローラーまでのパスが格納されている
■ $_SERVER['REQUEST_URI']
URLのホスト部分以降の値が格納されている

### http://example.com/foo/bar/listの場合
___
REQUEST_URI: /foo/bar/list  
SCRIPT_NAME: /foo/bar/index.php  
   ベースURL: /foo/bar  
  PATH_INFO: /list  

### http://example.com/index.php/list?foo=barの場合
___
REQUEST_URI: /index.php/list?foo=bar  
SCRIPT_NAME: /index.php  
   ベースURL: /index.php  
  PATH_INFO: /list

### http://example.com/の場合
___
REQUEST_URI: /  
SCRIPT_NAME: /index.php  
   ベースURL: ""  
  PATH_INFO: /  

</br>

</br>

# ルーティング
取得したPATH_INFOからコントローラーとアクションを特定する、  
user/editならUserコントローラーのeditアクションみたいな感じに

</br>

## ルーティングの定義方法
URLとコントローラーの対応付けはアプリによって異なるため、アプリ毎に定義する。
分かりやすく汎用性を持たせるために連想配列を使ってルーティング定義できる仕様にする。
下記例：
```
array(
  '/'          => array('controller' => 'home', 
                            'action' => 'index'),
  '/user/edit' => array('controller' => 'user', 
                            'action' => 'edit'),
);
//キーにはPATH_INFOを使用('/user/edit'など)
//コントローラ名は大文字なので後で変換する
```

</br>

## 動的ルーティング
/user?id=1というURLを/user/1として取り除いていたGETパラメーターをPATH_INFOに含めたい
そのためにルーティング定義中に"："で始まる文字列を動的に扱えるようにする。

```
array(
  '/user/:id' => array('controller' => 'user', 
                          'action' => 'show'),
);
//:idが動的に値が変動する部分
```

</br>

## 正規表現とキャプチャ
キャプチャとは正規表現中の（）で囲まれた部分を保持する機能のこと。
PHPで扱うにはpreg_match()関数を使用する。この関数は第3引数に指定したキャプチャの値を取得できる

</br>

## 名前付きキャプチャ
キーを数字で扱うのは大変なので下記のような正規表現でキャプチャに名前をつけれる
```
$pattern = '/ab(?P<foo>cd)ef/'

```

</br>

## 動的なコントローラーとアクション
上記を踏まえてコントローラーとアクションに対してのルーティングを動的に行えるようにする。
```
array(
  '/:controller' => array('action' => 'index'),
  '/item/:action' => array('controller' => 'item'),
);
```
この取得した配列をルーティングパラメーターとする。

</br>

# レスポンス
- HTTPヘッダとHTMLなどのコンテンツを返す
- HTTPヘッダを送信するためにheader()関数を使って送信する

</br>

</br>


# データベース
- データベースとの接続情報を管理するDbManagerクラス
- データベースへアクセスする処理を行うDbRepositoryクラス(モデル)

</br>

## PDOクラス:DbManager.php
データベース抽象化ライブラリと呼ばれ、MySQLやPostgreSQLなど
違う種類のデータベースに対して同じ記述方法で操作するためのライブラリ

</br>


## DbRepositoryクラス
- データベース上のテーブル毎にこのクラスを継承してクラスを作成する  
例えば、userテーブルであればUserRepositoryクラスを作成するみたいな。
- テーブルに対応したクラスを作成するので、DbRepositoryクラスは単一の接続情報を保持する。
- アクセス処理を抽象化した関数を用意し、各モデルでのプログラミングコストを下げる。


# Applicationクラス
- Request, Router, Response, Sessionなどの各クラスのオブジェクトを管理
- ルーティング定義、コントローラーの実行、レスポンスの送信など全体の流れを管理
- さまざまなディレクトリのパス管理
  

# コントローラーとビュー
- ApplicationクラスではControllerクラスのrun()メソッドを呼び出し、その戻り値としてResponseクラスのコンテンツの値であるHTMLを返す。
- ControllerクラスのコンストラクタにはApplicationクラス自信を渡す

## コントローラークラスの処理の流れ
1. run()メソッドで個別のアクションを呼び出す、
2. アクションの内部でDbRepositoryクラスからデータの取得を行う、
3. Viewクラスを生成してアクションに応じたHTMLのレンダリングを行う、
4. run()メソッドの実行結果としてレンダリングしたHTMLを文字列として返す。

## CSRF対策
ワンタイムトークン方式でいく！
1. フォームを開いた時に、推測しにくい文字列（トークン）を生成
2. サーバー側で保持すると同時にフォーム内のhiddenパラメータに埋め込み
3. そのフォームからのリクエストに含まれるトークンとサーバーで保持しているトークンを比較する
4. 一致していれば正常なリクエストとして処理し、トークンを削除する
5. 次回のフォームでは別のトークンを生成するためワンタイムトークン方式と呼ぶ