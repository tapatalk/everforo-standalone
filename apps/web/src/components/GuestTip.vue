<template>
    <a-row
        v-if="showTipWrapper"
        class="why-everforo-tip-wrapper"
    >
        <a-col
            :span="(isMobile || contentWidth <960)?24:12"
        >
            <div :class="['grow-and-manage-area',{'mobile':isMobile}]">
                <div :class="['why-everforo-tip-title']">
                    {{$t('home_title')}}
                </div>
            </div>
            <div
                class='button-box'
            >
                <a-col :span="(isMobile || contentWidth <960)?7:11">
                    <a
                        v-on:click="showLoginModal"
                    >
                        <div
                            :class="['button', {'mobile':isMobile}]"
                        >
                            <span>{{$t('join_group')}}</span>
                        </div>
                    </a>
                </a-col>
                <a-col :span="11">
                    <router-link :to="{name: `homehome` }">
                        <div class="go button">
                            <span>{{$t('learn_more')}}</span>
                        </div>
                    </router-link>
                </a-col>
            </div>
        </a-col>
        <a-col
            v-if="!isMobile&&contentWidth>=960"
            :span=12
        >
            <img 
                src="/img/home-home/cover_mobile.png" 
                class="why-everforo-image"
            />
        </a-col>
    </a-row>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {IS_MOBILE, StorageLocal, windowWidth} from '@/helpers/Utils';

    @Component({
        components: {

        },
    })
    export default class GuestTip extends Vue {

        protected contentWidth:Number = windowWidth();

        protected isMobile:boolean = IS_MOBILE;

        protected showTipWrapper: boolean = false;

        get userId(): number {
            return this.$store.state.User.id;
        }

        @Watch('userId', {immediate: true})
        protected onUserId(val: number) {
            if (StorageLocal.getItem('bearer')) {
                this.showTipWrapper = false;
            }else if (!val) {
                this.showTipWrapper = true;
            }
        }

        protected showLoginModal() {
            this.$store.commit('setShowLoginModal', true);
        }

        
    }
</script>
<style lang="scss" scoped>
    .why-everforo-tip-wrapper {
        background: #f8f8fd;
        height: 320px;
    }

    .grow-and-manage-area {
        margin: auto;
        height: auto;
        width: auto;
    }

    $tip-margin-left: var(--p9);

    .why-everforo-tip-title {
        font-size: $font-size14;
        line-height: $font-size14-line-height;
        margin-left: $tip-margin-left;
        margin-top: 54px;
    }

    .why-everforo-image {
        width: 100%;
        margin-left: 20px;
        height: 320px;
        object-fit: cover;

        img {
            max-width: 100%;
        }
    }

    .button-box {

        margin-left: $tip-margin-left;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;

        .button {
            width: 100%;
            height: $common-btn-height;
            padding: 14px 0px;
            margin-top: var(--p6);
            text-align: center;
            font-weight: 500;
            border-radius: $border-radius1;
            background-color: var(--theme-color);
            box-shadow: 4px 4px 16px 0px rgba(0,0,0,0.09);
            cursor: pointer;
            color: $font-color-contrast;

            &.go {
                background: var(--body-bg);
                color: var(--theme-color);
            }

        }
    }

    @media (max-width: 960px) {
        .why-everforo-tip-wrapper{
            height: 180px;
        }

        .grow-and-manage-area {
            text-align: left;
            width: fit-content;
            margin-right: $tip-margin-left;
        }

        .why-everforo-tip-title {
            margin-top: 30px;
            font-size: 1.6rem;
            line-height: 2.1rem;
        }

        .button-box {
            justify-content: flex-start;

            > div:last-child {
                margin-left: $tip-margin-left;
            }
            
        }
    }

    @media (max-width: 400px) {
        .why-everforo-tip-wrapper {
            height: 220px;
        }
    }
</style>
