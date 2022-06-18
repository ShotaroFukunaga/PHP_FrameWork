<?php
//パーフェクトPHP
//https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP


class DbManager
{
  //接続情報であるPDOクラスのインスタンスを配列で保持するためのプロパティ
  protected $connections = array();

  protected $repository_connection_map = array();

  protected $repositories = array();

  // 上記プロパティに
  // 第一引数：接続を特定するための名前、$connectionsプロパティのキー
  // 第二引数：PDOクラスのコンストラクタに渡す情報、データベースの指定など
  public function connect($name, $params)
  {
    $params = array_merge(
      array(//データベースの指定、ユーザーやパスワードなどの配列を用意
      'dsn'      => null,
      'user'     => '',
      'password' => '',
      'options'  => array(),
      ),
      $params);//コンストラクタに渡す情報を上記の配列にmerge

    $connect = new PDO(//PDOインスタンスの引数の指定に$paramsのキーを指定
      $params['dsn'],
      $params['user'],
      $params['password'],
      $params['options']
    );

    // PDO内部でエラーが起こった場合に例外を発生させる
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->connections[$name] = $connect;//上記の配列のキーとして$name
  }

  // connectメソッドで接続したコネクションを取得する関数
  public function getConnection($name = null)
  {
    if(is_null(($name))){//名前の指定がなかった場合current関数を利用して取得する
      return current(($this->connections));
    }

    return $this->connections[$name];

    //〜current関数〜
    // 配列の内部ポインタが示す値を取得する関数。ここでは配列の先頭の値を取得する、
    // つまり、指定がなければ上記で作成したPDOクラスのインスタンスが帰ってくる
  }
  

  /* Repositoryクラスでどの接続を扱うか管理する機能 */

  public function setRepositoryConnectionMap($repository_name, $name)
  {
    $this->repository_connection_map[$repository_name] = $name;
  }

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

  /* Repositoryクラスの管理 */
  // https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP
  
  // Repositoryクラスのインスタンスを生成
  public function get($repository_name)
  {
    if(!isset($this->repositories[$repository_name])){
      $repository_class = $repository_name . 'Repository';
      // 受け取った名前に'Repositoryを付け格納'
      $connect = $this->getConnectionForRepository($repository_name);
      // コネクションを取得する

      $repository = new $repository_class($connect);
      // 変数にクラス名を入れておくことで動的なクラス生成が行える

      $this->repositories[$repository_name] = $repository;
      // プロパティに格納し、インスタンスを保持する
    }

    return $this->repositories[$repository_name];
  }

  /*接続の解法処理 */

  // PDOのインスタンスを破棄すると接続が閉じるようになっているが、Repository内でも参照しているため
  // 参照が残っているとインスタンスを破棄できないので、このメソッドで先に破棄を行う必要がある。
  public function __destruct()
  {
    foreach($this->repositories as $repository){
      unset($repository);
    }

    foreach($this->connections as $con){
      unset($con);
    }
  }

}