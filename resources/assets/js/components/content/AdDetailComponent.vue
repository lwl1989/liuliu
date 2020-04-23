<template>
    <div id="app">
        <el-dialog title="廣告活動" :visible.sync="adVisible">
            <el-form :model="ad" :rules="rules" ref="ad" v-loading="loading">
                <el-form-item label="名稱" :label-width="formLabelWidth" prop="title">
                    <el-col :span="15">
                        <el-input v-model="ad.title" auto-complete="off" placehplder="名稱"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="廣告圖片" :label-width="formLabelWidth" prop="cover" required>
                    <img v-if="ad.cover_url !== ''" :src="ad.cover_url" width="250" height="320" />
                    <el-upload
                            action="/content/ad/cover"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept=".jpg,.jpeg,.png,.PNG,.JPG,.JPEG"
                            ref="upload"
                            :on-remove="handleRemove"
                            :file-list="covers"
                            :on-success="handleSuccess"
                            :on-change="handleFileChange"
                            :show-file-list="false"
                            :auto-upload=false>
                        <el-button size="small" type="primary">選取圖片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            圖檔建議尺寸{{except_width}}*{{except_height}}像素，僅限 .jpg .jpeg .png 格式，只能夾帶一個檔案，檔案大小不可超過2M
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="上架時間" :label-width="formLabelWidth" prop="select_time" required>
                    <el-date-picker
                            v-model="ad.select_time"
                            type="datetimerange"
                            start-placeholder="開始時間"
                            end-placeholder="結束時間" >
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="連結網址" :label-width="formLabelWidth" prop="target_url">
                    <el-col :span="15">
                        <el-input v-model="ad.target_url" auto-complete="off" placeholder="連結網址"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="最後變動資訊" :label-width="formLabelWidth" v-if="ad.updater !== ''">
                    <el-col :span="15">
                        <el-input :value="ad.update_time+' '+ad.updater" auto-complete="off" disabled style="border: none;"></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="adVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitAd">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {AdRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';

    export default {
        name: "ad-detail-component",

        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '120px',
                adVisible: false,
                 ad: {
                    id: 0,
                    title:'',
                    cover:'',
                    target_url:'',
                    online_time:'',
                    offline_time:'',
                    update_time:'',
                    select_time:'',
                    cover_url:'',
                    updater:''
                },
                covers:[],
                rules: AdRule,
                dialog: NewDialog(this),
                except_width: 1092,
                except_height: 1340,
                except_size: 2*1024*1024,
                loading : false
            }
        },

        watch: {
            adVisible(current) {
                if (current === false) {
                    this.resetAd();
                }
            },

            'ad.cover' : function() {
                this.$nextTick(() => {
                    this.$refs.ad.validateField('cover');
                });
            },

            'ad.select_time' : function() {
                this.$nextTick(() => {
                    this.$refs.ad.validateField('select_time');
                });
            }
        },

        methods: {
            resetAd() {
                this.ad ={
                    id: 0,
                    title:'',
                    cover:'',
                    cover_url:'',
                    target_url:'',
                    online_time:'',
                    offline_time:'',
                    update_time:'',
                    select_time:[],
                    updater:''
                };
                this.covers = [];
            },

            copyObj() {
                let data = {};
                let that = this;
                Object.keys(that.ad).forEach(function(key){
                    data[key] = that.ad[key];
                });
                return data;
            },

            submitAd() {
                const h = this.$createElement;

                this.$refs.ad.validate((result) => {
                    if (result) {
                        this.ad.online_time = this.ad.select_time[0].toString();
                        this.ad.offline_time = this.ad.select_time[1].toString();

                        this.dialog.openSelfDialog((callback) => {
                            let url = this.ad.id > 0
                                ? '/content/ad/update?id='+this.ad.id
                                : '/content/ad/create';

                            axios.post(url, this.ad).then((response) => {
                                if (response.data.code === 0) {
                                    this.dialog.openSuccess(() => {
                                        callback();
                                        this.adVisible = false;
                                        this.$refs.ad.resetFields();

                                        window.location.reload();
                                    });
                                } else {
                                    this.dialog.openWarning();
                                }
                            }).catch(error => {
                                this.dialog.openWarning(callback, error.toString());
                            });
                        }, h('p', null, [
                            h('span', null, '確定要儲存 '),
                            h('span', {style: 'color: teal'}, this.ad.title),
                            h('span', null, ' 嗎？')
                        ]), '執行中...');
                    }
                });
            },

            handleRemove : function(file) {
                let index = this.ad.image.indexOf(file);
                if(index !== -1) {
                    this.ad.image.splice(index);
                }
            },

            handleError : function(err, file) {
                let index = this.ad.image.indexOf(file);
                if(index !== -1) {
                    this.ad.image.splice(index);
                }
            },

            handleSuccess : function(res) {
                this.ad.cover = res.response.path;
                this.ad.cover_url = res.response.url;
                this.covers = [];
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

                        if (file.size > that.except_size) {
                            error('上傳的圖片大小不能超過 2M!');
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

            editAd : function(id) {
                let that = this;
                this.adVisible = true;
                if(id > 0) {
                    this.loading = true;
                    axios.get('/content/ad/get?id=' + id)
                        .then(function (response) {
                            that.loading = false;

                            response.data.response.data.select_time = [
                                new Date(response.data.response.data.online_time),
                                new Date(response.data.response.data.offline_time)
                            ];
                            that.ad = response.data.response.data;
                        }).catch(function (error) {
                            that.loading = false;
                        });
                }
            }
        }
    }
</script>

<style scoped>

</style>