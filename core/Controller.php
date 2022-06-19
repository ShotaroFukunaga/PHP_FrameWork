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

  public function __construct($application)
  {
    $this->controller_name = strtolower(substr(get_class($this), 0, -10));
    /*
      get_class($this)で自分自身のクラス名を取得
      -10で"Controller"という文字列を切り取っている
    */

    /* ~get_class()~
      指定したオブジェクトのクラス名を取得する関数
    */

    /* ~strtolower()~
      渡された文字列の大文字を小文字に変換する関数
    */

    //以下オブジェクトを取得
    $this->application = $application;
    $this->request = $application->getRequest();
    $this->response = $application->getResponse();
    $this->session = $application->getSession();
    $this->db_manager = $application->getDbManager();
  }

  // コントローラーに実装されたメソッドに対してアクションを起こす
  public function run($action, $params = array())
  {
    $this->action_name = $action;

    // アクション名
    $action_method = $action . 'Action';
    if(!method_exists($this,$action_method)){//メソッドが存在しているか確認
      $this->forward404();
    }

    if($this->needsAuthentication($action) && !$this->session->isAuthenticated()){
      throw new UnauthorizedActionException();
    }

    $content = $this->$action_method($params);
    //$action_methodを可変変数として使う。
    // 可変変数とは変数の値をそのままメソッド名として使える仕組み
    // run()メソッドの第二引数で受け取ったルーティングパラメータをアクションの引数とする

    return $content;
  }

  protected function needsAuthentication($action)
  {
    if($this->auth_actions === true || (is_array($this->auth_action) && in_array($action, $this->auth_actions))){
      return true;
    }

    return false;
  }

  // https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP
  protected function render($variables = array(), $template = null, $layout = 'layout')
  {
    $defaults = array(
      'request'  => $this->request,
      'base_url' => $this->request->getBaseUrl(),
      'session'  => $this->session,
    );

    $view = new View($this->application->getViewDir(), $defaults);

    if(is_null($template)){
      $template = $this->action_name;
    }

    $path = $this->controller_name . '/' .$template;

    return $view->render($path, $variables, $layout);
  }

  protected function forward404()
  {
    throw new HttpNotFoundException('Forwarded 404 page from '
                                      . $this->controller_name . '/' . $this->action_name);
  }

  // https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP
  protected function redirect($url)
  {
    if(!preg_match('#https?://#', $url)){
      $protocol = $this->request->isSsl() ? 'https://' : 'http://';
      $host = $this->request->getHost();
      $base_url = $this->request->getBaseUrl();

      $url = $protocol . $host . $base_url . $url;
    }

    $this->response->setStatusCode(302, 'Found');
    $this->response->setHttpHeader('Location', $url);
  }

  // トークン作成しサーバー上に保持するセッションを格納する
  // https://read.amazon.co.jp/reader?asin=B00P0UDWQY&ref_=kwl_kr_iv_rec_1&language=ja-JP
  protected function generateCsrfToken($form_name)
  {
    $key = 'csrf_tokens/' . $form_name;
    $tokens = $this->session->get($key, array());
    if(count($tokens) >= 10){
      array_shift($tokens);
    }

    $token = sha1($form_name . session_id() . microtime());
    $tokens[] = $token;

    $this->session->set($key, $tokens);

    return $token;
  }

  // セッション上に格納されているトークンからPOSTされたトークンを探す
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