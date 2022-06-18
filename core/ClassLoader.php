<?php

class ClassLoader
{
  // クラス自身と親子関係にあるクラスのみアクセスが可能
  protected $dirs;

  /* ~コールバック関数とは~
  引数に指定された関数名を呼び出し実行する関数のこと
  */

  // phpにオートローダクラスを登録する処理
  // spl_autoload_register()関数 => 指定した関数を__autoload()に登録する
  public function register()
  {
    spl_autoload_register(array($this, 'loadClass'));
  }

  // オートロードの対象とするディレクトリのフルパスをdirsプロパティに追加する
  public function registerDir($dir)
  {
    $this->dirs[] = $dir;
  }

  // $dirsプロパティに設定されたディレクトリから、引数で指定されたクラスを探す
  // is_readableでクラスファイルが見つかった場合はrequireで見つけたファイルを読み込む
  public function loadClass($class)
  {
    foreach($this->dirs as $dir){
      $file = $dir . '/' . $class . '.php';
      if(is_readable($file)){
        require $file;

        return;
      }
    }
  }
}
