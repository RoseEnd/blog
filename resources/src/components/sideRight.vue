<template>
    <div class="grid-content infoList">
        <el-input
                placeholder="憋说话，搜我"
                icon="search"
                v-model="options.query"
                :on-icon-click="handleIconClick">
        </el-input>
        <el-row>
            <el-col :xs="{span:0}" :sm="{span:0}" :md="{span:24}">
                <ul v-if="adverts.top && adverts.top.length > 0">
                    <li v-for="item in adverts.top" v-html="item.code" class="pointer"></li>
                </ul>
            </el-col>
        </el-row>
        <el-card class="box-card margin-top">
            <div slot="header" class="clearfix">
                <span style="line-height: 1.5rem;">标签分类</span>
            </div>
            <div class="list">
                <el-tag :type="type[index%type.length]"
                        :key="index" class="pointer"
                        v-for="(tag, index) in dynamicTags"
                        @click.native="getForTag(tag.id)"
                >
                    {{tag.name}}
                </el-tag>
            </div>
        </el-card>
        <el-row>
            <el-col :xs="{span:0}" :sm="{span:0}" :md="{span:24}">
                <ul v-if="adverts.middle && adverts.middle.length > 0">
                    <li v-for="item in adverts.middle" v-html="item.code" class="pointer"></li>
                </ul>
            </el-col>
        </el-row>
        <el-card class="box-card margin-top">
            <div slot="header" class="clearfix">
                <span style="line-height: 1.5rem;">热门文章</span>
            </div>
            <ul class="list">
                <li :key="index" @click="$emit('listenForHot', item.id)"
                    v-for="(item, index) in hotArticle"
                    class="item-li pointer">
                    <a :href="item.link_url" target="_blank">{{item.title}}</a>
                </li>
            </ul>
        </el-card>
        <el-card class="box-card margin-top">
            <div slot="header" class="clearfix">
                <span style="line-height: 1.5rem;">友情链接</span>
            </div>
            <ul class="list">
                <li :key="index"
                    v-for="(item, index) in friendly_link"
                    class="item-li">
                    <a :href="item.link_url" target="_blank">{{item.link_name}}</a>
                </li>
            </ul>
        </el-card>
        <el-row>
            <el-col :xs="{span:0}" :sm="{span:0}" :md="{span:24}">
                <ul v-if="adverts.bottom && adverts.bottom.length > 0">
                    <li v-for="item in adverts.bottom" v-html="item.code"></li>
                </ul>
            </el-col>
        </el-row>
    </div>
</template>
<script type="text/ecmascript-6">
    import '../assets/css/globble.css';
    import {Tag, Input, Card, Message, Loading, Row, Col} from 'element-ui';
    let components = {};
    let array = [Tag, Input, Card, Row, Col];
    array.forEach(item => {
        components[item.name] = item;
    });
    export default {
        name: 'sideRight',
        components : components,
        props: {
            init : {
                type : Object,
                default : () => {return {query : '', tag_id : 0}}
            }
        },
        computed : {
            friendly_link () {
                return this.$store.state.friendly_link;
            },
            hotArticle () {
                return this.$store.state.hotArticle;
            },
            dynamicTags () {
                return this.$store.state.dynamicTags;
            },
            adverts () {
                return this.$store.state.adverts;
            }
        },
        data() {
            return {
                type : ['primary', 'gray', 'success', 'warning', 'danger'],
                options : this.init
            }
        },
        methods: {
            handleIconClick() {
                this.$emit('sideListen', this.options);
            },
            /*标签搜索*/
            getForTag (tag_id) {
                this.options.tag_id = tag_id;
                this.$emit('sideListen', this.options);
            }
        }
    }
</script>
<style scoped>
    .item-li{
        border-bottom: 1px solid #f0f0f0;
    }
    .item-li:last-child{
        border-bottom-width: 0;
    }
    .item-li > a{
        color: darkturquoise;
        font-size: 0.7rem;
    }
    .pointer {
        cursor: pointer;
    }
</style>