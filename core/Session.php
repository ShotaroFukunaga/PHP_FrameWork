<?php

/*
セッション情報を管理するクラス、$_SESSION変数のラッパークラス
*/
class Session
{
  protected static $sessionStarted = false;
  protected static $sessionIdRegenerated = false;

  public function __construct()
  {
    if(!self::$sessionStarted){//falseだった場合
      session_start();

      self::$sessionStarted = true;
    }
    // ~session_start()~
    // セッションの作成や、クッキーなどから受け取ったセッションIDを元にセッションの復元を行う関数

    // ２回以上Sessionクラスが生成された場合に複数回session_startが呼ばれないよう、静的プロパティを使っている
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
    if(!self::$sessionIdRegenerated){
      session_regenerate_id($destroy);

      self::$sessionIdRegenerated = true;
    }

    
  }

  public function setAuthenticated($bool)
  {
    $this->set('_authenticated', (bool)$bool);

    $this->regenerate();
  }

  public function isAuthenticated()
  {
    return $this->get('authenticated', false);
  }
}