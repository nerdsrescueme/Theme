<?php

/**
 * Theme namespace
 *
 * @package Theme
 * @subpackage Theme
 */
namespace Theme;

use Nerd\Arr
  , Nerd\Asset\Collection as AssetCollection
  , \Nerd\Design\Architectural\MVC\View;

/**
 * Theme Factory Class
 *
 * The theme class provides a single source access object to various and typical
 * application assets and views. It provides a simpler way to manage multi-themed
 * applications and brings in many aspects of the application presentation layer into
 * one simple interface.
 *
 * @pacakge Theme
 * @subpackage Theme
 */
class Theme implements \Nerd\Design\Initializable
{
	/**
	 * Loaded theme configuration values
	 *
	 * @var array
	 */
    protected static $config;

	/**
	 * Directory read of themes
	 *
	 * @var  array
	 */
    protected static $themes = [];

	/**
	 * Static initializer
	 *
	 * Gets configuration values for the themes to be loaded. Then reads the root
	 * theme directory, allowing listing of all themes installed. This directory
	 * read will ensure that all requested themes actually exist among other
	 * validations.
	 *
	 * @todo Load and cache all theme configuration files to allow for more flexible
	 *       processing of theme information.
	 *
	 * @return void
	 */
    public static function __initialize()
    {
        static::$config = \Nerd\Config::get('theme::theme', []);

		if (!is_dir($root = static::$config['root'])) {
			throw new Exception\RootNotFound("Root folder [$root] does not exist.");
		}

        $handle = opendir($root);

        while (($dir = readdir($handle)) !== false) {
            if (strpos($dir, '.theme') !== false) {
                static::$themes[$dir] = ucfirst($dir);
            }
        }

        closedir($handle);
    }

	/**
	 * Retrieve a theme instance
	 *
	 * Theme is a factory class. Multiple instances can be created, managed and
	 * returned. This allows for very flexible theme support within the application.
	 *
	 * @param    string          Name of theme to load
	 * @param    array           Configuration overload array
	 * @return   this
	 */
    public static function instance($theme = 'default', array $config = [])
    {
        return new static("$theme.theme", $config);
    }

	/**
	 * Information file contents
	 *
	 * @var array
	 */
    protected $info = [];

	/**
	 * Private path to theme files
	 *
	 * @var string
	 */
    public $path;

	/**
	 * Public path to theme files
	 *
	 * @var string
	 */
	public $uri;

	/**
	 * Does this theme live in the public application folder?
	 *
	 * @var boolean
	 */
    public $public = false;

	/**
	 * Javascript asset collection
	 *
	 * @var \Nerd\Asset\Collection
	 */
    public $js;

	/**
	 * CSS asset collection
	 *
	 * @var \Nerd\Asset\Collection
	 */
    public $css;

	/**
	 * Loaded template cache
	 *
	 * Holds view objects relating to the theme's view objects
	 *
	 * @var array
	 */
	public $template = [];

	/**
	 * Fallback theme object
	 *
	 * Used when assets cannot be found within the active theme
	 *
	 * @var \Theme\Theme
	 */
	public $fallback;

	/**
	 * Constructor
	 *
	 * Create an instance of this theme object. This method first attemts to load the
	 * requested theme. It then creates and assigns the fallback theme object. Next
	 * all theme information is assigned to the object instance, the theme file is
	 * loaded if it exists, theme assets are pre-loaded.
	 *
	 * @throws \Theme\Exception if the theme does not exist
	 * 
	 * @param    string          Theme to load
	 * @param    array           Overload configuration array
	 * @param    boolean         Load a fallback theme?
	 * @return   \Theme\Theme
	 */
    public function __construct($theme = 'default', array $config = [], $isFallback = false)
    {
        if (!isset(static::$themes[$theme])) {
            throw new Exception("The requested theme [$theme] does not exist within {$this->config('root')}");
        }

		$current  = $this->config('theme', '');
		$fallback = $this->config('fallback', false);

		if (!$isFallback and $fallback and $current != $fallback) {
			$this->fallback = new static($fallback, [], true);
		}

        $this->path   = $this->config('root').DS.$theme;
        $this->public = strpos($this->path, \Nerd\DOCROOT) !== false;
        $this->uri    = trim(str_replace(\Nerd\DOCROOT, '', $this->path), DS);
        $this->js     = new AssetCollection([], $this->uri.'/'.'assets');
        $this->css    = new AssetCollection([], $this->uri.'/'.'assets');

        // Attempt to load theme information file...
        if ($this->config('info.enabled', false)) {
            $infoFile = $this->path.DS.$this->config('info.file', 'theme.json');

            if (file_exists($infoFile)) {
                $parser = \Nerd\Format::instance($this->config('info.format', 'json'));
                $data   = file_get_contents($infoFile);
                $this->info = $parser->to($data);
            }
        }

		// Import information file assets into assets arrays
		$this->js->add($this->info('assets.js', []));
		$this->css->add($this->info('assets.css', []));
    }

	/**
	 * Retrieve a saved configuration value
	 *
	 * If the configuration key can not be found a default value will be returned
	 *
	 * @param    string          Dot notated path to configuration key
	 * @param    mixed           Default value to return
	 * @return   mixed
	 */
    public function config($key, $default = null)
    {
        return Arr::get(static::$config, $key, $default);
    }

	/**
	 * Retrieve a value from the theme information file
	 *
	 * @param    string          Dot notated path to info key
	 * @param    mixed           Default value to return
	 * @return   mixed
	 */
    public function info($key, $default = null)
    {
        return Arr::get($this->info, $key, $default);
    }

	/**
	 * Retrieve a layout view within the active theme
	 *
	 * If the view cannot be found, and a fallback theme has been set it will attempt
	 * to load that view from the fallback theme layouts.
	 *
	 * @param    string          View file
	 * @param    array           Data to pass to the view
	 * @return   View
	 *
	 * @throws \Theme\Exception\ViewNotFound if the view cannot be found
	 */
	public function view($view, $data = [])
	{
		try {
			$view = new View($view, $data, $this->path.DS.'layouts');
		} catch (\InvalidArgumentException $e) {
			if ($this->fallback) {
				$view = new View($view, $data, $this->fallback->path.DS.'layouts');
			}
		}

		if (!isset($view)) {
			throw new Exception\ViewNotFound("View [{$this->path}] does not exist within the current theme or fallback theme");
		}

		return $view;
	}

	/**
	 * Retrieve a template view within the active theme
	 *
	 * @param    string          Template to load
	 * @return   View
	 *
	 * @throws \Theme\Exception\TemplateNotFound if the template cannot be found
	 */
	public function template($template = 'template')
	{
		if (!isset($this->template[$template])) {
			try {
				$this->template[$template] = new View($template, [], $this->path.DS.'templates');
			} catch (\InvalidArgumentException $e) {
				throw new Exception\TemplateNotFound("Template [$template] does not exist within the current theme");
			}
		}

		return $this->template[$template];
	}
}
