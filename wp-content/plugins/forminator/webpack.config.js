var path = require( 'path' );
var webpack = require( 'webpack' );

// As Webpack only understands JS, we'll use this plugin to extract the CSS to a file
const ExtractTextPlugin = require("extract-text-webpack-plugin");

// If there's an error, the console will beep
const SystemBellPlugin = require('system-bell-webpack-plugin');

const config = {
	source:{},
	output:{}
};

// Full path of main files that need to be ran through the bundler.
config.source.scss           = './_src/scss/admin.scss';
config.source.js             = './_src/js/index.js';

// Path where the scss & js should be compiled to.
config.output.scssDirectory  = 'assets/css/';       // No trailing slash.
config.output.jsDirectory    = 'assets/js/';        // No trailing slash.

// File names of the compiled scss & js.
config.output.scssFileName   = 'shared-ui.min.css';
config.output.jsFileName     = 'shared-ui.min.js';

// The path where the Shared UI fonts & images should be sent. (relative to config.output.jsFileName)
config.output.imagesDirectory = '../images/';            // Trailing slash required.
config.output.fontsDirectory  = '../fonts/';             // Trailing slash required.

var scssConfig = Object.assign( {}, {
	entry: config.source.scss,
	output: {
		filename: config.output.scssFileName,
		path: path.resolve(__dirname, config.output.scssDirectory)
	},
	module: {
		rules: [
			{
                test: /\.scss$/,
                exclude: /(node_modules|bower_components)/,
                use: ExtractTextPlugin.extract({
					fallback: 'style-loader',
					use: [
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: true
                            }
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                sourceMap: true
                            }
                        },
                        {
                            loader: 'resolve-url-loader'
                        },
                        {
                            loader: 'sass-loader',
                            options: {
                                sourceMap: true
                            }
                        }
					]
				})
			},
			{
				test: /\.(png|jpg|gif)$/,
				use: {
					loader: 'file-loader', // Instructs webpack to emit the required object as file and to return its public URL.
					options: {
						name: '[name].[ext]',
						outputPath: config.output.imagesDirectory
					}
				}
			},
			{
				test: /\.(woff|woff2|eot|ttf|otf|svg)$/,
				use: {
					loader: 'file-loader', // Instructs webpack to emit the required object as file and to return its public URL.
					options: {
						name: '[name].[ext]',
						outputPath: config.output.fontsDirectory
					}
				}
			}
		]
	},
    devtool: 'source-map',
	plugins: [
		new ExtractTextPlugin(config.output.scssFileName),
        new SystemBellPlugin()
	],
    watchOptions: {
        poll: 500
    }
});

var jsConfig = Object.assign( {}, {
	entry: config.source.js,
	output: {
		filename: config.output.jsFileName,
		path: path.resolve(__dirname, config.output.jsDirectory)
	},
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['env']
                    }
                }
            }
        ]
    },
	devtool: 'source-map',
	plugins: [
        new SystemBellPlugin(),
		// Automatically load modules instead of having to import or require them everywhere.
		new webpack.ProvidePlugin({
			ClipboardJS: '@wpmudev/shared-ui/js/clipboard.js',  // Cendor script in Shared UI.
			A11yDialog:  '@wpmudev/shared-ui/js/a11y-dialog.js', // Vendor script in Shared UI.
			Select2: '@wpmudev/shared-ui/js/select2.full.js'
		})
	]
} );

module.exports = [scssConfig, jsConfig];