define([
    'jquery',
    'mage/translate',
    'Kaufland/Common'
], function ($, $t) {
    window.KauflandTemplateCategoryChooserTabsSearch = Class.create(Common, {

        categoryChooser: null,
        searchContainer: null,
        resultContainer: null,

        /**
         * @param {KauflandCategoryChooser} categoryChooser
         * @param {string} searchContainerId
         * @param {string} resultContainerId
         */
        initialize: function (categoryChooser, searchContainerId, resultContainerId) {
            this.categoryChooser = categoryChooser
            this.searchContainer = $('#' + searchContainerId)
            this.resultContainer = $('#' + resultContainerId)

            this.initObservers()
        },

        initObservers: function () {
            const self = this;

            this.searchContainer.on('click', '.reset-input', function () {
                self.searchContainer.find('.search-input').val('').focus()
                self.resultContainer.find('.search_results_table').empty()
            })

            this.searchContainer.on('click', '.search-btn', function () {
                self.search(self.searchContainer.find('.search-input').val())
            })

            this.searchContainer.on('keypress', '.search-input', function (event) {
                if (event.which !== 13) {
                    return;
                }

                self.search(self.searchContainer.find('.search-input').val())
            })

            this.resultContainer.on('click', '.choice-category', function (event) {
                const choiceLink = $(event.currentTarget);

                self.categoryChooser.selectCategory(choiceLink.attr('data-category-id'))
            })
        },

        search: function (searchQuery) {
            const self = this;

            new Ajax.Request(Kaufland.url.get('kaufland_category/search'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    "search_query": searchQuery,
                    "storefront_id": self.categoryChooser.getStorefrontId()
                },
                onSuccess: function (transport) {
                    const resultTable = self.resultContainer.find('.search_results_table');
                    resultTable.empty()

                    $.each(transport.responseText.evalJSON(), function (index, category) {

                        let categoryName = `${category['path']} (${category['id']})`;
                        let style = '';
                        let choiceLink = `<a class="choice-category" data-category-id="${category['id']}">${$t('Select')}</a>`

                        const row = `
                            <tr>
                                <td><span style="${style}">${categoryName}</span></td>
                                <td style="text-align: right">${choiceLink}</td>
                            </tr>
                        `
                        resultTable.append($(row))
                    });
                }
            });
        }
    });
});
