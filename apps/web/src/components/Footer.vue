<template>
    <div >
        <div
            :class="['footer-wrapper1', {'mobile': isMobile}]"
        >
            <div
                :class="['more-to-come-wrapper', {'mobile': isMobile}]"
            >
                <div
                    class="more-to-come"
                >

                    <div
                        :class="['title',{'mobile': isMobile}]"
                        v-html="$t('more_to_come')"
                    >
                    </div>
                    <div
                        :class="['desc', {'mobile': isMobile}]"
                    >
                        {{$t("more_to_come_desc")}}
                    </div>
                </div>
            </div>

            <div
                :class="['migrate-to-everforo-wrapper', {'mobile': isMobile}]"
            >
                <div
                    :class="['migrate-to-everforo', {'mobile': isMobile}]"
                >
                    <div
                        :class="['title',{'mobile': isMobile}]"
                        v-html="$t('migrate_to_everforo')"
                    >
                    </div>
                    <div
                        :class="['desc', {'mobile': isMobile}]"
                    >
                        {{$t("migrate_to_everforo_desc")}}
                    </div>
                    <div
                        :class="['contact',{'mobile': isMobile}]"
                    >
                        <a href="https://docs.google.com/forms/u/7/d/1ON905CJfObU5f-VsWI0F_d-vmlNlIM_L4EKWDPzWaJk" target="_blank">{{$t('contact_us')}}</a>
                    </div>
                </div>
            </div>
        </div>

        <div :class="['footer', {'mobile':isMobile}]">
            <ul>
                <li 
                    v-for="(doc, index) in docs"
                    :key="index"
                >
                    <router-link
                        :to="{name: doc.link}"
                    >{{$t(doc.name)}}</router-link>
                </li>
            </ul>
        </div>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';

    @Component
    export default class Footer extends Vue {
        protected isMobile: boolean = IS_MOBILE;

        protected docs: { name: string, link: string }[] = [
            {name: 'privacy_policy', link: 'privacy'},
            {name: 'everforo_inc', link: 'homehome'},
            {name: 'EULA', link: 'eula'}
        ];

    }
</script>
<style lang="scss" scoped>
    .footer-wrapper1 {
        color: $font-color-contrast;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        height: 500px;
        width: 100%;
        padding: 0 5%;
        box-sizing: border-box;
        background: var(--theme-color);

        &.mobile {
            display: block;
            height: auto;
        }

        .more-to-come-wrapper {
            width: 50%;

            &.mobile {
                padding-left: 5%;
                padding-right: 5%;
                padding-top: 20px;
                width: 100%;
            }

            .more-to-come {
                max-width: 100%;
            }
        }


        .migrate-to-everforo-wrapper {
            background: #374B75;
            max-height: 100%;
            min-height: 100%;
            overflow: hidden;

            &.mobile {
                padding-top: 40px;
                padding-bottom: 20px;
                display: block;
                width: 100%;
            }

            .migrate-to-everforo {
                max-width: 600px;
                padding-left: 80px;
                margin-top: 100px;
                margin-bottom: 100px;

                &.mobile {
                    padding-left: 5%;
                    padding-right: 5%;
                    margin-top: 0;
                    margin-bottom: 0;
                }


            }
            .contact {
                width: fit-content;
                font-size: 1rem;
                line-height: 1.6rem;
                background-color: #ffffff;
                padding: 12px 50px;
                border-radius: 8px;
                margin-bottom:  0px;

                a {
                    color: #333333;
                    font-weight: 500;
                }

                &.mobile {
                    font-size: 1rem;
                    line-height: 1.3rem;
                    margin-top: 38px;
                    width: 220px;
                    height: 40px;
                }
            }
        }

        .title {
            margin: 0px 0 16px 0;
            font-size: $font-size14;
            line-height: $font-size14-line-height;
            font-weight: 500;
            text-align: left;

            &.mobile {
                margin-top: 24px;
                font-size: 1.3rem;
                line-height: 2rem;
            }
        }

        .desc {
            max-width: 1036px;
            width: 90%;
            font-size: $font-size3;
            line-height: $font-size3-line-height;
            margin-bottom: 48px;
            text-align: left;

            &.mobile {
                font-size: 1rem;
                line-height: 1.6rem;
            }
        }
    }

    .footer {
            max-width: 100%;
            padding-top: 20px;
            padding-bottom: 40px;
            text-align: center;

            &.mobile {
                a {
                    font-size: 0.8rem;
                }
            }
            

            li {
                display: inline-block;
                padding: 0 var(--p6);
                border-right: 1px solid var(--font-color2);
                &:last-of-type{
                    border-right: 0;
                }
                a {
                    color: var(--font-color2);
                    font-size: 1rem;
                    line-height: 1.6rem;
                }
            }
    }
</style>