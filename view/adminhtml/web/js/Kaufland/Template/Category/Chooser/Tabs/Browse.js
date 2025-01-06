define([
    'Kaufland/Common'
], function () {
    window.KauflandTemplateCategoryChooserTabsBrowse = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {
            this.parentCategoryId = 1;
            this.storefrontId = null;
            this.accountId = null;
            this.observers = {
                "leaf_selected": [],
                "not_leaf_selected": [],
                "any_selected": []
            };
        },

        // ---------------------------------------

        setStorefrontId: function (storefrontId) {
            this.storefrontId = storefrontId;
        },

        getStorefrontId: function () {
            if (this.storefrontId === null) {
                alert('You must set Site');
            }

            return this.storefrontId;
        },

        //----------------------------------------

        setAccountId: function (accountId) {
            this.accountId = accountId;
        },

        getAccountId: function () {
            if (this.accountId === null) {
                alert('You must set Account');
            }

            return this.accountId;
        },

        //----------------------------------------

        getCategoriesSelectElementId: function (categoryId) {
            if (categoryId === null) {
                categoryId = 0;
            }

            return 'category_chooser_select_' + categoryId;
        },

        getCategoryChildrenElementId: function (categoryId) {
            if (categoryId === null) {
                categoryId = 0;
            }
            return 'category_chooser_children_' + categoryId;
        },

        getSelectedCategories: function () {
            const self = KauflandTemplateCategoryChooserTabsBrowseObj;

            let categoryId = self.parentCategoryId;
            if (categoryId === null) {
                categoryId = 0;
            }

            const selectedCategories = [];
            let isLastCategory = false;

            while (!isLastCategory) {
                let categorySelect = $(self.getCategoriesSelectElementId(categoryId));
                if (!categorySelect || categorySelect.selectedIndex === -1) {
                    break;
                }

                categoryId = selectedCategories[selectedCategories.length]
                        = categorySelect.options[categorySelect.selectedIndex].value;

                if (categorySelect.options[categorySelect.selectedIndex].getAttribute('is_leaf') === 'true') {
                    isLastCategory = true;
                }
            }

            return selectedCategories;
        },

        // ---------------------------------------

        renderTopLevelCategories: function (containerId) {
            this.prepareDomStructure(this.parentCategoryId, $(containerId));
            this.renderChildCategories(this.parentCategoryId);
        },

        renderChildCategories: function (parentCategoryId) {
            const self = this;

            new Ajax.Request(Kaufland.url.get('kaufland_category/getChildCategories'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    "parent_category_id": parentCategoryId,
                    "storefront_id": self.getStorefrontId()
                },
                onSuccess: function (transport) {

                    if (transport.responseText.length <= 2) {
                        self.simulate('leaf_selected', self.getSelectedCategories());
                        return;
                    }

                    const categories = JSON.parse(transport.responseText);
                    let optionsHtml = '';
                    categories.each(function (category) {
                        optionsHtml += '<option is_leaf="' + category.is_leaf + '" value="' + category.category_id + '">';
                        optionsHtml += category.title + (category.is_leaf ? '' : ' >');
                        optionsHtml += '</option>';
                    });

                    $(self.getCategoriesSelectElementId(parentCategoryId)).innerHTML = optionsHtml;
                    $(self.getCategoriesSelectElementId(parentCategoryId)).style.display = 'inline-block';

                    $('chooser_browser').scrollLeft = $('chooser_browser').scrollWidth;
                }
            });
        },

        onSelectCategory: function (select) {
            const self = KauflandTemplateCategoryChooserTabsBrowseObj;

            const parentCategoryId = select.id.replace(self.getCategoriesSelectElementId(""), "");
            const categoryId = select.options[select.selectedIndex].value;
            const is_leaf = select.options[select.selectedIndex].getAttribute('is_leaf');

            const selectedCategories = self.getSelectedCategories();

            const parentDiv = $(self.getCategoryChildrenElementId(parentCategoryId));
            parentDiv.innerHTML = '';

            self.simulate('any_selected', selectedCategories);

            if (is_leaf === 'true') {
                self.simulate('leaf_selected', selectedCategories);
                return;
            }

            self.simulate('not_leaf_selected', selectedCategories);

            self.prepareDomStructure(categoryId, parentDiv);
            self.renderChildCategories(categoryId);
        },

        prepareDomStructure: function (categoryId, parentDiv) {
            const self = KauflandTemplateCategoryChooserTabsBrowseObj;

            const childrenSelect = document.createElement('select');
            childrenSelect.id = self.getCategoriesSelectElementId(categoryId);
            childrenSelect.style.minWidth = '200px';
            childrenSelect.style.maxHeight = 'none';
            childrenSelect.size = 10;
            childrenSelect.className = 'multiselect admin__control-multiselect';
            childrenSelect.onchange = function () {
                KauflandTemplateCategoryChooserTabsBrowseObj.onSelectCategory(this);
            };
            childrenSelect.style.display = 'none';
            parentDiv.appendChild(childrenSelect);

            const childrenDiv = document.createElement('div');
            childrenDiv.id = self.getCategoryChildrenElementId(categoryId);
            childrenDiv.className = 'category-children-block';
            parentDiv.appendChild(childrenDiv);
        },

        // ---------------------------------------

        observe: function (event, observer) {
            var self = KauflandTemplateCategoryChooserTabsBrowseObj;

            if (typeof observer != 'function') {
                self.alert('Observer must be a function!');
                return;
            }

            if (typeof self.observers[event] == 'undefined') {
                self.alert('Event does not supported!');
                return;
            }

            self.observers[event][self.observers[event].length] = observer;
        },

        simulate: function (event, parameters) {
            var self = KauflandTemplateCategoryChooserTabsBrowseObj;

            parameters = parameters || null;

            if (typeof self.observers[event] == 'undefined') {
                self.alert('Event does not supported!');
                return;
            }

            if (self.observers[event].length == 0) {
                return;
            }

            self.observers[event].each(function (observer) {
                if (parameters == null) {
                    (observer)();
                } else {
                    (observer)(parameters);
                }
            });
        }

        // ---------------------------------------
    });
});
