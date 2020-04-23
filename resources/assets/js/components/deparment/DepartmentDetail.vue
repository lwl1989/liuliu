<template>
    <div id="app">
        <el-dialog title="業務單位資訊" :visible.sync="departmentFormVisible">
            <el-form :model="departmentForm" :rules="rules" ref="departmentForm" v-loading="loading">
                <el-form-item label="隸屬單位" :label-width="formLabelWidth" prop="unit_id">
                    <el-select v-model="departmentForm.unit_id" placeholder="請選擇隸屬單位" :disabled="edit">
                        <el-option label="臺東縣政府" value="0" key="0"></el-option>
                        <template v-for="(item,index) in unit_sum">
                            <el-option
                                    :label="item"
                                    :value="index"
                                    :key="index"
                            >
                            </el-option>
                        </template>
                    </el-select>
                </el-form-item>
                <el-form-item label="業務單位" :label-width="formLabelWidth" prop="name">
                    <el-col :span="15">
                        <el-input v-model="departmentForm.name" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="英文單位" :label-width="formLabelWidth" prop="e_name">
                    <el-col :span="15">
                        <el-input v-model="departmentForm.e_name" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="代表圖像" :label-width="formLabelWidth" prop="image">
                    <el-upload
                            action="/department/image"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept=".jpg,.jpeg,.png,.PNG,.JPG,.JPEG"
                            ref="upload"
                            :on-remove="handleRemove"
                            :file-list="departmentForm.image"
                            :on-success="handleSuccess"
                            :on-change="handleFileChange"
                            :auto-upload=false
                            list-type="picture">
                        <el-button size="small" type="primary">選取圖片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            圖檔建議尺寸150像素*150像素，僅限.jpg .gif .png格式，只能夾帶一個檔案，檔案大小不可超過600kb
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="郵遞區號" :label-width="formLabelWidth" prop="mail_code">
                    <el-col :span="15">
                        <el-input v-model="departmentForm.mail_code" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="地址" :label-width="formLabelWidth" prop="address">
                    <el-col :span="15">
                        <el-input v-model="departmentForm.address" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="電話" :label-width="formLabelWidth">
                    <el-col :span="6">
                        <el-form-item prop="phone">
                            <el-input v-model="departmentForm.phone" auto-complete="off"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col class="line" :span="2" :offset="1" prop="tel_ext">分機</el-col>
                    <el-col :span="6">
                        <el-form-item prop="phone_ext">
                            <el-input v-model="departmentForm.phone_ext" auto-complete="off"></el-input>
                        </el-form-item>
                    </el-col>
                </el-form-item>
                <el-form-item label="傳真" :label-width="formLabelWidth" prop="facsimile">
                    <el-col :span="15">
                        <el-input v-model="departmentForm.facsimile" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="FB粉絲專頁" :label-width="formLabelWidth" prop="app_number">
                    <el-col :span="6">
                        <el-input v-model="departmentForm.app_number" auto-complete="off"></el-input>
                    </el-col>
                    <el-col class="line" :span="1" :offset="1" prop="fans_number">/</el-col>
                    <el-col :span="6">
                        <el-input v-model="departmentForm.fans_number" auto-complete="off"></el-input>
                    </el-col>
                    <el-col class="line" :span="1" :offset="1" prop="fans_name">/</el-col>
                    <el-col :span="6">
                        <el-input v-model="departmentForm.fans_name" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="最後變動資訊" :label-width="formLabelWidth">
                    <el-col :span="15">
                        <el-input v-model="departmentForm.admin_name" auto-complete="off" disabled>
                        </el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="departmentFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitDepartment">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {DepartmentRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';

    export default {
        name: "department-detail",
        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '120px',
                departmentFormVisible: false,
                departmentForm: {
                    id: '',
                    unit_id: '0',
                    name: '',
                    e_name: '',
                    image: [],
                    mail_code: '',
                    address: '',
                    phone: '',
                    phone_ext: '',
                    facsimile: '',
                    app_number: '',
                    fans_number: '',
                    fans_name: ''
                },
                loading : false,
                rules: DepartmentRule,
                dialog: NewDialog(this),
                edit: false,
                updateBeforeName : '',
                unit_sum : this.getUnitSum()
            }
        },
        watch: {
            departmentFormVisible(current) {
                if (current === false) {
                    this.resetDepartment();
                }
            }
        },
        methods: {
            resetDepartment() {
                this.departmentForm ={
                    id: '',
                    unit_id: '0',
                    name: '',
                    e_name:'',
                    image: [],
                    mail_code: '',
                    address: '',
                    phone: '',
                    phone_ext: '',
                    facsimile: '',
                    app_number: '',
                    fans_number: '',
                    fans_name: ''
                }
            },

            getUnitSum : function(){
                axios.get('/department/getUnit')
                    .then((response) => {
                        this.unit_sum = response.data.response;
                    }).catch((error) => {
                        console.log(error);
                    });
            },

            submitDepartment() {
                const h = this.$createElement;
                let that = this;

                this.$refs.departmentForm.validate(function (result) {
                    if (result) {
                        axios.get('/department/check/name?name='+that.departmentForm.name).then((res) => {
                            if ((!that.departmentForm.id || that.updateBeforeName !== that.departmentForm.name) &&
                                res.data.response.has
                            ) {
                                NewDialog(that).openWarning(null, '單位名稱重複');
                                return;
                            }

                            this.$msgbox({
                                title: '提示',
                                message: h('p', null, [
                                    h('span', null, '確定要創建業務單位 '),
                                    h('span', {style: 'color: teal'}, that.departmentForm.name),
                                    h('span', null, ' 嗎？')
                                ]),
                                showCancelButton: true,
                                confirmButtonText: '確定',
                                cancelButtonText: '取消',
                                beforeClose: (action, instance, done) => {
                                    let callback = function () {
                                        instance.confirmButtonLoading = false;
                                        instance.confirmButtonText = '確定';
                                        that.departmentFormVisible = false;
                                        done();
                                    };
                                    if (action === 'confirm') {
                                        instance.confirmButtonLoading = true;
                                        instance.confirmButtonText = '創建中...';

                                        let url = that.departmentForm.id > 0 ? '/department/update?id=' + that.departmentForm.id : '/department/create';
                                        axios.post(url, that.departmentForm)
                                            .then(function (response) {
                                                if (response.data.code === 0) {
                                                    that.$refs.departmentForm.resetFields();
                                                    that.dialog.openSuccess(function () {
                                                        callback();
                                                        window.location.reload();
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
                        });
                    }
                }.bind(this));
            },

            showDepartment() {
                this.edit = false;
                this.departmentFormVisible = true;
            },

            handleRemove : function(file) {
                let index = this.departmentForm.image.indexOf(file);
                if(index !== -1) {
                    this.departmentForm.image.splice(index);
                }
            },

            handleError : function(err, file) {
                let index = this.departmentForm.image.indexOf(file);
                if(index !== -1) {
                    this.departmentForm.image.splice(index);
                }
            },

            handleSuccess : function(res) {
                this.departmentForm.image.push(res.response);
            },

            createReader : function(file, error, success) {
                let reader = new FileReader;
                let that = this;
                reader.onload = function (evt) {
                    let image = new Image();
                    image.onload = function () {
                        let imageType = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (imageType.indexOf(file.type) < 0) {
                            error('上傳的文件是不正確的文件類型[image/jpg|image/jpeg|image/png|image/gif]');
                            return;
                        }

                        if (file.size / 1024  > 600) {
                            error('上傳的圖片大小不能超過 600K!');
                            return;
                        }

                        success();
                    };
                    image.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            },

            handleFileChange : function(file, files) {
                if (!("checked" in file)) {
                    let that = this;
                    this.createReader(file.raw, function (message) {
                        that.$message.error(message);
                        files.splice(-1);
                    }, function () {
                        file.checked = true;
                        that.$refs.upload.submit();
                    });
                }
            },

            editDepartment : function(id) {
                this.departmentFormVisible = true;
                this.loading = true;
                this.edit = true;
                axios.get('/department/getOne?id=' + id)
                    .then((response) => {
                        this.loading = false;
                        this.departmentForm = response.data.response.data;
                        this.updateBeforeName = this.departmentForm.name;
                        this.departmentForm.unit_id = this.departmentForm.parent_id.toString();
                    })
                    .catch (function (error) {

                    });
            }
        }
    }
</script>

<style scoped>

</style>