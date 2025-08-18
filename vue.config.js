// vue.config.js

const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
  transpileDependencies: [],
  
  // Disable source maps in production
  productionSourceMap: false,
  parallel: true,
  
  devServer: {
    hot: false,
    liveReload: true,
    headers: {
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0'
    }
  },

  css: {
    extract: process.env.NODE_ENV === 'production' ? {
      filename: 'css/[name].css',
      chunkFilename: 'css/[name].css'
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
    workboxPluginMode: 'InjectManifest',
    workboxOptions: {
      swSrc: 'public/sw.js',
      swDest: 'sw.js',
      skipWaiting: true,
      clientsClaim: true
    },
  },

  // Disable code splitting and chunk files
  chainWebpack: config => {
    // Remove prefetch and preload plugins for better control
    config.plugins.delete('prefetch')
    config.plugins.delete('preload')
    
    // Configure HTML plugin
    config.plugin('html').tap((args) => {
      args[0].title = 'Smart Clinic Management System';
      return args;
    });
  },

  // Enhanced performance optimizations with proper chunk configuration
  configureWebpack: {
    output: {
      filename: 'js/[name].[hash].js',
      chunkFilename: 'js/[name].[hash].js'
    },
    optimization: {
      splitChunks: {
        chunks: 'all',
        cacheGroups: {
          vendor: {
            test: /[\\/]node_modules[\\/]/,
            name: 'vendors',
            chunks: 'all',
            // Remove custom filename to prevent conflict
            // filename: 'vendors.[contenthash].js'
          }
        }
      },
      runtimeChunk: false,
      minimize: true,
      concatenateModules: false,
      providedExports: false,
      usedExports: false,
      sideEffects: false,
      removeAvailableModules: false,
      removeEmptyChunks: false,
      mergeDuplicateChunks: false
    },
    performance: {
      hints: false,
      maxEntrypointSize: 5048000,
      maxAssetSize: 5048000
    },
    resolve: {
      alias: {
        'vue$': 'vue/dist/vue.runtime.esm.js'
      }
    },
    plugins: [
      // Uncomment to analyze bundle size
      // new BundleAnalyzerPlugin(),
      new VuetifyLoaderPlugin({
        match (originalTag, { kebabTag, camelTag }) {
          if (kebabTag.startsWith('core-')) {
            return [
              camelTag,
              `import ${camelTag} from '@/components/core/${camelTag.substring(4)}.vue'`
            ]
          }
        }
      })
    ],
  },

  // Public path configuration
  publicPath: process.env.NODE_ENV === 'production' ? './' : '/'
}


