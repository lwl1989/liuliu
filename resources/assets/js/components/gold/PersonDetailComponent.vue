<template>
    <div id="app">
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="personGoldsDetail" stripe v-loading='loading'>
                    <el-table-column prop="stage_id" label="期別">
                    </el-table-column>
                    <el-table-column prop="issue_time" label="發放日">
                    </el-table-column>
                    <el-table-column prop="expire_time" label="有效期限">
                    </el-table-column>
                    <el-table-column prop="total" label="金幣數">
                        <template slot-scope="scope">
                            {{ scope.row.total }}
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="recyclePersonGold(scope.row.total, scope.row.stage_id)">回收</a>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;" v-show="recycleDetail.length > 0">
                <el-table :data="recycleDetail" stripe v-loading='loading'>
                    <el-table-column prop="stage_id" label="期別">
                    </el-table-column>
                    <el-table-column prop="number" label="回收金幣數">
                    </el-table-column>
                    <el-table-column prop="remark" label="備註">
                    </el-table-column>
                    <el-table-column prop="admin_info" label="最後異動資訊">
                    </el-table-column>
                </el-table>
            </el-col>

            <el-dialog title="個人金幣回收" :visible.sync="recycleVisible" :label-width="formLabelWidth">
                <el-form ref="recycleForm">
                    <div style="margin-top: 10px;">
                        <el-form-item label="回收金幣數" :label-width="formLabelWidth">
                            <el-input placeholder="請輸入內容" v-model="recycleForm.receive_number" style="width: 90%;"></el-input>
                        </el-form-item>
                    </div>
                    <div style="margin-top: 10px;">
                        <el-form-item label="備註" :label-width="formLabelWidth">
                            <el-input placeholder="請輸入內容" v-model="recycleForm.receive_remark" style="width: 90%;"></el-input>
                        </el-form-item>
                    </div>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="recycleVisible = false">取消</el-button>
                    <el-button type="primary" @click="submitRecycle('確認推播')">確定</el-button>
                </div>
            </el-dialog>
        </el-row>
    </div>
</template>

<script>
    import NewDialog from '../../tools/element-ui-dialog'
    import Tools from '../../tools/vue-common-tools';

    export default {
        name: "PersonDetailComponent",
        data : function() {
            return {
                user_id : this.$route.params.id,
                personGoldsDetail : [],
                recycleDetail : [],
                loading : false,
                dialog:NewDialog(this),
                recycleVisible : false,
                formLabelWidth : '120px',
                recycleForm : {
                    receive_number : 0,
                    receive_remark : ''
                },
                recycle_total : 0,
                recycle_stage_id : 1,
                toolsdialog: new Tools.Dialog(this)
            }
        },

        created:function () {
            this.loadData(1);
        },
        mounted() {
            window.addEventListener('beforeunload', e => this.closeAdminDetailWindowFuc(e))
        },

        watch : {
            'recycleForm.receive_number' : function (value) {
                if (value && !value.match(/^[0-9]{1,30}$/)) {
                    this.dialog.openError(null, '僅可輸入數字');
                }

                if (parseInt(value) > this.recycle_total) {
                    this.dialog.openError(null, '金幣不足');
                }
            }
        },

        methods: {
            loadData : function(page) {
                this.loading = true;
                axios.get('/gold/recycle/goldStage?user_id=' + this.user_id)
                    .then((response) => {
                        this.loading = false;
                        this.personGoldsDetail = response.data.response;
                    });

                axios.get('/gold/recycle/recycleInfo?user_id=' + this.user_id)
                    .then((response) => {
                        this.loading = false;
                        this.recycleDetail = response.data.response;

                        if (this.recycleDetail.length > 0) {
                            this.recycleDetail.forEach((item, index) => {
                                this.recycleDetail[index]['admin_info'] = item.create_time + ' ' + (item.account?item.account:'');
                            });
                        }
                    })
            },

            recyclePersonGold : function (total, stage_id) {
                this.recycleForm.receive_number = 0;
                this.recycleForm.receive_remark = '';
                this.recycleVisible = true;
                this.recycle_total = total;
                this.recycle_stage_id = stage_id;
            },

            submitRecycle : function () {
                if (this.recycleForm.receive_number === 0 || this.recycleForm.receive_number === '') {
                    this.dialog.openError(null, '請填寫回收金幣數');
                    return false;
                }

                if (parseInt(this.recycleForm.receive_number) > parseInt(this.recycle_total)) {
                    this.dialog.openError(null, '金幣不足');
                    return false;
                }

                let queryString = '';

                if (this.recycleForm.receive_remark !== '') {
                    queryString = '?stage_id=' + this.recycle_stage_id + '&user_id=' + this.user_id + '&number='
                        + this.recycleForm.receive_number + '&remark=' + this.recycleForm.receive_remark;
                } else {
                    queryString = '?stage_id=' + this.recycle_stage_id + '&user_id=' + this.user_id + '&number='
                        + this.recycleForm.receive_number;
                }
                let h = this.$createElement;
                let that = this;

                this.toolsdialog.openSelfDialog((closeCallback) => {
                    axios.get('/gold/recycle/recyclePerson' + queryString).then((response) => {
                        if (response.data.response.row > 0) {
                            that.recycleVisible = false;
                            that.loadData(1);
                            that.closeAdminDetailWindowFuc();
                        }
                        closeCallback()
                    }).catch(() => {
                        that.loading = false;
                        closeCallback()
                    });
                }, h('p', null, [
                    h('span', null, '確定要回收嗎?')
                ]), '執行中...', '確定');

            },
            closeAdminDetailWindowFuc() {
                let userAgent = navigator.userAgent;
                if (userAgent.indexOf("Firefox") !== -1 || userAgent.indexOf("Chrome") !== -1) {
                    window.opener.location.reload(true);
                    // window.location.href="about:blank";
                    // window.close();
                } else {
                    window.opener.location.reload(true);
                    window.opener = null;
                    // window.open("", "_self");
                    // window.close();
                }

            },

        }
    }
</script>

<style scoped>

</style>