<?php
/**
 * @package		mod_argensyametrika
 * @copyright 	Copyright (c) 2015 Arkadiy Sedelnikov
 * @license 	GNU General Public License version 3 or later
 */


defined('_JEXEC') or die();
jimport('joomla.form.formfield');

class JFormFieldTokenUrl extends JFormField
{
    public $type = 'TokenUrl';
    /**
     * Method to get the field input markup.
     *
     * @return	string The field input markup.
     * @since	1.6
     */
    protected function getInput()
    {
        $params = $this->form->getValue('params');
        $app_id = isset($params->app_id) ? (string)$params->app_id : '';

        if(empty($app_id))
        {
            $html = 'Создайте приложение, вставтье его ID в поле "ID приложения" и сохраните настройки.';
        }
        else
        {
            $html = '<a target="_blank" href="https://oauth.yandex.ru/authorize?response_type=token&client_id='.$app_id.'">https://oauth.yandex.ru/authorize?response_type=token&client_id='.$app_id.'</a>';
        }

        return $html;
    }
}
