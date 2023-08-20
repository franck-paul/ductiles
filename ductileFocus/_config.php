<?php
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

use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Dotclear\Helper\Network\Http;

if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

L10n::set(__DIR__ . '/locales/' . dcCore::app()->lang . '/admin');

if (preg_match('#^http(s)?://#', (string) dcCore::app()->blog->settings->system->themes_url)) {
    $img_url = Http::concatURL(dcCore::app()->blog->settings->system->themes_url, '/' . dcCore::app()->blog->settings->system->theme . '/img/');
} else {
    $img_url = Http::concatURL(dcCore::app()->blog->url, dcCore::app()->blog->settings->system->themes_url . '/' . dcCore::app()->blog->settings->system->theme . '/img/');
}
$img_path = __DIR__ . '/img/';

$standalone_config = (bool) dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'standalone_config');

// Load contextual help
if (file_exists(__DIR__ . '/locales/' . dcCore::app()->lang . '/resources.php')) {
    require __DIR__ . '/locales/' . dcCore::app()->lang . '/resources.php';
}

$fonts = [
    __('default')                 => '',
    __('Ductile Focus primary')   => 'Ductile Focus body',
    __('Ductile Focus secondary') => 'Ductile Focus alternate',
    __('Times New Roman')         => 'Times New Roman',
    __('Georgia')                 => 'Georgia',
    __('Garamond')                => 'Garamond',
    __('Helvetica/Arial')         => 'Helvetica/Arial',
    __('Verdana')                 => 'Verdana',
    __('Trebuchet MS')            => 'Trebuchet MS',
    __('Impact')                  => 'Impact',
    __('Monospace')               => 'Monospace',
];

function adjustFontSize($s)
{
    if (preg_match('/^([0-9.]+)\s*(%|pt|px|em|ex)?$/', $s, $m)) {
        if (empty($m[2])) {
            $m[2] = 'em';
        }

        return $m[1] . $m[2];
    }

    return null;
}

$font_families = [
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

$fontDef = fn ($c) => isset($font_families[$c]) ? '<span style="position:absolute;top:0;left:32em;">' . $font_families[$c] . '</span>' : '';

function adjustColor($c)
{
    if ($c === '') {
        return '';
    }

    $c = strtoupper($c);

    if (preg_match('/^[A-F0-9]{3,6}$/', $c)) {
        $c = '#' . $c;
    }

    if (preg_match('/^#[A-F0-9]{6}$/', $c)) {
        return $c;
    }

    if (preg_match('/^#[A-F0-9]{3,}$/', $c)) {
        return '#' . substr($c, 1, 1) . substr($c, 1, 1) . substr($c, 2, 1) . substr($c, 2, 1) . substr($c, 3, 1) . substr($c, 3, 1);
    }

    return '';
}

function computeContrastRatio($color, $background)
{
    // Compute contrast ratio between two colors

    $color = adjustColor($color);
    if (($color == '') || (strlen($color) != 7)) {
        return 0;
    }
    $background = adjustColor($background);
    if (($background == '') || (strlen($background) != 7)) {
        return 0;
    }

    $l1 = (0.2126 * (hexdec(substr($color, 1, 2)) / 255) ** 2.2) + (0.7152 * (hexdec(substr($color, 3, 2)) / 255) ** 2.2) + (0.0722 * (hexdec(substr($color, 5, 2)) / 255) ** 2.2);

    $l2 = (0.2126 * (hexdec(substr($background, 1, 2)) / 255) ** 2.2) + (0.7152 * (hexdec(substr($background, 3, 2)) / 255) ** 2.2) + (0.0722 * (hexdec(substr($background, 5, 2)) / 255) ** 2.2);

    return $l1 > $l2 ? ($l1 + 0.05) / ($l2 + 0.05) : ($l2 + 0.05) / ($l1 + 0.05);
}

function contrastRatioLevel($ratio, $size, $bold)
{
    if ($size == '') {
        return '';
    }

    // Eval font size in em (assume base font size in pixels equal to 16)
    if (preg_match('/^([0-9.]+)\s*(%|pt|px|em|ex)?$/', $size, $m)) {
        if (empty($m[2])) {
            $m[2] = 'em';
        }
    } else {
        return '';
    }
    switch ($m[2]) {
        case '%':
            $s = (float) $m[1] / 100;

            break;
        case 'pt':
            $s = (float) $m[1] / 12;

            break;
        case 'px':
            $s = (float) $m[1] / 16;

            break;
        case 'em':
            $s = (float) $m[1];

            break;
        case 'ex':
            $s = (float) $m[1] / 2;

            break;
        default:
            return '';
    }

    $large = ((($s > 1.5) && ($bold == false)) || (($s > 1.2) && ($bold == true)));

    // Check ratio
    if ($ratio > 7) {
        return 'AAA';
    } elseif (($ratio > 4.5) && $large) {
        return 'AAA';
    } elseif ($ratio > 4.5) {
        return 'AA';
    } elseif (($ratio > 3) && $large) {
        return 'AA';
    }

    return '';
}

function contrastRatio($color, $background, $size = '', $bold = false)
{
    if (($color != '') && ($background != '')) {
        $ratio = computeContrastRatio($color, $background);
        $level = contrastRatioLevel($ratio, $size, $bold);

        return
            '<span style="position:absolute;top:0;left:23em;">' .
            sprintf(__('ratio %.1f'), $ratio) .
            ($level != '' ? ' ' . sprintf(__('(%s)'), $level) : '') .
            '</span>';
    }

    return '';
}

$ductile_base = [
    // HTML
    'subtitle_hidden' => null,
    'logo_src'        => null,
    // CSS
    'body_font'           => null,
    'alternate_font'      => null,
    'blog_title_w'        => null,
    'blog_title_s'        => null,
    'blog_title_c'        => null,
    'post_title_w'        => null,
    'post_title_s'        => null,
    'post_title_c'        => null,
    'post_link_w'         => null,
    'post_link_v_c'       => null,
    'post_link_f_c'       => null,
    'blog_title_w_m'      => null,
    'blog_title_s_m'      => null,
    'blog_title_c_m'      => null,
    'post_title_w_m'      => null,
    'post_title_s_m'      => null,
    'post_title_c_m'      => null,
    'post_simple_title_c' => null,
];

$ductile_user = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_style');
$ductile_user = @unserialize($ductile_user);
if (!is_array($ductile_user)) {
    $ductile_user = [];
}
$ductile_user = array_merge($ductile_base, $ductile_user);

$ductile_stickers = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_stickers');
$ductile_stickers = @unserialize($ductile_stickers);

// If no stickers defined, add feed Atom one
if (!is_array($ductile_stickers)) {
    $ductile_stickers = [[
        'label' => __('Subscribe'),
        'url'   => dcCore::app()->blog->url . dcCore::app()->url->getURLFor('feed') . '/atom',
        'image' => 'sticker-feed.png',
    ]];
}

$ductile_stickers_full = [];
// Get all sticker images already used
if (is_array($ductile_stickers)) {
    foreach ($ductile_stickers as $v) {
        $ductile_stickers_full[] = $v['image'];
    }
}
// Get all sticker-*.png in img folder of theme
$ductile_stickers_images = Files::scandir($img_path);
if (is_array($ductile_stickers_images)) {
    foreach ($ductile_stickers_images as $v) {
        if (preg_match('/^sticker\-(.*)\.png$/', $v) && !in_array($v, $ductile_stickers_full)) {
            // image not already used
            $ductile_stickers[] = [
                'label' => null,
                'url'   => null,
                'image' => $v, ];
        }
    }
}

$ductile_focuses = dcCore::app()->blog->settings->themes->get(dcCore::app()->blog->settings->system->theme . '_focus');
$ductile_focuses = @unserialize($ductile_focuses);
if (!is_array($ductile_focuses)) {
    $ductile_focuses = [
        [
            'cat'      => '',
            'selected' => false,
            'subcat'   => false, ],
        [
            'cat'      => '',
            'selected' => true,
            'subcat'   => false, ],
        [
            'cat'      => '',
            'selected' => false,
            'subcat'   => false, ],
    ];
}

// Categories list
$categories_combo     = [];
$categories_combo_all = [
    __('All categories') => '',
];

try {
    $rs = dcCore::app()->blog->getCategories(['post_type' => 'post']);
    while ($rs->fetch()) {
        $categories_combo[] = $categories_combo_all[] = new formSelectOption(
            str_repeat('&nbsp;&nbsp;', $rs->level - 1) . ($rs->level - 1 == 0 ? '' : '&bull; ') . Html::escapeHTML($rs->cat_title),
            $rs->cat_url
        );
    }
} catch (Exception $e) {
}

$conf_tab = $_POST['conf_tab'] ?? 'html';

if (!empty($_POST)) {
    try {
        # HTML
        if ($conf_tab == 'html') {
            $ductile_user['subtitle_hidden'] = (int) !empty($_POST['subtitle_hidden']);
            $ductile_user['logo_src']        = $_POST['logo_src'];

            $ductile_stickers = [];
            $itemsCount       = is_countable($_POST['sticker_image']) ? count($_POST['sticker_image']) : 0;
            for ($i = 0; $i < (is_countable($_POST['sticker_image']) ? $itemsCount : 0); $i++) {
                $ductile_stickers[] = [
                    'label' => $_POST['sticker_label'][$i],
                    'url'   => $_POST['sticker_url'][$i],
                    'image' => $_POST['sticker_image'][$i],
                ];
            }

            $order = [];
            if (empty($_POST['ds_order']) && !empty($_POST['order'])) {
                $order = $_POST['order'];
                asort($order);
                $order = array_keys($order);
            }
            if (!empty($order)) {
                $new_ductile_stickers = [];
                foreach ($order as $i => $k) {
                    $new_ductile_stickers[] = [
                        'label' => $ductile_stickers[$k]['label'],
                        'url'   => $ductile_stickers[$k]['url'],
                        'image' => $ductile_stickers[$k]['image'],
                    ];
                }
                $ductile_stickers = $new_ductile_stickers;
            }

            $ductile_focuses   = [];
            $ductile_focuses[] = [
                'cat'      => '',
                'selected' => (int) false,
                'subcat'   => (int) false,
            ];
            $ductile_focuses[] = [
                'cat'      => $_POST['focus2_cat'],
                'selected' => (int) !empty($_POST['focus2_selected']),
                'subcat'   => (int) !empty($_POST['focus2_subcat']),
            ];
            $ductile_focuses[] = [
                'cat'      => $_POST['focus3_cat'],
                'selected' => (int) !empty($_POST['focus3_selected']),
                'subcat'   => (int) !empty($_POST['focus3_subcat']),
            ];
        }

        # CSS
        if ($conf_tab == 'css') {
            $ductile_user['body_font']      = $_POST['body_font'];
            $ductile_user['alternate_font'] = $_POST['alternate_font'];

            $ductile_user['blog_title_w'] = (int) !empty($_POST['blog_title_w']);
            $ductile_user['blog_title_s'] = adjustFontSize($_POST['blog_title_s']);
            $ductile_user['blog_title_c'] = adjustColor($_POST['blog_title_c']);

            $ductile_user['post_title_w'] = (int) !empty($_POST['post_title_w']);
            $ductile_user['post_title_s'] = adjustFontSize($_POST['post_title_s']);
            $ductile_user['post_title_c'] = adjustColor($_POST['post_title_c']);

            $ductile_user['post_link_w']   = (int) !empty($_POST['post_link_w']);
            $ductile_user['post_link_v_c'] = adjustColor($_POST['post_link_v_c']);
            $ductile_user['post_link_f_c'] = adjustColor($_POST['post_link_f_c']);

            $ductile_user['post_simple_title_c'] = adjustColor($_POST['post_simple_title_c']);

            $ductile_user['blog_title_w_m'] = (int) !empty($_POST['blog_title_w_m']);
            $ductile_user['blog_title_s_m'] = adjustFontSize($_POST['blog_title_s_m']);
            $ductile_user['blog_title_c_m'] = adjustColor($_POST['blog_title_c_m']);

            $ductile_user['post_title_w_m'] = (int) !empty($_POST['post_title_w_m']);
            $ductile_user['post_title_s_m'] = adjustFontSize($_POST['post_title_s_m']);
            $ductile_user['post_title_c_m'] = adjustColor($_POST['post_title_c_m']);
        }

        dcCore::app()->blog->settings->themes->put(dcCore::app()->blog->settings->system->theme . '_style', serialize($ductile_user));
        dcCore::app()->blog->settings->themes->put(dcCore::app()->blog->settings->system->theme . '_stickers', serialize($ductile_stickers));
        dcCore::app()->blog->settings->themes->put(dcCore::app()->blog->settings->system->theme . '_focus', serialize($ductile_focuses));

        // Blog refresh
        dcCore::app()->blog->triggerBlog();

        // Template cache reset
        dcCore::app()->emptyTemplatesCache();

        echo
        '<div class="message"><p>' .
        __('Theme configuration upgraded.') .
        '</p></div>';
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

// Legacy mode
if (!$standalone_config) {
    echo '</form>';
}

# HTML Tab

echo '<div class="multi-part" id="themes-list' . ($conf_tab == 'html' ? '' : '-html') . '" title="' . __('Content') . '">';

echo '<form id="theme_config" action="blog_theme.php?conf=1" method="post" enctype="multipart/form-data">';

echo '<fieldset><legend>' . __('Header') . '</legend>' .
'<p class="field"><label for="subtitle_hidden">' . __('Hide blog description:') . ' ' .
form::checkbox('subtitle_hidden', 1, $ductile_user['subtitle_hidden']) . '</label>' . '</p>';
if (dcCore::app()->plugins->moduleExists('simpleMenu')) {
    echo '<p>' . sprintf(__('To configure the top menu go to the <a href="%s">Simple Menu administration page</a>.'), 'plugin.php?p=simpleMenu') . '</p>';
}
echo '<p class="field"><label for="logo_src">' . __('Logo URL:') . ' ' .
    form::field('logo_src', 40, 255, $ductile_user['logo_src']) . '</label>' . '</p>';
echo '</fieldset>';

echo '<fieldset><legend>' . __('Stickers') . '</legend>';

echo '<table class="dragable">' . '<caption>' . __('Stickers (footer)') . '</caption>' .
'<thead>' .
'<tr>' .
'<th scope="col">' . '</th>' .
'<th scope="col">' . __('Image') . '</th>' .
'<th scope="col">' . __('Label') . '</th>' .
'<th scope="col">' . __('URL') . '</th>' .
'</tr>' .
'</thead>' .
'<tbody id="stickerslist">';
$count = 0;
foreach ($ductile_stickers as $i => $v) {
    $count++;
    echo
    '<tr class="line" id="l_' . $i . '">' .
    '<td class="handle minimal">' . form::field(['order[' . $i . ']'], 2, 3, $count, 'position', '', false) .
        form::hidden(['dynorder[]','dynorder-' . $i], $i) . '</td>' .
    '<td>' . form::hidden(['sticker_image[]'], $v['image']) . '<img src="' . $img_url . $v['image'] . '" /> ' . '</td>' .
    '<td scope="raw">' . form::field(['sticker_label[]','dsl-' . $i], 20, 255, $v['label']) . '</td>' .
    '<td>' . form::field(['sticker_url[]','dsu-' . $i], 40, 255, $v['url']) . '</td>' .
    '</tr>';
}
echo
'</tbody></table>';

echo '</fieldset>';

echo '<div class="two-cols">';
echo '<div class="col">';

echo '<fieldset><legend>' . __('Focus on text') . '</legend>';
echo '<p class="field"><label for="focus2_cat">' . __('Category') . ' ' .
form::combo('focus2_cat', $categories_combo_all, $ductile_focuses[1]['cat']) .
'</label>' . '</p>' .
'<p class="field"><label for="focus2_subcat">' . __('Including sub-categories:') . ' ' .
form::checkbox('focus2_subcat', 1, $ductile_focuses[1]['subcat']) . '</label>' . '</p>' .
'<p class="field"><label for="focus2_selected">' . __('Only selected posts:') . ' ' .
form::checkbox('focus2_selected', 1, $ductile_focuses[1]['selected']) . '</label>' . '</p>';
echo '</fieldset>';

echo '</div>';
echo '<div class="col">';

echo '<fieldset><legend>' . __('Focus on picture') . '</legend>';
echo '<p class="field"><label for="focus3_cat">' . __('Category') . ' ' .
form::combo('focus3_cat', $categories_combo, $ductile_focuses[2]['cat']) .
'</label>' . '</p>' .
'<p class="field"><label for="focus3_subcat">' . __('Including sub-categories:') . ' ' .
form::checkbox('focus3_subcat', 1, $ductile_focuses[2]['subcat']) . '</label>' . '</p>' .
'<p class="field"><label for="focus3_selected">' . __('Only selected posts:') . ' ' .
form::checkbox('focus3_selected', 1, $ductile_focuses[2]['selected']) . '</label>' . '</p>';
echo '</fieldset>';

echo '</div>';
echo '</div>';

echo '<input type="hidden" name="conf_tab" value="html">';
echo '<p class="clear">' . form::hidden('ds_order', '') . '<input type="submit" value="' . __('Save') . '" />' . dcCore::app()->formNonce() . '</p>';
echo '</form>';

echo '</div>'; // Close tab

# CSS tab

echo '<div class="multi-part" id="themes-list' . ($conf_tab == 'css' ? '' : '-css') . '" title="' . __('Presentation') . '">';

echo '<form id="theme_config" action="blog_theme.php?conf=1" method="post" enctype="multipart/form-data">';

echo '<h3>' . __('General settings') . '</h3>';

echo '<fieldset><legend>' . __('Fonts') . '</legend>' .
'<p class="field"><label for="body_font">' . __('Main:') . ' ' .
form::combo('body_font', $fonts, $ductile_user['body_font']) . '</label>' .
(empty($ductile_user['body_font']) ? '' : ' ' . $fontDef($ductile_user['body_font'])) .
'</p>' .

'<p class="field"><label for="alternate_font">' . __('Secondary:') . ' ' .
form::combo('alternate_font', $fonts, $ductile_user['alternate_font']) . '</label>' .
(empty($ductile_user['alternate_font']) ? '' : ' ' . $fontDef($ductile_user['alternate_font'])) .
'</p>' .
'</fieldset>';

echo '<div class="two-cols">';
echo '<div class="col">';

echo '<fieldset><legend>' . __('Blog title') . '</legend>' .
'<p class="field"><label for="blog_title_w">' . __('In bold:') . ' ' .
form::checkbox('blog_title_w', 1, $ductile_user['blog_title_w']) . '</label>' . '</p>' .

'<p class="field"><label for="blog_title_s">' . __('Font size (in em by default):') . '</label> ' .
form::field('blog_title_s', 7, 7, $ductile_user['blog_title_s']) . '</p>' .

'<p class="field picker"><label for="blog_title_c">' . __('Color:') . '</label> ' .
form::color('blog_title_c', 7, 7, $ductile_user['blog_title_c']) .
contrastRatio(
    $ductile_user['blog_title_c'],
    '#ffffff',
    (empty($ductile_user['blog_title_s']) ? '2em' : $ductile_user['blog_title_s']),
    $ductile_user['blog_title_w']
) .
'</p>' .
'</fieldset>';

echo '</div>';
echo '<div class="col">';

echo '<fieldset><legend>' . __('Post title') . '</legend>' .
'<p class="field"><label for="post_title_w">' . __('In bold:') . ' ' .
form::checkbox('post_title_w', 1, $ductile_user['post_title_w']) . '</label>' . '</p>' .

'<p class="field"><label for="post_title_s">' . __('Font size (in em by default):') . '</label> ' .
form::field('post_title_s', 7, 7, $ductile_user['post_title_s']) . '</p>' .

'<p class="field picker"><label for="post_title_c">' . __('Color:') . '</label> ' .
form::color('post_title_c', 7, 7, $ductile_user['post_title_c']) .
contrastRatio(
    $ductile_user['post_title_c'],
    '#ffffff',
    (empty($ductile_user['post_title_s']) ? '2.5em' : $ductile_user['post_title_s']),
    $ductile_user['post_title_w']
) .
'</p>' .
'</fieldset>';

echo '</div>';
echo '</div>';

echo '<fieldset><legend>' . __('Titles without link') . '</legend>' .

'<p class="field picker"><label for="post_simple_title_c">' . __('Color:') . '</label> ' .
form::color('post_simple_title_c', 7, 7, $ductile_user['post_simple_title_c']) .
contrastRatio(
    $ductile_user['post_simple_title_c'],
    '#ffffff',
    '1.1em',	// H5 minimum size
    false
) .
'</p>' .
'</fieldset>';

echo '<fieldset><legend>' . __('Inside posts links') . '</legend>' .
'<p class="field"><label for="post_link_w">' . __('In bold:') . ' ' .
form::checkbox('post_link_w', 1, $ductile_user['post_link_w']) . '</label>' . '</p>' .

'<p class="field picker"><label for="post_link_v_c">' . __('Normal and visited links color:') . '</label> ' .
form::color('post_link_v_c', 7, 7, $ductile_user['post_link_v_c']) .
contrastRatio(
    $ductile_user['post_link_v_c'],
    '#ffffff',
    '1em',
    $ductile_user['post_link_w']
) .
'</p>' .

'<p class="field picker"><label for="post_link_f_c">' . __('Active, hover and focus links color:') . '</label> ' .
form::color('post_link_f_c', 7, 7, $ductile_user['post_link_f_c']) .
contrastRatio(
    $ductile_user['post_link_f_c'],
    '#ebebee',
    '1em',
    $ductile_user['post_link_w']
) .
'</p>' .
'</fieldset>';

echo '<h3>' . __('Mobile specific settings') . '</h3>';

echo '<div class="two-cols">';
echo '<div class="col">';

echo '<fieldset><legend>' . __('Blog title') . '</legend>' .
'<p class="field"><label for="blog_title_w_m">' . __('In bold:') . ' ' .
form::checkbox('blog_title_w_m', 1, $ductile_user['blog_title_w_m']) . '</label>' . '</p>' .

'<p class="field"><label for="blog_title_s_m">' . __('Font size (in em by default):') . '</label> ' .
form::field('blog_title_s_m', 7, 7, $ductile_user['blog_title_s_m']) . '</p>' .

'<p class="field picker"><label for="blog_title_c_m">' . __('Color:') . '</label> ' .
form::color('blog_title_c_m', 7, 7, $ductile_user['blog_title_c_m']) .
contrastRatio(
    $ductile_user['blog_title_c_m'],
    '#d7d7dc',
    (empty($ductile_user['blog_title_s_m']) ? '1.8em' : $ductile_user['blog_title_s_m']),
    $ductile_user['blog_title_w_m']
) .
'</p>' .
'</fieldset>';

echo '</div>';
echo '<div class="col">';

echo '<fieldset><legend>' . __('Post title') . '</legend>' .
'<p class="field"><label for="post_title_w_m">' . __('In bold:') . ' ' .
form::checkbox('post_title_w_m', 1, $ductile_user['post_title_w_m']) . '</label>' . '</p>' .

'<p class="field"><label for="post_title_s_m">' . __('Font size (in em by default):') . '</label> ' .
form::field('post_title_s_m', 7, 7, $ductile_user['post_title_s_m']) . '</p>' .

'<p class="field picker"><label for="post_title_c_m">' . __('Color:') . '</label> ' .
form::color('post_title_c_m', 7, 7, $ductile_user['post_title_c_m']) .
contrastRatio(
    $ductile_user['post_title_c_m'],
    '#ffffff',
    (empty($ductile_user['post_title_s_m']) ? '1.5em' : $ductile_user['post_title_s_m']),
    $ductile_user['post_title_w_m']
) .
'</p>' .
'</fieldset>';

echo '</div>';
echo '</div>';

echo '<input type="hidden" name="conf_tab" value="css">';
echo '<p class="clear"><input type="submit" value="' . __('Save') . '" />' . dcCore::app()->formNonce() . '</p>';
echo '</form>';

echo '</div>'; // Close tab

dcPage::helpBlock('ductile');

// Legacy mode
if (!$standalone_config) {
    echo '<form style="display:none">';
}
