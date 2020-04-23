<template>
    <div id="app">
        <el-row>
            <el-form :inline="true" :model="searchTaxNoticeFrom" ref="searchTaxNoticeFrom" class="demo-form-inline">
                <el-form-item>
                    <el-date-picker
                        v-model="searchTaxNoticeFrom.time"
                        type="datetimerange"
                        range-separator="~"
                        start-placeholder="開始日期"
                        end-placeholder="結束日期"
                        align="right">
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="searchTaxNotice">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="notice" v-loading="loading" empty-text="目前沒有符合資料" :row-class-name="tableRowClassName">
                    <el-table-column prop="send_time" label="推播日期">
                    </el-table-column>
                    <el-table-column prop="name" label="車牌資料">
                    </el-table-column>
                    <el-table-column prop="import_num" label="匯入數量">
                    </el-table-column>
                    <el-table-column prop="valid_num" label="有效數量">
                    </el-table-column>
                    <el-table-column prop="notice_num" label="通知人數">
                    </el-table-column>
                    <el-table-column label="">
                        <template slot-scope="scope">
                            <el-button size="small" type="success" @click="downSendPerson(scope.row.id,
                            scope.row.send_time, scope.row.name, scope.row.import_num, scope.row.valid_num,
                             scope.row.notice_num, scope.row.admin_info)">下載</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination :page-sizes="[10, 20, 30, 50, 100]"
                        @size-change="handleSizeChange"
                        @current-change="loadData"
                        :current-page="currentPage"
                        :page-size="pageSize"
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
    import dialog from '../../tools/element-ui-dialog';

    export default {
        name: "TaxNoticeComponent",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                notice: [],
                searchTaxNoticeFrom: {
                    time: ''
                },
                dialog: dialog(this)
            }
        },

        mounted: function () {
            this.$nextTick(function () {
                this.loadData(1);
            })
        },
        
        methods: {
            tableRowClassName({row}) {
                let timeNow = new Date().getTime()
                let upTime  = new Date(row.online_time).getTime();
                let downTime = new Date(row.offline_time).getTime();
                if (row.status === 2 && timeNow > upTime && timeNow <= downTime) {
                    return 'warning-row';
                }

                return '';
            },
            
            getSearchUrl() {
                let time = [];
                if (this.searchTaxNoticeFrom.time && this.searchTaxNoticeFrom.time.length > 0) {
                    this.searchTaxNoticeFrom.time.forEach(function (value) {
                        time.push(value.getTime());
                    });
                }
                time = time.join(',');
                return 'time=' + time;
            },

            loadData(currentPage) {
                let that = this;
                that.loading = true;

                if (this.currentPage !== currentPage) {
                    this.currentPage = currentPage;
                }

                let url = '/tax/notice/select?page=' + currentPage + '&limit=' + that.pageSize + '&' + this.getSearchUrl();
                axios.get(url).then(function (response) {
                    that.notice = response.data.response.data;
                    that.total = response.data.response.count;
                    that.loading = false;
                }).catch(function (error) {
                    that.loading = false;
                });
            },

            searchTaxNotice() {
                this.loadData(1);
            },

            handleSizeChange(val) {
                this.pageSize = val;
                this.loadData(1);
            },

            downSendPerson(id, send_time, name, import_num, valid_num, notice_num, admin_info) {
                let url = '/tax/notice/down/' + id + '/' + send_time + '/' + name + '/' + import_num + '/' + valid_num + '/' + notice_num + '/' + admin_info;
                window.open(url);
            }
        },
    }
</script>

<style>
    .warning-row {
        background: oldlace !important;
    }
</style>

<style scoped>

</style>