<template>
    <div>
        <el-table :data="lists" stripe width="100%">
            <el-table-column
                    label="序号" width="auto"
            >
                <template scope="scope">{{scope.$index + (current_page -1)*per_page + 1}}</template>
            </el-table-column>

            <el-table-column
                    label="标签名" width="auto"
            >
                <template scope="scope">
                    <el-popover trigger="hover" placement="top">
                        <p>描述: {{ scope.row.description }}</p>
                        <div slot="reference" class="name-wrapper">
                            <el-tag :type="type[scope.$index%type.length]">{{ scope.row.name }}</el-tag>
                        </div>
                    </el-popover>
                </template>
            </el-table-column>
            <el-table-column
                    label="标签图片" width="auto"
            >
                <template scope="scope">
                    <img :src="scope.row.img_path.match(/^http/) ? scope.row.img_path : img_src + scope.row.img_path" alt="" style="max-height: 60px;">
                </template>
            </el-table-column>

            <el-table-column
                    label="操作" fixed="right" align="center" width="auto"
            >
                <template scope="scope">
                    <el-button
                            size="small"
                            @click="$router.push({name : 'tagDetail', params : {id : scope.row.id}})">编辑</el-button>
                    <el-button
                            size="small"
                            type="danger"
                            @click="handleDelete(scope.row.id)">删除</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination  :page-size="per_page" :total="total" layout="total, prev, pager, next, jumper"
                        @current-change="handleCurrentChange" v-if="total/per_page > 1" style="display: flex;margin-top:30px;">
        </el-pagination>
    </div>
</template>
<script>
    import https from '../../libs/http'
    import {
        Loading, Message, Table, TableColumn, Button, Pagination,
        Row, Col, MessageBox, Popover, Tag
    } from 'element-ui'
    let components = {};
    let array = [
        Table, TableColumn, Button, Pagination,
        Row, Col, Tag, Popover
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
                        this.$https.postWA('admin/tags/delete', {id : id}).then(
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
            handleCurrentChange (page) {
                this.getList({page : page});
            },
            getList (params = {}) {
                this.$https.postWA('admin/tags', {...params}).then(
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
            https.postWA('admin/tags').then(
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