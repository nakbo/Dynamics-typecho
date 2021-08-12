<?php

class Dynamics_Option extends Typecho_Widget
{
    /**
     * @var Widget_Options
     */
    private $options;

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
        if (is_array($config)) {
            $this->push($config);
        }

        if ($this->followPath) {
            $this->themesFile = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__ . '/';
            $this->themesUrl = Typecho_Common::url(__TYPECHO_THEME_DIR__ . '/', $this->options->index);
        } else {
            $this->themesFile = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Dynamics/themes/';
            $this->themesUrl = Typecho_Common::url('/Dynamics/themes/', $this->options->pluginUrl);
        }
        $this->homepage = Typecho_Common::url(Dynamics_Plugin::DYNAMICS_ROUTE, $this->options->index);
    }

    /**
     * 根据 did 计算动态的链接
     *
     * @param $did
     * @return string
     */
    public function applyUrl($did)
    {
        return $this->homepage . str_replace('=', '', base64_encode($did)) . '.html';
    }

    /**
     * 根据 slug 反解 did
     *
     * @param $slug
     * @return int|string|null
     */
    public static function parseUrl($slug)
    {
        return intval($did = base64_decode(strval($slug) . '==')) > 0 ? $did : NULL;
    }

    /**
     * 动态首页
     *
     * @param $path
     */
    public function homepage($path = NULL)
    {
        echo $this->homepage . $path;
    }

    /**
     * 弃用
     *
     * @param null $path
     */
    public function dynamicsUrl($path = NULL)
    {
        $this->homepage($path);
    }

    /**
     * 动态主题路径
     *
     * @param $path
     */
    public function themeUrl($path)
    {
        echo $this->themesUrl . $this->theme . '/' . $path;
    }

    /**
     * 动态主题绝对路径
     *
     * @param string $path
     * @return string
     */
    public function themeFile($path)
    {
        return $this->themesFile . $this->theme . '/' . $path;
    }

    /**
     * 博客主题绝对路径
     *
     * @param string $path
     * @return string
     */
    public function _themeFile($path)
    {
        return rtrim($this->options->themeFile($this->options->theme), '/') . '/' . $path;
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