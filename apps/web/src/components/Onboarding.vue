<template>
    <section
        :class="['onboarding', {'mobile': isMobile}]"
    >
        <div class="wrapper">
            <div
                v-if="!isMobile"
                class="title"
            >{{title}}</div>

            <div 
                v-if="!isMobile"
                class="desc"
            >
                <span>{{desc}}</span>
            </div>

            <div
                class="flexbox"
            >

                <div
                    class='email-login'
                >
                    <div
                        :class="['label',{'mobile':isMobile}]"
                    >{{$t('email_continue')}}</div>
                    <a-input
                        :placeholder="$t('email')"
                        size="large"
                        v-model.lazy="email"
                        v-on:pressEnter="checkData"
                        ref="emailInput"
                    />
                    <a-button
                        type="primary"
                        size="large"
                        v-on:click="checkData"
                        :class="[{'mobile':isMobile}]"
                    >{{$t('continue')}}
                    </a-button>
                    <div 
                        :class="['register-note',{'mobile':isMobile}]"
                    >
                        {{$t('register_note')}}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Ref, Vue} from 'vue-property-decorator';
    import {IS_MOBILE, StorageLocal} from "@/helpers/Utils";

    @Component({
        components: {
        },
    })
    export default class Onboarding extends Vue {

        @Ref('emailInput')
        public emailInput!: HTMLInputElement;

        protected isMobile: boolean = IS_MOBILE;
                
        protected email: string = '';
        protected username: string = '';
        protected password: string = '';
        protected title: string = this.$t('welcome_to') + this.$store.state.Group.title;
        protected desc: string = this.$store.state.Group.description;

        protected mounted() {
            if (this.emailInput.hasOwnProperty('focus')) {
                this.emailInput.focus();
            }
        }

        /**
         * Called in step 0 when user inputs email and clicks on continue
         */
        protected checkData() {
            if (!this.email) {
                this.$message.error(this.$t('email_required') as string);
                return;
            }

            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (!re.test(this.email.toLowerCase())) {
                this.$message.error(this.$t('email_invalid') as string);
                return;
            }

            const data = new FormData();

            data.append('email', encodeURIComponent(this.email));
            data.append('go_back', encodeURIComponent(process.env.VUE_APP_DOMAIN + this.$route.fullPath));

            this.$store.commit('setShowProgressLine', true);

            this.$store.dispatch('User/checkEmail', data)
            .then((response: any) => {
                if (response == '40013') {
                    this.$message.error(this.$t('not_to_join') as string);
                } else {
                    this.username = response.username;
                    this.afterEmailChecked();
                }

            })
            .catch(() => {
                this.$message.error(this.$t('network_error') as string);
            })
            .finally(() => {
                this.$store.commit('setShowProgressLine', false);
            });
        }

        @Emit('email-inbound')
        protected afterEmailChecked() {
            return {
                email: this.email,
                username: this.username,
            };
        }
        
    }
</script>
<style lang="scss" scoped>

    .onboarding {

        @include modal_flexbox;

        .wrapper {
            width: 100%;

            .title {
                @include title_font;
                font-size: $font-size5;
                // font-weight: bold;
                margin-top: -20px;
                margin-bottom: var(--p2);
            }

            .desc {
                @include content_font;
                // font-size: $font-size1;
                // line-height: $font-size1-line-height;
                margin-bottom: var(--p8);
            }


            .flexbox {
                //display: flex;
                //flex-direction: row;
                //
                //.or {
                //    flex-basis: 60px;
                //    flex-shrink: 0;
                //    text-transform: uppercase;
                //    // color: #e8e8e8;
                //    color: #333;
                //    display: flex;
                //    align-items: center;
                //    justify-content: center;
                //    font-size: 0.9rem;
                //}

                .qr-code, .email-login {
                    width: 60%;
                    margin-left: 20%;
                }

                .label {
                    @include secondary_title_font();
                    // font-size: $font-size4;
                    // line-height: $font-size4-line-height;
                    // font-weight: bold;
                    margin-bottom: var(--p10);

                    &.mobile {
                        line-height: 1.8rem;
                    }
                }

                $h: 50px;

                .ant-input, .ant-btn {
                    width: 100%;
                    height: $h;
                    margin-bottom: var(--p6);
                    font-size: $font-size1;

                    &.mobile {
                        font-size: 1rem;
                    }
                }

                .register-note {
                    @include description_font;
                    // line-height: 1.3rem;
                    font-size: 0.9rem;
                    line-height: 1.3rem;

                }
            }
        }

        &.mobile {
            .label {
                @include form_label;
                margin-bottom: var(--p6);
            }
        }

    }
</style>