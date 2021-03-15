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
        <div class="assets-list">
            <div class="title-box">
                {{$t('my_token')}}
            </div>
            <section
                v-if="tokenListLoaded"
            >
                <a-empty
                    v-if="tokenList.length == 0"
                />
                <div
                    class="token"
                    v-for="item in tokenList"
                    :key="item.id"    
                >
                    <div class="logo">
                        <img
                            v-if="item.logo"
                            :src="item.logo"
                            :alt="item.name"
                        >
                        <img 
                            v-else
                            src="/img/erc20.png" 
                            :alt="item.name"
                        >
                    </div>
                    <div class="name">{{item.name}}({{item.symbol}})</div>
                    <div class="balance">
                        <span
                            v-if="item.balance"
                        >{{decimalToFix(item.balance, 6)}}</span>
                        <span
                            v-else
                        >0.0000</span>
                    </div>
                    <div 
                        class="actions"
                    >
                        <a-button
                            v-if="item.is_import == 1"
                            v-on:click="onWithdraw(item)"
                        >{{$t('withdraw')}}</a-button>
                    </div>
                </div>
            </section>
            <section
                v-else
            >
                <a-skeleton 
                    v-for="i in [1,2]"
                    :key="i"
                    :title="false"
                    :paragraph="{rows: 1}" 
                />
            </section>
        </div>
        <TokenWithDraw
            v-if="showTokenWithDraw"
            :wallet-id="withdrawWalletId"
            :token-symbol="withdrawTokenSymbol"
            :balance="withdrawBalance"
            :fee="withdrawFee"
            :withdraw-request="withdrawRequest"
            :withdraw-token-contract-url="withdrawTokenContractUrl"
            :withdraw-token-address="withdrawTokenAddress"
            v-on:close-token-withdraw="showTokenWithDraw = false"
            v-on:withdraw-request-created="withdrawRequestCreated"
            v-on:withdraw-request-cancelled="withdrawRequestCancelled"
        />
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {IS_MOBILE} from '@/helpers/Utils';
    import {AssetInterface, ERC20TokenInterface, ProductInterface, WithdrawRequest} from '@/helpers/Interfaces';
    import TokenWithDraw from '@/components/TokenWithDraw.vue';

    @Component({
        components: {
            TokenWithDraw,
        },
    })
    export default class TokenAssetsList extends Vue {

        protected model: boolean = true;

        protected isMobile: boolean = IS_MOBILE;

        protected tokenListLoaded: boolean = false;
        protected tokenList: AssetInterface[] = [];

        protected showTokenWithDraw: boolean = false;

        protected withdrawWalletId: number = 0;
        protected withdrawTokenSymbol: string = '';
        protected withdrawBalance: string = '';
        protected withdrawFee: string = '';
        protected withdrawRequest: WithdrawRequest = {} as WithdrawRequest;
        protected withdrawTokenContractUrl: string = '';
        protected withdrawTokenAddress: string = '';

        protected created () {

            this.$store.dispatch('Token/fetchAssetList')
            .then((response: Response) => {
                    const data: {wallets: AssetInterface[]} = response.getData();

                    this.tokenListLoaded = true;

                    if (data && data.wallets) {
                        for (let i in data.wallets) {
                            this.tokenList.push({
                                id: data.wallets[i].id, 
                                token_id: data.wallets[i].token_id,
                                logo: data.wallets[i].logo,
                                name: data.wallets[i].name,
                                symbol: data.wallets[i].symbol,
                                is_import: data.wallets[i].is_import,
                                balance: data.wallets[i].balance,
                            });
                        }
                    }
            });
        }

        protected decimalToFix(num: number | string, fixed: number) {
            const parts = num.toString().split(".");

            if (parts[1] && parts[1].length > fixed) {
                parts[1] = parts[1].substring(0,fixed) + '..';
            }

            return parts.join(".");
        }

        protected onWithdraw(item: AssetInterface) {

            if (Number(item.balance) <= 0) {
                this.$message.info(this.$t('insufficient_balance') as string);
                return;
            }

            // fetch withdraw status
            const data = new FormData;

            data.append('wallet_id', item.id + '');

            this.$store.dispatch('Token/fetchWindrawDetail', data)
            .then((response: Response) => {
                const data: {product: ProductInterface, token: ERC20TokenInterface, withdraw: WithdrawRequest} = response.getData();

                if (data){

                    this.withdrawTokenSymbol = item.symbol;
                    this.withdrawWalletId = item.id;
                    this.withdrawBalance = item.balance + '';
                    this.withdrawFee = data.product.price;

                    if (data.withdraw && data.withdraw.id) {
                        
                        this.withdrawRequest = data.withdraw;

                    } else {
                        // empty the object while switch to another withdraw request
                        this.withdrawRequest = {} as WithdrawRequest;
                    }

                    if (data.token) {
                        this.withdrawTokenAddress = data.token.address as string;
                        this.withdrawTokenContractUrl = data.token.contract_url as string;
                    }

                    this.showTokenWithDraw = true;
                }
            })
            .catch(() => {
                this.$message.error('something is wrong.')
            });
        }

        protected withdrawRequestCreated(withdrawRequest: WithdrawRequest): void {
            this.withdrawRequest = withdrawRequest;
        }

        protected withdrawRequestCancelled(): void {
            this.withdrawRequest = {} as WithdrawRequest;
        }
        
        @Emit('close-assets-list')
        protected onClose() {

        }
    }
</script>
<style lang="scss" scoped>
    .assets-list {
        .title-box {
            @include modal_title_box;
        }

        .ant-empty {
            margin: var(--p4);
        }

        .token {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            padding: var(--p6);
            border-bottom: $border-width $border-style var(--border-color5);

            .logo {
                flex-shrink: 0;
                margin-right: var(--p4);
                img {
                    width: 40px;
                    height: 40px;
                }
            }

            .name {
                flex-shrink: 0;
                flex-basis: 200px;
                color: var(--font-color1);
            }

            .balance {
                @include secondary_title_font;
                flex-grow: 1;
                text-align: left;
            }

            .actions {
                .ant-btn {
                    &:last-child {
                        color: var(--theme-color);
                        margin-left: var(--p4);
                    }
                }
            }
        }

        .ant-skeleton {
            padding: var(--p6);
        }
    }
</style>
