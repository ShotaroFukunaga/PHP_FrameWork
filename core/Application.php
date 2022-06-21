<?php
// ~abstract~
// 抽象クラスとは抽象メソッドが1つ以上含まれるクラス
// この抽象クラスを継承したサブクラスは、必ず抽象メソッドをオーバライドして定義を行わなければいけない
abstract class Application
{
  protected $debug = false;
  protected $request;
  protected $response;
  protected $session;
  protected $db_manager;
  protected $login_action = array();

  public function __construct($debug = false)
  {
    $this->setDebugMode($debug);
    $this->initialize();
    $this->configure();
  }

  //デバックモードに応じてエラーの表示を変更
  protected function setDebugMode($debug)
  {
    if($debug){
      $this->debug = true;
      ini_set('display_errors', 1);
      error_reporting(-1);
    }else{
      $this->debug = false;
      ini_set('display_errors', 0);
    }

    //ini_set() — 設定オプションの値を設定する

    /*error_reporting()*
    出力する PHP エラーの種類を設定する:https://www.php.net/manual/ja/function.error-reporting.php
    全ての PHP エラーを表示するerror_reporting(-1);
    */
  }

  // クラスの初期化処理
  protected function initialize()
  {
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->db_manager = new DbManager();
    //インスタンスを作成するのにルーティング定義用の配列が必要なのでregisterRoutes(このクラス)を呼び出す
    $this->router = new Router($this->registerRoutes());
  }

  // 個別のアプリケーション設定用メソッド
  protected function configure()
  {

  }

  //~abstract:抽象メソッド~ 継承先で必ず定義しないとPHPが怒る
  //アプリケーションのルートディレクトリへのパスを返すメソッド、を継承先で定義
  abstract public function getRootDir();
  //抽象メソッドとしておいて継承先でルーティング定義配列の定義を行う
  abstract protected function registerRoutes();


  public function isDebugMode()
  {
    return $this->debug;
  }

  public function getRequest()
  {
    return $this->request;
  }

  public function getResponse()
  {
    return $this->response;
  }

  public function getSession()
  {
    return $this->session;
  }

  public function getDbManager()
  {
    return $this->db_manager;
  }

  public function getControllerDir()
  {
    return $this->getRootDir() . '/controllers';
  }

  public function getViewDir()
  {
    return $this->getRootDir() . '/views';
  }

  public function getModelDir()
  {
    return $this->getRootDir() . '/models';
  }

  public function getWebDir()
  {
    return $this->getRootDir() . '/web';
  }

  /*コントローラーの呼び出しとアクションの実行*/
  //Routerからコントローラーを特定しレスポンスの送信を行う
  public function run()
  {
    try{
      //Routerクラスのresolve()でコントローラー名とアクション名を取得し特定する
      $params = $this->router->resolve($this->request->getPathInfo());
      if($params === false){
        throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
      }

      $controller = $params['controller'];
      $action = $params['action'];

      // 下のメソッドで実行する
      $this->runAction($controller, $action, $params);
    }catch(HttpNotFoundException $e){//ページがなければ
      $this->render404Page($e);
    }catch(UnauthorizedActionException $e){//ログインしていなければ
      list($controller, $action) = $this->login_action;
      $this->runAction($controller, $action);
    }
    /*try/catch*
      発生した例外を補足する構文
    */

    $this->response->send();
  }

  // 上記関数で特定ができればアクションを実行
  public function runAction($controller_name, $action, $params = array())
  {//1.user 2.show 3.[user] => ':controller/show' みたいな感じな引数
    $controller_class = ucfirst($controller_name) . 'Controller';//先頭１文字大文字して連結

    $controller = $this->findController($controller_class);
    if($controller === false){//falseだったら
      throw new HttpNotFoundException($controller_class . 'controller is not found.');
    }

    $content = $controller->run($action, $params);
    //帰ってきたコントローラーインスタンスの関数run(実際にアクションする)を実行

    $this->response->setContent($content);
    //responseクラスのsetContent(クライアントに返す内容を格納する関数)

    // ~ucfirstメソッド~
    //先頭の文字を大文字に変える関数
    // ルーティングでは小文字のため
  }

  //コントローラークラスが読み込まれていない場合、クラスファイルを読み込み、インスタンスを作成して返す
  public function findController($controller_class)
  {
    if(!class_exists($controller_class)){//コントローラークラスが読み込まれていない場合、クラスファイルを読み込む
      $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
      // ディレクトリ/コントローラー名/.php：User/UserController.php
      if(!is_readable($controller_file)){
        return false;//なければ
      }else{
        require_once $controller_file;//あれば読み込む

        if(!class_exists($controller_class)){
          return false;//なんか読み込めなければ
        }
      }
    }

    return new $controller_class($this);//new UserController(applicationクラス)

    // ~class_exists()~
    // クラスが定義済みかどうかを確認する
  }

  //ページが存在しない例外
  protected function render404Page($e)
  {
    $this->response->setStatusCode(404, 'Not Found');
    $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';//デバックモードならエラーメッセージ：そうでないなら左のメッセージ
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');//文字列にエスケープ

    $this->response->setContent(<<<EOF
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Trasitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>404</title>
      </head>
      <body>
        {$message}
      </body>
      </html>
      EOF
    );
    /* "<<<" ヒアドキュメント*
      長い文字列を変数に代入する場合に使用する
    */
  }

}