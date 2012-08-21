<?php
/*************************************************************************************************
* The contents of this file is subject to JPL TSolucio, S.L. Copyright License (c)
* You may not use this extension except in the vtiger CRM install for which it was sold
*************************************************************************************************
*  Author       : JPL TSolucio, S. L.
*************************************************************************************************/
/*  Configuration variables  */

// Window
$window_width = 400;
$window_height = 400;
$window_top = 100;
$window_left = 40;
$edit_window_width = 500;
$edit_window_height = 400;

// TOOLTIP
$tooltip_delayIn='3000';   // delay before showing tooltip (ms)
$tooltip_delayOut='600';   // delay before hiding tooltip (ms)
$tooltip_offset='-10';     // pixel offset of tooltip from element
$tooltip_fade='true';      // fade tooltips in/out?
$tooltip_fallback='';      // fallback text to use when no tooltip text
$tooltip_gravity='n';      // gravity: nw | n | ne | w | e | sw | s | se
$tooltip_html='true';      // is tooltip content HTML?
$tooltip_live='false';     // use live event support?
$tooltip_opacity='0.8';    // opacity of tooltip
$tooltip_title='title';    // attribute/callback containing tooltip text
$tooltip_trigger='hover';  // how tooltip is triggered - hover | focus | manual

// Tipsy settings
$tipsy_settings="{delayIn: $tooltip_delayIn, delayOut: $tooltip_delayOut, html: true, offset: $tooltip_offset, fade: $tooltip_fade}";