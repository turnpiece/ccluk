var path = require('path')
var webpack = require('webpack')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer')
//     .BundleAnalyzerPlugin;
module.exports = {
	entry: {
		dashboard: './src/dashboard.js',
		'security-tweaks': './src/security-tweak.js',
		scan: './src/scan.js',
		audit: './src/audit.js',
		'ip-lockout': './src/ip-lockout.js',
		'advanced-tools': './src/advanced-tools.js',
		settings: './src/settings.js'
	},
	output: {
		path: __dirname + '/../assets/app',
		filename: '[name].js',
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: [
					'vue-style-loader',
					'css-loader'
				],
			}, {
				test: /\.vue$/,
				loader: 'vue-loader',
				options: {
					loaders: {}
					// other vue-loader options go here
				}
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/
			},
			{
				test: /\.(png|jpg|gif|svg)$/,
				loader: 'file-loader',
				options: {
					name: '[name].[ext]?[hash]'
				}
			},
			{
				test: /\.scss$/,
				use: [
					"style-loader", // creates style nodes from JS strings
					"css-loader", // translates CSS into CommonJS
					"sass-loader" // compiles Sass to CSS, using Node Sass by default
				]
			}
		]
	},
	plugins: [
		new VueLoaderPlugin(),
		new webpack.HotModuleReplacementPlugin(),
		new MomentLocalesPlugin(),
	],
	resolve: {
		alias: {
			'vue': 'vue/dist/vue.runtime.esm.js'
		},
		extensions: ['*', '.js', '.vue', '.json']
	},
	devServer: {
		contentBase: path.join(__dirname, 'public'),
		historyApiFallback: true,
		noInfo: true,
		overlay: true,
		hot: true
	},
	externals: {
		'vue': 'Vue', // Ca
	},
	performance: {
		hints: false
	},
	devtool: 'cheap-module-eval-source-map',
	mode: 'development',
	optimization: {
		namedModules: true, //NamedModulesPlugin()
		splitChunks: { // CommonsChunkPlugin()
			name: 'vendor',
			minChunks: 2
		},
	},
	watch: false
}

if (process.env.NODE_ENV === 'production') {
	module.exports.devtool = ''
	module.exports.mode = 'production'
	//http://vue-loader.vuejs.org/en/workflow/production.html
	module.exports.plugins = (module.exports.plugins || []).concat([
		new webpack.DefinePlugin({
			'process.env': {
				NODE_ENV: '"production"'
			}
		}),
		// new webpack.optimize.UglifyJsPlugin({
		//     sourceMap: true,
		//     compress: {
		//         warnings: false
		//     },
		//     extractComments: true
		// }),
		// new webpack.LoaderOptionsPlugin({
		//     minimize: true
		// })
	])
}
