(function () {
    const DataTransferTabView = OCA.Files.DetailTabView.extend({
        id: 'tataTransfer',
        className: 'tab tataTransfer',
        getLabel: () => `Data Transfer`,
        getIcon: () => `icon-share-dark`,
        template: function (vars) {
            return OCA.Files.Templates['dataTransfer'](vars);
        },
        render: function () {

            const searchResult = [
                { id: "1", title: "user_1@domain.com" },
                // { id: "2", title: "user_2@domain.com" },
                // { id: "3", title: "user_3@domain.com" },
                // { id: "4", title: "user_4@domain.com" },
                // { id: "5", title: "user_5@domain.com" },
                // { id: "6", title: "user_6@domain.com" },
                // { id: "7", title: "user_7@domain.com" },
                // { id: "8", title: "user_8@domain.com" },
                // { id: "9", title: "user_9@domain.com" },
            ]

            const html = `
                <div class="dataTransferTabContainer">
                    <div id="dataTransferInputWrapper" class="dataTransferInputWrapper">
                        <label>Search for share recipients</label>
                        <input onfocus="alert(111)" class="dataTransferTab_input" type="input">
                    </div>
                    

                    ${Boolean(searchResult.length > 0)
                    ?
                    `
                        <div id="dataTransferListWrapper" class="dataTransferListWrapper">

                            ${searchResult.map(x => `<p class="dataTransferListItem">${x.title}</p>`)}
        
                        </div>
                    `
                    :
                    null}

                </div>`;

            this.$el.html(html)
        },
    });

    OCA.dataTransfer = OCA.dataTransfer || {};
    OCA.dataTransfer.DataTransferTabView = DataTransferTabView;

    OC.Plugins.register('OCA.Files.FileList', {
        attach: (fileList) => fileList.registerTabView(new OCA.dataTransfer.DataTransferTabView())
    });

    // const dataTransferInputWrapper = document.getElementById("dataTransferInputWrapper")
    // const dataTransferListWrapper = document.getElementById("dataTransferListWrapper")

    // dataTransferInputWrapper.addEventListener("focus", function () {
    //     dataTransferListWrapper.classList.remove("hidden")
    // })
})();