<template>
    <div id="app" style="margin:20px 0 0;">
        <el-form :model="adminForm" :rules="rules" ref="adminForm" v-loading="loading" label-width="150px" size="medium">
            <el-form-item>
                <el-button @click="closeAdCompany">取 消</el-button>
                <el-button plain type="primary" @click="submitCompanyTemp">列 印</el-button>
                <el-button plain type="primary" @click="submitCompany">儲 存</el-button>
                <el-button plain v-if="id > 0" type="primary" @click="resetShopPassword">重設密碼</el-button>
            </el-form-item>

            <div id="generateCanvas">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="統一編號" prop="account">
                            <el-input v-model="adminForm.account" :disabled="disabled"></el-input>
                        </el-form-item>

                        <el-form-item v-if='adminForm.type === "2"' label="接受金幣轉贈" prop="is_accept_gold">
                            <el-radio-group v-model="adminForm.is_accept_gold">
                            <el-radio :value="1" label="1">開啟</el-radio>
                            <el-radio :value="2" label="2">關閉</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <template>
                            <el-form-item v-if='!disabled' label="密碼" prop="password">
                                <el-input v-model="adminForm.password" max="30"></el-input>
                            </el-form-item>
                        </template>

                        <el-form-item label="狀態" prop="status">
                            <el-select v-model="adminForm.status" placeholder="啓用">
                                <el-option label="啓用" value="1" key="1"></el-option>
                                <el-option label="停用" value="2" key="2"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="類型" prop="type">
                            <el-select v-model="adminForm.type">
                                <el-option label="特約商店" value="1"></el-option>
                                <el-option label="公益團體" value="2"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="商店名稱" prop="name">
                            <el-input v-model="adminForm.name"></el-input>
                        </el-form-item>
                    </el-col>

                    <el-col v-if="id > 0" :span="4">&nbsp;</el-col>
                    <el-col v-if="adminQrcode !== ''" :span="8">
                        <div>
                            <img :src="adminQrcode" width="200" height="200"/>
                        </div>
                        商品兌換QR Code&nbsp;<el-button type="primary" size="mini" @click="downQRCode">下載</el-button>
                    </el-col>
                </el-row>

                <el-form-item label="商店頭像" prop="cover_url">
                    {{adminForm.cover_url}}
                    <img v-if="adminForm.cover_url !== ''" :src="adminForm.cover_url" width="320" height="150" />
                    <el-upload
                            action="/shop/cover"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept=".jpg,.jpeg,.png,.PNG,.JPG,.JPEG"
                            ref="upload"
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

                <el-form-item label="兌換驗證碼" prop="exchange_code">
                    <el-input v-model="adminForm.exchange_code"></el-input>
                </el-form-item>

                <el-form-item label="電話">
                    <el-col :span="6">
                        <el-form-item prop="tel">
                            <el-input v-model="adminForm.tel"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col class="line" :span="2" :offset="1">分機</el-col>
                    <el-col :span="6">
                        <el-form-item prop="tel_ext">
                            <el-input v-model="adminForm.tel_ext"></el-input>
                        </el-form-item>
                    </el-col>
                </el-form-item>

                <el-form-item label="國碼" prop="code">
                    <el-select v-model="adminForm.code">
                        <el-option
                                v-for="(item, index) in countryCode"
                                :label="item.zh_name+' +'+item.code"
                                :value="item.id"
                                :key="index"
                        ></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item prop="mobile" label="手機號碼">
                    <el-input v-model="adminForm.mobile"></el-input>
                </el-form-item>

                <el-form-item label="行政區" prop="area">
                    <el-select v-model="adminForm.area" placeholder="請選擇行政區">
                        <el-option  v-for="(item,key) in areas" :key="key" :value="key" :label="item" >
                            {{item}}
                        </el-option>
                    </el-select>
                    <el-button type="primary" @click="selectMap">選擇地圖</el-button>
                </el-form-item>

                <el-form-item label="地址" prop="address">
                    <el-input v-model="adminForm.address"></el-input>
                </el-form-item>

                <el-form-item label="電子郵件" prop="email">
                    <el-input v-model="adminForm.email"></el-input>
                </el-form-item>

                <el-form-item label="官網" prop="self_url">
                    <el-input v-model="adminForm.self_url"></el-input>
                </el-form-item>

                <el-form-item label="粉絲專頁" prop="facebook_url">
                    <el-input v-model="adminForm.facebook_url"></el-input>
                </el-form-item>

                <el-form-item label="可使用功能" prop="permissions">
                    <label style="cursor: pointer">
                        <input type="checkbox" v-model="adminForm.permissions" value="goods" style="position:relative;top:2px;"> 商品兌換
                    </label>
                </el-form-item>
            </div>
                <el-form-item label="最後異動資訊" v-if="adminForm.updater_account">
                    {{adminForm.update_time}}&nbsp;&nbsp; {{adminForm.updater_account}}
                </el-form-item>

        </el-form>

        <change-admin-password ref="ChangeAdminPassword"></change-admin-password>
        <google-maps-component ref="GoogleMapsComponent" v-on:select="selectLatlng"></google-maps-component>

        <div style="display:none" id="printContent"></div>
    </div>
</template>

<script>
    import {ShopRule} from '../../tools/element-ui-validate'
    import NewDialog from '../../tools/element-ui-dialog'
    import GoogleMapsComponent from '../GoogleMapsComponent'
    import ChangeAdminPassword from './ChangeAdminPasswordComponent'
    import html2canvas from 'html2canvas'
    import ElRadioGroup from "element-ui/packages/radio/src/radio-group";

    export default {
        components: {
            ElRadioGroup,
            GoogleMapsComponent,ChangeAdminPassword},

        data: function () {
            return {
                disabled:false,
                id: this.$route.params.id,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                rules: ShopRule,
                permissions: [],
                formLabelWidth: '220px',
                adminForm: {
                    account: '',
                    password:'',
                    role: '1',
                    type: '1',
                    status: '1',
                    name: '',
                    exchange_code: '',
                    tel: '',
                    tel_ext: '',
                    mobile: '',
                    email: '',
                    address: '',
                    self_url: '',
                    facebook_url: '',
                    area: '1',
                    permissions: ['goods'],
                    lat:0,
                    lng:0,
                    cover:'',
                    cover_url:'',
                    shop_id:'0',
                    code:2,
                    is_accept_gold:'1',
                    updater_account:''
                },
                notice:NewDialog(this),
                areas: {
                    '1': '臺東市',
                    '2': '成功鎮',
                    '3': '關山鎮',
                    '4': '長濱鄉',
                    '5': '海端鄉',
                    '6': '池上鄉',
                    '7': '東河鄉',
                    '8': '鹿野鄉',
                    '9': '延平鄉',
                    '10': '卑南鄉',
                    '11': '金峰鄉',
                    '12': '太麻里鄉',
                    '13': '大武鄉',
                    '14': '達仁鄉',
                    '15': '綠島鄉',
                    '16': '蘭嶼鄉',
                    '17': '其他地區'
                },
                adminQrcode: "",
                loading: true,
                covers:[],
                countryCode:[],
                except_width:400,
                except_height:400,
                updateBeforeAccount : '',
                updateBeforeArea : '',
                origin_address : ''
            }
        },

        watch : {
            'adminForm.cover_url' : function() {
                this.$nextTick(() => {
                    this.$refs.adminForm.validateField('cover_url');
                });
            },
        },

        mounted: function () {
            this.$nextTick(function () {
                this.getCountryCode();
                this.getAllPerm();
                if (parseInt(this.id) > 0) {
                    this.disabled = true;
                    this.getUser();
                }else{
                    this.loading = false;
                }
            })
        },

        methods: {
            selectMap() {
                if (this.id > 0) {
                    if (this.updateBeforeArea !== this.adminForm.area) {
                        if (this.adminForm.address !== '') {
                            this.$refs.GoogleMapsComponent.selectMap(null, this.adminForm.address);
                            return;
                        }
                        this.$refs.GoogleMapsComponent.selectMap(this.adminForm.area, null);
                        return;
                    }

                    if (this.adminForm.address !== '' && this.adminForm.address !== this.origin_address) {
                        this.$refs.GoogleMapsComponent.selectMap(null, this.adminForm.address);
                        return;
                    }

                    this.$refs.GoogleMapsComponent.selectMapByPosition(this.adminForm.lat, this.adminForm.lng);
                    return;
                }
                
                if (this.adminForm.address !== '') {
                    this.$refs.GoogleMapsComponent.selectMap(null, this.adminForm.address);
                    return;
                }

                this.$refs.GoogleMapsComponent.selectMap(this.adminForm.area);
            },

            selectLatlng(select) {
                this.adminForm.lat = select.lat
                this.adminForm.lng = select.lng
            },

            getQr(){
                if(this.adminForm.shop_id !== "0") {
                    axios.get('/api/qr/shop/' + this.adminForm.shop_id).then((response) => {
                        if (response.data.code === '0') {
                            let content = response.data.response.content;
                            this.adminQrcode = 'data:image/png;base64,' + content;
                        }
                    }).catch(function (error) {
                    });
                }
            },

            downQRCode() {
                window.location.href = '/api/qr/shop/'+this.adminForm.shop_id+'?type=down&shop_name='+this.adminForm.name;
            },

            getCountryCode() {
                axios.get('/country/code').then((response) => {
                    this.countryCode = response.data.response;
                }).catch(error => {
                    NewDialog(this).openError(null, error.toString())
                })
            },

            getAllPerm() {
                let that = this;
                axios.get('/admin/perm').then(function (response) {
                    that.permissions = response.data.response;
                    if(that.adminForm.account !== '') {
                        that.loading = false;
                    }
                }).catch(function (error) {
                });
            },

            getUser() {
                axios.get('/admin/get?id='+this.id).then((response) => {
                    this.loading = false;

                    this.adminForm = response.data.response.data;
                    this.origin_address = this.adminForm.address;
                    this.updateBeforeArea = this.adminForm.area.toString();
                    this.updateBeforeAccount = this.adminForm.account;
                    this.adminForm.role = this.adminForm.role.toString();
                    this.adminForm.area = this.adminForm.area.toString();
                    this.adminForm.status = this.adminForm.status.toString();
                    this.adminForm.type = this.adminForm.type.toString();
                    this.adminForm.is_accept_gold = this.adminForm.is_accept_gold.toString();
                    this.adminForm.code = this.adminForm.code;
                    this.getQr();
                }).catch(() => {
                    this.loading = false;
                });
            },

            submitCompanyTemp() {
                //this.adminForm.status = 0;
                //this.submitCompany();

                this.printPage();
            },

            doSubmit() {
                const h = this.$createElement;
                let message = h('p', null, [
                    h('span', null, '確定要儲存帳號 '),
                    h('span', {style: 'color: teal'}, this.adminForm.account),
                    h('span', null, ' 嗎？')
                ]);

                if (this.adminForm.permissions.length === 0) {
                    message = '尚未勾選可使用功能，是否要儲存?'
                }

                this.notice.openSelfDialog((callback)=> {
                    let url = parseInt(this.id) > 0 ? '/admin/update?id=' + this.id : '/admin/create';
                    console.log(this.adminForm);
                    axios.post(url, this.adminForm)
                        .then( (response)=> {
                            let code = response.data.code;
                            if (code === 0) {
                                this.notice.openSuccess( () => {
                                    setTimeout(()=> {
                                        callback();
                                        this.closeAdminDetailWindowFuc();
                                    }, 2000);
                                }, '儲存成功');
                            } else {
                                if(code === 500311) {
                                    this.notice.openWarning(callback, '手機號未被註冊');
                                }else{
                                    this.notice.openWarning(callback, '操作失敗');
                                }
                            }
                        }).catch((error)=> {
                        console.log(error.toString());
                        this.notice.openWarning(callback, '操作失敗');
                    });
                }, message);
            },

            submitCompany() {
                let that = this;


                this.$refs.adminForm.validate((result) =>{
                    if (result) {
                        let position = this.$refs.GoogleMapsComponent.getSelect(true);
                        if (position.lat === 0) {
                            if(this.adminForm.lat === 0) {
                                this.notice.openWarning(null, '請選擇地圖位置');
                                return;
                            }
                        } else {
                            this.adminForm.lat = position.lat;
                            this.adminForm.lng = position.lng;
                        }


                        let url = '/admin/create';
                        if(parseInt(that.id) > 0){
                            url = '/admin/update?id=' + that.id;
                            this.doSubmit(url);
                        }else{
                            axios.get('/admin/check/username?u='+that.adminForm.account+'&code='+that.adminForm.exchange_code).then((res) => {
                                if(res.data.response.has) {
                                    //需求變更：兌換碼可以重複
                                    NewDialog(that).openWarning(null, '帳號重複');
                                } else {
                                    this.doSubmit(url);
                                }
                            });
                        }
                        // axios.get('/admin/check/username?u='+that.adminForm.account).then((res) => {
                        //     if ((parseInt(that.id) === 0 || that.updateBeforeAccount !== that.adminForm.account) &&
                        //         res.data.response.has
                        //     ) {
                        //         NewDialog(that).openWarning(null, '帳號重複');
                        //     } else {
                        //
                        //     }
                        // })
                    }
                });
            },

            resetShopPassword(){
                this.$refs.ChangeAdminPassword.doOpenPasswordPage(this.id);
            },

            closeAdminDetailWindowFuc() {
                let userAgent = navigator.userAgent;
                if (userAgent.indexOf("Firefox") !== -1 || userAgent.indexOf("Chrome") !== -1) {
                    window.opener.location.reload(true);
                    window.location.href="about:blank";
                    window.close();
                } else {
                    window.opener.location.reload(true);
                    window.opener = null;
                    window.open("", "_self");
                    window.close();
                }
            },

            closeAdCompany() {
                this.notice.openSelfDialog(()=> {
                    this.closeAdminDetailWindowFuc()
                }, "資料尚未儲存，是否要關閉本頁面？") ;
            },

            handleRemove(file, fileList) {},

            handleError(err, file, fileList) {},

            handleSuccess : function(res) {
                this.adminForm.cover = res.response.path;
                this.adminForm.cover_url = res.response.url;
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

            printPage() {
                this.$nextTick(() => {
                    html2canvas(
                        document.querySelector("#generateCanvas"),
                        {useCORS:true, allowTaint:false}
                    ).then(canvas => {
                        let printContent =document.getElementById('printContent');
                        let image = new Image();
                        image.src = canvas.toDataURL();
                        image.onload = function () {
                            printContent.appendChild(image);
                            let oldContent = document.body.innerHTML;
                            document.body.innerHTML = printContent.innerHTML;

                            window.print();
                            window.location.reload();

                            document.body.innerHTML = oldContent;
                        };

                        return false;
                    });
                });
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group {
        font-size: 12px;
    }
    .el-form{
        width: 80%;
        margin: 0 auto;
    }
</style>