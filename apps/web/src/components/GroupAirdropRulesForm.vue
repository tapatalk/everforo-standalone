<template>
    <a-modal
        v-model="model"
        width="660px"
        :closable="false"
        :footer="null"
        :centered="true"
        v-on:cancel="onClose"
    >
        <div
            :class="['modal-close-btn', {'mobile': isMobile}]"
            v-on:click="onClose"
        >
            <Icons
                type="chacha"
            />
        </div>
        <div class="airdrop-rules-form">
            <div class="title-box">
                {{$t('rules')}}
                <a-button
                    type="link"
                    :disabled="!canSave"
                    v-on:click="onSubmit"
                >
                    {{$t('save')}}
                </a-button>
            </div>

            <div class="rules-form">
                <div class="label">{{$t('rule_name')}}</div>
                <div class="item">
                    <a-input
                        type="text"
                        v-model.lazy="ruleModel.rule_name"
                    />
                </div>
                <div class="label">{{$t('amount')}}</div>
                <div class="item amount">
                    <a-input
                        type="number"
                        v-model.lazy="ruleModel.award_count"
                    />
                </div>
                <div class="label">{{$t('execute_when_member')}}</div>
                <div>
                    <div
                        class="options"
                        v-for="item in options"
                        :key="item.key"
                    >
                        <a-checkbox
                            v-on:change="selectOption"
                            :checked="item.value"
                            :value="item.key"
                        ></a-checkbox>
                        <span>{{$t(item.text1)}}</span>
                        <a-input
                            v-model.lazy="item.num"
                        />
                        <span
                            class='lower'
                        >{{$tc(item.text2, 0)}}</span>
                    </div>
                </div>
            </div>

            <div class="other-settings">
                <div class="title">{{$t('other_settings')}}</div>
                <div class="desc">
                    <a-checkbox
                        v-on:change="onSwitchRecurring"
                        :checked="ruleModel.repeat == 1"
                        :value="ruleModel.repeat"
                    >
                    </a-checkbox>
                    <span>
                        {{$t('recurring_rule_desc')}}
                    </span>
                </div>
            </div>

        </div>
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';
    import {AirdropRuleInterface} from '@/helpers/Interfaces';

    @Component({
        components: {
            
        },
    })
    export default class GroupAirdropRulesForm extends Vue {

        @Prop()
        public defaultValue!: AirdropRuleInterface;

        protected model: boolean = true;
        protected ruleModel: AirdropRuleInterface = {
            id: 0,
            rule_name: '',
            award_count: 0,
            action: '',
            condition: '',
            repeat: 1,
            total_count: 0,
        };

        protected isMobile: boolean = IS_MOBILE;

        protected topicAmount: number = 0;

        protected canSave: boolean = false;

        protected options: any[] = [
            {key: 'receive_likes', value: false, num: 0, text1: 'received', text2: 'likes'},
            {key: 'topics', value: false, num: 0, text1: 'created', text2: 'topics'},
            {key: 'topic_receive_reply', value: false, num: 0, text1: 'topic_received', text2: 'replies'},
        ];

        protected created() {
            if (this.defaultValue && this.defaultValue.id) {
                Object.assign(this.ruleModel, this.defaultValue);

                try {

                    if (this.defaultValue.condition){
                        const condition: any = JSON.parse(this.defaultValue.condition);

                        const keys = Object.keys(condition);

                        for (let i in this.options) {
                            if (keys.indexOf(this.options[i].key) != -1) {
                                
                                this.ruleModel.action = this.options[i].key;
                                this.ruleModel.condition = condition[this.options[i].key as string];
                                
                                this.options[i].value = true;
                                this.options[i].num = condition[this.options[i].key as string];
                                break;
                            }
                        } 

                    }
                } catch(e) {
                    // console.info('fucking shit');
                }

            }
        }

        @Watch('ruleModel', {deep: true, immediate: true})
        protected onRuleChange() {
            if (Number(this.ruleModel.award_count) < 0) {
                this.ruleModel.award_count = 1;
            }

            this.onCanSave();
        }

        @Watch('options', {deep: true})
        protected onOptionsChange() {
            for (let i = 0; i < this.options.length; i++) {
                if (this.options[i].num < 0) {
                    this.options[i].num = this.options[i].num * -1;
                }

                this.options[i].num = parseInt(this.options[i].num);

                if (typeof this.options[i].num !== 'number' || isNaN(this.options[i].num)) {
                    this.options[i].num = 1;
                }
            }

            this.onCanSave();
        }

        protected onCanSave() {

            if (!this.ruleModel.rule_name) {
                this.canSave = false;
                return;
            }  

            if (this.ruleModel.award_count <= 0) {
                this.canSave = false;
                return;
            }

            for (let i = 0; i < this.options.length; i++) {

                if (this.options[i].value && Number(this.options[i].num) > 0) {
                    this.canSave = true;
                    return;
                }
            }

            this.canSave = false;
        }

        protected selectOption(e: {target: any}) {

            for (let i in this.options) {
                if (this.options[i].key == e.target.value) {
                    this.options[i].value = true;
                    this.ruleModel.action = this.options[i].key;
                } else {
                    this.options[i].value = false;
                    this.options[i].num = 0;
                }
            }
        }

        protected onSwitchRecurring(e: {target: any}) {
            if (e.target.checked) {
                this.ruleModel.repeat = 1;
            } else {
                this.ruleModel.repeat = 0;
            }
        }

        protected onSubmit() {
            for (let i in this.options) {
                if (this.options[i].key == this.ruleModel.action) {
                    this.ruleModel.condition = this.options[i].num;
                }
            }
            
            const data = new FormData;

            data.append('rule_name', this.ruleModel.rule_name);
            data.append('award_count', this.ruleModel.award_count + '');
            data.append('require_count', this.ruleModel.condition + '');
            data.append('type', this.ruleModel.action);
            data.append('repeat', this.ruleModel.repeat + '');

            if (this.ruleModel.id) {

                data.append('airdrop_id', this.ruleModel.id + '');

                this.$store.dispatch('AirdropRule/edit', data)
                .then((response) => {

                    if(response.getCode() != '20000'){

                        if(response.getDescription() != ''){
                            this.$message.info(response.getDescription() as string);
                        } else {
                            this.$message.info(this.$t('network_error') as string);
                        }

                    } else {

                        const data: {airdrop: AirdropRuleInterface} = response.getData();

                        if (data && data.airdrop) {
                            // because the server deleted the old rule and created a new rule,
                            // so we assign the old id to the newly created rule temporarily,
                            // just to make the page respond correctly 
                            data.airdrop.id = this.ruleModel.id;

                            this.onEditRule(data.airdrop);
                        }
                    }

                    this.onClose();
                });

            } else {

                this.$store.dispatch('AirdropRule/create', data)
                .then((response) => {

                    if(response.getCode() != '20000'){

                        if(response.getDescription() != ''){
                            this.$message.info(response.getDescription() as string);
                        } else {
                            this.$message.info(this.$t('network_error') as string);
                        }

                    } else {
                    
                        const data: {airdrop: AirdropRuleInterface} = response.getData();

                        if (data && data.airdrop) {
                            this.onNewRule(data.airdrop);
                        }
                    }

                    this.onClose();
                });
            }
        }

        @Emit('new-airdrop-rules')
        protected onNewRule(airdropRule: AirdropRuleInterface) {
            return airdropRule;
        }

        @Emit('edit-airdrop-rules')
        protected onEditRule(airdropRule: AirdropRuleInterface) {
            return airdropRule;
        }

        @Emit('close-airdrop-rules-form')
        protected onClose() {

        }
    }
</script>
<style lang="scss" scoped>
    .airdrop-rules-form {
        .title-box {
            @include modal_title_box;
        }

        .rules-form {

            border-bottom: $border-width $border-style var(--border-color2);
            
            .label {
                @include form_label;
            }

            .item {
                @include form_item;
                .ant-input {
                    height: 40px;
                }
            }

            .amount {
                .ant-input {
                    width: 200px;
                }
            }

            .options {
                margin-bottom: var(--p4);
                .ant-checkbox-wrapper {
                    margin-right: var(--p2);
                }

                .ant-input {
                    width: 80px;
                    margin: 0 var(--p2);
                }

                .lower {
                    text-transform: lowercase;
                }
            }
        }

        .other-settings {
            padding: var(--p4) 0;
            .title {
                @include title_font;
            }

            .desc {
                @include content_font;
            }
        }
    }
</style>
