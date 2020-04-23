<template>
    <div id="app" style="padding:20px;">
        <el-row>
            <el-card class="box-card">
                <el-col :span="8">
                    <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{title}}</span></div>
                    <div>發送時間：{{send_time}}</div>
                    <div>填寫人數：{{sum}}人</div>
                    <div>平均分數：{{score?score.score:0}}</div>
                    <div>
                        <el-button type="primary" icon="el-icon-refresh" @click="loadData()">重新載入</el-button>
                        <el-button type="primary" icon="el-icon-download" @click="exportExcel()">下載清冊</el-button>
                    </div>
                </el-col>
                <el-col :offset="1" :span="14" >
                    <charts ref="chart" :id="id" :option="option" ></charts>
                </el-col>
            </el-card>
            <el-col style="margin-top: 20px">
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
                    <el-form-item label="滿意度:">
                        <el-select v-model="search.satisfaction" style="width:100px">
                            <el-option label="全部" value="-1"></el-option>
                            <el-option label="非常滿意" value="1"></el-option>
                            <el-option label="滿意" value="2"></el-option>
                            <el-option label="尚可" value="3"></el-option>
                            <el-option label="不滿意" value="4"></el-option>
                            <el-option label="非常不滿意" value="5"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="loadData(1, true)">查詢</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
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
                    <el-table-column prop="read" label="滿意度">
                        <template slot-scope="scope">
                            {{ getSatisfaction(scope.row.score) }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="content" label="評價內容">
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
    import charts from '../ChartsComponent'
    import Vue from 'vue'

    export default {
        name: "message-satisfaction-detail",
        components: {
            charts
        },
        data: function () {
            return {
                id: 'score',
                option: {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: ''
                    },
                    tooltip: {
                        headerFormat: '{series.name}<br>',
                        pointFormat: '{point.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,//每個扇塊能否選中
                            cursor: 'pointer',//鼠標指針
                            depth: 35,//餅圖的厚度
                            dataLabels: {
                                enabled: true,//是否顯示餅圖的線形tip
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            }
                        }
                    },
                    series: [
                        {
                            type: 'pie',
                            name: '滿意度調查結果',
                            data: [
                                ['非常滿意', 1]
                            ]
                        }
                    ]
                },
                chartLoad: true,
                search: {
                    id: this.$route.params.msgId,
                    page: 1,
                    limit: 10,
                    contentType: '1',
                    content: '',
                    sex: '-1',
                    satisfaction: '-1',
                    surveyId: this.$route.params.surveyId
                },
                members: [],
                title: this.$route.query.title,
                send_time: this.$route.query.send_time,
                score: null,
                total: 1,
                sum : 0,
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
                axios.get('/message/send/getSatisfaction' + Tools.BuildQueryString(search)).then((response) => {
                    this.loading = false;
                    this.sum = response.data.response.sum;
                    this.total = response.data.response.count;
                    this.members = response.data.response.list;
                    this.score = response.data.response.score;
                    this.parseOption(this.score)
                }).catch((error) => {
                    this.loading = false;
                    console.error(error)
                });
            },
            parseOption(score) {
                let data = [];
                if(score.rate1>0){
                    data.push(['非常滿意', score.rate1])
                }
                if(score.rate2>0){
                    data.push(['滿意', score.rate2])
                }
                if (score.rate3>0) {
                    data.push(['尚可', score.rate3])
                }
                if (score.rate4>0) {
                    data.push(['不滿意', score.rate4])
                }
                if (score.rate5>0) {
                    data.push(['非常不滿意', score.rate5])
                }

                let serie = {
                    type: 'pie',
                    name: '滿意度調查結果',
                    data: data
                }
                Vue.set(this.option, 'series', [serie])
                this.$refs.chart.show();
            },
            exportExcel() {
                let search= {
                    id: this.search.id,
                    surveyId: this.search.surveyId,
                    title: this.title,
                    send_time: this.send_time
                }
                let url='/message/send/exportSatisfaction' + Tools.BuildQueryString(search)
                window.location.href = url;
            },
            getSex(sex) {
                if (sex === 1) {
                    return '男';
                } else if (sex === 2) {
                    return '女';
                } else {
                    return '';
                }
            },
            getSatisfaction(score) {
                if (score === 100) {
                    return '非常滿意';
                } else if (score === 75) {
                    return '滿意';
                } else if (score === 50) {
                    return '尚可';
                } else if (score === 25) {
                    return '不滿意';
                } else if (score === 0) {
                    return '非常不滿意';
                } else {
                    return '';
                }
            }
        }
    }
</script>

<style scoped>

</style>