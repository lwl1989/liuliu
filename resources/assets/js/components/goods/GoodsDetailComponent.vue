<template>
    <div id="app" v-loading="loading">
        <el-alert v-if="copy"
                  :title="editName"
                  type="warning"
                  center
                  show-icon
                  :closable="false"
        >
        </el-alert>

        <el-form :model="goods" :rules="rules" ref="goods" label-width="150px" size="medium" style="margin-top:20px">
            <el-row justify="space-between">
                <el-col :span="16">
                    <el-form-item label="供應商資訊" prop="shop_id">
                        <el-select v-model="shopType" @change="shopTypeChange" :disabled="disabled" style="width:110px">
                            <el-option label='特約商店' value="1"></el-option>
                            <el-option label='公益團體' value="2"></el-option>
                        </el-select>
                        &nbsp;&nbsp;
                        <el-select v-model="goods.shop_id" placeholder="請選擇" :disabled="disabled">
                                <el-option
                                        v-if="shopType == item.type"
                                        v-for="item in admins"
                                        :label="item.name"
                                        :value="item.shop_id"
                                        :key="item.shop_id">
                                </el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="商品編號" prop="goods_num" :disabled="id > 0">
                        <el-input v-model="goods.goods_num" ></el-input>
                    </el-form-item>

                    <el-form-item label="商品類別" prop="category_id">
                        <el-select v-model="goods.category_id" placeholder="請選擇">
                            <el-option
                                    v-for="item in categories"
                                    :label="item.name"
                                    :value="item.id"
                                    :key="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="商品名稱" prop="goods_name">
                            <el-input v-model="goods.goods_name" ></el-input>
                    </el-form-item>

                    <el-form-item label="商品定價" prop="goods_price">
                        <el-input-number v-model="goods.goods_price" :min="0"></el-input-number>
                    </el-form-item>

                    <el-form-item label="兌換金幣數" prop="exchange_gold">
                            <el-input-number v-model="goods.exchange_gold" :min="0" :disabled="disabled"></el-input-number>
                    </el-form-item>

                    <el-form-item label="提供兌換數量" prop="goods_stock">
                            <el-input-number v-model="goods.goods_stock" :min="0" :max="999999" :disabled="disabled"></el-input-number>
                    </el-form-item>
                </el-col>
                <!--<el-col :offset='1' :span="7" v-if="id > 0">-->
                    <!--<div style="margin:20px 0 0 0;text-align:center">-->
                        <!--<img width="100%" :src="goods.qrUrl"/>-->
                        <!--商品資訊QR Code <el-button type="primary" size="mini" @click="downQRCode">下載</el-button>-->
                    <!--</div>-->
                <!--</el-col>-->
            </el-row>
            <el-form-item label="商品圖片" prop="goods_cover" required>
                <el-upload
                        action="/goods/cover"
                        :multiple=false
                        :limit=10
                        :headers="headers"
                        accept="'image/jpeg','image/png'"
                        ref="upload"
                        :on-remove="handleRemove"
                        :file-list="goods.goods_cover"
                        :on-success="handleSuccess"
                        :on-change="handleFileChange"
                        :auto-upload=false
                        :on-exceed="handleLimit"
                        list-type="picture"
                        :disabled="isUploading">
                    <el-button type="primary" >選取圖片</el-button>
                    <div slot="tip" class="el-upload__tip">
                        圖檔限制尺寸必須為1242像素*1242像素,僅限jpg.jpeg.png格式，最多上傳10張，點擊X可移除圖片
                    </div>
                </el-upload>
            </el-form-item>

            <el-form-item label="詳細規格描述" prop="goods_intro">
                <el-input type="textarea" :autosize="{ minRows: 3, maxRows: 4}" v-model="goods.goods_intro" ></el-input>
            </el-form-item>

            <el-form-item label="注意事項" prop="goods_remark">
                <el-input type="textarea" :autosize="{ minRows: 3, maxRows: 4}" v-model="goods.goods_remark" ></el-input>
            </el-form-item>

            <el-form-item label="搜尋關鍵字" prop="keyword">
                <el-input v-model="goods.keyword" ></el-input>
            </el-form-item>

            <el-form-item label="配送方式" prop="distribution_type">
                <el-select v-model="goods.distribution_type" >
                    <el-option label="現場取貨" value="1"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="優惠主打" prop="recommend_icon">
                <el-checkbox v-model="goods.recommend_icon">
                    <span>顯示本月主打圖示 (此商品若被選為本月主打商品將會顯示優惠圖示)</span>
                </el-checkbox>
            </el-form-item>

            <el-form-item label="最後異動資訊" v-if="goods.updater_account">
                {{goods.update_time}}&nbsp;&nbsp; {{goods.updater_account}}
            </el-form-item>

            <el-form-item>
                <el-button plain type='warning' @click="doSubmit('offline')">暫存</el-button>
                <el-button plain type="primary" @click="doSubmit('online')">上架</el-button>
                <el-button plain v-if="id > 0 && !copy" @click="copyGoods">複製</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import {GoodsRule} from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "goods-detail",

        data: function () {
            return {
                id: this.$route.params.id,
                copy: this.$route.query.copy == 1 ? true : false,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                disabled: false,
                isUploading : false,
                goods: {
                    id: 0,
                    shop_id: '',
                    goods_num: '',
                    category_id: '',
                    goods_name: '',
                    goods_price: '',
                    price_type: '1',
                    exchange_gold: '',
                    goods_sum: '',
                    goods_stock: '',
                    goods_cover: [],
                    goods_cover_url: '',
                    goods_remark: '',
                    goods_intro: '',
                    keyword: '',
                    distribution_type: '1',
                    update_time: '',
                    operator: '',
                    goods_volume:'',
                    recommend_icon:false,
                    qrUrl:'',
                    updater_account: ''
                },
                rules: GoodsRule,
                except_width: 260,
                except_height: 260,
                admins:[],
                shopType: '1',
                categories:[],
                again:0,
                loading:true,
                editName: '您在編輯的是 ',
            }
        },

        watch:{
            'goods.shop_id' : function(val) {
                this.$nextTick(() => {
                    for (let index in this.admins) {
                        if (this.admins[index].shop_id == val) {
                            this.shopType = this.admins[index].type.toString();
                            break;
                        }
                    }
                });
            }
        },

        created() {
            this.$nextTick(function() {
                this.getAdmin();
                this.getCate();
                this.getGoods();
            });
        },

        methods: {
            shopTypeChange(value) {
                for (let index in this.admins) {
                    if (this.admins[index].type == value) {
                        this.goods.shop_id = this.admins[index].shop_id;
                        break;
                    }
                }
            },

            downQRCode() {
                window.location.href = '/qr/goods/'+this.id+'?type=down&goods_name='+this.goods.goods_name;
            },

            getGoods() {
                if (this.id > 0) {
                    if (this.copy === false) {
                        this.disabled = true;
                    }

                    axios.get('/goods/get?id='+this.id).then((response) => {
                        this.loading = false;
                        this.resetPage();

                        if (response.data.code > 0) {
                            this.closeWindow('獲取商品資料失敗，是否關閉頁面？');
                            return
                        }

                        this.goods = response.data.response.data;

                        this.goods.price_type = this.goods.price_type.toString();
                        this.goods.recommend_icon = this.goods.recommend_icon == 1 ? true : false;
                        this.goods.distribution_type = this.goods.distribution_type.toString();

                        this.editName = this.editName + this.goods.goods_name;
                    }).catch((error) => {
                        this.loading = false;
                        this.closeWindow('獲取商品資料失敗，是否關閉頁面？錯誤訊息：' + error.toString());
                    });
                }
            },

            getAdmin() {
                axios.get('/admin/select?limit=10000&field=id,name,type&role=1').then((response) => {
                    this.resetPage();
                    this.loading = false;

                    if (response.data.code === 0) {
                        if (response.data.response.admin.length === 0) {
                            this.closeWindow('供應商列表爲空,是否關閉頁面？');
                            return;
                        }

                        this.admins = response.data.response.admin;
                        return;
                    }

                    this.closeWindow('獲取供應商失敗，是否關閉頁面？');
                }).catch(function () {
                    this.loading = false;

                    if(this.again > 3) {
                        this.closeWindow('獲取供應商失敗，是否關閉頁面？');
                    }

                    this.again += 1;
                });
            },

            getCate() {
                axios.get('/goods/cate/select?limit=1000') .then((response) => {
                    this.resetPage();
                    this.loading = false;

                    if (response.data.code === 0) {
                        if(response.data.response.list.length === 0) {
                            this.closeWindow('商品分類未空，是否關閉頁面？');
                            return;
                        }

                        this.categories = response.data.response.list;
                        return;
                    }

                    this.closeWindow('獲取商品分類失敗，是否關閉頁面？');
                }).catch(() => {
                    this.loading = false;

                    if (this.again > 3) {
                        this.closeWindow('獲取商品分類失敗，是否關閉頁面？');
                    }

                    this.again += 1;
                });
            },

            resetPage() {
                this.again = 0;
            },

            goBack(){
                this.openBookRefresh('直接返回將不會儲存本業數據，是否返回？', () => {
                    this.$router.push({path:'/goods/list'});
                });
            },

            closeWindowFuc() {
                let userAgent = navigator.userAgent;
                if (userAgent.indexOf("Firefox") !== -1 || userAgent.indexOf("Chrome") !== -1) {
                    window.opener.location.reload(true);
                    window.location.href="about:blank";
                    window.close();
                    return
                }
                window.opener.location.reload(true);
                window.opener = null;
                window.open("", "_self");
                window.close();
            },

            closeWindow(message){
                let that = this;
                this.$nextTick(function () {
                    this.resetPage();
                    this.openBookRefresh(message, function () {
                        that.closeWindowFuc();
                    });
                });
            },

            doSubmit(action) {
                let h = this.$createElement;
                let actionName = action === 'online' ? '上架' : '存儲';

                this.$refs.goods.validate((result) => {
                    let url = this.id > 0 ? '/goods/update?id='+this.id+'&action='+action : '/goods/create?action='+action;

                    if (result) {
                        Tools.Dialog(this).openSelfDialog((callback) => {
                            axios.post(url, this.goods).then((response) => {
                                if(response.data.code > 0) {
                                    this.openGDWarning(callback);
                                    return;
                                }

                                callback();
                                this.openGDDialog('success', () => {
                                    setTimeout(() => {
                                        this.closeWindowFuc();
                                    }, 2000);
                                }, '儲存成功');
                            }).catch(() => {
                                this.openGDWarning(callback);
                            });
                        }, h('p', null, [
                            h('span', null, '確定要'+actionName+'商品 '),
                            h('span', {style: 'color: teal'}, this.goods.goods_name),
                            h('span', null, ' 嗎？')
                        ]), '操作中...', '確定');
                    }
                });
            },

            copyGoods() {
                let h = this.$createElement;

                Tools.Dialog(this).openSelfDialog((callback) => {
                    axios.get('/goods/copy/'+this.id).then((response) => {
                        let insertId = response.data.response;

                        if(response.data.code > 0 || insertId <= 0) {
                            this.openGDWarning(callback);
                            return;
                        }

                        callback();
                        this.$router.push({path:'/edit/goods/'+insertId, query:{copy:1}}, () => {
                            window.location.reload();
                        });
                    }).catch((error) => {
                        this.openGDWarning(callback, error.toString());
                    });
                }, h('p', null, [
                    h('span', null, '確定要複製商品 '),
                    h('span', {style: 'color:#409EFF'}, this.goods.goods_name),
                    h('span', null, ' 嗎？確認後將直接進入該複製商品的編輯頁面。')
                ]), '儲存中...', '確定');
            },

            openBookRefresh(message, callback) {
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
                }).then(action => {
                    //執行完畢
                    //console.log(action);
                }).catch(e => {
                    //執行異常
                    //console.log(e)
                });
            },

            handleSuccess(res) {
                this.isUploading = false
                this.goods.goods_cover.push(res.response);
            },

            handleLimit() {
                Tools.Dialog(this).openWarning(null, '圖片最多可上傳10張');
            },

            handleRemove(file) {
                let index = this.goods.goods_cover.indexOf(file);
                if(index !== -1) {
                    this.goods.goods_cover.splice(index, 1)
                }
            },

            handleError(err, file) {
                this.isUploading = false
                let index = this.goods.goods_cover.indexOf(file);
                if(index !== -1) {
                    this.goods.goods_cover.splice(index, 1)
                }
            },

            createReader(file, error, success) {
                let reader = new FileReader;
                reader.onload = function (evt) {
                    let image = new Image();
                    image.onload = function () {
                        let imageType = ['image/jpeg', 'image/png'];
                        if (imageType.indexOf(file.type) < 0) {
                            error('圖片格式不符，請重新選擇');
                            return;
                        }

                        if (file.size / 1024 / 1024 > 2) {
                            error('上傳的圖片大小不能超過 2MB!');
                            return;
                        }

                        success();
                    };
                    image.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            },

            handleFileChange(file, files) {
                if (!("checked" in file)) {
                    let that = this;
                    this.createReader(file.raw, function (message) {
                        that.$message.error(message);
                        files.splice(-1);
                    }, function () {
                        that.isUploading = true
                        file.checked = true;
                        that.$refs.upload.submit();
                    });
                }
            },

            openGDWarning(callback,message){
                if(typeof(message) === 'undefined') {
                    message = '操作失敗，請檢查'
                }
                this.openGDDialog('warning',callback,message);
            },

            openGDDialog(type,callback,message) {
                this.$message({
                    type: type,
                    message: message
                });
                if(typeof(callback) === 'function') {
                    callback();
                }
            },
        }
    }
</script>

<style scoped>
    .el-form{
        width: 80%;
        margin: 0 auto;
    }
</style>
