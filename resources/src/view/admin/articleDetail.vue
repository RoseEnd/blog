<template>
    <el-row>
        <el-col :xs="{span:22, offset:1}" :sm="{span:22, offset:1}" :md="{span:18, offset:2}">
            <el-form :label-position="'left'" label-width="80px">

                <el-form-item label="是否置顶">
                    <el-radio-group v-model="article.pull_top">
                        <el-radio label="yes">是</el-radio>
                        <el-radio label="no">否</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="文章模式">
                    <el-radio-group v-model="article.status">
                        <el-radio label="public">公开</el-radio>
                        <el-radio label="private">私有</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="博主推荐">
                    <el-radio-group v-model="article.article_support">
                        <el-radio label="yes">推荐</el-radio>
                        <el-radio label="no">不推荐</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="文章标签">
                    <el-checkbox-group v-model="selectedTags">
                        <el-checkbox :label="item.id" v-for="(item, index) in allTags" :key="index">{{item.name}}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="缩略图">
                    <el-upload
                            :class="article.image ? 'hide-upload' : ''"
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
                <el-form-item label="关键字">
                    <el-input v-model="article.key_words"></el-input>
                </el-form-item>
                <el-form-item label="文章标题">
                    <el-input v-model="article.title"></el-input>
                </el-form-item>
                <el-form-item label="文章内容">
                    <mavon-editor style="height:100%;" v-model="article.content" :subfield="false" ref="editor"
                                  :defaultOpen="action == 'detail' ? 'preview' : 'edit'" @imgAdd="uploadFile" :boxShadow="false"
                                  :ishljs="true" codeStyle="tomorrow" placeholder="开始编辑...请使用markdown语法">
                    </mavon-editor>
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
    import { mavonEditor } from 'mavon-editor'
    import 'mavon-editor/dist/css/index.css'
    import {
        Loading, Message, Button, Upload, Dialog,
        Form, Input, FormItem, Row, Col, RadioGroup, Radio, Checkbox, CheckboxGroup
    } from 'element-ui'
    let components = {};
    let array = [
       Button, Form, Input, FormItem, Upload, Dialog,
        Row, Col, RadioGroup, Radio, Checkbox, CheckboxGroup
    ];
    array.forEach(item => {
        components[item.name] = item;
    });
    components['mavon-editor'] = mavonEditor;
    export default {
        components : components,
        data () {
            return {
                article : {
                    id : 0,
                    title : '',
                    content : '',
                    render : '',
                    pull_top : 'yes',
                    status : 'public',
                    article_support : 'yes',
                    key_words : '',
                    image : ''
                },
                allTags : [],
                selectedTags : [],/*选中的标签*/
                action : 'detail',
                dialogImageUrl : '',
                dialogVisible : false,
                file : '',
                server_url : this.$store.state.server_url
            }
        },
        computed : {
            fileList () {
                return this.article.image ? [{url : this.article.image.match(/^http|data:image/) ?
                    this.article.image : this.server_url + this.article.image}] : []
            }
        },
        methods : {
            onSubmit () {
                let load = Loading.service({target : '#admin-content'});
                let url = this.action == 'detail' ? 'admin/article/update' : 'admin/article/store';
                this.article.render = this.$refs.editor.d_render;
                https.postWA(url, {...this.article, tags : JSON.stringify(this.selectedTags), image : this.file}).then(
                    response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                           this.$router.push('/admin/article')
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
                    this.file = e.target.result;
                    this.article.image = e.target.result;
                };
            },
            removeImage () {
                this.file = '';
                this.article.image = '';
            },
            uploadFile (filename, file) {
                if (!file) return;
                let images = ['image/jpeg', 'image/jpg', 'image/png', 'image/jif', 'image/bmp'];
                if (images.indexOf(file.type) == -1) {
                    Message({type : 'error', message : '不支持的图片类型'});
                    return
                };
                let fileReader = new FileReader();
                fileReader.readAsDataURL(file);
                fileReader.onload = (e) => {
                    let base64 = e.target.result;
                    /*开始上传*/
                    let load = Loading.service({target : '#admin-content'});
                    this.$https.postWA('upload', {file : base64}).then(response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                            this.$refs.editor.$img2Url(filename, data.data.filePath);
                        } else {
                            Message({type : 'error', message : data.info});
                        }
                    }).catch(() => {
                        load.close();
                        Message({type : 'error', message : '上传图片失败'});
                    });
                };
            },

        },
        beforeRouteEnter (to, from, next) {/*添加和编辑*/
            let action = to.name == 'articleDetail';
            let params = action ? {id : to.params.id} : {};
            let url = action ? 'admin/article/edit' : 'admin/article/create';
            let load = Loading.service({target : '#admin-content'});
            https.postWA(url, params).then(
                response => {
                    load.close();
                    let data = response.data;
                    if (data.status) {
                        if (action) {
                            let oldTags = data.data.articleTags;
                            let newTags = [];
                            oldTags.forEach(item => {newTags.push(item.tag_id)});
                            next(vm => {
                                vm.action = 'detail';
                                vm.article = data.data.article;
                                vm.selectedTags = newTags;
                                vm.allTags = data.data.tags;
                            });
                        } else {
                            next(vm => {
                                vm.action = 'create';
                                vm.allTags = data.data;
                                /*重新初始化*/
                                vm.article = {
                                    id : 0,
                                    title : '',
                                    content : '',
                                    render : '',
                                    pull_top : 'yes',
                                    status : 'public',
                                    article_support : 'yes',
                                    key_words : '',
                                    image : ''
                                };
                                vm.selectedTags = [];
                            });
                        }
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
</script>