<template>
    <el-row>
        <el-col>
            <el-table :data="lists" stripe width="100%">
                <el-table-column
                        label="序号" width="auto"
                >
                    <template scope="scope">{{scope.$index + (current_page -1)*per_page + 1}}</template>
                </el-table-column>

                <el-table-column
                        label="广告代码" width="auto"
                >
                    <template scope="scope">
                        <el-popover trigger="hover" placement="top">
                            <p>描述: {{ scope.row.description }}</p>
                            <div slot="reference" class="name-wrapper">
                                <span>{{ scope.row.code }}</span>
                            </div>
                        </el-popover>
                    </template>
                </el-table-column>
                <el-table-column
                        label="广告图片" width="auto"
                >
                    <template scope="scope">
                        <div v-html="scope.row.code"></div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="广告位置" width="auto"
                >
                    <template scope="scope">
                        <span>{{scope.row.position_desc}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        label="是否展示" width="auto"
                >
                    <template scope="scope">
                        <span>{{scope.row.display == 'yes' ? '是' : '否'}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        label="操作" fixed="right" align="center" width="auto"
                >
                    <template scope="scope">
                        <el-button
                                size="small"
                                @click="$router.push({name : 'advertDetail', params : {id : scope.row.id}})">编辑</el-button>
                        <el-button
                                size="small"
                                type="danger"
                                @click="handleDelete(scope.row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination  :page-size="parseInt(per_page)" :total="total" layout="total, prev, pager, next, jumper"
                            :current_page.sync="current_page"
                            @current-change="handleCurrentChange" v-if="total/per_page > 1" style="display: flex;margin-top:30px;">
            </el-pagination>
        </el-col>

    </el-row>
</template>
<script>
    import https from '../../libs/http'
    import {
        Loading, Message, Table, TableColumn, Button, Pagination,
        Row, Col, MessageBox, Popover
    } from 'element-ui'
    let components = {};
    let array = [
        Table, TableColumn, Button, Pagination,
        Row, Col, Popover
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
                current_page : 1
            }
        },
        methods : {
            handleDelete (id) {
                MessageBox.confirm (
                    '此操作会永久删除这条广告',
                    '警告',
                    {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }

                ).then(
                    () => {
                        let load = Loading.service({target : '#admin-content'});
                        this.$https.postWA('admin/adverts/delete', {id : id}).then(
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
                );
            },
            handleCurrentChange (page) {
                this.getList({page : page});
            },
            getList (params = {}) {
                this.$https.postWA('admin/adverts', {...params}).then(
                    response => {
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
                ).catch(
                    () => {Message({type : 'error', message : '未知错误'})}
                );
            }
        },
        beforeRouteEnter (to, from, next) {
            /*获取标签列表*/
            https.postWA('admin/adverts').then(
                response => {
                    let data = response.data;
                    if (data.status) {
                        next(vm => {
                            vm.lists = data.data.data;
                            vm.total = data.data.total;
                            vm.per_page = data.data.per_page;
                            vm.current_page = data.data.current_page;
                        });
                    } else {
                        next(false);
                        Message({type : 'error', message : data.info});
                    }
                }
            ).catch(
                () => {Message({type : 'error', message : '未知错误'})}
            );
        }
    }
</script>