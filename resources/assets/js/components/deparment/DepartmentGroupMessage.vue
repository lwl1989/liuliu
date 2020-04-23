<template>
    <el-dialog :visible.sync="preferVisible" :modal-append-to-body="false" :close-on-click-modal="false">
        <el-row>
            <el-card class="box-card">
                <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{item.title ? item.title : item.name}}</span></div>
                <div v-if="type === 0">填寫時間：{{item.open_time}}~{{item.close_time}}</div>
                <div v-if="type === 1 && item.active">填寫時間：{{item.online_open_time}}~{{item.online_close_time}}</div>
                <div v-if="type === 1 && item.is_live">報到時間：{{item.open_time}}~{{item.close_time}}</div>
                <div v-if="type === 1 && item.active">已填寫人數：{{total}}人</div>
                <div v-if="type === 1 && item.is_live">已報到人數：{{live_total}}人</div>
            </el-card>
            <el-row style="padding-top: 20px" v-if="item.list.length > 0">
                <template v-for="(question,index) in item.list">
                    <template v-if="question.type === 1 || question.type === 2">
                        <div class="answer_box" v-if="question.option_type === 3">
                            <div class="question">
                                <span class="q_i">{{index+1}}.</span>
                                <el-checkbox v-model="question.checked">{{question.title}}</el-checkbox>
                                <span style="text-decoration:none;color: #00afff;">{{question.options[0].count}}</span>
                            </div>
                        </div>
                        <div class="answer_box" v-else>
                            <div class="question">
                                <span class="q_i">{{index+1}}.</span>
                                <span class="q_w">{{question.title}}</span>
                            </div>
                            <div class="a_item">
                                <template v-for="(value,key) in question.options">
                                    <el-checkbox v-model="value.checked"> {{key+1}}. {{value.option}}
                                        <span style="text-decoration:none;color: #00afff;">{{value.count}}</span>
                                    </el-checkbox>
                                </template>
                            </div>
                        </div>

                    </template>

                    <template v-else-if="question.type === 4">
                        <div class="answer_box">
                            <div class="question">
                                <span class="q_i">●</span>
                                <el-checkbox v-model="question.checked" show-overflow-tooltip
                                             style="white-space:normal">{{question.title}}
                                </el-checkbox>
                                <span
                                        style="text-decoration:none;color: #00afff;">{{question.options[0].count}}</span>
                            </div>

                        </div>
                    </template>

                    <template v-else-if="question.type === 5">
                        <div class="answer_box">
                            <div class="question">
                                <span class="q_i">●</span>
                                <el-checkbox v-model="question.checked" show-overflow-tooltip
                                             style="white-space:normal">{{question.title}}
                                </el-checkbox>
                                <span style="text-decoration:none;color: #00afff;">{{question.options[0].count}}</span>
                            </div>
                        </div>
                    </template>
                </template>
            </el-row>

            <el-row>
                <template v-if="item.list.length === 0 && item.active">
                    <div class="answer_box">
                        <div class="a_item">
                            <template>
                                <el-checkbox v-model="online">已填寫人數：
                                    <span style="text-decoration:none;color: #00afff;">{{total}}</span>
                                </el-checkbox>
                            </template>
                        </div>
                    </div>
                </template>

                <template>
                    <div v-if="item.is_live && item.active">
                        -----------------------------------------------------------------------------
                    </div>
                    <div class="answer_box" v-if="item.is_live">
                        <div class="a_item">
                            <template>
                                <el-checkbox v-model="offline">已報到人數：
                                    <span style="text-decoration:none;color: #00afff;">{{live_total}}</span>
                                </el-checkbox>
                            </template>
                        </div>
                    </div>
                </template>
            </el-row>
        </el-row>
        <div slot="footer" class="dialog-footer">
            <el-button @click="closeMessage">取 消</el-button>
            <el-button type="primary" @click="submit">確 認</el-button>
        </div>
    </el-dialog>
</template>

<script>
    import ElRow from "element-ui/packages/row/src/row";

    export default {
        components: {ElRow},
        name: "department-group-message",
        data() {
            return {
                item: {
                    list: [],
                    title: '',
                    name: '',
                    open_time: '',
                    close_time: '',
                    active : false,
                    is_live : false,
                    online_open_time : '',
                    online_close_time : ''
                },
                total: 0,
                live_total : 0,
                total_user : [],
                live_total_user : [],
                preferVisible: false,
                online : false,
                offline : false,
                type : 0
            }
        },
        methods: {
            openMsg(id, type) {
                this.item= {
                    list: [],
                        title: '',
                        name: '',
                        open_time: '',
                        close_time: '',
                        online_open_time : '',
                        online_close_time : ''
                };
                this.total = 0;
                this.live_total = 0;
                this.total_user = [];
                this.live_total_user = [];
                this.preferVisible = true;
                this.online = false;
                this.offline = false;
                let url;
                if (type === 0) {
                    url = '/question/getAnswersCount?id=' + id;
                    this.type = 0;
                } else {
                    url = '/activity/getAnswersCount?id=' + id;
                    this.type = 1;
                }
                axios.get(url)
                    .then((response) => {
                        this.item = response.data.response.data;
                        this.item.active = this.item.active;
                        this.item.is_live = this.item.is_live;
                        this.total = this.item.sum;
                        this.total_user = this.item.sum_user;
                        this.live_total = this.item.live_sum;
                        this.live_total_user = this.item.live_sum_user;
                    }).catch((error) => {
                    console.error(error)
                })
            },
            submit() {
                this.preferVisible = false;
                let users = [];

                if (this.type === 0) {
                    this.item.list.forEach(function (question) {
                        for (let option of question.options) {
                            if (question.checked || option.checked) {
                                for (let userId in option.users) {
                                    users.push(option.users[userId])
                                }
                            }
                        }
                    });
                }

                if (this.type === 1) {
                    if (this.item.list.length > 0) {//有题
                        if (this.item.active) {
                            this.item.list.forEach(function (question) {
                                for (let option of question.options) {
                                    if (question.checked || option.checked) {
                                        for (let userId in option.users) {
                                            users.push(option.users[userId])
                                        }
                                    }
                                }
                            });
                        }
                    }

                    if (this.online === true) {
                        for (let onlineUserId in this.total_user) {
                            users.push(this.total_user[onlineUserId]);
                        }
                    }

                    if (this.offline === true) {
                        for (let offlineUserId in this.live_total_user) {
                            users.push(this.live_total_user[offlineUserId]);
                        }
                    }
                }

                let data = {
                    'title': this.item.title ? this.item.title : this.item.name,
                    'users': users,
                    'chooseNum': users.length
                };

                this.$emit('choose', data);
            },
            closeMessage() {
                this.preferVisible = false
            }
        }
    }
</script>

<style scoped>

</style>