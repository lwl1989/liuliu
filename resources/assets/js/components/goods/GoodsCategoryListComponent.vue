<template>
    <div id="app" style="margin-top: 20px;">
        <el-row>
                <el-button size='small' type="primary" icon="el-icon-circle-plus" @click="onCateAdd">新增</el-button>
        </el-row>

        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="categories" stripe style="width:100%" v-loading="loading">
                    <el-table-column prop="name" label="商品類別名稱">
                        <template slot-scope="scope" >
                            <div v-if="scope.row.id === -1 || scope.row.edit !== 0">
                                <el-input size="small" v-model="scope.row.name"  placeholder="請輸入名稱，長度限制50字" :maxlength="50" ></el-input>
                            </div>
                            <div v-else>
                                {{ scope.row.name }}
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="e_name" label="英文名稱" >
                        <template slot-scope="scope" >
                            <div v-if="scope.row.id === -1 || scope.row.edit !== 0">
                                <el-input size="small" v-model="scope.row.e_name"  placeholder="請輸入英文名稱" ></el-input>
                            </div>
                            <div v-else>
                                {{ scope.row.e_name }}
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="sort" label="商品類別排序">
                        <template slot-scope="scope" v-if="scope.row.id > 0 && scope.row.edit === 0">
                            <el-button v-if='scope.$index <= (categories.length - 3)' @click="sort(scope.row.id, 'down')" size="mini" type="primary" icon="el-icon-arrow-down" circle></el-button>
                            <el-button v-if='scope.$index > 0' @click="sort(scope.row.id, 'up')"  size="mini" type="primary" icon="el-icon-arrow-up" circle></el-button>
                        </template>
                    </el-table-column>

                    <el-table-column label="操作">
                        <template slot-scope="scope">
                            <template v-if="scope.row.id === -1 || scope.row.edit !== 0">
                                <el-button type="success" size="mini" @click="onCateSave(scope.$index)">儲存</el-button>
                            </template>

                            <template v-else-if="scope.row.id > 0">
                                <el-button type="primary" size="mini" @click="onCateEdit(scope.$index)">編輯</el-button>
                            </template>

                            <template v-if="scope.row.id === -1 || scope.row.edit !== 0">
                                <el-button type="warning" size="mini" @click="onCateEditDisable(scope.$index)">取消</el-button>
                            </template>

                            <template v-if="scope.row.id > 0 && scope.row.edit === 0">
                                <el-button type="danger" size="mini" @click="onCateDel(scope.$index)">刪除</el-button>
                            </template>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools';

    export default {
        name: "goods-category-list-component",

        data: function () {
            return {
                categories:[],
                loading : true,
                isSubmit: false,
                isAdd: false,
                dialog: new Tools.Dialog(this)
            }
        },

        mounted: function () {
            this.$nextTick(function() {
                this.getAllCate();
            })
        },

        methods: {
            getAllCate() {
                this.loading = true;
                axios.get('/goods/cate/select?limit='+100).then((response) => {
                    this.loading = false;
                    this.categories = response.data.response.list;
                }).catch((error) => {
                    this.loading = false;
                    this.$emit('warning', function () {}, error.toString());
                });
            },

            sort(id, direction) {
                if (this.isSubmit) {
                    return;
                }

                this.isSubmit = true;
                axios.put('/goods/cate/sort', {id:id,direction:direction}).then((res) => {
                    this.isSubmit = false;
                    if (res.data.code > 0) {
                        return Tools.Dialog(this).openError(null, '操作失敗');
                    }

                    this.getAllCate();
                }).catch((error) => {
                    this.isSubmit = false;
                    Tools.Dialog(this).openError(null, error.toString());
                });
            },

            onCateAdd() {
                if(this.categories.length >= 20) {
                    return Tools(this).openWarning(null, '最多支持20個商品分類');
                }
                this.isAdd = true
                this.categories.splice(0, 0, {
                    id: -1,
                    name: '',
                    e_name: '',
                    cover: '',
                    sort: this.categories.length > 1 ? this.categories[this.categories.length-2].sort + 1 : 1,
                    edit: 0 //0正常 1編輯 2提交中
                });
            },

            onCateSave(index) {
                if (!this.categories[index].name) {
                    return Tools.Dialog(this).openError(null, '分類名稱為必填欄位');
                }

                if (!this.categories[index].e_name) {
                    return Tools.Dialog(this).openError(null, '分類英文名稱為必填欄位');
                }

                if (this.categories[index].edit === 2) {
                    return;
                }

                let url = '/goods/cate/update';
                if (this.categories[index].id === -1) {
                    url = '/goods/cate/create';
                }

                this.categories[index].edit = 2;
                axios.post(url, this.categories[index]).then((response) => {
                    this.categories[index].edit = 1;
                    if(response.data.code > 0) {
                        return Tools.Dialog(this).openError(null, '儲存失敗，請重試或聯系管理員');
                    }

                    this.categories[index].edit = 0;
                    if (this.categories[index].id === -1) {
                        this.categories[index].id = response.data.response.id;
                        this.categories[index].sort = response.data.response.sort;

                        this.categories.splice(this.categories.length - 1, 0, this.categories[index]);
                        this.categories.splice(index, 1);
                        this.isAdd = false
                    }
                }).catch((error) => {
                    Tools.Dialog(this).openError(null, error.toString());
                });
            },

            onCateEdit(index){
                this.categories[index].edit = 1;
            },
            onCateEditDisable(index){
                if(this.isAdd){
                    this.categories.splice(0, 1)
                    this.isAdd =false
                }
                this.categories[index].edit = 0;
            },

            onCateDel(index) {
                let h = this.$createElement;
                let that = this
                this.dialog.openSelfDialog((closeCallback) => {
                    if (that.categories[index].id === -1) {
                        that.categories.splice(index, 1);
                    } else { //判斷id存在，如果底下有商品不讓刪除
                        if (that.categories[index].edit !== 0) {
                            that.categories[index].edit = 0;
                            closeCallback()
                            return;
                        }

                        axios.get('/goods/cate/delete/check?id='+that.categories[index].id).then((response) =>{
                            if(response.data.code > 0) {
                                closeCallback()
                                return Tools.Dialog(this).openWarning(null, '此分類下有未刪除商品，不能刪除');
                            }
                            that.getAllCate();
                            closeCallback()
                        }).catch((error)=> {
                            Tools.Dialog(this).openWarning(null, error.toString());
                            closeCallback()
                        });
                    }
                }, h('p', null, [
                    h('span', null, '確定刪除'+that.categories[index].name+'嗎?')
                ]), '執行中...', '確定');

            }
        }
    }
</script>

<style scoped>

</style>