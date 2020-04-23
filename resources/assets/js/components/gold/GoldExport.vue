<template>
    <div id="app">
        <!--  商品按鈕列 -->
        <el-form :model="search" ref="search">
            <el-form-item :label-width="formLabelWidth"  label="報表名稱" placeholder="請選擇">
                <el-col :span="30">
                    <el-select size="30" v-model="search.table" @change="changeTable">
                        <el-option label="問卷填寫成效報表" :value="1"></el-option>
                        <el-option label="活動舉辦成效報表" :value="2"></el-option>

                        <el-option v-if="role==3" label="商品上架狀態與兌換量報表" :value="3"></el-option>
                        <el-option v-if="role==3" label="商品兌換資料表" :value="4"></el-option>
                        <el-option label="推播訊息滿意度回饋意見報表" :value="5"></el-option>

                        <el-option label="推播訊息滿意度調查報表" :value="6"></el-option>
						<el-option  label="推播訊息讀取率" :value="7"></el-option>
						<el-option label="臺東金幣帳戶使用報表" :value="8"></el-option>
						<el-option label="臺東金幣帳戶報表" :value="9"></el-option>

                        <el-option v-if="role==3" label="店家商品報表" :value="11"></el-option>
                        <el-option v-if="role==3" label="請款總表" :value="10"></el-option>
						<!--<el-option label="用戶停車繳費記錄" :value="12"></el-option>-->
						<el-option v-if="role==3" label="介接廠商請款報表" :value="13"></el-option>
						<el-option v-if="role==3" label="推薦人成效報表" :value="14"></el-option>
                    </el-select>
                </el-col>
            </el-form-item>
			 <el-form-item v-if="search.table == 13" :label-width="formLabelWidth"  :label="tagName" placeholder="請選擇">
                <el-col :span="15">
                    <el-radio v-model="search.firm" label="32">路邊停車</el-radio>
                    <el-radio v-model="search.firm" label="33">新生停車場</el-radio>
                </el-col>
            </el-form-item>
            <el-form-item v-if="search.table == 3" :label-width="formLabelWidth"  :label="tagName" placeholder="請選擇">
                <el-col :span="15">
                    <el-checkbox-group v-model="search.company" @change="handleCheckedCitiesChange">
                        <el-checkbox :label="1" :key="1">未上架</el-checkbox>
                        <el-checkbox :label="2" :key="2">已上架</el-checkbox>
                    </el-checkbox-group>
                </el-col>
            </el-form-item>

            <el-form-item v-if="search.table==4 || search.table==3" :label-width="formLabelWidth" :label="dateTagName+'（起）'" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.begin"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="兌換時間（起）"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>
            <el-form-item v-if="search.table==4 || search.table==3" :label-width="formLabelWidth" :label="dateTagName+'（迄）'" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.end"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="兌換時間（迄）"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>
		
            <el-form-item v-if="search.table != '' && search.table != 14 && search.table != 12 && search.table != 13 && search.table != 4 && search.table != 3 && search.table != 5" :label-width="formLabelWidth"  :label="tagName" placeholder="請選擇">
                <el-col :span="15" style="height: 300px;border: 1px solid;border-color:gainsboro;border-radius:5px;overflow-x: hidden;">
                    <el-scrollbar style="height: 100%;padding-left: 10px;overflow-x: hidden;">
                        <el-checkbox style="margin-left: 10px;" :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange">全選</el-checkbox>
                        <div></div>
                        <el-checkbox-group v-model="search.company" @change="handleCheckedCitiesChange">
                            <el-checkbox style="margin-left: 10px;" v-for="com in company" :label="com.id" :key="com.id">{{com.name}}</el-checkbox>
                        </el-checkbox-group>
                    </el-scrollbar>
                </el-col>

            </el-form-item>

            <el-form-item v-if="search.table==5" :label-width="formLabelWidth" :label="tagName" >

                <el-col :span="15">
                    <el-select v-model="search.company" @change="changeCompany">
                        <template  v-for="com in company">
                            <el-option :label="com.name" :value="com.id"></el-option>
                        </template>
                    </el-select>
                </el-col>
            </el-form-item>

            <el-form-item v-if="search.table==5" :label-width="formLabelWidth" label="業務名稱" >

                <el-col :span="15">
                    <el-select v-model="search.msg_id">
                        <template  v-for="msg in messages">
                            <el-option :label="msg.name" :value="msg.id"></el-option>
                        </template>
                    </el-select>
                </el-col>
            </el-form-item>

            <el-form-item v-if="search.table==10 || search.table == 11|| search.table == 13" :label-width="formLabelWidth" label="請款年度/月份" >
                <el-col :span="15">
                    <el-date-picker
                            type="month"
                            v-model="search.month"
                            format="yyyy-MM"
                            value-format="yyyy-MM"
                            start-placeholder="請款月份"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>
			<el-form-item v-if="search.table==7 || search.table==6 || search.table==2 || search.table==1" :label-width="formLabelWidth" label="推播時間(起)" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.begin"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="請款推播時間(起)"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>	
			<el-form-item v-if="search.table==7 || search.table==6 || search.table==2 || search.table==1" :label-width="formLabelWidth" label="推播時間(迄)" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.end"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="請款推播時間(迄)"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>
			<el-form-item v-if="search.table==14" :label-width="formLabelWidth" label="推薦時間(起)" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.begin"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="請款推薦時間(起)"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>	
			<el-form-item v-if="search.table==14" :label-width="formLabelWidth" label="推薦時間(迄)" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.end"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="請款推薦時間(迄)"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>
			<el-form-item v-if="search.table==12" :label-width="formLabelWidth" label="繳費時間(起)" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.begin"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="請款繳費時間(起)"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>	
			<el-form-item v-if="search.table==12" :label-width="formLabelWidth" label="繳費時間(迄)" >
                <el-col :span="15">
                    <el-date-picker
                            type="date"
                            v-model="search.end"
                            format="yyyy-MM-dd"
                            value-format="yyyy-MM-dd"
                            start-placeholder="請款繳費時間(迄)"
                    >
                    </el-date-picker>
                </el-col>
            </el-form-item>
            <el-form-item v-if="search.table==9 || search.table==8" :label-width="formLabelWidth" label="金幣帳戶期別" >

                <el-col :span="15">
                    <el-select v-model="search.stage_id" >
                        <template  v-for="item in stage">
                            <el-option :label="item.id+' ('+item.issue_time+'-'+item.expire_time+')'" :value="item.id"></el-option>
                        </template>
                    </el-select>
                </el-col>
            </el-form-item>
			<el-form-item v-if="this.showPage==1" :label-width="formLabelWidth"  >

                <el-col :span="15">
                     <el-pagination   @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage2" :page-sizes="[1000]" :page-size="100" layout="sizes, prev, pager, next" :total="total">
                    </el-pagination>
                </el-col>
            </el-form-item>
        </el-form>
        <el-row>
		
            <el-col :span="24" style="margin-top: 20px;margin-left: 200px;">
                <el-button @click="submit" type="primary">匯出</el-button>
            </el-col>
        </el-row>

    </div>
</template>

<script>
	
    const allCompany = [];

    export default {
        name: "gold-export",
        data: function () {
            return {
                search:{
                    table:'',
                    company:[],
					firm:[],
                    month:'',
                    stage_id:'',
                    begin:'',
                    end:'',
                    msg_id:''
                },
                formLabelWidth: '200px',
                currentPage: 1,
				currentPage2: 1,
				showPage: 0,
                pageSize: 10,
                total: 1000,
                company:[],
				firm:[],
                messages:[],
                checkAll:true,
                isIndeterminate:false,
                stage:[],
                tagName:'供應商',
                dateTagName:'兌換時間',
                role:''
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                this.getAdmin();
                this.goodsStag();
            })
        },
        methods: {
			 handleSizeChange(val) {
						console.log(`每页 ${val} 条`);
					  },
			  handleCurrentChange(val) {
				this.currentPage=val;
				console.log(`当前页: ${val}`);
			  },	
            changeTable:function(){
				this.search.firm = [];
                this.search.company = [];
                this.messages = [];
//                this.stage = [];
                this.company = [];
                this.search.month = '';
                this.search.begin = '';
                this.search.end = '';
				this.search.k = 0;
				this.search.stage_id = '';
                this.search.msg_id = '',
                this.checkAll = true;
				
                switch (this.search.table){
					case 13:
                        this.handleCompanyCurrentChange();
                        this.tagName='介接廠商';
                        break;
                    case 11:
                        this.handleCompanyCurrentChange();
                        this.tagName='供應商';
                        break;
                    case 10:
                        this.handleCompanyCurrentChange();
                        this.tagName='供應商';
                        break;
                    case 9:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
					case 8:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
					case 7:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
					case 6:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
                    case 5:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
                    case 4:
                        this.dateTagName='兌換時間';
                        break;
                    case 3:
                        this.dateTagName='最後異動日期';
                        this.tagName='商品狀態';
                        this.search.company = [1,2];
                        break;
                    case 2:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
                    case 1:
                        this.getDepartment();
                        this.tagName='業務單位';
                        break;
                }


            },
            changeCompany:function(){//獲取message->name

                let that = this;
                let url = '/message/send/getMsgMame?department_id='+this.search.company;
                if(that.search.table == 5){
                    that.search.msg_id = '';
                }
                axios.get(url) .then(function (response) {
                    that.messages = response.data.response;

                });
            },
            getAdmin() {
                let that = this;
                let url = '/admin/info';
                axios.get(url) .then(function (response) {
                    that.role = response.data.response.role;
                });
            },
            getDepartment() {
                let that = this;
                let url = '/department/getlist';

                if(that.search.table==9){
                    url += '?parent=1';
                }


                allCompany.length = 0;
                that.search.company = [];
                that.company = [];

                axios.get(url) .then(function (response) {

                    that.company = response.data.response;

                    if(that.search.table != 5) {
                        that.company.forEach(function (item, key) {

                            that.search.company.push(item.id);
                            allCompany.push(item.id);
                        })
                    }
                    if(that.role != 3 && (that.search.table == 8 ||that.search.table == 9)){
                        var departmentId = that.search.company.join(',');
                        let url = '/goods/stage?departmentId='+departmentId;

                        axios.get(url) .then(function (response) {
                            that.stage = response.data.response;
                        });
                    }
                });
            },
            handleCompanyCurrentChange(currentPage) {
                let that = this;
                let url = '/admin/select?role=1&page=1&limit=1000';
                allCompany.length = 0;
                that.search.company = [];
                that.company = [];
                axios.get(url) .then(function (response) {
                    that.company = response.data.response.admin;
                        that.company.forEach(function (item, key) {
                            that.search.company.push(item.id);
                            allCompany.push(item.id);
                        })
                });
            },
            goodsStag:function() {
                let that = this;
                let url = '/goods/stage';

                axios.get(url) .then(function (response) {
                    that.stage = response.data.response;

                });
            },
            handleCheckAllChange(val) {
                this.search.company = val ? allCompany : [];
                this.isIndeterminate = false;
            },
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.company.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.company.length;
            },
			submitstend(){
				var that= this;
						let url = '/gold/export/goodsAmountExport?company=' + this.search.company + '&begin=' + this.search.begin +'&end=' + this.search.end + '&table=' + this.search.table +'&count_export=1';
						axios.get(url) .then(function (response) {
							that.showPage=1;
							that.total=response.data.response.count_export;
							that.currentPage2=that.currentPage;
							alert('請選擇分頁點擊導出');
						});	
						
					
			},
			but_submitstend(){
				var that= this;
				var urls = '/gold/export/goodsAmountExport?company=' + that.search.company + '&begin=' + that.search.begin +'&end=' + that.search.end + '&table=' + that.search.table+'&count_export=0&currentPage=' + that.currentPage;
				window.location.href = urls;
							
			},
            submit:function(){

                if(this.search.table == ''){
                    this.$alert('尚未選擇報表名稱', '提示', {
                        confirmButtonText: '確定'
                    });
                }
                if(this.search.table == 5){
                    if(this.search.company == '' || this.search.company==null){
                        this.$alert('尚未選擇業務單位', '提示', {
                            confirmButtonText: '確定'
                        });
                        return false;
                    }
                    if(this.search.msg_id == '' || this.search.msg_id==null){
                        this.$alert('尚未選擇業務名稱', '提示', {
                            confirmButtonText: '確定'
                        });
                        return false;
                    }
                    var url = '/gold/export/goodsAmountExport?company='+this.search.company+'&table=' + this.search.table+'&msg_id=' + this.search.msg_id;
                    window.location.href = url;
                }
                if(this.search.table == 3){
                    if(this.search.company.length == 0){
                        this.$alert('尚未選擇商品狀態', '提示', {
                            confirmButtonText: '確定'
                        });
                        return false;
                    }
                    if(this.search.begin == null || this.search.end == null || this.search.begin == '' || this.search.end == '' || this.search.begin > this.search.end){
                        this.$alert('時間設定有誤', '提示', {
                            confirmButtonText: '確定'
                        });
                        return false;
                    }
                    var url = '/gold/export/goodsAmountExport?company='+this.search.company+'&begin=' + this.search.begin + '&end=' + this.search.end+'&table=' + this.search.table;
                    window.location.href = url;
                }
                if(this.search.table == 4){
                    if(this.search.begin == null || this.search.end == null || this.search.begin == '' || this.search.end == '' || this.search.begin > this.search.end){
                        this.$alert('時間設定有誤', '提示', {
                            confirmButtonText: '確定'
                        });
                        return false;
                    }

                    var url = '/gold/export/goodsAmountExport?begin=' + this.search.begin + '&end=' + this.search.end+'&table=' + this.search.table;
                    window.location.href = url;
                }
				if(this.search.table==10 || this.search.table == 11){

					 if(this.search.company.length == 0){
						this.$alert('尚未選擇供應商', '提示', {
							confirmButtonText: '確定'
						});

					}else if(this.search.month == '' || this.search.month == null){
						this.$alert('尚未選擇請款年度／月份', '提示', {
							confirmButtonText: '確定'
						});
					}else{
						var url = '/gold/export/goodsAmountExport?company=' + this.search.company + '&month=' + this.search.month + '&table=' + this.search.table+'&stage_id='+this.search.stage_id;
						window.location.href = url;
					}
				}
				if(this.search.table==9 || this.search.table==8){
					if(this.search.company.length == ''){
						this.$alert('尚未選擇業務單位', '提示', {
							confirmButtonText: '確定'
						});
					}else if(this.search.stage_id == ''){
						this.$alert('尚未選擇金幣期別', '提示', {
							confirmButtonText: '確定'
						});
					}else{
						var url = '/gold/export/goodsAmountExport?company=' + this.search.company + '&month=' + this.search.month + '&table=' + this.search.table+'&stage_id='+this.search.stage_id;
						window.location.href = url;
					}
					
					
				}
				
				if(this.search.table==7){
				
					if(this.search.company.length == ''){
						this.$alert('尚未選擇業務單位', '提示', {
							confirmButtonText: '確定'
						});
					}else if(this.search.begin == null || this.search.end == null || this.search.begin == '' || this.search.end == '' || this.search.begin > this.search.end){
						this.$alert('時間設定有誤', '提示', {
							confirmButtonText: '確定'
						});
					}else{
						if(this.showPage==1){
							this.but_submitstend();
						}else{
							this.submitstend();
						}
						
					}
					
					
				}
				if(this.search.table==6 || this.search.table==2 || this.search.table==1){
					if(this.search.company.length == ''){
						this.$alert('尚未選擇業務單位', '提示', {
							confirmButtonText: '確定'
						});
                    }else if(this.search.begin == null || this.search.end == null || this.search.begin == '' || this.search.end == ''
                            || this.search.begin > this.search.end){
						this.$alert('時間設定有誤', '提示', {
							confirmButtonText: '確定'
						});
					}else{

						var url = '/gold/export/goodsAmountExport?company=' + this.search.company
                                + '&begin=' + this.search.begin
                                + '&end=' + this.search.end
                                + '&table=' + this.search.table;
						window.location.href = url;
					}
					
					
				}
				if(this.search.table==12){
					if(this.search.begin == '' || this.search.begin == null){
						this.$alert('尚未選擇繳費時間(起)', '提示', {
							confirmButtonText: '確定'
						});
                    }else if(this.search.begin == null || this.search.end == null || this.search.begin == '' || this.search.end == '' || this.search.begin > this.search.end){
						this.$alert('時間設定有誤', '提示', {
							confirmButtonText: '確定'
						});
					}else{
						var url = '/gold/export/goodsAmountExport?' 
                                + 'begin=' + this.search.begin
                                + '&end=' + this.search.end
                                + '&table=' + this.search.table;
						window.location.href = url;
					}
					
					
				}
				
				if(this.search.table==13){
					if(this.search.firm.length == 0){
						this.$alert('尚未選擇介接廠商', '提示', {
							confirmButtonText: '確定'
						});

					}else if(this.search.month == '' || this.search.month == null){
						this.$alert('尚未選擇請款年度／月份', '提示', {
							confirmButtonText: '確定'
						});
					}else{
						var url = '/gold/export/goodsAmountExport?' 
                                + 'firm=' + this.search.firm
                                + '&month=' + this.search.month
                                + '&table=' + this.search.table;
						window.location.href = url;
					}
					
				}
				
				if(this.search.table==14){
                    if(this.search.begin == null || this.search.end == null || this.search.begin == '' || this.search.end == '' || this.search.begin > this.search.end){
						this.$alert('時間設定有誤', '提示', {
							confirmButtonText: '確定'
						});
					}else{
						var url = '/gold/export/goodsAmountExport?' 
                                + 'begin=' + this.search.begin
                                + '&end=' + this.search.end
                                + '&table=' + this.search.table;
						window.location.href = url;
					}
					
					
				}
               
            }
        },
    }
</script>

