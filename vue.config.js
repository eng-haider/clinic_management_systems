// vue.config.js

const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')

module.exports = {
  // Disable source maps in production
  productionSourceMap: false,
  parallel: false, // Disable parallel processing
  
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

  // PWA Configuration
  pwa: {
    name: 'Smart Clinic Management System',
    short_name: 'Smart Clinic',
    theme_color: '#1976d2',
    background_color: '#ffffff',
    display: 'standalone',
    scope: '/',
    start_url: '/',
    workboxPluginMode: 'GenerateSW',
    workboxOptions: {
      skipWaiting: true,
      clientsClaim: true,
      exclude: [/\.map$/, /manifest$/, /\.htaccess$/],
      runtimeCaching: []
    },
  },

  chainWebpack: config => {
    // Remove prefetch and preload plugins
    config.plugins.delete('prefetch')
    config.plugins.delete('preload')
    
    // Configure HTML plugin
    config
      .plugin('html')
      .tap((args) => {
        args[0].title = 'Smart Clinic Management System';
        return args;
      });
    
    // COMPLETELY DISABLE ALL CACHING
    config.cache(false);
    
    // Remove cache loaders completely
    const rules = ['vue', 'js', 'ts', 'tsx', 'css', 'scss', 'sass', 'less', 'stylus']
    rules.forEach(rule => {
      if (config.module.rules.get(rule)) {
        config.module.rule(rule).uses.delete('cache-loader')
      }
    })
    
    // Force fresh compilation
    config.resolve.symlinks(false)
  },

  configureWebpack: {
    // DISABLE ALL WEBPACK CACHING
    cache: false,
    
    // Force unique filenames
    output: {
      filename: process.env.NODE_ENV === 'production' ? 'js/[name].[contenthash:8].js' : '[name].js',
      chunkFilename: process.env.NODE_ENV === 'production' ? 'js/[name].[contenthash:8].js' : '[name].js'
    },
    
    performance: {
      hints: false,
      maxEntrypointSize: 512000,
      maxAssetSize: 512000
    },
    
    optimization: {
      minimize: process.env.NODE_ENV === 'production',
      runtimeChunk: false,
      splitChunks: {
        chunks: 'all',
        maxInitialRequests: Infinity,
        minSize: 0,
        cacheGroups: {
          vendor: {
            name: 'chunk-vendors',
            test: /[\\/]node_modules[\\/]/,
            priority: 10,
            chunks: 'initial'
          },
          common: {
            name: 'chunk-common',
            minChunks: 2,
            priority: 5,
            chunks: 'initial'
          },
          default: {
            minChunks: 2,
            priority: -20,
            reuseExistingChunk: true
          }
        }
      }
    },
    
    resolve: {
      alias: {
        'vue$': 'vue/dist/vue.runtime.esm.js'
      }
    },
    
    plugins: [
      new VuetifyLoaderPlugin({
        match (originalTag, { kebabTag, camelTag }) {
          if (kebabTag.startsWith('core-')) {
            return [camelTag, `import ${camelTag} from '@/components/core/${camelTag.substring(4)}.vue'`]
          }
        }
      })
    ],
  },
}


