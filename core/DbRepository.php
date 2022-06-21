<?php
/*
データベースにアクセスを行うクラス。テーブルごとにこのクラスの子クラスを作成する。
userテーブルであればUserRepositoryクラスを定義し、新規作成を行うinsert()メソッド
やidというカラムを元にデータを取得するメソッドを持つクラスファイル
頻繁に使うSQLを抽象化し、使い回しが出来るようにしておく
*/
abstract class DbRepository
{
  protected $con;

  //DbManagerクラスから受け取った値で下記関数を実行
  public function __construct($con)
  {
    $this->setConnection($con);
  }

  //PDOインスタンスを受け取って内部に保持
  public function setConnection($con)
  {
    $this->con = $con;
  }

  // ~プリペアドステートメント~ プレぺアド(prepead：準備済み)
  // SQLには直接変数を入れずに:nameのような形式で動的パラメータが入る、これをプレース
  // ホルダーと呼び、このプレースホルダーに入ってくる値をエスケープする仕組み。

  // データベースにクエリを発行するメソッド
  public function execute($sql, $params = array())
  {
    $stmt = $this->con->prepare($sql);
    $stmt->execute($params);

    return $stmt;

    // ~prepare()~
    // PDOStatementクラスのインスタンスが帰ってくる。
    // excute関数などを実行するのに必要になる

    // ~execute()~
    // 引数にプレースホルダに入る値を指定する。
    // クエリがデータベースに発行される、その際にプリペアドステートメントを実行。
  }

  // 1行のみ取得するメソッド
  public function fetch($sql, $params = array())
  {
    return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    //$paramsで1行を連想配列で受け取る
    // PDO::FETCH_ASSOC定数：連想配列で結果を受け取る指定
  }

  // 全ての行を取得するメソッド
  public function fetchAll($sql, $params = array())
  {
    return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    //$paramsで1行を連想配列で受け取る
    // PDO::FETCH_ASSOC定数：連想配列で結果を受け取る指定
  }
}