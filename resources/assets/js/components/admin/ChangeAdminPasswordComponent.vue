<template>
    <div id="app">
        <el-dialog title="重設密碼" :visible.sync="openPasswordPage">
            <el-row>
                    <el-form :model="password" :rules="rules" ref="password" label-position="left">
                        <el-form-item label="請輸入舊密碼" prop="oldPassword">
                            <el-input type="password" v-model="password.oldPassword"></el-input>
                        </el-form-item>
                        <el-form-item label="請輸入新密碼" prop="newPassword">
                            <el-input type="password" v-model="password.newPassword"></el-input>
                        </el-form-item>
                        <el-form-item label="再次輸入新密碼" prop="confirmPassword">
                            <el-input type="password"  v-model="password.confirmPassword"></el-input>
                        </el-form-item>
                        <el-form-item>
                            確定重設密碼？
                        </el-form-item>
                    </el-form>
            </el-row>
            <el-row>
                <el-button type="info" @click="closePassword" style="float: right;">取 消</el-button>
                <el-button type="primary" @click="doChangePass" style="float: right;margin-right:20px">確 定</el-button>
            </el-row>
        </el-dialog>

    </div>
</template>

<script>
    import NewDialog from '../../tools/element-ui-dialog'

    export default {
        name: "change-admin-password-component",

        data:function () {
            return {
                password : {
                    admin_id : 0,
                    oldPassword : '',
                    newPassword : '',
                    confirmPassword : ''
                },
                rules:{
                    oldPassword: [{required: true, message: '舊密碼為必填欄位', trigger: 'change'}],
                    newPassword: [{required: true, message: '新密碼為必填欄位', trigger: 'change'}],
                    confirmPassword: [{required: true, message: '確認密碼為必填欄位', trigger: 'change'}]
                },
                openPasswordPage : false,
                dialog : NewDialog(this)
            }
        },

        watch: {
            openPasswordPage(current) {
                if (current === false) {
                    this.resetPasswordFrom();
                }
            }
        },

        methods:{
            resetPasswordFrom(){
                this.password = {
                    admin_id : 0,
                    oldPassword : '',
                    newPassword: '',
                    confirmPassword: ''
                };
            },

            doOpenPasswordPage(id){
                if(id < 1) {
                    this.dialog.openWarning(null,'用戶id錯誤');
                }

                this.resetPasswordFrom();
                this.password.admin_id = id;
                this.openPasswordPage = true;
            },

            closePassword(){
                this.resetPasswordFrom();
                this.openPasswordPage = false;
            },

            doChangePass() {
                this.$refs.password.validate((result) => {
                    if (result) {
                        if(this.password.newPassword !== this.password.confirmPassword) {
                            return this.dialog.openWarning(null, '兩次輸入的新密碼不同');
                        }

                        axios.post('/admin/password', this.password).then((response) => {
                            if (parseInt(response.data.code) === -1) {
                                this.dialog.openWarning(null, '舊密碼錯誤！');
                                return;
                            }

                            if (parseInt(response.data.code) === -2) {
                                this.dialog.openWarning(null, '新舊密碼不可一致，請重新輸入新密碼');
                                return;
                            }

                            if (parseInt(response.data.code) === 0) {
                                this.dialog.openSuccess(() => {
                                    this.closePassword();
                                }, '操作成功');
                                return;
                            }

                            this.dialog.openWarning(null, '操作失敗');
                        }).catch(err => {
                            this.dialog.openWarning(null, '操作失敗'+err.toString());
                        });
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>