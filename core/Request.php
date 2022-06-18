<?php
// ユーザーのリクエスト情報を制御するクラス
// 主な機能はGET/POSTの判定、値の取得、リクエストされたURLの取得、サーバーのホスト名やSSLでのアクセスかどうかの判定など

// $_SERVERとは
// スーパーグローバル変数の一つ、'REQUEST_METHOD'や'HTTP_HOST'などのキーを持つ連想配列

class Request
{
  //HTTPメソッドがPOSTかどうかを判定するメソッド
  // 'REQUEST_METHOD'がPOSTかどうかで判定する
  public function isPost()
  {
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
      return true;
    }
    return false;
  }

  public function getGet($name, $default = null)
  {
    if(isset($_GET[$name])){
      return $_GET[$name];
    }
    return $default;
  }

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
  }

  // HTTPSでアクセスされたかどうかの判定を行う
  // 'HTTPS'にonが含まれているか
  public function isSsl()
  {
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
      return true;
    }
    
    return false;
  }

  // リクエストされたURLの情報が格納されているメソッド
  // 'REQUEST_URI'はURLのホスト部分以降の値が格納。URLの制御はこの値をもとに行う
  public function getRequestUri()
  {
    return $_SERVER['REQUEST_URI'];
  }

  // ~SCRIPT_NAMEとREQUEST_URI~
  // リクエストが http://example.com/foo/bar/listだとした場合
  // REQUEST_URI : ホスト部分より後の値が格納 -> /list
  // SCRIPT_NAME : フロントコントローラーまでのパス -> foo/bar/index.php



  public function getBaseUrl()
  {
    $script_name = $_SERVER['SCRIPT_NAME'];//ホスト部分

    $request_uri = $this->getRequestUri();//上記以降（listなど、getパラメも含む）

    if(0 === strpos($request_uri, $script_name)){
      return $script_name;//フロントコントローラーが指定されている場合
    }else if(0 === strpos($request_uri, dirname($script_name))){
    // script_nameの値を渡してindex.phpを省略
      return rtrim(dirname($script_name), '/');//フロントコントローラーが省略されている場合
    }
    
    return '';
    // ~strpos~
    // 第一引数の文字列から第二引数に指定した文字列が最初に出現する位置を調べる関数

    // ~dirname()~
    // ファイルのパスから指定したディレクトリ分遡って親ディレクトリのパスを返すメソッド
  }
  
  //
  public function getPathInfo()
  {
    $base_url = $this->getBaseUrl();
    $request_uri = $this->getRequestUri();

    //getパラメーターを取り除く処理
    if(false !== ($pos = strpos($request_uri, '?'))){//getパラを含むための'?'があった場合
      $request_uri = substr($request_uri, 0, $pos);
    }

    $path_info = (string)substr($request_uri, strlen($base_url));

    return $path_info;

    // ~substr~
    //第一引数で指定した文字列のうち、第二引数で指定した位置から、第三引数で指定した文字数ぶん取得する

  }

}