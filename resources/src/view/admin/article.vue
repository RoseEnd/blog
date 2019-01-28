<template>
    <div>
        <el-table :data="lists" stripe width="100%" :row-class-name="className">
            <el-table-column
                    label="序号" width="70px"
            >
                <template scope="scope">{{scope.$index + (current_page -1)*per_page + 1}}</template>
            </el-table-column>
            <el-table-column v-for="(item, index) in show_data" :key="index"
                             :prop="item.prop" align="center" height="auto" :sortable="item.prop=='updated_at'"
                             :label="item.label">
            </el-table-column>
            <el-table-column
                    label="操作" fixed="right" align="center" width="auto"
            >
                <template scope="scope">
                    <el-button
                            size="small"
                            @click="$router.push({name : 'articleDetail', params : {id : scope.row.id}})">编辑</el-button>
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
        MessageBox
    } from 'element-ui'
    let components = {};
    let array = [Table, TableColumn, Button, Pagination];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        components : components,
        data () {
            return {
                lists : [],
                total : 0,
                per_page : 1,
                current_page : 1,
                /*要显示的表格中的数据*/
                show_data : [
                    {label : '标题', prop : 'title'},
                    {label : '是否置顶', prop : 'pull_top_desc'},
                    {label : '文章模式', prop : 'status_desc'},
                    {label : '更新时间', prop : 'updated_at'}
                ],
                dialogVisible : false
            }
        },
        computed : {

        },
        methods : {
            className (row, index) {
                if (row.status == 'private' || row.pull_top == 'no') {
                    return 'info-row'
                }
            },
            handleDelete (id) {
                MessageBox.confirm (
                    '此操作会永久删除这篇文章',
                    '警告',
                    {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }

                ).then(
                    () => {
                        let load = Loading.service({target : '#admin-content'});
                        this.$https.postWA('admin/article/delete', {id : id}).then(
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
                ).catch(()=>{});
            },
            handleCurrentChange (page) {
                this.getList({page : page});
            },
            getList (params) {
                let load = Loading.service({target : '#admin-content'});
                this.$https.postWA('admin/article', {...params}).then(
                    response => {
                        load.close();
                        let data = response.data;
                        this.lists = data.data;
                        this.total = data.total;
                        this.per_page = data.per_page;
                        this.current_page = data.current_page;
                    }
                ).catch(
                    () => {
                        load.close();
                    }
                );
            },

        },
        beforeRouteEnter (to, from, next) {
            let load = Loading.service({target : '#admin-content'});
            https.postWA('admin/article').then(
                response => {
                    load.close();
                    let data = response.data;
                    next(vm => {
                        vm.lists = data.data;
                        vm.total = data.total;
                        vm.per_page = data.per_page;
                    });
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