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

use Dotclear\Core\Process;

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
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

use dcPage;
use Dotclear\App;

if (!defined('DC_CONTEXT_ADMIN')) {
    return false;
}
// admin part below

# Behaviors
App::behavior()->addBehavior('adminPageHTMLHead', [__NAMESPACE__ . '\tplDuctileMagThemeAdmin', 'adminPageHTMLHead']);

class tplDuctileMagThemeAdmin
{
    public static function adminPageHTMLHead()
    {
        if (App::blog()->settings()->system->theme != 'ductileFocus') {
            return;
        }

        echo "\n" . '<!-- Header directives for Ductile Focus configuration -->' . "\n";
        if (!App::auth()->prefs()->accessibility->nodragdrop) {
            echo
            dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
            dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js');
            echo <<<EOT
                <script>
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
