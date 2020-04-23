<template>
    <div id="data">
        <el-dialog title="歡迎頁" :visible.sync="WelcomeVisible">
            <el-form :model="data" :rules="rules" ref="data" label-position="top">
                <el-form-item label="名稱" :label-width="formLabelWidth" prop="title" >
                    <el-col :span="15">
                        <el-input v-model="data.title" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="歡迎頁圖片（iPhone 6 7 8 包含plus）" :label-width="formLabelWidth" required>
                    
                    <img v-if="cover_url.IPhone !== ''" :src="cover_url.IPhone" width="150" height="150" />
                    <el-upload
                            action="/content/welcome/cover"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept="image/jpg,image/jpeg,image/png"
                            ref="IPhone"
                            :on-remove="handleRemove"
                            :file-list="cover.IPhone"
                            :on-success="handleSuccess"
                            :on-change="handleFileChange"
                            :show-file-list="false"
                            :auto-upload=false>
                        <el-button size="small" type="primary" @click="setUploader('IPhone')">選取圖片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            圖檔建議尺寸{{except.IPhone.width}}*{{except.IPhone.height}}像素，僅限 .jpg .jpeg .png 格式，只能夾帶一個檔案，檔案大小不可超過2M
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="歡迎頁圖片（iPhone X）" :label-width="formLabelWidth" required>
                    
                    <img v-if="cover_url.IPhoneX !== ''" :src="cover_url.IPhoneX" width="150" height="150"/>
                    <el-upload
                            action="/content/welcome/cover"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept="image/jpg,image/jpeg,image/png"
                            ref="IPhoneX"
                            :on-remove="handleRemove"
                            :file-list="cover.IPhoneX"
                            :on-success="handleSuccess"
                            :on-change="handleFileChange"
                            :show-file-list="false"
                            :auto-upload=false>
                        <el-button size="small" type="primary" @click="setUploader('IPhoneX')">選取圖片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            圖檔建議尺寸{{except.IPhoneX.width}}*{{except.IPhoneX.height}}像素，僅限 .jpg .jpeg .png 格式，只能夾帶一個檔案，檔案大小不可超過2M
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="歡迎頁圖片（其他手機）" :label-width="formLabelWidth" required>
                    
                    <img v-if="cover_url.Normal !== ''" :src="cover_url.Normal" width="150" height="150"/>
                    <el-upload
                            action="/content/welcome/cover"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept="image/jpg,image/jpeg,image/png"
                            ref="Normal"
                            :on-remove="handleRemove"
                            :file-list="cover.Normal"
                            :on-success="handleSuccess"
                            :on-change="handleFileChange"
                            :show-file-list="false"
                            :auto-upload=false>
                        <el-button size="small" type="primary" @click="setUploader('Normal')">選取圖片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            圖檔建議尺寸{{except.Normal.width}}*{{except.Normal.height}}像素，僅限 .jpg .jpeg .png 格式，只能夾帶一個檔案，檔案大小不可超過2M
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="最後變動資訊" :label-width="formLabelWidth" v-if="data.updater !== ''">
                    <el-col :span="15">
                        <el-input :value="data.update_time+' '+data.updater" auto-complete="off" disabled style="border: none;"></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="WelcomeVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitWelcome">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {WelComeRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';

    export default {
        name: "data-detail-component",
        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '300px',
                WelcomeVisible: false,
                data: {
                    id: 0,
                    title:'',
                    cover:{
                        IPhone:'',
                        IPhoneX:'',
                        Normal:''
                    },
                    platform:'',
                    target_url:'',
                    update_time:'',
                    updater:''
                },
                cover_url:{
                    IPhone:'',
                    IPhoneX:'',
                    Normal:''
                },
                cover:{
                    IPhoneX:[],
                    Normal:[],
                    IPhone:[]
                },
                rules: WelComeRule,
                dialog: NewDialog(this),
                except: {
                    IPhone:{
                        width:1242,
                        height:2208
                    },
                    IPhoneX:{
                        width:1125,
                        height:2437
                    },
                    Normal:{
                        width:750,
                        height:1334
                    }
                },
                cover_IPhone: '',
                cover_IPhoneX: '',
                cover_Normal: '',
                except_size: 2*1024*1024,
                uploader:''
            }
        },
        watch: {
            WelcomeVisible(current) {
                if (current === false) {
                    this.resetWelcome();
                }
            }
        },
        methods: {
            setUploader(index){
                this.uploader = index
            },

            resetWelcome() {
                this.data ={
                    id: 0,
                    title:'',
                    cover:{
                        IPhone:'',
                        IPhoneX:'',
                        Normal:''
                    },
                    platform:'',
                    target_url:'',
                    update_time:'',
                    updater:'',
                    cover_IPhone: '',
                    cover_IPhoneX: '',
                    cover_Normal: ''
                };
                this.cover = {
                    IPhoneX:[],
                    Normal:[],
                    IPhone:[]
                };
                this.cover_url={
                    IPhone:'',
                    IPhoneX:'',
                    Normal:''
                };
            },

            copyObj() {
                let data = {};
                let that = this;
                Object.keys(that.data).forEach(function(key){
                    data[key] = that.data[key];
                });
                return data;
            },

            submitWelcome() {
                const h = this.$createElement;
                let data = this.copyObj();

                if (!data.title) {
                    return this.dialog.openWarning(null, '名稱為必填欄位');
                }
                if (data.cover.IPhone === '' || data.cover.IPhoneX === '' || data.cover.Normal === '') {
                    return this.dialog.openWarning(null, '圖片為必填欄位');
                }

                this.$refs.data.validate((result) => {
                    if (result) {
                        this.dialog.openSelfDialog((callback) => {
                            let url = this.data.id > 0
                                ? '/content/welcome/update?id='+this.data.id
                                : '/content/welcome/create';

                            axios.post(url, data).then((response) => {
                                    if (response.data.code === 0) {
                                        this.$refs.data.resetFields();
                                        this.dialog.openSuccess(() => {
                                            if (data.id > 0) {
                                                data.id = response.data.response.id;
                                                data.create_time = data.update_time = response.data.response.create_time;
                                                this.$emit('add', data);
                                            }

                                            callback();
                                        }, '儲存成功');

                                        window.location.reload();

                                        return;
                                    }
                                    this.dialog.openWarning(callback, '儲存失敗');
                                }).catch(() => {
                                    this.dialog.openWarning(callback, '儲存失敗');
                                });
                        }, h('p', null, [
                            h('span', null, '確定要新增 '),
                            h('span', {style: 'color: teal'}, this.data.title),
                            h('span', null, ' 嗎？')
                        ]), '創建中...');
                    }
                });
            },

            handleRemove : function(file) {
                let index = this.data.image.indexOf(file);
                if(index !== -1) {
                    this.data.image.splice(index);
                }
            },

            handleError : function(err, file) {
                let index = this.data.image.indexOf(file);
                if(index !== -1) {
                    this.data.image.splice(index);
                }
            },

            handleSuccess : function(res) {
                this.data.cover[this.uploader] = res.response.path;
                this.cover_url[this.uploader] = res.response.url;
                this.cover[this.uploader] = [];
            },

            createReader : function(file, error, success) {
                let reader = new FileReader;
                let that = this;
                reader.onload = function (evt) {
                    let image = new Image();
                    image.onload = function () {
                        let imageType = ['image/jpeg', 'image/png', 'image/jpg'];
                        if (imageType.indexOf(file.type) < 0) {
                            error('上傳的文件是不正確的文件類型[image/jpg|image/jpeg|image/png]');
                            return;
                        }

                        if (file.size > that.except_size) {
                            error('上傳的圖片大小不能超過 2M!');
                            return;
                        }

                        let width = parseInt(this.width);
                        let height = parseInt(this.height);

                        if (width !== that.except[that.uploader].width || height !== that.except[that.uploader].height) {
                            error('上傳圖片尺寸不正確（'+that.except[that.uploader].width+'x'+that.except[that.uploader].height+'）');
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
                        that.$refs[that.uploader].submit();
                    });
                }
            },
            editWelcome : function(id) {
                let that = this;
                this.WelcomeVisible = true;
                if(id > 0) {
                    axios.get('/content/welcome/get?id=' + id)
                        .then(function (response) {
                            that.data = response.data.response.data;
                            Object.keys(that.data.cover_url).forEach(function(key){
                                that.cover_url[key] = that.data.cover_url[key];
                            });
                        }).catch(function (error) {
                        console.log(error);
                    });
                }
            }
        }
    }
</script>

<style scoped>

</style>