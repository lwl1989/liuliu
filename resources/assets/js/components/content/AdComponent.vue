<template>
    <div id="app">
        <!-- 商品查詢列 -->
        <el-row>
            <el-form :inline="true" :model="searchAdFrom" ref="searchAdFrom" class="demo-form-inline">
                <el-form-item>
                    <el-select v-model="searchAdFrom.profile" placeholder="名稱">
                        <el-option label="名稱" value="title"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-input v-model="searchAdFrom.profileValue" @keyup.enter.native='searchAd' auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="searchAdFrom.timeType">
                        <el-option label="新增時間" value="create_time"></el-option>
                        <el-option label="最後異動時間" value="update_time"></el-option>
                        <el-option label="上架時間（起）" value="online_time"></el-option>
                        <el-option label="上架時間（迄）" value="offline_time"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            v-model="searchAdFrom.time"
                            type="datetimerange"
                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                            align="right">
                    </el-date-picker>
                    <!--:picker-options="pickerOptions2"-->
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="searchAd">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <!--  商品按鈕列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-button type="primary" icon="el-icon-circle-plus" @click="addAd">新增</el-button>
                <el-button type="primary" icon="el-icon-remove" @click="deleteAd">刪除</el-button>
                <el-button type="primary" icon="el-icon-goods" @click="onSale">上架</el-button>
                <el-button type="primary" icon="el-icon-sold-out" @click="offSale">下架</el-button>
            </el-col>
            <el-col :span="24">
                總筆數:{{ total }}
            </el-col>
        </el-row>
        <!--  商品Table列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="ad" v-loading="loading" empty-text="目前沒有符合資料" @selection-change="adSelect" :row-class-name="tableRowClassName">
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column label="項次" prop="id"
                            type="index"
                            width="50">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="title" label="名稱">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;"
                               @click="editAdDetail(scope.row.id)">{{ scope.row.title }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="狀態" :formatter="adStatusFormat">
                    </el-table-column>
                    <el-table-column prop="online_time" label="上架時間（起）">
                    </el-table-column>
                    <el-table-column prop="offline_time" label="上架時間（迄）">
                    </el-table-column>
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間">
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination :page-sizes="[10, 20, 30, 50, 100]" @size-change="handleSizeChange"
                                   @current-change="handleAdCurrentChange" :current-page="currentPage"
                                   :page-size="pageSize" layout="sizes, total, prev, pager, next" :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <ad-detail ref="AdDetail" v-on:add="addAdDetail"></ad-detail>
    </div>
</template>

<script>
    import AdDetail from './AdDetailComponent';
    import dialog from '../../tools/element-ui-dialog'

    export default {
        name: "ad-component",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                ad: [],
                selectIds: [],
                searchAdFrom: {
                    profile: "title",
                    profileValue: "",

                    time: [
                        new Date(new Date().getTime() - 3600 * 1000 * 24 * 90),
                        new Date()
                    ],
                    timeType: "update_time"
                },
                dialog: dialog(this)
            }
        },
        components: {AdDetail},
        mounted: function () {
            this.$nextTick(function () {
                this.handleAdCurrentChange(1);
            })
        },
        methods: {
            tableRowClassName({row}) {
                let timeNow = new Date().getTime()
                let upTime = new Date(row.online_time).getTime();
                let downTime = new Date(row.offline_time).getTime();
                if (row.status===2&&timeNow > upTime && timeNow <= downTime) {
                    return 'warning-row';
                }

                return '';
            },

            addAdDetail(item) {
                this.total += 1;
                if (Math.ceil(this.ad.length / this.pageSize) === this.currentPage) {
                    this.ad.push(item);
                }
            },
            getAdNowSearchUrl() {
                let time = [];
                if (this.searchAdFrom.time&&this.searchAdFrom.time.length > 0) {
                    this.searchAdFrom.time.forEach(function (value) {
                        time.push(value.getTime());
                    });
                }
                time = time.join(',');
                return this.searchAdFrom.profile + '=' + this.searchAdFrom.profileValue + '&' + this.searchAdFrom.timeType + '=' + time;
            },
            editAdDetail(id) {
                this.$refs.AdDetail.editAd(id);
            },
            getAdMaxPage() {
                let that = this;
                axios.get('/content/ad/count?' + this.getAdNowSearchUrl())
                    .then(function (response) {
                        that.total = response.data.response.count;
                    })
                    .catch(function (error) {
                        that.dialog.openWarning(function () {
                        }, error.toString());
                    });
            },
            handleAdCurrentChange(currentPage) {
                if(this.currentPage!==currentPage){
                    this.currentPage = currentPage;
                }

                let that = this;
                let url = '/content/ad/select?page=' + currentPage + '&limit=' + that.pageSize + '&' + this.getAdNowSearchUrl();

                that.loading = true;
                axios.get(url).then(function (response) {
                    that.ad = response.data.response.list;
                    that.loading = false;
                }).catch(function (error) {
                    that.loading = false;
                });
                this.getAdMaxPage();
            },

            sale(status) {
                if (this.selectIds.length === 0) {
                    this.dialog.openWarning(null, '請至少選擇一筆資料');
                    return;
                }

                let url = '/content/ad/on';
                let msg = '確定上架？該資料將顯示於手機端。';
                if (status !== 2) {
                    status = 1;
                    url = '/content/ad/off';

                    msg = '確定下架？該資料將於手機端隱藏。';
                } else {
                    status = 2;
                }


                let that = this;
                this.dialog.openSelfDialog(function (callback) {
                    that.loading = true;
                    axios.post(url, {ids: that.selectIds}).then(function (response) {
                        if (parseInt(response.data.code) === 0) {
                            let changeOff = [];
                            that.ad.forEach(function (item) {
                                if (that.selectIds.indexOf(item.id) >= 0) {
                                    if (status === 2) {
                                        let off = new Date(item.offline_time).getTime();
                                        let on = new Date(response.data.response.online_time).getTime();
                                        if (off <= on) {
                                            item.offline_time = response.data.response.offline_time;
                                            changeOff.push(item.title);
                                        }
                                        item.online_time = response.data.response.online_time;
                                    }
                                    if (status === 1) {
                                        item.offline_time = response.data.response.offline_time;
                                    }
                                    item.status = status;
                                }
                            });
                            if (changeOff.length > 0) {
                                callback();

                                that.dialog.openSuccess(function () {
                                    that.loading = false;
                                    that.handleAdCurrentChange(1);
                                }, '名稱爲： ' + changeOff.join(',') + ' 結束時間順延一週，如要修改，請進入詳情頁修改');
                                return;
                            }

                            that.dialog.openSuccess(function () {
                                that.loading = false;
                                window.location.reload();
                            });
                        } else {
                            that.dialog.openWarning(function () {
                                that.loading = false;
                            });
                        }

                        callback();
                    }).catch(function (error) {
                        that.dialog.openWarning(function () {
                            that.loading = false;
                        }, error.toString());
                    });
                }, msg);
            },

            deleteAd() {
                if (this.selectIds.length === 0) {
                    this.dialog.openWarning(null, '請至少選擇一筆資料');
                    return;
                }

                let that = this;
                this.dialog.openDelDialog(function (callback) {
                    that.loading = true;
                    axios.delete('/content/ad/delete', {data:{ id: this.selectIds}}).then(function (response) {
                        if (parseInt(response.data.code) === 0) {
                            let ad = [];
                            that.ad.forEach(function (item) {
                                if (that.selectIds.indexOf(item.id) === -1) {
                                    ad.push(item);
                                }
                            });
                            that.ad = ad;
                            that.dialog.openSuccess(function () {
                                that.loading = false;
                                that.handleAdCurrentChange(that.currentPage);
                            });
                        } else {
                            that.dialog.openWarning(function () {
                                that.loading = false;
                            });
                        }
                        callback();
                    }).catch(function (error) {
                        that.dialog.openWarning(function () {
                            that.loading = false;
                            callback();
                        }, error.toString());
                    });
                }.bind(this));
            },

            adStatusFormat(item) {
                let time = new Date().getTime();
                let upTime = new Date(item.online_time).getTime();
                let downTime = new Date(item.offline_time).getTime();
                if (item.status===2&&time > upTime && time <= downTime) {
                    return '上架中';
                } else {
                    return '已下架';
                }
            },

            searchAd() {
                this.handleAdCurrentChange(1);
            },

            handleSizeChange(val) {
                this.pageSize = val;
                this.handleAdCurrentChange(1);
            },

            offSale() {
                this.sale(1);
            },

            onSale() {
                this.sale(2);
            },

            addAd() {
                this.$refs.AdDetail.editAd(0);
            },

            adSelect(rows) {
                let ids = [];

                rows.forEach(function (item) {
                    ids.push(item.id);
                });
                this.selectIds = ids;
            }
        },
    }
</script>

<style>
    .warning-row {
        background: oldlace !important;
    }
</style>