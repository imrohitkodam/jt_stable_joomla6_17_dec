const common = require("./webpack.common");
const merge  = require("webpack-merge");
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = merge(common, {
	plugins: [
		new MiniCssExtractPlugin({
		  filename: '[name].css',
		  chunkFilename: '[id].css',
		}),
	],
	mode: "development",
	output: {
		filename: '[name].js',
		libraryTarget: 'umd',
		library: 'tjvendor'
	}
});
