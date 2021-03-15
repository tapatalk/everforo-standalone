<template>
    <div>
        <div
            class="title"
        >{{$t('aim_to_qrcode')}}</div>
        <div class="qr-code">
            <QrCode
                v-if="qrCodeValue"
                :value="qrCodeValue"
                :options="{width: 300}"
            />
        </div>
        <a-row :gutter="19" class="store-row">
            <a-col :span="12">
                <a target="_blank" v-bind:href="iosDownloadLink">
                    <img src="/img/apple-store.png" />
                </a>
            </a-col>
            <a-col :span="12">
                <a target="_blank" v-bind:href="androidDownloadLink">
                    <img src="/img/google@2x.png" />
                </a>
            </a-col>
        </a-row>
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import Echo from 'laravel-echo';
    import RequestMethods from '@/http/RequestMethods';
    import {Response} from '@/http/Response';
    import {ResponseError} from '@/http/ResponseError';
    import {StorageLocal} from "@/helpers/Utils";
    import {iOSAppLinkInStoreForPC,androidAppLinkInStoreForPC} from '@/helpers/AppLinkUtil';
    import QrCode from '@/components/QrCode.vue';

    @Component({
        components: {
            QrCode,
        },
    })
    export default class LoginQrCode extends Vue {
        /**
         * this component is for app scan qr code login
         */
        protected qrCodeValue: string = "";
        protected iosDownloadLink = iOSAppLinkInStoreForPC();
        protected androidDownloadLink = androidAppLinkInStoreForPC();

        protected created() {

            (window as any).io = require('socket.io-client');

            if (typeof (window as any).io !== 'undefined') {

                (window as any).Echo = new Echo({
                    broadcaster: 'socket.io',
                    host: process.env.VUE_APP_API_URL,
                    path: '/ws/socket.io',
                    rejectUnauthorized: false,
                });

                RequestMethods.fetch({route: "api/qr_code"})
                    .then((response) => {

                        const data: any = (response as Response).getData();
                        let channel: string = '';
// console.log('qr_code', response);
                        if (data) {

                            channel = data.token;

                            this.qrCodeValue = "https://app.everforo.com?amv=1&apn=com.everforo.android&ibi=com.everforo.everforo&imv=0&isi=1509209144&link=" 
                                            + encodeURIComponent('https://everforo.com?channel_token=' + channel);
                        }

                        if (channel) {
                            (window as any).Echo
                                .channel(channel)
                                .listen('LoginEvent', (eventData: { token: string, bearer: string }) => {
                                // eventData.token is actually the channel, bearer is jwt token. 
                                // we should change this notation to more instinctive one
// console.log('event', eventData);
                                    
                                    if (eventData.token === channel) {
                                        StorageLocal.setItem('bearer', eventData.bearer);
                                        this.$store.dispatch('User/getMe')
                                        .then(() => {
                                            this.$store.commit('setShowLoginModal', false);
                                        })
                                        .catch((error: ResponseError) => {
                                            StorageLocal.removeItem('bearer');
                                            this.$message.error(this.$t('login_failed') as string);
                                        });
                                    }
                                });
                        }
                    });
            }
        }
        
    }
</script>
<style lang="scss" scoped>
    .title {
        @include secondary_title_font();
        margin-bottom: var(--p3);
    }

    .qr-code {
        width: 300px;
        overflow: hidden;
        margin: 0 auto;
        border-radius: 4px;
        -webkit-box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
        
    .store-row {
        img {
            // width: 118px;
            max-width: 140px;
        }

        div:first-child {
            text-align: right;
        }

        div:last-child {
            text-align: left;
        }

        margin-top: 30px;
    }
</style>
