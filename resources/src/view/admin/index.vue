<template>
    <div style="height: 100%;">
        <el-row type="flex" style="height: 100%;">
            <el-col style="width: auto;height: 100%;display: flex;flex-direction: column;">
                <el-row type="flex" justify="space-between" align="middle" style="background-color: #00CED1;">
                    <el-col>
                        <transition name="slide" mode="out-in">
                            <h4 v-if="!isCollapse" class="grid-content side_title" key="text">后台管理</h4>
                            <h4 v-else class="grid-content">
                                <i class="el-icon-more" key="icon" style="color: #fff;" @click="isCollapse=!isCollapse"></i>
                            </h4>
                        </transition>
                    </el-col>
                </el-row>
                <el-menu default-active="2" theme="dark" unique-opened :collapse="isCollapse" class="el-menu"
                         router style="flex: 1;">
                    <template v-for="(item, index) in menu">
                        <el-submenu  v-if="item.sub" :index="item.index">
                            <template slot="title">
                                <i class="iconfont icon-link"></i>
                                <span slot="title">{{item.title}}</span>
                            </template>
                            <template v-for="(val, key) in item.sub">
                                <el-menu-item :index="val.index">
                                    <i class="iconfont icon-circlehollow"></i>
                                    <span slot="title">{{val.title}}</span>
                                </el-menu-item>
                            </template>
                        </el-submenu>
                        <template v-else>
                            <el-menu-item :index="item.index">
                                <span slot="title">{{item.title}}</span>
                                <i class="el-icon-edit"></i>
                            </el-menu-item>
                        </template>
                    </template>
                </el-menu>
            </el-col>
            <el-col style="flex:1; display: flex; flex-direction: column;" id="admin-content">
                <el-row type="flex" justify="space-between" align="middle" style="background-color: #fff;flex-shrink:0;border-bottom: 1px solid #f0f0f0;">
                    <el-col  :xs="{span:15, offset:0}" :sm="{span:15, offset:0}" :md="{span:14, offset:1}" style="display: flex; align-items: center;">
                        <div class="grid-content" v-if="!isCollapse">
                            <i class="el-icon-more define" @click="isCollapse=!isCollapse"></i>
                        </div>
                    </el-col>
                    <el-col :xs="{span:9,pull:0}" :sm="{span:9,pull:0}" :md="{span:8,pull:1}" style="display: flex; align-items: center;justify-content: flex-end;">
                        <div class="photo" v-if="user">
                            <img  :src="user.icon.match(/^http/) ? user.icon : server_url + user.icon">
                        </div>
                        <div class="grid-content" style="padding: .35rem;">
                            <div>
                                <el-dropdown trigger="click" placement="bottom-start">
                                    <span class="el-dropdown-link" v-if="user">
                                        {{user.name}}<i class="el-icon-caret-bottom el-icon--right"></i>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item>个人信息</el-dropdown-item>
                                        <el-dropdown-item @click.native="logout()">退出</el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                                <p style="color: #999;font-size:12px;">上次登录：2017/11/08 11:30:28</p>
                            </div>
                        </div>
                    </el-col>
                </el-row>
                <router-view style="flex: 1; overflow-y: scroll;margin-top: 10px"></router-view>
            </el-col>
        </el-row>
    </div>
</template>
<script>
    import "../../assets/css/globble.css"
    import {Menu, Submenu, MenuItem, Row, Col, Dropdown, DropdownMenu, DropdownItem} from 'element-ui'
    let components = {};
    let array = [Menu, Submenu, MenuItem, Row, Col, Dropdown, DropdownMenu, DropdownItem];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        components : components,
        data () {
            return {
                menu : [
                    {title : '文章管理', index : '/admin/article#', sub : [
                        {title : '文章列表', index : '/admin/article'},
                        {title : '添加文章', index : '/admin/article/create'}
                    ]},
                    {title : '标签管理', index : '/admin/tag#', sub : [
                            {title : '标签列表', index : '/admin/tag'},
                            {title : '添加标签', index : '/admin/tag/create'}
                        ]
                    },
                    {title : '友情链接', index : '/admin/links#', sub : [
                        {title : '链接列表', index : '/admin/links'}
                    ]},
                    {title : '广告管理', index : '/admin/adverts#', sub : [
                        {title : '广告列表', index : '/admin/adverts'},
                        {title : '添加广告', index : '/admin/adverts/create'}
                    ]}
                ],
                isCollapse : false,
                server_url : this.$store.state.server_url
            }
        },
        computed : {
            user () {
                return this.$store.state.admin_user;
            }
        },
        methods : {
            logout () {
                this.$https.postWA('logout').then(
                    response => {
                        let data = response.data;
                        if (data.status) {
                            /*清除sessionStorage*/
                            sessionStorage.removeItem('admin_user');
                            sessionStorage.removeItem('admin_token');
                            this.$store.commit('admin_logout');
                            setTimeout(() => {this.$router.replace('/admin/login')})
                        } else {
                            Message({type : 'error', message : data.info});
                        }
                    }
                ).catch(
                    () => {Message({type : 'error', message : '退出登录失败'})}
                );
            }
        }
    }
</script>
<style scoped>
    .el-menu:not(.el-menu--collapse) {
        width: 200px;
    }
    .define.el-icon-more{
        width: 20px;
        color: #00CED1;
    }
    .grid-content {
        border-radius: 4px;
        line-height: 36px;
        padding: 1.5rem;
    }
    .photo{
        height: 50px;
        width: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        padding: 0;
    }
    .photo > img{
        width: 100%;
        height: 100%;
        border: 1px solid #f0f0f0;
    }
    .side_title{
        color: #fff;
        text-align: center;
    }
    .slide-enter-active, .slide-leave-active {
        transition: opacity .1s;
    }
    .slide-enter, .slide-leave-to{
        opacity: 0;
    }
</style>