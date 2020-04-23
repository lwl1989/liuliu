<template>
    <div id="app" v-loading="loading" style="margin-top:10px;">
        <el-form :model="usersForm" :rules="rules" ref="usersForm" :label-width="formLabelWidth" size="medium">
            <el-form-item label="會員編號" prop="uid">
                    <el-input v-model="usersForm.uid" disabled></el-input>
            </el-form-item>

            <el-form-item label="手機號碼" prop="mobile">
                <el-input v-model="usersForm.showMobile" disabled></el-input>
                <el-button v-if='usersForm.acl' size='mini' type="primary" @click="editMobileShow = true">更改</el-button>
            </el-form-item>

            <el-form-item label="數位縣民" prop="digitalization">
                <el-input v-model="usersForm.digitalization" disabled></el-input>
            </el-form-item>

            <el-form-item label="身份證字號" prop="id_number">
                <el-input v-model="usersForm.id_number" disabled></el-input>
                <el-button v-if='usersForm.acl && usersForm.id_number' size='mini' type="primary" @click="decode('id_number')">解碼</el-button>
            </el-form-item>

            <el-form-item label="居留證號" prop="id_number">
                <el-input v-model="usersForm.residence" disabled></el-input>
                <el-button v-if='usersForm.acl && usersForm.residence' size='mini' type="primary" @click="decode('residence')">解碼</el-button>
            </el-form-item>

            <el-form-item label="護照證號" prop="id_number">
                <el-input v-model="usersForm.passport" disabled></el-input>
                <el-button v-if='usersForm.acl && usersForm.passport' size='mini' type="primary" @click="decode('passport')">解碼</el-button>
            </el-form-item>

            <el-form-item label="姓名" prop="name">
                <el-input v-model="usersForm.name" disabled></el-input>
            </el-form-item>

            <el-form-item label="性別" prop="name">
                <el-input v-model="usersForm.sex" disabled></el-input>
            </el-form-item>

            <el-form-item label="國籍" prop="nationality">
                <el-input v-model="usersForm.nationality" disabled></el-input>
            </el-form-item>

            <el-form-item label="電子郵件" prop="email">
                <el-input v-model="usersForm.email" disabled></el-input>
            </el-form-item>

            <el-form-item label="通訊地址" prop="address">
                <el-input v-model="usersForm.address" disabled></el-input>
            </el-form-item>

            <el-form-item label="行動裝置類別" prop="device_name">
                <el-input v-model="usersForm.device_name" disabled></el-input>
            </el-form-item>

            <el-form-item label="車號" prop="car_card">
                <el-input v-model="usersForm.car_card" disabled></el-input>
            </el-form-item>

            <el-form-item label="註冊座標" prop="location">
                <el-input v-model="usersForm.location" auto-complete="off" disabled></el-input>
            </el-form-item>

            <el-form-item label="新增時間" prop="disability_card">
                <el-input v-model="usersForm.create_time" disabled></el-input>
            </el-form-item>

            <el-form-item label="最後操作時間" prop="op_last_time">
                <el-input v-model="usersForm.op_last_time" auto-complete="off" disabled></el-input>
            </el-form-item>

            <el-form-item label="最後異動資訊" prop="last_update_time">
                <el-input v-model="usersForm.last_update_time" auto-complete="off" disabled></el-input>
            </el-form-item>

            <el-form-item label="最後異動紀錄" prop="last_update_log">
                    <el-input v-model="usersForm.last_update_log" auto-complete="off" disabled></el-input>
            </el-form-item>
        </el-form>

        <el-dialog :visible.sync="editMobileShow" :close-on-click-modal="false" :show-close="false">
            <el-form ref='editUserForm' :model="editUser" :rules="editUserRules">
                <el-form-item label="更改手機號碼" prop="mobile">
                    <el-input v-model="editUser.mobile"></el-input>
                </el-form-item>
                <el-form-item label="說明">
                    <el-input  v-model="editUser.content" type="textarea" :autosize="{ minRows: 3, maxRows: 4}" maxlength="30"></el-input>
                    <span style="color:#F56C6C">長度限制30個字數</span>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="editMobileShow = false">取 消</el-button>
                <el-button type="primary" @click="editMobile">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import { UsersRule } from '../../tools/element-ui-validate';
    import Dialog from '../../tools/element-ui-dialog'

    export default {
        name: "usersDetailComponent",

        data : function() {
            return {
                id: this.$route.params.id,
                formLabelWidth: '180px',
                loading : true,
                usersForm:{
                    mobile:'',
                    id_number: '',
                    device_uuid : '',
                    acl: false
                },
                editUser:{
                    mobile : '',
                    content : ''
                },
                rules: UsersRule,
                editMobileShow: false,
                isSubmit : false,
                editUserRules : {
                    mobile : [
                        {
                            required: true,
                            validator: (rule, value, callback)=>{
                                let val = value.toString();

                                if (!val.match(/^((8869[0-9]{8})|(09[0-9]{8})|(9[0-9]{8}))$/)) {
                                    return callback('手機號碼格式錯誤');
                                }

                                callback();
                            }
                        }
                    ],
                }
            }
        },
        
        watch : {
            editMobileShow : function (val) {
                if (val === false) {
                    this.editUser = {
                        mobile : '',
                        content : ''
                    }
                }
            }
        },
        
        created:function () {
            this.handleCurrentChange(1);
        },

        methods: {
            handleCurrentChange : function() {
                let that = this;
                this.loading = true;
                axios.get('/users/get?id='+this.id).then(function (response) {
                    that.loading = false;
                    if (response.data.code === 0) {
                        that.usersForm = response.data.response.data;
                        that.usersForm.showMobile = that.usersForm.code+' '+that.usersForm.mobile;
                    } else {
                        that.closeWindow('獲取臺東縣民資料失敗，是否關閉頁面？');
                    }
                }).catch(function (error) {
                    that.loading = false;
                    that.closeWindow('獲取商品資料失敗，是否關閉頁面？錯誤訊息：'+error.toString());
                });
            },

            editMobile : function () {
                if (this.isSubmit) {
                    return;
                }

                this.$refs.editUserForm.validate((valid) => {
                    if (valid) {
                        let tmpMobile = this.editUser.mobile.substr(
                            this.editUser.mobile.indexOf(9),
                            9
                        );

                        if (tmpMobile === this.usersForm.mobile) {
                            return Dialog(this).openError(null, '手機號碼已有人使用');
                        }

                        this.isSubmit = true;
                        axios.post('users/update?id='+this.id+'&type=mobile', this.editUser).then((response) => {
                            this.isSubmit = false;
                            if (response.data.code === 0) {
                                if (parseInt(response.data.response.row) === 2) {
                                    return Dialog(this).openError(null, '手機號碼已有人使用');
                                }
                            }

                            this.editMobileShow = false;
                            Dialog(this).openSuccess(() => {
                                this.usersForm.mobile = this.editUser.mobile;
                                // setTimeout(() => {
                                //     window.opener.location.reload(true);
                                //     this.closeWindowFuc();
                                // }, 2000);
                            }, '儲存成功');
                        }).catch((error) => {
                            this.isSubmit = false;
                            Dialog(this).openError(callback, error);
                        });
                    }
                });
            },

            decode : function (key) {
                axios.put('users/decode/field', {'decode_data':this.usersForm[key]}).then((res) => {
                    if (res.data.code > 0) {
                        return Dialog(this).openError(null, '解碼失敗！請重試！');
                    }

                    let title;
                    switch (key) {
                        case 'id_number':
                            title = '身份證字號';
                            break;
                        case 'residence':
                            title = '居留證號';
                            break;
                        case 'passport':
                            title = '護照證號';
                            break;
                    }

                    Dialog(this).openTip(res.data.response, title);
                }).catch((error) => {
                    return Dialog(this).openError(null, error);
                });
            },

            delDeviceUuid : function () {
                let that = this;
                axios.post('users/update?id=' + this.id + '&type=uuid').then(function (response) {
                    if (response.data.code === 0) {
                        that.openGDDialog('success',function () {
                            that.usersForm.device_uuid = '';
                            setTimeout(function () {
                                that.closeWindowFuc();
                            },2000);
                        }, '儲存成功');
                    }else{
                        that.openGDWarning(callback);
                    }
                }).catch(function () {
                    that.openGDWarning(callback);
                });
            },

            closeWindow : function(message){
                let that = this;
                this.$nextTick(function () {
                    this.openBookRefresh(message, function () {
                        that.closeWindowFuc();
                    });
                });
            },

            openBookRefresh : function(message, callback) {
                let h = this.$createElement;
                this.$msgbox({
                    title: '提示',
                    message: h('p', null, [
                        h('span', null, message)
                    ]),
                    showCancelButton: true,
                    confirmButtonText: '確定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            callback();
                            done();
                        } else {
                            done();
                        }
                    },
                })
                .then(
                    action => {
                        //執行完畢
                        //console.log(action);
                    }).catch(e => {
                        //執行異常
                        //console.log(e)
                    });
            },

            closeWindowFuc : function(){
                let userAgent = navigator.userAgent;
                if (userAgent.indexOf("Firefox") != -1 || userAgent.indexOf("Chrome") !=-1) {
                    window.location.href="about:blank";
                    window.close();
                } else {
                    window.opener = null;
                    window.open("", "_self");
                    window.close();
                }
            },

            openGDDialog : function(type,callback,message) {
                this.$message({
                    type: type,
                    message: message
                });
                if(typeof(callback) == 'function') {
                    callback();
                }
            },

            openGDWarning : function(callback,message){
                if(typeof(message) == 'undefined') {
                    message = '操作失敗，請檢查'
                }
                this.openGDDialog('warning',callback,message);
            }
        }
    }
</script>

<style scoped>
    .el-form {
        width: 80%;
        margin: 0 auto;
    }
</style>
