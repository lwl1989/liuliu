<template>
    <div id="app" style="padding:20px;">
        <el-row>
            <el-card class="box-card">
                <div style="display: inline;float: left;">
                    <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{title}}</span></div>
				
                    <div>填寫時間:{{open_time}}</div>
                    <div>已填寫人數:{{user_count}}人</div>
                </div>
               
            </el-card>

            <el-col style="margin-top:20px;">
                <el-row>
                    <el-button type="primary" size='small' @click="reloadPage">重新載入</el-button>
                    <el-button type="primary" size='small' @click="exportExcel">下載清冊</el-button>
                    <!--<el-button type="primary" size='small' @click="">建立報到群組</el-button>-->
                </el-row>
            </el-col>
			 <el-col style="margin-top:20px;" :label-width="formLabelWidth">
                <div style="margin-left:5px; padding:20px; color: #409EFF;">
                    問題
                </div>
                <template  v-for="(item, index) in answers">
				<div  style="clear:both;"></div> 
                    <div style="margin-left:10px; padding:10px; ">
                      {{item.key}}.   {{item.title}}  <el-button v-if="item.type_ex == 1" type="primary" size='small'  @click="userdata(item.question_id,item.key)">下載</el-button>
                    </div>
					<template  v-for="(val, index) in item.alldata">
                    <div  style="margin-left:30px; padding:10px; float:left">
					{{val.option}}<el-button type="text">{{val.sum}}</el-button>
                      
                    </div>    
					 </template>
                  
                </template>
				
            </el-col>
        </el-row>
		
		
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools';
    import Dialog from '../../tools/element-ui-dialog';

    export default {
        name: "MessageActivityOfflineList",

        data : function () {
            return {
                id:  this.$route.params.id,
                loading : true,
                search : {
                    aid : this.$route.params.id,
                    sex : '-1',
                    page : 1,
                    type : 'mobile',
                    limit : 10,
                    keys : ''
                },
                lists : [],
                count : {},
				answers:[],
                total : 0,
                activity : {},
                activityQr:'',
				open_time:'',
                title: '',
				user_count:'',
				formLabelWidth : '100px',
            };
			
        },
        mounted: function () {
            this.$nextTick(function () {
                this.getQr();
            })
        },
		
        methods : {
			newdata:function (){
				 axios.get('/question/questionnaireselect/'+this.id).then((response) => {
				 this.open_time=response.data.response.open_time;
				 this.user_count=response.data.response.user_count;
				 this.title=response.data.response.title;
				 if (response.data.code == 0) {
                        this.answers = response.data.response.data;
                    }
				
                    //this.total = response.data.response.count;
                }).catch((error) => {

                });
			},
			userdata:function (question_id,key){
				 window.open('/question/questionnaiuserdata/'+question_id+'/'+this.id+'/'+key);
				
			},
			exportExcel(){
               window.open('question/questionnaireexport/'+this.id);
            },
            getQr(){
                axios.get('/api/qr/active/'+this.id).then((response) => {
                    if(response.data.code === '0') {
                        let content = response.data.response.content;
                        this.activityQr = 'data:image/png;base64,'+content;
                    }
                }).catch(function (error) {
                });
            },
            doSearch : function () {
                this.search.page = 1;
                this.loadData(this.search.page);
            },
            loadData : function (page) {
				
                let queryString = Tools.BuildQueryString(this.search, page);

                axios.get('/activity/offlineUserCount'+queryString).then((response) => {
                    this.total = response.data.response.count;
                }).catch((error) => {

                });

                this.changeLoadStatus(true);
                axios.get('/activity/offlineUserList'+queryString).then((response) => {
                    this.changeLoadStatus(false);
                    this.lists = response.data.response.lists;
                    this.activity = response.data.response.activity;
                }).catch((error) => {
                    this.changeLoadStatus(false);
                });
            },

            changeLimit : function (limit) {
                this.search.limit = limit;
                this.loadData(1);
            },

            changeLoadStatus : function (status) {
                this.loading = status;
            },

            reloadPage : function () {
                window.location.reload();
            }
        },

        created : function () {
            this.newdata();
        },
    }
</script>

<style scoped>

</style>