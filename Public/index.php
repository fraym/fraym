<?php
/**
 * @link      http://fraym.org
 * @author    Dominik Weber <info@fraym.org>
 * @copyright Dominik Weber <info@fraym.org>
 * @license   http://www.opensource.org/licenses/gpl-license.php GNU General Public License, version 2 or later (see the LICENSE file)
 */
require '../Bootstrap.php';

$hook = $diContainer->get('Fraym\Hook\Hook');
$hook->load();

$core = $diContainer->get('Fraym\Core');
$core->init();