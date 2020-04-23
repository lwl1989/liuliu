<template>
    <div id="app" style="padding:20px;">
        <el-row>
            <el-card class="box-card">
                <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{title}}</span></div>
                <div v-if="search.date_value && search.date_value.length == 2 && firstStatus === true && old_status === true">使用時間：{{search.date_value[0]}}~{{search.date_value[1]}}</div>
                <div v-if="search.date_value && search.date_value.length == 2 && firstStatus === false && old_status === false">使用時間：{{search.date_value[0].toLocaleDateString().replace(/\//g,'-')}}~{{search.date_value[1].toLocaleDateString().replace(/\//g,'-')}}</div>
                <div>使用人數：{{sum}}人</div>
            </el-card>

            <el-col style="margin-top:20px;">
                <el-form :inline="true" class="demo-form-inline">
                    <el-form-item>
                        <el-select v-model="search.type">
                            <el-option label="手機號碼" value="mobile"></el-option>
                            <el-option label="姓名" value="name"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-input v-model="search.keys" auto-complete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="性別:">
                        <el-select v-model="search.sex">
                            <el-option label="全部" value="-1"></el-option>
                            <el-option label="男" value="1"></el-option>
                            <el-option label="女" value="2"></el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-select v-model="date_type">
                            <el-option label="使用時間" value="-1"></el-option>
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
                        <el-button type="primary" icon="el-icon-search" @click="doSearch">查詢</el-button>
                        <el-button type="primary" icon="el-icon-download" @click="exportExcel">匯出</el-button>
                    </el-form-item>
                </el-form>
            </el-col>

            <el-col style="margin-top:20px;">
                <el-table :data="lists" stripe v-loading="loading">
                    <el-table-column prop="id" label="項次">
                        <template slot-scope="scope">
                            {{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="mobile" label="手機號碼"></el-table-column>
                    <el-table-column prop="name" label="姓名"></el-table-column>
                    <el-table-column prop="sex" label="性別">
                        <template slot-scope="scope">
                            {{ getSex(scope.row.sex) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" label="使用時間"></el-table-column>
                </el-table>
            </el-col>

            <el-col v-if="lists&&lists.length > 0">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="changeLimit"
                            @current-change="loadData"
                            :current-page="search.page"
                            :page-size="search.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools';
    export default {
        name: "preferential-users-component",
        data: function () {
            return {
                date_type: '-1',
                search: {
                    id : this.$route.params.id,
                    sex : '-1',
                    page : 1,
                    type : 'mobile',
                    limit : 10,
                    keys : '',
                    date_value: []
                },
                lists : [],
                count : {},
                total : 0,
                sum : 0,
                title : this.$route.query.title,
                timeRange : this.$route.query.time,
                timeRangeExport : [],
                loading : false,
                firstStatus : false,
                old_time : [],
                old_status : false
            }
        },
        methods : {
            doSearch : function () {
                this.loadData();
            },

            loadData : function (page) {
                this.search.page = page ? page : 1;
                let search = JSON.parse(JSON.stringify(this.search));
                search.date_value = [];
                let time = [];

                if (this.search.date_value && this.search.date_value.length > 0) {
                    if (this.search.date_value === this.old_time) {
                        this.old_status = true;
                    } else {
                        this.old_status = false;
                    }

                    let that = this;
                    this.search.date_value.forEach(function (value) {
                        if (that.old_status === false) {
                            time.push(value.getTime());
                        } else {
                            time.push(new Date(value).getTime());
                        }
                    })
                }

                if (this.timeRange.length > 0) {
                    this.firstStatus = true;
                    time = this.timeRange;
                    this.timeRangeExport = this.timeRange;

                    let time_show = [];
                    let strs = [];
                    strs = this.timeRange.split(",");
                    strs.forEach(function (val) {
                        let _time = new Date(parseInt(val));
                        let y = _time.getFullYear();
                        let m = _time.getMonth() + 1;
                        let d = _time.getDate();

                        if (m < 10) {
                            m = '0' + m;
                        }
                        if (d < 10) {
                            d = '0' + d;
                        }

                        let t = y + '-' + m + '-' + d;//+ ' 00:00:00'
                        time_show.push(t);
                    });

                    this.search.date_value = time_show;
                    this.old_time = time_show;
                    this.timeRange = [];
                } else {
                    this.firstStatus = false;
                }

                search.time = time;

                let queryString = Tools.BuildQueryString(search, this.search.page);
                console.log(queryString);
                axios.get('/preferential/getUserCount'+queryString).then((response) => {
                    this.total = response.data.response.count;
                }).catch((error) => {

                });

                this.changeLoadStatus(true);
                axios.get('/preferential/getUsers'+queryString).then((response) => {
                    this.changeLoadStatus(false);
                    this.lists = response.data.response.lists;
                    this.sum = response.data.response.sum;
                }).catch((error) => {
                    console.error(error);
                    this.changeLoadStatus(false);
                });
            },

            changeLimit : function (limit) {
                this.search.limit = limit;
                this.loadData();
            },

            changeLoadStatus : function (status) {
                this.loading = status;
            },

            reloadPage : function () {
                window.location.reload();
            },
            exportExcel: function () {
                let search = JSON.parse(JSON.stringify(this.search));
                search.date_value = [];
                let time = [];

                if (this.search.date_value && this.search.date_value.length > 0) {
                    if (this.search.date_value === this.old_time) {
                        this.old_status = true;
                    } else {
                        this.old_status = false;
                    }

                    let that = this;
                    this.search.date_value.forEach(function (value) {
                        if (that.old_status === false) {
                            time.push(value.getTime());
                        } else {
                            time.push(new Date(value).getTime());
                        }
                    })
                }

                if (this.timeRange.length > 0) {
                    this.firstStatus = true;
                    time = this.timeRange;
                    this.timeRangeExport = [];

                    let time_show = [];
                    let strs = [];
                    strs = this.timeRange.split(",");
                    strs.forEach(function (val) {
                        let _time = new Date(parseInt(val));
                        let y = _time.getFullYear();
                        let m = _time.getMonth() + 1;
                        let d = _time.getDate();

                        if (m < 10) {
                            m = '0' + m;
                        }
                        if (d < 10) {
                            d = '0' + d;
                        }

                        let t = y + '-' + m + '-' + d;//+ ' 00:00:00'
                        time_show.push(t);
                    });

                    this.search.date_value = time_show;
                    this.old_time = time_show;
                    this.timeRange = [];
                } else {
                    this.firstStatus = false;
                }

                search.time = time;
                search.title = this.title;
                let queryString = Tools.BuildQueryString(search, this.search.page);
                console.log(queryString);
                let url = '/preferential/exportUser' + queryString;
                window.location.href = url;
            },
            dateSelect() {
            },
            limitChange(val) {
                this.search.limit = val;
                this.loadData();
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
        },

        created : function () {
            this.loadData();
        }
    }
</script>

<style scoped>

</style>