<?php
// ルーティング定義配列とPATH_INFOを受け取りルーティングパラメーターを特定する役割
class Router
{
  protected $routes;

  public function __construct($definitions)//definitions:定義
  {
    $this->routes = $this->compileRoutes($definitions);
    /*下記のような配列形式で受け取る*
    array(
      '/:controller' => array('action' => 'index'),
      '/item/:action' => array('controller' => 'item'),
    );
    */
  }

  //ルーティング配列の動的パラメーターを正規表現で扱える形式に変換する
  public function compileRoutes($definitions)
  {
    $routes = array();

    // foreach(配列 as キー => バリュー)
    foreach($definitions as $url => $params){
      $tokens = explode('/', ltrim($url, '/'));//一度スラッシュごとに分割する
      foreach($tokens as $i => $token){//分割した$url
        if(0 === strpos($token, ':')){// ':'があった場合0を返す
          $name = substr($token,1);//先頭の１文字、つまり':'以降の文字列を取り出す
          $token = '(?P<' . $name . '>[^/]+)';//(:P<名前>パターン)で正規表現で使える形式に変換する
        }
        $tokens[$i] = $token;//変換した動的パラメータを再度、配列に格納する
      }

      $pattern = '/' . implode('/', $tokens);//分割したURLを再度繋げる、文字列の先頭にも'/'をつける
      //注意！！implodeはPHP7.4で非推奨、PHP8.0に以降は削除されているため、バージョンにより修正が必要
      $routes[$pattern] = $params;//正規表現に変換して連結したURL文字列をキーとして、先ほどのバリューを格納
    }

    return $routes;

    // ~ltrim関数~
    // 文字列の最初から空白や指定した文字を取り除く

    // ~explode関数~
    // 文字列をスラッシュごとに分割する

    // ~implode関数~
    //指定した区切り文字で配列を連結させる
      // $array = ['lastname', 'email', 'phone'];
      // var_dump(implode(",", $array)); // string(20) "lastname,email,phone"
  }

  // 変換済みのルーティングとPATH_INFOのマッチングを行いマッチすればarray_merge関数で連結する
  public function resolve($path_info)
  {
    if('/' !== substr($path_info, 0, 1)){//PATH_INFOの先頭に'/'がなかった場合
      $path_info = '/' . $path_info;// '/'を連結させる
    }

    foreach($this->routes as $pattern => $params){
      if(preg_match('#^' . $pattern . '$#', $path_info, $matches)){//$routesプロパティを正規表現を使ってマッチング、
        $params = array_merge($params, $matches);//$paramsをキーに$matchesがバリュー

        return $params;
      }
    }

    return false;

    /*~preg_match関数~*
      PHPで扱うにはpreg_match()関数を使用する。
      第一引数に検索する文字列、第二引数に入力文字列
      第3引数を指定した場合、検索結果が代入される
    */
  }
}