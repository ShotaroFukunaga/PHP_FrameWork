<?php

class view
{
  protected $base_dir;//viewファイルを格納するディレクトリまでの絶対パス
  protected $defaults;//ビューファイルに変数を渡す際にデフォルト値を設定
  protected $layout_variables = array();

  public function __construct($base_dir, $defaults = array())
  {
    $this->base_dir = $base_dir;
    $this->defaults = $defaults;
  }

  //$layout_variableプロパティに値を設定するメソッド
  public function setLayoutVar($name, $value)
  {
    $this->layout_variables[$name] = $value;
  }

  // viewファイル内で別のviewファイルの読み込む
  //メソッド内で定義している変数は全てアンダーバーをつけ、変数展開時の名前衝突を避ける
  public function render($_path, $_variables = array(), $_layout = false)
  {//第一：viewファイルのパス、第二：viewファイルに渡す変数、第三：layoutファイル名の指定（Controllerクラスから呼び出された時だけ）
    $_file = $this->base_dir . '/' . $_path . '.php';

    extract(array_merge($this->defaults, $_variables));//配列を連結

    ob_start();//アウトプットバッファリングを開始
    ob_implicit_flush(0);//バッファの上限でバッファの内容を吐き出す機能、0でoff

    require $_file;

    $content = ob_get_clean();//バッファの内容を取得し、バッファをクリーン、アウトプットバッファを終了

    if($_layout){//レイアウト名が指定されている場合、array_mergeして再度render()メソッドを読びだす
      $content = $this->render($_layout,
        array_merge($this->layout_variables, array(
          '_content' => $content,//変数に_contentというキーで先に読み込んだviewファイルの内容を渡している
          // $_content変数に展開され内容を出力することで1つのHTMLファイルになる
          )
        )
      );
    }

    return $content;

    /* ~extract()~
      引数に連想配列を指定し、連想配列のキーを変数名に、連想配列の値を変数の値として展開する
    */

    /*アウトプットバッファリング
      出力バッファを溜めておき、出力したいタイミングで出力させる機能
      echoやrequireを使用した時点で出力されてしまうので
      何らかの処理を実行する前に文字列が出力されてしまう。
      なのでバッファとして文字列を溜めて、処理を実行した後に文字列として表示する
    */
  }

  //viewファイルの可読性を上げるためにhtmlspecialchars()関数をラッピングした関数
  public function escape($string)
  {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }

}