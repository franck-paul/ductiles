<?php
/**
 * @brief ductileFocus, a plugin for Dotclear 2
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

namespace Dotclear\Plugin\ductileFocus;

use Dotclear\App;
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
        $settings = App::blog()->settings()->get(My::id());
        if (!(bool) $settings->active) {
            return false;
        }

        // ToDo

        return true;
    }
}

// --- old code below ---

/**
 * @brief ductileFocus, a theme for Dotclear 2
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

namespace Dotclear\Theme\DuctileFocus;

use dcThemeConfig;
use Dotclear\App;
use Dotclear\Core\Frontend\Ctx;

# Behaviors
App::behavior()->addBehaviors([
    'publicHeadContent'  => [__NAMESPACE__ . '\tplDuctileFocusTheme', 'publicHeadContent'],
    'publicInsideFooter' => [__NAMESPACE__ . '\tplDuctileFocusTheme', 'publicInsideFooter'],
    'tplIfConditions'    => [__NAMESPACE__ . '\tplDuctileFocusTheme', 'tplIfConditions'],
]);

# Templates
App::frontend()->template()->addBlock('EntryIfContentIsCut', [__NAMESPACE__ . '\tplDuctileFocusTheme', 'EntryIfContentIsCut']);
App::frontend()->template()->addValue('focusEntries', [__NAMESPACE__ . '\tplDuctileFocusTheme', 'focusEntries']);
App::frontend()->template()->addValue('ductileLogoSrc', [__NAMESPACE__ . '\tplDuctileFocusTheme', 'ductileLogoSrc']);

class tplDuctileFocusTheme
{
    public static function focusEntries($attr)
    {
        $case = empty($attr['focus']) ? 1 : (int) $attr['focus'];
        $case--;
        if ($case < 0) {
            $case++;
        }

        return '<?php ' . "\n" .
        '   if (!isset($params)) $params = [];' . "\n" .
        '   $params = new ArrayObject($params);' . "\n" .
        '   ' . __NAMESPACE__ . '\tplDuctileFocusTheme::focusEntriesHelper(' . $case . ',$params);' . "\n" .
            '   $params = (array)$params;' . "\n" .
            ' ?>';
    }

    public static function focusEntriesHelper($case, $params)
    {
        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_focus');
        $s = @unserialize($s);
        if (is_array($s) && isset($s[$case])) {
            $f = $s[$case];
            if (is_array($f)) {
                $cat      = ($f['cat'] ?? '');
                $selected = (isset($f['selected']) ? (bool) $f['selected'] : false);
                $subcat   = (isset($f['subcat']) ? (bool) $f['subcat'] : false);
                $ret      = '';
                if ($cat != '') {
                    $params['cat_url'] = $cat . ($subcat ? ' ?sub' : '');
                }
                if ($selected) {
                    $params['post_selected'] = 1;
                }
            }
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
            $s    = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_focus');
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

            $c   = App::blog()->getCategories(['cat_url' => $s[2]['cat']]);
            $cc  = App::blog()->getCategoryFirstChildren($c->cat_id);
            $ret = 'in_array(App::frontend()->context()->posts->cat_id,[' . ($c->cat_id ?: 'null');
            if ($cc) {
                while ($cc->fetch()) {
                    $ret .= ',' . ($c->cat_id ?: 'null');
                }
            }
            $ret .= '])';
            $if[] = $sign . $ret;
        }
    }

    public static function tplIfConditionsHelper($with_category = false)
    {
        $ret = '';
        $ret = Ctx::EntryFirstImageHelper('s', $with_category);

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

        $short              = App::frontend()->template()->getFilters($attr);
        $cut                = $attr['cut_string'];
        $attr['cut_string'] = 0;
        $full               = App::frontend()->template()->getFilters($attr);
        $attr['cut_string'] = $cut;

        return '<?php if (strlen(' . sprintf($full, 'App::frontend()->context()->posts->getContent(' . $urls . ')') . ') > ' .
        'strlen(' . sprintf($short, 'App::frontend()->context()->posts->getContent(' . $urls . ')') . ')) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    public static function ductileLogoSrc($attr)
    {
        return '<?php echo ' . __NAMESPACE__ . '\tplDuctileFocusTheme::ductileLogoSrcHelper(); ?>';
    }

    public static function ductileLogoSrcHelper()
    {
        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_style');
        if ($s === null) {
            return;
        }
        $s = @unserialize($s);
        if (!is_array($s)) {
            return;
        }

        $img_url = App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme . '/img/logo.png';
        if (isset($s['logo_src']) && $s['logo_src'] !== null && $s['logo_src'] != '') {
            if ((substr($s['logo_src'], 0, 1) == '/') || (parse_url($s['logo_src'], PHP_URL_SCHEME) != '')) {
                // absolute URL
                $img_url = $s['logo_src'];
            } else {
                // relative URL (base = img folder of ductile focus theme)
                $img_url = App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme . '/img/' . $s['logo_src'];
            }
        }

        return $img_url;
    }

    public static function publicInsideFooter()
    {
        $res     = '';
        $default = false;
        $img_url = App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme . '/img/';

        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_stickers');

        if ($s === null) {
            $default = true;
        } else {
            $s = @unserialize($s);
            if (!is_array($s)) {
                $default = true;
            } else {
                $s = array_filter($s, __NAMESPACE__ . '\tplDuctileFocusTheme::cleanStickers');
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
            $res = self::setSticker(1, true, __('Subscribe'), App::blog()->url() . App::url()->getURLFor('feed') . '/atom', $img_url . 'sticker-feed.png');
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

        echo
        '<script src="' .
        App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme .
            '/ductile.js"></script>' . "\n";
    }

    public static function ductileStyleHelper()
    {
        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_style');

        if ($s === null) {
            return;
        }

        $s = @unserialize($s);
        if (!is_array($s)) {
            return;
        }

        $css = [];

        # Properties

        # Blog description
        $selectors = '#blogdesc';
        if (isset($s['subtitle_hidden'])) {
            dcThemeConfig::prop($css, $selectors, 'display', ($s['subtitle_hidden'] ? 'none' : null));
        }

        # Main font
        $selectors = 'body, .supranav li a span, .comment-info, #comments .me, .comment-number';
        if (isset($s['body_font'])) {
            dcThemeConfig::prop($css, $selectors, 'font-family', self::fontDef($s['body_font']));
        }

        # Secondary font
        $selectors = '#blogdesc, .supranav, #prelude, #submenu, #content-info, #subcategories, p.post-date, #comments, #ping-url, #comment-form, #comments-feed, ' .
            '.field input, .field textarea, #sidebar, #footer p, .arch-block h4';
        if (isset($s['alternate_font'])) {
            dcThemeConfig::prop($css, $selectors, 'font-family', self::fontDef($s['alternate_font']));
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

    protected static $fonts = [
        // Theme standard
        'Ductile Focus body'      => '"New Century Schoolbook", "Century Schoolbook", "Century Schoolbook L", Georgia, serif',
        'Ductile Focus alternate' => '"DejaVu Sans", "helvetica neue", helvetica, sans-serif',

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
