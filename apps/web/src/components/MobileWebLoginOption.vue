<template>
    <section>
        <div class="wrapper">
            <img src="/img/blue-logo.png" />
            <div class="title">{{$t('welcome_to')}}</div>
            <div class="label">{{$t('respect_privacy')}} {{$t('sign_in_intro')}}</div>
            <a-button
                type="default"
                size="large"
                v-on:click="continueWithEmail"
            >{{$t('continue_with_email')}}
            </a-button>
            <a-button
                type="primary"
                size="large"
                v-on:click="continueWithApp"
            >{{$t('continue_with_app')}}
            </a-button>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';

    @Component({
        components: {

        },
    })
    export default class MobileWebLoginOption extends Vue {
        // When user clicks on login on mobile browser, they can choose to continue with app
        // This link is a firebase dynamic link, it will open Everforo app if installed or app store if not installed
        protected continueWithApp() {
            window.open('https://app.everforo.com?amv=1&apn=com.everforo.android&ibi=com.everforo.everforo&imv=0&isi=1509209144&link=https%3A%2F%2Feverforo.com');
        }

        @Emit('continue-with-email')
        protected continueWithEmail() {

        }
        
    }
</script>
<style lang="scss" scoped>
    section {

        @include modal_flexbox;

        .wrapper {
            text-align: center;
            width: 110%;

            img {
                width: 54px;
                height: 54px;
                margin-bottom: var(--p6);
            }

            .title {
                @include title_font;
                margin-bottom: var(--p4);
            }

            .label {
                margin-bottom: var(--p8);
                font-size: 1rem;
                line-height: 1.3rem;
            }

            .ant-btn {
                width: 100%;
                height: 50px;
                margin-bottom: var(--p6);
                font-size: 1rem;
            }
        }
    }
</style>
