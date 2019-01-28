<template>
    <el-row type="flex"  align="middle" style="background-color: #00CED1;max-height: 72px;">
        <el-col :xs="{span:2, offset:0}" :sm="{span:2, offset:0}" :md="{span:0}" style="overflow: hidden;">
            <i class="iconfont icon-geren" style="padding: 8px;color: #f0f0f0;" @click="isCollapse = !isCollapse"></i>
            <transition name="el-zoom-in-left">
                <el-menu
                        v-if="isCollapse"
                        class="user-info"
                        key="login_in"
                >
                    <el-menu-item index="3" class="side-content">
                            <div class="list">
                                <el-tag :type="type[index%type.length]"
                                        :key="index"
                                        v-for="(tag, index) in dynamicTags"
                                        @click.native="getForTag(tag.id)"
                                >
                                    {{tag.name}}
                                </el-tag>
                            </div>
                    </el-menu-item>

                    <el-menu-item index="1" v-if="user" style="line-height: 88px;height: 88px;border-bottom: 1px solid #dedeed;">
                        <div class="avatar_wrapper">
                            <img class="avatar" :src="user.icon.match(/^http/) ? user.icon : server_url + user.icon">
                        </div>
                    </el-menu-item>
                    <el-menu-item index="2" v-if="user">
                        <i class="iconfont icon-tuichu"></i>
                        <span slot="title" @click.prevent="logout()" class="pointer">退出</span>
                    </el-menu-item>
                    <el-menu-item index="1" style="border-bottom: 1px solid #dedeed;" v-if="!user">
                        <i class="iconfont icon-denglu" style="font-size: 18px;"></i>
                        <span slot="title" @click="$router.push({path : '/login'})" class="pointer">登录</span>
                    </el-menu-item>
                    <el-menu-item index="2" v-if="!user">
                        <i class="iconfont icon-zhuce1"></i>
                        <span slot="title" @click="$router.push({path : '/register'})" class="pointer">注册</span>
                    </el-menu-item>
                </el-menu>
            </transition>
        </el-col>
        <el-col :xs="{span:0}" :sm="{span:0}" :md="{span:15}" :lg="{span:11,offset:4}" style="overflow: hidden;">
            <span class="grid-content pointer" style="color: #fff;" @click="$emit('sideListen', {query : '', tag_id : 0})">roseEnd's Blog</span>
        </el-col>
        <el-col :xs="{span:20, offset:0}" :sm="{span:20, offset:0}" :md="{span:0}" style="overflow: hidden;">
            <span class="grid-content pointer" style="color: #fff;text-align: center;" @click="$emit('sideListen', {query : '', tag_id : 0})">roseEnd's Blog</span>
        </el-col>
        <el-col :xs="{span:0,pull:0}" :sm="{span:0,pull:0}" :md="{span:9}" :lg="{span:5}" style="overflow: hidden;">
            <div class="grid-content status">
                <router-link to="/register" tag="span"  v-if="!user" style="color: #fff;" class="pointer">注册</router-link>
                <router-link to="/login" tag="span"  v-if="!user" style="color: #fff;" class="pointer">登录</router-link>
                <el-dropdown v-else>
                    <span class="el-dropdown-link" style="color: #fff;">
                        {{user.name}}<i class="el-icon-caret-bottom el-icon--right" ></i>
                    </span>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item>{{user.name}}</el-dropdown-item>
                        <el-dropdown-item @click.native="logout()" class="pointer">退出</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
        </el-col>
    </el-row>
</template>
<script type="text/ecmascript-6">
    import {Row, Col, Dropdown, DropdownMenu, DropdownItem, Message, Menu, MenuItem, Tag} from 'element-ui';
    import CollapseTransition from 'element-ui/lib/transitions/collapse-transition';
    let components = {};
    let array = [Row, Col, Dropdown, DropdownMenu, DropdownItem, Menu, MenuItem, Tag, CollapseTransition];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        name: 'topNav',
        components : components,
        props: {
            init : {
                type : Object,
                default : () => {return {query : '', tag_id : 0}}
            }
        },
        data() {
            return {
                type : ['primary', 'gray', 'success', 'warning', 'danger'],
                author: true,
                isCollapse: false,
                options : this.init,
                server_url : this.$store.state.server_url
            }
        },
        computed : {
            user () {
                return this.$store.state.home_user;
            },
            friendly_link () {
                return this.$store.state.friendly_link;
            },
            hotArticle () {
                return this.$store.state.hotArticle;
            },
            dynamicTags () {
                return this.$store.state.dynamicTags;
            }
        },
        methods : {
            getForTag (tag_id) {
                this.options.tag_id = tag_id;
                this.$emit('sideListen', this.options);
                this.isCollapse = false;
            },
            logout () {
                this.$https.postWA('logout', {}, 'home').then(
                    response => {
                        let data = response.data;
                        if (data.status) {
                            /*清除sessionStorage*/
                            sessionStorage.removeItem('home_user');
                            sessionStorage.removeItem('home_token');
                            this.$store.commit('home_logout');
                            this.isCollapse = false;
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
    .bg-purple {
        background: #d3dce6;
    }
    .status {
        text-align: right;
        padding: 6px 10px;
        margin-right: 10px;
    }
    .grid-content {
        border-radius: 4px;
        min-height: 36px;
    }
    .user-info{
        height: 100%;
        position: fixed;
        top: 72px;
        z-index: 1501;
        min-width: 66%;
        box-shadow: 2px 0 3px rgba(0, 0, 0, .16);
    }
    .avatar_wrapper{
        width: 100%;
        height: 100%;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .avatar{
        max-width: 100%;
        max-height: 100%;
        border-radius: 50%;
    }
    .el-col-xs-20 .grid-content{
        text-align: center;
    }
    .box-card .define{
        width: 80%;
        height: 58%;
        box-shadow: 3px 3px 3px rgba(0, 0, 0, .3);
    }
    .side-content{
        border-bottom: 0px solid #dedeed;
    }
    .pointer{
        cursor: pointer;
    }
</style>