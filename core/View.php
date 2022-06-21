<?php

class view
{
  protected $base_dir;//viewディレクトリまでの絶対パス
  protected $defaults;//ビューファイルに渡す変数にデフォルト値を設定、読み込んだ全てのファイルで利用したい値がある場合このプロパティに設定する
  protected $layout_variables = array();

  //上記プロパティに格納
  public function __construct($base_dir, $defaults = array())
  {
    $this->base_dir = $base_dir;
    $this->defaults = $defaults;
  }

  //Layoutファイルとして設定する
  public function setLayoutVar($name, $value)
  {
    $this->layout_variables[$name] = $value;
  }

  // viewファイル内で別のviewファイルの読み込む
  public function render($_path, $_variables = array(), $_layout = false)//メソッド内の変数は全て'_'を付け、変数展開時の名前衝突を避ける('_'に特別な機能がある訳ではない)
  {//第一：viewファイルのパス、第二：viewファイルに渡す配列、第三：layoutファイル名の指定（Controllerクラスから呼び出された時だけ）
    $_file = $this->base_dir . '/' . $_path . '.php';

    extract(array_merge($this->defaults, $_variables));//配列を連結

    ob_start();//アウトプットバッファリングを開始
    ob_implicit_flush(0);//バッファの上限で内容をゲロする機能、0でoff

    require $_file;//この時点でアウトプットバッファリングしてないと出力されちまう

    $content = ob_get_clean();//バッファの内容を取得し、バッファをクリーン、アウトプットバッファを終了

    if($_layout){//レイアウト名が指定されている場合、
      $content = $this->render($_layout,//下記の引数に修正してrender()メソッドを読びだす
        array_merge($this->layout_variables, array(//array_mergeして,[$layout_variables => ['_content' => $content]]という形式
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