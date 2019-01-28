<template>
    <div class="content-wrapper" id="login">
        <el-row type="flex" class="validate-wrapper fwrap">
            <el-col :xs="{span: 20, offset:2}" :sm="{span:20, offset:2}"
                    :md="{span:8, offset:8}" class="validate">
                <el-form ref="form" :model="form" label-position="left" label-width="60px">
                    <el-form-item label="账号">
                        <el-input class="define" v-model="form.name" auto-complete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="密码" prop="pass">
                        <el-input class="define" type="password" v-model="form.password" auto-complete="off"></el-input>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :xs="{span: 20, offset:2}" :sm="{span:20, offset:2}" :md="{span:4, offset:10}" class="content">
                <el-button type="success" @click.native="login()">登录</el-button>
                <el-button :plain="true" type="success" class="register"
                           style="margin-left:0;background-color: transparent;border-color: #fafafa;color: #fafafa;">
                    <router-link to="/register" tag="p" style="width: 100%;height: 100%;">注册</router-link>
                </el-button>
            </el-col>
        </el-row>
    </div>
</template>
<script>
    import '../../assets/css/globble.css';
    import {Row, Col, Form, FormItem, Input, Button, Message} from 'element-ui';
    let components = {};
    let array = [Row, Col, Form, FormItem, Input, Button];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default{
        components: components,
        data() {
            return{
                form: {
                    name: '',
                    password: ''
                }
            }
        },
        methods : {
           login () {
               this.$https.post('login', this.form).then(
                   response => {
                        let data = response.data;
                        if (data.status) {
                            /*将token写入vux与sessionStorage*/
                            window.sessionStorage.removeItem('home_login');
                            window.sessionStorage.removeItem('home_user');
                            this.$store.commit('home_login', data.data);
                            sessionStorage.setItem('home_token', data.data.token);
                            window.sessionStorage.setItem('home_user', JSON.stringify(data.data.user));
                            setTimeout(()=>{
                                this.$router.replace({path : '/'})
                            }, 500)
                        } else {
                            Message({type : 'error', message : data.info});
                        }
                   }
               ).catch(
                   () => {
                       Message({type : 'error', message : '未知错误'});
                   }
               );
           }
        }
    };
</script>
<style>
    #login.content-wrapper{
        height: 100%;
        background: url("../../assets/sun.jpg") no-repeat center;
        background-size: cover;
    }
    #login .validate-wrapper{
        width: 100%;
        height: 100%;
    }
    #login .validate, .content{
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    #login .content{
        justify-content: center;
    }
    #login .mask{
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: white;
        z-index: 0;
        top: 0;
        left: 0;
        opacity: .26;
    }
    #login .el-button+.register{
        margin: 20px 0 36px;
    }
    #login .el-input__inner{
        border-width: 0;
        border-bottom: 1px solid #999;
        border-radius: 0;
    }
    #login .el-input.define > .el-input__inner{
        background: transparent;
    }
</style>