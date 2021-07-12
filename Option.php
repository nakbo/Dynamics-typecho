<?php

class Dynamics_Option extends Typecho_Widget
{
    /**
     * @var Widget_Options
     */
    private $options;

    /**
     * @var string
     */
    public $dynamicsUrl, $theme, $themeUrl, $themesPath, $themesFile, $themesUrl;

    /**
     * @var string
     */
    protected $themeDir, $_themeDir;

    /**
     * @param $request
     * @param $response
     * @param null $params
     * @throws Typecho_Exception
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->options = $this->widget('Widget_Options');

        $config = unserialize($this->options->{'plugin:Dynamics'});
        if (is_array($themeConfig = unserialize($config['themeConfig']))) {
            $config = array_merge($config, $themeConfig);
        }
        unset($config['themeConfig']);
        foreach ($config as $name => $value) {
            $this->{$name} = $value;
        }

        if ($this->followPath) {
            $this->themesPath = "/";
            $this->themesFile = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__ . $this->themesPath;
            $this->themesUrl = Typecho_Common::url(__TYPECHO_THEME_DIR__ . "/{$this->themesPath}/", $this->options->index);
        } else {
            $this->themesPath = "/Dynamics/themes/";
            $this->themesFile = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . $this->themesPath;
            $this->themesUrl = Typecho_Common::url("{$this->themesPath}/", $this->options->pluginUrl);
        }
        $this->themeUrl = $this->themesUrl . $this->theme . '/';
        $this->dynamicsUrl = Typecho_Common::url(Dynamics_Plugin::DYNAMICS_ROUTE, $this->options->index);
        $this->_themeDir = rtrim($this->options->themeFile($this->options->theme), '/') . '/';
        $this->themeDir = $this->themesFile . $this->theme . '/';
    }

    /**
     * 根据 did 计算动态的链接
     *
     * @param $did
     * @return string
     */
    public function applyUrl($did)
    {
        $slug = base64_encode($did);
        $slug = str_replace('=', '', $slug) . '.html';
        return $this->dynamicsUrl . $slug;
    }

    /**
     * 根据 slug 反解 did
     *
     * @param $slug
     * @return int|string|null
     */
    public static function parseUrl($slug)
    {
        $slug = strval($slug) . '==';
        $did = base64_decode($slug);
        return intval($did) > 0 ? $did : NULL;
    }

    /**
     * 标题
     */
    public function title()
    {
        echo $this->title;
    }

    /**
     * 动态首页
     * @param $path
     */
    public function dynamicsUrl($path = NULL)
    {
        echo $this->dynamicsUrl . $path;
    }

    /**
     * 动态主题路径
     *
     * @param $path
     */
    public function themeUrl($path)
    {
        echo $this->themeUrl . $path;
    }

    /**
     * 动态主题绝对路径
     *
     * @param string $path
     * @return string
     */
    public function themeFile($path)
    {
        return $this->themeDir . $path;
    }

    /**
     * 博客主题绝对路径
     *
     * @param string $path
     * @return string
     */
    public function _themeFile($path)
    {
        return $this->_themeDir . $path;
    }

    /**
     * 动态主题绝对路径
     *
     * @param string $theme
     * @param string $file
     * @return string
     */
    public function themesFile($theme, $file = '')
    {
        return $this->themesFile . trim($theme, './') . '/' . trim($file, './');
    }

    /**
     * 动态主题相对路径
     *
     * @param string $theme
     * @param string $file
     * @return string
     */
    public function themesUrl($theme, $file = '')
    {
        return $this->themesUrl . trim($theme, './') . '/' . trim($file, './');
    }
}