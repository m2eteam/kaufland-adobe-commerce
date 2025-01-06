define([
    'jquery',
    'Kaufland/Grid',
    'Magento_Ui/js/modal/modal'
], function (jquery, grid, modal) {

    window.ControlPanelDatabaseGrid = Class.create(Grid, {

        prepareActions: function () {
            this.actions = {
                deleteTableRowsAction: function (id) {
                    this.deleteTableRows(id);
                }.bind(this),
                updateTableCellsAction: function () {
                    this.openTableCellsPopup('update');
                }.bind(this)
            }
        },

        // ---------------------------------------

        deleteTableRows: function (id) {
            var self = this,
                    confirmAction,
                    selectedIds = id ? id : self.getSelectedProductsString();

            confirmAction = function () {
                new Ajax.Request(Kaufland.url.get('controlPanel/deleteTableRows'), {
                    method: 'post',
                    parameters: {
                        ids: selectedIds
                    },
                    onSuccess: function (transport) {
                        self.unselectAllAndReload();
                    }
                });
            };

            if (id) {
                self.confirm({
                    actions: {
                        confirm: function () {
                            confirmAction();
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            } else {
                confirmAction();
            }
        },

        openTableCellsPopup: function (mode) {
            var self = this;

            new Ajax.Request(Kaufland.url.get('controlPanel/getTableCellsPopupHtml'), {
                method: 'post',
                parameters: {
                    ids: self.getSelectedProductsString(),
                    mode: mode
                },
                onSuccess: function (transport) {

                    var containerEl = jQuery('#controlPanel_manage_row_popup_content').get(0);
                    if (!containerEl) {
                        containerEl = document.createElement('div');
                        containerEl.id = 'controlPanel_manage_row_popup_content';
                    }

                    containerEl.update(transport.responseText);

                    self.popup = jQuery(containerEl);

                    modal({
                        title: mode == 'update' ? 'Edit Table Records' : 'Add Table Row',
                        modalClass: 'width-50',
                        type: 'popup',
                        buttons: [
                            {
                                text: 'Close',
                                class: 'action',
                                click: function () {
                                    self.popup.modal('closeModal');
                                }
                            },
                            {
                                text: 'Confirm',
                                class: 'action primary',
                                click: function () {
                                    mode == 'update' ? self.confirmUpdateCells()
                                            : self.confirmAddRow();
                                }
                            }
                        ]
                    }, self.popup);

                    self.popup.modal('openModal');
                }
            });
        },

        confirmUpdateCells: function () {
            var self = this;

            if (!this.isAnySwitcherEnabled()) {

                self.alert('You should select columns.');
                return;
            }

            new Ajax.Request(Kaufland.url.get('controlPanel/updateTableCells'), {
                method: 'post',
                asynchronous: false,
                parameters: Form.serialize($('controlPanel_tabs_database_table_cells_popup_form')),
                onSuccess: function (transport) {
                    self.unselectAllAndReload();
                    self.popup && self.popup.modal('closeModal');
                }
            });
        },

        confirmAddRow: function () {
            var self = this;

            if (!this.isAnySwitcherEnabled()) {

                self.alert('You should select columns.');
                return;
            }

            new Ajax.Request(Kaufland.url.get('controlPanel/addTableRow'), {
                method: 'post',
                asynchronous: false,
                parameters: Form.serialize($('controlPanel_tabs_database_table_cells_popup_form')),
                onSuccess: function (transport) {
                    self.getGridObj().reload();
                    self.popup && self.popup.modal('closeModal');
                }
            });
        },

        // ---------------------------------------

        mouseOverCell: function () {
            var cellId = this.id;

            if ($(cellId + '_save_link').getStyle('display') != 'none') {
                return;
            }

            $(cellId + '_edit_link').show();
            $(cellId + '_view_link').hide();
            $(cellId + '_save_link').hide();
        },

        mouseOutCell: function () {
            var cellId = this.id;

            if ($(cellId + '_save_link').getStyle('display') != 'none') {
                return;
            }

            $(cellId + '_edit_link').hide();
            $(cellId + '_view_link').hide();
            $(cellId + '_save_link').hide();
        },

        // ---------------------------------------

        switchCellToView: function (cellId) {
            $(cellId + '_edit_link').show();
            $(cellId + '_view_link').hide();
            $(cellId + '_save_link').hide();

            $(cellId + '_edit_container').hide();
            $(cellId + '_view_container').show();
        },

        switchCellToEdit: function (cellId) {
            $(cellId + '_edit_link').hide();
            $(cellId + '_view_link').show();
            $(cellId + '_save_link').show();

            $(cellId + '_edit_container').show();
            $(cellId + '_view_container').hide();
        },

        saveTableCell: function (rowId, columnName) {
            var self = this;
            var params = {
                ids: rowId,
                cells: columnName
            };

            var cellId = 'table_row_cell_' + columnName + '_' + rowId;
            params['value_' + columnName] = $(cellId + '_edit_input').value;

            new Ajax.Request(Kaufland.url.get('controlPanel/updateTableCells'), {
                method: 'post',
                asynchronous: false,
                parameters: params,
                onSuccess: function (transport) {
                    self.switchCellToView(cellId);
                    self.getGridObj().reload();
                }
            });
        },

        onKeyDownEdit: function (rowId, columnName, event) {
            if (event.keyCode != 13) {
                return false;
            }

            this.saveTableCell(rowId, columnName);
            return false;
        },

        // ---------------------------------------

        switcherStateChange: function () {
            var inputElement = $(this.id.replace('switcher', 'input'));

            inputElement.removeAttribute('disabled');

            if (!this.checked) {
                inputElement.value = '';
                inputElement.setAttribute('disabled', 'disabled');
            }
        },

        isAnySwitcherEnabled: function () {
            var result = false;

            $$('#controlPanel_tabs_database_table_cells_popup .input_switcher').each(function (el) {
                if (el.checked) {
                    result = true;
                    return true;
                }
            });

            return result;
        }
    });
});
