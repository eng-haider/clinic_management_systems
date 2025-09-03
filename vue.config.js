// vue.config.js

const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')

module.exports = {
  productionSourceMap: false,
  parallel: false,
  
  devServer: {
    hot: false,
    liveReload: true,
  },

  css: {
    extract: process.env.NODE_ENV === 'production' ? {
      filename: 'css/[name].[contenthash:8].css',
      chunkFilename: 'css/[name].[contenthash:8].css',
    } : false,
    sourceMap: false,
  },

  chainWebpack: config => {
    config.plugins.delete('prefetch')
    config.plugins.delete('preload')
    
    // Disable caching
    config.cache(false);
    
    // Remove cache loaders
    ['vue', 'js', 'ts', 'tsx', 'css', 'scss'].forEach(rule => {
      if (config.module.rules.get(rule)) {
        config.module.rule(rule).uses.delete('cache-loader')
      }
    })
    
    // PWA cache busting for HTML with BASE_URL fix
    config.plugin('html').tap(args => {
      args[0].templateParameters = {
        ...args[0].templateParameters,
        BASE_URL: process.env.NODE_ENV === 'production' ? './' : '/',
        htmlWebpackPlugin: {
          options: {
            title: 'Clinic Management System',
            CACHE_BUST: Date.now(),
            PWA_VERSION: Date.now()
          }
        }
      }
      return args
    })
    
    // Force service worker update
    if (config.plugins.has('workbox')) {
      config.plugin('workbox').tap(args => {
        args[0].skipWaiting = true
        args[0].clientsClaim = true
        args[0].cleanupOutdatedCaches = true
        return args
      })
    }
  },

  configureWebpack: {
    cache: false,
    
    output: {
      filename: process.env.NODE_ENV === 'production' ? 'js/[name].[contenthash:8].js' : '[name].js',
      chunkFilename: process.env.NODE_ENV === 'production' ? 'js/[name].[contenthash:8].js' : '[name].js'
    },
    
    plugins: [
      new VuetifyLoaderPlugin()
    ],
  },

  pwa: {
    name: 'Clinic Management System',
    themeColor: '#4DBA87',
    msTileColor: '#000000',
    appleMobileWebAppCapable: 'yes',
    appleMobileWebAppStatusBarStyle: 'black',
    
    // Force service worker update
    workboxPluginMode: 'InjectManifest',
    workboxOptions: {
      swSrc: 'src/sw.js',
      // Force update on every build
      skipWaiting: true,
      clientsClaim: true,
      // Clear old caches
      cleanupOutdatedCaches: true,
      // Cache busting
      revision: Date.now().toString()
    },
    
    // Manifest cache busting
    manifestOptions: {
      name: 'Clinic Management System',
      short_name: 'Clinic',
      start_url: '/',
      display: 'standalone',
      theme_color: '#4DBA87',
      background_color: '#ffffff',
      // Add version to force manifest update
      version: Date.now().toString()
    }
  }
}

