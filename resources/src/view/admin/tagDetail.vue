<template>
    <el-row>
        <el-col :xs="{span:22, offset:1}" :sm="{span:22, offset:1}" :md="{span:18, offset:2}">
            <el-form :label-position="'left'" label-width="80px">

                <el-form-item label="标签图片">
                    <el-upload
                            :class="tag.img_path ? 'hide-upload' : ''"
                            action="https://jsonplaceholder.typicode.com/posts/"
                            :file-list="fileList"
                            list-type="picture-card" :on-change="changeImage" :auto-upload="false"
                            :on-preview="handlePictureCardPreview" :on-remove="removeImage">
                        <i class="el-icon-plus"></i>
                    </el-upload>
                    <el-dialog v-model="dialogVisible" size="tiny">
                        <img width="100%" :src="dialogImageUrl">
                    </el-dialog>
                </el-form-item>
                <el-form-item label="标签名">
                    <el-input v-model="tag.name"></el-input>
                </el-form-item>
                <el-form-item label="标签描述">
                    <el-input v-model="tag.description" type="textarea"></el-input>
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
        Loading, Message, Button, Upload, Dialog,
        Form, Input, FormItem, Row, Col
    } from 'element-ui'
    let components = {};
    let array = [
        Button, Form, Input, FormItem, Upload, Dialog,
        Row, Col
    ];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        components : components,
        data () {
            return {
                tag : {
                    id : 0,
                    name : '',
                    description : '',
                    img_path : '',
                },
                dialogImageUrl : '',
                dialogVisible : false,
                file : '',
                action : 'detail',
                server_url : this.$store.state.server_url
            }
        },
        computed : {
            fileList () {
                return this.tag.img_path ? [{url : this.tag.img_path.match(/^http|data:image/) ?
                    this.tag.img_path : this.server_url + this.tag.img_path}] : []
            }
        },
        methods : {
            onSubmit () {
                let load = Loading.service({target : '#admin-content'});
                let url = this.action == 'detail' ? 'admin/tags/update' : 'admin/tags/create';
                https.postWA(url, {...this.tag}).then(
                    response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                            this.$router.push('/admin/tag')
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
                    this.tag.img_path = e.target.result;
                };
            },
            removeImage () {
                this.file = '';
                this.tag.img_path = '';
            }
        },
        beforeRouteEnter (to, from, next) {/*添加和编辑*/
            let action = to.name == 'tagDetail';
            if (!action) {
                next(vm => {
                    vm.tag = {
                        id : 0,
                        name : '',
                        description : '',
                        img_path : '',
                    };
                    vm.file = '';
                    vm.action = 'create';
                })
            } else {
                let load = Loading.service({target : '#admin-content'});
                https.postWA('admin/tags/detail', {id : to.params.id}).then(
                    response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                            next(vm => {
                                vm.tag = data.data;
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