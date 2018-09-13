<?php
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

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    "Ductile Photo",                           // Name
    "Variation de Ductile pour un photoblog",  // Description
    "Kozlika et Franck Paul",                  // Author
    '0.8',                                     // Version
    [                                          // Properties
        'requires'          => [['core', '2.13']], // Dependencies
        'standalone_config' => true,
        'type'              => 'theme'
    ]
);
