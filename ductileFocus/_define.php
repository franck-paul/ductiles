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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Ductile Focus',
    'Variation de Ductile pour un blog mixte de textes et photos',
    'Kozlika et Franck Paul',
    '1.0',
    [
        'requires'          => [['core', '2.24']],
        'standalone_config' => true,
        'type'              => 'theme',
    ]
);
