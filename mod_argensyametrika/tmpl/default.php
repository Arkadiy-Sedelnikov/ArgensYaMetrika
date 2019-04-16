<?php
/**
 * @package		mod_argensyametrika
 * @copyright 	Copyright (c) 2015 Arkadiy Sedelnikov
 * @license 	GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

?>
<div id="line_chartdiv" style="width:100%; height:<?php echo $height; ?>px;"></div>
<h3>Суммарная статистика</h3>
<div class="row-fluid">
    <div class="span6">
        <p>Посетители: <?php echo $visitors ?></p>
        <p>Новые посетители: <?php echo $new_visitors ?></p>
        <p>Процент новых посетителей: <?php echo $new_visitors_perc ?> %</p>
    </div>
    <div class="span6">
        <p>Визиты: <?php echo $visits ?></p>
        <p>Просмотры: <?php echo $page_views ?></p>
    </div>
</div>
