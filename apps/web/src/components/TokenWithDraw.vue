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
        <div
            v-if="showWithdrawRequestForm"
        >
            <div class="title-box">
                {{$t('withdraw')}} ({{tokenSymbol}})
            </div>
            <table
                class='withdraw-request-table'
            >
                <tr>
                    <td>{{$t('token_symbols')}}:</td>
                    <td>{{tokenSymbol}}</td>
                </tr>
                <tr>
                    <td>{{$t('from')}}</td>
                    <td><a :href="withdrawTokenContractUrl" target="_blank">{{withdrawTokenAddress}}</a></td>
                </tr>
                <tr>
                    <td>{{$t('to')}}:</td>
                    <td>{{withdrawRequest.to}}</td>
                </tr>
                <tr>
                    <td>{{$t('amount')}}:</td>
                    <td>{{withdrawRequest.amount.toLocaleString()}} ({{tokenSymbol}})</td>
                </tr>
                <tr>
                    <td>{{$t('total_price')}}:</td>
                    <td>${{fee}}</td>
                </tr>
                <tr>
                    <td>{{$t('status')}}:</td>
                    <td>{{withdrawRequestStatusString(withdrawRequest.status)}}</td>
                </tr>
            </table>
            <div
                v-if="withdrawRequest.status == 1"
                class="withdraw-paypal"
            >
                <PaypalButton
                    :order-id="withdrawRequest.order_id"
                    v-on:payment-success="onPaypalComplete"
                />
                <div class="desc">
                    {{$t('withdraw_fee_desc', {fee: fee})}} <a v-on:click="cancelOrder">{{$t('cancel_order')}}.</a>
                </div>
            </div>
        </div>
        <div
            v-else
            class="token-withdraw"
        >
            <div class="title-box">
                {{$t('withdraw')}} ({{tokenSymbol}})
            </div>
            <div class="label">{{$t('address')}}</div>
            <div class="item">
                <a-input
                    :placeholder="'Please fill in the address'"
                    v-model.lazy="address"
                ></a-input>
            </div>
            <div class="label">{{$t('amount')}}</div>
            <div class="item amount">
                <a-input
                    v-model.lazy="amount"
                ></a-input>
                <span>Available {{tokenSymbol}} {{balance}}</span   >
            </div>
            <div class="label fee">
                <span>{{$t('total_price')}}:</span>
                <span>${{fee}}</span>
            </div>
            <a-button
                type="primary"
                :disabled="!canSave"
                v-on:click="createWithdrawRequest"
            >
                {{$t('continue')}}
            </a-button>
        </div>
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {IS_MOBILE} from '@/helpers/Utils';
    import {WithdrawRequest} from '@/helpers/Interfaces';
    import PaypalButton from '@/components/PaypalButton.vue';

    @Component({
        components: {
            PaypalButton,
        },
    })
    export default class TokenWithDraw extends Vue {
        
        @Prop()
        protected walletId!: number;
        @Prop()
        protected tokenSymbol!: string;
        @Prop()
        protected balance!: string;
        @Prop()
        protected fee!: string;
        @Prop()
        protected withdrawRequest!: WithdrawRequest;
        @Prop()
        protected withdrawTokenContractUrl!: string;
        @Prop()
        protected withdrawTokenAddress!: string;

        protected isMobile: boolean = IS_MOBILE;


        // control whether display the modal
        protected model: boolean = true;

        protected address: string = '';
        protected amount: string = '';

        protected canSave: boolean = false;

        get showWithdrawRequestForm() {
            return this.withdrawRequest && this.withdrawRequest.id;
        }

        @Watch('address', {immediate: true})
        protected onAddress() {
            this.onCanSave();
        }

        @Watch('amount', {immediate: true})
        protected onAmount() {
            if (Number(this.amount) > Number(this.balance)) {
                this.amount = this.balance;
            }

            this.onCanSave();
        }

        protected onCanSave() {
            this.canSave = Number(this.amount) > 0 && !!this.address;
        }

        protected createWithdrawRequest() {
            
            const data = new FormData;

            data.append('wallet_id', this.walletId + '');
            data.append('address', this.address);
            data.append('amount', this.amount);
            
            this.$store.dispatch('Token/createWindrawRequest', data)
            .then((response: Response) => {
                const data: {withdraw: WithdrawRequest} = response.getData();

                if (data && data.withdraw) {
                    this.withdrawRequestCreated(data.withdraw);
                }

            });
        }

        @Emit('withdraw-request-created')
        protected withdrawRequestCreated(withdrawRequest: WithdrawRequest): WithdrawRequest {
            return withdrawRequest;
        }

        private withdrawRequestStatusString(status: number): string {
            switch(status) {
                case 1:
                    return this.$t('to_be_paid') as string;
                case 2:
                case 3:
                case 4:
                    return this.$t('in_transfer') as string;
                case 5:
                    return this.$t('transfer_error') as string;
            }

            return '';
        }

        protected onPaypalComplete() {
            this.withdrawRequest.status = 2;
        }

        protected cancelOrder() {

            const data = new FormData;

            data.append('order_id', this.withdrawRequest.order_id);

            this.$store.dispatch('Token/cancelWindrawRequest', data)
            .then((response: Response) => {
                this.withdrawRequestCancelled();
            });
        }

        @Emit('withdraw-request-cancelled')
        protected withdrawRequestCancelled() {

        }

        @Emit('close-token-withdraw')
        protected onClose() {

        }
    }
</script>
<style lang="scss" scoped>
    .title-box {
        @include modal_title_box;
    }

    .token-withdraw {

        .label {
            @include form_label;

            &.fee {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        .item {
            @include form_item;
        }

        .amount {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;

            .ant-input {
                width: 200px;
                margin-right: var(--p4);
            }

            span {
                @include description_font;
            }
        }

        .ant-btn {
            margin-top: var(--p6);
            width: 120px;
        }
    }

    .withdraw-request-table {
        @include form_table;
    }

    .withdraw-paypal {
        @include paypal_block;
    }
</style>
