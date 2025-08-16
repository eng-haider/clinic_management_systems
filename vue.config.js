// vue.config.js

const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
  // Disable source maps in production
  productionSourceMap: false,
  parallel: true,
  
  devServer: {
    hot: false,
    liveReload: true,
  },

  css: {
    extract: process.env.NODE_ENV === 'production' ? {
      filename: '[name].[contenthash].css',
      chunkFilename: '[name].[contenthash].css'
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
    },
  },

  chainWebpack: config => {
    // Remove prefetch and preload plugins for better control
    config.plugins.delete('prefetch')
    config.plugins.delete('preload')
    
    // Configure HTML plugin
    config
      .plugin('html')
      .tap((args) => {
        args[0].title = 'Smart Clinic Management System';
        return args;
      });
    
    // Disable chunk splitting
    config.optimization.delete('splitChunks');
    
    // Add cache-loader for better build performance
    config.module
      .rule('vue')
      .use('cache-loader')
      .loader('cache-loader')
      .before('vue-loader')
  },

  // Enhanced performance optimizations
  configureWebpack: {
    output: {
      filename: process.env.NODE_ENV === 'production' ? 'bundle.js' : 'bundle.js',
      chunkFilename: process.env.NODE_ENV === 'production' ? 'bundle.js' : 'bundle.js'
    },
    performance: {
      hints: false,
      maxEntrypointSize: 512000,
      maxAssetSize: 512000
    },
    optimization: {
      minimize: process.env.NODE_ENV === 'production',
      // No chunk splitting
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
}


