<?php
/**
 * LY框架核心
 */

/*
 * ------------------------------------------------------
 *  框架版本
 * ------------------------------------------------------
 */
define('LY_VERSION','1.0.0');

/*
 * ------------------------------------------------------
 *  加载配置文件
 * ------------------------------------------------------
 */
require(APP.'config.php');

/*
 * ------------------------------------------------------
 *  获取PATH_INFO URI
 * ------------------------------------------------------
 */
$uri = '';
if(isset($_SERVER['PATH_INFO'])){
    $uri = $_SERVER['PATH_INFO'];
}else if(isset($_SERVER['ORIG_PATH_INFO'])){
    $uri = $_SERVER['ORIG_PATH_INFO'];
}else if(isset($_SERVER['QUERY_STRING'])){
    $queryString = explode('&',$_SERVER['QUERY_STRING']);
    $uri = $queryString[0];
}

/*
 * ------------------------------------------------------
 *  PATH_INFO URI友好化
 * ------------------------------------------------------
 */
renderUrl();

/*
 * ------------------------------------------------------
 *  数据过滤
 * ------------------------------------------------------
 */
if(get_magic_quotes_gpc()){
    $_GET = stripslashesDeep($_GET);
    $_POST = stripslashesDeep($_POST);
    $_COOKIE = stripslashesDeep($_COOKIE);
}

/*
 * ------------------------------------------------------
 *  路由配置
 * ------------------------------------------------------
 */
if(isset($config['route']) && is_array($config['route']) && !empty($config['route'])){
    foreach($config['route'] as $key => $value){
        $key = str_replace(array(':any',':num'), array('([^\/.]+)','([0-9]+)'), $key);
        if(preg_match('#^'.$key.'#',$uri)){
            $uri = preg_replace('#^'.$key.'#', $value, $uri);
        }
    }
}

/*
 * ------------------------------------------------------
 *  获取URI段
 * ------------------------------------------------------
 */
$uri = trim($uri,'/');
$segments = explode('/',$uri);

/*
 * ------------------------------------------------------
 *  载入控制器下所有目录初始化架构文件__construct.php
 * ------------------------------------------------------
 */
if(isset($segments) && is_array($segments) && !empty($segments)){
    $loadDir = $multipleDir = '';
    foreach($segments as $dir){
        $loadDir .= $dir.DIRECTORY_SEPARATOR;
        if(file_exists(APP.'c'.DIRECTORY_SEPARATOR.$loadDir.'__construct.php')){
            require(APP.'c'.DIRECTORY_SEPARATOR.$loadDir.'__construct.php');
            $multipleDir .= array_shift($segments).DIRECTORY_SEPARATOR;
        }else{
            break;
        }
    }
}else{
    exit('params error.');
}

/*
 * ------------------------------------------------------
 *  调用请求
 * ------------------------------------------------------
 */
$class = isset($segments[0]) ? $segments[0] : 'home';
$method = isset($segments[1]) ? $segments[1] : 'index';
if(!file_exists(APP.'c'.DIRECTORY_SEPARATOR.$multipleDir.$class.'.php')){
    show_404(APP.'c'.DIRECTORY_SEPARATOR.$multipleDir.$class.'.php');
}
require(APP.'c'.DIRECTORY_SEPARATOR.$multipleDir.$class.'.php');
if(!class_exists($class)){
    show_404($class.' class doesn\'t exists.');
}
if(!method_exists($class, $method)){
    show_404($class.'-'.$method.' doesn\'t exists.');
}
$LY = new $class();
call_user_func_array(array(&$LY,$method), array_slice($segments, 2));


/*
 * ------------------------------------------------------
 *  框架核心函数
 * ------------------------------------------------------
 */

// --------------------------------------------------------------------
/**
 * 利于SEO的URL
 * @access  public
 * @return  void
 * @author  菜鸟CK
 */   
function renderUrl(){
    global $uri;
    if(strpos($uri,'.') !== false or $_SERVER['QUERY_STRING'] or substr($uri, -1) == '/' or empty($uri)){
        return;
    }
    header("HTTP/1.1 301 Moved Permanently");
    header('Location:'.$_SERVER['REQUEST_URI'].'/');
    exit(0);
}

// --------------------------------------------------------------------
/**
 * 输入安全过滤
 * @access  public
 * @param   mixed
 * @return  mixed
 * @author  菜鸟CK
 */  
function stripslashesDeep($value){
    return is_array($value) ? array_map('stripslashesDeep', $value) : (isset($value) ? stripslashes($value) : null);
}

// --------------------------------------------------------------------
/**
 * 显示404错误页
 * @access  public
 * @param   mixed
 * @return  void
 * @author  菜鸟CK
 */
function show_404($msg = ''){
  header("HTTP/1.1 404 Not Found");
  echo '404:'.$msg;
  exit(1);
}

// --------------------------------------------------------------------
/**
 * 友好的浏览器输出
 * @access  public
 * @param   mixed
 * @return  void
 * @author  菜鸟CK
 */
function dump($var,$exit = false){
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
    if($exit){
        exit(0);
    }
}