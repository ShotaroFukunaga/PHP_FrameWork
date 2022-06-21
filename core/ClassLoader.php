<?php
/* ~オートロードを設定するクラス~
  オートロードとは定義されていないクラスを使おうとした時に、
  指定されたオートロード関数を使ってクラスを読み込む仕組み。
  require_onceを使ってクラスファイルを読み込まなくて良くなる。
*/

/* 機能要件
  ・PHPにオートローダクラスを登録する
  ・オートローダが実行された際にくらすファイルを読み込む
  ・クラスは「クラス名.php」という形式で保存
  ・クラスはcoreディレクト(フレームワークのクラス)
      またはmodelディレクトリ(モデルのクラス)に配置する
*/
class ClassLoader
{
  // protected : クラス自身と親子関係にあるクラスのみアクセスが可能
  protected $dirs;

  /* ~コールバック関数とは~
  引数に指定された関数名を呼び出し実行する関数のこと
  */

  // オートロード発火時に下記関数に登録した関数が実行される
  // spl_autoload_register()関数 => 指定した関数を__autoload()に登録する
  public function register()
  {
    spl_autoload_register(array($this, 'loadClass'));
  }

  // オートロード対象のディレクトリのフルパスを$dirsプロパティに追加する
  public function registerDir($dir)
  {
    $this->dirs[] = $dir;
  }

  //オートロード対象関数
  // $dirsプロパティに設定されたディレクトリから、引数で指定されたクラスを探す
  public function loadClass($class)
  {
    foreach($this->dirs as $dir){
      $file = $dir . '/' . $class . '.php';// core/Request.phpみたいな形式になる
      if(is_readable($file)){
        require $file;

        return;
      }
    }
    // is_readable：クラスファイルが見つかった場合はrequireで見つけたファイルを読み込む
  }
}
