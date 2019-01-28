import http from 'axios';
import baseUrl from '../../../config/mobile';

let instance = http.create({
    baseURL: baseUrl.baseUrl,
    timeout: 60000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

instance.$vue = {};
//定义request拦截器
instance.interceptors.request.use(function (config) {
    return config;
}, function (error) {
    return Promise.reject(error);
});

//定义response拦截器
instance.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    if (error.response != null) {
        switch (error.response.status) {
            case 401:
                /*判断当前路由是否是后台路由*/
                let admin = instance.$vue.$route.path.match(/^\/admin/);
                admin ? instance.$vue.$router.push({path : '/admin/login'}) : instance.$vue.$router.push({path : '/login'});
                instance.$vue.message({type : 'error', close : true, message : '认证已失效，请重新认证'});
                break;
            case 407:
                instance.$vue.$router.push({path : '/admin/login'});
                instance.$vue.message({type : 'error', close : true, message : '认证已失效，请重新认证'});
                break;
            case 405:
                break;
        }
    } else {
        return Promise.reject(error);
    }
});

/**
 *
 * @param url
 * @param params
 * @param role
 * @param config 为其他配置
 * @returns {*}
 */
instance.postWA = function (url, params = {}, role = 'admin',  config = {}) {
    let token = role === 'admin' ? instance.$vue.$store.state.admin_token : instance.$vue.$store.state.home_token;
    return instance.post(url, params, {
        'headers': {
            Authorization: "Bearer " + token,
        },
        ...config
    });
};

instance.getWA = function (url, role = 'admin', config = {}) {
    let token = role === 'admin' ? instance.$vue.$store.state.admin_token : instance.$vue.$store.state.home_token;
    return instance.get(url, {
        'headers': {
            Authorization: "Bearer " + token,
        },
        ...config
    });
};
export default instance;
