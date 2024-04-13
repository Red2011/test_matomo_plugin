<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MyPlugin;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;

/**
 * This class allows you to add, remove or rename menu items.
 * To configure a menu (such as Admin Menu, Top Menu, User Menu...) simply call the corresponding methods as
 * described in the API-Reference http://developer.piwik.org/api-reference/Piwik/Menu/MenuAbstract
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        // $menu->addItem('MyPlugin_MyTopItem', null, $this->urlForDefaultAction(), $orderId = 30);
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        // reuse an existing category. Execute the showList() method within the controller when menu item was clicked
        // $menu->addManageItem('MyPlugin_MyUserItem', $this->urlForAction('showList'), $orderId = 30);
        // $menu->addPlatformItem('MyPlugin_MyUserItem', $this->urlForDefaultAction(), $orderId = 30);

        // or create a custom category
        $menu->addItem('personal', 'MyPlugin', $this->urlForDefaultAction(), $orderId = 30);
    }
}
