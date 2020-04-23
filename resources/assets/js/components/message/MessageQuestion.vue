<template>
    <div id="app">
        <!-- 商品查詢列 -->
        <el-row>
            <el-form :inline="true"  :model="doSearchQuestionFrom"  ref="doSearchQuestionFrom" class="demo-form-inline">
                <el-form-item>
                    <el-select v-model="doSearchQuestionFrom.search_type" placeholder="活動名稱">
                        <el-option label="問卷名稱" value="1"></el-option>
                        <el-option label="業務單位" value="2"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-input v-model="doSearchQuestionFrom.search_type_key" @keyup.enter.native='doSearchQuestion' auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="doSearchQuestionFrom.type" placeholder="請選擇">
                        <el-option label="剩餘金幣" value="lost_gold" selected></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="doSearchQuestionFrom.operator" placeholder="請選擇">
                        <el-option label=">" value="1"></el-option>
                        <el-option label=">=" value="2"></el-option>
                        <el-option label="=" value="3"></el-option>
                        <el-option label="<=" value="4"></el-option>
                        <el-option label="<" value="5"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-input-number v-model="doSearchQuestionFrom.value" :min="0">
                    </el-input-number>
                </el-form-item>


                <el-form-item>
                    <el-select v-model="doSearchQuestionFrom.timeType">
                        <el-option label="問卷填寫日期" value="1"></el-option>
                        <el-option label="新增日期" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            v-model="doSearchQuestionFrom.time"
                            type="daterange"
                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                            align="right">
                    </el-date-picker>
                    <!--:picker-options="pickerOptions2"-->
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="doSearchQuestion">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <!--  商品按鈕列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-button-group>
                    <el-button type="primary" icon="el-icon-circle-plus" @click="addQuestion">新增</el-button>
                    <el-button type="primary" icon="el-icon-remove" @click="deleteQuestion">刪除</el-button>
                </el-button-group>
            </el-col>
        </el-row>

        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="question" stripe style="width: 100%" v-loading="loading" @selection-change="questionSelect">
                    <el-table-column type="selection" >
                    </el-table-column>
                    <el-table-column prop="index" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}</template>
                    </el-table-column>
                    <el-table-column prop="name" label="業務單位">
                    </el-table-column>
                    <el-table-column prop="title" label="問卷名稱">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="editQuestionDetail(scope.row.id)">{{ scope.row.title }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
					
                    <el-table-column label="問卷填寫日期" :formatter="questionTimeFormat">
                    </el-table-column>
					 <el-table-column prop="offline_registration" label="問卷回收數">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="offlineRegistration(scope.row.id)">{{ scope.row.count }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column label="剩餘問卷金幣">
                        <template slot-scope="scope">
                            <template v-if="scope.row.recycle_status === 1">
                                {{scope.row.remain_gold}}
                            </template>
                            <template v-else-if="scope.row.recycle_status === 2">
                                {{scope.row.recycle_gold}}
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                        <template v-if="scope.row.recycle_status == 2">
                            已回收
                        </template>
                        <template v-else-if="scope.row.status !== 3 && scope.row.setting_gold !== scope.row.sent_gold">
                            <el-button type="warning" @click="closeQuestion(scope.row.id, scope.row.setting_gold, scope.row.sent_gold)">回  收</el-button>
                        </template>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="handleSizeChange"
                            @current-change="loadData"
                            :current-page="doSearchQuestionFrom.page"
                            :page-size="doSearchQuestionFrom.limit"
                            layout="sizes, total, prev, pager, next"
                            :total="total"
                    >
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <question-detail ref="QuestionDetail" v-on:add="addQuestionDetail"></question-detail>
    </div>
</template>

<script>
    import QuestionDetail from './MessageQuetionDetailComponent';
    import dialog from '../../tools/element-ui-dialog';
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "message-question-component",
        data: function () {
            return {
                total: 0,
                loading: true,
                question: [],
                selectIds:[],
                doSearchQuestionFrom:{
                    search_type: '1',
                    search_type_key: '',
                    type:"lost_gold",
                    operator:"",
                    value: 0,
                    time:[
                        // new Date(new Date().getTime() - 3600 * 1000 * 24 * 30),
                        // new Date()
                    ],
                    timeType:"1",
                    page : 1,
                    limit : 10
                },
                dialog:dialog(this)
            }
        },
        components:{QuestionDetail},

        created : function() {
            this.loadData(1);
        },

        methods:{
            closeQuestion(id, setting_gold, sent_gold){
                let remain_gold = setting_gold - sent_gold;
                let that = this;
                let h = this.$createElement;
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.get('/question/recycleGold?id=' + id + '&remain_gold=' + remain_gold)
                        .then(function (response) {
                            if (response.data.code == 0) {
                                that.loadData(that.doSearchQuestionFrom.page);
                            }
                            closeCallback()
                        })
                        .catch(function (error) {
                            console.log(error);
                            closeCallback()
                        });
                }, h('p', null, [
                    h('span', null, '確定要回收嗎?')
                ]), '執行中...', '確定');

            },

            questionTimeFormat(item){
                let open = item.open_time;
                let close = item.close_time;
                return open.substr(0, 16) + ' - ' + close.substr(0, 16)
            },
			offlineRegistration : function (activityId) {
                Tools.OpenNewWindow('/#/message/questionnaire/'+activityId, '問卷回收列表', window.screen.height - 200, window.screen.width - 100);
            },
            addQuestionDetail(item){
                this.total += 1;
                if(Math.ceil(this.question.length / this.pageSize) === this.currentPage) {
                    this.question.push(item);
                }
            },

            editQuestionDetail(id){
                this.$refs.QuestionDetail.editQuestion(id);
            },

            loadData(page) {
                this.doSearchQuestionFrom.page = page ? page : 1;
                let queryArr = JSON.parse(JSON.stringify(this.doSearchQuestionFrom));
                queryArr.time = this.getTimestamp(this.doSearchQuestionFrom);
                let queryString = Tools.BuildQueryString(queryArr, page);

                axios.get('/question/count'+queryString).then((response) => {
                    this.total = response.data.response.count;
                });

                this.loading = true;
                axios.get('/question/select'+queryString).then((response) => {
                    this.question = response.data.response.list;
                    console.log(this.question);
                    this.loading = false;
                }).catch(() => {
                    this.loading = false;
                });
            },

            doDelQuestion(callback) {
                let that = this;
                this.loading = true;

                axios.delete('/question/delete', { data:{id: this.selectIds} }).then(function (response) {
                    console.log(response);
                    if (parseInt(response.data.code) === 0) {
                        let data = [];
                        that.question.forEach(function (item, index) {
                            if (that.selectIds.indexOf(item.id) === -1) {
                                data.push(item);
                            }
                        });
                        that.question = data;
                        that.dialog.openSuccess( function () {
                            that.loading = false;
                            that.loadData(that.doSearchQuestionFrom.page);
                        });
                    } else {
                        if (parseInt(response.data.code) < 0) {
                            that.dialog.openWarning( function () {
                                that.loading = false;
                            }, '問卷已綁定至推播設定，無法刪除');
                        } else {
                            that.dialog.openWarning( function () {
                                that.loading = false;
                            });
                        }

                    }
                    callback();
                }).catch(function (error) {
                    that.dialog.openWarning( function () {
                        that.loading = false;
                        callback();
                    }, error.toString());
                });
            },

            deleteQuestion() {
                if (this.selectIds.length === 0) {
                    this.dialog.openWarning( function () {
                    }, '請至少選擇一筆資料');
                    return;
                }
                let that = this;
                this.$msgbox({
                    title: '提示',
                    message: '確定刪除嗎?資料刪除後將無法復原。',
                    showCancelButton: true,
                    confirmButtonText: '確定',
                    cancelButtonText: '取消',

                    beforeClose: (action, instance, done) => {
                        let callback = function () {
                            instance.confirmButtonQuestion = false;
                            instance.confirmButtonText = '確定';
                            that.loading = false;
                            done();
                        };
                        if (action === 'confirm') {
                            that.doDelQuestion(callback);
                        }else{
                            callback();
                        }
                    }
                });
            },

            doSearchQuestion() {
                this.loadData(1);
            },

            handleSizeChange(val) {
                this.doSearchQuestionFrom.limit = val;
                this.loadData(1);
            },

            offSale() {
                this.sale(1);
            },

            onSale() {
                this.sale(2);
            },

            addQuestion() {
                this.$refs.QuestionDetail.editQuestion(0);
            },

            questionSelect(rows) {
                let ids = [];

                rows.forEach(function (item) {
                    ids.push(item.id);
                });
                this.selectIds = ids;
            },

            getTimestamp : function(queryArray) {
                let time = [];

                if (queryArray.time && queryArray.time.length > 0) {
                    queryArray.time.forEach(function (value) {
                        time.push(value.getTime());
                    });
                }

                return time.join(',');
            },
        },
    }
</script>

<style scoped>

</style>