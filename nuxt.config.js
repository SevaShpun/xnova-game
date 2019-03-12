const webpack = require('webpack')

module.exports = {
	head: {
		title: 'XNova Game',
		meta: [
			{ charset: 'utf-8' },
			{ name: 'viewport', content: 'width=device-width, initial-scale=1' },
			{ 'http-equiv': 'X-UA-Compatible', content: 'IE=edge' },
			{ property: 'og:title', content: 'XNova Game', hid: 'og:title' },
			{ property: 'og:image', content: '/images/logo.jpg' },
			{ property: 'og:image:width', content: '300' },
			{ property: 'og:image:height', content: '300' },
			{ property: 'og:site_name', content: 'Звездная Империя' },
			{ property: 'og:description', content: 'Вы являетесь межгалактическим императором, который распространяет своё влияние посредством различных стратегий на множество галактик.' },
		],
		link: [
			{ rel: 'image_src', href: '/images/logo.jpg' },
			{ rel: 'apple-touch-icon', href: '/images/apple-touch-icon.png' },
		]
	},
	css: [
		'~/assets/styles/bootstrap/bootstrap.scss',
		'~/assets/app.scss',
	],
	transition: {
		name: 'page-switch',
		mode: 'out-in'
	},
	plugins: [
		'~/plugins/api.js',
		'~/plugins/validate.js',
		'~/plugins/i18n.js',
		'~/plugins/filters.js',
		'~/plugins/components.js',
		{src: '~/plugins/jquery.js', ssr: false},
		{src: '~/plugins/modal.js', ssr: false},
		{src: '~/plugins/router.js', ssr: false},
		{src: '~/plugins/metrika.js', ssr: false},
	],
	router: {
		base: '/',
		middleware: 'router',
		prefetchLinks: false,
	},
	loading: false,
	build: {
		cssSourceMap: false,
		extractCSS: true,
		publicPath: '/static/',
		loaders: {
			vue: {
				compilerOptions: {
					preserveWhitespace: true
				}
			}
		},
		optimizeCSS: {
			cssProcessorPluginOptions: {
				preset: ['default', {
					discardComments: {
					removeAll: true
				}}],
			},
		},
		extend (config, {isDev, isClient})
		{
			if (isDev && isClient)
			{
				config.module.rules.push({
					enforce: 'pre',
					test: /\.(js|vue)$/,
					exclude: /(node_modules)/
				});
			}

			config.plugins.push(new webpack.ProvidePlugin({
				$: 'jquery',
				jQuery: 'jquery',
				'window.jQuery': 'jquery',
				'window.$': 'jquery'
			}));

			config.module.rules.push({
				test: require.resolve('jquery'),
				use: [{
					loader: 'expose-loader',
					options: '$'
				}, {
					loader: 'expose-loader',
					options: 'jQuery'
				}]
			});

			config.resolve.alias['jquery'] = 'jquery/dist/jquery.slim.js'
			config.resolve.alias['socket.io-client'] = 'socket.io-client/dist/socket.io.slim.js'

			if (isClient)
			{
				config.externals = {
					"window": "window"
				};

				config.module.rules.push({
					test: /vue-router(.*?)\.js$/,
					loader: 'string-replace-loader',
					options: {
						search: 'isSameRoute(route, current)',
						replace: 'false',
					}
				})
			}
		}
	},
	modules: [
		'@nuxtjs/proxy',
		'@nuxtjs/axios',
	],
	axios: {
		prefix: '/api',
		proxy: true,
		proxyHeaders: true,
		credentials: true,
		baseURL: 'http://test.xnova.su/',
	},
	proxy: {
		'/api': {target: 'http://test.xnova.su', pathRewrite: {'^/api' : ''}, cookieDomainRewrite: {"*": ""}},
		'/upload': {target: 'http://test.xnova.su'},
	},
	vue: {
		config: {
			productionTip: false
		}
	},
};