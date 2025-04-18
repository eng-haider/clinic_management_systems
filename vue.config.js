// vue.config.js

const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;




module.exports = {
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
  configureWebpack: {
    optimization: {
      splitChunks: {
        chunks: 'all'
      }
    }
  },
  // devServer: {
  //   port: 8081, // Replace 8081 with the desired port number
  // },

  chainWebpack: (config) => {
    config
      .plugin('html')
      .tap((args) => {
        args[0].title = 'clinic management system';
        return args;
      });
  },
  
  configureWebpack: {
    plugins: [
      // new BundleAnalyzerPlugin() ,
      
      new VuetifyLoaderPlugin({
        /**
         * This function will be called for every tag used in each vue component
         * It should return an array, the first element will be inserted into the
         * components array, the second should be a corresponding import
         *
         * originalTag - the tag as it was originally used in the template
         * kebabTag    - the tag normalized to kebab-case
         * camelTag    - the tag normalized to PascalCase
         * path        - a relative path to the current .vue file
         * component   - a parsed representation of the current component
         */
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


