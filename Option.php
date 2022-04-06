<?php

namespace TypechoPlugin\Dynamics;

use Typecho\Common;
use Typecho\Widget;
use Widget\Options;

/**
 * Class Option
 * @package TypechoPlugin\Dynamics
 *
 * @property int pageSize
 * @property string theme
 * @property string homepage
 * @property string themesUrl
 * @property string followPath
 * @property string themesFile
 * @property string avatarSize
 * @property string avatarPrefix
 * @property string avatarRandom
 */
class Option extends Widget
{
    /**
     * @var string 默认主题相对路径
     */
    const DEFAULT_THEMES_DIR = '/Dynamics/themes/';

    /**
     * @var Options
     */
    private $options;

    /**
     * Widget init
     */
    protected function init()
    {
        $this->options = Options::alloc();
        $config = unserialize(
            $this->options->{'plugin:Dynamics'}
        );

        if (is_array($config)) {
            $themeConfig = unserialize(
                $config['themeConfig']
            );
            if (is_array($themeConfig)) {
                // Dynamic theme configuration
                // overrides dynamic plugin configuration
                $config = array_merge(
                    $config, $themeConfig
                );
            }
            $this->push($config);
        }

        if ($this->followPath) {
            $this->themesFile = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__ . '/';
            $this->themesUrl = Common::url(
                __TYPECHO_THEME_DIR__ . '/', $this->options->index
            );
        } else {
            $this->themesFile = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . self::DEFAULT_THEMES_DIR;
            $this->themesUrl = Common::url(
                self::DEFAULT_THEMES_DIR, $this->options->pluginUrl
            );
        }

        // Dynamic homepage path
        $this->homepage = Common::url(
            Plugin::DEFAULT_ROUTE, $this->options->index
        );
    }

    /**
     * 根据 did 计算动态的链接
     *
     * @param $did
     * @return string
     */
    public function applyUrl($did): string
    {
        $slug = str_replace(
            '=', '', base64_encode($did)
        );

        return "{$this->homepage}$slug.html";
    }

    /**
     * 根据 slug 反解 did
     *
     * @param $slug
     * @return int
     */
    public function parseUrl($slug): int
    {
        $did = base64_decode(
            strval($slug) . '=='
        );

        return intval($did);
    }

    /**
     * 动态首页
     *
     * @param string $path
     */
    public function homepage(
        string $path = '')
    {
        echo "{$this->homepage}$path";
    }

    /**
     * 动态主题路径
     *
     * @param string $path
     */
    public function themeUrl(
        string $path = '')
    {
        echo "{$this->themesUrl}{$this->theme}/$path";
    }

    /**
     * 动态主题绝对路径
     *
     * @param string $path
     * @return string
     */
    public function themeFile(
        string $path = ''): string
    {
        return "{$this->themesFile}{$this->theme}/$path";
    }

    /**
     * 博客主题绝对路径
     *
     * @param string $path
     * @return string
     * @deprecated
     */
    public function _themeFile(
        string $path = ''): string
    {
        return "{$this->options->themeFile($this->options->theme)}/$path";
    }

    /**
     * 动态主题相对路径
     *
     * @param string $theme
     * @param string $file
     * @return string
     */
    public function themesUrl(
        string $theme,
        string $file = ''): string
    {
        return "{$this->themesUrl}$theme/$file";
    }

    /**
     * 动态主题绝对路径
     *
     * @param string $theme
     * @param string $file
     * @return string
     */
    public function themesFile(
        string $theme,
        string $file = ''): string
    {
        return "{$this->themesFile}$theme/$file";
    }
}