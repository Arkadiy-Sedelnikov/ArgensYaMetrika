<?php
/**
 * @package		mod_argensyametrika
 * @copyright 	Copyright (c) 2015 Arkadiy Sedelnikov
 * @license 	GNU General Public License version 3 or later
 */


defined('_JEXEC') or die();
jimport('joomla.form.formfield');

class JFormFieldAppUrl extends JFormField
{
    public $type = 'AppUrl';
    /**
     * Method to get the field input markup.
     *
     * @return	string The field input markup.
     * @since	1.6
     */
    protected function getInput()
    {
        $html = '<a target="_blank" href="https://oauth.yandex.ru/client/new">https://oauth.yandex.ru/client/new</a>';
        return $html;
    }
}
