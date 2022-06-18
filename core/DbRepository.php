<?php
/*
データベースにアクセスを行うクラス。テーブルごとにこのクラスの子クラスを作成する。
userテーブルであればUserRepositoryクラスを定義し、新規作成を行うinsert()メソッドやidというカラムを
元にデータを取得するメソッドを持つクラスファイル
頻繁に使うSQLを抽象化し、使い回しが出来るようにしておく
*/
abstract class DbRepository
{
  protected $con;

  // DbManagerクラスからPDOクラスのインスタンスを受け取って内部に保持するメソッド
  public function __construct($con)
  {
    $this->setConnection($con);
  }

  // DbManagerクラスからPDOクラスのインスタンスを受け取って内部に保持するメソッド
  public function setConnection($con)
  {
    $this->con = $con;
  }

  // ~プリペアドステートメント~ プレぺアド(prepead：準備済み)
  // SQLには直接変数を入れずに:nameのような形式で動的パラメータが入るという指定になる。
  // これをプレースホルダーと呼ぶ。
  // プリペアドステートメントを用いるとプレースホルダーに入ってくる値をエスケープしてくれる。

  // データベースにクエリを発行するメソッド
  public function execute($sql, $params = array())
  {
    $stmt = $this->con->prepare($sql);
    $stmt->execute($params);

    return $stmt;

    // ~prepare())メソッド~
    // PDOStatementクラスのインスタンスが帰ってくる。

    // ~execute()メソッド~
    // クエリがデータベースに発行される、
    // その際にexecute()メソッドの引数にプレースホルダに入る値を指定する。
  }

  // 1行のみ取得するメソッド
  public function fetch($sql, $params = array())
  {
    return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);

    // PDO::FETCH_ASSOC定数：連想配列で結果を受け取る指定
  }

  // 全ての行を取得するメソッド
  public function fetchAll($sql, $params = array())
  {
    return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
  }
}