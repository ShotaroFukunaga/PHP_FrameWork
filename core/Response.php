<?php
/*
 * HTTPヘッダとHTMLなどのコンテンツを返す
 * HTTPヘッダを送信するためにheader()関数を使って送信する
 */
class Response
{
  protected $content;
  protected $status_code = 200;
  protected $status_text = 'OK';
  protected $http_headers = array();

  //上記プロパティに設定された値を元にレスポンスの送信を行う
  public function send()
  {
    // HTTPプロトコルのバージョンを指定、ステータスコード、テキスト
    header('HTTP/1.1' . $this->status_code . ' ' . $this->status_text);//

    foreach($this->http_headers as $name => $value){
      header($name . ': ' . $value);
    }

    echo $this->content;//レスポンスの内容をechoを用いて出力するだけで送信される

    // header関数
    // https://www.php.net/manual/ja/function.header.php
  }

  // HTMLなど実際にクライアントに返す内容を格納するメソッド
  public function setContent($content)
  {
    $this->content = $content;
  }

  // ステータスコードを格納するメソッド。ステータスコードとはレスポンスがどのような状態にあるかを表す
  // 404 Not Foundや、内部エラーを表す500 Internal Server Errorなど
  public function setStatusCode($status_code, $status_text = '')
  {
    $this->status_code = $status_code;
    $this->status_text = $status_text;
  }

  // HTTPヘッダを格納するプロパティ、ヘッダの名前をキーに、ヘッダの内容を値にして連想配列形式で格納
  public function setHttpHeader($name, $value)
  {
    $this->http_headers[$name] = $value;
  }
  
}