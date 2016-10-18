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

pimcore.registerNS("pimcore.document.pushover");
pimcore.document.pushover = Class.create(pimcore.document.document, {

    urlprefix : '/plugin/Pushover/',

    initialize: function(id) {

        this.id = intval(id);
        this.setType("pushover");
        this.addLoadingPanel();

        pimcore.plugin.broker.fireEvent("preOpenDocument", this, "pushover");
        this.getData();
    },

    init: function () {

        var user = pimcore.globalmanager.get("user");

        if (this.isAllowed("properties")) {
            this.properties = new pimcore.document.properties(this, "document");
        }

        if (user.isAllowed("notes_events")) {
            this.notes = new pimcore.element.notes(this, "document");
        }

        this.dependencies = new pimcore.element.dependencies(this, "document");
        this.tagAssignment = new pimcore.element.tag.assignment(this, "document");
    },

    getSaveData : function () {
        var parameters = {};

        parameters.id = this.id;
        var values = this.panel.getForm().getFieldValues();
        values.published = this.data.published;
        parameters.data = Ext.encode(values);

        if (this.isAllowed("properties")) {
            // properties
            try {
                parameters.properties = Ext.encode(this.properties.getValues());
            }
            catch (e) {
                //console.log(e);
            }
        }

        return parameters;
    },

    addTab: function () {

        var tabTitle = this.data.key;
        this.tabPanel = Ext.getCmp("pimcore_panel_tabs");
        var tabId = "document_" + this.id;
        this.tab = new Ext.Panel({
            id: tabId,
            title: tabTitle,
            closable:true,
            layout: "border",
            items: [
                this.getLayoutToolbar(),
                this.getTabPanel()
            ],
            iconCls: "pimcore_icon_" + this.data.type,
            document: this
        });

        this.tab.on("beforedestroy", function () {
            Ext.Ajax.request({
                url: "/admin/element/unlock-element",
                params: {
                    id: this.data.id,
                    type: "document"
                }
            });
        }.bind(this));

        // remove this instance when the panel is closed
        this.tab.on("destroy", function () {
            pimcore.globalmanager.remove("document_" + this.id);
            pimcore.helpers.forgetOpenTab("document_" + this.id + "_link");
        }.bind(this));

        this.tab.on("activate", function () {
            this.tab.updateLayout();
            pimcore.layout.refresh();
        }.bind(this));

        this.tab.on("afterrender", function (tabId) {
            this.tabPanel.setActiveItem(tabId);
            pimcore.plugin.broker.fireEvent("postOpenDocument", this, "link");
        }.bind(this, tabId));

        this.removeLoadingPanel();

        this.tabPanel.add(this.tab);

        // recalculate the layout
        pimcore.layout.refresh();
    },

    getLayoutToolbar : function () {

        if (!this.toolbar) {

            this.toolbarButtons = {};

            this.toolbarButtons.publish = new Ext.Button({
                text: t('save_and_publish'),
                iconCls: "pimcore_icon_publish",
                scale: "medium",
                handler: this.publish.bind(this)
            });


            this.toolbarButtons.unpublish = new Ext.Button({
                text: t('unpublish'),
                iconCls: "pimcore_icon_unpublish",
                scale: "medium",
                handler: this.unpublish.bind(this)
            });

            this.toolbarButtons.remove = new Ext.Button({
                tooltip: t('delete'),
                iconCls: "pimcore_icon_delete",
                scale: "medium",
                handler: this.remove.bind(this)
            });

            this.toolbarButtons.rename = new Ext.Button({
                tooltip: t('rename'),
                iconCls: "pimcore_icon_key pimcore_icon_overlay_go",
                scale: "medium",
                handler: function () {
                    var options = {
                        elementType: "document",
                        elementSubType: this.getType(),
                        id: this.id,
                        default: this.data.key
                    }
                    pimcore.elementservice.editElementKey(options);
                }.bind(this)
            });

            var buttons = [];

            if (this.isAllowed("publish")) {
                buttons.push(this.toolbarButtons.publish);
            }
            if (this.isAllowed("unpublish") && !this.data.locked) {
                buttons.push(this.toolbarButtons.unpublish);
            }

            buttons.push("-");

            if(this.isAllowed("delete") && !this.data.locked) {
                buttons.push(this.toolbarButtons.remove);
            }
            if(this.isAllowed("rename") && !this.data.locked) {
                buttons.push(this.toolbarButtons.rename);
            }

            buttons.push({
                tooltip: t('reload'),
                iconCls: "pimcore_icon_reload",
                scale: "medium",
                handler: this.reload.bind(this)
            });

            if (pimcore.elementservice.showLocateInTreeButton("document")) {
                buttons.push({
                    tooltip: t('show_in_tree'),
                    iconCls: "pimcore_icon_show_in_tree",
                    scale: "medium",
                    handler: this.selectInTree.bind(this)
                });
            }

            buttons.push(this.getTranslationButtons());

            buttons.push("-");
            buttons.push({
                xtype: 'tbtext',
                text: this.data.id,
                scale: "medium"
            });

            this.toolbar = new Ext.Toolbar({
                id: "document_toolbar_" + this.id,
                region: "north",
                border: false,
                cls: "main-toolbar",
                items: buttons,
                overflowHandler: 'scroller'
            });

            this.toolbar.on("afterrender", function () {
                window.setTimeout(function () {
                    // it's not possible to delete the root-node
                    if (this.id == 1) {
                        this.toolbarButtons.remove.hide();
                    }

                    if (!this.data.published) {
                        this.toolbarButtons.unpublish.hide();
                    }
                }.bind(this), 500);
            }.bind(this));
        }

        return this.toolbar;
    },

    getTabPanel: function () {

        var items = [];
        var user = pimcore.globalmanager.get("user");

        items.push(this.getLayoutForm());

        if (this.isAllowed("properties")) {
            items.push(this.properties.getLayout());
        }

        items.push(this.dependencies.getLayout());

        if (user.isAllowed("notes_events")) {
            items.push(this.notes.getLayout());
        }

        if (user.isAllowed("tags_assignment")) {
            items.push(this.tagAssignment.getLayout());
        }

        this.tabbar = new Ext.TabPanel({
            tabPosition: "top",
            region:'center',
            deferredRender:true,
            enableTabScroll:true,
            border: false,
            items: items,
            activeTab: 0
        });

        return this.tabbar;
    },

    getLayoutForm: function () {

        if (!this.panel) {
            this.panel = Ext.create('Ext.form.Panel', {

                title: t('pushover_properties'),
                bodyStyle:'padding:0 10px 0 10px;',
                border: false,
                autoScroll: true,
                iconCls: "pimcore_icon_settings",
                items: [
                    {
                        xtype:'fieldset',
                        title: t('pushover_settings'),
                        collapsible: true,
                        autoHeight:true,
                        labelWidth: 200,
                        defaultType: 'textfield',
                        defaults: {width: 700},
                        items :[
                            {
                                fieldLabel: t('pushover_title'),
                                name: 'title',
                                value: this.data.title
                            },
                            {
                                fieldLabel: t('pushover_message'),
                                name: 'message',
                                value: this.data.message
                            },
                            {
                                fieldLabel: t('pushover_recipient'),
                                name: 'recipient',
                                value: this.data.recipient
                            },
                            {
                                fieldLabel: t('pushover_sound'),
                                name: 'sound',
                                value: this.data.sound ? this.data.sound : '',
                                width: 500,
                                xtype: 'combo',
                                store: [
                                    ['', t('pushover_user_default')],
                                    ['pushover', 'pushover'],
                                    ['bike', 'bike'],
                                    ['bugle', 'bugle'],
                                    ['cashregister', 'cashregister'],
                                    ['classical', 'classical'],
                                    ['cosmic', 'cosmic'],
                                    ['falling', 'falling'],
                                    ['gamelan', 'gamelan'],
                                    ['incoming', 'incoming'],
                                    ['intermission', 'intermission'],
                                    ['magic', 'magic'],
                                    ['mechanical', 'mechanical'],
                                    ['pianobar', 'pianobar'],
                                    ['siren', 'siren'],
                                    ['spacealarm', 'spacealarm'],
                                    ['tugboat', 'tugboat'],
                                    ['alien', 'alien'],
                                    ['climb', 'climb'],
                                    ['persistent', 'persistent'],
                                    ['echo', 'echo'],
                                    ['updown', 'updown'],
                                    ['none', t('none')]
                                ],
                                triggerAction: 'all',
                                typeAhead: false,
                                editable: false,
                                forceSelection: true,
                                queryMode: 'local'
                            }
                        ]
                    }
                ]
            });
        }

        return this.panel;
    }
});

