<template>
    <a-modal
            v-model="showAdminLogin"
            :maskClosable="false"
            :width="modalWidth"
            :bodyStyle="modalHeight"
            :maskStyle="{backgroundColor: 'rgba(0,0,0,0.75)'}"
            :closable="false"
            :footer="null"
            :centered="true"
            v-on:cancel="closeModal"
    >
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
                            class="flexbox"
                    >

                        <div
                                class='email-login'
                        >
                            <div
                                    :class="['label',{'mobile':isMobile}]"
                            >{{$t('admin_login')}}</div>
                            <a-input
                                    :placeholder="$t('email')"
                                    size="large"
                                    v-model.lazy="email"
                                    v-on:pressEnter="checkData"
                                    ref="emailInput"
                            />

                            <div>

                                <a-input
                                        :placeholder="$t('username')"
                                        size="large"
                                        v-model.lazy="username"
                                />
                            </div>

                            <div>

                                <a-input
                                        :placeholder="$t('password')"
                                        size="large"
                                        type="password"
                                        v-model.lazy="password"
                                />
                            </div>


                            <a-button
                                    type="primary"
                                    size="large"
                                    v-on:click="checkData"
                                    :class="[{'mobile':isMobile}]"
                            >{{$t('register')}}
                            </a-button>
                            <div
                                    :class="['register-note',{'mobile':isMobile}]"
                            >
                                {{$t('need_register')}}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </a-modal>
</template>

<script lang="ts">
    import {Component, Emit, Ref, Vue} from 'vue-property-decorator';
    import {
        IS_MOBILE, PASSWORD_MAX_LENGTH,
        PASSWORD_MIN_LENGTH,
        StorageLocal,
        USERNAME_MIN_LENGTH,
        USRENAME_MAX_LENGTH
    } from "@/helpers/Utils";
    import {ResponseError} from "@/http/ResponseError";
    import {RawLocation} from "vue-router";

    @Component({
        components: {
        },
    })
    export default class AdminLogin extends Vue {

        @Ref('emailInput')
        public emailInput!: HTMLInputElement;

        protected isMobile: boolean = IS_MOBILE;
                
        protected email: string = '';
        protected username: string = '';
        protected password: string = '';
        protected title: string = this.$t('welcome_to') + this.$store.state.Group.title;
        protected desc: string = this.$t('admin_login') as string;
        protected modalWidth: number = 750;
        protected modalHeight = {height: '650px'};
        protected showAdminLogin:boolean = true;
        protected usernameMinLength: number = USERNAME_MIN_LENGTH;
        protected usernameMaxLength: number = USRENAME_MAX_LENGTH;
        protected passwordMinLength: number = PASSWORD_MIN_LENGTH;
        protected passwordMaxLength: number = PASSWORD_MAX_LENGTH;


        protected mounted() {

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

            if (!this.username || this.username.length < this.usernameMinLength || this.username.length > this.usernameMaxLength) {
                this.$message.error(this.$t('username_error', {min: this.usernameMinLength, max: this.usernameMaxLength}) as string);
                return;
            }

            if (!this.password || this.password.length < this.passwordMinLength || this.password.length > this.passwordMaxLength) {
                this.$message.error(this.$t('password_error', {min: this.passwordMinLength, max: this.passwordMaxLength}) as string);
                return;
            }

            const data = new FormData();

            data.append('email', this.email);
            data.append('password', this.password);
            data.append('name', this.username);

            this.$store.dispatch('User/registerAdmin', data)
                    .then((data: {token: string} | any) => {
                        if (data && data.token) {
                            StorageLocal.setItem('bearer', data.token);
                            this.$store.dispatch('Group/load');
                            this.$store.dispatch('User/getMe');
                            this.closeModal();
                        } else if(data.getCode() == '40014') {
                            this.$message.error(this.$t('admin_exits') as string);
                            this.$store.dispatch('Group/load');
                            this.closeModal();
                        } else {
                            this.$message.error(data.getDescription());
                        }
                    });
        }

        protected closeModal() {

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
                margin-top: -20px;
                margin-bottom: var(--p2);
            }

            .desc {
                @include content_font;
                margin-bottom: var(--p8);
            }


            .flexbox {
                .qr-code, .email-login {
                    width: 60%;
                    margin-left: 20%;
                }

                .label {
                    @include secondary_title_font();
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