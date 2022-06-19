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

  public function __construct($debug = false)
  {
    $this->setDebugMode($debug);
    $this->initialize();
    $this->configure();
  }

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
  }

  // クラスの初期化処理
  protected function initialize()
  {
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->db_manager = new DbManager();
    $this->router = new Router($this->registerRoutes());
  }

  // 個別のアプリケーション設定用メソッド
  protected function configure()
  {

  }

  //アプリケーションのルートディレクトリへのパスを返すメソッド、
  // アプリケーションごとに設定するように抽象メソッドとして定義
  abstract public function getRootDir();

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

  /*
  コントローラーの呼び出しと実行
  全体の処理の流れを管理する、コントローラーを呼び出してアクションを実行する処理
  */

  //Routerからコントローラーを特定しレスポンスの送信を行うまで担当
  public function run()
  {
    try{
      //Routerクラスのresolve()メソッドを呼び出してルーティングパラメータを取得し、
      //コントローラー名とアクション名を特定する
      $params = $this->router->resolve($this->request->getPathInfo());
      if($params === false){
        throw new HttpNotFoundException('No route found for ' . $this->request->getPathInfo());
      }

      $controller = $params['controller'];
      $action = $params['action'];

      // 下のメソッドで実行する
      $this->runAction($controller, $action, $params);
    }catch(HttpNotFoundException $e){
      $this->render404Page($e);
    }

    $this->response->send();
  }

  protected function render404Page($e)
  {
    $this->response->setStatusCode(404, 'Not Found');
    $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

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
  }

  // 実際にアクションを実行する担当
  public function runAction($controller_name, $action, $params = array())
  {
    $controller_class = ucfirst($controller_name) . 'Controller';

    $controller = $this->findController($controller_class);
    if($controller === false){
      throw new HttpNotFoundException($controller_class . 'controller is not found.');
    }

    $content = $controller->run($action, $params);

    $this->response->setContent($content);

    // ~ucfirstメソッド~
    //先頭の文字を大文字に変える関数
    // ルーティングでは小文字のため
  }

  // メソッドの中でコントローラークラスを生成する担当
  public function findController($controller_class)
  {
    if(!class_exists($controller_class)){//コントローラークラスが読み込まれていない場合、クラスファイルを読み込む
      $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
      // ディレクトリ/コントローラー名/.php
      if(!is_readable($controller_file)){
        return false;//なければ
      }else{
        require_once $controller_file;//あれば読み込む

        if(!class_exists($controller_class)){
          return false;
        }
      }
    }

    return new $controller_class($this);//

    // ~class_exists()~
    // クラスが定義済みかどうかを確認する
  }

}