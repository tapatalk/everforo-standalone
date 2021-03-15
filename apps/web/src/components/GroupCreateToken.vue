<template>
    <a-modal
        v-model="showCreateToken"
        width="660px"
        :closable="false"
        :footer="null"
        :centered="true"
        v-on:cancel="onCloseForm"
    >
        <div
            :class="['modal-close-btn', {'mobile': isMobile}]"
            v-on:click="onCloseForm"
        >
            <Icons
                type="chacha"
            />
        </div>
        <div class="create-form">
            <div class="title-box">
                {{$t('create_new_erc20_token')}}
            </div>
            <a-row>
                <div class="label">{{$t('token_logo')}}</div>
                <div
                    class="item"
                >
                    <div class="logo-box">
                        <MediaInput
                            v-on:file-uploaded="onLogoUpload"
                            v-on:file-cleared="onLogoCleared"
                            :borderRadius = 8
                            :clear-file="clearLogo"
                            :width="undefined"
                            :height="undefined"
                            :default-media="undefined"
                            :description="undefined"
                            :camera-color="undefined"
                        />
                        <div 
                            v-if="logoObject"
                            v-on:click="onClearCover"
                            class="close"
                        >
                            <Icons type="guanbi"/>
                        </div>
                    </div>
                    <div class="desc">
                        {{$t('token_logo_desc')}}
                    </div>
                </div>
            </a-row>
            <a-row>
                <div class="label">{{$t('token_name')}}</div>
                <div
                    class="item"
                >
                    <a-input                
                        size="large"
                        v-model.lazy="tokenName"
                        :maxLength=10
                    />
                    <div class="desc">
                        {{$t('token_name_desc')}}
                    </div>
                </div>
            </a-row>
            <a-row>
                <div class="label">{{$t('token_symbols')}}</div>
                <div
                    class="item"
                >
                    <a-input                
                        size="large"
                        v-model.lazy="tokenSymbol"
                        :maxLength=4
                    />
                    <div class="desc">
                        {{$t('token_symbol_desc')}}
                    </div>
                </div>
            </a-row>
            <a-row>
                <div class="label">{{$t('token_balance')}}</div>
                <div
                    class="item"
                >
                    <a-input                
                        size="large"
                        v-model.lazy="tokenBalance"
                        type="number"
                        :maxLength=30
                    />
                    <div class="desc">
                        {{$t('token_balance_desc')}}
                    </div>
                </div>
            </a-row>
            <a-row>
                <div class="label">{{$t('token_decimals')}}</div>
                <div
                    class="item"
                >
                    <a-input                
                        size="large"
                        v-model.lazy="tokenDecimal"
                        type="number"
                        :maxLength=2
                    />
                    <div class="desc">
                        {{$t('token_decimals_desc')}}
                    </div>
                </div>
            </a-row>
            <a-row class='fee'>
                <a-button
                    type="primary"
                    v-on:click="createToken"
                    :disabled="!canSave"
                >{{$t('continue')}}</a-button>
            </a-row>
        </div>
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Vue, Watch} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {IS_MOBILE} from "@/helpers/Utils";
    import {ERC20TokenInterface, UploadedImageUrl} from '@/helpers/Interfaces';
    import MediaInput from '@/components/MediaInput.vue';

    @Component({
        components: {
            MediaInput,
        },
    })
    export default class GroupCreateToken extends Vue {

        protected showCreateToken: boolean = true;

        protected isMobile: boolean = IS_MOBILE;

        protected clearLogo: boolean = false;
        protected logoObject: string | Blob = '';

        protected canSave: boolean = false;

        set tokenName(name: string) {
            this.$store.commit('Group/setTokenName', name);
        }

        set tokenBalance(balance: number) {
            this.$store.commit('Group/setTokenBalance', balance);
        }

        set tokenSymbol(symbol: string) {
            this.$store.commit('Group/setTokenSymbol', symbol);
        }

        set tokenDecimal(decimal: number) {
            this.$store.commit('Group/setTokenDecimal', decimal);
        }

        set tokenLogo(logo: string) {
            this.$store.commit('Group/setTokenLogo', logo);
        }

        get tokenName() {
            return this.$store.state.Group.erc20_token.name;
        }

        get tokenBalance() {
            return parseInt(this.$store.state.Group.erc20_token.balance);
        }

        get tokenSymbol() {
            return this.$store.state.Group.erc20_token.symbol;
        }

        get tokenAddress() {
            return this.$store.state.Group.erc20_token.address;
        }

        get tokenDecimal() {
            return this.$store.state.Group.erc20_token.decimal;
        }

        get tokenLogo() {
            return this.$store.state.Group.erc20_token.logo;
        }

        @Watch('tokenName', {immediate: true})
        protected onTokenName() {
            this.onCanSave();
        }

        @Watch('tokenSymbol')
        protected onTokenSymbol() {
            this.onCanSave();
        }

        @Watch('tokenBalance')
        protected onTokenBalance() {

            if (typeof this.tokenBalance !== 'number' || isNaN(this.tokenBalance)) {
                this.tokenBalance = 0;
            }

            if (this.tokenBalance < 0) {
                this.tokenBalance = this.tokenBalance * -1;
            }

            this.onCanSave();
        }

        @Watch('tokenDecimal')
        protected onTokenDecimal() {
            if (this.tokenDecimal < 0) {
                this.tokenDecimal = this.tokenDecimal * -1;
            }
        }

        protected onCanSave() {
            if (this.tokenName && this.tokenSymbol && this.tokenBalance) {
                this.canSave = true;
            } else {
                this.canSave = false;
            }
        }

        protected onLogoUpload(fileObject: string | Blob) {
            this.logoObject = fileObject;
        }

        protected onLogoCleared() {
            this.clearLogo = false;
        }

        protected onClearCover() {
            this.logoObject = '';
            this.clearLogo = true;
        }

        protected async createToken() {
            
            if (this.tokenName.length < 3) {
                this.$message.error(this.$t('must_between_character_length', {property: this.$t('token_name'), min: 3, max: 10}) as string);
            }

            if (this.tokenSymbol.length < 3) {
                this.$message.error(this.$t('must_between_character_length', {property: this.$t('token_symbol'), min: 3, max: 4}) as string);
            }

            if (!this.tokenName || !this.tokenSymbol) {                
                return;
            }

            const data = new FormData;

            data.append('name', this.tokenName);
            data.append('symbol', this.tokenSymbol);
            data.append('balance', this.tokenBalance + '');
            data.append('decimal', this.tokenDecimal + '');

            if (this.logoObject) {
                const uploadedLogoUrl: UploadedImageUrl = await this.$store.dispatch('Attachment/uploadGroupPic',
                    {dataUrl: this.logoObject});

                data.append('logo', uploadedLogoUrl.url);
            }

            this.$store.dispatch('Group/createERC20Token', data)
            .then((response: Response) => {

                const data: {order: {order_id: string}} = response.getData();

                if (data && data.order) {
                    // token order created, waiting user to pay the fee via paypal
                    this.$store.commit('Group/setTokenStatus', 1);
                    this.$store.commit('Group/setTokenOrderId', data.order.order_id);
                }
            });
            
            this.onCloseForm();
        }

        @Emit('close-create-token-form')
        protected onCloseForm() {

        }
        
    }
</script>
<style lang="scss" scoped>
    .create-form {

        .title-box {
            @include modal_title_box;
        }

        .label {
            @include form_label;
        }

        .item {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;

            $input-width: 40%;

            .logo-box {
                position: relative;

                .media-holder {
                    width: 50px;
                    min-width: 50px;
                    height: 50px;
                    margin-right: var(--p4);
                }

                .close {
                    position: absolute;
                    top: 0;
                    right: var(--p2);
                    cursor: pointer;
                }
            }

            .ant-input {
                width: $input-width;
                min-width: $input-width;
                margin-right: var(--p4);
            }

            .desc {
                @include description_font;
                font-size: $upload-desc-font-size;
                line-height: 1.3rem;
            }
        }

        .ant-btn {
            margin-top: var(--p6);
        }

        .fee {
            .label {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .desc {
                @include description_font;
                margin-top: var(--p4);
                font-size: $upload-desc-font-size;
                line-height: 1.3rem;
            }
        }
    }
</style>
