const webpackConfig = require("@nextcloud/webpack-vue-config");
const path = require('path')

webpackConfig.output = {
    path: path.join(__dirname, "js"),
    publicPath: "auto",
    filename: "[name].js?v=[contenthash]",
    chunkFilename: "[name]-[id].js?v=[contenthash]",
    devtoolNamespace: "nextcloud",
    devtoolModuleFilenameTemplate(info) {
        const rootDir = process?.cwd();
        const rel = path.relative(rootDir, info.absoluteResourcePath);
        return `webpack:///nextcloud/${rel}`;
    },
    // clean: {
    //     keep: /icons\.css/,
    // },
};


module.exports = webpackConfig;