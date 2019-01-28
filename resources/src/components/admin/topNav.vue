<template>
    <el-row type="flex" justify="space-between" align="middle" style="background-color: #00CED1">
        <el-col  :xs="{span:15, offset:0}" :sm="{span:15, offset:0}" :md="{span:13, offset:2}">
            <div class="grid-content" style="color: #fff;">SX's Blog</div>
        </el-col>
        <el-col :xs="{span:9,pull:0}" :sm="{span:9,pull:0}" :md="{span:7,pull:2}">
            <div class="grid-content status">
                <router-link to="/login" tag="span" v-if="author" style="color: #fff;">登录</router-link>
                <el-dropdown v-else>
                    <span class="el-dropdown-link" style="color: #fff;">
                        {{user.name}}<i class="el-icon-caret-bottom el-icon--right"></i>
                    </span>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item>个人信息</el-dropdown-item>
                        <el-dropdown-item>退出</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
        </el-col>
    </el-row>
</template>
<script type="text/ecmascript-6">
    import {Row, Col, Dropdown, DropdownMenu, DropdownItem} from 'element-ui';
    let components = {};
    let array = [Row, Col, Dropdown, DropdownMenu, DropdownItem];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        name: 'topNav',
        components : components,
        data() {
            return {
                author: true,
                user: {
                    name: 'Reina'
                }
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
</style>