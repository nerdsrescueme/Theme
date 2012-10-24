<?php

namespace Theme;

use Nerd\Arr
  , Nerd\Asset\Collection as AssetCollection;

class Theme implements \Nerd\Design\Initializable
{
    protected static $config;

    protected static $themes = [];

    public static function __initialize()
    {
        static::$config = \Nerd\Config::get('theme::theme', []);

        // List themes from root directory
        $handle = opendir(static::$config['root']);

        while (($dir = readdir($handle)) !== false) {
            if (substr($dir, 0, 1) != '.') {
                static::$themes[$dir] = ucfirst($dir);
            }
        }

        closedir($handle);
    }

    public static function instance($theme = 'default', array $config = [])
    {
        return new static($theme, $config);
    }

    public static function all()
    {

    }

    protected $info = [];
    public $name;
    public $path;
    public $public = false;
    public $uri;

    public $js;
    public $css;

    public function __construct($theme = 'default', array $config = [])
    {
        if (!isset(static::$themes[$theme])) {
            throw new Exception("The requested theme [$theme] does not exist within {$config['root']}");
        }

        $this->name   = static::$themes[$theme];
        $this->path   = $this->config('root').DS.$theme;
        $this->public = strpos($this->path, \Nerd\DOCROOT) !== false;
        $this->uri    = trim(str_replace(\Nerd\DOCROOT, '', $this->path), DS);
        $this->js     = new AssetCollection([], $this->uri.DS.'assets');
        $this->css    = new AssetCollection([], $this->uri.DS.'assets');

        // Attempt to load theme information file...
        if ($this->config('info.enabled', false)) {
            $infoFile = $this->path.DS.$this->config('info.file', 'theme.json');

            if (file_exists($infoFile)) {
                $parser = \Nerd\Format::instance($this->config('info.format', 'json'));
                $data   = file_get_contents($infoFile);
                $this->info = $parser->to($data);
            }
        }
    }

    public function config($key, $default = null)
    {
        return Arr::get(static::$config, $key, $default);
    }

    public function info($key, $default = null)
    {
        return Arr::get($this->info, $key, $default);
    }
}
