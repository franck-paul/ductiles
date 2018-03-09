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
 * @copyright GPL-2.0-only
 */

namespace themes\DuctileFocus;

if (!defined('DC_RC_PATH')) {return;}
// public part below

if (!defined('DC_CONTEXT_ADMIN')) {return false;}
// admin part below

# Behaviors
$GLOBALS['core']->addBehavior('adminPageHTMLHead', array(__NAMESPACE__ . '\tplDuctileMagThemeAdmin', 'adminPageHTMLHead'));

class tplDuctileMagThemeAdmin
{
    public static function adminPageHTMLHead()
    {
        global $core;
        if ($core->blog->settings->system->theme != 'ductileFocus') {return;}

        echo "\n" . '<!-- Header directives for Ductile Focus configuration -->' . "\n";
        $core->auth->user_prefs->addWorkspace('accessibility');
        if (!$core->auth->user_prefs->accessibility->nodragdrop) {
            echo
            \dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
            \dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js');
            echo <<<EOT
<script type="text/javascript">
$(function() {
    $("#stickerslist").sortable({'cursor':'move'});
    $("#stickerslist tr").hover(function () {
        $(this).css({'cursor':'move'});
    }, function () {
        $(this).css({'cursor':'auto'});
    });
    $('#theme_config').submit(function() {
        var order=[];
        $("#stickerslist tr td input.position").each(function() {
            order.push(this.name.replace(/^order\[([^\]]+)\]$/,'$1'));
        });
        $("input[name=ds_order]")[0].value = order.join(',');
        return true;
    });
    $("#stickerslist tr td input.position").hide();
    $("#stickerslist tr td.handle").addClass('handler');
});
</script>
EOT;
        }

    }
}
