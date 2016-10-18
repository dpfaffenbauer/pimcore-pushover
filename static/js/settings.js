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

pimcore.registerNS('pimcore.plugin.pushover.settings');
pimcore.plugin.pushover.settings = Class.create({

    shopPanels : {},

    initialize: function () {

        this.getData();
    },

    getData: function () {
        Ext.Ajax.request({
            url: '/plugin/Pushover/settings/get',
            success: function (response) {

                this.data = Ext.decode(response.responseText);

                this.getTabPanel();

            }.bind(this)
        });
    },

    getValue : function (key) {
        var current = null;

        if (this.data.settings.hasOwnProperty(key)) {
            current = this.data.settings[key];
        }

        if (typeof current != 'object' && typeof current != 'array' && typeof current != 'function') {
            return current;
        }

        return '';
    },

    getTabPanel: function () {
        if (!this.panel) {
            var me = this;

            this.panel = Ext.create('Ext.panel.Panel', {
                id: 'pushover_settings',
                title: t('pushover_settings'),
                iconCls: 'pushover_icon_settings',
                border: false,
                layout: 'fit',
                closable:true
            });

            var tabPanel = Ext.getCmp('pimcore_panel_tabs');
            tabPanel.add(this.panel);
            tabPanel.setActiveItem('pushover_settings');

            this.panel.on('destroy', function () {
                pimcore.globalmanager.remove('pushover_settings');
            }.bind(this));

            this.layout = Ext.create('Ext.panel.Panel', {
                bodyStyle: 'padding:20px 5px 20px 5px;',
                border: false,
                autoScroll: true,
                forceLayout: true,
                defaults: {
                    forceLayout: true
                },
                buttons: [
                    {
                        text: t('save'),
                        handler: this.save.bind(this),
                        iconCls: 'pimcore_icon_apply'
                    }
                ]
            });

            this.settingsPanel = Ext.create('Ext.form.Panel', {
                border: false,
                autoScroll: true,
                forceLayout: true,
                defaults: {
                    forceLayout: true
                },
                items : [
                    {
                        xtype: 'fieldset',
                        title: t('pushover_settings'),
                        autoHeight: true,
                        labelWidth: 250,
                        defaultType: 'textfield',
                        defaults: { width: 600 },
                        items: [
                            {
                                fieldLabel: t('pushover_applicationid'),
                                xtype: 'textfield',
                                name: 'APPLICATION.TOKEN',
                                value: this.getValue('APPLICATION.TOKEN')
                            }
                        ]
                    }
                ]
            });

            this.layout.add(this.settingsPanel);

            this.panel.add(this.layout);

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    activate: function () {
        var tabPanel = Ext.getCmp('pimcore_panel_tabs');
        tabPanel.setActiveItem('pushover_settings');
    },

    save: function () {
        var values = {};

        var data = this.settingsPanel.getForm().getFieldValues();

        Ext.Ajax.request({
            url: '/plugin/Pushover/settings/set',
            method: 'post',
            params: {
                settings : Ext.encode(data)
            },
            success: function (response) {
                try {
                    var res = Ext.decode(response.responseText);
                    if (res.success) {
                        pimcore.helpers.showNotification(t('success'), t('success'), 'success');

                        Ext.MessageBox.confirm(t('info'), t('reload_pimcore_changes'), function (buttonValue) {
                            if (buttonValue == 'yes') {
                                window.location.reload();
                            }
                        }.bind(this));

                    } else {
                        pimcore.helpers.showNotification(t('error'), t('error'),
                            'error', t(res.message));
                    }
                } catch (e) {
                    pimcore.helpers.showNotification(t('error'), t('error'), 'error');
                }
            }
        });
    }
});
