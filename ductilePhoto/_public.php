<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
# This file is part of Ductile, a theme for Dotclear
#
# Copyright (c) 2011 - Association Dotclear
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

# Behaviors
$core->addBehavior('publicHeadContent',array('tplDuctilePhotoTheme','publicHeadContent'));
$core->addBehavior('publicInsideFooter',array('tplDuctilePhotoTheme','publicInsideFooter'));
$core->addBehavior('tplIfConditions',array('tplDuctilePhotoTheme','tplIfConditions'));

# Templates
$core->tpl->addValue('ductileNbEntryPerPage',array('tplDuctilePhotoTheme','ductileNbEntryPerPage'));
$core->tpl->addBlock('EntryIfContentIsCut',array('tplDuctilePhotoTheme','EntryIfContentIsCut'));

class tplDuctilePhotoTheme
{
	public static function ductileNbEntryPerPage($attr)
	{
		global $core;

		$nb = 0;
		$s = $core->blog->settings->themes->get($core->blog->settings->system->theme.'_entries_counts');
		if ($s !== null) {
			$s = @unserialize($s);
			if (is_array($s)) {
				if (isset($s[$core->url->type])) {
					// Nb de billets par page défini par la config du thème
					$nb = (integer) $s[$core->url->type];
				} else {
					if (($core->url->type == 'default-page') && (isset($s['default']))) {
						// Les pages 2 et suivantes de la home ont le même nombre de billet que la première page
						$nb = (integer) $s['default'];
					}
				}
			}
		}

		if ($nb == 0) {
			if (!empty($attr['nb'])) {
				// Nb de billets par page défini par défaut dans le template
				$nb = (integer) $attr['nb'];
			}
		}

		if ($nb > 0)
			return '<?php $_ctx->nb_entry_per_page = '.$nb.' ; ?>';
	}
	
	public static function tplIfConditions($tag,$attr,$content,$if)
	{
		global $core;

		if ($tag == 'EntryIf' && isset($attr['has_img'])) {

			$sign = (boolean) $attr['has_img'] ? '' : '!';
			$with_category = !empty($attr['with_category']) ? 'true' : 'false';
			$if[] = $sign.'(tplDuctileFocusTheme::tplIfConditionsHelper('.$with_category.'))';

		} elseif ($tag == 'EntryIf' && isset($attr['focus_cat_image'])) {

			$sign = (boolean) $attr['focus_cat_image'] ? '' : '!';
			$s = $core->blog->settings->themes->get($core->blog->settings->system->theme.'_focus');
			if ($s === null) return;
			$s = @unserialize($s);
			if (!is_array($s)) return;
			if (!isset($s[2])) return;
			if (!isset($s[2]['cat'])) return;

			$c = $core->blog->getCategories(array('cat_url' => $s[2]['cat']));
			$cc = $core->blog->getCategoryFirstChildren($c->cat_id);
			$ret = 'in_array($_ctx->posts->cat_id,array('.$c->cat_id;
			if ($cc) {
				while ($cc->fetch()) {
					$ret .= ','.$cc->cat_id;
				}
			}
			$ret .= '))';
			$if[] = $sign.$ret;
		}
	}

	public static function tplIfConditionsHelper($with_category = false)
	{
		$ret = '';
		$ret = context::EntryFirstImageHelper('s',$with_category);
		return ($ret != '');
	}
	
	public static function EntryIfContentIsCut($attr,$content)
	{
		global $core;
		
		if (empty($attr['cut_string']) || !empty($attr['full'])) {
			return '';
		}
		
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}

		$short = $core->tpl->getFilters($attr);
		$cut = $attr['cut_string'];
		$attr['cut_string'] = 0;
		$full = $core->tpl->getFilters($attr);
		$attr['cut_string'] = $cut;

		return '<?php if (strlen('.sprintf($full,'$_ctx->posts->getContent('.$urls.')').') > '.
			'strlen('.sprintf($short,'$_ctx->posts->getContent('.$urls.')').')) : ?>'.
			$content.
			'<?php endif; ?>';
	}
	
	public static function publicInsideFooter($core)
	{
		$res = '';
		$default = false;
		$img_url = $core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme.'/img/';

		$s = $core->blog->settings->themes->get($core->blog->settings->system->theme.'_stickers');

		if ($s === null) {
			$default = true;
		} else {
			$s = @unserialize($s);
			if (!is_array($s)) {
				$default = true;
			} else {
				$s = array_filter($s,"self::cleanStickers");
				if (count($s) == 0) {
					$default = true;
				} else {
					$count = 1;
					foreach ($s as $sticker) {
						$res .= self::setSticker($count,($count == count($s)),$sticker['label'],$sticker['url'],$img_url.$sticker['image']);
						$count++;
					}
				}
			}
		}

		if ($default || $res == '') {
			$res = self::setSticker(1,true,__('Subscribe'),$core->blog->url.$core->url->getBase('feed').'/atom',$img_url.'sticker-feed.png');
		}

		if ($res != '') {
			$res = '<ul id="stickers">'."\n".$res.'</ul>'."\n";
			echo $res;
		}
	}
	
	protected static function cleanStickers($s)
	{
		if (is_array($s)) {
			if (isset($s['label']) && isset($s['url']) && isset($s['image'])) {
				if ($s['label'] != null && $s['url'] != null && $s['image'] != null) {
					return true;
				}
			}
		}
		return false;
	}
	
	protected static function setSticker($position,$last,$label,$url,$image)
	{
		return '<li id="sticker'.$position.'"'.($last ? ' class="last"' : '').'>'."\n".
			'<a href="'.$url.'">'."\n".
			'<img alt="" src="'.$image.'" />'."\n".
			'<span>'.$label.'</span>'."\n".
			'</a>'."\n".
			'</li>'."\n";
	}

	public static function publicHeadContent($core)
	{
		
		echo 
			'<style type="text/css">'."\n".
			'/* '.__('Additionnal style directives').' */'."\n".
			self::ductileStyleHelper().
			"</style>\n";
			
		$ambiance_src = self::ambianceCssSrc();
		if ($ambiance_src != '') {
			echo '<link media="screen" href="'.$ambiance_src.'" type="text/css" rel="stylesheet">'."\n";
		}
		
		echo
			'<script type="text/javascript" src="'.
			$core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme.
			'/ductile.js"></script>'."\n";
	}
	
	protected static function ambianceCssSrc()
	{
		$s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme.'_style');
		if ($s === null) {
			return;
		}
		$s = @unserialize($s);
		if (!is_array($s)) {
			return;
		}
		
		$css_src = $GLOBALS['core']->blog->settings->system->themes_url.'/'.$GLOBALS['core']->blog->settings->system->theme;
		if (isset($s['ambiance'])) {
			if ($s['ambiance'] !== null) {
				if ($s['ambiance'] != '') {
					$css_src .= '/'.$s['ambiance'].'-ambiance.css';
				}
			}
		} else {
			return;
		}
		
		return $css_src;
	}

	public static function ductileStyleHelper()
	{
		$s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme.'_style');

		if ($s === null) {
			return;
		}

		$s = @unserialize($s);
		if (!is_array($s)) {
			return;
		}

		$css = array();

		# Properties
		
		# Blog logo
		$selectors = 'h1 a';
		$logoSrc = self::logoSrc();
		if ($logoSrc != '') self::prop($css,$selectors,'background','transparent url("'.$logoSrc.'") no-repeat left center');
		
		# Blog description
		$selectors = '#blogdesc';
		if (isset($s['subtitle_hidden'])) self::prop($css,$selectors,'display',($s['subtitle_hidden'] ? 'none' : null));

		# Main font
		$selectors = 'body';
		if (isset($s['body_font'])) self::prop($css,$selectors,'font-family',self::fontDef($s['body_font']));

		# Inside posts links font weight
		$selectors = '.post-excerpt a, .post-content a';
		if (isset($s['post_link_w'])) self::prop($css,$selectors,'font-weight',($s['post_link_w'] ? 'bold' : 'normal'));

		# Inside posts links colors (normal, visited)
		$selectors = '.post-excerpt a:link, .post-excerpt a:visited, .post-content a:link, .post-content a:visited';
		if (isset($s['post_link_v_c'])) self::prop($css,$selectors,'color',$s['post_link_v_c']);

		# Inside posts links colors (hover, active, focus)
		$selectors = '.post-excerpt a:hover, .post-excerpt a:active, .post-excerpt a:focus, .post-content a:hover, .post-content a:active, .post-content a:focus';
		if (isset($s['post_link_f_c'])) self::prop($css,$selectors,'color',$s['post_link_f_c']);

		# Style directives
		$res = '';
		foreach ($css as $selector => $values) {
			$res .= $selector." {\n";
			foreach ($values as $k => $v) {
				$res .= $k.':'.$v.";\n";
			}
			$res .= "}\n";
		}

		# Large screens
		$css_large = array();

		# Blog title font weight
		$selectors = 'h1, h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
		if (isset($s['blog_title_w'])) self::prop($css_large,$selectors,'font-weight',($s['blog_title_w'] ? 'bold' : 'normal'));
		
		# Blog title font size
		$selectors = 'h1';
		if (isset($s['blog_title_s'])) self::prop($css_large,$selectors,'font-size',$s['blog_title_s']);
		
		# Blog title color
		$selectors = 'h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
		if (isset($s['blog_title_c'])) self::prop($css_large,$selectors,'color',$s['blog_title_c']);

		# Post title font weight
		$selectors = 'h2.post-title, h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
		if (isset($s['post_title_w'])) self::prop($css_large,$selectors,'font-weight',($s['post_title_w'] ? 'bold' : 'normal'));
		
		# Post title font size
		$selectors = 'h2.post-title';
		if (isset($s['post_title_s'])) self::prop($css_large,$selectors,'font-size',$s['post_title_s']);
		
		# Post title color
		$selectors = 'h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
		if (isset($s['post_title_c'])) self::prop($css_large,$selectors,'color',$s['post_title_c']);

		# Simple title color (title without link)
		$selectors = '#content-info h2, .post-title, .post h3, .post h4, .post h5, .post h6, .arch-block h3';
		if (isset($s['post_simple_title_c'])) self::prop($css_large,$selectors,'color',$s['post_simple_title_c']);

		# Style directives for large screens
		if (count($css_large)) {
			$res .= '@media only screen and (min-width: 481px) {'."\n";
			foreach ($css_large as $selector => $values) {
				$res .= $selector." {\n";
				foreach ($values as $k => $v) {
					$res .= $k.':'.$v.";\n";
				}
				$res .= "}\n";
			}
			$res .= "}\n";
		}

		# Small screens
		$css_small = array();

		# Blog title font weight
		$selectors = 'h1, h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
		if (isset($s['blog_title_w_m'])) self::prop($css_small,$selectors,'font-weight',($s['blog_title_w_m'] ? 'bold' : 'normal'));
		
		# Blog title font size
		$selectors = 'h1';
		if (isset($s['blog_title_s_m'])) self::prop($css_small,$selectors,'font-size',$s['blog_title_s_m']);
		
		# Blog title color
		$selectors = 'h1 a:link, h1 a:visited, h1 a:hover, h1 a:visited, h1 a:focus';
		if (isset($s['blog_title_c_m'])) self::prop($css_small,$selectors,'color',$s['blog_title_c_m']);

		# Post title font weight
		$selectors = 'h2.post-title, h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
		if (isset($s['post_title_w_m'])) self::prop($css_small,$selectors,'font-weight',($s['post_title_w_m'] ? 'bold' : 'normal'));
		
		# Post title font size
		$selectors = 'h2.post-title';
		if (isset($s['post_title_s_m'])) self::prop($css_small,$selectors,'font-size',$s['post_title_s_m']);
		
		# Post title color
		$selectors = 'h2.post-title a:link, h2.post-title a:visited, h2.post-title a:hover, h2.post-title a:visited, h2.post-title a:focus';
		if (isset($s['post_title_c_m'])) self::prop($css_small,$selectors,'color',$s['post_title_c_m']);

		# Style directives for small screens
		if (count($css_small)) {
			$res .= '@media only screen and (max-width: 480px) {'."\n";
			foreach ($css_small as $selector => $values) {
				$res .= $selector." {\n";
				foreach ($values as $k => $v) {
					$res .= $k.':'.$v.";\n";
				}
				$res .= "}\n";
			}
			$res .= "}\n";
		}
		
		return $res;
	}
	
	protected static function logoSrc()
	{
		$s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme.'_style');
		if ($s === null) {
			return;
		}
		$s = @unserialize($s);
		if (!is_array($s)) {
			return;
		}
		
		$img_url = $GLOBALS['core']->blog->settings->system->themes_url.'/'.$GLOBALS['core']->blog->settings->system->theme.'/img/logo.png';
		if (isset($s['logo_src'])) {
			if ($s['logo_src'] !== null) {
				if ($s['logo_src'] != '') {
					if ((substr($s['logo_src'],0,1) == '/') || (parse_url($s['logo_src'],PHP_URL_SCHEME) != '')) {
						// absolute URL
						$img_url = $s['logo_src'];
					} else {
						// relative URL (base = img folder of ductile photo theme)
						$img_url = $GLOBALS['core']->blog->settings->system->themes_url.'/'.$GLOBALS['core']->blog->settings->system->theme.'/img/'.$s['logo_src'];
					}
				}
			}
		}
		
		return $img_url;
	}
	
	protected static $fonts = array(
		// Serif families
		'Times New Roman' => 'Cambria, "Hoefler Text", Utopia, "Liberation Serif", "Nimbus Roman No9 L Regular", Times, "Times New Roman", serif',
		'Georgia' => 'Constantia, "Lucida Bright", Lucidabright, "Lucida Serif", Lucida, "DejaVu Serif", "Bitstream Vera Serif", "Liberation Serif", Georgia, serif',
		'Garamond' => '"Palatino Linotype", Palatino, Palladio, "URW Palladio L", "Book Antiqua", Baskerville, "Bookman Old Style", "Bitstream Charter", "Nimbus Roman No9 L", Garamond, "Apple Garamond", "ITC Garamond Narrow", "New Century Schoolbook", "Century Schoolbook", "Century Schoolbook L", Georgia, serif',

		// Sans-serif families
		'Helvetica/Arial' => 'Frutiger, "Frutiger Linotype", Univers, Calibri, "Gill Sans", "Gill Sans MT", "Myriad Pro", Myriad, "DejaVu Sans Condensed", "Liberation Sans", "Nimbus Sans L", Tahoma, Geneva, "Helvetica Neue", Helvetica, Arial, sans-serif',
		'Verdana' => 'Corbel, "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", "Bitstream Vera Sans", "Liberation Sans", Verdana, "Verdana Ref", sans-serif',
		'Trebuchet MS' => '"Segoe UI", Candara, "Bitstream Vera Sans", "DejaVu Sans", "Bitstream Vera Sans", "Trebuchet MS", Verdana, "Verdana Ref", sans-serif',

		// Cursive families
		'Impact' => 'Impact, Haettenschweiler, "Franklin Gothic Bold", Charcoal, "Helvetica Inserat", "Bitstream Vera Sans Bold", "Arial Black", sans-serif',

		// Monospace families
		'Monospace' => 'Consolas, "Andale Mono WT", "Andale Mono", "Lucida Console", "Lucida Sans Typewriter", "DejaVu Sans Mono", "Bitstream Vera Sans Mono", "Liberation Mono", "Nimbus Mono L", Monaco, "Courier New", Courier, monospace'
	);

	protected static function fontDef($c)
	{
		return isset(self::$fonts[$c]) ? self::$fonts[$c] : null;
	}

	protected static function prop(&$css,$selector,$prop,$value)
	{
		if ($value) {
			$css[$selector][$prop] = $value;
		}
	}
}
?>