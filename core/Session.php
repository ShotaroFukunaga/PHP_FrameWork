<?php

/*
セッション情報を管理するクラス、$_SESSION変数のラッパークラス
・~selfは自クラスを表す~
static変数、クラス定数などはインスタンス化せずに使用するため
・~thisは自身のオブジェクトを指す~
作成したインスタンスの変数やメソッドにアクセスするため
*/
class Session
{

  //２回以上Sessionクラスが生成された場合に複数回session_startが呼ばれないよう、静的プロパティを使っている
  protected static $sessionStarted = false;
  //セッションの再会
  protected static $sessionIdRegenerated = false;

  public function __construct()
  {
    if(!self::$sessionStarted){//falseだった場合
      session_start();
      self::$sessionStarted = true;
    }
    /*~session_start()~*
      セッションの作成や、クッキーなどから受け取った
      セッションIDを元にセッションの復元を行う関数
    */
  }

  // セッションの設定
  public function set($name, $value)
  {
    $_SESSION[$name] = $value;
  }
  // セッションの取得
  public function get($name, $default = null)
  {
    if(isset($_SESSION[$name])){
      return $_SESSION[$name];
    }
    return $default;
  }
  // _SESSIONから指定した値を削除
  public function remove($name)
  {
    unset($_SESSION[$name]);
  }
  // _SESSIONを空にするメソッド
  public function clear()
  {
    $_SESSION = array();
  }

  // セッションidを新しく発行する
  public function regenerate($destroy = true)
  {
    if(!self::$sessionIdRegenerated){//falseだった場合
      session_regenerate_id($destroy);

      //一度のリクエスト中に複数回呼び出されることがないように静的プロパティでチェック
      self::$sessionIdRegenerated = true;

      /*~session_regenerate_id()~* 
      現在のセッションIDを新しく生成したものと置き換える
      https://www.php.net/manual/ja/function.session-regenerate-id.php
      */
    }
  }

  /* ログイン状態の制御 */
  //_authenticatedというキーでログインしているかどうかのフラグを格納
  public function setAuthenticated($bool)
  {
    $this->set('_authenticated', (bool)$bool);//タイプヒントでbool型の変数
    //タイプヒンティング：https://ts0818.hatenablog.com/entry/2015/10/06/012325

    $this->regenerate();
    //セッションidを更新
  }

  //_authenticatedというキーでログインしているか
  public function isAuthenticated()
  {
    return $this->get('_authenticated', false);
  }
}