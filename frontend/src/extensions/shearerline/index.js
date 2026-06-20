import routes from './router'

const ShearerlineExtension = {
  install(app, options = {}) {
    if (options.router) {
      routes.forEach((route) => {
        if (!options.router.hasRoute(route.name)) {
          options.router.addRoute(route)
        }
      })
    }

    app.config.globalProperties.$shearerline = {
      version: '1.0.0',
      name: 'Shearerline 剪切线管理',
    }
  },
}

export { routes }
export default ShearerlineExtension
