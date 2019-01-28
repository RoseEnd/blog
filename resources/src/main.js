// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import VueRouter from 'vue-router';
import routes from './router'
import VueX from 'vuex';
import stores from './store'
import http from './libs/http'
import {Loading, Tooltip} from 'element-ui';


Vue.use(VueRouter);
Vue.use(VueX);
Vue.use(Tooltip);
let router = new VueRouter(routes);
let store =  new VueX.Store(stores);

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.no_auth)) {
        next();
    } else {
        if (store.state.admin_token && store.state.admin_user) {
            next();
        } else {
            next(false);
            router.replace({path : '/admin/login', query: { redirect: to.fullPath }})
        }
    }
});

Vue.config.productionTip = false;


let app = new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: { App }
});
//为http客户引入app实例
http.$vue = app;
//注册全局的http请求客户端
Vue.prototype.$https = http;
Vue.prototype.$loading = Loading;
