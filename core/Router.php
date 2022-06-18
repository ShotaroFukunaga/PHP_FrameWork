<?php
// ルーティング定義配列とPATH_INFOを受け取りルーティングパラメーターを特定する役割
class Router
{
  protected $routes;

  public function __construct($definitions)
  {
    $this->routes = $this->compileRoutes($definitions);
  }

  // 受け取ったルーティング定義配列中の動的パラメーターを正規表現で扱える形式に変換する関数
  public function compileRoutes($definitions)
  {
    $routes = array();

    // foreach(配列 as キー => バリュー)
    foreach($definitions as $url => $params){
      $tokens = explode('/', ltrim($url, '/'));//スラッシュごとに分割
      foreach($tokens as $i => $token){
        if(0 === strpos($token, ':')){// ':''があった場合
          $name = substr($token,1);//':'以降の文字列を取り出す
          $token = '(?P<' . $name . '>[^/]+)';//(:P<名前>パターン)で指定した名前で値を取得できる
        }
        $tokens[$i] = $token;
      }

      $pattern = '/' . implode('/', $tokens);//分割したURLを再度繋げる
      $routes[$pattern] = $params;
    }

    return $routes;

    // ~ltrim関数~
    // 文字列の最初から空白や指定した文字を取り除く

    // ~explode関数~
    // 文字列をスラッシュごとに分割する
  }

  // 変換済みのルーティングとPATH_INFOのマッチングを行いマッチすればarray_merge関数で連結する
  public function resolve($path_info)
  {
    if('/' !== substr($path_info, 0, 1)){
      $path_info = '/' . $path_info;
    }

    foreach($this->routes as $pattern => $params){
      if(preg_match('#^' . $pattern . '$#', $path_info, $matches)){
        $params = array_merge($params, $matches);

        return $params;
      }
    }

    return false;
  }
}