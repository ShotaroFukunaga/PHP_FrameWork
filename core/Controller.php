<?php

// コントローラで使う抽象機能を実装するクラス
abstract class Controller
{
  protected $controller_name;
  protected $action_name;
  protected $application;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;

  //配列形式でログインが必要なアクションを保持
  protected $auth_actions = array();

  //RequestやResponseクラスを使うため引数のApplicationクラスを取得
  public function __construct($application)
  {
    $this->controller_name = strtolower(substr(get_class($this), 0, -10));
    /*
      get_class($this)で自分自身のクラス名を取得
      -10で"Controller"という後ろの１０文字の文字列を切り取っている
      UserController 変換後-> user
    */

    /* ~get_class()~
      指定したオブジェクトのクラス名を取得する関数
    */

    /* ~strtolower()~
      渡された文字列の大文字を小文字に変換する関数
    */

    //Applicationクラスインスタンスからオブジェクトを取得
    $this->application = $application;//プロパティに設定する必要はないが簡略化のため
    $this->request = $application->getRequest();
    $this->response = $application->getResponse();
    $this->session = $application->getSession();
    $this->db_manager = $application->getDbManager();
  }

  //コントローラーのアクションを実行する
  public function run($action, $params = array())
  {
    $this->action_name = $action;//アクション名をプロパティに格納

    // メソッド名は[アクション名 + Action()]という形式で扱う
    $action_method = $action . 'Action';
    if(!method_exists($this,$action_method)){//アクションの存在をチェック
      $this->forward404();//存在しなければ404エラー画面に遷移
    }

    //戻り値がtrueかつ未ログインの場合
    if($this->needsAuthentication($action) && !$this->session->isAuthenticated()){
      throw new UnauthorizedActionException();//例外を通知、Applicationクラスのrun()メソッドに続く
    }

    $content = $this->$action_method($params);
    //$action_methodを可変変数として使う。
    // 可変変数とは変数の値をそのままメソッド名として使える仕組み
    // 第二引数で受け取ったルーティングパラメータを引数とする

    return $content;
  }

  //ログインが必要かどうかの判定を行うメソッド
  protected function needsAuthentication($action)
  {
    if($this->auth_actions === true || (is_array($this->auth_action) && in_array($action, $this->auth_actions))){
      return true;
    }

    return false;
  }

  // https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP
  //ビューファイルの読み込み処理をラッピングする（個別のアクションから毎回指定するのが面倒なため）
  protected function render($variables = array(), $template = null, $layout = 'layout')
  {
    //Viewクラスのコンストラクタ第二引数に指定するデフォルトの連想配列
    $defaults = array(
      'request'  => $this->request,
      'base_url' => $this->request->getBaseUrl(),
      'session'  => $this->session,
    );

    //Viewクラスのインスタンスを作成、viewsディレクトリへのパスはgetViewDir()で取得
    $view = new View($this->application->getViewDir(), $defaults);

    if(is_null($template)){//テンプレート名が指定されなかった場合
      $template = $this->action_name;//アクション名をテンプレート名とする
    }

    $path = $this->controller_name . '/' .$template;//コントローラー名を先頭にふよ

    return $view->render($path, $variables, $layout);//Viewクラスのrender()メソッドを実行、ビューファイルを読み込みControllerクラスのrender()メソッドの戻り値とする
  }

  //404エラー画面へリダイレクトする
  protected function forward404()
  {
    throw new HttpNotFoundException('Forwarded 404 page from '
                                      . $this->controller_name . '/' . $this->action_name);
  }

  //指定された任意のURLにリダイレクトする,p253
  protected function redirect($url)
  {
    if(!preg_match('#https?://#', $url)){
      $protocol = $this->request->isSsl() ? 'https://' : 'http://';
      $host = $this->request->getHost();
      $base_url = $this->request->getBaseUrl();

      $url = $protocol . $host . $base_url . $url;
    }

    $this->response->setStatusCode(302, 'Found');//302はブラウザにリダイレクトを伝えるためのステータスコード
    $this->response->setHttpHeader('Location', $url);

    /*302コードとPHPリダイレクトの関係*
     *Lacationヘッダは実際にはリダイレクトさせるものでは無く、リダイレクト先のURLを指定するヘッダ
     *PHPではheader()関数にLocationヘッダを指定するとステータスコードを
     *302に自動的に書き換えてくれるためリダイレクトが行われる。この関数では明示的に302を指定している
     */
  }

  // トークン作成し、セッションに格納。したらばトークン返す,p253
  protected function generateCsrfToken($form_name)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());
    if(count($tokens) >= 10){//トークンを最大１０個保持する
      array_shift($tokens);//すでに１０個保持している場合はシフトしてメモリから追い出す
    }

    $token = sha1($form_name . session_id() . microtime());
    $tokens[] = $token;

    $this->session->set($key, $tokens);

    return $token;

    /*~microtime()~*
      UNIXタイムスタンプをマイクロ秒まで返す関数
    */

    /*~session_id()~* 
      現在のセッション ID を取得または設定する
    */

    /*~sha1()~*
      文字列のハッシュを計算する
      https://www.php.net/manual/ja/function.sha1
    */
  }

  // セッション格納トークンとリクエストhiddenトークンを比較し、セッションからトークンをですとろい
  protected function checkCsrfToken($form_name, $token)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());

    if(false !== ($pos = array_search($token, $tokens, true))){
      unset($tokens[$pos]);
      $this->session->set($key, $tokens);

      return true;
    }

    return false;
  }

}