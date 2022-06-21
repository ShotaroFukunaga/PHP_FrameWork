<?php
// ユーザーのリクエスト情報を制御するクラス
// 主な機能はGET/POSTの判定、値の取得、リクエストされたURLの取得、サーバーのホスト名やSSLでのアクセスかどうかの判定など

// $_SERVERとは
// スーパーグローバル変数の一つ、'REQUEST_METHOD'や'HTTP_HOST'などのキーを持つ連想配列

class Request
{
  //HTTPメソッドがPOSTかどうかを判定するメソッド
  public function isPost()
  {// 'REQUEST_METHOD'がPOSTかどうかで判定する
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
      return true;
    }
    return false;
  }

  //$_GET変数の値を取得する、無ければデフォルト値を返す
  public function getGet($name, $default = null)
  {
    if(isset($_GET[$name])){
      return $_GET[$name];
    }
    return $default;
    /* ~isset()~
      変数に値が入っていればtrueを返す
    */
  }

  //$_POST変数の値を取得する、無ければデフォルト値を返す
  public function getPost($name, $default = null)
  {
    if(isset($_POST[$name])){
      return $_POST[$name];
    }
    return $default;
  }

  // サーバーのホスト名を取得するメソッド
  public function getHost()
  {
    if(!empty($_SERVER['HTTP_HOST'])){
      return $_SERVER['HTTP_HOST'];
    }
    return $_SERVER['SERVER_NAME'];
    //$_SERVER['HTTP_HOST']リクエストヘッダにはホスト名が格納されている。
    //含まれていなければApache側の$_SERVER['SERVER_NAME']のホスト名を返す。
  }

  // HTTPSでアクセスされたかどうかの判定を行う
  public function isSsl()
  {
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
      return true;
    }

    return false;
    // HTTPSでアクセスされた際に$_SERVER['HTTPS']の値がonになる
    // 変数の値を取得してonが格納されているか比較する
  }

/****** 仮 ********/
// リクエストがhttp://example.com/foo/bar/listの場合
// REQUEST_URI: /foo/bar/list
// SCRIPT_NAME: /foo/bar/index.php
//    ベースURL: /foo/bar
//   PATH_INFO: /list

  // リクエストされたURLの情報が格納されているメソッド
  public function getRequestUri()
  {
    return $_SERVER['REQUEST_URI'];//:/foo/bar/listまたは/foo/bar/index.php
  }

  //ベースURLを取得する
  public function getBaseUrl()
  {
    $script_name = $_SERVER['SCRIPT_NAME'];//:/foo/bar/index.php

    $request_uri = $this->getRequestUri();//:/foo/bar/list

    if(0 === strpos($request_uri, $script_name)){//フロントコントローラーが含まれる場合:/foo/bar/index.php/list
      return $script_name;
    }else if(0 === strpos($request_uri, dirname($script_name))){//フロントコントローラーが含まれない場合:/foo/bar/list =? /foo/bar/[index.php]ここを削除
    //  ↑dirname()にscript_nameの値を渡したら親ディレクトリのパスを取得する、その際に'index.php'が省略される
      return rtrim(dirname($script_name), '/');//:/foo/bar/<-ここのスラッシュ
    }
    
    return '';

    /* ~strpos~ *
      第一引数の検索対象の文字列から
      第二引数で指定した文字列が最初に出現する位置を探す
      https://techacademy.jp/magazine/11704
    */

    // ~dirname()~
    // ファイルのパスから指定したディレクトリ分遡って親ディレクトリのパスを返すメソッド

    /* ~rtrim()~*
     * 第一引数の文字列から、第二引数に指定した文字を
     * 右側から取り除く
     */
  }
  
  //PATH_INFOを返す
  public function getPathInfo()//REQUEST_URIからベースURLを取り除いた値を返す
  {
    $base_url = $this->getBaseUrl();
    $request_uri = $this->getRequestUri();

    //getパラメーターを取り除く処理
    if(false !== ($pos = strpos($request_uri, '?'))){//'?'をもとにgetパラメーターが何文字目からあるのかをstrposを使って数字で返す、無ければ0としてFALSEになる
      $request_uri = substr($request_uri, 0, $pos);//0文字目から$pos文字目までを取得
    }

  // GETパラメータを除いたREQUEST_URIからベースURL部分を取り除きPATH_INFOの値として返す
    $path_info = (string)substr($request_uri, strlen($base_url));

    return $path_info;

    /*
      /index.php/list?foo=bar //7文字目
      /index.php/list         //0文字目から7文字目までを取得する
      /list                   //$request_uri文字列からstrlen文字目~終端まで(第3引数を指定していないため)を取得
    */

    // ~substr~
    //第一引数で指定した文字列のうち、第二引数で指定した位置から、第三引数で指定した文字数ぶん取得する

    /* strlen()*
     * 文字列の長さを返す
     */

  }

}