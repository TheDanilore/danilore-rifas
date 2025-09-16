const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false, // Desactivar ESLint en desarrollo
  devServer: {
    port: 8080,
    host: 'localhost',
    proxy: {
      '/api': {
        // Para desarrollo local sin Docker
        target: 'http://localhost:8000',
        // Para Docker: target: 'http://backend:8000',
        changeOrigin: true,
        secure: false,
        logLevel: 'debug'
      }
    }
  }
})
