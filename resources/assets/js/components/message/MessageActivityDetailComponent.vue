<template>
    <div id="app">
        <el-dialog v-loading="loading" title="活動報名" :close-on-click-modal='false' :visible.sync="ActivityVisible" width="70%">
            <el-steps :active="active" finish-status="success" style="margin-top:20px;">
                <el-step title="填寫活動基本資料" description=""></el-step>
                <el-step title="設計報名表" description="" v-show="activity.active"></el-step>
                <el-step title="設定報名金幣" description="" v-show="activity.active"></el-step>
                <el-step title="設定報到金幣" description="" v-if="activity.is_live"></el-step>
            </el-steps>

            <template>
                <!-- 填寫活動基本資料 -->
                <template v-if="active === 1" style="margin-top:20px;">
                    <el-form  :model="activity" ref="activity" :rules="activityRule" label-position="top">
                        <el-form-item label="業務單位"  prop="department_id">
                            <el-select v-model="activity.department_id" :disabled="disabled" >
                                <el-option label="請選擇" value="0" key="0"></el-option>
                                <template  v-for="(item) in departments">
                                    <el-option :label="item.name" :value="item.id.toString()" :key="item.id.toString()"></el-option>
                                </template>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="發放期別(發放日/有效期限)"  prop="stage_id">
                            <el-select v-model="activity.stage_id" style="width:380px;" :disabled="disabled">
                                <el-option label="請選擇" value="0" key="0"></el-option>
                                <template  v-for="item in stage_golds">
                                    <el-option
                                            :key="item.id.toString()"
                                            :value="item.id.toString()"
                                            :label="item.id + '(' + item.issue_time+' ~ '+ item.expire_time + ')'"
                                    ></el-option>
                                </template>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="活動名稱"  prop="name">
                            <el-col :span="15">
                                <el-input v-model="activity.name" auto-complete="off" :disabled="recycle_status && live_recycle_status"></el-input>
                            </el-col>
                        </el-form-item>

                        <el-form-item label="活動舉辦時間">
                            <el-date-picker
                                    v-model="activityStartTime"
                                    type="datetimerange"
                                    range-separator="~"
                                    format="yyyy-MM-dd HH:mm"
                                    start-placeholder="開始日期"
                                    end-placeholder="結束日期"
                                    align="right"
                                    :disabled="disabled">
                            </el-date-picker>
                        </el-form-item>

                        <el-form-item label="線上報名名額"  prop="online_number">
                            <el-col :span="15">
                                <el-input v-model="activity.online_number" auto-complete="off" :disabled="recycle_status && live_recycle_status || disabled"></el-input>
                            </el-col>
                            <el-col class="line" :span="1" :offset="1" prop="tel_ext">
                                <el-checkbox v-model="activity.infinite" :disabled="disabled">無限制</el-checkbox>
                            </el-col>
                        </el-form-item>

                        <el-form-item label="線上報名時間">
                            <el-date-picker
                                    v-model="activityOnlineRegistrationTime"
                                    type="datetimerange"
                                    range-separator="~"
                                    format="yyyy-MM-dd HH:mm"
                                    start-placeholder="開始日期"
                                    end-placeholder="結束日期"
                                    align="right"
                                    :disabled="disabled">
                            </el-date-picker>
                        </el-form-item>

                        <el-form-item>
                            <el-checkbox v-model="activity.active" :disabled="disabled">用戶可線上報名</el-checkbox>
                        </el-form-item>

                        <el-form-item>
                            <el-checkbox v-model="activity.is_live" :disabled="disabled">用戶需現場報到(產生QR碼)</el-checkbox>
                            <el-form-item v-show="activity.is_live">
                                <el-col :span="15" style="margin-left: 20px;">
                                    <el-radio v-model="activity.is_live_type" :label="1" :disabled="disabled">單一QR碼(可多人掃描)</el-radio>
                                    <el-radio v-model="activity.is_live_type" :label="2" :disabled="disabled">多個QR碼(限掃描一次)</el-radio>
                                </el-col>
                            </el-form-item>
                        </el-form-item>
                    </el-form>
                </template>

                <!-- 問題 -->
                <template v-else-if="active === 2" style="margin-top:20px;">
                    <el-row>
                        <el-col style="color: red">
                        * 不需要報名表可直接點擊下一步
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="4">
                            <el-select v-model="activityType">
                                <el-option key="1" value="1" label="活動調查"></el-option>
                                <el-option key="2" value="2" label="有獎徵答"></el-option>
                                <!--<el-option key="3" value="3" label="簡答題"></el-option>-->
                                <el-option key="4" value="4" label="會員資料"></el-option>
                                <el-option key="5" value="5" label="上傳附件"></el-option>
                            </el-select>
                        </el-col>
                        <el-col :offset='1' :span="4" v-if="optionTypes.length > 0">
                            <el-select v-model="activityOptionType">
                                <el-option v-for="option in optionTypes" :key="option.type" :value="option.type" :label="option.name"></el-option>
                            </el-select>
                        </el-col>
                        <el-col :offset='1' :span="4">
                            <el-button type="primary" @click="addActivity" v-if="!disabled">新增題目</el-button>
                        </el-col>
                    </el-row>

                    <el-row>
                        <template v-for="(activity,index) in params">
                            <template  v-if="activity.type === '1' || activity.type === '2'">
                                <div class="answer_box" v-if="activity.optionType === '3'">
                                    <div class="activity">
                                        <div class="q_i">●</div>
                                        <div class="q_w">{{activity.title}}</div>
                                        <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small"
                                                   @click="activity.edit = true" circle></el-button>
                                        <el-button v-if="!disabled" type="info" icon="el-icon-delete" size="small"
                                                   @click="removeActivity(index)" circle></el-button>
                                    </div>

                                    <div class="correct" v-if="activity.type === '2'&& activity.correct">【正確答案】{{activity.correct}}</div>
                                </div>
                                <div class="answer_box" v-else>
                                    <div class="activity">
                                        <div class="q_i">●</div>
                                        <div class="q_w">{{activity.title}}</div>
                                        <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small" @click="activity.edit = true" circle></el-button>
                                        <el-button v-if="!disabled" type="info" icon="el-icon-delete"  size="small" @click="removeActivity(index)" circle></el-button>
                                    </div>
                                    <div class="a_item">
                                        <span v-for="(value,key) in activity.options">
                                            <span v-if="value.option">
                                                {{key+1}}. {{value.option}}
                                            </span>
                                        </span>
                                    </div>

                                    <div v-if="activity.type === '2'">
                                        <div class="correct" v-if="activity.optionType === '1'">
                                            【正確答案】{{activity.options[activity.correct].option}}
                                        </div>
                                        <div class="correct" v-else-if="activity.optionType === '2'" >【正確答案】<span v-for="(cor) in activity.correct"> {{activity.options[cor].option}}</span>
                                        </div>
                                        <div class="correct" v-else>【正確答案】<span> {{activity.options[0].correct}}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!--<template v-else-if="activity.type === '3'">-->
                            <!--<div class="answer_box">-->
                            <!--<div class="activity">-->
                            <!--<div class="q_i">●</div>-->
                            <!--<div class="q_w">{{activity.title}}</div>-->
                            <!--<el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small" @click="activity.edit = true" circle></el-button>-->
                            <!--<el-button v-if="!disabled" type="info" icon="el-icon-delete"  size="small" @click="removeActivity(index)" circle></el-button>-->
                            <!--</div>-->

                            <!--<div class="correct">【正確答案】{{activity.correct}}</div>-->
                            <!--</div>-->
                            <!--</template>-->

                            <template v-else-if="activity.type === '4'">
                                <div class="answer_box">
                                    <div class="activity">
                                        <div class="q_i">●</div>
                                        <div class="q_w">{{activity.title}}</div>
                                        <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small" @click="activity.edit = true" circle></el-button>
                                        <el-button v-if="!disabled" type="info" icon="el-icon-delete"  size="small" @click="removeActivity(index)" circle></el-button>
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="activity.type === '5'">
                                <div class="answer_box">
                                    <div class="activity">
                                        <div class="q_i">●</div>
                                        <div class="q_w">{{activity.title}}</div>
                                        <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small" @click="activity.edit = true" circle></el-button>
                                        <el-button v-if="!disabled" type="info" icon="el-icon-delete" size="small" @click="removeActivity(index)" circle></el-button>
                                    </div>

                                    <!--<div class="correct">【正確答案】{{activity.correct}}</div>-->
                                </div>
                            </template>

                        </template>
                    </el-row>
                </template>

                <!-- 金幣 -->
                <template v-else-if="active === 3" style="margin-top:20px;">
                    <el-form :model="golds" label-position="top" v-if="activity.id === 0">
                        <el-form-item style="color: red">* 若不發放報名金幣，金幣數可設定為0</el-form-item>
                        <el-form-item>帳號目前可設定發放金幣為 {{remain_gold}}</el-form-item>
                        <el-form-item>活動已發送金幣數量為 {{golds.sent_gold}}</el-form-item>
                        <el-form-item prop="person_gold">
                            <el-col class="line" :span="5" :offset="0" prop="fans_number">每個人發放</el-col>
                            <el-col :span="6">
                                <el-input-number
                                        v-model="golds.person_gold"
                                        :min="0"
                                ></el-input-number>
                            </el-col>
                            <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                        </el-form-item>
                        <el-form-item prop="person_limit">
                            <el-row>
                                <el-col class="line" :span="5" :offset="0" prop="fans_number">限制前</el-col>
                                <el-col :span="6">
                                    <el-input-number
                                            v-model="golds.person_limit"
                                            :min="0"
                                    ></el-input-number>
                                </el-col>
                                <el-col class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                            </el-row>
                        </el-form-item>

                        <el-form-item>總計 {{golds.setting_gold}} 枚</el-form-item>
                    </el-form>

                    <el-form :model="goldsUpdate" label-position="top" v-else>
                        <el-form-item style="color: red">* 若不發放報名金幣，金幣數可設定為0</el-form-item>
                        <el-form-item>帳號目前可設定發放金幣為 {{remain_gold}}</el-form-item>
                        <el-form-item>
                            已設定人數為 {{goldsUpdate.person_limit}},
                            每人金幣數為 {{goldsUpdate.person_gold}},
                            已設定總數為 {{goldsUpdate.setting_gold}},
                            活動已發送金幣數量為 {{goldsUpdate.sent_gold}}
                            <span v-show="recycle_status">, 已回收金幣數量為{{recycle_gold}} </span>
                        </el-form-item>
                        <el-form-item prop="person_gold">
                            <el-col class="line" :span="5" :offset="0" prop="fans_number">每個人發放</el-col>
                            <el-col :span="6">
                                <el-input-number v-model="goldsUpdate.person_gold" :disabled="disabled"></el-input-number>
                            </el-col>
                            <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                        </el-form-item>
                        <el-form-item prop="person_limit">
                            <el-row>
                                <el-col class="line" :span="5" :offset="0" prop="fans_number">限制前</el-col>
                                <el-col :span="6">
                                    <el-input-number
                                            v-model="goldsUpdate.person_limit"
                                            :min="golds.person_limit"
                                            :disabled="recycle_status"
                                    ></el-input-number><!--:disabled="!remain_gold > 0"-->
                                </el-col>
                                <el-col class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                            </el-row>
                        </el-form-item>

                        <el-form-item>
                            總計 {{goldsUpdate.setting_gold}} 枚
                            <span v-if="!recycle_status">
                                        - 新增 <span style="color: red">{{goldsUpdate.setting_gold-golds.setting_gold}} </span>枚
                                    </span>
                        </el-form-item>
                    </el-form>
                </template>

                <!-- 現場金幣 -->
                <template v-else-if="active === 4" style="margin-top:20px;">
                    <el-form :model="offlineGolds" label-position="top" v-if="activity.id === 0">
                        <el-form-item style="color: red">* 若不發放報到金幣，金幣數可設定為0</el-form-item>
                        <el-form-item>帳號目前可設定發放金幣為 {{remain_gold}}</el-form-item>
                        <el-form-item>活動已發送金幣數量為{{offlineGolds.live_sent_gold}}</el-form-item>
                        <el-form-item prop="person_gold">
                            <el-col class="line" :span="5" :offset="0" prop="fans_number">每個人發放</el-col>
                            <el-col :span="6">
                                <el-input-number v-model="offlineGolds.live_person_gold" :min="min"></el-input-number>
                            </el-col>
                            <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                        </el-form-item>
                        <el-form-item prop="person_limit">
                            <el-row>
                                <el-col class="line" :span="5" :offset="0" prop="fans_number">限制前</el-col>
                                <el-col :span="6">
                                    <el-input-number
                                            v-model="offlineGolds.live_person_limit"
                                            :min="0"
                                    ></el-input-number>
                                </el-col>
                                <el-col class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                            </el-row>
                        </el-form-item>

                        <el-form-item>總計 {{offlineGolds.live_setting_gold}} 枚</el-form-item>
                    </el-form>

                    <el-form :model="offlineGoldsUpdate" label-position="top" v-else>
                        <el-form-item style="color: red">* 若不發放報到金幣，金幣數可設定為0</el-form-item>
                        <el-form-item>帳號目前可設定發放金幣為 {{remain_gold}}</el-form-item>
                        <el-form-item>
                            已設定人數為 {{offlineGoldsUpdate.live_person_limit}},
                            每人金幣數為 {{offlineGoldsUpdate.live_person_gold}},
                            已設定總數為 {{offlineGoldsUpdate.live_setting_gold}},
                            活動已發送金幣數量為 {{offlineGoldsUpdate.live_sent_gold}}
                            <span v-show="live_recycle_status">, 已回收金幣數量為{{live_recycle_gold}} </span>
                        </el-form-item>
                        <el-form-item prop="person_gold">
                            <el-col class="line" :span="5" :offset="0" prop="fans_number">每個人發放</el-col>
                            <el-col :span="6">
                                <el-input-number v-model="offlineGoldsUpdate.live_person_gold" :disabled="disabled"></el-input-number>
                            </el-col>
                            <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                        </el-form-item>
                        <el-form-item prop="person_limit">
                            <el-row>
                                <el-col class="line" :span="5" :offset="0" prop="fans_number">限制前</el-col>
                                <el-col :span="6">
                                    <el-input-number
                                            v-model="offlineGoldsUpdate.live_person_limit"
                                            :min="offlineGolds.live_person_limit"
                                            :disabled="live_recycle_status"
                                    ></el-input-number><!--:disabled="!remain_gold > 0"-->
                                </el-col>
                                <el-col class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                            </el-row>
                        </el-form-item>

                        <el-form-item>
                            總計 {{offlineGoldsUpdate.live_setting_gold}} 枚
                            <span v-if="!recycle_status">
                                        - 新增 <span style="color: red">{{offlineGoldsUpdate.live_setting_gold-offlineGolds.live_setting_gold}} </span>枚
                                    </span>
                        </el-form-item>
                    </el-form>
                </template>

                <template>
                    <el-row style="margin-top:20px">
                        <el-col :span="12">
                            <el-button v-if="active > 1" type="primary" @click="pre">上一步</el-button>
                            <template>&nbsp;</template>
                        </el-col>
                        <el-col :span="12" v-if="active < step">
                            <el-button style="float: right" type="primary" @click="next">下一步</el-button>
                        </el-col>
                        <el-col :span="12" v-else>
                            <el-button type="primary" @click="submitActivity" style="float: right;margin-left: 30px;">確 定</el-button>
                            <el-button @click="ActivityVisible = false" style="float: right;margin-left: 30px;">取 消</el-button>
                        </el-col>
                    </el-row>
                </template>
            </template>

            <template v-for="(activity,index) in params">
                <el-dialog :close-on-click-modal="false" :visible.sync="activity.edit" v-if="activity.optionType === '1'" append-to-body>
                    <el-form  :model="activity" ref="reference" :rules="activityRadioRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="activity.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>

                        <template v-for="(value,key) in activity.options">
                            <el-row style="margin-top: 2px;">
                                <el-col :span="2">
                                    <template v-if="activity.type === '1'">
                                        <el-radio disabled v-model="activity.correct" :label="key">{{key+1}}</el-radio>
                                    </template>
                                    <template v-else>
                                        <template v-if="activity.correct !== ''" >
                                            <el-radio v-model="activity.correct" :label="key">{{key+1}}</el-radio>
                                        </template>
                                    </template>
                                </el-col>
                                <el-col :span="16">
                                    <el-form-item label="問題選項"  :prop="'options['+key+'].option'">
                                        <el-input v-model="value.option" placeholder="請輸入選項"></el-input>
                                        <el-button v-if="key>0" type="text" @click="removeRadio(index,key)">移除選項</el-button>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </template>

                        <el-form-item>
                            <el-button type="text" @click="addRadioOption(index)">新增選項</el-button>
                        </el-form-item>

                        <el-form-item v-if="activity.type === '1'">
                            <el-form-item>
                                <el-select v-model="activity.required" placeholder="">
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-form-item>

                        <el-button  @click="saveActivity(index)" type="warning" style="margin-top: 50px;" round>儲 存</el-button>
                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="activity.edit" v-else-if="activity.optionType === '2'"   append-to-body>
                    <el-form :model="activity" ref="reference" :rules="activityRadioRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="activity.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>
                        <template v-for="(value,key) in activity.options">
                            <el-row style="margin-top: 2px;">
                                <el-col :span="2">
                                    <template v-if="activity.type === '1'">
                                        <el-checkbox v-model="activity.correct" :label="key" :key="key" disabled>{{key+1}}</el-checkbox>
                                    </template>
                                    <template v-else>
                                        <el-checkbox v-model="activity.correct" :label="key" :key="key">{{key+1}}</el-checkbox>
                                    </template>
                                </el-col>
                                <el-col :span="16">
                                    <el-form-item label="問題選項" :prop="'options['+key+'].option'">
                                        <el-input v-model="value.option" placeholder="請輸入選項"></el-input>
                                        <el-button v-if="key>0" type="text" @click="removeRadio(index,key)">移除選項
                                        </el-button>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </template>

                        <el-form-item>
                            <el-button type="text" @click="addRadioOption(index)">新增題目</el-button>
                        </el-form-item>

                        <el-form-item label="問題性質" prop="title" v-if="activity.type === '1'">
                            <el-select v-model="activity.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-button @click="saveActivity(index)" type="warning" style="margin-top: 50px;" round>儲 存
                        </el-button>
                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="activity.edit" v-else-if="activity.optionType === '3'" append-to-body>
                    <el-form :model="activity" ref="reference" :rules="activityTextRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="activity.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>
                        <el-form-item label="正確答案" v-if="activity.type === '2'">
                            <el-checkbox v-model="activity.judgement" label="1" @change="activity.judgement.length==0?activity.correct='':''">有正確答案否</el-checkbox>
                            <template v-if="activity.judgement.length > 0">
                                <el-input v-model="activity.correct"></el-input>
                            </template>
                            <template v-else>
                                <el-input disabled></el-input>
                            </template>
                        </el-form-item>

                        <el-form-item label="問題性質">

                            <template v-if="activity.judgement.length > 0 && activity.type === '2'">
                                <el-select v-model="activity.required" placeholder="" disabled>
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </template>
                            <template v-else>
                                <el-select v-model="activity.required" placeholder="">
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </template>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveActivity(index)" type="warning" style="margin-top: 50px;" round>儲 存
                            </el-button>
                        </el-form-item>

                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="activity.edit" v-else-if="activity.type === '3'"  append-to-body>
                    <el-form :model="activity" ref="reference" :rules="activityTextRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="activity.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>
                        <el-form-item label="正確答案" v-if="activity.type === '2'">
                            <el-checkbox v-model="activity.judgement" label="1" @change="activity.judgement.length==0?activity.correct='':''">有正確答案否</el-checkbox>
                            <template v-if="activity.judgement.length > 0">
                                <el-input v-model="activity.correct"></el-input>
                            </template>
                            <template v-else>
                                <el-input disabled></el-input>
                            </template>
                        </el-form-item>

                        <el-form-item label="問題性質">
                            <template v-if="activity.judgement.length > 0 && activity.type === '2'">
                                <el-select v-model="activity.required" placeholder="" disabled>
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </template>
                            <template v-else>
                                <el-select v-model="activity.required" placeholder="">
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </template>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveActivity(index)" type="warning" style="margin-top: 50px;" round>儲 存</el-button>
                        </el-form-item>

                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="activity.edit" append-to-body v-else-if="activity.type === '4'" >
                    <el-form :model="activity" ref="reference" :rules="activityProfileRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="activity.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>

                        <el-form-item label="卡片類型">
                            <el-select v-model="activity.profileType" placeholder="卡片類型">
                                <el-option v-for="(value) in profileTypes" :key="value.type" :value="value.type"
                                           :label="value.name"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="問題性質">
                            <el-select v-model="activity.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveActivity(index)" type="warning" style="margin-top: 50px;" round>儲 存</el-button>
                        </el-form-item>
                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="activity.edit" append-to-body v-else-if="activity.type === '5'" >
                    <el-form :model="activity" ref="reference" :rules="activityProfileRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="activity.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>

                        <el-form-item label="附件上傳類型">
                            <el-select v-model="activity.form" placeholder="">
                                <el-option key="1" value="1" label="圖片"></el-option>
                                <el-option key="2" value="2" label="Word"></el-option>
                                <el-option key="3" value="3" label="Excel"></el-option>
                                <el-option key="4" value="4" label="PDF"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="問題性質">
                            <el-select v-model="activity.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveActivity(index)" type="warning" style="margin-top: 50px;" round>儲 存</el-button>
                        </el-form-item>
                    </el-form>
                </el-dialog>
            </template>
        </el-dialog>
    </div>
</template>

<script>
    import NewDialog from '../../tools/element-ui-dialog';
    import {ActivityProfileRule,ActivityRadioRule,ActivityRule,ActivityTextRule} from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "data-detail-component",

        data: function () {
            return {
                id : 0,
                activityRule:ActivityRule,
                activityRadioRule:ActivityRadioRule,
                activityTextRule:ActivityTextRule,
                activityProfileRule:ActivityProfileRule,
                min:0,
                attemptMax:5,
                activityOnlineRegistrationTime: [],
                activityStartTime : [],
                activityType:"1",
                activityOptionType:"1",
                optionTypes:[
                    {type:'1',name:'單選'},
                    {type:'2',name:'複選'},
                    {type:'3',name:'問答題'}
                ],
                profileTypes:[
                    // {type:'1',name:'身份證號'},
                    // {type:'2',name:'居住證號'},
                    // {type:'3',name:'護照號'}
                    {type: '4', name: 'email'}
                ],
                ActivityVisible: false,
                isAdd: true,// false編輯
                activity: {
                    id: 0,
                    stage_id:"0",
                    department_id:"0",
                    name:'',
                    start_time:'',
                    end_time:'',
                    online_start_time:'',
                    online_end_time:'',
                    update_time:'',

                    online_number : 0,
                    is_live : 0,
                    active : false,
                    infinite : false,
                    is_live_type : 1
                },
                golds:{
                    setting_gold:0,
                    person_gold:0,
                    sent_gold:0,
                    person_limit:0
                },
                goldsUpdate:{
                    setting_gold:0,
                    person_gold:0,
                    sent_gold:0,
                    person_limit:0
                },
                offlineGolds:{
                    live_person_gold:0,
                    live_person_limit:0,
                    live_sent_gold:0,
                    live_setting_gold:0
                },
                offlineGoldsUpdate:{
                    live_person_gold:0,
                    live_person_limit:0,
                    live_sent_gold:0,
                    live_setting_gold:0
                },
                params:[],
                departments:[],
                stage_golds:[],
                dialog: NewDialog(this),
                active:1,
                updater:'',
                send_stage_sum : {},
                remain_gold:0,
                sum:0,
                disabled: false,
                loading : false,
                step : 3,
                recycle_gold : 0,
                recycle_status : false,
                live_recycle_gold : 0,
                live_recycle_status : false
            }
        },

        watch: {
            'activity.is_live' : function(val) {
                this.step = val ? 4: 3;
            },

            'activity.department_id' : function(current, old) {
                //判斷是編輯頁面還是添加頁面
                let action = 'add';

                if (parseInt(current) !== 0) {
                    if (current !== old && this.loading === false) {
                        this.activity.department_id = current;
                        this.setStageGold(action);
                        this.setRemainGold();
                    }
                } else {
                    this.activity.stage_id = '0';
                }
            },

            'activity.stage_id' : function(current, old) {
                if(current !== old) {
                    if(current === 0) {
                        this.remain_gold = 0;
                        return;
                    }

                    this.stage_golds.forEach((item) => {
                        if (item.id === parseInt(current)) {
                        if ((this.activity.open_time !== "" && this.activity.open_time < item.issue_time) ||
                                (this.activity.close_time !== "" && this.activity.close_time < item.expire_time)
                        ) {
                            return this.dialog.openWarning(null, '您所設定的金幣發放期別不符合活動填寫時間，請選擇其他發放期別');
                        }
                    }
                });

                    this.setRemainGold();
                }
            },

            'golds.person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.golds.person_gold = '';
                    return false;
                }
                this.checkGoldNumber('golds');
            },

            'golds.person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.golds.person_limit = 0;
                    return false;
                }
                this.checkGoldNumber('golds');
            },

            'goldsUpdate.person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.goldsUpdate.person_gold = '';
                    return false;
                }
                this.checkGoldNumber('goldsUpdate');
            },

            'goldsUpdate.person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.goldsUpdate.person_limit = '';
                    return false;
                }
                this.checkGoldNumber('goldsUpdate');
            },

            'offlineGolds.live_person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.offlineGolds.live_person_gold = '';
                    return false;
                }
                this.checkGoldNumber('offlineGolds');
            },

            'offlineGolds.live_person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.offlineGolds.live_person_limit = 0;
                    return false;
                }
                this.checkGoldNumber('offlineGolds');
            },

            'offlineGoldsUpdate.live_person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.offlineGoldsUpdate.live_person_gold = '';
                    return false;
                }
                this.checkGoldNumber('offlineGoldsUpdate');
            },

            'offlineGoldsUpdate.live_person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.offlineGoldsUpdate.live_person_limit = '';
                    return false;
                }
                this.checkGoldNumber('offlineGoldsUpdate');
            },

            ActivityVisible(current) {
                if (current === false) {
                    this.resetActivity();
                }
            },

            activityType(current) {
                current = parseInt(current);
                if (current === 1 || current === 2) {
                    this.optionTypes = [
                        {type: '1',name:'單選'},
                        {type: '2',name:'複選'},
                        {type: '3',name: '問答題'}
                    ]
                } else {
                    this.optionTypes = [];
                }
            },

            activityStartTime(current){
                this.checkStartTime(current);
            },

            activityOnlineRegistrationTime(current) {
                this.checkOnlineRegistrationTime(current);
            },
        },

        created: function () {
            let that = this
            this.$watch('params', function () {
                that.params.forEach(function (item) {
                    if(!item.edit&&!item.title) {
                        that.params.splice(that.params.indexOf(item),1)
                    }
                })
            }, {
                deep: true,
                immediate: true
            })
        },

        methods: {
            submitActivity() {
                if (this.activityStartTime.length === 0) {
                    return NewDialog(this).openError(null, '請於第一頁選擇活動舉辦時間');
                }

                if (this.activity.active) {
                    if (this.activity.online_number === 0 && this.activity.infinite === false) {
                        return NewDialog(this).openError(null, '請於第一頁填寫線上報名名額');
                    }

                    if (this.activityOnlineRegistrationTime.length === 0){
                        return NewDialog(this).openError(null, '請於第一頁選擇線上報名時間');
                    }
                }

                let attr = 'golds'
                if(this.active>3){
                    if(this.isAdd){
                        attr = 'offlineGolds'
                    }else {
                        attr = 'offlineGoldsUpdate'
                    }
                }else{
                    if(this.isAdd){
                        attr = 'golds'
                    }else{
                        attr = 'goldsUpdate'
                    }
                }

                let result = this.checkGoldNumber(attr);
                if (result !== true) {
                    return;
                }
                let data =  {
                    activity : this.activity,
                    golds : this.golds,
                    params : this.params,
                    goldsUpdate : this.goldsUpdate,
                    offlineGolds : this.offlineGolds,
                    offlineGoldsUpdate : this.offlineGoldsUpdate,
                    recycleStatus : this.recycle_status,
                    liveRecycleStatus : this.live_recycle_status
                };

                console.log(data);

                if (data.activity.is_live_type === 2 && data.offlineGolds.live_person_limit === 0) {
                    this.dialog.openError(null, '領取人數不能為0，請重新設定');
                    return;
                }

                axios.post('/activity/create?id='+data.activity.id, {data:data}).then((response) => {
                    if(response.data.code===0){
                        if(response.data.response.id>0){
                            this.dialog.openSuccess(() => {
                                this.ActivityVisible = false;
                                window.location.reload(true);
                            }, '操作成功');
                            return
                        }
                    }
                    this.dialog.openError(null, '操作失敗');
                }).catch(() => {
                    this.dialog.openError(null, '操作失敗');
                });
            },

            checkGoldNumber(key) {
                if (!this.ActivityVisible) {
                    return;
                }
                let settingGold;
                if(key==='golds'||key==='goldsUpdate'){
                    settingGold = this[key].person_gold * this[key].person_limit;
                    this[key].setting_gold = settingGold;
                }else{
                    settingGold = this[key].live_person_gold * this[key].live_person_limit;
                    this[key].live_setting_gold = settingGold;
                }

                if(this.isAdd){
                    // 新增
                    if (this.golds.setting_gold + this.offlineGolds.live_setting_gold > this.remain_gold) {
                        this.dialog.openWarning(function () {}, '總發放金幣不得高於可發放金幣');
                        return false;
                    }
                }else{
                    // 編輯
                    if (this.goldsUpdate.setting_gold > this.golds.setting_gold || this.offlineGoldsUpdate.live_setting_gold > this.offlineGolds.live_setting_gold) {
                        let online_change  = this.goldsUpdate.setting_gold - this.golds.setting_gold;
                        let offline_change = this.offlineGoldsUpdate.live_setting_gold - this.offlineGolds.live_setting_gold;
                        if (online_change + offline_change > this.remain_gold) {
                            this.dialog.openWarning(function () {}, '總發放金幣不得高於可發放金幣');
                            return false;
                        }
                    }
                }

                return true;
            },

            checkStartTime(time) {
                let status = true;

                if (time && time.length > 1 && this.loading === false) {
                    let start = time[0].toString();
                    let end = time[1].toString();
                    let stageId = parseInt(this.activity.stage_id);

                    if (start === end) {
                        this.dialog.openWarning(() => {

                        }, '活動舉辦填寫時間(起)不得等於活動舉辦填寫時間(迄)');
                        return;
                    }

                    if (stageId !== 0) {
                        this.stage_golds.forEach((item)=>{
                            if(stageId === parseInt(item.id)) {
                            if(start < item.issue_time || end > item.expire_time) {
                                this.dialog.openWarning(null, '您所設定的金幣發放期別不符合活動填寫時間，請選擇其他發放期別');

                                status = false;
                            }
                        }
                    });
                    }

                    this.activity.start_time = start;
                    this.activity.end_time = end;
                }

                return status;
            },

            checkOnlineRegistrationTime(time) {
                let status = true;

                if (time && time.length > 1 && this.activity.active) {
                    let start = time[0].toString();
                    let end = time[1].toString();
                    let stageId = parseInt(this.activity.stage_id);

                    if (start === end) {
                        this.dialog.openWarning(() => {

                        }, '活動線上報名時間(起)不得等於活動線上報名填寫時間(迄)');
                        return;
                    }

                    if (stageId !== 0) {
                        this.stage_golds.forEach((item) => {
                            if(stageId === parseInt(item.id)) {
                            if(start < item.issue_time || end > item.expire_time) {
                                this.dialog.openWarning(null, '您所設定的金幣發放期別不符合活動填寫時間，請選擇其他發放期別');

                                status = false;
                            }
                        }
                    });
                    }

                    this.activity.online_start_time = start;
                    this.activity.online_end_time = end;
                }

                return status;
            },

            addActivity() {
                if (this.activityType === '1' || this.activityType === '2') {
                    if (parseInt(this.activityOptionType) === 1) {
                        this.createRadio(this.activityType);
                    } else if(parseInt(this.activityOptionType) === 2) {
                        this.createCheck(this.activityType);
                    } else {
                        this.createAnswer(this.activityType);
                    }
                }
//                else if (this.activityType === '3') {
//                    this.createAnswer(this.activityType);
//                }
                else if (this.activityType === '4') {
                    this.createProfile(this.activityType);
                }
                else if (this.activityType === '5') {
                    this.createUpload(this.activityType);
                }
            },

            checkParamsLength() {
                if (this.params.length > 49) {
                    this.dialog.openWarning(function () {

                    }, '最多可設定50題');
                    return false;
                }
                this.params.forEach(function (value) {
                    value.edit = false;
                });
                return true;
            },

            addRadioOption (index) {
                if (this.params[index].options.length > 9) {
                    this.dialog.openWarning(function () {
                    }, '最多可設定10個選項');
                    return;
                }

                let len = this.params[index].options.length;
                let key = 'options[' + (len-1) + '].option';
                this.activityRadioRule[key] = this.activityRadioRule['options[0].option'];
                this.params[index].options.push({
                    id: 0,
                    option: '',
                    type: '1'
                });
            },

            removeActivity(index) {
                this.params.splice(index, 1);
            },

            removeRadio(index,key) {
                this.params[index].options.splice(key, 1);
                //this.activityRadioRule['options['+key+'].option'] = this.activityRadioRule['options[0].option'];
            },

            createCheck(type) {
                if (!this.checkParamsLength()) {
                    return;
                }
                this.params.forEach((value) => {
                    value.edit = false;
            });

                let item = {
                    id:0,
                    title:"",
                    type:type,
                    optionType:'2',
                    required:parseInt(type) !== 1 ? '1' : '0',
                    options:[
                        {
                            id:0,
                            option:'',
                            type:'1'
                        },
                    ],
                    correct: [],
                    edit:true
                }
                this.params.push(item);
            },

            createProfile(type){
                if (!this.checkParamsLength()) {
                    return;
                }

                this.params.push({
                    id:0,
                    title:"",
                    type:type,
                    required:parseInt(type) !== 1 ? '1' : '0',
                    profileType:'4',
                    correct: '',
                    edit:true
                });
            },

            createUpload(type) {
                if (!this.checkParamsLength()) {
                    return;
                }
                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    form : '1',
                    required: parseInt(type) !== 1 ? '1' : '0',
                    profileType: '5',
                    correct: '',
                    edit: true
                });
            },

            createAnswer(type) {
                if (!this.checkParamsLength()) {
                    return;
                }

                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    optionType: '3',
                    required: parseInt(type) !== 1 ? '1' : '0',
                    judgement: ['1'],
                    correct: '',
                    edit: true
                });
            },

            createRadio(type) {
                if(!this.checkParamsLength()) {
                    return;
                }

                this.params.push({
                    id:0,
                    title:"",
                    type:type,
                    required:parseInt(type) !== 1 ? '1' : '0',
                    optionType:'1',
                    options:[
                        {
                            id:0,
                            option:'',
                            type:'1'
                        },
                    ],
                    correct: 0,
                    edit:true
                });
            },

            next() {
                if (this.activity.is_live === false && this.activity.active === false) {
                    this.dialog.openWarning(() => {
                    }, '線上報名、現場報到需擇一勾選');
                    return;
                }

                if(this.active === 4) {
                    return this.dialog.openWarning(null, '沒有下一步了');
                }

                if (this.active === 1) {
                    if (this.activityStartTime === null) {
                        this.dialog.openWarning(() => {
                        }, '請選擇活動舉辦時間');
                        return;
                    }

                    if (this.activityStartTime.length === 0 && this.activityStartTime !== null) {
                        this.dialog.openWarning(() => {
                        }, '請選擇活動舉辦時間');
                        return;
                    }

                    if (this.activity.active === true && this.activityOnlineRegistrationTime === null) {
                        this.dialog.openWarning(() => {
                        }, '請選擇線上報名時間');
                        return;
                    }

                    if (this.activity.active === true && this.activityOnlineRegistrationTime.length === 0 && this.activityOnlineRegistrationTime !== null) {
                        this.dialog.openWarning(() => {
                        }, '請選擇線上報名時間');
                        return;
                    }

                    let start = this.activityStartTime[0].toString();
                    let end = this.activityStartTime[1].toString();
                    if (start === end) {
                        this.dialog.openWarning(() => {

                        }, '活動舉辦時間(起)不得等於活動舉辦時間填寫時間(迄)');
                        return;
                    }

                    if (this.activity.active === true) {
                        let onlineStart = this.activityOnlineRegistrationTime[0].toString();
                        let onlineEnd = this.activityOnlineRegistrationTime[1].toString();
                        if (onlineStart === onlineEnd) {
                            this.dialog.openWarning(() => {

                            }, '活動線上報名時間(起)不得等於活動線上報名填寫時間(迄)');
                            return;
                        }
                    }

                    //報名時間的結束時間 不能晚於 活動舉辦的結束時間
                    let open_end = this.activityStartTime[1].toString();

                    //編輯時只有報到的情況不提示
                    if (!this.disabled) {
                        if (this.activityOnlineRegistrationTime && this.activityOnlineRegistrationTime.length > 0) {
                            let online_end = this.activityOnlineRegistrationTime[1].toString();

                            if (online_end > open_end) {
                                this.dialog.openWarning(() => {
                                }, '報名結束時間不得晚於活動結束時間');
                                return;
                            }
                        }
                    }

                    if (this.activity.is_live === 0 && this.activity.active === false) {
                        this.dialog.openWarning(() => {
                        }, '線上報名、現場報到需擇一勾選');
                        return;
                    }

                    if (this.activity.active === true && this.activity.online_number === 0 && this.activity.infinite === false) {
                        this.dialog.openWarning(() => {
                        }, '請填寫線上報名名額');
                        return;
                    }

                    if (false === this.checkStartTime(this.activityStartTime)) {
                        return;
                    }

                    if (this.activity.active) {
                        if (false === this.checkOnlineRegistrationTime(this.activityOnlineRegistrationTime)) {
                            return;
                        }
                    }

                    this.$refs.activity.validate((result) => {
                        if (result) {
                            if (this.activity.is_live === true && this.activity.active === false) {
                                this.active += 3;
                            } else {
                                this.active++;
                            }
                        }
                    });
                } else if (this.active === 2) {
                    // if(this.params.length === 0) {
                    //     return this.dialog.openWarning(null, '活動必須包含問題');
                    // }
                    this.active++;
                } else if (this.active === 3) {
                    let result = this.checkGoldNumber(this.id <= 0 ? 'golds' : 'goldsUpdate');
                    if (result !== true) {
                        return;
                    }

                    //金幣數量未設定之類
                    this.active++;
                }
            },

            pre() {
                if (this.active === 4 && this.activity.active === false && this.activity.is_live === true) {
                    this.active = 1;
                } else {
                    if (this.active === 1) {
                        return this.dialog.openWarning(null,'沒有上一步了');
                    }

                    this.active--;
                }
            },

            resetActivity() {
                this.params = [];

                this.activity = {
                    id: 0,
                    stage_id : "0",
                    department_id : "",
                    name:'',
                    start_time:'',
                    end_time:'',
                    online_start_time:'',
                    online_end_time:'',
                    update_time:'',

                    online_number : 0,
                    is_live : 0,
                    is_live_type : 1,
                    active : false,
                    infinite : false
                };

                this.golds = {
                    setting_gold:0,
                    person_gold:0,
                    sent_gold:0,
                    person_limit:0
                };

                this.offlineGolds = {
                    live_person_gold:0,
                    live_person_limit:0,
                    live_sent_gold:0,
                    live_setting_gold:0
                };

                this.goldsUpdate = {
                    setting_gold:0,
                    person_gold:0,
                    sent_gold:0,
                    person_limit:0
                };

                this.offlineGoldsUpdate = {
                    live_person_gold:0,
                    live_person_limit:0,
                    live_sent_gold:0,
                    live_setting_gold:0
                };

                this.activityStartTime = [];

                this.activityOnlineRegistrationTime = [];

                this.recycle_gold = 0;
                this.recycle_status = false;
                this.live_recycle_gold = 0;
                this.live_recycle_status = false;
            },

            editActivity (id) {
                this.id = id;
                this.active = 1;
                let that = this;

                let callbackActivity = ()=> {
                    this.disabled = false;
                    if (id <= 0) {
                        this.ActivityVisible = true;
                        this.isAdd = true;
                        that.activity.department_id = '0';
                        that.activity.stage_id = '0';
                        that.activity.name = '';

                        return;
                    }
                    this.isAdd = false

                    this.loading = true;
                    this.disabled = true;
                    this.ActivityVisible = true;

                    axios.get('/activity/get?id='+this.id).then((response) => {
                        let activity = response.data.response.data;
                        console.log(activity);
                        this.recycle_gold = activity.recycle_gold;
                        this.recycle_status = activity.recycle_status.toString() === '2' ? true : false;
                        this.live_recycle_gold = activity.live_recycle_gold;
                        this.live_recycle_status = activity.live_recycle_status.toString() === '2' ? true : false;

                        this.activity.department_id = activity.department_id.toString();

                        this.getRemainGold(() => {
                            let tmpActivity = {
                                id : activity.id,
                                name : activity.name,
                                stage_id : activity.stage_id.toString(),
                                department_id : activity.department_id.toString(),
                                start_time : activity.start_time,
                                end_time : activity.end_time,
                                is_live : activity.is_live,
                                is_live_type : parseInt(activity.is_live_type),
                                active : activity.active,
                                infinite : activity.infinite,
                                online_number : activity.online_number,
                                online_start_time : activity.online_start_time,
                                online_end_time : activity.online_end_time
                            };
                            this.activity = tmpActivity;

                            let golds = {
                                sent_gold : activity.sent_gold,
                                setting_gold : activity.setting_gold,
                                person_gold : activity.person_gold,
                                person_limit : parseInt(activity.person_limit)
                            };
                            this.golds = golds;

                            let goldsUpdate = {
                                sent_gold : activity.sent_gold,
                                setting_gold : activity.setting_gold,
                                person_gold : activity.person_gold,
                                person_limit : activity.person_limit
                            };
                            this.goldsUpdate = goldsUpdate;


                            let offlineGolds = {
                                live_sent_gold : activity.live_sent_gold,
                                live_setting_gold : activity.live_setting_gold,
                                live_person_gold : activity.live_person_gold,
                                live_person_limit : parseInt(activity.live_person_limit)
                            };
                            this.offlineGolds = offlineGolds;

                            let offlineGoldsUpdate = {
                                live_sent_gold : activity.live_sent_gold,
                                live_setting_gold : activity.live_setting_gold,
                                live_person_gold : activity.live_person_gold,
                                live_person_limit : activity.live_person_limit
                            };
                            this.offlineGoldsUpdate = offlineGoldsUpdate;

                            this.activityStartTime = [
                                new Date(activity.start_time),
                                new Date(activity.end_time)
                            ];

                            this.activityOnlineRegistrationTime = [
                                new Date(activity.online_start_time),
                                new Date(activity.online_end_time)
                            ];

                            this.params = [];
                            let toStringKey = ['title','stage_id','type','profileType'];
                            if (activity.hasOwnProperty('activities')) {
                                activity.activities.forEach((item)=>{
                                    let obj = {edit:false};
                                let keys = ['title','id','required','type','profileType','correct'];

                                keys.forEach((value)=>{
                                    if (item.hasOwnProperty(value)) {
                                    if (toStringKey.indexOf(value) !== -1) {
                                        obj[value] = item[value].toString();
                                    } else {
                                        obj[value] = item[value];
                                    }
                                }
                            });

                            if(Array.isArray(item.options)) {
                                obj.options = item.options;
                                let type = item.options[0].type.toString();
                                let correct = [];

                                item.options.forEach((value,key) => {
                                    if(parseInt(type) === 1 && parseInt(value.correct) === 1) {
                                        obj.correct = key;
                                        obj.optionType = '1';
                                    }

                                    if (parseInt(type) === 2 && parseInt(value.correct) === 1) {
                                        correct.push(key);
                                        obj.optionType = '2';
                                    }
                                });

                                if (correct.length>0) {
                                    obj.correct = correct;
                                }

                            } else {
                                let keysOption = ['title','id','correct'];

                                keysOption.forEach((value) => {
                                    if (item.options && item.options.hasOwnProperty(value)) {
                                        obj[value] = item.options[value];
                                        if (obj['correct'] === '') {
                                            obj['correct'] = '無';
                                        }
                                    }
                                });
                            }

                            this.params.push(obj);
                        });
                    }

                    this.loading = false;
                        });
                }).catch(() => {
                        this.loading = false;
                });
                };

                if (this.departments.length === 0) {
                    axios.get('/department/getAllUnit').then((response) => {
                        for (let key in response.data.response) {
                        this.departments.push({
                            id:key,
                            name:response.data.response[key]
                        });
                    }

                    callbackActivity();
                });
                } else {
                    callbackActivity();
                }
            },

            getRemainGold : function (callback) {
                axios.get('/gold/account/department?id='+this.activity.department_id + '&action=edit').then((response) => {
                    this.stage_golds = response.data.response.list;
                if (this.stage_golds.length > 0 && this.id <= 0) {
                    this.activity.stage_id = this.stage_golds[0].stage_id.toString();
                }

                if (typeof callback === 'function') {
                    callback();
                }
            });
            },

            setRemainGold : function () {
                var that = this;
                axios.get('/gold/send/getRemainGold?stage_id=' + parseInt(that.activity.stage_id) + '&unit_id=' + parseInt(that.activity.department_id))
                        .then(function (response) {
                            if (response.data.response.remain_gold === undefined) {
                                that.remain_gold = 0;
                            } else {
                                that.remain_gold = response.data.response.remain_gold;
                            }
                        })
                        .catch(function (error) {
                            console.error(error);
                        });
            },

            saveActivity(index) {
                //有獎徵da
                if (this.params[index].type === '2') {

                    if(this.params[index].optionType !== '3') {
                        if (this.params[index].correct === '' || this.params[index].correct.length === 0) {
                            this.dialog.openWarning(() => {

                            }, '請選擇正確答案');
                            return;
                        }
                    }else{
                        if (this.params[index].judgement.length === 1) {
                            if(this.params[index].required === '0') {
                                this.params[index].required = '1';
                                this.dialog.openWarning(() => {

                                }, '有正確答案的爲必填項目');
                                return;
                            }
                            if (this.params[index].correct === '' ) {
                                this.dialog.openWarning(() => {

                                }, '請輸入正確答案');
                                return;
                            }
                        }
                    }
                }

                if(this.params[index].edit) {
                    this.$refs.reference[index].validate((result) => {
                        if (result) {
                            this.params[index].edit = false;
                            this.resetRule();
                        }
                    });
                }
            },

            resetRule() {
                this.activityRadioRule = ActivityRadioRule;
                this.activityTextRule = ActivityTextRule;
                this.activityProfileRule = ActivityProfileRule;
            },

            setStageGold : function (action) {
                if (this.activity.department_id > 0) {
                    axios.get('/gold/account/departmentStage?id=' + this.activity.department_id + '&event_id=' + this.activity.id  + '&action=' + action)
                            .then((response) => {
                        this.stage_golds = response.data.response.list;
                    if (this.activity.stage_id > 0) {
                        this.setRemainGold();
                    }
                })
                .catch((error) => {
                        console.error(error);
                });
                }
            },
        }
    }
</script>

<style scoped>
    .answer_box{ margin:10px 0 30px 0; overflow:hidden; color:#606266; font-size:14px; border-bottom:1px dotted #CCCCCC;}
    .activity{ overflow:hidden; margin-bottom:5px;}
    .q_i{ float:left; font-size:16px; line-height:40px; color:#67c23a;}
    .q_w{ float:left; max-width:calc(100% - 130px); margin-left:10px; margin-right:10px; font-size:18px; line-height:40px; font-weight:bold;}

    .a_item{ float:left; margin:0 20px;}
    .a_item span{ margin-right:30px; line-height:32px;}

    .correct{ clear:both; margin-left:20px; line-height:32px; color:#67c23a;}
</style>