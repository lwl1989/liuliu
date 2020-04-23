<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div class="app-search-bg">
                </div>
                <div style="padding-top:5px;">
                    <el-button size="small" type="primary" class="el-icon-circle-plus" @click="addSendGold">新增發放事件</el-button>
                    <el-button size="small" type="primary" class="el-icon-circle-plus" @click="openGoldExternal">管理外部應用程式</el-button>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="send" stripe v-loading='loading' style="width: 100%">
                    <el-table-column prop="id" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label=發放事件>
                    </el-table-column>
                    <el-table-column prop="object_name" label=發放對象>
                    </el-table-column>
                    <el-table-column prop="department_name" label="舉辦單位">
                    </el-table-column>
                    <el-table-column prop="send_stage" label="發放期別(發放日/有效期限)">
                    </el-table-column>
                    <el-table-column prop="setting_gold" label="設定發放金幣">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;"  @click="preinstallGold(scope.row.id)">{{ scope.row.setting_gold }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="sent_gold" label="已發放金幣">
                    </el-table-column>
                    <el-table-column label="剩餘金幣">
                        <template slot-scope="scope">
                            {{scope.row.left_gold}}
                        </template>
                    </el-table-column>
                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <el-button size="small" type="warning" @click="recycleSend(scope.row.id, scope.row.left_gold)" v-show="scope.row.status == 1 && scope.row.left_gold > 0">回收</el-button>
                            <el-button size="small" type="success" v-show="scope.row.status == 2" style="margin-left: -5px;">已回收</el-button>
                            <el-button size="small" type="danger" @click="deleteSend(scope.row.id, scope.row.left_gold)" v-show="!scope.row.overdue">刪除</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="admin_info" label="最後異動資訊">
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

        <el-dialog title="金幣發放" :visible.sync="addSendFormVisible">
            <el-form :model="sendGoldForm" ref="sendGoldForm" :label-width="formLabelWidth">
                <el-form-item label="發放事件" prop="incident_type">
                    <el-select v-model="sendGoldForm.incident_type" placeholder="" @change="changeIncident" v-bind:disabled="edit_action">
                        <template  v-for="(index, item) in incident_sum">
                            <el-option :label="index" :value="item"></el-option>
                        </template>
                    </el-select>
                </el-form-item>

                <el-form-item label="發放對象" prop="send_object">
                    <el-select v-if="sendGoldForm.incident_type != 20" v-model="sendGoldForm.send_object" v-bind:disabled="edit_action">
                        <el-option label="臺東縣民" value="1"></el-option>
                    </el-select>
                    <template v-if="sendGoldForm.incident_type == 20">
                        <el-select v-model="sendGoldForm.open_id" v-bind:disabled="edit_action">
                            <el-option v-if="external.length === 0" label="尚未建立" value="0"></el-option>
                            <template v-for="item in external">
                                <el-option
                                        :label="item.open_name"
                                        :value="item.id"
                                ></el-option>
                            </template>
                        </el-select>
                    </template>
                </el-form-item>

                <el-form-item label="舉辦單位" prop="department_id">
                    <el-select v-model="sendGoldForm.department_id" @change="changeUnit" v-bind:disabled="edit_action">
                        <el-option key="0" value="0" label="請選擇"></el-option>
                        <template  v-for="(index, item) in unit_sum">
                            <el-option :label="index" :value="item" :key="index + 1"></el-option>
                        </template>
                    </el-select>
                </el-form-item>

                <el-form-item label="發放期別(發放日/有效期限)" prop="stage_id">
                    <el-select v-model="sendGoldForm.stage_id" placeholder="" @change="getRemainGold" v-bind:disabled="edit_action">
                        <el-option key="0" value="0" label="請選擇"></el-option>
                        <template  v-for="item in send_stage_sum">
                            <el-option  :key="item.id.toString()" :value="item.id.toString()" :label="item.id + '(' + item.issue_time+' ~ '+ item.expire_time + ')'">
                            </el-option>
                        </template>
                    </el-select>
                </el-form-item>

                <el-form-item label="設定發放金幣" :label-width="formLabelWidth" prop="setting_gold">
                    <el-col style="color:#409EFF;">
                        帳號目前可設定發放金幣為 {{remain_gold}}
                    </el-col>
                    <template v-if="sendGoldForm.incident_type != 12 && sendGoldForm.incident_type != 20">
                        <el-col class="line" :span="5" prop="fans_number">每個人發放</el-col>
                        <el-col :span="6"><el-input v-model="sendGoldForm.person_gold" auto-complete="off" v-bind:disabled="edit_action"></el-input></el-col>
                        <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                        <el-col :span="20" ></el-col>

                        <el-col class="line" :span="5" prop="fans_number">限制前</el-col>
                        <el-col :span="6"><el-input v-model="sendGoldForm.person_limit" v-on:change="getSum" auto-complete="off" v-bind:disabled="overdue"></el-input></el-col>
                        <el-col class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                        <el-col :span="20">總計 {{sum}} 枚</el-col>
                    </template>
                    <template v-else>
                        <el-col class="line" :span="5" :offset="0" prop="fans_number">總計</el-col>
                        <el-col :span="6">
                            <el-input-number v-model="sendGoldForm.person_gold" :min="sendGoldForm.sent_gold" :max="remain_gold+sendGoldForm.setting_gold" v-bind:disabled="overdue"></el-input-number>
                        </el-col>
                        <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                    </template>
                </el-form-item>
                <el-form-item label="已發放金幣" prop="sent_gold">
                    <el-col :span="15">
                        <el-input v-model="sendGoldForm.sent_gold" auto-complete="off" disabled></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="最後異動資訊" prop="admin_info">
                    <el-col :span="15">
                        <el-input v-model="sendGoldForm.admin_info" auto-complete="off" disabled></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="addSendFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitSendGold">儲 存</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "gold-send",
        data : function() {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 0,
                send: [],
                formLabelWidth: '220px',
                addGoldForm : {
                    issue_time : '',
                    expire_time : ''
                },
                sendGoldForm:{
                    id: 0,
                    incident_type : '1',
                    send_object : '1',
                    open_id: '0',
                    stage_id : '0',
                    setting_gold : 0,
                    sent_gold : 0,
                    person_limit : 0,
                    person_gold : 0,
                    admin_profile_name : '',
                    update_time : '',
                    admin_info : '',
                    department_id: '0',
                    person_limit_original : 0
                },
                addSendFormVisible : false,
                incident_sum : [],
                business_unit_sum : [],
                send_stage_sum : [],
                sum : 0,
                remain_gold : 0,
                first_id : 0,
                stage_id : 0,
                unit_id  : 0,
                unit_sum : this.getUnitSum(),
                external:[],
                loading : true,
                sum_original : 0,
                overdue : false,
                edit_action : false,
                dialog: new Tools.Dialog(this)
            }
        },

        created : function() {
            this.$nextTick(function() {
                this.getMaxPage();
                this.handleCurrentChange(1);
            })
        },

        watch : {
            'sendGoldForm.person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    this.sendGoldForm.person_gold = '';
                    Tools.Dialog(this).openWarning(null, '輸入框僅能輸入1-10碼的半形數字');
                    return false;
                }
            },

            'sendGoldForm.person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    this.sendGoldForm.person_limit = '';
                    Tools.Dialog(this).openWarning(null, '輸入框僅能輸入1-10碼的半形數字');
                    return false;
                }
            },

            'sendGoldForm.incident_type' : function(val) {
                if (parseInt(val) === 12 || parseInt(val) === 20) {
                    this.sendGoldForm.person_limit = 1;
                }

                this.sendGoldForm.send_object = '1';

                if (parseInt(val) === 20) {
                    this.sendGoldForm.send_object = '0';
                }
            },

            addSendFormVisible : function (val) {
                if (!val) {
                    this.sendGoldForm.id = 0;
                } else {
                    if (parseInt(this.sendGoldForm.incident_type) === 12 ||
                        parseInt(this.sendGoldForm.incident_type) === 20
                    ) {
                        this.sendGoldForm.person_limit = 1;
                    }
                }
            }
        },

        methods: {
            getUnitSum : function () {
                let that = this;
                axios.get('/department/getAllUnit')
                    .then(function (response) {
                        that.unit_sum = response.data.response;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            getExternal() {
                axios.get('/gold/external/select').then((res) => {
                    this.external = res.data.response;
                    
                    if (this.external.length > 0) {
                        this.sendGoldForm.open_id = this.external[0].id;
                    }
                }).catch(error => {
                    Tools.Dialog(this).openError(null, error.toString())
                });
            },

            submitSendGold () {
                if(!this.sendGoldForm.department_id||this.sendGoldForm.department_id=='0') {
                    Tools.Dialog(this).openWarning(null, '請選擇舉辦單位');
                    return
                }
                if(!this.sendGoldForm.send_object) {
                    Tools.Dialog(this).openWarning(null, '請選擇發放對象');
                    return
                }
                if(!this.sendGoldForm.stage_id||this.sendGoldForm.stage_id=='0') {
                    Tools.Dialog(this).openWarning(null, '請選擇發放期別');
                    return
                }
                let url = this.sendGoldForm.id > 0
                    ? '/gold/send/updatePreInstallGold?id='+this.sendGoldForm.id
                    : '/gold/send/createPreinstallGold';

                this.sendGoldForm.reserve_gold = this.remain_gold;

                if (this.sendGoldForm.id <= 0) {
                    let total = this.sendGoldForm.person_gold * this.sendGoldForm.person_limit;
                    if (this.remain_gold < total) {
                        Tools.Dialog(this).openWarning(null, '總發放金幣不得高於可設定發放金幣');
                        this.sendGoldForm.person_limit = this.sendGoldForm.person_gold = this.sum = 0;

                        return false;
                    } else if (parseInt(total) === 0) {
                        Tools.Dialog(this).openWarning(null, '總發放金幣不得高於可設定發放金幣');

                        return false;
                    }
                }

                const h = this.$createElement;
                let message = '';
                if (parseInt(this.sendGoldForm.incident_type) === 12) {
                    message = h('p', null, [
                        h('span', null, '總計發放 '),
                        h('span', { style: 'color: teal' }, this.sendGoldForm.person_gold * this.sendGoldForm.person_limit),
                        h('span', null, ' 枚')
                    ]);
                } else if(parseInt(this.sendGoldForm.incident_type) === 20) {
                    let externalName = '';
                    this.external.forEach((item) => {
                        if (item.id === this.sendGoldForm.open_id) {
                            externalName = item.open_name;
                        }
                    });

                    message = h('p', null, [
                        h('span', null, '發給 '),
                        h('span', { style: 'color: teal' }, externalName),
                        h('span', null, '，總計 '),
                        h('span', { style: 'color: teal' },this.sendGoldForm.person_gold * this.sendGoldForm.person_limit),
                        h('span', null, ' 枚金幣')
                    ]);
                } else {
                    message = h('p', null, [
                        h('span', null, '每人可得 '),
                        h('span', { style: 'color: teal' }, this.sendGoldForm.person_gold),
                        h('span', null, ' 枚;    '),
                        h('span', null, '限制前 '),
                        h('span', { style: 'color: teal' }, this.sendGoldForm.person_limit),
                        h('span', null, ' 位用戶')
                    ]);
                }

                Tools.Dialog(this).openSelfDialog((callback) => {
                    axios.post(url, this.sendGoldForm).then((response) => {
                        if (response.data.code > 0) {
                            callback();
                            this.openGoldWarning();

                            return;
                        }

                        callback();
                        this.$refs.sendGoldForm.resetFields();
                        this.addSendFormVisible = false;
                        this.openGoldSuccess();
                    }).catch(() => {
                        callback();
                        this.openGoldWarning();
                    });
                }, message,null,null,'請確認發放金幣');
            },

            getMaxPage : function() {
                let that = this;
                axios.get('/gold/send/count')
                        .then(function (response) {
                            that.total = response.data.response.count;
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
            },

            handleSizeChange : function (page) {
                this.pageSize = page;
                this.handleCurrentChange(1);
            },

            handleCurrentChange : function(currentPage) {
                let that = this;
                let url = '/gold/send/select?page='+currentPage+'&limit='+that.pageSize;

                that.loading = true;
                axios.get(url) .then(function (response) {
                    that.loading = false;
                    that.send = response.data.response.data;
                }).catch(function (error) {
                    that.loading = false;
                });
            },

            deleteSend : function (id, left_gold) {
                let that = this;
                axios.delete('/gold/send/delete', {data: JSON.stringify({id: id,left_gold : left_gold})})
                    .then(function (response) {
                        if (response.data.response.row > 0) {
                            that.openGoldSuccess();
                        }
                    });
            },

            recycleSend : function(id, left_gold){
                let h = this.$createElement;
                let that = this;
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.get('/gold/send/recycleGold?id='+id+'&left_gold='+left_gold)
                        .then(function (response) {
                            if (response.data.response.row > 0) {
                                that.openGoldSuccess();
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

            preinstallGold : function (id) {
                this.edit_action = true;

                this.getExternal();
                axios.get('/gold/send/getPreinstallGold?id=' + id).then((response) => {
                    this.sendGoldForm = response.data.response.data;
                    this.overdue = this.sendGoldForm.overdue;
                    this.stage_id = this.sendGoldForm.stage_id;
                    this.unit_id = this.sendGoldForm.department_id;
                    this.remain_gold = response.data.response.remain_gold;
                    this.incident_sum = response.data.response.incident_sum;
                    this.setStageGold(this.unit_id);
                    if (this.sendGoldForm.incident_type == 12 || this.sendGoldForm.incident_type == 20) {
                        this.sendGoldForm.person_limit = 1;
                        this.sendGoldForm.person_gold = this.sendGoldForm.setting_gold;
                    }

                    this.sendGoldForm.person_limit_original = this.sendGoldForm.person_limit;
                    this.sum = this.sendGoldForm.person_limit * this.sendGoldForm.person_gold;
                    this.sum_original = this.sum;
                    this.sendGoldForm.admin_info = this.sendGoldForm.update_time+'    '+(this.sendGoldForm.admin_profile_name?this.sendGoldForm.admin_profile_name:'');
                });

                this.addSendFormVisible = true;
            },

            openGoldSuccess : function () {
                this.$emit('success', () => {
                    this.getMaxPage();
                    this.handleCurrentChange(1);
                });
            },

            openGoldWarning : function () {
                this.$emit('warning', () => {
                    this.getMaxPage();
                    this.handleCurrentChange(1);
                });
            },

            addSendGold : function () {
                this.sum = 0;
                this.edit_action = false;
                this.overdue = false;
                this.remain_gold = 0;

                this.sendGoldForm = {
                    id: 0,
                    incident_type : '1',
                    send_object : '1',
                    open_id: '0',
                    stage_id : '0',
                    setting_gold : 0,
                    sent_gold : 0,
                    person_limit : 0,
                    person_gold : 0,
                    admin_profile_name : '',
                    update_time : '',
                    admin_info : '',
                    department_id: '0',
                    person_limit_original : 0
                };

                axios.get('/gold/send/getIndexInfo').then((response) => {
                    this.incident_sum = response.data.response.incident_sum;

                    let arr = Object.keys(this.incident_sum);
                    if (arr.length === 1) {
                        this.sendGoldForm.incident_type = '20';
                    }

                    this.setStageGold(0);
                });
                this.getExternal();
                this.addSendFormVisible = true;
            },

            dateChangeIssueTime : function (val) {
                this.addGoldForm.issue_time = val;
            },

            dateChangeValidityPeriod : function (val) {
                this.addGoldForm.expire_time = val;
            },

            getSum : function () {
                if (this.sendGoldForm.person_limit_original > this.sendGoldForm.person_limit) {
                    Tools.Dialog(this).openWarning(null, '領取名額只能增加不能減少');
                    this.sendGoldForm.person_limit = this.sendGoldForm.person_limit_original;
                }

                this.sum = this.sendGoldForm.person_limit * this.sendGoldForm.person_gold;

                if (this.sum - this.sum_original > this.remain_gold) {
                    Tools.Dialog(this).openWarning(null, '金幣不足');

                    this.sum = this.sum_original;
                    this.sendGoldForm.person_limit = this.sendGoldForm.person_limit_original;
                }
            },

            getRemainGold : function (val) {
                if (parseInt(val) === 0) {
                    this.remain_gold = 0;
                    return;
                }
                this.stage_id = val;

                axios.get('/gold/send/getRemainGold?stage_id='+this.stage_id+'&unit_id='+this.unit_id).then((response) => {
                    this.remain_gold = response.data.response.remain_gold;
                    if (this.remain_gold === undefined) {
                        this.remain_gold = 0;
                    }
                });
            },

            changeUnit : function (val) {
                this.unit_id = val;

                axios.get('/gold/send/getRemainGold?stage_id='+this.stage_id+'&unit_id='+this.unit_id).then((response) => {
                    this.remain_gold = response.data.response.remain_gold;
                    if (this.remain_gold === undefined) {
                        this.remain_gold = 0;
                    }
                });

                this.setStageGold(this.unit_id);
            },

            changeIncident : function (id) {
                if (parseInt(id) === 20) {
                    this.sendGoldForm.send_object = '0';
                } else {
                    this.sendGoldForm.send_object = '1';
                }
            },

            setStageGold : function (id) {
                if (id > 0) {
                    let action = 'add';
                    if (this.edit_action) {
                        action = 'edit';
                    }

                    axios.get('/gold/account/departmentStage?id=' + id + '&event_id=' + this.sendGoldForm.id + '&action=' + action).then((response) => {
                        this.send_stage_sum = response.data.response.list;

                        if (this.send_stage_sum.length == 0) {
                            this.stage_id = '0';
                            this.sendGoldForm.stage_id = '0';
                        }
                    });
                }
            },

            openGoldExternal() {
                this.$router.push({path: '/gold/external'})
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
</style>