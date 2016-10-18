/**
 * Pushover Documents.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2016 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS("pimcore.plugin.pushover");
pimcore.plugin.pushover = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.pushover";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {

        var user = pimcore.globalmanager.get('user');

        if (user.isAllowed('plugins')) {

            var settingsMenu = new Ext.Action({
                text: t('pushover_settings'),
                iconCls: 'pushover_icon_settings',
                handler:this.openSettings
            });

            layoutToolbar.settingsMenu.add(settingsMenu);
        }
    },

    prepareDocumentTreeContextMenu : function(menu, tree, record) {
        menu.insert(5, {
            text: "&gt; " + t("pushover_document"),
            iconCls: "pimcore_icon_pushover pimcore_icon_overlay_add",
            handler: tree.addDocument.bind(tree, tree, record, "pushover")
        });
    },

    openSettings : function ()
    {
        try {
            pimcore.globalmanager.get('pushover_settings').activate();
        }
        catch (e) {
            pimcore.globalmanager.add('pushover_settings', new pimcore.plugin.pushover.settings());
        }
    }
});

var pushoverPlugin = new pimcore.plugin.pushover();

