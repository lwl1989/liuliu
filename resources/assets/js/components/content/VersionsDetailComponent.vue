<template>
    <div id="app">
        <el-dialog title="版本設置" :visible.sync="versionsVisible">
            <el-form :model="version" :rules="rules" ref="version">
                <el-form-item v-if="version.mobile_type == 2" label="版本號" :label-width="formLabelWidth" prop="vernumber">
                    <el-col :span="15">
                        <el-input v-model="version.vernumber" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item v-else label="版本號" :label-width="formLabelWidth" prop="vermobile">
                    <el-col :span="15">
                        <el-input v-model="version.vermobile" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth" label="使用系統" placeholder="請選擇">
                    <el-col :span="15">
                        <el-select v-model="version.mobile_type" @change="changeMobile" :disabled="!isAdd">
                            <el-option label="Android" :value="1"></el-option>
                            <el-option label="iOS" :value="2"></el-option>
                        </el-select>
                    </el-col>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth" label="是 否強制更新" placeholder="請選擇">
                    <el-col :span="15">
                        <el-select v-model="version.is_auto" >
                            <el-option label="是" :value="1"></el-option>
                            <el-option label="否" :value="0"></el-option>
                        </el-select>
                    </el-col>
                </el-form-item>
                <el-form-item :label-width="formLabelWidth" label="上架時間" prop="up_time">
                    <el-col :span="15">
                        <el-date-picker
                                type="date"
                                v-model="version.up_time"
                                format="yyyy-MM-dd HH:mm:ss"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                start-placeholder="上架時間" >
                        </el-date-picker>
                    </el-col>
                </el-form-item>
                <el-form-item label="更新內容" :label-width="formLabelWidth" prop="content">
                    <el-col :span="15">
                        <el-input type="textarea" :autosize="{ minRows: 3, maxRows: 4}" v-model="version.content" ></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="versionsVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitBanner">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {VersionsRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';
    export default {
        name: "versions-detail-component",
        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '120px',
                versionsVisible: false,
                isAdd : true,
                version: {
                    id: 0,
                    vernumber:'',
                    up_time:'',
                    is_auto:0,
                    content:'',
                    mobile_type:1,
                    vermobile:'',
                },

                rules: VersionsRule,
                dialog: NewDialog(this),
                version_number:'Android',
            }
        },
        watch: {
            versionsVisible(current, old) {
                if (current === false) {
                    this.resetBanner();
                }
            }
        },
        methods: {
            changeMobile(){
                if(this.version.mobile_type == 1){
                    this.version.version_number='Android';
                }else{
                    this.version.version_number='vernumber';
                }
            },
            resetBanner() {
                this.version ={
                    id: 0,
                    vernumber:'',
                    up_time:'',
                    is_auto:0,
                    content:'',
                    mobile_type:1,
                };
                this.vermobile = this.version.vernumber;

            },
            copyObj() {
                let data = {};
                let that = this;
                Object.keys(that.version).forEach(function(key){
                    data[key] = that.version[key];
                });

                return data;
            },
            submitBanner() {
                let data = this.copyObj();

                // 表單驗證方法
                this.$refs.version.validate(function (result) {
                    const h = this.$createElement;
                    let that = this;
                    if (result) {
                        //驗證通過,調用module裏的setUserInfo方法
                        //this.$store.dispatch("setUserInfo");
                        this.$msgbox({
                                    title: '提示',
                                    message: h('p', null, [
                                        h('span', null, '確定要新增 '),
                                        h('i', {style: 'color: teal'}, that.version.title),
                                        h('span', null, ' 嗎？')
                                    ]),
                                    showCancelButton: true,
                                    confirmButtonText: '確定',
                                    cancelButtonText: '取消',
                                    beforeClose: (action, instance, done) => {
                                    let callback = function () {
                                        instance.confirmButtonLoading = false;
                                        instance.confirmButtonText = '確定';
                                        that.versionsVisible = false;
                                        done();
                                    };
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '新增中...';

                            let url = that.version.id > 0 ? '/content/versions/update?id=' + that.version.id : '/content/versions/create';
                            if(that.version.mobile_type == 1){
                                that.version.vernumber = that.version.vermobile;
                            }

                            axios.post(url, that.version)
                                    .then(function (response) {
                                        if (response.data.code === 0) {
                                            that.$refs.version.resetFields();
                                            that.dialog.openSuccess(function(){
                                                if(data.id === 0) {

                                                    data.id = response.data.response.id;

                                                    if(data.mobile_type == 1){
                                                        data.vernumber = data.vermobile;
                                                    }
                                                    data.create_time = data.update_time = response.data.response.create_time;
                                                }
                                                callback();
                                                that.$emit('add', data);
                                            });
                                        } else {
                                            that.dialog.openWarning(callback);
                                        }
                                    }).catch(function (error) {
                                that.dialog.openWarning(callback);
                            });
                        } else {
                            callback()
                        }
                    }
                    });
                    } else {

                    }
                }.bind(this));
            },

            editVersions : function(id) {

                let that = this;
                this.versionsVisible = true;
                if(id > 0) {
                    this.isAdd = false
                    axios.get('/content/versions/get?id=' + id)
                            .then(function (response) {
                                that.version = response.data.response.data;
                                if(that.version.mobile_type == 1){
                                    that.version.vermobile = that.version.vernumber;
                                    that.version.vernumber = '';
                                }
                            }).catch(function (error) {
                        that.$message.error(error.toString());
                    });
                } else {
                    this.isAdd = true
                }
            }
        }
    }
</script>

<style scoped>

</style>