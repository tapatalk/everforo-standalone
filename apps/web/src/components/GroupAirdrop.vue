<template>
    <section>
        <div
            v-if="tokenStatus == 0"
            class="create-import"
        >
            <div class="create-new-token">
                <div class="title">
                    {{$t('create_new_erc20_token')}}
                </div>
                <div class="desc">
                    {{$t('create_new_erc20_token_desc')}}
                </div>
                <a-button
                    type="primary"
                    v-on:click="createTokenForm"
                >
                    {{$t('get_started')}}
                </a-button>
            </div>
            <div class="import-token">
                <div class="title">
                    {{$t('import_erc20_token')}}
                </div>
                <div class="desc padding-desc">
                    {{$t('import_erc20_token_desc')}}
                </div>
                <TokenListDropdown
                    :source-data="importTokenList"
                    :default-value="$t('select_tokens')"
                    v-on:select-item="selectImportToken"
                >
                    <template v-slot:left_icon>
                        <div
                            class="erc20-icon dropdown-left-icon"
                        >
                            <img
                                v-if="selectedImportTokenLogo"
                                :src="selectedImportTokenLogo"
                            >
                            <img
                                v-else
                                src="/img/erc20.png"
                            >
                        </div>
                    </template>
                </TokenListDropdown>
                <a-button
                    class='import-token-btn'
                    type="primary"
                    v-on:click="importToken"
                    :disabled="importTokenId == 0"
                >
                    {{$t('continue')}}
                </a-button>
            </div>
        </div>
        <div
            v-else-if="tokenStatus == 1"
        >
            <table
                class="create-token-table"
            >
                <tr>
                    <td>{{$t('token_logo')}}:</td>
                    <td>
                        <img
                            v-if="tokenLogo"
                            :src="tokenLogo"
                        >
                        <img
                            v-else
                            src="/img/erc20.png"
                        >
                    </td>
                </tr>
                <tr>
                    <td>{{$t('token_name')}}:</td>
                    <td>{{tokenName}}</td>
                </tr>
                <tr>
                    <td>{{$t('token_symbols')}}:</td>
                    <td>{{tokenSymbol}}</td>
                </tr>
                <tr>
                    <td>{{$t('token_decimals')}}:</td>
                    <td>{{tokenDecimal}}</td>
                </tr>
                <tr>
                    <td>{{$t('total_price')}}:</td>
                    <td>${{fee}}</td>
                </tr>
                <tr>
                    <td>{{$t('status')}}:</td>
                    <td
                        v-if="createTokenDisabled"
                    >{{$t('create_token_disabled')}}</td>
                    <td
                        v-else
                    >{{$t('to_be_paid')}}</td>
                </tr>
            </table>
            <div
                v-if="!createTokenDisabled"
                class="create-token-paypal"
            >
                <PaypalButton
                    :key="$store.state.activatedGroupTab"
                    :order-id="$store.state.Group.erc20_token.order_id"
                    v-on:payment-success="onCreateTokenPaypalComplete"
                />
                <div
                    class="desc"
                >
                    {{$t('token_create_fee_desc', {fee: fee})}}
                </div>
            </div>
        </div>
        <div
            v-else-if="tokenStatus == 2 || tokenStatus == 3"
            class="waiting"
        >
            <div class="clock">
                <img src="/img/clock.png" alt="">
            </div>
            <div class="title">
                {{$t('application_submitted')}}
            </div>
            <div class="text">
                {{$t('application_processing')}}
            </div>
            <div class="count-down">
                <span>{{$t('refresh_after', {count_down: countDown})}}</span>
            </div>
        </div>
        <div
            v-else-if="tokenStatus == 4"
            class="success"
        >
            <div class="token-info">
                <div
                    class="logo"
                >
                    <img
                        v-if="tokenLogo"
                        :src="tokenLogo" 
                    >
                    <img
                        v-else
                        src="/img/corn.png"
                    >
                </div>
                <div
                    class="info"
                >
                    <div class="name">
                        <a 
                            :href="tokenUrl"
                            target="_blank"
                        >
                            {{tokenName}}({{tokenSymbol}})
                        </a>
                    </div>
                    <div class="address">
                        <input
                            ref="addressInput"
                            class="address-input"
                            type="text"
                            :value="tokenAddress"
                        >
                        <a-icon
                            type="copy"
                            v-on:click="copyAddress"
                        />
                    </div>
                    <div class="balance">
                        <span>{{$t('balance')}}: {{tokenBalance}}</span>
                        <div 
                            class="refresh"
                            ref="refresh"
                            v-on:click="refreshBalance"
                        >
                            <Icons
                                type="shuaxin"
                            />
                        </div>
                    </div>
                </div>
                <div
                    class="qrcode"
                >
                    <QrCode
                        v-if="tokenAddress"
                        :value="tokenAddress"
                        :options="{width: 100}"
                    />
                </div>
            </div>
            <div class="rules">
                <div class="title">{{$t('airdrop_rule_customization')}}</div>
                <div class="desc">{{$t('customize_rules_desc', {number_rules: numberRules, dropped_30days: dropped30days.toLocaleString()})}}</div>
                <a-button
                    type="primary"
                    v-on:click="customizeRules"
                >
                    {{$t('customize_rules')}}
                </a-button>
                <div class="desc one-desc">{{$t('one_time_airdrop_desc')}}</div>
                <a-button
                    type="primary"
                    v-on:click="performOneTimeAirDrop"
                >
                    {{$t('perform_one_time_airdrop')}}
                </a-button>
            </div>
            <div
                v-if="false" 
                class="other-settings"
            >
                <div class="title">{{$t('other_settings')}}</div>
                <div class="desc">
                    <a-checkbox
                        v-on:change="onSwitchArbitraryAirdrop"
                        :default-checked="false"
                    >
                    </a-checkbox>
                    <span>
                        {{$t('allow_arbitrary_airdrop')}}
                    </span>
                </div>
            </div>
            <div class="delete-token">
                <div class="desc">
                    <span>{{$t('delete_token_desc')}} </span>
                    <a
                        v-on:click="deleteToken"
                    >{{$t('click_here')}}</a>
                </div>
            </div>
        </div>
        <GroupCreateToken
            v-if="tokenStatus == 0 && showCreateTokenForm"
            v-on:close-create-token-form="onCreateTokenformClose"
        />
        <GroupAirdropRules
            v-if="tokenStatus == 4 && showAirdropRules"
            :source-airdrop-list="sourceAirdropList"
            v-on:close-airdrop-rules="showAirdropRules = false"
            v-on:delete_airdrop="deleteAirdrop"
            v-on:airdrop-rules-change="updateAirdropRules"
        />
        <GroupAirdropOneTime
            v-if="tokenStatus == 4 && showAirdropOneTime"
            v-on:close-airdrop-onetime="showAirdropOneTime = false"
        />
        <ConfirmModal
            v-if="showDeleteToken"
            :reverse-button="true"
            :yes-text="$t('delete')"
            :no-text="$t('not_now')"
            v-on:confirm="confirmDelete"
            v-on:cancel="showDeleteToken = false"
        >
            <div class="title">
                <QuestionMark/>
            </div>
            <div
                class="message"
            >
                <div><strong>Are you sure to delete your token?</strong></div>
            </div>
        </ConfirmModal>
    </section>
</template>
<script lang="ts">
    import {Component, Ref, Vue, Watch} from 'vue-property-decorator';
    import {AirdropRuleInterface, ProductInterface, TokenInterface} from '@/helpers/Interfaces';
    import {thousandComma} from '@/helpers/Utils';
    import {Response} from '@/http/Response';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import TokenListDropdown from '@/components/TokenListDropdown.vue';
    import GroupAirdropRules from '@/components/GroupAirdropRules.vue';
    import GroupAirdropOneTime from '@/components/GroupAirdropOneTime.vue'; 
    import GroupCreateToken from '@/components/GroupCreateToken.vue';
    import PaypalButton from '@/components/PaypalButton.vue';
    import QrCode from '@/components/QrCode.vue';
    import QuestionMark from '@/components/QuestionMark.vue';

    @Component({
        components: {
            ConfirmModal,
            TokenListDropdown,
            GroupAirdropRules,
            GroupAirdropOneTime,
            GroupCreateToken,
            PaypalButton,
            QrCode,
            QuestionMark,
        },
    })
    export default class GroupAirdrop extends Vue {
        
        @Ref('addressInput')
        readonly addressInput!: HTMLInputElement;
        @Ref('refresh')
        readonly refresh!: HTMLDivElement;

        protected showCreateTokenForm: boolean = false;
        protected showAirdropRules: boolean = false;
        protected showAirdropOneTime: boolean = false;
        protected animation: number = 0;
        protected countDown: number = 90;

        protected importTokenList: TokenInterface[] = [];
        protected importTokenId: number = 0;
        protected imported: boolean = false;
        protected selectedImportTokenLogo: string = '';

        protected sourceAirdropList: AirdropRuleInterface[] = [];
        protected numberRules: number = 0;
        protected dropped30days: string = '0';

        protected showDeleteToken: boolean = false;

        protected fee: string = '40.00';
        protected createTokenDisabled: boolean = false;

        get tokenName() {
            return this.$store.state.Group.erc20_token.name;
        }

        get tokenBalance() {
            return thousandComma(this.$store.state.Group.erc20_token.balance);
        }

        get tokenSymbol() {
            return this.$store.state.Group.erc20_token.symbol;
        }

        get tokenAddress() {
            return this.$store.state.Group.erc20_token.address;
        }

        get tokenUrl() {
            return this.$store.state.Group.erc20_token.contract_url;
        }

        get tokenDecimal() {
            return this.$store.state.Group.erc20_token.decimal;
        }

        get tokenLogo() {
            return this.$store.state.Group.erc20_token.logo;
        }

        get tokenStatus() {
            return this.$store.state.Group.erc20_token.status;
        }

        protected created() {
            if (this.tokenStatus == 0) {
                this.$store.dispatch('Token/fetchImportList')
                .then((response: Response) => {
                    const data: {tokenlist: TokenInterface[]} = response.getData();

                    if (data && data.tokenlist) {
                        for (let i in data.tokenlist) {
                            this.importTokenList.push({
                                id: data.tokenlist[i].id,
                                logo: data.tokenlist[i].logo,
                                name: data.tokenlist[i].name,
                                symbol: data.tokenlist[i].symbol,
                                contract_address: data.tokenlist[i].contract_address,
                            });
                        }
                    }
                });
            }
        }

        protected beforeDestroy() {
            cancelAnimationFrame(this.animation);
        }

        @Watch('tokenStatus', {immediate: true})
        protected onStatus(val: number) {
            if (this.tokenStatus == 4 && this.sourceAirdropList.length == 0) {
                this.$store.dispatch('AirdropRule/fetchList')
                .then((response: Response) => {

                    const data: {airdrop: AirdropRuleInterface[], award: string, count: string} = response.getData();

                    this.numberRules = parseInt(data.count);
                    this.dropped30days = data.award + '';
                    this.sourceAirdropList = data.airdrop;
                });
            }

            if (this.tokenStatus == 1) {
                this.$store.dispatch('Token/fetchCreateTokenPrice')
                .then((response: Response) => {
                    const data: ProductInterface = response.getData();

                    if (data){
                        this.fee = data.price;

                        if (data.status != 1) {
                            this.createTokenDisabled = true;
                        }
                    }
                })
            }
        }

        protected updateAirdropRules(airdropRules: AirdropRuleInterface[]) {
            this.sourceAirdropList = [];
                
            for (let i in airdropRules) {
                this.sourceAirdropList.push(airdropRules[i]);
            }

            this.numberRules = this.sourceAirdropList.length;
        }

        protected createTokenForm() {
            this.showCreateTokenForm = true;
        }

        protected onCreateTokenPaypalComplete() {
            // paypal payment complete, waiting for token creation script
            this.$store.commit('Group/setTokenStatus', 2);
        }

        protected onCreateTokenformClose() {
            this.showCreateTokenForm = false;
        }

        protected selectImportToken(item: TokenInterface) {
            this.importTokenId = item.id;
            this.selectedImportTokenLogo = item.logo ? item.logo : '';
        }

        protected importToken() {

            const data = new FormData;

            data.append('token_id', this.importTokenId + '');

            this.$store.dispatch('Token/import', data);

            this.$store.commit('Group/setTokenStatus', 2);

            this.imported = true;
        }

        @Watch('tokenStatus', {immediate: true})
        protected onStatusChange(val: number) {
            if (val == 2 || val == 3) {

                if (this.$store.state.Token.createCountDown) {
                    this.countDown = this.$store.state.Token.createCountDown;
                }
                
                if (this.imported ||  this.$store.state.Group.erc20_token.is_import == 1) {
                    this.countDown = 5;
                }

                cancelAnimationFrame(this.animation);
                
                this.countingDown();
            }
        }

        protected countingDown() {
            
            this.animation = requestAnimationFrame(this.countingDown);

            if (this.animation % 60 == 0) {
                this.countDown = this.countDown - 1;

                this.$store.commit('Token/setCreateCountDown', this.countDown);

                if (this.countDown <= 0) {
                    
                    this.$store.dispatch('Group/load');
                    cancelAnimationFrame(this.animation);
                }
            }
        }

        protected copyAddress() {

            if (navigator.clipboard) {
                navigator.clipboard.writeText(this.tokenAddress)
                .then(() => {
                    this.$message.success(this.$t('cpied') as string);
                })
                .catch(() => {
                    this.$message.info(this.$t('copy_failed') as string);
                });
            } else {
                this.addressInput.select();

                let res: boolean = false;

                try {
                    res = document.execCommand("copy");
                }catch(e) {

                }

                if (res) {
                    this.$message.success(this.$t('cpied') as string);
                }else {
                    this.$message.info(this.$t('copy_failed') as string);
                }
            }
        }

        protected customizeRules() {
            this.showAirdropRules = true;
        }

        protected deleteAirdrop(id: number) {
            for (let i in this.sourceAirdropList) {
                if (this.sourceAirdropList[i].id == id) {

                    this.sourceAirdropList.splice(parseInt(i), 1);
                }
            }
        }

        protected performOneTimeAirDrop() {
            this.showAirdropOneTime = true;
        }

        protected refreshBalance() {
            //todo fetch from server
            this.refresh.classList.add('anim');

            this.$store.dispatch('Group/load').then(() => {
                this.refresh.classList.remove('anim');
            });
        }

        // protected onSwitchArbitraryAirdrop() {
        //     // this.$message.success('to be continue...');
        // }

        protected deleteToken() {
            this.showDeleteToken = true;
        }

        protected confirmDelete() {
            this.$store.dispatch('Group/deleteERC20Token')
            .then(() => {
                this.$store.commit('Group/setTokenStatus', 0);
                this.showDeleteToken = false;
            });
        }
    }
</script>
<style lang="scss" scoped>
    .title {
        @include title_font;
        font-size: $font-size2;
    }
    .padding-desc {
        padding-bottom: var(--p6);
    }
    .desc {
        @include description_font;
        font-size: $upload-desc-font-size;
        line-height: 1.3rem;
    }
    .create-import {
        .create-new-token, .import-token {
            padding-top: var(--p2);
            padding-bottom: var(--p6);
            .ant-btn {
                width: 140px;
                margin-top: var(--p6);
            }
        }

        .create-new-token {
            border-bottom: $border-width $border-style var(--border-color2);
        }

        .import-token {
            .erc20-icon {
                display: flex;
                flex-direction: column;
                justify-content: space-around;
                align-items: center;

                img {
                    width: 50%;
                    height: 50%;
                }
            }
        }

        .import-token-btn {
            margin-bottom: 200px;
        }
    }

    .create-token-table {
        @include form_table;
    }

    .create-token-paypal {
        @include paypal_block;
    }

    .waiting {
        text-align: center;

        .clock {
            padding: 80px 0 30px;
            height: 120px;
            box-sizing: content-box;

            img {
                max-height: 100%;
            }
        }
        .title {
            @include title_font;
        }
        .text {
            @include content_font;
        }
        .count-down {
            margin: 0 0 100px;
        }
    }

    .success {

        .token-info {
            padding: var(--p2) 0;
            border-bottom: $border-width $border-style var(--border-color5);
            display: flex;
            flex-direction: row;
            //justify-content: center;
            //align-items: center;
            padding-bottom: var(--p6);
            .logo {
                margin-right: var(--p4);
                img {
                    width: 50px;
                    height: 50px;
                    margin-top: var(--p1);
                }
            }

            .info {
                flex-grow: 1;

                .name {
                    @include title_font;
                    font-size: $font-size2;
                    a {
                        color: var(--font-color1);

                        &:hover {
                            color: var(--theme-color);
                        }
                    }
                }

                .address {
                    @include description_font;

                    .address-input {
                        color: #36989A;
                        border: 0;
                        width: 380px;
                        display: inline-block;
                        padding: 0;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                }

                .balance {
                    margin-top: var(--p2);
                    display: flex;
                    color: var(--font-color1);
                    .refresh {
                        margin-left: var(--p2);
                        width: 1em;
                        height: 1em;
                        cursor: pointer;
                        
                        &.anim {
                            animation: spin 1s linear normal;
                        }
                    }
                }
            }

            .qrcode {
                margin-left: var(--p4);
            }
        }

        .rules {
            padding: var(--p6) 0 var(--p6);
            border-bottom: $border-width $border-style var(--border-color5);

            .desc {
                @include content_font;
                margin: var(--p2) 0 var(--p2);
            }
            .one-desc {
                margin-top: var(--p6);
            }
            
        }

        .other-settings {
            padding: var(--p4) 0;
            border-bottom: $border-width $border-style var(--border-color5);

            .desc {
                @include content_font;
            }
        }

        .delete-token {
            .desc {
                margin-top: var(--p2);
                font-size: $upload-desc-font-size;
            }
        }
    }

    .ant-modal {
        .title {
            text-align: center;
        }
        .message {
            margin: var(--p6) 0;
            text-align: center;
        }
    }
</style>
