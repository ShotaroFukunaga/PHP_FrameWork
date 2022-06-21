<?php
//ClassLoaderと対象ディレクトリをオートロードに登録するファイル
require 'core/ClassLoader.php';

$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__).'/core');//オートロード対象ディレクトリ
$loader->registerDir(dirname(__FILE__).'/models');//オートロード対象ディレクトリ
$loader->register();//オートロードに登録