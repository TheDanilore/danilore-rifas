const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false, // Desactivar ESLint en desarrollo
  devServer: {
    port: 8080,
    host: 'localhost'
  }
})
