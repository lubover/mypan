<?php

//模版引擎2200707 格式化
!defined('VIEW_PATH') && define('VIEW_PATH', 'view/');
class view
{
    private $_view = array('file' => '', 'data' => array());
    public static $_pos = null;
    public static $_section = null;

    public static function load($file, $set = null)
    {
        if (is_int(strripos($file, '..'))) {
            die("error view file name:$file");
        }
        $file = str_replace('.', '/', $file);

        return new view($file, $set);
    }

    public function __construct($file, $set = null)
    {
        if (isset(self::$_pos)) {
            $this->_view['parent'] = self::$_pos;
            $this->with($this->_view['parent']->_view['data']);
        }
        $this->_view['file'] = $file;
        $this->with($set);
    }

    public function with($name, $value = null)
    {
        if (is_array($name)) {
            $this->_view['data'] = array_merge($this->_view['data'], $name);
        } elseif (is_string($name)) {
            $this->_view['data'][$name] = $value;
        }

        return $this;
    }

    public function __toString()
    {
        self::$_pos = $this;

        $this->_view['file'] = VIEW_PATH.$this->_view['file'].'.php';
        if (!file_exists($this->_view['file'])) {
            self::to404('404');
        }

        extract($this->_view['data']);

        ob_start();
        include $this->_view['file'];

        if (isset($this->_view['layout'])) {
            echo $this->_view['layout'].ob_get_clean();
        }

        self::$_pos = isset($this->_view['parent']) ? $this->_view['parent'] : null;

        $return_str = ob_get_clean();

        return $return_str;
    }

    public function show()
    {
        echo $this;
    }

    public static function layout($file)
    {
        ob_start();
        $view_layout = self::load($file);
        self::$_pos->_view['layout'] = $view_layout;
    }

    public static function section($name)
    {
        if (isset(self::$_pos->_view['section'][$name])) {
            echo self::$_pos->_view['section'][$name];
        } elseif (isset(self::$_pos->_view['parent']->_view['section'][$name])) {
            echo self::$_pos->_view['parent']->_view['section'][$name];
        }
    }

    public static function begin($name)
    {
        self::$_section = $name;
        ob_start();
    }

    public static function end()
    {
        self::$_pos->_view['section'][self::$_section] = ob_get_clean();
        self::$_section = null;
    }

    public static function direct($loction)
    {
        header("Location:$loction");
        exit();
    }

    public static function abort()
    {
        header('HTTP/1.1 502 Bad Gateway');
        exit();
    }

    public static function to404()
    {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
}
