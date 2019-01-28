<template>
    <el-row>
        <el-col :xs="{span:22, offset:1}" :sm="{span:22, offset:1}" :md="{span:18, offset:2}">
            <el-form :label-position="'left'" label-width="80px">
                <el-form-item label="是否展示">
                    <el-radio-group v-model="advert.display">
                        <el-radio label="yes">是</el-radio>
                        <el-radio label="no">否</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="展示位置">
                    <el-radio-group v-model="advert.position">
                        <el-radio :label="index" v-for="(item, index) in position" :key="index">{{item}}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="广告描述">
                    <el-input v-model="advert.description" type="textarea"></el-input>
                </el-form-item>
                <el-form-item label="广告代码">
                    <el-input type="textarea" v-model="advert.code"></el-input>
                </el-form-item>
                <el-form-item label="广告展示">
                    <div v-html="advert.code"></div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click.native="onSubmit()">{{action == 'detail' ? '确定修改' : '添加'}}</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>
</template>
<script>
    import https from '../../libs/http'
    import {
        Loading, Message, Button, Dialog, RadioGroup, Radio,
        Form, Input, FormItem, Row, Col
    } from 'element-ui'
    let components = {};
    let array = [
        Button, Form, Input, FormItem, Dialog,
        Row, Col, RadioGroup, Radio
    ];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        components : components,
        data () {
            return {
                advert : {
                    id : 0,
                    code : '',
                    position : 'bottom',
                    description : '',
                    display : 'yes',
                },
                action : 'detail',
                position : []
            }
        },
        methods : {
            onSubmit () {
                let load = Loading.service({target : '#admin-content'});
                let url = this.action == 'detail' ? 'admin/adverts/update' : 'admin/adverts/store';
                https.postWA(url, {...this.advert}).then(
                    response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                            this.$router.push('/admin/adverts')
                        } else {
                            Message({type : 'error', message : data.info});
                        }
                    }
                ).catch(
                    error => {
                        load.close();
                        Message({type : 'error', message : '未知错误'});
                    }
                );
            },
            handlePictureCardPreview (file) {
                this.dialogImageUrl = file.url;
                this.dialogVisible = true;
            },
            /*获取图片的base64编码*/
            changeImage (fileInfo) {
                let file = fileInfo.raw;
                let fileReader = new FileReader();
                if (file) fileReader.readAsDataURL(file);
                fileReader.onload = (e) => {
                    this.advert.img_path = e.target.result;
                };
            },
            removeImage () {
                this.file = '';
                this.advert.img_path = '';
            }
        },
        beforeRouteEnter (to, from, next) {/*添加和编辑*/
            let action = to.name == 'advertDetail';
            if (!action) {
                let load = Loading.service({target : '#admin-content'});
                https.getWA('admin/adverts/create').then(
                    response => {
                        load.close();
                        let data = response.data;
                        next(vm => {
                            vm.advert = {
                                id : 0,
                                code : '',
                                position : 'bottom',
                                description : '',
                                display : 'yes',
                            };
                            vm.position = data;
                            vm.action = 'create';
                        });
                    }
                ).catch(
                    error => {
                        load.close();
                        Message({type : 'error', message : '未知错误'});
                    }
                );
            } else {
                let load = Loading.service({target : '#admin-content'});
                https.postWA('admin/adverts/detail', {id : to.params.id}).then(
                    response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                            next(vm => {
                                vm.advert = data.data.advert;
                                vm.position = data.data.position;
                                vm.action = 'detail';
                            });
                        } else {
                            next(false);
                            Message({type : 'error', message : data.info});
                        }
                    }
                ).catch(
                    error => {
                        load.close();
                        Message({type : 'error', message : '未知错误'});
                    }
                );
            }
        }
    }
</script>