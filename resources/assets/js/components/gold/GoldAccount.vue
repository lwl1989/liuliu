<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div class="app-search-bg">
                </div>
                <div style="padding-top:5px;">
                    <el-col :span="8">
                        <el-button-group>
                            <el-button type="primary" class="el-icon-circle-plus" @click="addGold">新增下期</el-button>
                        </el-button-group>
                    </el-col>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="account" stripe v-loading='loading' style="width: 100%">
                    <el-table-column prop="id" label="期別" :aria-disabled="true">
                    </el-table-column>
                    <el-table-column prop="issue_time" label=發放日>
                    </el-table-column>
                    <el-table-column prop="expire_time" label="有效期限">
                    </el-table-column>
                    <el-table-column prop="reserve_gold" label="存入金幣">
                        <template slot-scope="scope">
                            <!--<div v-if="scope.row.editTime">-->
                                <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="reserveGold(scope.row.id)">{{ scope.row.reserve_gold_sum }}</a>
                            <!--</div>-->
                            <!--<div v-else>-->
                                <!--{{ scope.row.reserve_gold_sum }}-->
                            <!--</div>-->
                        </template>
                    </el-table-column>
                    <el-table-column prop="preinstall_gold_sum" label="已設定金幣">
                    </el-table-column>
                    <el-table-column prop="recycle_person_sum" label="個人回收金幣">
                    </el-table-column>
                    <el-table-column prop="remain_gold" label="剩餘金幣">
                    </el-table-column>
                    <el-table-column prop="admin_info" label="最後異動資訊">
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <span v-show="scope.row.deleted" style="color:#67c23a;">已刪除</span>
                            <el-button size="small" type="danger" @click="deleteAccount(scope.row.id)" v-show="deleteStage(scope.row.issue_time, scope.row.preinstall_gold_sum, scope.row.deleted)">刪除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page="currentPage"
                            :page-size="pageSize"
                            layout="sizes, total, prev, pager, next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <el-dialog title="新增下期" :visible.sync="addAccountFormVisible">
            <el-form :model="addGoldForm" ref="addGoldForm">
                <el-form-item label="發放日" :label-width="formLabelWidth" prop="issue_time">
                    <el-col :span="11">
                        <el-date-picker type="datetime" placeholder="選擇日期" v-model="addGoldForm.issue_time" @change="dateChangeIssueTime" style="width: 100%;" v-bind:disabled="edit"></el-date-picker>
                    </el-col>
                </el-form-item>
                <el-form-item label="有效期限" :label-width="formLabelWidth" prop="expire_time">
                    <el-col :span="11">
                        <el-date-picker type="datetime" placeholder="選擇日期" v-model="addGoldForm.expire_time" @change="dateChangeValidityPeriod" style="width: 100%;" :disabled="addGoldForm.del==1"></el-date-picker>
                    </el-col>
                </el-form-item>
                <el-form-item label="本期總額度" :label-width="formLabelWidth" prop="issue_time">
                    <el-col :span="11">
                        <el-input v-model="addGoldForm.max_sum" auto-complete="off" :disabled="addGoldForm.del==1"></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="addAccountFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitGold">下一步</el-button>
            </div>
        </el-dialog>

        <el-dialog title="存入金幣" :visible.sync="reserveGoldFormVisible" width="50%">
            <el-form :model="addGoldForm" ref="addGoldForm">
                <el-form-item label="業務單位" :label-width="formLabelWidth">
                    <el-col :span="8">
                        存入金幣
                    </el-col>
                    <el-col class="line" :span="1" :offset="1"></el-col>
                    <el-col :span="8">
                        已設定金幣
                    </el-col>
                    <el-col class="line" :span="1" :offset="1"></el-col>
                    <el-col :span="8">
                        尚未設定金幣
                    </el-col>
                </el-form-item>

                <div v-for="(index, item) in unit_sum_gold" :id="item">
                    <el-form-item  :label="index.name" :label-width="formLabelWidth">
                        <el-col :span="6">
                            <el-input-number v-model="index.reserve_gold" auto-complete="off" :disabled="addGoldForm.del==1"></el-input-number><!--@change="(value) => changeNumber(value, index.id)" -->
                        </el-col>
                        <el-col class="line" :span="1" :offset="1">/</el-col>
                        <el-col :span="6">
                            <el-input v-model="index.setting_gold" auto-complete="off" disabled></el-input>
                        </el-col>
                        <el-col class="line" :span="1" :offset="1">/</el-col>
                        <el-col :span="6">
                            <el-input v-model="index.remain_gold" auto-complete="off" disabled></el-input>
                        </el-col>
                    </el-form-item>
                </div>
            </el-form>
            <el-form>
                <el-form-item :label-width="formLabelWidth">
                    <el-col :span="50" style="color: red" v-if="over_red">
                        已存入金幣總數 {{sum}}
                    </el-col>
                    <el-col :span="50" style="color: dodgerblue" v-else>
                        已存入金幣總數 {{sum}}
                    </el-col>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth">
                    <el-col :span="50" style="color: dodgerblue">
                        本期總額度 {{addGoldForm.max_sum}}
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="reserveGoldFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitReserveGold" v-if="addGoldForm.del==0">儲 存</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import NewDialog from '../../tools/element-ui-dialog'

    export default {
        name: "gold-send",

        data : function() {
            return {
                id : 0,
                currentPage: 1,
                pageSize: 10,
                total: 0,
                account: [],
                formLabelWidth: '130px',
                addGoldForm : {
                    issue_time : '',
                    expire_time : '',
                    reserve_gold : [],
                    max_sum : 0
                },
                reserveGoldForm:{
                    id: '',
                    name : '',
                    reserve_gold : 0,
                    preinstall_gold_sum : 0
                },
                addAccountFormVisible : false,
                reserveGoldFormVisible : false,
                unit_sum_gold : this.getUnitSum(),
                loading : true,
                dialog:NewDialog(this),
                edit : false,
                original_expire_time : '',
                sum : 0,
                over_red : false
            }
        },

        mounted: function() {
            this.$nextTick(function() {
                this.handleCurrentChange(1);
            })
        },

        watch : {
            'addGoldForm.expire_time' : function (value) {
                if (new Date(this.original_expire_time) > new Date(value)) {
                    this.dialog.openError(null, '有效期限只可延長不可縮短');
                    this.addGoldForm.expire_time = this.original_expire_time;
                    return false;
                } else if(this.addGoldForm.del!==1&&new Date()>new Date(value)){
                    this.dialog.openError(null, '有效期限不得為過去時間');
                    this.addGoldForm.expire_time = this.original_expire_time;
                    return false;
                }else {
                    this.addGoldForm.expire_time = value;
                }
            },

            'unit_sum_gold' : {
                handler: function(newVal, oldVal) {
                    this.over_red = false;
                    let sum = 0;
                    if(newVal){
                        newVal.forEach((item) => {
                            if (item.reserve_gold === undefined || item.reserve_gold === '') {
                                item.reserve_gold = 0;
                            }
                            sum += parseInt(item.reserve_gold);
                        });
                    }


                    this.sum = sum;
                    if (this.sum > parseInt(this.addGoldForm.max_sum)) {
                        this.over_red = true;
                        return false;
                    }
                },
                deep: true
            },

            'addGoldForm.max_sum' : function (value) {
                if (parseInt(value) < parseInt(this.sum)) {
                    this.over_red = true;
                    return false;
                } else {
                    this.over_red = false;
                }
            }
        },

        methods: {
            getUnitSum: function getUnitSum() {
                axios.get('/department/getSecondaryUnit').then((response) => {
                    this.unit_sum_gold = response.data.response;
                });
            },

            submitReserveGold () {
                let url = this.id > 0 ? '/gold/account/updateGold?id='+this.id : '/gold/account/createGold';
                this.addGoldForm.reserve_gold = this.unit_sum_gold;

                if (this.sum > this.addGoldForm.max_sum) {
                    this.dialog.openError(null, '存入金幣總數不得大於本期總額度');
                    return false;
                }

                this.dialog.openSelfDialog((callback) => {
                    axios.post(url, this.addGoldForm).then((response) => {
                        if(response.data.code > 0) {
                            this.openGoldWarning();
                            return;
                        }

                        callback();
                        this.$refs.addGoldForm.resetFields();
                        this.reserveGoldFormVisible = false;
                        this.addAccountFormVisible = false;
                        this.openGoldSuccess();
                    }).catch(() => {
                        this.openGoldWarning();
                    });
                }, '確定儲存本期金幣？');

                this.addGoldForm.reserve_gold.forEach((item) => {
                    if (item.reserve_gold < item.setting_gold) {
                        this.$alert(item.name +  ' 存入金幣不得小於設定發放金幣', '提示', {
                            confirmButtonText: '確定'
                        });
                        return false;
                    }
                });
            },

            getMaxPage : function() {
                axios.get('/gold/account/count').then((response) => {
                    this.total = response.data.response.count;
                });
            },

            handleSizeChange : function (page) {
                this.pageSize = page;
                this.handleCurrentChange(1);
            },

            handleCurrentChange : function(currentPage) {
                let that = this;
                let url = '/gold/account/select?page='+currentPage+'&limit='+that.pageSize;

                if(this.currentPage !== currentPage){
                    this.currentPage = currentPage;
                }
                that.loading = true;
                axios.get(url) .then(function (response) {
                    that.loading = false;
                    that.account = response.data.response.data;

                    // that.account.forEach(function (value, index) {
                    //     if ((new Date(value.expire_time)).getTime() > (new Date()).getTime()) {
                    //         value.editTime = true;
                    //     } else {
                    //         value.editTime = false;
                    //     }
                    // });
                }).catch(() => {
                    that.loading = false;
                });
                this.getMaxPage();
            },

            deleteAccount : function (id) {
                let that = this;
                axios.delete('/gold/account/delete',
                    {
                        data: JSON.stringify({id: id})
                    })
                    .then(function (response) {
                        if(response.data.code === 0) {
                            if(response.data.response.row != undefined && response.data.response.row.length > 0){
                                that.openGoldSuccess();
                            }
                            if(response.data.response.length === 0){
                                that.openGoldWarning();
                            }
                        }else{
                            that.openGoldWarning();
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            reserveGold : function (id) {
                let that = this;

                that.id = id;

                that.edit = true;

                axios.get('/gold/account/getReserveGold?id=' + id)
                        .then(function (response) {
                            let res = response.data.response.data;
                            let stage = response.data.response.stage;

                            that.addGoldForm.issue_time = stage.issue_time;
                            that.addGoldForm.expire_time = stage.expire_time;
                            that.addGoldForm.max_sum = stage.max_sum.toString();
                            that.addGoldForm.del = stage.deleted;
                            that.original_expire_time = that.addGoldForm.expire_time;
                            if ((new Date(stage.expire_time)).getTime() <= (new Date()).getTime()){
                                that.addGoldForm.del=1
                            }
                            if(that.unit_sum_gold&&Array.isArray(that.unit_sum_gold)){
                                that.unit_sum_gold.forEach(function (value, index) {
                                    if (res[value.id] !== undefined) {
                                        value.reserve_gold = res[value.id].reserve_gold;
                                        value.setting_gold = res[value.id].setting_gold;
                                        value.remain_gold = res[value.id].remain_gold;
                                    } else {
                                        value.reserve_gold = 0;
                                        value.setting_gold = 0;
                                        value.remain_gold = 0;
                                    }
                                });
                            }
                        })
                        .catch(function (error) {
                            console.log(error);
                        });

                this.addAccountFormVisible = true;
            },

            openGoldSuccess : function () {
                let that = this;
                that.$emit('success',function () {
                    that.handleCurrentChange(1);
                });
            },

            openGoldWarning : function () {
                let that = this;
                that.$emit('warning',function () {
                    that.handleCurrentChange(1);
                });
            },

            addGold : function () {
                this.addGoldForm = {
                    issue_time : '',
                    expire_time : '',
                    reserve_gold : [],
                    max_sum : 0,
                    del: 0
                };

                this.id = 0;
                this.edit = false;
                this.sum = 0;
                this.original_expire_time = '';
                this.unit_sum_gold = this.getUnitSum();

                this.addAccountFormVisible = true;
            },

            editGold : function() {
                this.addAccountFormVisible = true;
            },

            dateChangeIssueTime : function (val) {
                this.addGoldForm.issue_time = val;

                if (this.addGoldForm.del==0&&new Date(this.addGoldForm.issue_time) >= new Date(this.addGoldForm.expire_time)) {
                    this.dialog.openError(null, '發放日不得早於或等於有效期');
                    this.addGoldForm.issue_time = '';
                    return false;
                }
            },

            dateChangeValidityPeriod : function (val) {
                this.addGoldForm.expire_time = val;

                if (this.addGoldForm.del==0&&new Date(this.addGoldForm.issue_time) >= new Date(this.addGoldForm.expire_time)) {
                    this.dialog.openError(null, '有效時間不得早於或等於發放日');
                    this.addGoldForm.expire_time = '';
                    return false;
                }
            },

            submitGold : function () {
                if (this.addGoldForm.issue_time === '') {
                    this.dialog.openError(null, '發放日時間為必填欄目');
                    return false;
                }

                if (this.addGoldForm.expire_time === '') {
                    this.dialog.openError(null, '有效期時間為必填欄目');
                    return false;
                }

                if (this.addGoldForm.max_sum === '') {
                    this.dialog.openError(null, '本期總額度為必填欄位');
                    return false;
                }

                if (this.addGoldForm.max_sum <= 0) {
                    this.dialog.openError(null, '本期總額度為必填欄位');
                    return false;
                }
                
                if (this.addGoldForm.max_sum && !this.addGoldForm.max_sum.match(/^[0-9]{1,10}$/)) {
                    this.dialog.openError(null, '本期總額度僅能輸入 1-10 碼的半型數字');
                    return false;
                }

                this.reserveGoldFormVisible = true;
            },
            deleteStage : function (stage, preinstall_gold_sum, deleted_status) {
                //發放日在當前日期後可刪除
                if (deleted_status == 1) {
                    return false;
                } else {
                    if (new Date(stage) > new Date() && preinstall_gold_sum == 0) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
//            changeNumber : function (value, item) {
//                this.addGoldForm.reserve_gold.push({'unit_id' : item, 'reserve_gold' : value});
//            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
</style>