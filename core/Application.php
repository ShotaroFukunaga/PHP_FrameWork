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
    $params = $this->router->resolve($this->request->getPathInfo());
    if($params === false){
      // todo-A
    }

    $controller = $params['controller'];
    $action = $params['action'];

    $this->runAction($controller, $action, $params);

    $this->response->send();
  }

  // 実際にアクションを実行する担当
  public function runAction($controller_name, $action, $params = array())
  {
    $controller_class = ucfirst($controller_name) . 'Controller';

    $controller = $this->findController($controller_class);
    if($controller === false){
      //todo-B
    }

    $content = $controller->run($action, $params);

    $this->response->setContent($content);
  }

  // メソッドの中でコントローラークラスを生成する担当
  public function findController($controller_class)
  {
    if(!class_exists($controller_class)){
      $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';
      if(!is_readable($controller_file)){
        return false;
      }else{
        require_once $controller_file;

        if(!class_exists($controller_class)){
          return false;
        }
      }
    }

    return new $controller_class($this);
  }

}