<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MyPlugin;

class MyPlugin extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'CronArchive.getArchivingAPIMethodForPlugin' => 'getArchivingAPIMethodForPlugin',
        ];
    }

    // support archiving just this plugin via core:archive
    public function getArchivingAPIMethodForPlugin(&$method, $plugin)
    {
        if ($plugin == 'MyPlugin') {
            $method = 'MyPlugin.getExampleArchivedMetric';
        }
    }
    public function getStylesheetFiles(&$files)
    {
        $files[] = "plugins/MyPlugin/style/myCssStylesheet.css";
    }
}
