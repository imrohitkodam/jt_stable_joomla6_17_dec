const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
module.exports = {
	entry: {
		app: ['babel-polyfill', path.join(__dirname, 'js', 'index')]
	},
	module: {
		rules: [{
			test: /\.js?$/,
			exclude: /node_module/,
			loader: 'babel-loader',
			query: {
				presets: ["@babel/preset-env"],
				plugins: ["@babel/plugin-proposal-class-properties"]
			}
		},
		{
			test: /\.css$/i,
			use: [MiniCssExtractPlugin.loader, 'css-loader']
		}],
	}
}
