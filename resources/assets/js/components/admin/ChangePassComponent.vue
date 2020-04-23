<template>
    <div id="app">
        <el-dialog title="重設密碼" :visible.sync="changeNowUserPassword">
            <el-row>

                    <el-form :model="password" :rules="rules" ref="password" >
                        <el-form-item label="原密碼" :label-width="formLabelWidth"  prop="oldPassword">
                            <el-input  type="password" v-model="password.oldPassword" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="新密碼" :label-width="formLabelWidth"  prop="newPassword">
                            <el-input type="password" v-model="password.newPassword" auto-complete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="確認新密碼" :label-width="formLabelWidth"  prop="confirmPassword">
                            <el-input type="password"  v-model="password.confirmPassword" auto-complete="off"></el-input>
                        </el-form-item>
                    </el-form>
            </el-row>
            <el-row>
                <el-button type="primary" @click="doChangePass" style="float: right;">確 定</el-button>
                <el-button type="info" @click="closeChangeNowUserPassword" style="float: right;margin-right: 10%;">取 消</el-button>
            </el-row>
        </el-dialog>
    </div>
</template>

<script>
    import Dialog from  '../../tools/element-ui-dialog'
    export default {
        name: "change-pass-component",
        data:function () {
            return {
                rules:{},
                formLabelWidth:'100px',
                password : {
                    oldPassword: '',
                    newPassword: '',
                    confirmPassword: ''
                },
                changeNowUserPassword:false,
                dialog:Dialog(this)
            }
        },
        methods:{
            openChangeNowUserPassword(){
                this.changeNowUserPassword = true;
            },
            closeChangeNowUserPassword(){
                this.resetPasswordFrom();
                this.changeNowUserPassword = false;
            },
            resetPasswordFrom(){
                this.password = {
                        oldPassword: '',
                        newPassword: '',
                        confirmPassword: ''
                };
            },
            doChangePass(){
                let that = this;
                axios.post('/admin/change/password',this.password).then(function (response) {
                        if(parseInt(response.data.code) === 0) {
                            that.dialog.openSuccess(function () {
                                setTimeout(function () {
                                    window.location.href = '/logout';
                                },2000);
                            },'您已重設密碼，請重新登入，即將跳轉到登入頁！')
                        }else{
                            that.dialog.openWarning(function () {
                                that.resetPasswordFrom();
                            },'操作失敗');
                        }
                }).catch(function (err) {
                    that.dialog.openWarning(function () {
                        that.resetPasswordFrom();
                    },'操作失敗'+err.toString());

                });
            }
        }
    }
</script>

<style scoped>

</style>