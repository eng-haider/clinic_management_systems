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
    extract: true,
    sourceMap: false,
  },

  chainWebpack: config => {
    // Remove prefetch plugins to improve initial load time
    config.plugins.delete('prefetch')
    
    // Configure HTML plugin
    config
      .plugin('html')
      .tap((args) => {
        args[0].title = 'clinic management system';
        return args;
      });
    
    // Simple chunk splitting to avoid conflicts
    config.optimization.splitChunks({
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          chunks: 'all',
          priority: 1
        }
      }
    })
  },

  // Performance optimizations
  configureWebpack: {
    performance: {
      hints: false,
      maxEntrypointSize: 512000,
      maxAssetSize: 512000
    },
    optimization: {
      minimize: true,
      runtimeChunk: 'single'
    },
    plugins: [
      // new BundleAnalyzerPlugin(),
      new VuetifyLoaderPlugin({
        match (originalTag, { kebabTag, camelTag, path, component }) {
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


