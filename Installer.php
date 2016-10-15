<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\HAgenda;

/**
 * This class describes the behavio of the installer for the plugin HAgenda
 */
class Installer extends PluginInstaller{
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install(){

        HAgendaEvent::createTable();

        if(Plugin::existAndIsActive('h-connect')){
            HAgendaContact::createTable();
        }
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall(){
        HAgendaContact::dropTable();

        HAgendaEvent::dropTable();
    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){

        $menu = MenuItem::getByName('utility.main');

        if(!$menu)
            $menu = MenuItem::add(array(
                'plugin' => 'utility',
                'name' => 'main',
                'labelKey' => $this->_plugin . '.main-menu-title',
                'icon' => 'legal',
                'active' => 1
            )); 

        MenuItem::add(array(
            'plugin' => $this->_plugin,
            'name' => 'agenda',
            'labelKey' => $this->_plugin . '.agenda-menu-title',
            'action' => 'h-agenda-index',
            'parentId' => $menu->id,
            'icon' => 'calendar',
            'active' => 1
        ));
    }

    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        $menus = MenuItem::getPluginMenuItems($this->_plugin);
        foreach($menus as $menu){
            $menu->delete();
        }
    }

    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if
     * configurable.
     */
    /*
    public function settings(){

    }
    */
}