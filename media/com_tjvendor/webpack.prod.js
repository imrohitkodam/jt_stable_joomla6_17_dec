const common = require("./webpack.common");
const merge  = require("webpack-merge");
const TerserJSPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = merge(common, {
	optimization: {
		minimizer: [new TerserJSPlugin({}), new OptimizeCSSAssetsPlugin({})],
	},
	plugins: [
		new MiniCssExtractPlugin({
		  filename: '[name].min.css',
		  chunkFilename: '[id].min.css',
		}),
	],
	mode: "production",
	output: {
		filename: '[name].min.js',
		libraryTarget: 'umd',
		library: 'tjvendor'
	}
});
