<?php
//パーフェクトPHP
//https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP

/*DbManagerクラスとDbRepositoryクラスの関係*
  ・DbManagerクラスの内部にテーブルごとのRepositoryクラスを保持する。
  ・DbManagerクラスのインスタンスが変数に入っているとしたら、
    $db_manager->get('User')みたいな形式でUserRepositoryクラスを取得する
*/
class DbManager
{
  //PDOインスタンスを配列で保持する
  protected $connections = array();

  //テーブル毎のRepositoryクラスと接続名の対応を保持
  protected $repository_connection_map = array();

  //Repositoryで使うインスタンス保持
  protected $repositories = array();


  
  public function connect($name, $params)
  {// 第一引数：データベース名、$connectionsプロパティのキー
   // 第二引数：PDOクラスの__constructに渡す情報、データベースの指定など

    $params = array_merge(//PDOクラスのインスタンスに渡す値
      array(
        'dsn'      => null,//データベースの指定、
        'user'     => '',  //ユーザーやパスワードなどの配列を用意
        'password' => '',  //
        'options'  => array(),
      ),
      $params);//配列キーに格納された値を上書きする（あれば）

    $connect = new PDO(//PDOインスタンスの引数の指定に$paramsのキーを指定
      $params['dsn'],//データベース名、データベースホスト名、データベースのドライバを指定
      $params['user'],
      $params['password'],
      $params['options']//ドライバ毎のオプション
    );

    // PDO内部でエラーが起こった場合に例外を発生させる
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //$name(モデル名)をキーとしてPDOインスタンス配列をプロパティ配列に格納
    $this->connections[$name] = $connect;


    /*~array_merge()~*
    第一引数の配列に第二引数の配列を連結させる関数、
    その際に同じキーの値を第二引数の配列のバリューで上書きする
    */
  }

  //connect関数で接続したコネクション情報を取得する関数
  public function getConnection($name = null)
  {//引数にデフォルト値null
    if(is_null(($name))){//名前の指定がなかった場合current関数を利用して取得する
      return current(($this->connections));//connectionプロパティの先頭、つまり$nameにあたる部分を取得
    }

    return $this->connections[$name];

    //〜current関数〜
    // 配列の内部ポインタが示す値(先頭アドレス)を取得する関数。
    // つまり配列の先頭の値を取得する関数
  }
  

  /* Repositoryクラスでどのデータベース接続を扱うか管理 */
  public function setRepositoryConnectionMap($repository_name, $name)
  {
    $this->repository_connection_map[$repository_name] = $name;
  }

  // $repository_name(モデル名)に対応したconectionsプロパティを返す
  public function getConnectionForRepository($repository_name)
  {
    if(isset($this->repository_connection_map[$repository_name])){
      $name = $this->repository_connection_map[$repository_name];
      $connect = $this->getConnection($name);
    }else{
      $connect = $this->getConnection();
    }

    return $connect;
  }

  // Repositoryクラスのインスタンスを生成
  public function get($repository_name)
  {
    if(!isset($this->repositories[$repository_name])){//プロパティに格納されていない場合
      $repository_class = $repository_name . 'Repository';
      // 受け取った名前に'Repositoryを付け格納'
      $connect = $this->getConnectionForRepository($repository_name);
      // $repository_name(モデル名)に対応したconectionsプロパティが帰ってくる

      $repository = new $repository_class($connect);
      // 変数にクラス名を入れておくことで動的なクラス生成が行える
      //例： = new userRepository(userコネクションプロパティ);

      $this->repositories[$repository_name] = $repository;
      // プロパティに格納し、インスタンスを保持する
    }

    // $repository_name.Repositoryクラスのインスタンスを返す
    return $this->repositories[$repository_name];
  }

  //データベース接続解除
  public function __destruct()
  {
    foreach($this->repositories as $repository){
      unset($repository);
    }

    foreach($this->connections as $con){
      unset($con);
    }
  // PDOのインスタンスを破棄すると接続が閉じるようになっているが、Repository内でも参照しているため
  // 参照が残っているとインスタンスを破棄できないので、このメソッドで先に破棄を行う必要がある。
  }

}