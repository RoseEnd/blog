<template>
    <div>
        <el-table :data="lists" stripe width="100%">
            <el-table-column
                    label="序号" width="70px"
            >
                <template scope="scope">{{scope.$index + (current_page -1)*per_page + 1}}</template>
            </el-table-column>

            <el-table-column
                    label="链接名称" width="auto"
            >
                <template scope="scope">
                    <el-input v-model="scope.row.link_name" :disabled="!scope.row.isEdit">
                    </el-input>
                </template>
            </el-table-column>
            <el-table-column
                    label="链接地址" width="auto"
            >
                <template scope="scope">
                    <el-input v-model="scope.row.link_url" :disabled="!scope.row.isEdit">
                        <template slot="prepend">Http://</template>
                    </el-input>
                </template>
            </el-table-column>
            <el-table-column
                    label="排序" width="100px"
            >
                <template scope="scope">
                    <el-input v-model="scope.row.sort" :disabled="!scope.row.isEdit" type="number">
                    </el-input>
                </template>
            </el-table-column>
            <el-table-column
                    label="操作" fixed="right" align="center" width="200px"
            >
                <template scope="scope">
                    <template v-if="scope.row.id">
                        <el-button
                                size="small"
                                @click.native="lists[scope.$index].isEdit = !lists[scope.$index].isEdit">
                            {{lists[scope.$index].isEdit ? '取消' : '编辑'}}
                        </el-button>
                        <el-button v-if="scope.row.isEdit"
                                   size="small" type="info"
                                   @click.native="updateData(scope)">
                            保存
                        </el-button>
                        <el-button
                                size="small"
                                type="danger"
                                @click="handleDelete(scope.row.id)">删除</el-button>
                    </template>
                    <template v-else>
                        <el-button v-if="scope.row.isEdit"
                                   size="small" type="info"
                                   @click.native="handleAdd(scope)">
                            保存
                        </el-button>
                        <el-button
                                size="small"
                                @click.native="cancel(scope)">
                            取消
                        </el-button>
                    </template>
                </template>
            </el-table-column>
        </el-table>
        <el-button type="info" @click.native="add()">新增</el-button>
        <el-pagination  :page-size="per_page" :total="total" layout="total, prev, pager, next, jumper"
                        @current-change="handleCurrentChange" v-if="total/per_page > 1" style="display: flex;margin-top:30px;">
        </el-pagination>
    </div>
</template>
<script>
    import https from '../../libs/http'
    import {
        Loading, Message, Table, TableColumn, Button, Pagination,
        Row, Col, MessageBox, Input
    } from 'element-ui'
    let components = {};
    let array = [
        Table, TableColumn, Button, Pagination,
        Row, Col, Input
    ];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        components : components,
        data () {
            return {
                lists : [],
                per_page : 1,
                total : 0,
                current_page : 1,
                type : ['primary', 'gray', 'success', 'warning', 'danger'],
                img_src : this.$store.state.server_url
            }
        },
        methods : {
            handleDelete (id) {
                MessageBox.confirm (
                    '此操作会永久删除这个标签',
                    '警告',
                    {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }

                ).then(
                    () => {
                        let load = Loading.service({target : '#admin-content'});
                        this.$https.postWA('admin/links/delete', {id : id}).then(
                            response => {
                                load.close();
                                let data = response.data;
                                if (!data.status) {
                                    Message({type : 'error', message : data.info});
                                } else {
                                    this.getList({page : this.current_page});
                                }
                            }
                        ).catch(
                            () => {
                                load.close();
                                Message({type : 'error', message : '未知错误'});
                            }
                        );
                    }
                ).catch(()=>{});;
            },
            checkData (params) {
                if (!/[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/.test(params['link_url'])) return {valid : false, mes : '链接地址格式不正取'};
                if (!params['link_name']) return {valid : false, mes : '链接名字不能为空'};
                return {valid : true, mes : '成功'};
            },
            updateData (scope) {
                let row = scope.row;
                let params = {id : row.id, link_name : row.link_name, link_url : row.link_url, sort : row.sort};
                let res = this.checkData(params);
                if (!res.valid) return Message({type : 'error', message : res.mes});
                let load = Loading.service({target : '#admin-content'});
                this.$https.postWA('admin/links/update', params).then(
                    response => {
                        load.close();
                        let data = response.data;
                        Message({type : data.status ? 'success' : 'error', message : data.info});
                        if (data.status) this.lists[scope.$index].isEdit = false;
                    }
                ).cache(
                    () => {
                        load.close();
                        Message({type : 'error', message : data.info});
                    }
                );
            },
            add () {
                this.lists.push({link_name : '', link_url : '', isEdit : true, 'sort' : 0});
            },
            handleAdd (scope) {
                let params = {link_name : scope.row.link_name, link_url : scope.row.link_url, sort : scope.row.sort};
                let res = this.checkData(params);
                if (!res.valid) return Message({type : 'error', message : res.mes});
                let load = Loading.service({target : '#admin-content'});
                this.$https.postWA('admin/links/create', params).then(
                    response => {
                        load.close();
                        let data = response.data;
                        if (data.status) {
                            this.lists = data.data.data;
                            this.total = data.data.total;
                            this.per_page = data.data.per_page;
                            this.current_page = data.data.current_page;
                        } else {
                            Message({type : 'error', message : data.info});
                        }
                    }
                ).cache(
                    () => {
                        load.close();
                        Message({type : 'error', message : '添加失败'});
                    }
                );
            },
            cancel (scope) {
                let index = scope.$index;
                this.lists.splice(index, 1);
            },
            handleCurrentChange (page) {
                this.getList({page : page});
            },
            getList (params = {}) {
                this.$https.getWA('admin/links', {...params}).then(
                    response => {
                        let data = response.data;
                        this.lists = data.data;
                        this.total = data.total;
                        this.per_page = data.per_page;
                        this.current_page = data.current_page;
                    }
                ).catch(
                    () => {Message({type : 'error', message : '未知错误'})}
                );
            }
        },
        beforeRouteEnter (to, from, next) {
            /*获取标签列表*/
            https.getWA('admin/links').then(
                response => {
                    let data = response.data;
                    next(vm => {
                        vm.lists = data.data;
                        vm.total = data.total;
                        vm.per_page = data.per_page;
                        vm.current_page = data.current_page;
                    });
                }
            ).catch(
                () => {Message({type : 'error', message : '未知错误'})}
            );
        }
    }
</script>