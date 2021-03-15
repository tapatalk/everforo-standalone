<template>
    <section
        class="password-login"
    >
        <div class="wrapper">
            <div 
                class="title"
            >{{$t('welcome_back', {username: username})}}</div>
            <a-input
                :placeholder="$t('password_continue')"
                size="large"
                type="password"
                v-on:pressEnter="loginWithPassword"
                v-model.lazy="password"
            />
            <a-button
                type="primary"
                size="large"
                v-on:click="loginWithPassword"
            >{{$t('continue')}}
            </a-button>
            <div 
                class="reset-password"
            >
                <span>{{$t('forgot_password')}}</span>
                <span
                    v-if="!isResettingPassword"
                    class="reset-link"
                    v-on:click="resetPassword"
                >{{$t('reset')}}</span>
            </div>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {PASSWORD_MIN_LENGTH, PASSWORD_MAX_LENGTH, StorageLocal} from '@/helpers/Utils';
    import {ResponseError} from '@/http/ResponseError';

    @Component({
        components: {

        },
    })
    export default class PasswordLogin extends Vue {

        @Prop()
        public email!: string;
        @Prop()
        public username!: string;

        protected password: string = '';

        protected passwordMinLength: number = PASSWORD_MIN_LENGTH;
        protected passwordMaxLength: number = PASSWORD_MAX_LENGTH;

        protected isResettingPassword : boolean = false;

        /**
         * If user's email already exists, will go to step 3
         * Login user with email and password
         */
        protected async loginWithPassword() {
            if (!this.password || this.password.length < this.passwordMinLength || this.password.length > this.passwordMaxLength) {
                this.$message.error(this.$t('wrong_password', {min: this.passwordMinLength, max: this.passwordMaxLength}) as string);
                return;
            }

            const data = new FormData();

            data.append('email', this.email);
            data.append('password', this.password);

            this.$store.commit('setShowProgressLine', true);

            this.$store.dispatch('User/loginWithEmail', data)
            .then((token: string) => {
                if (!token) {
                    this.$message.error(this.$t('wrong_password') as string);
                    return;
                }
                StorageLocal.setItem('bearer', token);
                this.$store.dispatch('User/getMe')
                .then(() => {
                    this.$store.commit('setShowLoginModal', false);
                })
                .catch((error: ResponseError) => {
                    StorageLocal.removeItem('bearer');
                    this.$message.error(this.$t('login_failed') as string);
                });
            })
            .finally(() => {
                this.$store.commit('setShowProgressLine', false);
            });
        }

        // When user clicks on reset link in step 3, server will send the link
        protected async resetPassword() {

            if(this.isResettingPassword == true){
                return;
            }

            this.isResettingPassword = true;

            const data = new FormData();
            data.append('email', this.email);

            this.$store.dispatch('User/sendResetPasswordLink', data)
            .then((response) => {
                if (response.success) {
                    this.resetPasswordLinkSent();
                } else {
                    this.$message.error(this.$t('network_error') as string);
                    this.isResettingPassword = false;
                }
            });

        }
        

        @Emit('reset-password')
        protected resetPasswordLinkSent() {
            
        }
        
    }
</script>
<style lang="scss" scoped>
    .password-login {
        
        @include modal_flexbox;

        .title {
            @include title_font;
            font-size: $font-size5;
        }
        // .label {
        //     @include form_label;
        //     padding-bottom: var(--p5);
        // }

        $h: 50px;

        .ant-input {
            margin-top: var(--p4);
            height: $h;
        }

        .ant-btn {
            width: 100%;
            border-radius: $border-radius1;
            margin-top: var(--p6);
            height: $h;
        }
       
        .reset-password {
            margin-top: var(--p6);
            @include content_font;
            .reset-link {
                text-transform: capitalize;
                text-decoration: underline;
                color: var(--theme-color);
                margin-left: var(--p2);
                cursor: pointer;
            }
        }
    }
</style>
