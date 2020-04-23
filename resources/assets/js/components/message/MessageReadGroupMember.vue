<template>
    <div id="app" style="padding:20px;">
        <el-row>
            <el-form :inline="true" :model="search" class="demo-form-inline" size="small">
                <el-form-item>
                    <el-select v-model="search.contentType" style="width:100px">
                        <el-option label="手機號碼" value="1"></el-option>
                        <el-option label="姓名" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-input v-model="search.content" style="width:180px"/>
                </el-form-item>
                <el-form-item label="性別:">
                    <el-select v-model="search.sex" style="width:100px">
                        <el-option label="全部" value="-1"></el-option>
                        <el-option label="男" value="1"></el-option>
                        <el-option label="女" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="狀態:">
                    <el-select v-model="search.read" style="width:100px">
                        <el-option label="全部" value="-1"></el-option>
                        <el-option label="已讀" value="1"></el-option>
                        <el-option label="未讀" value="0"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="loadData(1, true)">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <el-row>
            <el-col :span="24" style="margin-top:20px;">
                <el-table :data="members" stripe style="width:100%" v-loading="loading" empty-text="目前沒有符合資料">
                    <el-table-column prop="item_index" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}</template>
                    </el-table-column>
                    <el-table-column prop="mobile" label="手機號碼">
                    </el-table-column>
                    <el-table-column prop="name" label="姓名">
                    </el-table-column>
                    <el-table-column prop="sex" label="性別">
                        <template slot-scope="scope">
                            {{ getSex(scope.row.sex) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="read" label="是否已讀">
                        <template slot-scope="scope" v-if="scope.row.read === '1'">
                            已讀
                        </template>
                    </el-table-column>
                    <el-table-column prop="read_time" label="已讀時間">
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="limitChange"
                            @current-change="loadData"
                            :current-page="search.page"
                            :page-size="search.limit"
                            layout="sizes, total, prev, pager, next"
                            :total="total"
                    >
                    </el-pagination>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools'
    export default {
        name: "message-read-group-member",
        data: function () {
            return {
                search: {
                    id: this.$route.params.msgId,
                    page: 1,
                    limit: 10,
                    contentType: '1',
                    content: '',
                    sex: '-1',
                    read: '-1',
                    groupId: this.$route.params.groupId
                },
                members: [],
                total: 1,
                isSearch: false,
                loading: true,
                dialog: new Tools.Dialog(this)
            }
        },
        created() {
            this.loadData();
        },
        methods: {
            limitChange(val) {
                this.search.limit = val;
                this.loadData();
            },

            loadData(page, isSearch) {
                if (isSearch) {
                    this.isSearch = true;
                }
                this.search.page = page ? page : 1;
                let search = JSON.parse(JSON.stringify(this.search));
                this.loading = true;
                axios.get('/message/send/getReadMembers' + Tools.BuildQueryString(search)).then((response) => {
                    this.loading = false;
                    this.total = response.data.response.count;
                    this.members = response.data.response.list;
                }).catch((error) => {
                    this.loading = false;
                    console.error(error)
                });
            },
            getSex(sex) {
                if(sex===1) {
                    return '男';
                } else if(sex===2) {
                    return '女';
                } else {
                    return '';
                }
            }
        }
    }
</script>

<style scoped>

</style>