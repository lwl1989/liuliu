<template>
    <div id="app">
        <el-row>
            <el-form :inline="true" :model="search" class="demo-form-inline" size="small">
                <el-form-item>
                    <el-button-group>
                        <el-button size="small" type="primary" icon="el-icon-circle-plus" @click="openWindowNew(0)">新增
                        </el-button>
                        <el-button size="small" type="primary" icon="el-icon-remove" @click="removeItem()">刪除
                        </el-button>
                    </el-button-group>
                    <el-button-group>
                        <el-button size="small" type="primary" icon="el-icon-download" @click="exportExcel()">匯出
                        </el-button>
                    </el-button-group>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="contentType" style="width:100px">
                        <el-option label="優惠內容" value="-1"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-input v-model="search.title" style="width:180px"/>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="search.status" style="width:100px">
                        <el-option label="狀態" value="-1"></el-option>
                        <el-option label="啓用" value="1"></el-option>
                        <el-option label="停用" value="0"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="search.date_type" style="width:100px">
                        <el-option label="最後異動時間" value="1"></el-option>
                        <el-option label="使用時間" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            style="width:230px"
                            :change="dateSelect"
                            v-model="search.date_value"
                            type="daterange"
                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                            align="right"
                    >
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="loadData(1, true)">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <el-row>
            <el-col :span="24" style="margin-top:20px;">
                <el-table :data="preferentials" stripe style="width:100%" v-loading="loading"
                          @selection-change="goodsSelect" empty-text="目前沒有符合資料">
                    <el-table-column type="selection"></el-table-column>
                    <el-table-column prop="item_index" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}</template>
                    </el-table-column>
                    <el-table-column prop="item_name" label="優惠內容">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;"
                               @click="openWindowNew(scope.row.id)">
                                {{scope.row.title}}
                            </a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="狀態">
                        <template slot-scope="scope">
                                <span v-if="scope.row.status == 1" style="color:#F56C6C">
                                    啓用
                                </span>
                            <span v-else>
                                    停用
                                </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="" label="使用人數">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;"
                               @click="openWindowUsers(scope.row)">
                                {{scope.row.count}}
                            </a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="online_time" label="啓用時間">
                        <template slot-scope="scope">
                            <span v-if="scope.row.resume_time == null">-</span>
                            <span v-else>{{scope.row.resume_time}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="offline_time" label="最後異動資訊">
                        <template slot-scope="scope">
                            <span v-if="scope.row.update_time == null">-</span>
                            <span v-else>{{scope.row.update_time}} {{scope.row.updater}}</span>
                        </template>
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
        <preferential-detail-component ref="preferDetail" v-on:add="addPreferential"
                                       v-on:edit="loadData(search.page,isSearch)"></preferential-detail-component>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools'
    import ElRow from "element-ui/packages/row/src/row";
    import PreferentialDetailComponent from "./PreferentialDetailComponent";

    export default {
        components: {
            PreferentialDetailComponent,
            ElRow
        },
        name: "PreferentialList",
        data: function () {
            return {
                total: 1,
                search: {
                    page: 1,
                    limit: 10,
                    title: '',
                    status: '-1',
                    date_type: '1',
                    date_value: []
                },
                contentType: '-1',
                isSearch: false,
                loading: true,
                preferentials: [],
                selectIds: [],
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
                search.date_value = '';
                let time = [];
                if (this.search.date_value&&this.search.date_value.length > 0) {
                    this.search.date_value.forEach(function (value) {
                        time.push(value.getTime());
                    })
                }
                search.time = time;
                this.loading = true;
                axios.get('/preferential/select' + Tools.BuildQueryString(search)).then((response) => {
                    this.loading = false;
                    this.total = response.data.response.count;
                    this.preferentials = response.data.response.list;
                }).catch((error) => {
                    this.loading = false;
                    console.error(error)
                });
            },
            openWindowNew(id) {
                this.$refs.preferDetail.editPrefer(id);
            },
            removeItem() {
                if (this.selectIds.length === 0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }
                let h = this.$createElement;
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.post('/preferential/delete', {id: this.selectIds}).then((response) => {
                        if (response.data.code > 0) {
                            return this.dialog.openError(closeCallback, '操作失敗！');
                        }

                        closeCallback();

                        this.dialog.openSuccess(() => {
                            this.loadData()
                        });
                    }).catch((error) => {
                        closeCallback();

                        this.dialog.openWarning(() => {
                            this.loadData();
                        }, error.toString());
                    });
                }, h('p', null, [
                    h('span', null, '確定刪除？')
                ]), '執行中...', '確定');
            },
            exportExcel() {
                let search = JSON.parse(JSON.stringify(this.search));
                search.date_value = '';
                let time = [];
                
                if (this.search.date_value !== null && this.search.date_value.length > 0) {
                    this.search.date_value.forEach(function (value) {
                        time.push(value.getTime());
                    })
                }
                search.time = time;
                let url = '/preferential/exportList' + Tools.BuildQueryString(search);
                window.location.href = url;
            },
            dateSelect() {
            },
            goodsSelect(rows) {
                let ids = [];
                rows.forEach(function (item) {
                    ids.push(item.id);
                });
                this.selectIds = ids;
            },
            addPreferential(item) {
                this.total += 1;
                if (Math.ceil(this.preferentials.length / this.pageSize) === this.currentPage) {
                    this.preferentials.push(item);
                }
            },
            openWindowUsers(obj) {
                let time = [];
                if (this.search.date_value&&this.search.date_value.length > 0) {
                    this.search.date_value.forEach(function (value) {
                        time.push(value.getTime());
                    })
                }
                console.log(time);
                Tools.OpenNewWindow(
                    "/#/goods/preferentialUsers/" + obj.id + "?title=" + obj.title + '&time=' + time,
                    "數位縣民優惠使用記錄",
                    800,
                    1024
                );
            }
        }
    }
</script>

<style scoped>

</style>