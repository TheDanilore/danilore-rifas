const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false, // Desactivar ESLint en desarrollo
  devServer: {
    port: 8080,
    host: 'localhost',
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
        logLevel: 'debug'
      }
    }
  }
})
