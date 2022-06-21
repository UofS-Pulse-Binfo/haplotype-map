'use strict';
var webpack = require("webpack");
var path = require("path");
var webpath = process.env.npm_config_webpath || 'haplotype-map-tree/';
const TerserPlugin = require('terser-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');

// Remove single leading "/" if it is present.
if (webpath.charAt( 0 ) === '/') { webpath = webpath.substring(1); }

module.exports = {
    entry: ['babel-polyfill', './src/app.jsx'],
    output: {
        path: path.resolve("dist/bundle"),
        filename: "[name].bundle.haplotype-map.[chunkhash].js",
        chunkFilename: "[name].haplotype-map.js"
    },
    plugins: [new webpack.DefinePlugin({
        'process.env': {
            NODE_ENV: JSON.stringify('production'),
            DATADIR_PATH: JSON.stringify(webpath)
        }
    }),
    new TerserPlugin({
        parallel: true,
        terserOptions: {
            ecma: 6
        }
    }),
    new HtmlWebpackPlugin({
        filename: '../index.html',
        template: './src/assets/index.template.html'
    })
    ],
    module: {
        rules: require("./rules.config"),
    },
    resolve: {
        extensions: ['.js', '.jsx']
    }
}
