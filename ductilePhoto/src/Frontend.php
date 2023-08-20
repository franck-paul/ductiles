<?php
/**
 * @brief ductilePhoto, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\ductilePhoto;

use dcCore;
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Don't do things in frontend if plugin disabled
        $settings = dcCore::app()->blog->settings->get(My::id());
        if (!(bool) $settings->active) {
            return false;
        }

        // ToDo

        return true;
    }
}

// --- old code below ---

/**
 * @brief ductilePhoto, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @author Kozlika
 * @author Franck Paul
 *
 * @copyright Kozlika and Franck Paul
 * @copyright GPL-2.0
 */

namespace Dotclear\Theme\DuctilePhoto;

use context;
use dcCore;
use dcThemeConfig;

# Behaviors
dcCore::app()->addBehaviors([
    'publicHeadContent'  => [__NAMESPACE__ . '\tplDuctilePhotoTheme', 'publicHeadContent'],
    'publicInsideFooter' => [__NAMESPACE__ . '\tplDuctilePhotoTheme', 'publicInsideFooter'],
    'tplIfConditions'    => [__NAMESPACE__ . '\tplDuctilePhotoTheme', 'tplIfConditions'],
]);

# Templates
dcCore::app()->tpl->addValue('ductileNbEntryPerPage', [__NAMESPACE__ . '\tplDuctilePhotoTheme', 'ductileNbEntryPerPage']);
dcCore::app()->tpl->addBlock('EntryIfContentIsCut', [__NAMESPACE__ . '\tplDuctilePhotoTheme', 'EntryIfContentIsCut']);

class tplDuctilePhotoTheme
{
    public static function ductileNbEntryPerPage($attr)
    {
        return '<?php ' . __NAMESPACE__ . '\tplDuctilePhotoTheme::ductileNbEntryPerPageHelper(); ?>';
    }

    public static function ductileNbEntryPerPageHelper()
    {
        $attr = [];

        $nb = 0;
        $s  = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_entries_counts');
        if ($s !== null) {
            $s = @unserialize($s);
            if (is_array($s)) {
                if (isset($s[dcCore::app()->url->type])) {
                    // Nb de billets par page défini par la config du thème
                    $nb = (int) $s[dcCore::app()->url->type];
                } elseif ((dcCore::app()->url->type == 'default-page') && (isset($s['default']))) {
                    // Les pages 2 et suivantes de la home ont le même nombre de billet que la première page
                    $nb = (int) $s['default'];
                }
            }
        }

        if ($nb == 0 && !empty($attr['nb'])) {
            // Nb de billets par page défini par défaut dans le template
            $nb = (int) $attr['nb'];
        }

        if ($nb > 0) {
            dcCore::app()->ctx->nb_entry_per_page = $nb;
        }
    }

    public static function tplIfConditions($tag, $attr, $content, $if)
    {
        if ($tag == 'EntryIf' && isset($attr['has_img'])) {
            $sign          = (bool) $attr['has_img'] ? '' : '!';
            $with_category = empty($attr['with_category']) ? 'false' : 'true';
            $if[]          = $sign . '(' . __NAMESPACE__ . '\tplDuctileFocusTheme::tplIfConditionsHelper(' . $with_category . '))';
        } elseif ($tag == 'EntryIf' && isset($attr['focus_cat_image'])) {
            $sign = (bool) $attr['focus_cat_image'] ? '' : '!';
            $s    = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_focus');
            if ($s === null) {
                return;
            }

            $s = @unserialize($s);
            if (!is_array($s)) {
                return;
            }

            if (!isset($s[2])) {
                return;
            }

            if (!isset($s[2]['cat'])) {
                return;
            }

            $c   = dcCore::app()->blog->getCategories(['cat_url' => $s[2]['cat']]);
            $cc  = dcCore::app()->blog->getCategoryFirstChildren($c->cat_id);
            $ret = 'in_array(dcCore::app()->ctx->posts->cat_id,[' . $c->cat_id;
            if ($cc) {
                while ($cc->fetch()) {
                    $ret .= ',' . $cc->cat_id;
                }
            }
            $ret .= '])';
            $if[] = $sign . $ret;
        }
    }

    public static function tplIfConditionsHelper($with_category = false)
    {
        $ret = '';
        $ret = context::EntryFirstImageHelper('s', $with_category);

        return ($ret != '');
    }

    public static function EntryIfContentIsCut($attr, $content)
    {
        if (empty($attr['cut_string']) || !empty($attr['full'])) {
            return '';
        }

        $urls = '0';
        if (!empty($attr['absolute_urls'])) {
            $urls = '1';
        }

        $short              = dcCore::app()->tpl->getFilters($attr);
        $cut                = $attr['cut_string'];
        $attr['cut_string'] = 0;
        $full               = dcCore::app()->tpl->getFilters($attr);
        $attr['cut_string'] = $cut;

        return '<?php if (strlen(' . sprintf($full, 'dcCore::app()->ctx->posts->getContent(' . $urls . ')') . ') > ' .
        'strlen(' . sprintf($short, 'dcCore::app()->ctx->posts->getContent(' . $urls . ')') . ')) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    public static function publicInsideFooter()
    {
        $res     = '';
        $default = false;
        $img_url = dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme . '/img/';

        $s = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_stickers');

        if ($s === null) {
            $default = true;
        } else {
            $s = @unserialize($s);
            if (!is_array($s)) {
                $default = true;
            } else {
                $s = array_filter($s, 'self::cleanStickers');
                if (count($s) == 0) {
                    $default = true;
                } else {
                    $count = 1;
                    foreach ($s as $sticker) {
                        $res .= self::setSticker($count, ($count === count($s)), $sticker['label'], $sticker['url'], $img_url . $sticker['image']);
                        $count++;
                    }
                }
            }
        }

        if ($default || $res == '') {
            $res = self::setSticker(1, true, __('Subscribe'), dcCore::app()->blog->url . dcCore::app()->url->getURLFor('feed') . '/atom', $img_url . 'sticker-feed.png');
        }

        if ($res != '') {
            $res = '<ul id="stickers">' . "\n" . $res . '</ul>' . "\n";
            echo $res;
        }
    }

    protected static function cleanStickers($s)
    {
        return is_array($s) && (isset($s['label']) && isset($s['url']) && isset($s['image'])) && ($s['label'] != null && $s['url'] != null && $s['image'] != null);
    }

    protected static function setSticker($position, $last, $label, $url, $image)
    {
        return '<li id="sticker' . $position . '"' . ($last ? ' class="last"' : '') . '>' . "\n" .
            '<a href="' . $url . '">' . "\n" .
            '<img alt="" src="' . $image . '" />' . "\n" .
            '<span>' . $label . '</span>' . "\n" .
            '</a>' . "\n" .
            '</li>' . "\n";
    }

    public static function publicHeadContent()
    {
        echo
        '<style type="text/css">' . "\n" .
        '/* ' . __('Additionnal style directives') . ' */' . "\n" .
        self::ductileStyleHelper() .
            "</style>\n";

        $ambiance_src = self::ambianceCssSrc();
        if ($ambiance_src != '') {
            echo '<link media="screen" href="' . $ambiance_src . '" type="text/css" rel="stylesheet">' . "\n";
        }

        echo
        '<script src="' .
        dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme .
            '/ductile.js"></script>' . "\n";
    }

    protected static function ambianceCssSrc()
    {
        $s = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_style');
        if ($s === null) {
            return;
        }
        $s = @unserialize($s);
        if (!is_array($s)) {
            return;
        }

        $css_src = dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme;
        if (isset($s['ambiance'])) {
            if ($s['ambiance'] !== null && $s['ambiance'] != '') {
                $css_src .= '/' . $s['ambiance'] . '-ambiance.css';
            }
        } else {
            return;
        }

        return $css_src;
    }

    public static function ductileStyleHelper()
    {
        $s = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_style');

        if ($s === null) {
            return;
        }

        $s = @unserialize($s);
        if (!is_array($s)) {
            return;
        }

        $css = [];

        # Properties

        # Blog logo
        $selectors = 'h1 a';
        $logoSrc   = self::logoSrc();
        if ($logoSrc != '') {
            dcThemeConfig::prop($css, $selectors, 'background', 'transparent url("' . $logoSrc . '") no-repeat left center');
        }

        # Blog description
        $selectors = '#blogdesc';
        if (isset($s['subtitle_hidden'])) {
            dcThemeConfig::prop($css, $selectors, 'display', ($s['subtitle_hidden'] ? 'none' : null));
        }

        # Main font
        $selectors = 'body';
        if (isset($s['body_font'])) {
            dcThemeConfig::prop($css, $selectors, 'font-family', self::fontDef($s['body_font']));
        }

        # Inside posts links font weight
        $selectors = '.post-excerpt a, .post-content a';
        if (isset($s['post_link_w'])) {
            dcThemeConfig::prop($css, $selectors, 'font-weight', ($s['post_link_w'] ? 'bold' : 'normal'));
        }

        # Inside posts links colors (normal, visited)
        $selectors = '.post-excerpt a:link, .post-excerpt a:visited, .post-content a:link, .post-content a:visited';
        if (isset($s['post_link_v_c'])) {
            dcThemeConfig::prop($css, $selectors, 'color', $s['post_link_v_c']);
        }

        # Inside posts links colors (hover, active, focus)
        $selectors = '.post-excerpt a:hover, .post-excerpt a:active, .post-excerpt a:focus, .post-content a:hover, .post-content a:active, .post-content a:focus';
        if (isset($s['post_link_f_c'])) {
            dcThemeConfig::prop($css, $selectors, 'color', $s['post_link_f_c']);
        }

        # Style directives
        $res = '';
        foreach ($css as $selector => $values) {
            $res .= $selector . " {\n";
            foreach ($values as $k => $v) {
                $res .= $k . ':' . $v . ";\n";
            }
            $res .= "}\n";
        }

        # Large screens
        $css_large = [];

        # Blog title font weight
        $selectors = 'h1, h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
        if (isset($s['blog_title_w'])) {
            dcThemeConfig::prop($css_large, $selectors, 'font-weight', ($s['blog_title_w'] ? 'bold' : 'normal'));
        }

        # Blog title font size
        $selectors = 'h1';
        if (isset($s['blog_title_s'])) {
            dcThemeConfig::prop($css_large, $selectors, 'font-size', $s['blog_title_s']);
        }

        # Blog title color
        $selectors = 'h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
        if (isset($s['blog_title_c'])) {
            dcThemeConfig::prop($css_large, $selectors, 'color', $s['blog_title_c']);
        }

        # Post title font weight
        $selectors = 'h2.post-title, h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
        if (isset($s['post_title_w'])) {
            dcThemeConfig::prop($css_large, $selectors, 'font-weight', ($s['post_title_w'] ? 'bold' : 'normal'));
        }

        # Post title font size
        $selectors = 'h2.post-title';
        if (isset($s['post_title_s'])) {
            dcThemeConfig::prop($css_large, $selectors, 'font-size', $s['post_title_s']);
        }

        # Post title color
        $selectors = 'h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
        if (isset($s['post_title_c'])) {
            dcThemeConfig::prop($css_large, $selectors, 'color', $s['post_title_c']);
        }

        # Simple title color (title without link)
        $selectors = '#content-info h2, .post-title, .post h3, .post h4, .post h5, .post h6, .arch-block h3';
        if (isset($s['post_simple_title_c'])) {
            dcThemeConfig::prop($css_large, $selectors, 'color', $s['post_simple_title_c']);
        }

        # Style directives for large screens
        if ($css_large !== []) {
            $res .= '@media only screen and (min-width: 481px) {' . "\n";
            foreach ($css_large as $selector => $values) {
                $res .= $selector . " {\n";
                foreach ($values as $k => $v) {
                    $res .= $k . ':' . $v . ";\n";
                }
                $res .= "}\n";
            }
            $res .= "}\n";
        }

        # Small screens
        $css_small = [];

        # Blog title font weight
        $selectors = 'h1, h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
        if (isset($s['blog_title_w_m'])) {
            dcThemeConfig::prop($css_small, $selectors, 'font-weight', ($s['blog_title_w_m'] ? 'bold' : 'normal'));
        }

        # Blog title font size
        $selectors = 'h1';
        if (isset($s['blog_title_s_m'])) {
            dcThemeConfig::prop($css_small, $selectors, 'font-size', $s['blog_title_s_m']);
        }

        # Blog title color
        $selectors = 'h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
        if (isset($s['blog_title_c_m'])) {
            dcThemeConfig::prop($css_small, $selectors, 'color', $s['blog_title_c_m']);
        }

        # Post title font weight
        $selectors = 'h2.post-title, h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
        if (isset($s['post_title_w_m'])) {
            dcThemeConfig::prop($css_small, $selectors, 'font-weight', ($s['post_title_w_m'] ? 'bold' : 'normal'));
        }

        # Post title font size
        $selectors = 'h2.post-title';
        if (isset($s['post_title_s_m'])) {
            dcThemeConfig::prop($css_small, $selectors, 'font-size', $s['post_title_s_m']);
        }

        # Post title color
        $selectors = 'h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
        if (isset($s['post_title_c_m'])) {
            dcThemeConfig::prop($css_small, $selectors, 'color', $s['post_title_c_m']);
        }

        # Style directives for small screens
        if ($css_small !== []) {
            $res .= '@media only screen and (max-width: 480px) {' . "\n";
            foreach ($css_small as $selector => $values) {
                $res .= $selector . " {\n";
                foreach ($values as $k => $v) {
                    $res .= $k . ':' . $v . ";\n";
                }
                $res .= "}\n";
            }
            $res .= "}\n";
        }

        return $res;
    }

    protected static function logoSrc()
    {
        $s = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_style');
        if ($s === null) {
            return;
        }
        $s = @unserialize($s);
        if (!is_array($s)) {
            return;
        }

        $img_url = dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme . '/img/logo.png';
        if (isset($s['logo_src']) && $s['logo_src'] !== null && $s['logo_src'] != '') {
            if ((substr($s['logo_src'], 0, 1) == '/') || (parse_url($s['logo_src'], PHP_URL_SCHEME) != '')) {
                // absolute URL
                $img_url = $s['logo_src'];
            } else {
                // relative URL (base = img folder of ductile photo theme)
                $img_url = dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme . '/img/' . $s['logo_src'];
            }
        }

        return $img_url;
    }

    protected static $fonts = [
        // Serif families
        'Times New Roman' => 'Cambria, "Hoefler Text", Utopia, "Liberation Serif", "Nimbus Roman No9 L Regular", Times, "Times New Roman", serif',
        'Georgia'         => 'Constantia, "Lucida Bright", Lucidabright, "Lucida Serif", Lucida, "DejaVu Serif", "Bitstream Vera Serif", "Liberation Serif", Georgia, serif',
        'Garamond'        => '"Palatino Linotype", Palatino, Palladio, "URW Palladio L", "Book Antiqua", Baskerville, "Bookman Old Style", "Bitstream Charter", "Nimbus Roman No9 L", Garamond, "Apple Garamond", "ITC Garamond Narrow", "New Century Schoolbook", "Century Schoolbook", "Century Schoolbook L", Georgia, serif',

        // Sans-serif families
        'Helvetica/Arial' => 'Frutiger, "Frutiger Linotype", Univers, Calibri, "Gill Sans", "Gill Sans MT", "Myriad Pro", Myriad, "DejaVu Sans Condensed", "Liberation Sans", "Nimbus Sans L", Tahoma, Geneva, "Helvetica Neue", Helvetica, Arial, sans-serif',
        'Verdana'         => 'Corbel, "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", "Bitstream Vera Sans", "Liberation Sans", Verdana, "Verdana Ref", sans-serif',
        'Trebuchet MS'    => '"Segoe UI", Candara, "Bitstream Vera Sans", "DejaVu Sans", "Bitstream Vera Sans", "Trebuchet MS", Verdana, "Verdana Ref", sans-serif',

        // Cursive families
        'Impact' => 'Impact, Haettenschweiler, "Franklin Gothic Bold", Charcoal, "Helvetica Inserat", "Bitstream Vera Sans Bold", "Arial Black", sans-serif',

        // Monospace families
        'Monospace' => 'Consolas, "Andale Mono WT", "Andale Mono", "Lucida Console", "Lucida Sans Typewriter", "DejaVu Sans Mono", "Bitstream Vera Sans Mono", "Liberation Mono", "Nimbus Mono L", Monaco, "Courier New", Courier, monospace',
    ];

    protected static function fontDef($c)
    {
        return self::$fonts[$c] ?? null;
    }
}
