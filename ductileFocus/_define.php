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
    'Ductile Focus',                                               // Name
    'Variation de Ductile pour un blog mixte de textes et photos', // Description
    'Kozlika et Franck Paul',                                      // Author
    '0.6',                                                         // Version
    [                                                              // Properties
        'requires'          => [['core', '2.19']],                     // Dependencies
        'standalone_config' => true,
        'type'              => 'theme'
    ]
);
