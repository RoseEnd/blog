<template>
    <div id="app">
        <topNav @sideListen="options=arguments[0];getList()"></topNav>
        <div id="article-list">
            <el-row type="flex" justify="space-between" style="overflow: hidden;">
                <el-col :xs="{span:24}" :sm="{span:24}" :md="{span:15}" :lg="{span:11, offset:4}">
                    <div class="grid-content contentList" id="index-content">
                        <template v-if="show=='index'">
                            <template v-for="(item, index) in lists">
                                <div class="fwrap list-item">
                                    <el-row>
                                        <el-col :xs="{span:22,offset:1}" :sm="{span:9}" :md="{span:8}">
                                            <img class="listImg pointer" @click="getDetail(item.id)"
                                                 :src="item.image.match(/^http/) ? item.image : server_url + item.image">
                                        </el-col>
                                        <el-col :xs="{span:22,offset:1}" :sm="{span:13,offset:1}" :md="{span:14,offset:1}" >
                                            <div >
                                                <h4 @click="getDetail(item.id)" class="pointer">{{item.title}}</h4>
                                                <div class="text" v-html="item.render"></div>
                                            </div>
                                        </el-col>
                                    </el-row>
                                    <el-row type="flex">
                                        <el-col :xs="{span:7,offset:1}" :sm="{span:9}" :md="{span:7,offset:1}">
                                            <span class="date light-font">{{item.created_at.substr(0, 10)}}</span>
                                        </el-col>
                                        <el-col :xs="{span:15,pull:1}" :sm="{span:13,offset:1,pull:1}" :md="{span:15,offset:1}">
                                            <el-row>
                                                <el-col :xs="20" :sm="22" :md="22">
                                                    <el-tag v-for="(val, index) in item.tags" :key="index" :type="type[index%type.length]">
                                                        {{val.tag.name}}
                                                    </el-tag>
                                                </el-col>
                                                <el-col :xs="4" :sm="2" :md="{span:1,pull:1}">
                                                    <span style="font-size: 12px" class="light-font">
                                                        <i style="font-size: 12px;margin-right: 6px;" class="iconfont icon-liulan"></i><span>{{item.click_number}}</span>
                                                    </span>
                                                </el-col>
                                            </el-row>
                                        </el-col>
                                    </el-row>
                                </div>
                            </template>
                            <el-row type="flex" justify="center">
                                <el-pagination v-if="total/per_page>1"
                                        layout="prev, pager, next" @current-change="handleCurrentChange"
                                        :total="total" :current-page="current_page" :page-size="per_page">
                                </el-pagination>
                            </el-row>
                        </template>
                        <template v-if="show=='article'">
                            <h2>{{article.title}}</h2>
                            <el-tabs v-model="active">
                                <el-tab-pane label="文章" name="article">
                                    <mavon-editor style="min-height: 1px;min-width: 1px" v-model="article.content" :subfield="false"
                                                  defaultOpen="preview" :editable="false"
                                                  :ishljs="true" codeStyle="tomorrow" :toolbarsFlag="false">
                                    </mavon-editor>
                                    <div style="display: flex;justify-content: center">
                                        <span class="reprint">本文为原创，转载需注明来源</span>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="评论" name="comment">
                                    <template v-if="article.comments.length > 0">
                                        <template v-for="(item, index) in article.comments">
                                                <el-row type="flex" :class="index == 0 ? '' : 'content-margin'">
                                                    <el-col>
                                                        <div class="content_header flex comment-header">
                                                            <div class="header-left">
                                                                <div class="avatar">
                                                                    <img :src="item.users.icon.match(/^http/) ? item.users.icon : server_url + item.users.icon">
                                                                </div>
                                                                <div>
                                                                    <span class="name">{{item.user_name}}</span>
                                                                    <span class="header-span">
                                                                 commented {{item.created_at}}
                                                                </span>
                                                                </div>
                                                            </div>
                                                            <span class="name" @click="showReply(index)"
                                                                  style="min-width: 76px;text-align: right;"
                                                                  :class="item.showReply ? 'el-icon-check' : 'el-icon-edit'"
                                                            >
                                                                {{item.showReply ? '取消回复' : '回复'}}
                                                            </span>
                                                        </div>
                                                        <mavon-editor style="min-height: 1px;min-width: 1px;" v-model="item.content" :editable="false" :subfield="false"
                                                                      :ishljs="true" codeStyle="tomorrow" :toolbarsFlag="false"
                                                                      defaultOpen="preview">
                                                        </mavon-editor>

                                                        <!--回复-->
                                                        <template v-if="item.showReply">
                                                            <mavon-editor style="min-height: 1px;min-width: 1px;margin-top: 10px"
                                                                          v-model="reply.content" :toolbars="showBar" codeStyle="tomorrow"
                                                                          :ishljs="true"  :toolbarsFlag="true" :subfield="false"
                                                                          placeholder="开始编辑...请使用markdown语法"
                                                                          defaultOpen="edit">
                                                            </mavon-editor>
                                                            <div class="reply-button">
                                                                <el-button type="info" @click.native="commitReply()">提交回复</el-button>
                                                                <el-button type="info" @click.native="item.showReply=false">取消回复</el-button>
                                                            </div>
                                                        </template>
                                                        <template v-if="item.replies && item.replies.length > 0">
                                                            <template v-for="val in item.replies">
                                                                    <el-row type="flex" justify="space-between" style="margin-top: 1rem;">
                                                                        <el-col :offset="1">
                                                                            <div class="content_header flex">
                                                                                <div class="header-left">
                                                                                    <div class="avatar">
                                                                                        <img :src="val.users.icon.match(/^http/) ? val.users.icon : server_url + val.users.icon">
                                                                                    </div>
                                                                                    <div>
                                                                                        <span class="name">{{val.user_name}}</span>
                                                                                        <span class="header-span">replied {{val.created_at}}</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <mavon-editor v-model="val.content" style="min-height: 1px;min-width: 1px" :editable="false"
                                                                                          :ishljs="true" codeStyle="tomorrow"
                                                                                          :toolbarsFlag="false" :subfield="false"
                                                                                          defaultOpen="preview">
                                                                            </mavon-editor>
                                                                        </el-col>
                                                                </el-row>
                                                            </template>
                                                        </template>

                                                    </el-col>
                                                </el-row>
                                        </template>
                                    </template>
                                    <h3>来说两句吧</h3>
                                    <mavon-editor style="min-width: 1px;height: auto" :subfield="false" :toolbars="showBar" codeStyle="tomorrow"
                                                  defaultOpen="edit" v-model="comment.content" :boxShadow="false"
                                                  :ishljs="true" placeholder="开始编辑...请使用markdown语法" :toolbarsFlag="true">
                                    </mavon-editor>
                                    <el-button type="info" @click.native="commitComment()" style="margin-top: 5px">提交评论</el-button>
                                </el-tab-pane>
                            </el-tabs>
                        </template>
                    </div>
                </el-col>
                <el-col :xs="{span:0}" :sm="{span:0}" :md="{span:9}" :lg="{span:5, pull:4}" style="background-color: floralwhite;overflow: hidden;">
                    <sideRight :init="options"  @listenForHot="getDetail(arguments[0])"
                               @sideListen="options=arguments[0];getList()"
                    ></sideRight>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<script>
    import '../../assets/css/globble.css';
    import {Row, Col, Tag, Pagination, Message, Loading, Tabs, TabPane, Button, Icon} from 'element-ui';
    import topNav from '../../components/topNav.vue';
    import sideRight from '../../components/sideRight.vue';
    import https from '../../libs/http';
    import mark from 'mark.js'
    import { mavonEditor } from 'mavon-editor'
    import 'mavon-editor/dist/css/index.css'
    let components = {};
    let array = [Row, Col, Tag, Pagination, topNav, sideRight, Tabs, TabPane, Button, Icon];
    array.forEach(item => {
        components[item.name] = item;
    });
    components['mavon-editor'] = mavonEditor;
    export default {
        name: 'app',
        components: components,
        data() {
            return {
                lists: [],
                article : {comments : [], id : 0},
                total: 0,
                per_page: 10,
                current_page: 1,
                type : ['primary', 'gray', 'success', 'warning', 'danger'],
                options : {query : '', tag_id : 0},
                show : 'index',
                server_url : this.$store.state.server_url,
                comment : {content : ''},
                reply : {content : ''},
                showBar : {
                    fullscreen: true, // 全屏编辑
                    help: true, // 帮助
                    trash: true, // 清空
                    preview: true, // 预览
                    undo: true, // 上一步
                    redo: true, // 下一步
                },
                active : 'article',
                commentIndex : 0,
            }
        },
        computed : {
            user () {
                return this.$store.state.home_user;
            }
        },
        methods: {
            handleCurrentChange (page) {
                this.getList({page : page});
            },
            getList (params = {}) {
                let loading = Loading.service({target : '#index-content'});
                this.$https.post('index', {...params, ...this.options}).then(
                    response => {
                        let data = response.data;
                        loading.close();
                        if (data.status) {
                            this.lists = data.data.data;
                            this.total = data.data.total;
                            this.per_page = data.data.per_page;
                            this.current_page = data.data.current_page;
                            this.show = 'index';
                            //滚动区域滚动到顶部
                            this.toTop();
                        } else {
                            Message({type : 'error', message : data.info, showClose : true});
                        }
                    }
                ).catch(
                    error => {loading.close();Message({type : 'error', message : '未知错误', showClose : true})}
                );
            },
            highLight () {/*搜索高亮显示*/
                let instance = new mark(document.querySelector("#app"));
                instance.unmark();
                this.$nextTick(() => {
                    if (this.options.query) {
                        instance.mark(this.options.query, [{
                            className:'mark',
                            accuracy : 'exactly', ignorePunctuation: [
                                "`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")",
                                "-", "_", "=", "+", "{", "}", "[", "]", "\\", "|", ":", ";",
                                "'", "\"", ",", ".", "<", ">", "/", "?"
                            ]
                        }]);
                    }
                });
            },
            getDetail (id) {
                let url = 'article/' + id;
                let loading = Loading.service({target : '#index-content'});
                https.get(url).then(
                    response => {
                        loading.close();
                        let data = response.data;
                        if (data.status) {
                            //页面回到顶部
                            this.toTop();
                            this.article = data.data;
                            this.show = 'article';
                            this.active = 'article';
                        } else {
                            Message({type : 'error', message : data.info, showClose : true});
                        }
                    }
                ).catch(
                    error => {
                        loading.close();
                        Message({type : 'error', message : '未知错误', showClose : true});
                    }
                )
            },
            //滚动区域滚动到顶部
            toTop () {
                document.getElementById('article-list').scrollTop=0;
            },
            commitComment () {
                if (!(this.$store.state.home_token&&this.$store.state.home_user)) {
                    Message({type : 'info', message : '您还未登录,请先登录'});return;
                }
                let loading = Loading.service({target : '#index-content'});
                let params = {article_id : this.article.id, content : this.comment.content};
                this.$https.postWA('comment/create', params, 'home').then(
                    response => {
                        loading.close();
                        let data = response.data;
                        if (data.status) {
                            this.article = data.data;
                            this.comment.content = '';
                            this.show = 'article';
                        } else {
                            Message({type : 'error', message : data.info, showClose : true});
                        }
                    }
                ).catch(
                    () => {
                        loading.close();
                        Message({type : 'error', message : '未知错误', showClose : true});
                    }
                );
            },
            showReply (index) {
                this.article.comments.forEach((item, key) => {
                    if (index == key) {
                        this.commentIndex = index;
                        item.showReply = !item.showReply;
                    } else {
                        item.showReply = false;
                    }
                });
                this.reply.content = '';
            },
            commitReply () {
                if (!(this.$store.state.home_token&&this.$store.state.home_user)) {
                    Message({type : 'info', message : '您还未登录,请先登录'});return;
                }
                let loading = Loading.service({target : '#index-content'});
                let params = {
                    comment_id : this.article.comments[this.commentIndex].id,
                    article_id : this.article.id, content : this.reply.content
                };
                this.$https.postWA('apply/create', params, 'home').then(
                    response => {
                        loading.close();
                        let data = response.data;
                        if (data.status) {
                            this.article = data.data;
                            this.comment.content = '';
                            this.show = 'article';
                        } else {
                            Message({type : 'error', message : data.info, showClose : true});
                        }
                    }
                ).catch(
                    () => {
                        loading.close();
                        Message({type : 'error', message : '未知错误', showClose : true});
                    }
                );
            }
        },
        beforeRouteEnter (to, from, next) {
            let loading = Loading.service();
            https.post('index', {init : 'yes'}).then(
                response => {
                    loading.close();
                    let data = response.data;
                    if (data.status) {
                        let list = data.data.list;
                        next(vm => {
                            vm.lists = list.data;
                            vm.total = list.total;
                            vm.per_page = list.per_page;
                            vm.current_page = list.current_page;
                            /*写入vuex*/
                            let side = {
                                hotArticle : data.data.hot,
                                dynamicTags : data.data.tags,
                                friendly_link : data.data.links
                            };
                            vm.$store.commit('setSideData', side);
                            /*写入sessionStorage*/
                            sessionStorage.removeItem('hotArticle');
                            sessionStorage.removeItem('dynamicTags');
                            sessionStorage.removeItem('friendly_link');
                            sessionStorage.removeItem('adverts');
                            sessionStorage.setItem('hotArticle', JSON.stringify(data.data.hot));
                            sessionStorage.setItem('dynamicTags', JSON.stringify(data.data.tags));
                            sessionStorage.setItem('friendly_link', JSON.stringify(data.data.links));
                            sessionStorage.setItem('adverts', JSON.stringify(data.data.adverts));
                        });
                    } else {
                        next(false);
                        Message({type : 'error', message : data.info, showClose : true});
                    }
                }
            ).catch(
                error => {
                    loading.close();
                    Message({type : 'error', message : '未知错误', showClose : true});
                }
            )
        },
        updated () {
            this.highLight();
        }
    }
</script>

<style>
    #app {
        display: flex;
        flex-direction: column;
    }

    #article-list {
        flex: 1;
        overflow-y: scroll;
    }

    .grid-content {
        border-radius: 4px;
        line-height: 36px;
        padding: 1.5rem;
    }

    .text {
        font-size: 14px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        word-break: break-all;
        text-overflow: ellipsis;
        max-height: 4rem;
        word-wrap: break-word;
    }

    .mark{
        background: orange;
        color: black;
    }
    .listImg{
        width: 100%;
    }
    .other{
        display: flex;
        flex-direction: column;
    }
    .list-item{
        margin: 0 0 26px;
    }

    .list-item{
        box-shadow: 3px 4px 16px rgba(0,0,0,.08);
    }
    .content_header{
        background-color: #f6f8fa;
        border-bottom:1px solid #dde0e4;
        border-left:4px solid #00CED1;
        line-height: 24px;
        padding-right: 12px;
        font-size: 13px;
        color: #666;
    }

    .content_header .header-span{
        line-height: 20px;
        color:#999;
    }
    .avatar{
        width: 42px;
        height: 42px;
        overflow:hidden;
        border-radius: 50%;
        display: flex;
        margin: 0px 8px;
        flex-shrink: 0;
    }
    .avatar > img{
        max-width: 100%;
        max-height: 100%;
    }
    .header-left {
        display: flex;
        align-items: center;
    }
    .reply-button {
        display: flex;
        justify-content: space-between;
        margin-top: 5px
    }
    .content-margin{
        margin-top: 1rem;
    }
    .comment-header{
        border-top: 1px solid #dde0e4;
        border-right:1px solid #dde0e4;
    }
    blockquote {
        display: block;
        -webkit-margin-before: 0em;
        -webkit-margin-after: 0em;
        -webkit-margin-start: 0rem;
        -webkit-margin-end: 0rem;
    }
    .reprint{
        font-size: 1rem;
        color: firebrick;
    }
    .pointer{
        cursor: pointer;
    }
</style>
