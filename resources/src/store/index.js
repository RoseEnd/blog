import baseUrl from '../../config/mobile';
export default {
    state: {
        home_token: sessionStorage.getItem('home_token') ? sessionStorage.getItem('home_token'):'',
        home_user: sessionStorage.getItem('home_user') ? JSON.parse(sessionStorage.getItem('home_user')):null,
        admin_token : sessionStorage.getItem('admin_token') ? sessionStorage.getItem('admin_token'):'',
        admin_user : sessionStorage.getItem('admin_user') ? JSON.parse(sessionStorage.getItem('admin_user')):null,
        hotArticle : sessionStorage.getItem('hotArticle')? JSON.parse(sessionStorage.getItem('hotArticle')):[],
        dynamicTags : sessionStorage.getItem('dynamicTags')? JSON.parse(sessionStorage.getItem('dynamicTags')):[],
        friendly_link : sessionStorage.getItem('friendly_link')? JSON.parse(sessionStorage.getItem('friendly_link')):[],
        adverts : sessionStorage.getItem('adverts')? JSON.parse(sessionStorage.getItem('adverts')):{},
        server_url : baseUrl.imgUrl
    },
    getters: {},
    mutations: {
        home_login (state, data) {
            state.home_token = data.token;
            state.home_user = data.user;
        },
        home_logout (state) {
            state.home_token = '';
            state.home_user = null;
        },
        admin_login (state, data) {
            state.admin_token = data.token;
            state.admin_user = data.user;
        },
        admin_logout (state) {
            state.admin_token = '';
            state.admin_user = null;
        },
        clearToken (state) {
            state.admin_token = '';
        },
        setSideData (state, witch = {}) {
            for (let index in witch) {
                state[index] = witch[index];
            }
        }
    },
    actions: {}
}