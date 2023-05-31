import Vue from 'vue'
import { translate, translatePlural } from 'nextcloud-l10n'

import DataTransferTab from '../components/DataTransferTab'

Vue.prototype.t = translate
Vue.prototype.n = translatePlural
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA
const View = Vue.extend(DataTransferTab)
let tabInstance = null

export const registerDataTransferTab = () => {
    window.addEventListener('DOMContentLoaded', function () {
        if (OCA.Files && OCA.Files.Sidebar) {
            const DataTransferTab = new OCA.Files.Sidebar.Tab({
                id: 'dataTransferTab',
                name: t('sharerenamer', 'Data Transfer'),
                icon: 'icon-share',

                mount(el, fileInfo, context) {
                    if (tabInstance) {
                        tabInstance.$destroy()
                    }
                    tabInstance = new View({
                        // Better integration with vue parent component
                        parent: context,
                    })
                    // Only mount after we have all the info we need
                    tabInstance.update(fileInfo)
                    tabInstance.$mount(el)
                },
                update(fileInfo) {
                    tabInstance.update(fileInfo)
                },
                destroy() {
                    tabInstance.$destroy()
                    tabInstance = null
                },
                enabled(fileInfo) {
                    // return (fileInfo && !fileInfo.isDirectory());
                    return true
                },
            })
            OCA.Files.Sidebar.registerTab(DataTransferTab)
        }
    })
}